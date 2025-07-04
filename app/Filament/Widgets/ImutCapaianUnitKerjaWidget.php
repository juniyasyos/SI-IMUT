<?php

namespace App\Filament\Widgets;

use App\Models\ImutCategory;
use App\Models\LaporanImut;
use App\Services\ImutChartSeriesService;
use App\Support\ApexChartConfig;
use App\Support\CacheKey;
use Carbon\Carbon;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class ImutCapaianUnitKerjaWidget extends ApexChartWidget
{
    protected static ?string $chartId = 'imutCapaianUnitKerjaWidget';
    protected static ?int $sort = 20;
    protected static MaxWidth|string $filterFormWidth = MaxWidth::ExtraLarge;
    protected int|string|array $columnSpan = 'full';

    protected function getChartService(): ImutChartSeriesService
    {
        return new ImutChartSeriesService();
    }

    public static function canView(): bool
    {
        $user = Auth::user();

        return $user
            && $user->can('widget_ImutCapaianUnitKerjaWidget')
            && $user->unitKerjas()->exists();
    }

    protected function getHeading(): ?string
    {
        $user = Auth::user();

        $unitKerja = $user->unitKerjas->first();

        return $unitKerja
            ? 'Capaian IMUT setiap Kategori Untuk Unit ' . $unitKerja->unit_name
            : static::$heading;
    }


    protected function getFormSchema(): array
    {
        $categories = $this->getChartService()->getCategories();
        $colors = $this->getChartService()->getDefaultColors();

        return [
            Section::make('Konfigurasi Series')
                ->schema(
                    collect($categories)->values()->map(function ($shortName, $i) use ($colors) {
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
                                    ])->columns(2)
                            ]);
                    })->toArray()
                )
                ->columns(1),
        ];
    }

    protected function getOptions(): array
    {
        $laporans = $this->getCachedLaporans();

        if ($laporans->isEmpty()) {
            return ApexChartConfig::noDataOptions();
        }

        $xLabels = $this->generateXLabels($laporans);
        $series = $this->getChartService()->buildSeries($laporans, $this->filterFormData ?? []);

        return ApexChartConfig::defaultOptions($series, $xLabels, xLableTitle: 'IMUT Kategori', yLableTitle: 'Capaian (%)');
    }

    protected function getCachedLaporans()
    {
        $user = Auth::user();
        $unitKerjaIds = Auth::user()->unitKerjas->pluck('id')->toArray();

        return
            Cache::remember(
                CacheKey::imutLaporansForUnitKerjas($unitKerjaIds),
                now()->addDay(1),
                fn() => LaporanImut::with([
                    'laporanUnitKerjas' => function ($query) use ($unitKerjaIds) {
                        $query->whereIn('unit_kerja_id', $unitKerjaIds);
                    },
                    'laporanUnitKerjas.imutPenilaians.profile.imutData.categories',
                ])
                    ->whereHas('laporanUnitKerjas', function ($query) use ($unitKerjaIds) {
                        $query->whereIn('unit_kerja_id', $unitKerjaIds);
                    })
                    ->where('assessment_period_start', '>=', now()->subMonths(6))
                    ->whereIn('status', [
                        LaporanImut::STATUS_COMPLETE,
                        LaporanImut::STATUS_COMINGSOON
                    ])
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