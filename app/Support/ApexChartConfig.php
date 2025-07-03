<?php

namespace App\Support;

class ApexChartConfig
{
    public static function defaultOptions(
        array $series,
        array $xLabels,
        string $xLableTitle = 'Periode',
        string $yLableTitle = 'Capaian (%)',
        int $yAxisMin = null,
        int $yAxisMax = null
    ): array {
        $maxValue = collect($series)
            ->pluck('data')
            ->flatten()
            ->max();

        $yAxisMax = $yAxisMax ?? ($maxValue > 100 ? ceil($maxValue) : 100);
        $yAxisMin = $yAxisMin ?? 0;

        return [
            'chart' => [
                'type' => 'line',
                'height' => 450,
                'stacked' => false,
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
                'fontFamily' => 'inherit',
            ],
            'plotOptions' => [
                'bar' => [
                    'columnWidth' => '40%',
                    'borderRadius' => 4,
                ],
            ],
            'dataLabels' => ['enabled' => false],
            'series' => $series,
            'stroke' => [
                'width' => array_fill(0, count($series), 3),
                'curve' => 'smooth',
            ],
            'xaxis' => [
                'categories' => $xLabels,
                'title' => ['text' => $xLableTitle],
                'labels' => [
                    'rotate' => -45,
                    'style' => [
                        'fontSize' => '12px',
                        'fontWeight' => 500,
                    ],
                ],
                'axisBorder' => ['show' => true],
                'axisTicks' => ['show' => true],
            ],
            'yaxis' => [[
                'min' => $yAxisMin,
                'max' => $yAxisMax,
                'axisTicks' => ['show' => true],
                'axisBorder' => ['show' => true],
                'title' => ['text' => $yLableTitle],
            ]],
            'markers' => [
                'size' => 5,
                'hover' => ['sizeOffset' => 3],
            ],
            'tooltip' => [
                'shared' => true,
                'intersect' => false,
                'x' => ['show' => true],
            ],
            'legend' => [
                'horizontalAlign' => 'center',
            ],
        ];
    }

    public static function noDataOptions(string $message = 'Belum ada data tersedia.'): array
    {
        return [
            'chart' => [
                'type' => 'line',
                'height' => 450,
            ],
            'series' => [],
            'xaxis' => ['categories' => []],
            'noData' => [
                'text' => $message,
                'align' => 'center',
                'verticalAlign' => 'middle',
                'style' => [
                    'color' => '#999',
                    'fontSize' => '16px',
                ],
            ],
        ];
    }
}