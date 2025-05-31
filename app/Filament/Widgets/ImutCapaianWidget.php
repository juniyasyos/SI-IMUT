<?php

namespace App\Filament\Widgets;

use App\Models\ImutCategory;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class ImutCapaianWidget extends ApexChartWidget
{
    protected static ?string $chartId = 'imutCapaianWidget';
    protected static ?string $heading = 'Capaian IMUT setiap Kategori';
    protected static ?int $sort = 4;
    protected int|string|array $columnSpan = 'full';

    protected array $colorThemes = [
        'modern' => [
            '#6366f1', // indigo-500
            '#10b981', // green-500
            '#f59e0b', // amber-500
            '#3b82f6', // blue-500
            '#8b5cf6', // violet-500
            '#06b6d4', // cyan-500
            '#eab308', // yellow-500
            '#ef4444', // red-500
            '#0ea5e9', // sky-500
            '#22c55e', // emerald-500
        ],
        'vibrant' => [
            '#ef4444', // red-500
            '#f97316', // orange-500
            '#f59e0b', // amber-500
            '#84cc16', // lime-500
            '#10b981', // green-500
            '#14b8a6', // teal-500
            '#06b6d4', // cyan-500
            '#3b82f6', // blue-500
            '#8b5cf6', // violet-500
            '#d946ef', // fuchsia-500
        ],
        'cool' => [
            '#0ea5e9', // sky-500
            '#06b6d4', // cyan-500
            '#22d3ee', // cyan-300
            '#38bdf8', // sky-400
            '#3b82f6', // blue-500
            '#6366f1', // indigo-500
            '#8b5cf6', // violet-500
            '#a78bfa', // violet-400
            '#818cf8', // indigo-400
            '#7dd3fc', // sky-300
        ],
        'warm' => [
            '#f59e0b', // amber-500
            '#f97316', // orange-500
            '#fb923c', // orange-400
            '#facc15', // yellow-400
            '#eab308', // yellow-500
            '#fcd34d', // amber-300
            '#fbbf24', // amber-400
            '#f87171', // red-400
            '#ef4444', // red-500
            '#b91c1c', // red-700
        ],
        'pastel' => [
            '#fde68a', // yellow-300
            '#a5f3fc', // cyan-200
            '#c4b5fd', // violet-300
            '#fbcfe8', // pink-200
            '#bbf7d0', // green-200
            '#fcd34d', // amber-300
            '#fdba74', // orange-300
            '#fca5a5', // red-300
            '#93c5fd', // blue-300
            '#a78bfa', // violet-400
        ],
        'earth' => [
            '#78350f', // amber-900
            '#92400e', // orange-800
            '#a16207', // yellow-800
            '#15803d', // green-800
            '#166534', // emerald-800
            '#1e3a8a', // blue-900
            '#4c1d95', // violet-900
            '#7f1d1d', // red-900
            '#365314', // lime-900
            '#0f172a', // slate-900
        ],
    ];

    protected function getOptions(): array
    {
        $years = ['2021', '2022', '2023', '2024'];
        $colors = $this->colorThemes['modern'];

        // Ambil short_name dari kategori
        $categories = ImutCategory::orderBy('short_name')->pluck('short_name')->toArray();

        // Jika kosong, gunakan dummy kategori
        if (empty($categories)) {
            $categories = ['IGD', 'OK', 'VK'];
        }

        // Dummy data tetap per kategori
        $dummyValues = [
            'IGD' => [78, 82, 85, 88],
            'OK'  => [70, 75, 80, 84],
            'VK'  => [65, 72, 78, 80],
        ];

        // Bangun series untuk setiap kategori
        $series = [];
        foreach ($categories as $index => $shortName) {
            $series[] = [
                'name' => $shortName,
                'type' => 'column',
                'data' => $dummyValues[$shortName] ?? [60, 65, 70, 75],
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
                'categories' => $years,
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
