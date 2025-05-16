<?php

namespace App\Filament\Resources\ImutDataResource\Pages;

use App\Filament\Resources\ImutDataResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateImutData extends CreateRecord
{
    protected static string $resource = ImutDataResource::class;
    protected static bool $canCreateAnother = false;

    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', [
            'record' => $this->record->slug,
        ]);
    }
}
