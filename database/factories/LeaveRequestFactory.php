<?php

namespace Database\Factories;

use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LeaveRequest>
 */
class LeaveRequestFactory extends Factory
{
    protected $model = LeaveRequest::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('+1 day', '+30 days');
        $endDate   = fake()->dateTimeBetween($startDate, '+35 days');

        $start = \Carbon\Carbon::instance($startDate);
        $end   = \Carbon\Carbon::instance($endDate);

        return [
            'user_id'          => User::factory()->employee(),
            'start_date'       => $start->toDateString(),
            'end_date'         => $end->toDateString(),
            'total_days'       => $start->diffInDays($end) + 1,
            'reason'           => fake()->sentence(10),
            'status'           => LeaveRequest::STATUS_PENDING,
            'rejection_reason' => null,
            'approved_by'      => null,
            'approved_at'      => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(['status' => LeaveRequest::STATUS_PENDING]);
    }

    public function approved(): static
    {
        return $this->state(function () {
            $admin = User::factory()->admin()->create();
            return [
                'status'      => LeaveRequest::STATUS_APPROVED,
                'approved_by' => $admin->id,
                'approved_at' => now(),
            ];
        });
    }

    public function rejected(): static
    {
        return $this->state(function () {
            $admin = User::factory()->admin()->create();
            return [
                'status'           => LeaveRequest::STATUS_REJECTED,
                'rejection_reason' => fake()->sentence(),
                'approved_by'      => $admin->id,
                'approved_at'      => now(),
            ];
        });
    }

    /**
     * Buat leave request dengan tanggal spesifik
     */
    public function forDates(string $start, string $end): static
    {
        $startCarbon = \Carbon\Carbon::parse($start);
        $endCarbon   = \Carbon\Carbon::parse($end);

        return $this->state([
            'start_date' => $start,
            'end_date'   => $end,
            'total_days' => $startCarbon->diffInDays($endCarbon) + 1,
        ]);
    }
}
