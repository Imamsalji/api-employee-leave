<?php
// database/seeders/LeaveQuotaSeeder.php

namespace Database\Seeders;

use App\Models\LeaveQuota;
use App\Models\User;
use Illuminate\Database\Seeder;

class LeaveQuotaSeeder extends Seeder
{
    public function run(): void
    {
        $currentYear = now()->year;

        User::where('role', 'employee')->each(function (User $user) use ($currentYear) {
            LeaveQuota::firstOrCreate(
                ['user_id' => $user->id, 'year' => $currentYear],
                [
                    'total_days'     => 12,
                    'used_days'      => 0,
                    'remaining_days' => 12,
                ]
            );
        });
    }
}
