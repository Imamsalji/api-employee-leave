<?php
// app/Repositories/Contracts/LeaveQuotaRepositoryInterface.php

namespace App\Repositories\Contracts;

use App\Models\LeaveQuota;

interface LeaveQuotaRepositoryInterface
{
    /**
     * Ambil kuota user di tahun tertentu
     * Jika belum ada, buat otomatis (auto-provision)
     */
    public function getOrCreateForYear(int $userId, int $year): LeaveQuota;

    /**
     * Kurangi kuota setelah cuti di-approve
     */
    public function deductQuota(int $userId, int $year, int $days): bool;

    /**
     * Kembalikan kuota jika cuti dibatalkan / di-reject ulang
     */
    public function restoreQuota(int $userId, int $year, int $days): void;
}
