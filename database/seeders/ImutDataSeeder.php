<?php

namespace Database\Seeders;

use Carbon\Carbon;
use App\Models\User;
use App\Models\ImutData;
use App\Models\UnitKerja;
use App\Models\RegionType;
use App\Models\ImutProfile;
use App\Models\LaporanImut;
use Faker\Factory as Faker;
use App\Models\ImutCategory;
use App\Models\ImutStandard;
use App\Models\ImutPenilaian;
use Illuminate\Database\Seeder;
use App\Models\ImutBenchmarking;
use App\Models\LaporanUnitKerja;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ImutDataSeeder extends Seeder
{
    protected $faker;
    protected $now;
    protected $adminUserId;
    protected $unitKerjaIds;
    protected $category;
    protected $laporanList = [];

    public function run(): void
    {
        DB::transaction(function () {
            $this->init();

            $filesByCategoryShortName = [
                'INM'     => 'inm.json',
                'IMP-UNIT' => 'imp-unit.json',
                'IMP-RS'  => 'imp-rs.json',
                'IMIKP'   => 'imp_kp.json',
            ];

            $this->createLaporanImut();

            foreach ($filesByCategoryShortName as $shortName => $filename) {
                $category = ImutCategory::where('short_name', $shortName)->first();

                if (!$category) {
                    $this->command->warn("Kategori dengan short_name \"$shortName\" tidak ditemukan. Lewati file \"$filename\".");
                    continue;
                }

                $indicators = $this->getJsonData($filename);
                if (!$indicators) continue;

                foreach ($indicators as $indicator) {
                    $this->processIndicator($indicator, $category);
                }
            }
        });
    }


    private function init(): void
    {
        $this->faker = Faker::create();
        $this->now = Carbon::now();

        $this->category = ImutCategory::where('category_name', 'Indikator Mutu Nasional (INM)')->first();
        if (!$this->category) {
            $this->command->warn('Kategori INM tidak ditemukan. Jalankan ImutCategorySeeder terlebih dahulu.');
        }

        $this->adminUserId = User::where('name', 'admin')->value('id');
        if (!$this->adminUserId) {
            $this->command->warn('User admin tidak ditemukan.');
        }

        $this->unitKerjaIds = UnitKerja::pluck('id')->toArray();
    }

    private function getJsonData(string $filename): ?array
    {
        $filePath = database_path("data/$filename");

        if (!File::exists($filePath)) {
            $this->command->warn("File \"$filename\" tidak ditemukan di folder database/data.");
            return null;
        }

        return json_decode(File::get($filePath), true);
    }

    private function createLaporanImut(): void
    {
        for ($i = 0; $i < 3; $i++) {
            $month = $this->now->copy()->subMonths($i)->month;
            $year = $this->now->copy()->subMonths($i)->year;

            $start = Carbon::create($year, $month, 1);
            $end = $start->copy()->endOfMonth();
            $assessmentStart = $end->copy()->subDays(4);

            $laporan = LaporanImut::firstOrCreate([
                'name' => "Laporan IMUT Periode $month/$year",
            ], [
                'assessment_period_start' => $assessmentStart,
                'assessment_period_end' => $end,
                'status' => LaporanImut::STATUS_COMPLETE,
                'created_by' => $this->adminUserId,
            ]);

            foreach ($this->unitKerjaIds as $unitKerjaId) {
                LaporanUnitKerja::firstOrCreate([
                    'laporan_imut_id' => $laporan->id,
                    'unit_kerja_id' => $unitKerjaId,
                ]);
            }

            $this->laporanList[] = $laporan;
        }
    }

    private function processIndicator(array $indicator, ImutCategory $category): void
    {
        try {
            $imutData = ImutData::firstOrCreate([
                'title' => $indicator['title'],
                'imut_kategori_id' => $category->id,
                'description' => $indicator['description'],
                'status' => true,
                'created_by' => $this->adminUserId
            ]);

            $profile = $indicator['profile'];

            // Cek apakah semua key penting ada
            $requiredKeys = [
                'rationale',
                'quality_dimension',
                'objective',
                'operational_definition',
                'indicator_type',
                'numerator_formula',
                'denominator_formula',
                'target_value',
                'inclusion_criteria',
                'exclusion_criteria',
                'data_source',
                'data_collection_frequency',
                'analysis_plan',
                'analysis_period_type',
                'analysis_period_value',
                'data_collection_method',
                'sampling_method',
                'data_collection_tool',
                'responsible_person'
            ];

            foreach ($requiredKeys as $key) {
                if (!array_key_exists($key, $profile)) {
                    throw new \Exception("Missing key in profile: '$key'");
                }
            }

            $indicatorType = in_array($profile['indicator_type'], ['process', 'outcome', 'output'])
                ? $profile['indicator_type']
                : 'process';

            $imutProfile = ImutProfile::firstOrCreate([
                'imut_data_id' => $imutData->id,
                'version' => 'version 1',
            ], [
                'rationale' => $profile['rationale'],
                'quality_dimension' => $profile['quality_dimension'],
                'objective' => $profile['objective'],
                'operational_definition' => $profile['operational_definition'],
                'indicator_type' => $indicatorType,
                'numerator_formula' => $profile['numerator_formula'],
                'denominator_formula' => $profile['denominator_formula'],
                'target_operator' => $profile['target_operator'] ?? '>=',
                'target_value' => $profile['target_value'],
                'inclusion_criteria' => $profile['inclusion_criteria'],
                'exclusion_criteria' => $profile['exclusion_criteria'],
                'data_source' => $profile['data_source'],
                'data_collection_frequency' => $profile['data_collection_frequency'],
                'analysis_plan' => $profile['analysis_plan'],
                'analysis_period_type' => $profile['analysis_period_type'],
                'analysis_period_value' => $profile['analysis_period_value'],
                'data_collection_method' => $profile['data_collection_method'],
                'sampling_method' => $profile['sampling_method'],
                'data_collection_tool' => $profile['data_collection_tool'],
                'responsible_person' => $profile['responsible_person'],
            ]);
        } catch (\Throwable $e) {
            dd([
                'error' => $e->getMessage(),
                'indicator' => $indicator,
            ]);
        }

        // Hanya buat laporan jika kategori adalah INM
        if ($category->short_name === 'INM') {
            $this->createStandard($imutProfile);

            // Relasi Unit Kerja
            foreach ($this->unitKerjaIds as $unitId) {
                $imutData->unitKerja()->syncWithoutDetaching([
                    $unitId => [
                        'assigned_by' => $this->adminUserId,
                        'assigned_at' => now(),
                    ]
                ]);
            }

            if ($category->is_benchmark_category) {
                $this->createBenchmarking($imutProfile);
            }

            $this->createPenilaian($imutProfile);
        }
    }


    private function createStandard(ImutProfile $imutProfile): void
    {
        for ($i = 0; $i < 3; $i++) {
            $start = Carbon::create($this->now->copy()->subMonths($i)->year, $this->now->copy()->subMonths($i)->month, 1);
            $end = $start->copy()->endOfMonth();
            $month = $start->month;
            $year = $start->year;

            $standard = ImutStandard::factory()->create([
                'imut_profile_id' => $imutProfile->id,
                'value' => $this->faker->randomFloat(2, 80, 100),
                'start_period' => $start,
                'end_period' => $end,
            ]);
        }
    }

    private function createBenchmarking(ImutProfile $imutProfile): void
    {
        $regionTypes = RegionType::all();

        for ($i = 0; $i < 3; $i++) {
            $start = Carbon::create($this->now->copy()->subMonths($i)->year, $this->now->copy()->subMonths($i)->month, 1);
            $end = $start->copy()->endOfMonth();
            $month = $start->month;
            $year = $start->year;

            foreach ($regionTypes as $type) {
                $regionName = match ($type->type) {
                    'nasional' => null,
                    'provinsi' => 'Jawa Timur',
                    'rs' => "{$this->faker->company} Hospital",
                    default => 'Unknown',
                };

                ImutBenchmarking::factory()->create([
                    'imut_profile_id' => $imutProfile->id,
                    'region_type_id' => $type->id,
                    'region_name' => $regionName,
                    'year' => $year,
                    'month' => $month,
                ]);
            }
        }
    }

    private function createPenilaian(ImutProfile $imutProfile): void
    {
        foreach ($this->laporanList as $laporan) {
            foreach ($this->unitKerjaIds as $unitId) {
                $pivotId = DB::table('laporan_unit_kerjas')
                    ->where('laporan_imut_id', $laporan->id)
                    ->where('unit_kerja_id', $unitId)
                    ->value('id');

                if (!$pivotId) {
                    $this->command->warn("Pivot laporan_unit_kerja tidak ditemukan untuk laporan ID $laporan->id dan unit ID $unitId");
                    continue;
                }

                $denominator = $this->faker->numberBetween(80, 120);
                $numerator = $this->faker->numberBetween(
                    (int) ($denominator * 0.7),
                    $denominator
                );

                $imutStandard = ImutStandard::where('imut_profile_id', $imutProfile->id)
                    ->where('start_period', '<=', $laporan->assessment_period_end)
                    ->where('end_period', '>=', $laporan->assessment_period_start)
                    ->first();

                if (!$imutStandard) {
                    $this->command->warn("Tidak ada data ImutStandard yang cocok dengan periode laporan.");
                    continue;
                }

                ImutPenilaian::create([
                    'imut_profil_id' => $imutProfile->id,
                    'laporan_unit_kerja_id' => $pivotId,
                    'imut_standar_id' => $imutStandard->id,
                    'analysis' => $this->faker->sentence(2),
                    'recommendations' => $this->faker->sentence(15),
                    'document_upload' => "{$this->faker->word}.pdf",
                    'numerator_value' => $numerator,
                    'denominator_value' => $denominator,
                ]);
            }
        }
    }
}
