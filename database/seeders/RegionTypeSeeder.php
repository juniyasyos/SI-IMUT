<?php

namespace Database\Seeders;

use App\Models\RegionType;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RegionTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = ['🌐 Nasional', '🏛️ Provinsi', '🏥 Rumah Sakit'];

        foreach ($types as $type) {
            RegionType::firstOrCreate(['type' => $type]);
        }
    }
}
