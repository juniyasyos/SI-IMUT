<?php

namespace App\Filament\Widgets;

use App\Models\LaporanImut;
use Filament\Widgets\Widget;

class LaporanLatestWidget extends Widget
{
    protected static string $view = 'filament.widgets.laporan-latest-widget';

     protected static ?int $sort = 1; 

    protected int | string | array $columnSpan = 'full'; 

    public function getLaporan(): ?LaporanImut
    {
        return LaporanImut::where('status', LaporanImut::STATUS_PROCESS)
            ->latest('assessment_period_start')
            ->first()

            ?? LaporanImut::latest('assessment_period_start')->first();
    }
}
