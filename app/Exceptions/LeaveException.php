<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeaveException extends Exception
{
    /**
     * Named constructors — lebih ekspresif daripada new LeaveException('...')
     */
    public static function quotaExceeded(int $remaining, int $requested): self
    {
        return new self(
            "Kuota cuti tidak mencukupi. Sisa kuota: {$remaining} hari, diajukan: {$requested} hari.",
            422
        );
    }

    public static function overlappingDates(string $start, string $end): self
    {
        return new self(
            "Tanggal cuti {$start} s/d {$end} bentrok dengan pengajuan cuti yang sudah ada.",
            422
        );
    }

    public static function invalidDateRange(): self
    {
        return new self(
            'Tanggal selesai harus sama dengan atau setelah tanggal mulai.',
            422
        );
    }

    public static function notFound(): self
    {
        return new self('Pengajuan cuti tidak ditemukan.', 404);
    }

    public static function cannotReview(string $currentStatus): self
    {
        return new self(
            "Pengajuan cuti dengan status '{$currentStatus}' tidak dapat diproses ulang.",
            422
        );
    }

    public static function unauthorized(): self
    {
        return new self(
            'Anda tidak memiliki akses ke pengajuan cuti ini.',
            403
        );
    }

    /**
     * Render otomatis sebagai JSON saat exception ini di-throw
     */
    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
            'errors'  => [],
        ], $this->getCode());
    }
}
