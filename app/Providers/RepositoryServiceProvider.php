<?php
// app/Providers/RepositoryServiceProvider.php

namespace App\Providers;

use App\Repositories\Contracts\LeaveQuotaRepositoryInterface;
use App\Repositories\Contracts\LeaveRequestRepositoryInterface;
use App\Repositories\LeaveQuotaRepository;
use App\Repositories\LeaveRequestRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Daftarkan semua binding interface → implementasi di sini
     * Untuk swap implementasi (misal: ganti ke Redis cache layer),
     * cukup ubah di sini tanpa menyentuh Service atau Controller
     */
    public function register(): void
    {
        $this->app->bind(
            LeaveRequestRepositoryInterface::class,
            LeaveRequestRepository::class
        );

        $this->app->bind(
            LeaveQuotaRepositoryInterface::class,
            LeaveQuotaRepository::class
        );
    }
}
