<?php

namespace App\Services;

use App\Models\ImutData;
use App\Models\ImutPenilaian;
use App\Models\LaporanImut;
use App\Models\ImutProfil; // Tambahkan ini jika belum ada
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Collection; // Tambahkan ini

class DashboardSiimutService
{
    /**
     * Mengambil ID laporan terbaru, prioritaskan status PROCESS.
     */
    public function getLatestLaporanId(): int
    {
        // Gunakan cache untuk hasil ini juga, karena sering dipanggil
        return Cache::remember('latest_laporan_id', now()->addMinutes(30), function () {
            $latestLaporan = LaporanImut::where('status', LaporanImut::STATUS_PROCESS)
                ->latest('assessment_period_start')
                ->first();

            // Jika tidak ada laporan dengan status PROCESS, ambil yang terbaru secara umum
            if (!$latestLaporan) {
                $latestLaporan = LaporanImut::latest('assessment_period_start')->first();
            }

            return $latestLaporan?->id ?? 0;
        });
    }

    /**
     * Mengambil semua data yang dibutuhkan untuk dashboard.
     * Data ini akan di-cache untuk performa.
     */
    public function getAllDashboardData(): array
    {
        $latestLaporanId = $this->getLatestLaporanId();

        $cacheKey = "dashboard_siimut_all_data_{$latestLaporanId}";

        return Cache::remember($cacheKey, now()->addDays(7), function () use ($latestLaporanId) {
            // Ambil laporan terbaru secara langsung sebagai objek model
            $latestLaporan = LaporanImut::find($latestLaporanId);

            if (!$latestLaporan) {
                return [
                    'totalIndikator' => 0,
                    'tercapai'       => 0,
                    'unitMelapor'    => 0,
                    'totalUnit'      => 0,
                    'belumDinilai'   => 0,
                    'chart'          => [
                        'tercapai' => [],
                        'unitMelapor' => [],
                        'belumDinilai' => [],
                    ],
                ];
            }

            // Sekarang $latestLaporan dipastikan adalah instance App\Models\LaporanImut
            $currentPeriodData = $this->fetchDataByLaporan($latestLaporan);
            $chartData = $this->generateAllChartData();

            return array_merge($currentPeriodData, ['chart' => $chartData]);
        });
    }


    /**
     * Mengambil data metrik untuk laporan periode saat ini.
     * Diubah menjadi private karena dipanggil dari getAllDashboardData()
     * dan menerima objek $laporan secara langsung.
     */
    private function fetchCurrentPeriodData(): array
    {
        // Logika ini sudah digabungkan ke getAllDashboardData() dan fetchDataByLaporan()
        // Jadi metode ini tidak lagi diperlukan secara langsung.
        // Atau, jika Anda ingin mempertahankan struktur, pastikan $latestLaporan
        // di-load dengan eager loading yang tepat di sini.
        // Untuk optimasi, kita akan memanggil fetchDataByLaporan langsung dari getAllDashboardData
        // setelah mendapatkan $latestLaporan berdasarkan $latestLaporanId.
        // Jadi, metode ini bisa dihapus atau diubah menjadi:
        // return $this->fetchDataByLaporan(LaporanImut::find($this->getLatestLaporanId()));
        // Tapi lebih baik langsung di getAllDashboardData
        return []; // Akan dihapus setelah refactor
    }


