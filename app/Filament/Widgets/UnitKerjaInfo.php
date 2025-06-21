<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class UnitKerjaInfo extends Widget
{
    protected static string $view = 'filament.widgets.unit-kerja-info';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 2;

    public static function canView(): bool
    {
        return auth()->check() && auth()->user()->unitKerjas()->exists();
    }

    protected function getViewData(): array
    {
        $user = auth()->user();
        $unitKerja = $user->unitKerjas()->first(); // Ambil unit kerja pertama

        return compact('unitKerja');
    }
}
