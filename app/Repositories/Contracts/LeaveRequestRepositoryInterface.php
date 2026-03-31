<?php
// app/Repositories/Contracts/LeaveRequestRepositoryInterface.php

namespace App\Repositories\Contracts;

use App\Models\LeaveRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface LeaveRequestRepositoryInterface
{
    /**
     * Ambil semua pengajuan cuti (untuk admin) dengan pagination
     */
    public function getAllPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Ambil semua cuti milik satu user dengan pagination
     */
    public function getByUserPaginated(int $userId, array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Cari satu leave request by ID, eager load relasi
     */
    public function findById(int $id): ?LeaveRequest;

    /**
     * Buat pengajuan cuti baru
     */
    public function create(array $data): LeaveRequest;

    /**
     * Update status cuti (approve / reject)
     */
    public function updateStatus(LeaveRequest $leaveRequest, array $data): LeaveRequest;

    /**
     * Cek apakah ada overlap tanggal cuti untuk user tertentu
     */
    public function hasOverlapping(int $userId, string $startDate, string $endDate, ?int $excludeId = null): bool;

    /**
     * Hitung total hari cuti yang sudah diambil user di tahun tertentu
     */
    public function getTotalApprovedDays(int $userId, int $year): int;
}
