<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class AccountWidget extends Widget
{
    protected static ?int $sort = -3;

    protected int|string|array $columnSpan = 1;

    protected static bool $isLazy = false;

    /**
     * @var view-string
     */
    // protected static string $view = 'filament-panels::widgets.account-widget';
    protected static string $view = 'filament.widgets.account-widget';
}
