<?php
// app/Http/Resources/LeaveQuotaResource.php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaveQuotaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'year'            => $this->year,
            'total_days'      => $this->total_days,
            'used_days'       => $this->used_days,
            'remaining_days'  => $this->remaining_days,
        ];
    }
}
