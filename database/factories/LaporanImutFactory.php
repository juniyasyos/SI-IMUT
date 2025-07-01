<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LaporanImut>
 */
class LaporanImutFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $admin = User::where('name', 'admin')->first();

        return [
            'name' => $this->faker->unique()->word(),
            'status' => $this->faker->randomElement(['process', 'complete', 'coming_soon']),
            'assessment_period_start' => now()->subDays(rand(30, 365)),
            'assessment_period_end' => now()->subDays(rand(0, 29)),
            'created_by' => $admin?->id ?? User::factory(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
