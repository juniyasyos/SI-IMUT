<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class FilamentInfoWidget extends Widget
{
    protected static ?int $sort = -2;

    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 1;

    /**
     * @var view-string
     */
    // protected static string $view = 'filament-panels::widgets.filament-info-widget';
    protected static string $view = 'filament.widgets.filament-info-widget';
}