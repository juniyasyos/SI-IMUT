<?php

namespace App\Filament\Resources\ImutDataResource\Widgets;

use App\Models\ImutData;
use App\Models\LaporanImut;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class ImutDataLineChart extends ApexChartWidget
{
    protected static ?string $chartId = 'imutDataLineChart';

    protected static ?string $heading = 'Grafik Penilaian IMUT per Bulan';

    protected int|string|array $columnSpan = 'full';

    public ImutData $imutData;

    protected function getFormSchema(): array
    {
        return [
            \Filament\Forms\Components\Select::make('year')
                ->label('Tahun')
                ->options([
                    '2023' => '2023',
                    '2024' => '2024',
                    '2025' => '2025',
                ])
                ->default(now()->year)
                ->reactive(),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'chart' => [
                'type' => 'line',
                'height' => 450,
                'toolbar' => [
                    'show' => true,
                    'tools' => [
                        'download' => true,
                        'selection' => true,
                        'zoom' => true,
                        'zoomin' => true,
                        'zoomout' => true,
                        'reset' => true,
                    ],
                ],
                'zoom' => ['enabled' => true],
            ],
            'stroke' => ['width' => 4],
            'markers' => ['size' => 5],
            'colors' => ['#6366f1', '#10b981', '#f59e0b', '#ef4444'],
            'series' => $this->getChartSeries(),
            'xaxis' => [
                'categories' => $this->getMonthLabels(),
                'title' => ['text' => 'Bulan'],
                'labels' => ['style' => ['fontFamily' => 'inherit']],
            ],
            'yaxis' => [
                'title' => ['text' => 'Nilai'],
                'labels' => ['style' => ['fontFamily' => 'inherit']],
            ],
            'legend' => [
                'position' => 'bottom',
                'horizontalAlign' => 'center',
            ],
            'responsive' => [
                ['breakpoint' => 768, 'options' => ['chart' => ['height' => 350]]],
            ],
        ];
    }

    protected function getMonthLabels(): array
    {
        $year = $this->filterFormData['year'] ?? now()->year;

        $laporanList = LaporanImut::select('assessment_period_start')
            ->whereYear('assessment_period_start', $year)
            ->orderBy('assessment_period_start')
            ->get();

        return $laporanList
            ->pluck('assessment_period_start')
            ->map(fn ($date) => \Carbon\Carbon::parse($date)->locale('id')->translatedFormat('M'))
            ->unique()
            ->values()
            ->toArray();
    }

    protected function getChartSeries(): array
    {
        $year = $this->filterFormData['year'] ?? now()->year;

        return [
            [
                'name' => 'Unit Gawat Darurat',
                'data' => [90, 88, 85, 87, 91, 92, 90, 93, 95, 94, 92, 90],
            ],
        ];
    }
}
