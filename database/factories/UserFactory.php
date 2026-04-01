<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'              => fake()->name(),
            'email'             => fake()->unique()->safeEmail(),
            'password'          => Hash::make('password'),
            'role'              => User::ROLE_EMPLOYEE,
            'jabatan'           => fake()->jobTitle(),
            'divisi'            => fake()->randomElement(['HR', 'IT', 'Finance', 'Marketing']),
            'email_verified_at' => now(),
            'remember_token'    => null,
        ];
    }

    public function admin(): static
    {
        return $this->state(['role' => User::ROLE_ADMIN]);
    }

    public function employee(): static
    {
        return $this->state(['role' => User::ROLE_EMPLOYEE]);
    }

    public function unverified(): static
    {
        return $this->state(['email_verified_at' => null]);
    }
}
