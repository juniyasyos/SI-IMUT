<?php

namespace App\Filament\Resources\FolderCustomResource\Pages;

use Filament\Actions;
use App\Filament\Resources\FolderCustomResource;
use Juniyasyos\FilamentMediaManager\Resources\FolderResource\Pages\ListFolders;

class ListFoldersCustom extends ListFolders
{
    protected static string $resource = FolderCustomResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getBreadcrumbs(): array
    {
        return [
            url('/') => 'Dashboard',
            'folders'
        ];
    }
}
