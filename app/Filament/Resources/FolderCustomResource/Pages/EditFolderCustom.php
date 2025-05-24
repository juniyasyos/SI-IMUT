<?php

namespace App\Filament\Resources\FolderCustomResource\Pages;

use Filament\Actions;
use App\Filament\Resources\FolderCustomResource;
use Juniyasyos\FilamentMediaManager\Resources\FolderResource\Pages\EditFolder;

class EditFolderCustom extends EditFolder
{
    protected static string $resource = FolderCustomResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
