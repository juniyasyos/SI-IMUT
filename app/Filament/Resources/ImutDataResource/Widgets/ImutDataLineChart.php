<?php

namespace App\Filament\Resources\ImutDataResource\Widgets;

use App\Models\ImutData;
use App\Models\LaporanImut;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\DB;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class ImutDataLineChart extends ApexChartWidget
{
    protected static ?string $chartId = 'imutDataLineChart';

    protected static ?string $heading = 'Grafik Penilaian IMUT per Periode';

    protected int|string|array $columnSpan = 'full';

    public ImutData $imutData;

    protected function getFormSchema(): array
    {
        $years = LaporanImut::selectRaw('YEAR(assessment_period_start) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year', 'year')
            ->toArray();

        return [
            Select::make('year')
                ->label('Tahun')
                ->options($years)
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
            'colors' => ['#6366f1'],
            'series' => $this->getChartSeries(),
            'xaxis' => [
                'categories' => $this->getMonthLabels(),
                'title' => ['text' => 'Periode'],
                'labels' => ['style' => ['fontFamily' => 'inherit']],
            ],
            'yaxis' => [
                'title' => ['text' => 'Nilai (%)'],
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

        return LaporanImut::whereYear('assessment_period_start', $year)
            ->orderBy('assessment_period_start')
            ->get()
            ->pluck('assessment_period_start')
            ->map(fn ($date) => \Carbon\Carbon::parse($date)->translatedFormat('F Y'))
            ->unique()
            ->values()
            ->toArray();
    }

    protected function getChartSeries(): array
    {
        $year = $this->filterFormData['year'] ?? now()->year;

        $penilaianData = DB::table('imut_penilaians')
            ->join('laporan_unit_kerjas', 'laporan_unit_kerjas.id', '=', 'imut_penilaians.laporan_unit_kerja_id')
            ->join('laporan_imuts', 'laporan_imuts.id', '=', 'laporan_unit_kerjas.laporan_imut_id')
            ->whereYear('laporan_imuts.assessment_period_start', $year)
            ->whereNull('laporan_imuts.deleted_at')
            ->selectRaw("
            DATE_FORMAT(laporan_imuts.assessment_period_start, '%Y-%m') as periode,
            SUM(imut_penilaians.numerator_value) as total_num,
            SUM(imut_penilaians.denominator_value) as total_denum
        ")
            ->groupBy('periode')
            ->orderBy('periode')
            ->get();

        $data = [];

        foreach ($penilaianData as $row) {
            $label = \Carbon\Carbon::parse($row->periode.'-01')->translatedFormat('F Y');
            $nilai = ($row->total_denum > 0) ? round(($row->total_num / $row->total_denum) * 100, 2) : 0;
            $data[$label] = $nilai;
        }

        // Label konsisten dari hasil query
        $labels = array_keys($data);
        $values = array_values($data);

        return [
            [
                'name' => 'Total Nilai IMUT',
                'data' => $values,
            ],
        ];
    }
}
