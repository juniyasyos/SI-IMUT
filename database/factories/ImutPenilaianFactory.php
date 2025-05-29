<?php

namespace Database\Factories;

use App\Models\UnitKerja;
use App\Models\ImutProfile;
use App\Models\LaporanUnitKerja;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ImutPenilaian>
 */
class ImutPenilaianFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'imut_profil_id' => ImutProfile::random()->id,
            'laporan_unit_kerja_id' => LaporanUnitKerja::random()->id,
            'analysis' => $this->faker->paragraph,
            'recommendations' => $this->faker->sentence,
            'document_upload' => $this->faker->word . '.pdf',
            'numerator_value' => $this->faker->randomFloat(2, 0, 100),
            'denominator_value' => $this->faker->randomFloat(2, 0, 100),
        ];
    }
}
