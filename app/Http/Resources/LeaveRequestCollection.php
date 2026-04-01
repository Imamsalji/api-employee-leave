<?php
// app/Http/Resources/LeaveRequestCollection.php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class LeaveRequestCollection extends ResourceCollection
{
    public $collects = LeaveRequestResource::class;

    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
        ];
    }

    /**
     * Tambahkan informasi tambahan di level collection
     * Berguna untuk menampilkan summary di list page
     */
    public function with(Request $request): array
    {
        return [
            'meta' => [
                'filters_available' => ['status', 'year'],
            ],
        ];
    }
}
