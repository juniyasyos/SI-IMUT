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
    public function run(): void
    {
        DB::transaction(function () {
            $faker = Faker::create();
            $now = Carbon::now();
            $regionTypes = ['nasional', 'provinsi', 'rs'];

            $category = ImutCategory::where('category_name', 'Indikator Mutu Nasional (INM)')->first();
            if (!$category) {
                $this->command->warn('Kategori INM tidak ditemukan. Jalankan ImutCategorySeeder terlebih dahulu.');
                return;
            }

            $filePath = database_path('data/inm.json');
            if (!File::exists($filePath)) {
                $this->command->warn('File "inm.json" tidak ditemukan di folder database/data.');
                return;
            }

            $data = json_decode(File::get($filePath), true);
            $unitKerjaIds = UnitKerja::pluck('id')->toArray();
            $adminUserId = User::where('name', 'admin')->value('id');

            if (!$adminUserId) {
                $this->command->warn('User admin tidak ditemukan.');
                return;
            }

            // Buat laporan berdasarkan bulan mundur
            $laporanList = [];
            for ($i = 0; $i < 3; $i++) { // Membuat laporan untuk bulan ini, bulan lalu, 2 bulan lalu
                $month = $now->copy()->subMonths($i)->month;
                $year = $now->copy()->subMonths($i)->year;

                $start = Carbon::create($year, $month, 1);
                $end = $start->copy()->endOfMonth();
                $assessmentStart = $end->copy()->subDays(4);

                $laporan = LaporanImut::firstOrCreate([
                    'name' => "Laporan IMUT Periode $month/$year",
                ], [
                    'assessment_period_start' => $assessmentStart,
                    'assessment_period_end' => $end,
                    'status' => LaporanImut::STATUS_COMPLETE,
                    'created_by' => User::where('name', 'admin')->first()
                ]);

                foreach ($unitKerjaIds as $unitKerjaId) {
                    LaporanUnitKerja::firstOrCreate([
                        'laporan_imut_id' => $laporan->id,
                        'unit_kerja_id' => $unitKerjaId,
                    ]);
                }

                $laporanList[] = $laporan;
            }

            // Proses untuk setiap indikator
            foreach ($data as $indicator) {
                $imutData = ImutData::firstOrCreate([
                    'title' => $indicator['title'],
                    'imut_kategori_id' => $category->id,
                    'description' => $indicator['description'],
                    'status' => true
                ]);

                $profile = $indicator['profile'];
                $indicatorType = in_array($profile['indicator_type'], ['process', 'outcome', 'output']) ? $profile['indicator_type'] : 'process';

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

                // Hubungkan ke unit kerja lewat tabel pivot imut_data_unit_kerja
                foreach ($unitKerjaIds as $unitId) {
                    $imutData->unitKerja()->syncWithoutDetaching([
                        $unitId => [
                            'assigned_by' => $adminUserId,
                            'assigned_at' => now(),
                        ]
                    ]);
                }

                // Perbaikan untuk ImutStandard
                for ($i = 0; $i < 3; $i++) {
                    $start = Carbon::create($now->copy()->subMonths($i)->year, $now->copy()->subMonths($i)->month, 1);
                    $end = $start->copy()->endOfMonth();

                    ImutStandard::factory()->create([
                        'imut_profile_id' => $imutProfile->id,
                        'value' => $faker->randomFloat(2, 80, 100),
                        'start_period' => $start,
                        'end_period' => $end,
                    ]);

                    $regionTypes = RegionType::all();

                    foreach ($regionTypes as $type) {
                        $regionName = match ($type->type) {
                            'nasional' => null,
                            'provinsi' => 'Jawa Timur',
                            'rs' => "{$faker->company} Hospital",
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

                // Generate Penilaian
                foreach ($laporanList as $laporan) {
                    foreach ($unitKerjaIds as $unitId) {
                        $pivotId = DB::table('laporan_unit_kerjas')
                            ->where('laporan_imut_id', $laporan->id)
                            ->where('unit_kerja_id', $unitId)
                            ->value('id');

                        if (!$pivotId) {
                            $this->command->warn("Pivot laporan_unit_kerja tidak ditemukan untuk laporan ID $laporan->id dan unit ID $unitId");
                            continue;
                        }

                        $denominator = $faker->numberBetween(80, 120);
                        $numerator = $faker->numberBetween(
                            (int) ($denominator * 0.7),
                            $denominator
                        );

                        // Menyelaraskan data standar berdasarkan periode yang sesuai
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
                            'analysis' => $faker->sentence(2),
                            'recommendations' => $faker->sentence(15),
                            'document_upload' => "{$faker->word}.pdf",
                            'numerator_value' => $numerator,
                            'denominator_value' => $denominator,
                        ]);
                    }
                }
            }
        });
    }
}
