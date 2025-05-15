<?php

namespace Database\Factories;

use App\Models\ImutBenchmarking;
use App\Models\ImutProfile;
use App\Models\RegionType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ImutBenchmarking>
 */
class ImutBenchmarkingFactory extends Factory
{
    protected $model = ImutBenchmarking::class;

    public function definition(): array
    {
        // Ambil salah satu RegionType secara acak
        $regionType = RegionType::inRandomOrder()->first();

        // Fallback jika belum ada RegionType
        if (!$regionType) {
            $regionType = RegionType::factory()->create(['name' => 'nasional']);
        }

        return [
            'imut_profile_id' => ImutProfile::inRandomOrder()->value('id') ?? ImutProfile::factory(),
            'region_type_id' => $regionType->id,
            // 'region_name' => $this->generateRegionName($regionType->name),
            'year' => $this->faker->numberBetween(2022, 2025),
            'month' => $this->faker->numberBetween(1, 12),
            'benchmark_value' => $this->faker->randomFloat(2, 70, 100),
        ];
    }

    // /**
    //  * Generate region name based on region type name.
    //  *
    //  * @param string $type
    //  * @return string|null
    //  */
    // private function generateRegionName(string $type)
    // {
    //     return match ($type) {
    //         'nasional' => null,
    //         'provinsi' => $this->faker->randomElement(['Jawa Barat', 'Jawa Timur', 'Sumatera Utara']),
    //         'rs' => $this->faker->company . ' Hospital',
    //         default => 'Unknown Region',
    //     };
    // }
}
