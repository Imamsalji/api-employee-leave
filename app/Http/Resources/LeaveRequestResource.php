<?php
// app/Http/Resources/LeaveRequestResource.php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaveRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'status'           => $this->status,

            // Info pemohon
            'employee'         => [
                'id'      => $this->user->id,
                'name'    => $this->user->name,
                'email'   => $this->user->email,
                'jabatan' => $this->user->jabatan,
                'divisi'  => $this->user->divisi,
            ],

            // Info cuti
            'start_date'       => $this->start_date->toDateString(),
            'end_date'         => $this->end_date->toDateString(),
            'total_days'       => $this->total_days,
            'reason'           => $this->reason,

            // Info review (hanya tampil jika sudah diproses)
            'reviewed_by'      => $this->when(
                ! is_null($this->approved_by),
                fn() => [
                    'id'   => $this->approver->id,
                    'name' => $this->approver->name,
                ]
            ),
            'reviewed_at'      => $this->when(
                ! is_null($this->approved_at),
                fn() => $this->approved_at->toDateTimeString()
            ),
            'rejection_reason' => $this->when(
                $this->isRejected(),
                $this->rejection_reason
            ),

            // Lampiran
            'attachments'      => LeaveAttachmentResource::collection(
                $this->whenLoaded('attachments')
            ),

            'created_at'       => $this->created_at->toDateTimeString(),
            'updated_at'       => $this->updated_at->toDateTimeString(),
        ];
    }
}
