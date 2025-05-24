<?php

namespace App\Filament\Resources\MediaCustomResource\Pages;

use Juniyasyos\FilamentMediaManager\Resources\MediaResource;
use Filament\Actions;
use Juniyasyos\FilamentMediaManager\Resources\MediaResource\Pages\EditMedia;

class EditMediaCustom extends EditMedia
{
    protected static string $resource = MediaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
