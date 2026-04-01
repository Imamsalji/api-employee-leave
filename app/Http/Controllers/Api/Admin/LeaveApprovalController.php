<?php
// app/Http/Controllers/Api/Admin/LeaveApprovalController.php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReviewLeaveRequest;
use App\Http\Resources\LeaveRequestCollection;
use App\Http\Resources\LeaveRequestResource;
use App\Models\LeaveRequest;
use App\Services\LeaveService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeaveApprovalController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly LeaveService $leaveService,
    ) {}

    /**
     * GET /api/admin/leaves
     * Semua pengajuan cuti dari seluruh employee
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['status', 'year', 'user_id']);

        $leaves = $this->leaveService->getAllLeaves($filters);

        return $this->paginatedResponse(
            new LeaveRequestCollection($leaves),
            'Semua pengajuan cuti berhasil diambil.'
        );
    }

    /**
     * GET /api/admin/leaves/{id}
     * Detail pengajuan cuti (admin bisa lihat semua)
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
     * PUT /api/admin/leaves/{id}/review
     * Approve atau Reject pengajuan cuti
     */
    public function review(ReviewLeaveRequest $request, int $id): JsonResponse
    {
        $action = $request->validated('action');

        $leave = match ($action) {
            LeaveRequest::STATUS_APPROVED => $this->leaveService->approveLeave(
                leaveRequestId: $id,
                admin: $request->user()
            ),
            LeaveRequest::STATUS_REJECTED => $this->leaveService->rejectLeave(
                leaveRequestId: $id,
                admin: $request->user(),
                reason: $request->validated('rejection_reason')
            ),
        };

        $message = $action === LeaveRequest::STATUS_APPROVED
            ? 'Pengajuan cuti berhasil disetujui.'
            : 'Pengajuan cuti berhasil ditolak.';

        return $this->successResponse(
            new LeaveRequestResource($leave),
            $message
        );
    }
}
