<?php

namespace App\Filament\Resources;

use App\Traits\HasActiveIcon;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Resources\Resource;

abstract class BaseResource extends Resource implements HasShieldPermissions
{
    use HasActiveIcon;

    /**
     * Get default permission prefixes for all resources.
     *
     * @return array
     */
    public static function getPermissionPrefixes(): array
    {
        return array_merge(
            [
                'view',
                'view_any',
                'create',
                'update',
                'restore',
                'restore_any',
                'replicate',
                'reorder',
                'delete',
                'delete_any',
                'force_delete',
                'force_delete_any',
                static::getModelPermissionPrefix() . ':create',
                static::getModelPermissionPrefix() . ':update',
                static::getModelPermissionPrefix() . ':delete',
                static::getModelPermissionPrefix() . ':pagination',
                static::getModelPermissionPrefix() . ':detail',
            ],
            static::getAdditionalPermissionPrefixes()
        );
    }

    /**
     * Get model permission prefix dynamically from the model name.
     *
     * @return string
     */
    protected static function getModelPermissionPrefix(): string
    {
        return strtolower(class_basename(static::getModel()));
    }

    /**
     * Additional permission prefixes for specific resources.
     *
     * @return array
     */
    protected static function getAdditionalPermissionPrefixes(): array
    {
        return [];
    }
}
