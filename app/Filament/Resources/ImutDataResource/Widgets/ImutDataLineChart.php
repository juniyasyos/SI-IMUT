<?php

namespace App\Filament\Resources\ImutDataResource\Widgets;

use App\Models\ImutBenchmarking;
use App\Models\ImutData;
use App\Models\LaporanImut;
use App\Models\RegionType;
use App\Support\ApexChartConfig;
use App\Support\CacheKey;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class ImutDataLineChart extends ApexChartWidget
{
    protected static ?string $chartId = 'imutDataLineChart';

    protected static ?string $heading = 'Grafik Penilaian IMUT Data';

    protected int|string|array $columnSpan = 'full';

    protected static MaxWidth|string $filterFormWidth = MaxWidth::ExtraLarge;

    protected static bool $isLazy = false;

    public ImutData $imutData;

    protected function hasFilterForm(): bool
    {
        return true;
    }

    protected function getFormSchema(): array
    {
        $years = LaporanImut::selectRaw('YEAR(assessment_period_start) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year', 'year')
            ->toArray();

        $regionTypes = RegionType::pluck('type', 'id')->toArray();

        $is_benchmarking = $this->imutData->categories->is_benchmark_category;

        return [
            Section::make('Filter Data')
                ->schema([
                    Select::make('year')->label('Tahun')->options($years)->default(now()->year)->reactive(),
                    Select::make('region_type_id')->label('Benchmarking Region')->options($regionTypes)->multiple()->searchable()->reactive(),
                    Checkbox::make('show_benchmarking')->label('Tampilkan Benchmarking')->default(false)->reactive()->visible($is_benchmarking),
                    Checkbox::make('show_dataLabels')->label('Tampilkan Nilai')->default(true)->reactive(),
                ])
                ->columns(2),

            Section::make('Konfigurasi Chart Utama')
                ->schema([
                    Group::make([
                        Select::make('nilai_type')
                            ->label('Tipe Nilai IMUT')
                            ->options(['line' => 'Line', 'column' => 'Column'])
                            ->default('line')
                            ->reactive(),

                        ColorPicker::make('color_nilai')
                            ->label('Warna Nilai IMUT')
                            ->default('#3b82f6')
                            ->reactive(),
                    ])->columns(2),

                    Group::make([
                        Select::make('target_type')
                            ->label('Tipe Target')
                            ->options(['line' => 'Line', 'column' => 'Column'])
                            ->default('line')
                            ->reactive(),

                        ColorPicker::make('color_target')
                            ->label('Warna Target')
                            ->default('#f59e0b')
                            ->reactive(),
                    ])->columns(2),
                ])
                ->columns(1),

            Section::make('Benchmarking Series')
                ->schema(
                    collect($regionTypes)->map(function ($name, $id) {
                        return Fieldset::make($name)
                            ->schema([
                                Grid::make()
                                    ->schema([
                                        Select::make("benchmark_types.$id")
                                            ->label('Tipe')
                                            ->options(['line' => 'Line', 'column' => 'Column'])
                                            ->default('column')
                                            ->reactive(),

                                        ColorPicker::make("benchmark_colors.$id")
                                            ->label('Warna')
                                            ->default('#' . substr(md5($name), 0, 6))
                                            ->reactive(),
                                    ])
                                    ->columns(2),
                            ]);
                    })->values()->toArray()
                )
                ->columns(2)
                ->collapsed(),
        ];
    }

    protected function getOptions(): array
    {
        $chartType = $this->filterFormData['chart_type'] ?? 'mixed';
        $showdataLabels = $this->filterFormData['show_dataLabels'] ?? true;

        $seriesData = $this->getChartSeries($chartType);
        $xLabels = $this->getMonthLabels();

        if (empty($seriesData)) {
            return ApexChartConfig::noDataOptions();
        }

        return ApexChartConfig::defaultOptions(
            series: $seriesData,
            xLabels: $xLabels,
            xLableTitle: 'Periode',
            yLableTitle: 'Nilai (%)',
            yAxisMin: 0,
            yAxisMax: 120,
            showDataLabels: $showdataLabels
        );
    }


    protected function getMonthLabels(): array
    {
        $year = $this->filterFormData['year'] ?? now()->year;

        return LaporanImut::whereYear('assessment_period_start', $year)
            ->orderBy('assessment_period_start')
            ->pluck('assessment_period_start')
            ->map(fn($date) => \Carbon\Carbon::parse($date)->translatedFormat('F Y'))
            ->unique()
            ->values()
            ->toArray();
    }

    protected function getChartSeries(string $chartType = 'mixed'): array
    {
        $year = $this->filterFormData['year'] ?? now()->year;
        $regionTypeId = $this->filterFormData['region_type_id'] ?? null;
        $imutDataId = $this->imutData->id;
        $showBenchmarking = $this->filterFormData['show_benchmarking'] ?? true;

        $penilaianData = Cache::remember(
            CacheKey::imutPenilaian($imutDataId, $year),
            now()->addMinutes(30),
            fn() => DB::table('imut_penilaians')
                ->join('laporan_unit_kerjas', 'laporan_unit_kerjas.id', '=', 'imut_penilaians.laporan_unit_kerja_id')
                ->join('laporan_imuts', 'laporan_imuts.id', '=', 'laporan_unit_kerjas.laporan_imut_id')
                ->join('imut_profil', 'imut_profil.id', '=', 'imut_penilaians.imut_profil_id')
                ->join('imut_data', 'imut_data.id', '=', 'imut_profil.imut_data_id')
                ->where('imut_data.id', $imutDataId)
                ->whereYear('laporan_imuts.assessment_period_start', $year)
                ->whereNull('laporan_imuts.deleted_at')
                ->selectRaw("
                DATE_FORMAT(laporan_imuts.assessment_period_start, '%Y-%m') as periode,
                SUM(imut_penilaians.numerator_value) as total_num,
                SUM(imut_penilaians.denominator_value) as total_denum,
                AVG(imut_profil.target_value) as target")
                ->groupBy('periode')
                ->orderBy('periode')
                ->get()
        );

        $dataNilai = [];
        $dataTarget = [];

        foreach ($penilaianData as $row) {
            $label = \Carbon\Carbon::parse($row->periode . '-01')->translatedFormat('F Y');
            $nilai = $row->total_denum > 0 ? round(($row->total_num / $row->total_denum) * 100, 2) : 0;
            $target = round($row->target, 2);

            $dataNilai[$label] = $nilai;
            $dataTarget[$label] = $target;
        }

        $labels = array_keys($dataNilai);

        $tipeNilai = $this->filterFormData['nilai_type'] ?? ($chartType === 'bar' || $chartType === 'mixed' ? 'column' : 'line');
        $tipeTarget = $this->filterFormData['target_type'] ?? 'line';

        $series = [
            [
                'name' => 'Nilai IMUT',
                'type' => $tipeNilai,
                'data' => array_map(fn($l) => $dataNilai[$l] ?? 0, $labels),
                'color' => $this->filterFormData['color_nilai'] ?? '#3b82f6',
            ],
            [
                'name' => 'Target Standar',
                'type' => $tipeTarget,
                'data' => array_map(fn($l) => $dataTarget[$l] ?? 0, $labels),
                'color' => $this->filterFormData['color_target'] ?? '#f59e0b',
            ],
        ];

        if ($showBenchmarking) {
            $benchmarkKey = CacheKey::imutBenchmarking($year, $regionTypeId);
            $benchmarking = Cache::remember(
                $benchmarkKey,
                now()->addMinutes(30),
                fn() => ImutBenchmarking::query()
                    ->with('regionType:id,type')
                    ->select('year', 'month', 'benchmark_value', 'region_type_id')
                    ->where('year', $year)
                    ->when($regionTypeId, fn($q) => $q->whereIn('region_type_id', (array) $regionTypeId))
                    ->get()
            );

            $benchmarkGrouped = $benchmarking->groupBy(fn($item) => sprintf('%04d-%02d', $item->year, $item->month));
            $regionSeries = [];

            foreach ($benchmarkGrouped as $periodeKey => $items) {
                $label = \Carbon\Carbon::createFromFormat('Y-m', $periodeKey)->translatedFormat('F Y');

                foreach ($items as $item) {
                    $typeName = $item->regionType->type ?? 'Unknown';
                    $regionSeries[$item->region_type_id][$typeName][$label] = round($item->benchmark_value, 2);
                }
            }

            foreach ($regionSeries as $regionId => $seriesGroup) {
                foreach ($seriesGroup as $name => $data) {
                    if (collect($labels)->contains(fn($l) => isset($data[$l]))) {
                        $series[] = [
                            'name' => $name,
                            'type' => $this->filterFormData['benchmark_types'][$regionId] ?? 'column',
                            'data' => array_map(fn($l) => $data[$l] ?? null, $labels),
                            'color' => $this->filterFormData['benchmark_colors'][$regionId] ?? '#' . substr(md5($name), 0, 6),
                        ];
                    }
                }
            }
        }

        return $series;
    }
}