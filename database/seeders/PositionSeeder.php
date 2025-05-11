<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Position;

class PositionSeeder extends Seeder
{
    public function run(): void
    {
        collect([
            ['name' => 'Pengumpul Data', 'description' => 'Bertanggung jawab mengumpulkan dan menyusun data dari unit kerja untuk keperluan analisis dan pelaporan.'],
            ['name' => 'PIC Indikator', 'description' => 'Penanggung jawab pemantauan dan evaluasi indikator mutu serta pelaporan kinerja unit kerja.'],
        ])->each(
                fn($position) =>
                Position::firstOrCreate(
                    ['name' => $position['name']],
                    ['description' => $position['description']]
                )
            );
    }
}
