<?php

namespace App\Services;

use App\Models\LaporanImut;
use Illuminate\Support\Facades\Cache;

class DashboardImutService
{
    protected LaporanImutService $laporanService;

    public function __construct(LaporanImutService $laporanService)
    {
        $this->laporanService = $laporanService;
    }

    /**
     * Ambil latest laporan id via laporanService dengan cache.
     */
    public function getLatestLaporanId(): int
    {
        // Bisa langsung pakai method di laporanService yang sudah handle cache
        return $this->laporanService->getLatestLaporanId();
    }

    /**
     * Ambil semua data dashboard dengan cache, gunakan service laporan untuk data utama.
     */
    public function getAllDashboardData(): array
    {
        $latestLaporanId = $this->getLatestLaporanId();
        $cacheKey = \App\Support\CacheKey::dashboardSiimutAllData($latestLaporanId);

        return Cache::remember($cacheKey, now()->addDays(7), function () use ($latestLaporanId) {
            $laporan = LaporanImut::find($latestLaporanId)->first();

            if (!$laporan) {
                return [
                    'totalIndikator' => 0,
                    'tercapai' => 0,
                    'unitMelapor' => 0,
                    'totalUnit' => 0,
                    'belumDinilai' => 0,
                    'chart' => [
                        'tercapai' => [],
                        'unitMelapor' => [],
                        'belumDinilai' => [],
                    ],
                ];
            }

            // Panggil service laporan untuk data ringkas periode terakhir
            $currentPeriodData = $this->laporanService->getCurrentLaporanData($laporan);

            // Panggil service laporan untuk data chart beberapa periode terakhir
            $chartData = $this->laporanService->getChartDataForLastLaporan(6);

            return array_merge($currentPeriodData, ['chart' => $chartData]);
        });
    }

    /**
     * Konfigurasi tampilan statistik untuk dashboard.
     */
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
