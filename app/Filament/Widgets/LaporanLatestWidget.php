<?php

namespace App\Filament\Widgets;

use App\Models\LaporanImut;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class LaporanLatestWidget extends Widget
{
    protected static string $view = 'filament.widgets.laporan-latest-widget';

    public static function canView(): bool
    {
        return Auth::user()?->can('widget_LaporanLatestWidget');
    }

    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    public function getLaporan(): ?LaporanImut
    {
        $today = Carbon::today();

        return LaporanImut::where('status', LaporanImut::STATUS_PROCESS)
            ->whereDate('assessment_period_start', '<=', $today)
            ->whereDate('assessment_period_end', '>=', $today)
            ->orderByDesc('assessment_period_start')
            ->first()

            ?? LaporanImut::latest('assessment_period_start')->first();
    }
}
