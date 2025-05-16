<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ImutCategory;

class ImutCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'category_name' => 'Indikator Mutu Nasional (INM)',
                'short_name' => 'INM',
                'scope' => 'global',
                'description' => 'Indikator yang ditetapkan secara nasional untuk menilai mutu pelayanan di rumah sakit seluruh Indonesia.',
            ],
            [
                'category_name' => 'Indikator Mutu Prioritas Rumah Sakit (IMP-RS)',
                'short_name' => 'IMP-RS',
                'scope' => 'internal',
                'description' => 'Indikator prioritas yang dipilih oleh manajemen rumah sakit berdasarkan isu strategis internal.',
            ],
            [
                'category_name' => 'Indikator Mutu Prioritas Unit (IMP-UNIT)',
                'short_name' => 'IMP-UNIT',
                'scope' => 'unit',
                'description' => 'Indikator prioritas pada tingkat unit pelayanan untuk meningkatkan mutu spesifik di masing-masing unit.',
            ],
            [
                'category_name' => 'Indikator Mutu Insiden Keselamatan Pasien',
                'short_name' => 'IMIKP',
                'scope' => 'internal',
                'description' => 'Indikator yang digunakan untuk memantau dan mencegah insiden keselamatan pasien di fasilitas pelayanan kesehatan.',
            ],
        ];

        foreach ($categories as $data) {
            ImutCategory::factory()->create($data);
        }
    }
}
