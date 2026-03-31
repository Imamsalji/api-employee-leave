<?php
// app/Repositories/LeaveRequestRepository.php

namespace App\Repositories;

use App\Models\LeaveRequest;
use App\Repositories\Contracts\LeaveRequestRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class LeaveRequestRepository implements LeaveRequestRepositoryInterface
{
    public function __construct(
        private readonly LeaveRequest $model
    ) {}

    public function getAllPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->with(['user', 'approver', 'attachments'])
            ->when(
                isset($filters['status']),
                fn($q) => $q->where('status', $filters['status'])
            )
            ->when(
                isset($filters['user_id']),
                fn($q) => $q->where('user_id', $filters['user_id'])
            )
            ->when(
                isset($filters['year']),
                fn($q) => $q->forYear($filters['year'])
            )
            ->latest()
            ->paginate($perPage);
    }

    public function getByUserPaginated(int $userId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->with(['approver', 'attachments'])
            ->forUser($userId)
            ->when(
                isset($filters['status']),
                fn($q) => $q->where('status', $filters['status'])
            )
            ->when(
                isset($filters['year']),
                fn($q) => $q->forYear($filters['year'])
            )
            ->latest()
            ->paginate($perPage);
    }

    public function findById(int $id): ?LeaveRequest
    {
        return $this->model
            ->with(['user', 'approver', 'attachments'])
            ->find($id);
    }

    public function create(array $data): LeaveRequest
    {
        return $this->model->create($data);
    }

    public function updateStatus(LeaveRequest $leaveRequest, array $data): LeaveRequest
    {
        $leaveRequest->update($data);

        return $leaveRequest->fresh(['user', 'approver', 'attachments']);
    }

    public function hasOverlapping(
        int    $userId,
        string $startDate,
        string $endDate,
        ?int   $excludeId = null
    ): bool {
        return $this->model
            ->overlapping($userId, $startDate, $endDate, $excludeId)
            ->exists();
    }

    public function getTotalApprovedDays(int $userId, int $year): int
    {
        return $this->model
            ->forUser($userId)
            ->approved()
            ->forYear($year)
            ->sum('total_days');
    }
}
