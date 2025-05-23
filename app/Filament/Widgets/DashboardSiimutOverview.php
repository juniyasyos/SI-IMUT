<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class DashboardSiimutOverview extends BaseWidget
{
    public static function canView(): bool
    {
        return Auth::user()?->can('widget_DashboardSiimutOverview');
    }

    protected function getStats(): array
    {
        // Dummy data
        $data = [
            'totalIndikator' => 18,
            'tercapai'       => 12,
            'unitMelapor'    => 7,
            'totalUnit'      => 10,
            'belumDinilai'   => 4,
        ];

        // Persentase
        $pencapaianPersen = round($data['tercapai'] / $data['totalIndikator'] * 100);
        $persenUnit       = round($data['unitMelapor'] / $data['totalUnit'] * 100);

        return [
            // 1. Indikator Tercapai
            Stat::make('Indikator Tercapai', "{$data['tercapai']} / {$data['totalIndikator']}")
                ->icon($pencapaianPersen >= 80 ? 'heroicon-o-check-circle' : 'heroicon-o-adjustments-vertical')
                ->description('Naik 2 dari bulan lalu')
                ->descriptionIcon('heroicon-o-arrow-trending-up')
                ->chart([4, 5, 7, 8, 9, 10, $data['tercapai']])
                ->color(match (true) {
                    $pencapaianPersen >= 80 => 'success',
                    $pencapaianPersen >= 50 => 'warning',
                    default                => 'danger',
                }),

            // 2. Unit Aktif Melapor
            Stat::make('Unit Aktif Melapor', "{$data['unitMelapor']} / {$data['totalUnit']} Unit")
                ->icon('heroicon-o-user-group')
                ->description('+1 unit dari minggu lalu')
                ->descriptionIcon('heroicon-o-user-plus')
                ->chart([5, 6, 6, 6, 6, 7, $data['unitMelapor']])
                ->color(match (true) {
                    $persenUnit >= 80 => 'success',
                    $persenUnit >= 50 => 'info',
                    default           => 'danger',
                }),

            // 3. Indikator Belum Dinilai
            Stat::make('Indikator Belum Dinilai', "{$data['belumDinilai']}")
                ->icon('heroicon-o-clock')
                ->description('Perlu analisis atau rekomendasi')
                ->descriptionIcon('heroicon-o-pencil-square')
                ->chart([3, 4, 3, 5, 4, 3, $data['belumDinilai']])
                ->color($data['belumDinilai'] > 5 ? 'danger' : 'warning'),
        ];
    }
}
