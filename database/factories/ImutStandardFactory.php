<?php

namespace Database\Factories;

use App\Models\ImutProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ImutStandard>
 */
class ImutStandardFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = $this->faker->dateTimeBetween('-1 year', 'now');
        $end = (clone $start)->modify('+6 months');

        return [
            'imut_profile_id' => ImutProfile::factory(),
            'value' => $this->faker->randomFloat(2, 71, 100),
            'description' => $this->faker->sentence(),
            'start_period' => $start->format('Y-m-d'),
            'end_period' => $end->format('Y-m-d'),
        ];
    }
}
