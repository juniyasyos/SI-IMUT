<?php

namespace App\Services;

use App\Models\ImutData;
use App\Models\ImutPenilaian;
use App\Models\LaporanImut;
use Illuminate\Support\Facades\Cache;

class DashboardSiimutService
{
    public function getLatestLaporanId(): int
    {
        return Cache::remember('latest_laporan_id', now()->addMinutes(30), function () {
            $latestLaporan = LaporanImut::where('status', LaporanImut::STATUS_PROCESS)
                ->latest('assessment_period_start')
                ->first();

            if (!$latestLaporan) {
                $latestLaporan = LaporanImut::latest('assessment_period_start')->first();
            }

            return $latestLaporan?->id ?? 0;
        });
    }

    public function getAllDashboardData(): array
    {
        $latestLaporanId = $this->getLatestLaporanId();
        $cacheKey = "dashboard_siimut_all_data_{$latestLaporanId}";

        return Cache::remember($cacheKey, now()->addDays(7), function () use ($latestLaporanId) {
            $latestLaporan = LaporanImut::find($latestLaporanId);

            if (!$latestLaporan) {
                return [
                    'totalIndikator' => 0,
                    'tercapai'       => 0,
                    'unitMelapor'    => 0,
                    'totalUnit'      => 0,
                    'belumDinilai'   => 0,
                    'chart'          => [
                        'tercapai'     => [],
                        'unitMelapor'  => [],
                        'belumDinilai' => [],
                    ],
                ];
            }

            $currentPeriodData = $this->fetchDataByLaporan($latestLaporan);
            $chartData = $this->generateAllChartData();

            return array_merge($currentPeriodData, ['chart' => $chartData]);
        });
    }

