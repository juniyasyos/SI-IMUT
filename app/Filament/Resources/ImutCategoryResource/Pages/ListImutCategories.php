<?php

namespace App\Filament\Resources\ImutCategoryResource\Pages;

use App\Filament\Resources\ImutCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListImutCategories extends ListRecords
{
    protected static string $resource = ImutCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