    /**
     * Mengambil data metrik berdasarkan objek LaporanImut tertentu.
     * Menggunakan eager loading dan pemrosesan koleksi untuk mengurangi query.
     */
    protected function fetchDataByLaporan(LaporanImut $laporan): array
    {
        // 1. Eager load semua ImutPenilaian terkait dengan laporan ini,
        //    beserta relasi 'profile' dan 'laporanUnitKerja.unitKerja'
        //    (Diasumsikan LaporanUnitKerja punya relasi ke UnitKerja)
        $imutPenilaians = ImutPenilaian::whereHas('laporanUnitKerja', function ($q) use ($laporan) {
            $q->where('laporan_imut_id', $laporan->id);
        })
            ->with(['profile', 'laporanUnitKerja.unitKerja']) // Eager load profile dan unit kerja
            ->get();

        // 2. Ambil semua ImutData aktif yang terkait dengan laporan ini.
        //    Eager load profiles dan penilaian.
        //    Kunci 'profiles' dan 'penilaian' diperlukan untuk filter selanjutnya.
        $indikatorAktif = ImutData::where('status', true)
            ->whereHas('profiles.penilaian.laporanUnitKerja', function ($q) use ($laporan) {
                $q->where('laporan_imut_id', $laporan->id);
            })
            ->with(['profiles' => function ($query) {
                // Eager load penilaian hanya untuk profil terbaru
                $query->latest('version')->take(1);
            }, 'profiles.penilaian' => function ($query) use ($laporan) {
                $query->whereHas('laporanUnitKerja', fn($q) => $q->where('laporan_imut_id', $laporan->id));
            }])
            ->get();


        // 3. Kumpulkan semua imut_profil_id yang relevan dari indikator aktif
        $relevantProfileIds = $indikatorAktif->flatMap(function ($indikator) {
            // Pastikan kita hanya mengambil ID dari profil versi terbaru
            $profile = $indikator->profiles->sortByDesc('version')->first();
            return $profile ? [$profile->id] : [];
        })->unique();

        // 4. Ambil semua penilaian yang relevan dalam satu query besar
        //    dengan eager loading profile-nya
        $allRelevantPenilaians = ImutPenilaian::whereIn('imut_profil_id', $relevantProfileIds)
            ->whereHas('laporanUnitKerja', fn($q) => $q->where('laporan_imut_id', $laporan->id))
            ->with('profile') // Pastikan profile di-eager load untuk perbandingan target
            ->get()
            ->groupBy('imut_profil_id'); // Kelompokkan berdasarkan imut_profil_id untuk akses mudah


        // Menghitung Indikator Tercapai
        $indikatorTercapaiCount = 0;
        foreach ($indikatorAktif as $indikator) {
            $profile = $indikator->profiles->sortByDesc('version')->first();

            if (!$profile) {
                continue;
            }

            // Ambil penilaian untuk profil ini dari koleksi yang sudah di-load
            $penilaiansForProfile = $allRelevantPenilaians->get($profile->id, collect());

            if ($penilaiansForProfile->isEmpty()) {
                continue;
            }

            $tercapaiCountForProfile = $penilaiansForProfile->filter(function ($p) use ($profile) {
                if ($p->denominator_value == 0) {
                    return false;
                }
                $result = round(($p->numerator_value / $p->denominator_value) * 100, 2);

                return match ($profile->target_operator) {
                    '='  => $result == $profile->target_value,
                    '>=' => $result >= $profile->target_value,
                    '<=' => $result <= $profile->target_value,
                    '>'  => $result > $profile->target_value,
                    '<'  => $result < $profile->target_value,
                    default => false,
                };
            })->count();

            if ($tercapaiCountForProfile / $penilaiansForProfile->count() >= 0.8) {
                $indikatorTercapaiCount++;
            }
        }

        $totalIndikator = $indikatorAktif->count();
        $tercapai = $indikatorTercapaiCount;

        // Hitung unit melapor dari imutPenilaians yang sudah di-load
        $unitMelapor = $imutPenilaians->pluck('laporanUnitKerja.unit_kerja_id')->unique()->count();

        // totalUnit - pastikan relasi 'unitKerjas' di LaporanImut sudah di-load
        // atau gunakan count() pada builder jika belum di-load dan Anda hanya butuh count
        $totalUnit = $laporan->unitKerjas()->count(); // Jika ini memicu query N+1, pertimbangkan untuk eager load laporan->unitKerjas() di level atas

        $belumDinilai = $imutPenilaians->filter(
            fn($penilaian) => is_null($penilaian->numerator_value) || is_null($penilaian->denominator_value)
        )->count();

        return [
            'totalIndikator' => $totalIndikator,
            'tercapai'       => $tercapai,
            'unitMelapor'    => $unitMelapor,
            'totalUnit'      => $totalUnit,
            'belumDinilai'   => $belumDinilai,
        ];
    }

