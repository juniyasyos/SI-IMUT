<?php

namespace App\Filament\Widgets;

use App\Models\ImutCategory;
use App\Models\LaporanImut;
use App\Services\ImutChartSeriesService;
use App\Support\ApexChartConfig;
use App\Support\CacheKey;
use Carbon\Carbon;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class ImutCapaianWidget extends ApexChartWidget
{
    protected static ?string $chartId = 'imutCapaianWidget';
    protected static ?string $heading = 'Capaian IMUT setiap Kategori Semua Unit Kerja';
    protected static ?int $sort = 4;
    protected static MaxWidth|string $filterFormWidth = MaxWidth::ExtraLarge;
    protected int|string|array $columnSpan = 'full';

    protected function getChartService(): ImutChartSeriesService
    {
        return new ImutChartSeriesService();
    }

    public static function canView(): bool
    {
        return Auth::user()?->can('widget_ImutCapaianWidget');
    }

    protected function getFormSchema(): array
    {
        $categories = $this->getChartService()->getCategories();
        $colors = $this->getChartService()->getDefaultColors();

        return [
            Section::make('Konfigurasi Series')
                ->schema([
                    Checkbox::make('show_dataLabels')
                        ->label('Tampilkan Nilai')
                        ->default(false)
                        ->reactive(),
                    ...collect($categories)->values()->map(function ($shortName, $i) use ($colors) {
                        return Fieldset::make($shortName)
                            ->schema([
                                Grid::make()
                                    ->schema([
                                        Select::make("series_types.{$shortName}")
                                            ->label('Tipe')
                                            ->options([
                                                'column' => 'Column',
                                                'line'   => 'Line',
                                            ])
                                            ->default('column')
                                            ->reactive(),

                                        ColorPicker::make("series_colors.{$shortName}")
                                            ->label('Warna')
                                            ->default($colors[$i % count($colors)])
                                            ->reactive(),
                                    ])
                                    ->columns(2),
                            ]);
                    })->toArray(),
                ])
                ->columns(1)
        ];
    }

    protected function getOptions(): array
    {
        $laporans = $this->getCachedLaporans();
        $showdataLabels = $this->filterFormData['show_dataLabels'] ?? true;

        if ($laporans->isEmpty()) {
            return ApexChartConfig::noDataOptions();
        }

        $xLabels = $this->generateXLabels($laporans);
        $series = $this->getChartService()->buildSeries($laporans, $this->filterFormData ?? []);

        return ApexChartConfig::defaultOptions(
            $series,
            $xLabels,
            xLableTitle: 'IMUT Kategori',
            yLableTitle: 'Capaian (%)',
            showDataLabels: $showdataLabels
        );
    }

    protected function getCachedLaporans()
    {
        return Cache::remember(
            CacheKey::imutLaporans(),
            now()->addMinutes(5),
            fn() => LaporanImut::with([
                'laporanUnitKerjas.imutPenilaians.profile.imutData.categories',
            ])
                ->where('assessment_period_start', '>=', now()->subMonths(6))
                ->where('status', [LaporanImut::STATUS_COMPLETE, LaporanImut::STATUS_COMINGSOON])
                ->orderBy('assessment_period_start')
                ->get()
        );
    }

    protected function generateXLabels($laporans): array
    {
        return $laporans->map(function ($laporan) {
            $start = $laporan->assessment_period_start ? Carbon::parse($laporan->assessment_period_start) : null;
            $end = $laporan->assessment_period_end ? Carbon::parse($laporan->assessment_period_end) : null;

            if (! $start || ! $end) {
                return 'Tidak diketahui';
            }

            return $start->month === $end->month
                ? $start->day . ' - ' . $end->day . ' ' . $start->translatedFormat('F Y')
                : $start->translatedFormat('j F') . ' - ' . $end->translatedFormat('j F Y');
        })->toArray();
    }
}