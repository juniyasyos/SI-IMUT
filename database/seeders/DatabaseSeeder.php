<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\UserSeeder;
use Database\Seeders\PositionSeeder;
use Database\Seeders\ImutDataSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(
            [
                PositionSeeder::class,
                ShieldSeeder::class,
                UserSeeder::class,
                UnitKerjaSeeder::class,
                ImutCategorySeeder::class,
                RegionTypeSeeder::class,
                ImutDataSeeder::class,
            ]
        );
    }
}