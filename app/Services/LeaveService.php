<?php
// app/Services/LeaveService.php

namespace App\Services;

use App\Exceptions\LeaveException;
use App\Models\LeaveAttachment;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Repositories\Contracts\LeaveQuotaRepositoryInterface;
use App\Repositories\Contracts\LeaveRequestRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class LeaveService
{
    public function __construct(
        private readonly LeaveRequestRepositoryInterface $leaveRequestRepo,
        private readonly LeaveQuotaRepositoryInterface   $leaveQuotaRepo,
    ) {}

    // =========================================================
    // EMPLOYEE ACTIONS
    // =========================================================

    /**
     * Proses pengajuan cuti baru dari employee
     * Semua validasi bisnis + simpan data dalam satu transaksi
     */
    public function submitLeave(User $user, array $data): LeaveRequest
    {
        $startDate = Carbon::parse($data['start_date']);
        $endDate   = Carbon::parse($data['end_date']);
        $year      = $startDate->year;

        // --- 1. Validasi range tanggal ---
        $this->validateDateRange($startDate, $endDate);

        // --- 2. Hitung jumlah hari kerja (termasuk weekend jika memang dihitung) ---
        $totalDays = $this->calculateLeaveDays($startDate, $endDate);

        // --- 3. Cek overlap dengan cuti lain ---
        $this->validateNoOverlap($user->id, $data['start_date'], $data['end_date']);

        // --- 4. Cek kuota tahunan ---
        $this->validateQuota($user->id, $year, $totalDays);

        // --- 5. Simpan semua dalam DB transaction ---
        return DB::transaction(function () use ($user, $data, $totalDays) {
            $leaveRequest = $this->leaveRequestRepo->create([
                'user_id'    => $user->id,
                'start_date' => $data['start_date'],
                'end_date'   => $data['end_date'],
                'total_days' => $totalDays,
                'reason'     => $data['reason'],
                'status'     => LeaveRequest::STATUS_PENDING,
            ]);

            // --- 6. Upload attachment jika ada ---
            if (! empty($data['attachments'])) {
                $this->storeAttachments($leaveRequest, $data['attachments']);
            }

            return $leaveRequest->load(['attachments']);
        });
    }

    /**
     * Daftar cuti milik employee yang sedang login
     */
    public function getMyLeaves(User $user, array $filters = []): LengthAwarePaginator
    {
        return $this->leaveRequestRepo->getByUserPaginated($user->id, $filters);
    }

    /**
     * Detail satu cuti — employee hanya boleh lihat punyanya sendiri
     */
    public function getLeaveDetail(int $leaveRequestId, User $user): LeaveRequest
    {
        $leave = $this->leaveRequestRepo->findById($leaveRequestId);

        if (! $leave) {
            throw LeaveException::notFound();
        }

        // Jika bukan admin, pastikan hanya bisa lihat miliknya sendiri
        if ($user->isEmployee() && $leave->user_id !== $user->id) {
            throw LeaveException::unauthorized();
        }

        return $leave;
    }

    // =========================================================
    // ADMIN ACTIONS
    // =========================================================

    /**
     * Semua pengajuan cuti (untuk admin)
     */
    public function getAllLeaves(array $filters = []): LengthAwarePaginator
    {
        return $this->leaveRequestRepo->getAllPaginated($filters);
    }

    /**
     * Approve pengajuan cuti
     * Kuota langsung dipotong saat di-approve
     */
    public function approveLeave(int $leaveRequestId, User $admin): LeaveRequest
    {
        $leave = $this->leaveRequestRepo->findById($leaveRequestId);

        if (! $leave) {
            throw LeaveException::notFound();
        }

        // Hanya status pending yang bisa di-approve
        if (! $leave->isPending()) {
            throw LeaveException::cannotReview($leave->status);
        }

        return DB::transaction(function () use ($leave, $admin) {
            // Potong kuota saat approve
            $year    = Carbon::parse($leave->start_date)->year;
            $success = $this->leaveQuotaRepo->deductQuota(
                $leave->user_id,
                $year,
                $leave->total_days
            );

            // Double-check kuota (race condition guard)
            if (! $success) {
                throw LeaveException::quotaExceeded(0, $leave->total_days);
            }

            return $this->leaveRequestRepo->updateStatus($leave, [
                'status'      => LeaveRequest::STATUS_APPROVED,
                'approved_by' => $admin->id,
                'approved_at' => now(),
            ]);
        });
    }

    /**
     * Reject pengajuan cuti
     */
    public function rejectLeave(int $leaveRequestId, User $admin, string $reason): LeaveRequest
    {
        $leave = $this->leaveRequestRepo->findById($leaveRequestId);

        if (! $leave) {
            throw LeaveException::notFound();
        }

        // Hanya status pending yang bisa di-reject
        if (! $leave->isPending()) {
            throw LeaveException::cannotReview($leave->status);
        }

        return $this->leaveRequestRepo->updateStatus($leave, [
            'status'           => LeaveRequest::STATUS_REJECTED,
            'rejection_reason' => $reason,
            'approved_by'      => $admin->id,
            'approved_at'      => now(),
        ]);
    }

    // =========================================================
    // PRIVATE HELPERS — Business Logic
    // =========================================================

    /**
     * Validasi end_date >= start_date
     */
    private function validateDateRange(Carbon $startDate, Carbon $endDate): void
    {
        if ($endDate->lt($startDate)) {
            throw LeaveException::invalidDateRange();
        }
    }

    /**
     * Hitung jumlah hari cuti (inclusive, termasuk weekend)
     * Ganti dengan logika hari kerja saja jika diperlukan
     *
     * Contoh: 1 Jan - 3 Jan = 3 hari
     */
    private function calculateLeaveDays(Carbon $startDate, Carbon $endDate): int
    {
        // +1 karena inclusive (start dan end dihitung)
        return $startDate->diffInDays($endDate) + 1;
    }

    /**
     * Pastikan tidak ada overlap dengan cuti lain yang pending/approved
     */
    private function validateNoOverlap(
        int    $userId,
        string $startDate,
        string $endDate,
        ?int   $excludeId = null
    ): void {
        $hasOverlap = $this->leaveRequestRepo->hasOverlapping(
            $userId,
            $startDate,
            $endDate,
            $excludeId
        );

        if ($hasOverlap) {
            throw LeaveException::overlappingDates($startDate, $endDate);
        }
    }

    /**
     * Pastikan sisa kuota mencukupi untuk jumlah hari yang diajukan
     */
    private function validateQuota(int $userId, int $year, int $requestedDays): void
    {
        $quota = $this->leaveQuotaRepo->getOrCreateForYear($userId, $year);

        if (! $quota->isSufficient($requestedDays)) {
            throw LeaveException::quotaExceeded($quota->remaining_days, $requestedDays);
        }
    }

    /**
     * Simpan file attachment ke storage dan catat ke DB
     *
     * @param  array<UploadedFile>  $files
     */
    private function storeAttachments(LeaveRequest $leaveRequest, array $files): void
    {
        foreach ($files as $file) {
            $path = $file->store(
                "leave-attachments/{$leaveRequest->id}",
                'public'
            );

            LeaveAttachment::create([
                'leave_request_id' => $leaveRequest->id,
                'file_name'        => $file->getClientOriginalName(),
                'file_path'        => $path,
                'file_type'        => $file->getClientOriginalExtension(),
                'file_size'        => $file->getSize(),
            ]);
        }
    }
}
