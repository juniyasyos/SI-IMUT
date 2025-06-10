<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MediaPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_media::custom');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function viewAll(User $user): bool
    {
        return $user->can('view_all_media::custom');
    }

    public function viewByUnitKerja(User $user): bool
    {
        return $user->can('view_by_unit_kerja_media::custom');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_media::custom');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user): bool
    {
        return $user->can('update_media::custom');
    }

    public function creteSubFolder(User $user): bool
    {
        return $user->can('create_sub_folder_media::custom');
    }
}
