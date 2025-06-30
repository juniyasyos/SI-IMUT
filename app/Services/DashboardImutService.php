<?php

namespace App\Services;

use App\Facades\LaporanImut as LaporanImutFacade;
use App\Models\LaporanImut;
use App\Support\CacheKey;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Service untuk mengelola data dashboard mutu (Siimut).
 */
class DashboardImutService
{
    /**
     * Mengambil ID laporan terbaru, menggunakan cache jika tersedia.
     */
    public function getLatestLaporanId(): int
    {
        try {
            return LaporanImutFacade::getLatestLaporanId();
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
            $currentPeriodData = LaporanImutFacade::getCurrentLaporanData($laporan);
            $chartData = LaporanImutFacade::getChartDataForLastLaporan(6);

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
        $payload = $data;

        return [
            [
                'key' => 'tercapai',
                'label' => 'Indikator Tercapai',
                'description' => $this->generateTrendDescription(
                    array_column($payload['chart'] ?? [], 'tercapai'),
                    'indikator'
                ),
                'descriptionIcon' => 'heroicon-o-arrow-trending-up',
                'icon' => $this->resolveIcon($payload['tercapai'] ?? 0, $payload['totalIndikator'] ?? 1),
                'color' => fn($d) => $this->resolvePercentageColor($d['tercapai'] ?? 0, $d['totalIndikator'] ?? 1),
                'chart' => 'tercapai',
                'format' => fn($v) => "$v / " . ($payload['totalIndikator'] ?? 1),
            ],
            [
                'key' => 'unitMelapor',
                'label' => 'Unit Aktif Melapor',
                'description' => $this->generateTrendDescription(
                    array_column($payload['chart'] ?? [], 'unitMelapor'),
                    'unit'
                ),
                'descriptionIcon' => 'heroicon-o-user-plus',
                'icon' => 'heroicon-o-user-group',
                'color' => fn($d) => $this->resolvePercentageColor($d['unitMelapor'] ?? 0, $d['totalUnit'] ?? 1),
                'chart' => 'unitMelapor',
                'format' => fn($v) => "$v / " . ($payload['totalUnit'] ?? 1) . ' Unit',
            ],
            [
                'key' => 'belumDinilai',
                'label' => 'Indikator Belum Dinilai',
                'description' => $this->generateTrendDescription(
                    array_column($payload['chart'] ?? [], 'belumDinilai'),
                    'indikator belum dinilai',
                    true
                ),
                'descriptionIcon' => 'heroicon-o-pencil-square',
                'icon' => 'heroicon-o-clock',
                'color' => fn($d) => ($d['belumDinilai'] ?? 0) > 5 ? 'danger' : 'warning',
                'chart' => 'belumDinilai',
            ],
        ];
    }

    /**
     * Menghasilkan deskripsi tren informatif dari data chart.
     */
    protected function generateTrendDescription(array $chart, string $unit = '', bool $inverse = false): string
    {
        $count = count($chart);

        if ($count < 2) {
            return 'Data belum cukup untuk menganalisis tren.';
        }

        $latest = $chart[$count - 1];
        $previous = $chart[$count - 2];
        $diff = $latest - $previous;
        $abs = abs($diff);

        if ($diff === 0) {
            return match ($unit) {
                'indikator' => 'Capaian indikator stabil dalam dua periode terakhir.',
                'unit' => 'Jumlah unit pelapor tidak berubah.',
                'indikator belum dinilai' => 'Tidak ada perubahan pada indikator yang belum dinilai.',
                default => ucfirst($unit) . ' stabil tanpa perubahan.',
            };
        }

        // Interpretasi terbalik (semakin kecil semakin baik)
        if ($inverse) {
            return $diff > 0
                ? ucfirst($unit) . " meningkat sebesar $abs dibandingkan periode sebelumnya — arah negatif."
                : ucfirst($unit) . " menurun sebesar $abs — ini pertanda positif.";
        }

        // Interpretasi normal (semakin besar semakin baik)
        return match ($unit) {
            'indikator' => $diff > 0
                ? "Jumlah indikator tercapai meningkat $abs dibandingkan periode sebelumnya."
                : "Jumlah indikator tercapai menurun $abs dari periode sebelumnya.",
            'unit' => $diff > 0
                ? "$abs unit baru mulai melapor dibandingkan sebelumnya."
                : "$abs unit tidak melapor dibandingkan periode sebelumnya.",
            'indikator belum dinilai' => $diff > 0
                ? "$abs indikator tambahan belum dinilai — perlu perhatian."
                : "$abs indikator telah dinilai sejak periode sebelumnya — perkembangan positif.",
            default => ucfirst($unit) . ($diff > 0
                ? " naik sebesar $abs dibanding sebelumnya."
                : " turun sebesar $abs dari sebelumnya."),
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