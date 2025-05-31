<?php

namespace App\Filament\Widgets;

use App\Services\DashboardImutService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class DashboardSiimutOverview extends BaseWidget
{
    protected static ?int $sort = 2;

    // Hapus constructor

    protected function getDashboardService(): DashboardImutService
    {
        return app(DashboardImutService::class);
    }

    public static function canView(): bool
    {
        return Auth::user()?->can('widget_DashboardSiimutOverview');
    }

    protected function getStats(): array
    {
        $dashboardService = $this->getDashboardService();

        $data = $dashboardService->getAllDashboardData();
        $statsConfig = $dashboardService->getStatsConfig($data);

        return collect($statsConfig)->map(fn($config) => Stat::make(
            $config['label'],
            $dashboardService->formatValue(data_get($data, $config['key']), $config['format'] ?? null)
        )
            ->icon($config['icon'] ?? null)
            ->description($config['description'])
            ->descriptionIcon($config['descriptionIcon'] ?? null)
            ->chart($data['chart'][$config['chart']] ?? [])
            ->color(is_callable($config['color']) ? ($config['color'])($data) : $config['color']))->toArray();
    }
}
