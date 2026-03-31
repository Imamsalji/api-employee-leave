<?php
// app/Models/User.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Konstanta role — hindari magic string di seluruh codebase
     */
    const ROLE_ADMIN    = 'admin';
    const ROLE_EMPLOYEE = 'employee';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'jabatan',
        'divisi',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'role'              => 'string',
        ];
    }

    // =========================================================
    // RELATIONSHIPS
    // =========================================================

    /**
     * Semua pengajuan cuti milik user ini
     */
    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    /**
     * Semua kuota cuti milik user ini (lintas tahun)
     */
    public function leaveQuotas(): HasMany
    {
        return $this->hasMany(LeaveQuota::class);
    }

    /**
     * Kuota cuti tahun ini (shortcut)
     */
    public function currentYearQuota(): HasOne
    {
        return $this->hasOne(LeaveQuota::class)
            ->where('year', now()->year)
            ->latestOfMany();
    }

    /**
     * Cuti yang pernah di-approve/reject oleh admin ini
     */
    public function reviewedLeaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class, 'approved_by');
    }

    // =========================================================
    // HELPER METHODS
    // =========================================================

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isEmployee(): bool
    {
        return $this->role === self::ROLE_EMPLOYEE;
    }
}
