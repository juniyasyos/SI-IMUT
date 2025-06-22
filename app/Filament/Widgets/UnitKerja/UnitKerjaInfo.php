<?php

namespace App\Filament\Widgets\UnitKerja;

use Filament\Widgets\Widget;

class UnitKerjaInfo extends Widget
{
    protected static string $view = 'filament.widgets.unit-kerja-info';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 2;

    public static function canView(): bool
    {
        return auth()->check()
        && auth()->user()->unitKerjas()->exists()
        && auth()->user()?->can('widget_UnitKerjaInfo');
    }

    protected function getViewData(): array
    {
        $user = auth()->user();
        $unitKerja = $user->unitKerjas()->first();

        return compact('unitKerja');
    }
}