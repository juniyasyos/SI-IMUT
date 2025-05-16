<?php

namespace App\Filament\Resources\ImutDataResource\Pages;

use App\Filament\Resources\ImutDataResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListImutData extends ListRecords
{
    protected static string $resource = ImutDataResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
