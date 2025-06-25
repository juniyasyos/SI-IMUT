<?php

namespace App\Filament\Resources;

use App\Traits\HasActiveIcon;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Juniyasyos\FilamentMediaManager\Resources\MediaResource as BaseMediaResource;

class MediaCustomResource extends BaseMediaResource implements HasShieldPermissions
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
            // 'create_sub_folder'
        ];
    }

    // public static function getPages(): array
    // {
    //     return [
    //         'index' => \App\Filament\Resources\MediaCustomResource\Pages\ListMediaCustom::route('/folders'),
    //     ];
    // }

    /**
     * Override slug resource secara statik.
     */
    public static function getSlug(): string
    {
        return config('filament-media-manager.slug_media', 'media-custom');
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\MediaCustomResource\Pages\ListMediaCustom::route('/'),
        ];
    }
}
