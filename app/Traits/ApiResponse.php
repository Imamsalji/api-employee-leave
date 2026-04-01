<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

trait ApiResponse
{
    protected function successResponse(
        mixed  $data = null,
        string $message = 'Success',
        int    $statusCode = 200
    ): JsonResponse {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if (! is_null($data)) {
            $response['data'] = $data;
        }

        return response()->json($response, $statusCode);
    }

    protected function createdResponse(
        mixed  $data = null,
        string $message = 'Data berhasil dibuat'
    ): JsonResponse {
        return $this->successResponse($data, $message, 201);
    }

    protected function errorResponse(
        string $message,
        array  $errors = [],
        int    $statusCode = 400
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors'  => $errors,
        ], $statusCode);
    }

    protected function paginatedResponse(
        ResourceCollection $collection,
        string             $message = 'Success'
    ): JsonResponse {
        $data = $collection->response()->getData(true);

        return response()->json([
            'success'  => true,
            'message'  => $message,
            'data'     => $data['data'],
            'meta'     => $data['meta'] ?? null,
            'links'    => $data['links'] ?? null,
        ]);
    }
}
