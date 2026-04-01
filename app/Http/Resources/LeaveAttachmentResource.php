<?php
// app/Http/Resources/LeaveAttachmentResource.php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaveAttachmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'file_name'           => $this->file_name,
            'file_type'           => $this->file_type,
            'file_size'           => $this->file_size,
            'file_size_formatted' => $this->file_size_formatted, // accessor dari model
            'url'                 => $this->url,                 // accessor dari model
            'uploaded_at'         => $this->created_at->toDateTimeString(),
        ];
    }
}
