<?php

namespace App\Filament\Resources\MediaCustomResource\Pages;

use App\Filament\Resources\MediaCustomResource;
use Filament\Actions;
use Juniyasyos\FilamentMediaManager\Resources\MediaResource\Pages\EditMedia;

class EditMediaCustom extends EditMedia
{
    protected static string $resource = MediaCustomResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn () => \Illuminate\Support\Facades\Gate::any(['delete_media::custom'])),
        ];
    }
}
