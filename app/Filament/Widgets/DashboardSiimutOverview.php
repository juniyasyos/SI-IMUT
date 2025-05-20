<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardSiimutOverview extends BaseWidget
{
    protected function getStats(): array
    {
        // Dummy data
        $totalIndikator = 18;
        $tercapai = 12;
        $belumDiisi = 3;
        $unitMelapor = 7;
        $totalUnit = 10;

        // Logika percabangan warna & ikon
        $pencapaianPersen = $tercapai / $totalIndikator * 100;
        $colorPencapaian = $pencapaianPersen >= 80 ? 'success' : ($pencapaianPersen >= 50 ? 'warning' : 'danger');
        $iconPencapaian = $pencapaianPersen >= 80 ? 'heroicon-o-check-circle' : 'heroicon-o-adjustments-vertical';

        $colorBelumDiisi = $belumDiisi > 2 ? 'warning' : 'success';
        $iconBelumDiisi = $belumDiisi > 2 ? 'heroicon-o-exclamation-triangle' : 'heroicon-o-document-check';

        $persenUnit = $unitMelapor / $totalUnit * 100;
        $colorUnit = $persenUnit >= 80 ? 'success' : ($persenUnit >= 50 ? 'info' : 'danger');
        $iconUnit = 'heroicon-o-user-group';

        return [
            Stat::make('Indikator Tercapai', "$tercapai / $totalIndikator")
                ->icon($iconPencapaian)
                ->description('Naik 2 dari bulan lalu')
                ->descriptionIcon('heroicon-o-arrow-trending-up')
                ->chart([4, 5, 7, 8, 9, 10, 12])
                ->color($colorPencapaian),

            Stat::make('Indikator Belum Diisi', "$belumDiisi Indikator")
                ->icon($iconBelumDiisi)
                ->description('Unit: ICU, IGD, Lab')
                ->descriptionIcon('heroicon-o-exclamation-triangle')
                ->chart([2, 3, 3, 4, 3, 3, 3])
                ->color($colorBelumDiisi),

            Stat::make('Unit Aktif Melapor', "$unitMelapor / $totalUnit Unit")
                ->icon($iconUnit)
                ->description('+1 unit dari minggu lalu')
                ->descriptionIcon('heroicon-o-user-plus')
                ->chart([5, 6, 6, 6, 6, 7, 7])
                ->color($colorUnit),
        ];
    }
}