    /**
     * Menghasilkan data chart berdasarkan 6 laporan terakhir.
     * Menggunakan caching dan eager loading yang dioptimalkan.
     */
    protected function generateAllChartData(): array
    {
        // Kunci cache tidak perlu bergantung pada latestLaporanId di sini
        // karena kita mengambil 6 laporan terakhir, yang mungkin berbeda
        // dari "latest PROCESS" laporan.
        $cacheKey = "dashboard_siimut_all_chart_data";

        return Cache::remember($cacheKey, now()->addDays(7), function () {
            $laporanList = LaporanImut::orderBy('assessment_period_start', 'desc')
                ->limit(6)
                // Eager load unitKerjas untuk totalUnit agar tidak N+1 di loop
                ->with('unitKerjas')
                ->get();

            if ($laporanList->count() < 6) {
                $additional = LaporanImut::where('status', '!=', LaporanImut::STATUS_PROCESS)
                    ->orderBy('assessment_period_start', 'desc')
                    ->limit(6 - $laporanList->count())
                    ->with('unitKerjas') // Eager load juga untuk tambahan
                    ->get();
                $laporanList = $laporanList->concat($additional);
            }

            $laporanList = $laporanList->sortBy('assessment_period_start');

            $tercapaiArr = [];
            $unitMelaporArr = [];
            $belumDinilaiArr = [];

            // Kumpulkan semua ID laporan untuk fetch sekaligus
            $laporanIds = $laporanList->pluck('id')->toArray();

            // Ambil semua ImutPenilaian untuk semua laporan yang relevan dalam satu query
            $allPenilaiansForCharts = ImutPenilaian::whereHas('laporanUnitKerja', function ($query) use ($laporanIds) {
                $query->whereIn('laporan_imut_id', $laporanIds);
            })
                ->with(['profile', 'laporanUnitKerja'])
                ->get()
                ->groupBy('laporanUnitKerja.laporan_imut_id'); // Group by laporan_imut_id

            // Ambil semua ImutData aktif untuk semua laporan yang relevan dalam satu query
            $allIndikatorAktifForCharts = ImutData::where('status', true)
                ->whereHas('profiles.penilaian.laporanUnitKerja', function ($query) use ($laporanIds) {
                    $query->whereIn('laporan_imut_id', $laporanIds);
                })
                ->with(['profiles' => function ($query) {
                    $query->latest('version')->take(1);
                }, 'profiles.penilaian' => function ($query) use ($laporanIds) {
                    $query->whereHas('laporanUnitKerja', fn($q) => $q->whereIn('laporan_imut_id', $laporanIds));
                }])
                ->get();

            // Kumpulkan semua profil ID dari indikator aktif untuk semua laporan
            $allRelevantProfileIdsForCharts = $allIndikatorAktifForCharts->flatMap(function ($indikator) {
                $profile = $indikator->profiles->sortByDesc('version')->first();
                return $profile ? [$profile->id] : [];
            })->unique();

            // Ambil semua penilaian yang relevan untuk profil ID ini
            $allPenilaiansByProfileIdForCharts = ImutPenilaian::whereIn('imut_profil_id', $allRelevantProfileIdsForCharts)
                ->whereHas('laporanUnitKerja', fn($q) => $q->whereIn('laporan_imut_id', $laporanIds))
                ->with('profile')
                ->get()
                ->groupBy('imut_profil_id');


            foreach ($laporanList as $laporan) {
                // Ambil data spesifik untuk laporan ini dari koleksi yang sudah di-load
                $currentLaporanPenilaians = $allPenilaiansForCharts->get($laporan->id, collect());

                // Filter indikator aktif yang relevan dengan laporan ini
                $indikatorAktifForLaporan = $allIndikatorAktifForCharts->filter(function ($indikator) use ($laporan) {
                    return $indikator->profiles->first(function ($profile) use ($laporan) {
                        return $profile->penilaian->first(function ($penilaian) use ($laporan) {
                            return $penilaian->laporanUnitKerja->laporan_imut_id === $laporan->id;
                        });
                    });
                });

                $indikatorTercapaiCount = 0;
                foreach ($indikatorAktifForLaporan as $indikator) {
                    $profile = $indikator->profiles->sortByDesc('version')->first();

                    if (!$profile) {
                        continue;
                    }

                    $penilaiansForProfile = $allPenilaiansByProfileIdForCharts->get($profile->id, collect())
                        ->filter(fn($p) => $p->laporanUnitKerja->laporan_imut_id === $laporan->id); // Pastikan ini untuk laporan yang benar

                    if ($penilaiansForProfile->isEmpty()) {
                        continue;
                    }

                    $tercapaiForProfileCount = $penilaiansForProfile->filter(function ($p) use ($profile) {
                        if ($p->denominator_value == 0) {
                            return false;
                        }
                        $result = round(($p->numerator_value / $p->denominator_value) * 100, 2);

                        return match ($profile->target_operator) {
                            '='  => $result == $profile->target_value,
                            '>=' => $result >= $profile->target_value,
                            '<=' => $result <= $profile->target_value,
                            '>'  => $result > $profile->target_value,
                            '<'  => $result < $profile->target_value,
                            default => false,
                        };
                    })->count();

                    if ($tercapaiForProfileCount / $penilaiansForProfile->count() >= 0.8) {
                        $indikatorTercapaiCount++;
                    }
                }

                $tercapaiArr[] = $indikatorTercapaiCount;
                $unitMelaporArr[] = $currentLaporanPenilaians->pluck('laporanUnitKerja.unit_kerja_id')->unique()->count();
                $belumDinilaiArr[] = $currentLaporanPenilaians->filter(
                    fn($penilaian) => is_null($penilaian->numerator_value) || is_null($penilaian->denominator_value)
                )->count();
            }

            return [
                'tercapai'     => $tercapaiArr,
                'unitMelapor'  => $unitMelaporArr,
                'belumDinilai' => $belumDinilaiArr,
            ];
        });
    }

