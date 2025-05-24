<?php

namespace App\Filament\Resources;

use App\Traits\HasActiveIcon;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Juniyasyos\FilamentMediaManager\Resources\FolderResource as BaseFolderResource;

class FolderResource extends BaseFolderResource implements HasShieldPermissions
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
}
