<?php
// app/Repositories/LeaveQuotaRepository.php

namespace App\Repositories;

use App\Models\LeaveQuota;
use App\Repositories\Contracts\LeaveQuotaRepositoryInterface;

class LeaveQuotaRepository implements LeaveQuotaRepositoryInterface
{
    public function __construct(
        private readonly LeaveQuota $model
    ) {}

    public function getOrCreateForYear(int $userId, int $year): LeaveQuota
    {
        return $this->model->firstOrCreate(
            ['user_id' => $userId, 'year' => $year],
            [
                'total_days'     => 12,
                'used_days'      => 0,
                'remaining_days' => 12,
            ]
        );
    }

    public function deductQuota(int $userId, int $year, int $days): bool
    {
        $quota = $this->getOrCreateForYear($userId, $year);

        return $quota->deductDays($days);
    }

    public function restoreQuota(int $userId, int $year, int $days): void
    {
        $quota = $this->getOrCreateForYear($userId, $year);

        $quota->restoreDays($days);
    }
}
