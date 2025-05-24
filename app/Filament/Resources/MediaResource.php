<?php

namespace App\Filament\Resources;

use App\Traits\HasActiveIcon;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Juniyasyos\FilamentMediaManager\Resources\MediaResource as BaseMediaResource;

class MediaResource extends BaseMediaResource implements HasShieldPermissions
{
    use HasActiveIcon;
    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_all',
            'view_by_unit_kerja',
            'create',
            'update',
            'delete',
            'delete_any',
        ];
    }
}
