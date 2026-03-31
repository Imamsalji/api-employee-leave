<?php
// app/Models/LeaveAttachment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class LeaveAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'leave_request_id',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
        ];
    }

    // =========================================================
    // RELATIONSHIPS
    // =========================================================

    public function leaveRequest(): BelongsTo
    {
        return $this->belongsTo(LeaveRequest::class);
    }

    // =========================================================
    // ACCESSORS
    // =========================================================

    /**
     * URL publik file (untuk response API)
     * Append accessor — otomatis tersedia sebagai $attachment->url
     */
    protected $appends = ['url', 'file_size_formatted'];

    public function getUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }

    /**
     * Ukuran file dalam format human-readable (KB/MB)
     */
    public function getFileSizeFormattedAttribute(): string
    {
        $bytes = $this->file_size;

        if ($bytes >= 1_048_576) {
            return round($bytes / 1_048_576, 2) . ' MB';
        }

        return round($bytes / 1_024, 2) . ' KB';
    }
}
