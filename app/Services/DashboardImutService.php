<?php

namespace App\Services;

use App\Models\LaporanImut;
use App\Support\CacheKey;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Service untuk mengelola data dashboard mutu (Siimut).
 */
class DashboardImutService
{
    protected LaporanImutService $laporanService;

    public function __construct(LaporanImutService $laporanService)
    {
        $this->laporanService = $laporanService;
    }

    /**
     * Mengambil ID laporan terbaru, menggunakan cache jika tersedia.
     */
    public function getLatestLaporanId(): int
    {
        try {
            return $this->laporanService->getLatestLaporanId();
        } catch (\Throwable $e) {
            Log::error('Gagal mendapatkan latest laporan ID.', ['exception' => $e]);

            return 0;
        }
    }

    /**
     * Mengambil semua data dashboard utama, dengan cache dan fallback saat data tidak ditemukan.
     */
    public function getAllDashboardData(): array
    {
        $latestLaporanId = $this->getLatestLaporanId();
        if ($latestLaporanId === 0) {
            Log::warning('Tidak ditemukan laporan terbaru.');
        }

        $cacheKey = CacheKey::dashboardSiimutAllData($latestLaporanId);

        // return Cache::remember($cacheKey, now()->addDays(7), function () use ($latestLaporanId) {
            $laporan = LaporanImut::find($latestLaporanId);

            if (! $laporan) {
                Log::info('LaporanImut dengan ID tidak ditemukan.', ['id' => $latestLaporanId]);

                return $this->getEmptyDashboardData();
            }

            try {
                $currentPeriodData = $this->laporanService->getCurrentLaporanData($laporan);
                $chartData = $this->laporanService->getChartDataForLastLaporan(6);

                return array_merge($currentPeriodData, ['chart' => $chartData]);
            } catch (\Throwable $e) {
                Log::error('Gagal mengambil data dashboard.', ['exception' => $e]);

                return $this->getEmptyDashboardData();
            }
        // });
    }

    /**
     * Mengembalikan konfigurasi statistik untuk tampilan dashboard.
     */
    public function getStatsConfig(array $data): array
    {
        return [
            [
                'key' => 'tercapai',
                'label' => 'Indikator Tercapai',
                'description' => $this->generateTrendDescription($data['chart']['tercapai'] ?? [], 'indikator'),
                'descriptionIcon' => 'heroicon-o-arrow-trending-up',
                'icon' => $this->resolveIcon($data['tercapai'] ?? 0, $data['totalIndikator'] ?? 1),
                'color' => fn ($d) => $this->resolvePercentageColor($d['tercapai'] ?? 0, $d['totalIndikator'] ?? 1),
                'chart' => 'tercapai',
                'format' => fn ($v) => "$v / ".($data['totalIndikator'] ?? 1),
            ],
            [
                'key' => 'unitMelapor',
                'label' => 'Unit Aktif Melapor',
                'description' => $this->generateTrendDescription($data['chart']['unitMelapor'] ?? [], 'unit'),
                'descriptionIcon' => 'heroicon-o-user-plus',
                'icon' => 'heroicon-o-user-group',
                'color' => fn ($d) => $this->resolvePercentageColor($d['unitMelapor'] ?? 0, $d['totalUnit'] ?? 1),
                'chart' => 'unitMelapor',
                'format' => fn ($v) => "$v / ".($data['totalUnit'] ?? 1).' Unit',
            ],
            [
                'key' => 'belumDinilai',
                'label' => 'Indikator Belum Dinilai',
                'description' => $this->generateTrendDescription($data['chart']['belumDinilai'] ?? [], 'indikator belum dinilai', true),
                'descriptionIcon' => 'heroicon-o-pencil-square',
                'icon' => 'heroicon-o-clock',
                'color' => fn ($d) => ($d['belumDinilai'] ?? 0) > 5 ? 'danger' : 'warning',
                'chart' => 'belumDinilai',
            ],
        ];
    }

    /**
     * Menghasilkan deskripsi tren sederhana dari chart.
     */
    protected function generateTrendDescription(array $chart, string $unit = '', bool $inverse = false): string
    {
        if (count($chart) < 2) {
            return 'Data belum cukup untuk analisis.';
        }

        $diff = $chart[count($chart) - 1] - $chart[count($chart) - 2];
        $abs = abs($diff);

        if ($diff === 0) {
            return match ($unit) {
                'indikator' => 'Capaian indikator stabil.',
                'unit' => 'Jumlah unit tetap.',
                'indikator belum dinilai' => 'Belum ada perubahan penilaian.',
                default => ucfirst($unit).' stabil.',
            };
        }

        if ($inverse) {
            return $diff > 0
                ? ucfirst($unit)." naik $abs — tren negatif."
                : ucfirst($unit)." turun $abs — tren positif.";
        }

        return match ($unit) {
            'indikator' => $diff > 0 ? "Indikator tercapai naik $abs." : "Indikator tercapai turun $abs.",
            'unit' => $diff > 0 ? "$abs unit mulai melapor." : "$abs unit berhenti melapor.",
            default => ucfirst($unit).($diff > 0 ? " naik $abs." : " turun $abs."),
        };
    }

    /**
     * Mengembalikan ikon berdasarkan persentase pencapaian.
     */
    protected function resolveIcon(int $value, int $total): string
    {
        $percentage = $total ? round($value / $total * 100) : 0;

        return $percentage >= 80 ? 'heroicon-o-check-circle' : 'heroicon-o-adjustments-vertical';
    }

    /**
     * Menentukan warna berdasarkan persentase pencapaian.
     */
    protected function resolvePercentageColor(int $value, int $total): string
    {
        $percentage = $total ? round($value / $total * 100) : 0;

        return match (true) {
            $percentage >= 80 => 'success',
            $percentage >= 50 => 'warning',
            default => 'danger',
        };
    }

    /**
     * Format nilai dengan formatter opsional.
     *
     * @param  mixed  $value
     * @param  callable|null  $formatter
     */
    public function formatValue($value, $formatter = null): string
    {
        return is_callable($formatter) ? $formatter($value) : (string) $value;
    }

    /**
     * Data default jika laporan tidak tersedia.
     */
    protected function getEmptyDashboardData(): array
    {
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
}
