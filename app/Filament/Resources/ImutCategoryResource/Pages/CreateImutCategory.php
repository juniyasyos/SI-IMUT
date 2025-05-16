<?php

namespace App\Filament\Resources\ImutCategoryResource\Pages;

use App\Filament\Resources\ImutCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateImutCategory extends CreateRecord
{
    protected static string $resource = ImutCategoryResource::class;
    protected static bool $canCreateAnother = false;

    //customize redirect after create
    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
