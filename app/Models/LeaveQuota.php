<?php
// app/Models/LeaveQuota.php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveQuota extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'year',
        'total_days',
        'used_days',
        'remaining_days',
    ];

    protected function casts(): array
    {
        return [
            'year'           => 'integer',
            'total_days'     => 'integer',
            'used_days'      => 'integer',
            'remaining_days' => 'integer',
        ];
    }

    // =========================================================
    // RELATIONSHIPS
    // =========================================================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // =========================================================
    // QUERY SCOPES
    // =========================================================

    /**
     * Filter kuota berdasarkan tahun tertentu
     *
     * Usage: LeaveQuota::forYear(2025)->...
     */
    public function scopeForYear(Builder $query, int $year): Builder
    {
        return $query->where('year', $year);
    }

    /**
     * Filter kuota tahun berjalan
     *
     * Usage: LeaveQuota::currentYear()->...
     */
    public function scopeCurrentYear(Builder $query): Builder
    {
        return $query->where('year', now()->year);
    }

    /**
     * Filter kuota yang masih tersisa
     *
     * Usage: LeaveQuota::hasRemainingDays()->...
     */
    public function scopeHasRemainingDays(Builder $query): Builder
    {
        return $query->where('remaining_days', '>', 0);
    }

    // =========================================================
    // HELPER METHODS
    // =========================================================

    /**
     * Cek apakah kuota mencukupi untuk jumlah hari tertentu
     */
    public function isSufficient(int $days): bool
    {
        return $this->remaining_days >= $days;
    }

    /**
     * Deduct kuota (dipanggil saat cuti di-approve)
     * Menggunakan increment/decrement untuk menghindari race condition
     */
    public function deductDays(int $days): bool
    {
        if (! $this->isSufficient($days)) {
            return false;
        }

        $this->increment('used_days', $days);
        $this->decrement('remaining_days', $days);

        return true;
    }

    /**
     * Kembalikan kuota (dipanggil saat cuti yang approved di-cancel / di-reject ulang)
     */
    public function restoreDays(int $days): void
    {
        $this->decrement('used_days', $days);
        $this->increment('remaining_days', $days);
    }
}
