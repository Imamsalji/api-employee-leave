<?php

namespace Database\Factories;

use App\Models\LeaveQuota;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LeaveQuota>
 */
class LeaveQuotaFactory extends Factory
{
    protected $model = LeaveQuota::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'        => User::factory()->employee(),
            'year'           => now()->year,
            'total_days'     => 12,
            'used_days'      => 0,
            'remaining_days' => 12,
        ];
    }

    /**
     * Simulasi user yang sudah pakai sebagian kuota
     */
    public function withUsedDays(int $used): static
    {
        return $this->state([
            'used_days'      => $used,
            'remaining_days' => 12 - $used,
        ]);
    }

    /**
     * Simulasi kuota habis total
     */
    public function exhausted(): static
    {
        return $this->state([
            'used_days'      => 12,
            'remaining_days' => 0,
        ]);
    }

    /**
     * Kuota untuk tahun tertentu
     */
    public function forYear(int $year): static
    {
        return $this->state(['year' => $year]);
    }
}
