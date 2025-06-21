<?php

namespace App\Filament\Widgets\UnitKerja;

use App\Services\UnitKerjaStatService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class StatsForUnitKerja extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected static ?int $sort = 10;

    public static function canView(): bool
    {
        $user = Auth::user();

        return $user
        && $user->unitKerjas()->exists()
        && $user?->can('widget_StatsForUnitKerja');
    }

    protected function getStats(): array
    {
        $service = app(UnitKerjaStatService::class);
        $data = $service->getStats();

        $totalIndikator = $data['totalIndikator'];
        $jumlahMemenuhi = $data['jumlahMemenuhiTarget'];
        $rataCapaian = number_format($data['averagePercentage'], 2);

        // Dummy untuk indikator yang belum dinilai
        $tidakDinilai = 3;
        $sudahDinilai = $totalIndikator - $tidakDinilai;

        return [
            Stat::make('Total Indikator Mutu', $totalIndikator)
                ->description('Jumlah indikator mutu yang dimiliki unit kerja')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('primary')
                ->chart([2, 3, 4, 5, 6, 7, $totalIndikator]),

            Stat::make('Rata-rata Capaian', "{$rataCapaian}%")
                ->description('Rerata capaian dari seluruh penilaian')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('primary')
                ->chart([45, 55, 60, 65, 68, 70, $data['averagePercentage']]),

            Stat::make('Progress Penilaian', "{$sudahDinilai} / {$totalIndikator}")
                ->description("{$tidakDinilai} belum dinilai")
                ->descriptionIcon('heroicon-m-pencil-square')
                ->color('info')
                ->chart([2, 3, 4, 5, 6, 7, $sudahDinilai]),
        ];
    }
}
