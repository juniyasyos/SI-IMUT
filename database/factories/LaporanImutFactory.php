<?php

namespace Database\Factories;

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
        return [
            'name' => $this->faker->unique()->word(),
            'status' => $this->faker->randomElement(['process', 'complete', 'canceled']),
            'assessment_period_start' => now()->subDays(rand(0, 365)),
            'assessment_period_end' => now()->subDays(rand(0, 365)),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
