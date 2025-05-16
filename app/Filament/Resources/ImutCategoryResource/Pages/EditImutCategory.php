<?php

namespace App\Filament\Resources\ImutCategoryResource\Pages;

use App\Filament\Resources\ImutCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditImutCategory extends EditRecord
{
    protected static string $resource = ImutCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }

    //customize redirect after create
    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
