<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ImutCategory>
 */
class ImutCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_name' => $this->faker->unique()->words(3, true),
            'short_name' => $this->faker->unique()->words(3, true),
            'description' => $this->faker->sentence(),
        ];
    }
}