    protected function fetchDataByLaporan(LaporanImut $laporan): array
    {
        $imutPenilaians = ImutPenilaian::whereHas('laporanUnitKerja', function ($q) use ($laporan) {
            $q->where('laporan_imut_id', $laporan->id);
        })
            ->with(['profile', 'laporanUnitKerja.unitKerja'])
            ->get();

        $indikatorAktif = ImutData::where('status', true)
            ->whereHas('profiles.penilaian.laporanUnitKerja', fn($q) => $q->where('laporan_imut_id', $laporan->id))
            ->with([
                'profiles' => fn($q) => $q->latest('version')->take(1),
                'profiles.penilaian' => fn($q) => $q->whereHas('laporanUnitKerja', fn($q) => $q->where('laporan_imut_id', $laporan->id)),
            ])
            ->get();

        $relevantProfileIds = $indikatorAktif->flatMap(function ($indikator) {
            $profile = $indikator->profiles->sortByDesc('version')->first();
            return $profile ? [$profile->id] : [];
        })->unique();

        $allRelevantPenilaians = ImutPenilaian::whereIn('imut_profil_id', $relevantProfileIds)
            ->whereHas('laporanUnitKerja', fn($q) => $q->where('laporan_imut_id', $laporan->id))
            ->with('profile')
            ->get()
            ->groupBy('imut_profil_id');

        $indikatorTercapaiCount = 0;
        foreach ($indikatorAktif as $indikator) {
            $profile = $indikator->profiles->sortByDesc('version')->first();
            if (!$profile) continue;

            $penilaians = $allRelevantPenilaians->get($profile->id, collect());
            if ($penilaians->isEmpty()) continue;

            $tercapai = $penilaians->filter(function ($p) use ($profile) {
                if ($p->denominator_value == 0) return false;
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

            if ($tercapai / $penilaians->count() >= 0.8) {
                $indikatorTercapaiCount++;
            }
        }

        return [
            'totalIndikator' => $indikatorAktif->count(),
            'tercapai'       => $indikatorTercapaiCount,
            'unitMelapor'    => $imutPenilaians->pluck('laporanUnitKerja.unit_kerja_id')->unique()->count(),
            'totalUnit'      => $laporan->unitKerjas()->count(),
            'belumDinilai'   => $imutPenilaians->filter(
                fn($p) => is_null($p->numerator_value) || is_null($p->denominator_value)
            )->count(),
        ];
    }

    protected function generateAllChartData(): array
    {
        $cacheKey = "dashboard_siimut_all_chart_data";

        return Cache::remember($cacheKey, now()->addDays(7), function () {
            $laporanList = LaporanImut::orderBy('assessment_period_start', 'desc')->limit(6)->with('unitKerjas')->get();

            if ($laporanList->count() < 6) {
                $additional = LaporanImut::where('status', '!=', LaporanImut::STATUS_PROCESS)
                    ->orderBy('assessment_period_start', 'desc')
                    ->limit(6 - $laporanList->count())
                    ->with('unitKerjas')
                    ->get();
                $laporanList = $laporanList->concat($additional);
            }

            $laporanList = $laporanList->sortBy('assessment_period_start');
            $laporanIds = $laporanList->pluck('id')->toArray();

            $allPenilaians = ImutPenilaian::whereHas('laporanUnitKerja', fn($q) => $q->whereIn('laporan_imut_id', $laporanIds))
                ->with(['profile', 'laporanUnitKerja'])
                ->get()
                ->groupBy('laporanUnitKerja.laporan_imut_id');

            $indikatorAktif = ImutData::where('status', true)
                ->whereHas('profiles.penilaian.laporanUnitKerja', fn($q) => $q->whereIn('laporan_imut_id', $laporanIds))
                ->with([
                    'profiles' => fn($q) => $q->latest('version')->take(1),
                    'profiles.penilaian' => fn($q) => $q->whereHas('laporanUnitKerja', fn($q) => $q->whereIn('laporan_imut_id', $laporanIds)),
                ])
                ->get();

            $profileIds = $indikatorAktif->flatMap(function ($indikator) {
                $profile = $indikator->profiles->sortByDesc('version')->first();
                return $profile ? [$profile->id] : [];
            })->unique();

            $penilaiansByProfile = ImutPenilaian::whereIn('imut_profil_id', $profileIds)
                ->whereHas('laporanUnitKerja', fn($q) => $q->whereIn('laporan_imut_id', $laporanIds))
                ->with('profile')
                ->get()
                ->groupBy('imut_profil_id');

            $result = [
                'tercapai' => [],
                'unitMelapor' => [],
                'belumDinilai' => [],
            ];

            foreach ($laporanList as $laporan) {
                $currentPenilaians = $allPenilaians->get($laporan->id, collect());

                $indikatorTercapai = 0;
                foreach ($indikatorAktif as $indikator) {
                    $profile = $indikator->profiles->sortByDesc('version')->first();
                    if (!$profile) continue;

                    $penilaians = $penilaiansByProfile->get($profile->id, collect())
                        ->filter(fn($p) => $p->laporanUnitKerja->laporan_imut_id === $laporan->id);

                    if ($penilaians->isEmpty()) continue;

                    $tercapai = $penilaians->filter(function ($p) use ($profile) {
                        if ($p->denominator_value == 0) return false;
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

                    if ($tercapai / $penilaians->count() >= 0.8) {
                        $indikatorTercapai++;
                    }
                }

                $result['tercapai'][] = $indikatorTercapai;
                $result['unitMelapor'][] = $currentPenilaians->pluck('laporanUnitKerja.unit_kerja_id')->unique()->count();
                $result['belumDinilai'][] = $currentPenilaians->filter(
                    fn($p) =>
                    !is_null($p->numerator_value)
                        && !is_null($p->denominator_value)
                        && is_null($p->recommendations)
                )->count();
            }

            return $result;
        });
    }

    public function getStatsConfig(array $data): array
    {
        return [
            [
                'key' => 'tercapai',
                'label' => 'Indikator Tercapai',
                'description' => $this->generateTrendDescription($data['chart']['tercapai'] ?? [], 'indikator'),
                'descriptionIcon' => 'heroicon-o-arrow-trending-up',
                'icon' => $this->resolveIcon($data['tercapai'], $data['totalIndikator']),
                'color' => fn($d) => $this->resolvePercentageColor($d['tercapai'], $d['totalIndikator']),
                'chart' => 'tercapai',
                'format' => fn($v) => "$v / {$data['totalIndikator']}",
            ],
            [
                'key' => 'unitMelapor',
                'label' => 'Unit Aktif Melapor',
                'description' => $this->generateTrendDescription($data['chart']['unitMelapor'] ?? [], 'unit'),
                'descriptionIcon' => 'heroicon-o-user-plus',
                'icon' => 'heroicon-o-user-group',
                'color' => fn($d) => $this->resolvePercentageColor($d['unitMelapor'], $d['totalUnit']),
                'chart' => 'unitMelapor',
                'format' => fn($v) => "$v / {$data['totalUnit']} Unit",
            ],
            [
                'key' => 'belumDinilai',
                'label' => 'Indikator Belum Dinilai',
                'description' => $this->generateTrendDescription($data['chart']['belumDinilai'] ?? [], 'indikator belum dinilai', true),
                'descriptionIcon' => 'heroicon-o-pencil-square',
                'icon' => 'heroicon-o-clock',
                'color' => fn($d) => $d['belumDinilai'] > 5 ? 'danger' : 'warning',
                'chart' => 'belumDinilai',
            ],
        ];
    }

    protected function generateTrendDescription(array $chart, string $unit = '', bool $inverse = false): string
    {
        if (count($chart) < 2) return 'Data belum cukup untuk analisis.';

        $diff = $chart[count($chart) - 1] - $chart[count($chart) - 2];
        $abs = abs($diff);

        if ($diff === 0) {
            return match ($unit) {
                'indikator' => 'Capaian indikator stabil.',
                'unit' => 'Jumlah unit tetap.',
                'indikator belum dinilai' => 'Belum ada perubahan penilaian.',
                default => ucfirst($unit) . ' stabil.',
            };
        }

        if ($inverse) {
            return $diff > 0
                ? "$unit naik $abs — tren negatif."
                : "$unit turun $abs — tren positif.";
        }

        return match ($unit) {
            'indikator' => $diff > 0 ? "Indikator tercapai naik $abs." : "Indikator tercapai turun $abs.",
            'unit' => $diff > 0 ? "$abs unit mulai melapor." : "$abs unit berhenti melapor.",
            default => ucfirst($unit) . ($diff > 0 ? " naik $abs." : " turun $abs."),
        };
    }

    protected function resolveIcon(int $value, int $total): string
    {
        $percentage = $total ? round($value / $total * 100) : 0;
        return $percentage >= 80 ? 'heroicon-o-check-circle' : 'heroicon-o-adjustments-vertical';
    }

    protected function resolvePercentageColor(int $value, int $total): string
    {
        $percentage = $total ? round($value / $total * 100) : 0;
        return match (true) {
            $percentage >= 80 => 'success',
            $percentage >= 50 => 'warning',
            default => 'danger',
        };
    }

    public function formatValue($value, $formatter = null): string
    {
        return is_callable($formatter) ? $formatter($value) : (string) $value;
    }
}
