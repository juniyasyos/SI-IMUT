<?php

namespace App\Filament\Widgets;

use App\Models\ImutCategory;
use App\Models\ImutPenilaian;
use App\Models\LaporanImut;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class ImutCapaianWidget extends ApexChartWidget
{
    protected static ?string $chartId = 'imutCapaianWidget';
    protected static ?string $heading = 'Capaian IMUT setiap Kategori';
    protected static ?int $sort = 4;
    protected int|string|array $columnSpan = 'full';

    protected array $colorThemes = [
        'modern' => ['#6366f1', '#10b981', '#f59e0b', '#3b82f6', '#8b5cf6', '#06b6d4', '#eab308', '#ef4444', '#0ea5e9', '#22c55e'],
        // ... (tema lainnya tetap)
    ];

    protected function getOptions(): array
    {
        // Ambil data laporan dengan cache
        $laporans = Cache::remember('imut_laporans', now()->addMinutes(5), function () {
            return LaporanImut::with([
                'laporanUnitKerjas.imutPenilaians.profile.imutData.categories'
            ])->orderBy('assessment_period_start')->get();
        });

        // Buat label sumbu X berdasarkan periode assessment
        $xLabels = $laporans->map(function ($laporan) {
            $start = $laporan->assessment_period_start ? Carbon::parse($laporan->assessment_period_start) : null;
            $end = $laporan->assessment_period_end ? Carbon::parse($laporan->assessment_period_end) : null;

            if (!$start || !$end) {
                return 'Tidak diketahui';
            }

            return $start->month === $end->month
                ? $start->day . ' - ' . $end->day . ' ' . $start->translatedFormat('F Y')
                : $start->translatedFormat('j F') . ' - ' . $end->translatedFormat('j F Y');
        })->toArray();

        // Ambil semua kategori berdasarkan short_name
        $categories = ImutCategory::orderBy('short_name')->pluck('short_name')->toArray();

        // Inisialisasi nilai dummy untuk semua kategori
        $dummyValues = [];
        foreach ($categories as $shortName) {
            $dummyValues[$shortName] = array_fill(0, $laporans->count(), 0);
        }

        // Hitung nilai pencapaian berdasarkan kategori
        foreach ($laporans as $laporanIndex => $laporan) {
            foreach ($laporan->laporanUnitKerjas as $laporanUK) {
                foreach ($laporanUK->imutPenilaians as $penilaian) {
                    $profile = $penilaian->profile;
                    $imutData = $profile?->imutData;
                    $category = $imutData?->categories;

                    if (
                        !$category ||
                        !$category->short_name ||
                        $penilaian->denominator_value == 0
                    ) {
                        continue;
                    }

                    $shortName = $category->short_name;
                    $nilai = ($penilaian->numerator_value / $penilaian->denominator_value) * 100;

                    if ($profile && $nilai >= $profile->target_value) {
                        $dummyValues[$shortName][$laporanIndex]++;
                    }
                }
            }
        }

        // Buat series chart
        $colors = $this->colorThemes['modern'];
        $series = [];
        foreach ($categories as $shortName) {
            $series[] = [
                'name' => $shortName,
                'type' => 'column',
                'data' => $dummyValues[$shortName] ?? array_fill(0, count($xLabels), 0),
            ];
        }

        return [
            'chart' => [
                'type' => 'line',
                'height' => 350,
                'stacked' => false,
                'toolbar' => ['show' => true],
            ],
            'dataLabels' => ['enabled' => false],
            'colors' => array_slice($colors, 0, count($series)),
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
            'yaxis' => [
                [
                    'axisTicks' => ['show' => true],
                    'axisBorder' => ['show' => true],
                    'title' => ['text' => 'Capaian (%)'],
                ],
            ],
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
}
