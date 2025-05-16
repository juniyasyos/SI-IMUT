<?php

namespace App\Filament\Resources\UnitKerjaResource\Pages;

use App\Filament\Resources\UnitKerjaResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateUnitKerja extends CreateRecord
{
    protected static string $resource = UnitKerjaResource::class;
    protected static bool $canCreateAnother = false;

    //customize redirect after create
    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->record->id]);
    }
}
