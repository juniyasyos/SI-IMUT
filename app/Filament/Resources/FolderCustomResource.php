<?php

namespace App\Filament\Resources;

use App\Traits\HasActiveIcon;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Juniyasyos\FilamentMediaManager\Resources\FolderResource as BaseFolderResource;

class FolderCustomResource extends BaseFolderResource implements HasShieldPermissions
{
    use HasActiveIcon;

    public static function getPermissionPrefixes(): array
    {
        return [
            'view_any',
            'view_all',
            'view_by_unit_kerja',
            'create',
            'update',
            'delete',
            'delete_any',
        ];
    }

    // public static function getPages(): array
    // {
    //     return [
    //         'index' => \App\Filament\Resources\FolderCustomResource\Pages\ListFoldersCustom::route('/'),
    //     ];
    // }

    /**
     * Override slug resource secara statik.
     */
    public static function getSlug(): string
    {
        return config('filament-media-manager.slug_folder', 'folder-custom');
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\FolderCustomResource\Pages\ListFoldersCustom::route('/'),
            'media' => \App\Filament\Resources\MediaCustomResource\Pages\ListMediaCustom::route('/media-name={folderName}'),
        ];
    }
}