    /**
     * Mengembalikan konfigurasi statistik untuk widget.
     */
    public function getStatsConfig(array $data): array
    {
        return [
            [
                'key' => 'tercapai',
                'label' => 'Indikator Tercapai',
                'description' => $this->generateTrendDescription(
                    $data['chart']['tercapai'] ?? [],
                    'indikator'
                ),
                'descriptionIcon' => 'heroicon-o-arrow-trending-up',
                'icon' => $this->resolveIcon($data['tercapai'], $data['totalIndikator']),
                'color' => fn($d) => $this->resolvePercentageColor($d['tercapai'], $d['totalIndikator']),
                'chart' => 'tercapai',
                'format' => fn($v) => "$v / {$data['totalIndikator']}",
            ],
            [
                'key' => 'unitMelapor',
                'label' => 'Unit Aktif Melapor',
                'description' => $this->generateTrendDescription(
                    $data['chart']['unitMelapor'] ?? [],
                    'unit'
                ),
                'descriptionIcon' => 'heroicon-o-user-plus',
                'icon' => 'heroicon-o-user-group',
                'color' => fn($d) => $this->resolvePercentageColor($d['unitMelapor'], $d['totalUnit']),
                'chart' => 'unitMelapor',
                'format' => fn($v) => "$v / {$data['totalUnit']} Unit",
            ],
            [
                'key' => 'belumDinilai',
                'label' => 'Indikator Belum Dinilai',
                'description' => $this->generateTrendDescription(
                    $data['chart']['belumDinilai'] ?? [],
                    'indikator belum dinilai',
                    true // invers trend
                ),
                'descriptionIcon' => 'heroicon-o-pencil-square',
                'icon' => 'heroicon-o-clock',
                'color' => fn($d) => $d['belumDinilai'] > 5 ? 'danger' : 'warning',
                'chart' => 'belumDinilai',
            ],
        ];
    }

    /**
     * Menghasilkan deskripsi tren berdasarkan data chart.
     */
    protected function generateTrendDescription(array $chart, string $unit = '', bool $inverse = false): string
    {
        $count = count($chart);
        if ($count < 2) {
            return 'Data historis tidak mencukupi untuk analisis tren.';
        }

        $previous = $chart[$count - 2];
        $current = $chart[$count - 1];
        $diff = $current - $previous;

        if ($diff === 0) {
            return match ($unit) {
                'indikator' => 'Kinerja indikator tetap stabil dibandingkan periode sebelumnya.',
                'unit' => 'Jumlah unit yang melapor tidak berubah dari minggu lalu.',
                'indikator belum dinilai' => 'Tidak ada perubahan dalam jumlah indikator yang belum dinilai.',
                default => ucfirst($unit) . ' stabil dari periode sebelumnya.',
            };
        }

        $absDiff = abs($diff);

        if ($inverse) {
            return $diff > 0
                ? "Jumlah $unit meningkat sebesar $absDiff — perlu perhatian karena ini menunjukkan penurunan kualitas."
                : "Jumlah $unit berhasil ditekan sebanyak $absDiff — tren positif yang menggembirakan.";
        }

        return match ($unit) {
            'indikator' => $diff > 0
                ? "Jumlah indikator tercapai meningkat sebanyak $absDiff — menunjukkan perbaikan kinerja."
                : "Penurunan $absDiff indikator tercapai — evaluasi perlu dilakukan.",
            'unit' => $diff > 0
                ? "$absDiff unit tambahan aktif melaporkan data — partisipasi meningkat."
                : "$absDiff unit tidak lagi melaporkan — monitoring dan pembinaan diperlukan.",
            default => ucfirst($unit) . ' ' . ($diff > 0 ? 'naik' : 'turun') . " sebesar $absDiff dari periode sebelumnya.",
        };
    }

    /**
     * Menentukan ikon berdasarkan persentase capaian.
     */
    protected function resolveIcon(int $value, int $total): string
    {
        $percentage = $total ? round($value / $total * 100) : 0;
        return $percentage >= 80 ? 'heroicon-o-check-circle' : 'heroicon-o-adjustments-vertical';
    }

    /**
     * Menentukan warna berdasarkan persentase capaian.
     */
    protected function resolvePercentageColor(int $value, int $total): string
    {
        $percentage = $total ? round($value / $total * 100) : 0;
        return match (true) {
            $percentage >= 80 => 'success',
            $percentage >= 50 => 'warning',
            default            => 'danger',
        };
    }

    /**
     * Memformat nilai berdasarkan formatter yang diberikan.
     */
    public function formatValue($value, $formatter = null): string
    {
        return is_callable($formatter) ? $formatter($value) : (string) $value;
    }
}
