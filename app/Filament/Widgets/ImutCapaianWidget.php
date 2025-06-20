<?php

namespace App\Filament\Widgets;

use App\Models\ImutCategory;
use App\Models\LaporanImut;
use App\Support\CacheKey;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

/**
 * Widget untuk menampilkan capaian indikator mutu berdasarkan kategori per periode.
 */
class ImutCapaianWidget extends ApexChartWidget
{
    protected static ?string $chartId = 'imutCapaianWidget';

    protected static ?string $heading = 'Capaian IMUT setiap Kategori';

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    /**
     * Daftar warna tema modern untuk series chart.
     *
     * @var array<string, array<int, string>>
     */
    protected array $colorThemes = [
        'modern' => ['#6366f1', '#10b981', '#f59e0b', '#3b82f6', '#8b5cf6', '#06b6d4', '#eab308', '#ef4444', '#0ea5e9', '#22c55e'],
    ];

    /**
     * Cek apakah pengguna bisa melihat widget ini.
     */
    public static function canView(): bool
    {
        return Auth::user()?->can('widget_ImutCapaianWidget');
    }

    /**
     * Konfigurasi chart.
     */
    protected function getOptions(): array
    {
        $laporans = $this->getCachedLaporans();

        // 🔒 Early return jika tidak ada data
        if ($laporans->isEmpty()) {
            return [
                'chart' => ['type' => 'line'],
                'series' => [],
                'xaxis' => ['categories' => []],
                'noData' => [
                    'text' => 'Belum ada laporan tersedia.',
                    'align' => 'center',
                    'verticalAlign' => 'middle',
                    'style' => [
                        'color' => '#999',
                        'fontSize' => '16px',
                    ],
                ],
            ];
        }

        // Lanjutkan jika ada data
        $xLabels = $this->generateXLabels($laporans);
        $categories = ImutCategory::orderBy('short_name')->pluck('short_name')->toArray();
        $dataPerKategori = $this->calculateAchievementData($laporans, $categories);

        $series = collect($categories)->map(fn ($shortName) => [
            'name' => $shortName,
            'type' => 'column',
            'data' => $dataPerKategori[$shortName] ?? array_fill(0, count($xLabels), 0),
        ])->values()->toArray();

        return [
            'chart' => [
                'type' => 'line',
                'height' => 350,
                'stacked' => false,
                'toolbar' => ['show' => true],
            ],
            'dataLabels' => ['enabled' => false],
            'colors' => array_slice($this->colorThemes['modern'], 0, count($series)),
            'series' => $series,
            'stroke' => [
                'width' => array_fill(0, count($series), 2),
                'curve' => 'smooth',
            ],
            'plotOptions' => [
                'bar' => [
                    'columnWidth' => '40%',
                    'borderRadius' => 3,
                ],
            ],
            'xaxis' => [
                'categories' => $xLabels,
                'labels' => ['style' => ['fontFamily' => 'inherit']],
            ],
            'yaxis' => [[
                'axisTicks' => ['show' => true],
                'axisBorder' => ['show' => true],
                'title' => ['text' => 'Capaian (%)'],
            ]],
            'tooltip' => [
                'shared' => true,
                'intersect' => false,
                'x' => ['show' => true],
            ],
            'legend' => [
                'horizontalAlign' => 'left',
                'offsetX' => 40,
            ],
        ];
    }

    /**
     * Mengambil data laporan dengan cache selama 5 menit.
     */
    protected function getCachedLaporans()
    {
        return Cache::remember(CacheKey::imutLaporans(), now()->addMinutes(5), fn () => LaporanImut::with([
            'laporanUnitKerjas.imutPenilaians.profile.imutData.categories',
        ])->orderBy('assessment_period_start')->get()
        );
    }

    /**
     * Menghasilkan label sumbu X berdasarkan periode laporan.
     */
    protected function generateXLabels($laporans): array
    {
        return $laporans->map(function ($laporan) {
            $start = $laporan->assessment_period_start ? Carbon::parse($laporan->assessment_period_start) : null;
            $end = $laporan->assessment_period_end ? Carbon::parse($laporan->assessment_period_end) : null;

            if (! $start || ! $end) {
                return 'Tidak diketahui';
            }

            return $start->month === $end->month
                ? $start->day.' - '.$end->day.' '.$start->translatedFormat('F Y')
                : $start->translatedFormat('j F').' - '.$end->translatedFormat('j F Y');
        })->toArray();
    }

    /**
     * Menghitung capaian indikator per kategori per laporan.
     *
     * @param  \Illuminate\Support\Collection  $laporans
     * @param  array<int, string>  $categories
     * @return array<string, array<int, int>>
     */
    protected function calculateAchievementData($laporans, array $categories): array
    {
        $data = [];

        // Inisialisasi array
        foreach ($categories as $shortName) {
            $data[$shortName] = array_fill(0, $laporans->count(), 0);
        }

        // Iterasi laporan dan hitung pencapaian
        foreach ($laporans as $i => $laporan) {
            foreach ($laporan->laporanUnitKerjas as $unitKerja) {
                foreach ($unitKerja->imutPenilaians as $penilaian) {
                    $profile = $penilaian->profile;
                    $category = $profile?->imutData?->categories;

                    if (! $category || ! $category->short_name || $penilaian->denominator_value == 0) {
                        continue;
                    }

                    $shortName = $category->short_name;
                    $nilai = ($penilaian->numerator_value / $penilaian->denominator_value) * 100;

                    if ($nilai >= $profile->target_value) {
                        $data[$shortName][$i]++;
                    }
                }
            }
        }

        return $data;
    }
}