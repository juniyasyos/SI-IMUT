<?php

use App\Models\ImutData;
use App\Services\DashboardSiimutService;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class ImutTercapai extends BaseWidget
{
    protected function getDashboardService(): DashboardSiimutService
    {
        return app(DashboardSiimutService::class);
    }

    public function table(Table $table): Table
    {
        $dashboardService = $this->getDashboardService();
        $laporanId = $dashboardService->getLatestLaporanId();

        return $table
            ->query(
                ImutData::where('status', true)
                    ->whereHas('profiles.penilaian.laporanUnitKerja', function ($q) use ($laporanId) {
                        $q->where('laporan_imut_id', $laporanId);
                    })
                    ->with([
                        'profiles' => function ($query) {
                            $query->latest('version')->take(1);
                        },
                        'profiles.penilaian' => function ($query) use ($laporanId) {
                            $query->whereHas('laporanUnitKerja', fn($q) => $q->where('laporan_imut_id', $laporanId));
                        }
                    ])
            )
            ->columns([
                Tables\Columns\TextColumn::make('title')
            ]);
    }
}
