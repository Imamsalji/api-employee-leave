<?php
// app/Http/Requests/ReviewLeaveRequest.php

namespace App\Http\Requests;

use App\Models\LeaveRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReviewLeaveRequest extends FormRequest
{
    /**
     * Hanya admin yang bisa approve/reject
     */
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'action' => [
                'required',
                Rule::in([LeaveRequest::STATUS_APPROVED, LeaveRequest::STATUS_REJECTED]),
            ],
            // rejection_reason wajib jika action = rejected
            'rejection_reason' => [
                'nullable',
                'required_if:action,rejected',
                'string',
                'min:5',
                'max:500',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'action.required'                => 'Action wajib diisi (approved / rejected).',
            'action.in'                      => 'Action hanya boleh: approved atau rejected.',
            'rejection_reason.required_if'   => 'Alasan penolakan wajib diisi jika cuti ditolak.',
            'rejection_reason.min'           => 'Alasan penolakan minimal 5 karakter.',
            'rejection_reason.max'           => 'Alasan penolakan maksimal 500 karakter.',
        ];
    }
}
