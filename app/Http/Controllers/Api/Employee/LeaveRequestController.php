<?php
// app/Http/Controllers/Api/Employee/LeaveRequestController.php

namespace App\Http\Controllers\Api\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubmitLeaveRequest;
use App\Http\Resources\LeaveQuotaResource;
use App\Http\Resources\LeaveRequestCollection;
use App\Http\Resources\LeaveRequestResource;
use App\Repositories\Contracts\LeaveQuotaRepositoryInterface;
use App\Services\LeaveService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeaveRequestController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly LeaveService                  $leaveService,
        private readonly LeaveQuotaRepositoryInterface $leaveQuotaRepo,
    ) {}

    /**
     * GET /api/employee/leaves
     * Daftar cuti milik employee yang login
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['status', 'year']);

        $leaves = $this->leaveService->getMyLeaves(
            user: $request->user(),
            filters: $filters
        );

        return $this->paginatedResponse(
            new LeaveRequestCollection($leaves),
            'Daftar pengajuan cuti berhasil diambil.'
        );
    }

    /**
     * POST /api/employee/leaves
     * Ajukan cuti baru
     */
    public function store(SubmitLeaveRequest $request): JsonResponse
    {
        $leaveRequest = $this->leaveService->submitLeave(
            user: $request->user(),
            data: $request->validated() + ['attachments' => $request->file('attachments')]
        );

        return $this->createdResponse(
            new LeaveRequestResource($leaveRequest),
            'Pengajuan cuti berhasil dikirim dan menunggu persetujuan.'
        );
    }

    /**
     * GET /api/employee/leaves/{id}
     * Detail satu pengajuan cuti
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $leave = $this->leaveService->getLeaveDetail(
            leaveRequestId: $id,
            user: $request->user()
        );

        return $this->successResponse(
            new LeaveRequestResource($leave),
            'Detail pengajuan cuti berhasil diambil.'
        );
    }

    /**
     * GET /api/employee/leaves/quota
     * Cek sisa kuota cuti tahun ini
     */
    public function quota(Request $request): JsonResponse
    {
        $quota = $this->leaveQuotaRepo->getOrCreateForYear(
            userId: $request->user()->id,
            year: now()->year
        );

        return $this->successResponse(
            new LeaveQuotaResource($quota),
            'Informasi kuota cuti berhasil diambil.'
        );
    }
}
