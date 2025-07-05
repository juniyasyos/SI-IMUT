<?php

namespace Database\Seeders;

use App\Models\ImutBenchmarking;
use App\Models\ImutCategory;
use App\Models\ImutData;
use App\Models\ImutPenilaian;
use App\Models\ImutProfile;
use App\Models\LaporanImut;
use App\Models\LaporanUnitKerja;
use App\Models\RegionType;
use App\Models\UnitKerja;
use App\Models\User;
use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
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

    protected int $totalYears = 1;

    public function run(): void
    {
        DB::transaction(function () {
            $this->init();

            $filesByCategoryShortName = [
                'INM' => 'inm.json',
                'IMP-UNIT' => 'imp-unit.json',
                'IMP-RS' => 'imp-rs.json',
                'IMIKP' => 'imp_kp.json',
            ];

            $this->createLaporanImut();

            foreach ($filesByCategoryShortName as $shortName => $filename) {
                $category = ImutCategory::where('short_name', $shortName)->first();

                if (! $category) {
                    $this->command->warn("Kategori dengan short_name \"$shortName\" tidak ditemukan. Lewati file \"$filename\".");

                    continue;
                }

                $indicators = $this->getJsonData($filename);
                if (! $indicators) {
                    continue;
                }

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
        if (! $this->category) {
            $this->command->warn('Kategori INM tidak ditemukan. Jalankan ImutCategorySeeder terlebih dahulu.');
        }

        $this->adminUserId = User::where('name', 'admin')->value('id');
        if (! $this->adminUserId) {
            $this->command->warn('User admin tidak ditemukan.');
        }

        $this->unitKerjaIds = UnitKerja::pluck('id')->toArray();
    }

    private function getJsonData(string $filename): ?array
    {
        $filePath = database_path("data/$filename");

        if (! File::exists($filePath)) {
            $this->command->warn("File \"$filename\" tidak ditemukan di folder database/data.");

            return null;
        }

        return json_decode(File::get($filePath), true);
    }

    private function createLaporanImut(): void
    {
        $totalMonths = $this->totalYears * 12;

        for ($i = 0; $i < $totalMonths; $i++) {
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
                'status' => LaporanImut::STATUS_PROCESS,
                'created_by' => $this->adminUserId ?? 1,
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
                'created_by' => $this->adminUserId ?? 1,
            ]);

            $profile = $indicator['profile'];

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
                'responsible_person',
            ];

            $missing = array_diff($requiredKeys, array_keys($profile));
            if (!empty($missing)) {
                throw new \Exception("Missing keys in profile: " . implode(', ', $missing));
            }

            $indicatorType = in_array($profile['indicator_type'], ['process', 'outcome', 'output'])
                ? $profile['indicator_type']
                : 'process';

            $analysisPeriodType = $profile['analysis_period_type'];
            $analysisPeriodValue = (int) $profile['analysis_period_value'];

            $startPeriod = now()->startOfYear();

            $endPeriod = match ($analysisPeriodType) {
                'mingguan' => $startPeriod->copy()->addWeeks($analysisPeriodValue),
                'bulanan' => $startPeriod->copy()->addMonths($analysisPeriodValue),
                default => $startPeriod->copy(),
            };

            $baseAttributes = [
                'rationale' => $profile['rationale'],
                'quality_dimension' => $profile['quality_dimension'],
                'objective' => $profile['objective'],
                'operational_definition' => $profile['operational_definition'],
                'indicator_type' => $indicatorType,
                'numerator_formula' => $profile['numerator_formula'],
                'denominator_formula' => $profile['denominator_formula'],
                'target_operator' => $profile['target_operator'] ?? '>=',
                'inclusion_criteria' => $profile['inclusion_criteria'],
                'exclusion_criteria' => $profile['exclusion_criteria'],
                'data_source' => $profile['data_source'],
                'data_collection_frequency' => $profile['data_collection_frequency'],
                'analysis_plan' => $profile['analysis_plan'],
                'analysis_period_type' => $analysisPeriodType,
                'analysis_period_value' => $analysisPeriodValue,
                'start_period' => $startPeriod->format('Y-m-d'),
                'end_period' => $endPeriod->format('Y-m-d'),
                'data_collection_method' => $profile['data_collection_method'],
                'sampling_method' => $profile['sampling_method'],
                'data_collection_tool' => $profile['data_collection_tool'],
                'responsible_person' => $profile['responsible_person'],
            ];

            // ============================================
            // >> VERSI KUARTAL DINAMIS REALISTIS <<
            // ============================================

            $initialTarget   = (float) $profile['target_value'];
            $targetOperator  = $profile['target_operator'] ?? '>=';
            $totalQuarters = $this->totalYears * 4;
            $startQuarter = now()->copy()->subYears($this->totalYears)->startOfYear()->startOfQuarter();
            $versionList     = [];

            // Buat daftar versi kuartal, misal ["2024-Q1", "2024-Q2", …]
            for ($i = 0; $i < $totalQuarters; $i++) {
                $q = ceil($startQuarter->month / 3);
                $versionList[] = 'verion-' . $startQuarter->year . '-Q' . $q;
                $startQuarter->addQuarter();
            }

            $currentTarget = $initialTarget;
            $lastImutProfile = null;

            // Fungsi bantu: hitung step acak, tapi tetap kecil (2–8%)
            $getRandomStep = fn() => rand(2, 8);

            // Loop tiap kuartal
            foreach ($versionList as $index => $versionKey) {
                // Untuk kuartal pertama, jangan lompat jauh—beri variasi kecil
                if ($index === 0) {
                    $step = $getRandomStep();
                } else {
                    $step = $getRandomStep();
                }

                // Tentukan arah perubahan sesuai operator
                if (in_array($targetOperator, ['>=', '>'])) {
                    // kalau target awal < 100, naik perlahan
                    if ($currentTarget < 100) {
                        $currentTarget = min($currentTarget + $step, 100);
                    }
                    // kalau sudah >= 100, bisa turun sedikit (misal karena fluktuasi)
                    elseif ($currentTarget > 100) {
                        $currentTarget = max($currentTarget - $getRandomStep(), 100);
                    }
                } else {
                    // operator <= atau < : target menurun ke rendah
                    if ($currentTarget > 0) {
                        $currentTarget = max($currentTarget - $step, 0);
                    }
                    // kalau sampai 0, naik sedikit fluktuasi
                    elseif ($currentTarget < 0) {
                        $currentTarget = min($currentTarget + $getRandomStep(), 0);
                    }
                }

                // Bulatkan ke integer
                $currentTarget = round($currentTarget);

                // Siapkan attributes dan simpan profile
                $attributes               = $baseAttributes;
                $attributes['target_value'] = $currentTarget;

                $createdAt = now()->copy()->subQuarters($totalQuarters - $index);

                $lastImutProfile = ImutProfile::firstOrCreate([
                    'imut_data_id' => $imutData->id,
                    'version'      => $versionKey,
                ], array_merge($attributes, [
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]));
            }

            // Tambahkan ke unit kerja & penilaian bila perlu
            if (in_array($category->short_name, ['INM', 'IMP-RS', 'IMIKP'])) {
                foreach ($this->unitKerjaIds as $unitId) {
                    $imutData->unitKerja()->syncWithoutDetaching([
                        $unitId => [
                            'assigned_by' => $this->adminUserId ?? 1,
                            'assigned_at' => now(),
                        ],
                    ]);
                }

                if ($category->is_benchmark_category) {
                    $this->createBenchmarking($imutData);
                }

                if ($lastImutProfile) {
                    $this->createPenilaian($lastImutProfile);
                }
            }
        } catch (\Throwable $e) {
            dd([
                'error' => $e->getMessage(),
                'indicator' => $indicator,
            ]);
        }
    }


    private function createBenchmarking(ImutData $imutData): void
    {
        $regionTypes = RegionType::all();

        foreach ($this->laporanList as $laporan) {
            $start = Carbon::create($laporan->assessment_period_start);
            $month = $start->month;
            $year = $start->year;
            $createdAt = $start->copy()->addDays(rand(0, 10));

            foreach ($regionTypes as $type) {
                $regionName = match ($type->type) {
                    '🌐 Nasional' => 'Indonesia',
                    '🏛️ Provinsi' => 'Jawa Timur',
                    '🏥 Rumah Sakit' => "{$this->faker->company} Hospital",
                    default => 'Unknown',
                };

                ImutBenchmarking::factory()->create([
                    'imut_data_id' => $imutData->id,
                    'region_type_id' => $type->id,
                    'region_name' => $regionName,
                    'year' => $year,
                    'month' => $month,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
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

                if (! $pivotId) {
                    $this->command->warn("Pivot laporan_unit_kerja tidak ditemukan untuk laporan ID $laporan->id dan unit ID $unitId");
                    continue;
                }

                $denominator = 0;
                $numerator = 0;

                if ($laporan->status !== 'coming_soon') {
                    $denominator = $this->faker->numberBetween(80, 120);
                    $numerator = $this->faker->numberBetween(
                        (int) ($denominator * 0.7),
                        $denominator
                    );
                }

                $createdAt = Carbon::create($laporan->assessment_period_end)->copy()->subDays(rand(0, 3));

                ImutPenilaian::create([
                    'imut_profil_id' => $imutProfile->id,
                    'laporan_unit_kerja_id' => $pivotId,
                    'analysis' => $this->faker->sentence(2),
                    'recommendations' => $this->faker->sentence(15),
                    'numerator_value' => $numerator,
                    'denominator_value' => $denominator,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            }
        }
    }
}