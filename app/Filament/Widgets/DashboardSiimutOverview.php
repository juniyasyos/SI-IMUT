<?php

namespace App\Filament\Widgets;

use App\Services\DashboardImutService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class DashboardSiimutOverview extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return Auth::user()?->can('widget_DashboardSiimutOverview');
    }

    protected function getDashboardService(): DashboardImutService
    {
        return app(DashboardImutService::class);
    }

    protected function getStats(): array
    {
        $service = $this->getDashboardService();
        $data = $service->getAllDashboardData();

        if (($data['totalIndikator'] ?? 0) === 0) {
            return [
                Stat::make('📢 Belum Ada Laporan Aktif', '')
                    ->description('Tidak dapat menampilkan data karena belum ada laporan aktif.')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->color('gray'),
            ];
        }

        return collect($service->getStatsConfig($data))->map(fn ($config) => Stat::make(
            $config['label'],
            $service->formatValue(data_get($data, $config['key']), $config['format'] ?? null)
        )
            ->icon($config['icon'] ?? null)
            ->description($config['description'])
            ->descriptionIcon($config['descriptionIcon'] ?? null)
            ->chart($data['chart'][$config['chart']] ?? [])
            ->color(is_callable($config['color']) ? $config['color']($data) : $config['color'])
        )->toArray();
    }
}