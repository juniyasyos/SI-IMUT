<?php

namespace App\Filament\Resources\FolderCustomResource\Pages;

use App\Filament\Resources\FolderCustomResource;
use Juniyasyos\FilamentMediaManager\Resources\FolderResource\Pages\CreateFolder;

class CreateFolderCustom extends CreateFolder
{
    protected static string $resource = FolderCustomResource::class;

    public static function canAccess(array $parameters = []): bool
    {
        /** @var User $user */
        $user = \Illuminate\Support\Facades\Auth::user();

        return $user->can('create_folder::custom');
    }
}
