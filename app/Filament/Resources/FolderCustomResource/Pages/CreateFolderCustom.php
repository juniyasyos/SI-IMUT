<?php

namespace App\Filament\Resources\FolderCustomResource\Pages;

use App\Filament\Resources\FolderCustomResource;
use Juniyasyos\FilamentMediaManager\Resources\FolderResource\Pages\CreateFolder;

class CreateFolderCustom extends CreateFolder
{
    protected static string $resource = FolderCustomResource::class;
}
