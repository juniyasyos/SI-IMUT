<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\UserSeeder;
use Database\Seeders\PositionSeeder;
use Database\Seeders\ImutDataProdSeeder;

class DatabaseProductionSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(
            [
                PositionSeeder::class,
                UserSeeder::class,
                ShieldSeeder::class,
                UnitKerjaSeeder::class,
                ImutCategorySeeder::class,
                RegionTypeSeeder::class,
                ImutDataProdSeeder::class,
            ]
        );
    }
}