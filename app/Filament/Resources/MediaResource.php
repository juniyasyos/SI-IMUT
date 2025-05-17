<?php

namespace App\Filament\Resources;

use App\Traits\HasActiveIcon;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use TomatoPHP\FilamentMediaManager\Resources\MediaResource as BaseMediaResource;

class MediaResource extends BaseMediaResource implements HasShieldPermissions
{
    use HasActiveIcon;
    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
        ];
    }
}
