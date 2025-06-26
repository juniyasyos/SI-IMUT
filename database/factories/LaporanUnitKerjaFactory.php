<?php


namespace Database\Factories;

use App\Models\LaporanImut;
use App\Models\UnitKerja;
use Illuminate\Database\Eloquent\Factories\Factory;

class LaporanUnitKerjaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'laporan_imut_id' => LaporanImut::factory(),
            'unit_kerja_id' => UnitKerja::factory(),
        ];
    }
}