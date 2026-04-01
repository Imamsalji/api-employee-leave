<?php
// app/Http/Requests/SubmitLeaveRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitLeaveRequest extends FormRequest
{
    /**
     * Hanya employee yang bisa submit cuti
     */
    public function authorize(): bool
    {
        return $this->user()->isEmployee();
    }

    public function rules(): array
    {
        return [
            'start_date'    => ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:today'],
            'end_date'      => ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'reason'        => ['required', 'string', 'min:10', 'max:1000'],

            // Array attachment — opsional tapi jika ada harus valid
            'attachments'   => ['nullable', 'array', 'max:3'],
            'attachments.*' => [
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:2048', // 2MB per file
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'start_date.required'        => 'Tanggal mulai cuti wajib diisi.',
            'start_date.date_format'     => 'Format tanggal mulai harus YYYY-MM-DD.',
            'start_date.after_or_equal'  => 'Tanggal mulai tidak boleh kurang dari hari ini.',
            'end_date.required'          => 'Tanggal selesai cuti wajib diisi.',
            'end_date.date_format'       => 'Format tanggal selesai harus YYYY-MM-DD.',
            'end_date.after_or_equal'    => 'Tanggal selesai harus sama atau setelah tanggal mulai.',
            'reason.required'            => 'Alasan cuti wajib diisi.',
            'reason.min'                 => 'Alasan cuti minimal 10 karakter.',
            'reason.max'                 => 'Alasan cuti maksimal 1000 karakter.',
            'attachments.max'            => 'Maksimal 3 file lampiran.',
            'attachments.*.file'         => 'Lampiran harus berupa file.',
            'attachments.*.mimes'        => 'Lampiran harus berformat PDF, JPG, atau PNG.',
            'attachments.*.max'          => 'Ukuran setiap lampiran maksimal 2MB.',
        ];
    }

    /**
     * Transformasi nilai sebelum validasi dijalankan
     * Pastikan tanggal selalu dalam format yang benar
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'start_date' => $this->start_date ? trim($this->start_date) : null,
            'end_date'   => $this->end_date ? trim($this->end_date) : null,
            'reason'     => $this->reason ? trim($this->reason) : null,
        ]);
    }
}
