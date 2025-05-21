<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ImutProfile>
 */
class ImutProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'rationale' => $this->faker->paragraph(),
            'quality_dimension' => $this->faker->word(),
            'objective' => $this->faker->sentence(),
            'operational_definition' => $this->faker->sentence(),
            'indicator_type' => $this->faker->randomElement(['process', 'output', 'outcome']),
            'numerator_formula' => $this->faker->sentence(),
            'denominator_formula' => $this->faker->sentence(),
            'inclusion_criteria' => $this->faker->paragraph(),
            'exclusion_criteria' => $this->faker->paragraph(),
            'data_source' => $this->faker->company(),
            'data_collection_frequency' => $this->faker->randomElement(['Bulanan', 'Triwulan', 'Tahunan']),
            'analysis_plan' => $this->faker->paragraph(),
            'target_operator' => '=>',
            'target_value' => $this->faker->numberBetween(70, 100),
            'analysis_period_type' => $this->faker->word(),
            'analysis_period_value' => $this->faker->numberBetween(1, 12),
            'data_collection_method' => $this->faker->sentence(),
            'sampling_method' => $this->faker->sentence(),
            'data_collection_tool' => $this->faker->paragraph(),
            'responsible_person' => $this->faker->name(),
        ];
    }
}
