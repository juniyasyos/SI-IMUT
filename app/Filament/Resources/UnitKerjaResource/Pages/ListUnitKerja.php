<?php

namespace App\Filament\Resources\UnitKerjaResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\UnitKerjaResource;

class ListUnitKerja extends ListRecords
{
    protected static string $resource = UnitKerjaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label(__('filament-forms::unit-kerja.actions.add')),
        ];
    }
}
