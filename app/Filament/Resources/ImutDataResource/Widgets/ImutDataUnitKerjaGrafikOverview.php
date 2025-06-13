<?php

namespace App\Filament\Resources\ImutDataResource\Widgets;

use App\Models\ImutBenchmarking;
use App\Models\LaporanImut;
use App\Models\RegionType;
use App\Models\UnitKerja;
use App\Support\CacheKey as SupportCacheKey;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class ImutDataUnitKerjaGrafikOverview extends ApexChartWidget
{
    protected static ?string $chartId = 'imutDataUnitKerjaGrafikOverview';

    protected function getHeading(): ?string
    {
        $unitKerjaId = $this->filterFormData['unit_kerja_id'] ?? null;

        $unitName = UnitKerja::find($unitKerjaId)?->unit_name;

        return 'Grafik Penilaian IMUT Data'.($unitName ? ': '.$unitName : '');
    }

    protected int|string|array $columnSpan = 'full';

    public \App\Models\ImutData $imutData;

    public \App\Models\UnitKerja $unitKerja;

    protected function getFormSchema(): array
    {
        $years = LaporanImut::query()
            ->selectRaw('YEAR(assessment_period_start) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year', 'year')
            ->toArray();

        $regionTypes = RegionType::pluck('type', 'id')->toArray();

        $unitKerjaOptions = UnitKerja::pluck('unit_name', 'id')->toArray();

        return [
            Select::make('year')
                ->label('Tahun')
                ->options($years)
                ->required()
                ->default(now()->year)
                ->reactive(),

            Select::make('unit_kerja_id')
                ->label('Unit Kerja')
                ->options($unitKerjaOptions)
                ->default($this->unitKerja->id)
                ->searchable()
                ->required()
                ->reactive(),

            Select::make('region_type_id')
                ->label('Benchmarking Region')
                ->options($regionTypes)
                ->multiple()
                ->default(null)
                ->searchable()
                ->reactive(),

            Checkbox::make('show_benchmarking')
                ->label('Tampilkan Benchmarking')
                ->default(true)
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
            'stroke' => [
                'width' => [8, 4, 2, 2, 2],
                'dashArray' => [0, 5, 8, 3, 6],
            ],
            'markers' => ['size' => 3],
            'colors' => ['#3b82f6', '#f59e0b', '#10b981', '#ef4444', '#6366f1', '#14b8a6'],
            'series' => $this->getChartSeries(),
            'xaxis' => [
                'categories' => $this->getMonthLabels(),
                'title' => ['text' => 'Periode'],
                'labels' => ['style' => ['fontFamily' => 'inherit']],
            ],
            'yaxis' => [
                'min' => 0,
                'max' => 100,
                'title' => ['text' => 'Nilai (%)'],
            ],
            'legend' => [
                'position' => 'bottom',
                'horizontalAlign' => 'left',
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
            ->pluck('assessment_period_start')
            ->map(fn ($date) => \Carbon\Carbon::parse($date)->translatedFormat('F Y'))
            ->unique()
            ->values()
            ->toArray();
    }

    protected function getChartSeries(): array
    {
        $year = $this->filterFormData['year'] ?? now()->year;
        $unitKerjaId = $this->filterFormData['unit_kerja_id'] ?? null;
        $regionTypeId = $this->filterFormData['region_type_id'] ?? null;
        $showBenchmarking = $this->filterFormData['show_benchmarking'] ?? true;
        $imutDataId = $this->imutData->id;

        $penilaianData = Cache::remember(
            SupportCacheKey::imutPenilaianImutDataUnitKerja($imutDataId, $year, $unitKerjaId),
            now()->addMinutes(30),
            function () use ($imutDataId, $year, $unitKerjaId) {
                return DB::table('imut_penilaians')
                    ->join('laporan_unit_kerjas', 'laporan_unit_kerjas.id', '=', 'imut_penilaians.laporan_unit_kerja_id')
                    ->join('laporan_imuts', 'laporan_imuts.id', '=', 'laporan_unit_kerjas.laporan_imut_id')
                    ->join('imut_profil', 'imut_profil.id', '=', 'imut_penilaians.imut_profil_id')
                    ->join('imut_data', 'imut_data.id', '=', 'imut_profil.imut_data_id')
                    ->where('imut_data.id', $imutDataId)
                    ->whereYear('laporan_imuts.assessment_period_start', $year)
                    ->when($unitKerjaId, function ($query) use ($unitKerjaId) {
                        $query->where('laporan_unit_kerjas.unit_kerja_id', $unitKerjaId);
                    })
                    ->whereNull('laporan_imuts.deleted_at')
                    ->selectRaw("
                        DATE_FORMAT(laporan_imuts.assessment_period_start, '%Y-%m') as periode,
                        SUM(imut_penilaians.numerator_value) as total_num,
                        SUM(imut_penilaians.denominator_value) as total_denum,
                        AVG(imut_profil.target_value) as target
                    ")
                    ->groupBy('periode')
                    ->orderBy('periode')
                    ->get();
            }
        );

        $dataNilai = [];
        $dataTarget = [];

        foreach ($penilaianData as $row) {
            $label = \Carbon\Carbon::parse($row->periode.'-01')->translatedFormat('F Y');
            $nilai = ($row->total_denum > 0) ? round(($row->total_num / $row->total_denum) * 100, 2) : 0;
            $target = round($row->target, 2);

            $dataNilai[$label] = $nilai;
            $dataTarget[$label] = $target;
        }

        $labels = array_keys($dataNilai);

        $series = [
            [
                'name' => 'Nilai IMUT',
                'data' => array_map(fn ($l) => $dataNilai[$l] ?? 0, $labels),
            ],
            [
                'name' => 'Target Standar',
                'data' => array_map(fn ($l) => $dataTarget[$l] ?? 0, $labels),
            ],
        ];

        if ($showBenchmarking) {
            $benchmarkKey = SupportCacheKey::imutBenchmarking($year, $regionTypeId);
            $benchmarking = Cache::remember(
                $benchmarkKey,
                now()->addMinutes(30),
                function () use ($year, $regionTypeId) {
                    return ImutBenchmarking::query()
                        ->with('regionType:id,type')
                        ->select('year', 'month', 'benchmark_value', 'region_type_id')
                        ->where('year', $year)
                        ->when($regionTypeId, fn ($q) => $q->whereIn('region_type_id', $regionTypeId))
                        ->get();
                }
            );

            $benchmarkGrouped = $benchmarking->groupBy(fn ($item) => sprintf('%04d-%02d', $item->year, $item->month));

            $regionSeries = [];

            foreach ($benchmarkGrouped as $periodeKey => $items) {
                $label = \Carbon\Carbon::createFromFormat('Y-m', $periodeKey)->translatedFormat('F Y');

                foreach ($items as $item) {
                    $type = $item->regionType->type ?? 'Unknown';
                    $regionSeries[$type][$label] = round($item->benchmark_value, 2);
                }
            }

            foreach ($regionSeries as $type => $data) {
                if (collect($labels)->contains(fn ($l) => isset($data[$l]))) {
                    $series[] = [
                        'name' => $type,
                        'data' => array_map(fn ($l) => $data[$l] ?? null, $labels),
                    ];
                }
            }
        }

        return $series;
    }
}
