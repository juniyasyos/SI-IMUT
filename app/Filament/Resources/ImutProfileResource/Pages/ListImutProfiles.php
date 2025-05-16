<?php

namespace App\Filament\Resources\ImutProfileResource\Pages;

use App\Filament\Resources\ImutProfileResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListImutProfiles extends ListRecords
{
    protected static string $resource = ImutProfileResource::class;

    // protected function getHeaderActions(): array
    // {
    //     return [
    //         Actions\CreateAction::make(),
    //     ];
    // }
}
