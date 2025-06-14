<?php

namespace App\Filament\Forms;

use App\Filament\Resources\ImutDataResource;
use App\Models\RegionType;
use Awcodes\TableRepeater\Components\TableRepeater;
use Awcodes\TableRepeater\Header;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;

class ImutBenchmarkingForm
{
    protected static function benchmarkingSchema(): array
    {
        return [
                ];
    }
}