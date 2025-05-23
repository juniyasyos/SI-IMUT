<?php

namespace App\Policies;

use App\Models\User;
use TomatoPHP\FilamentMediaManager\Models\Media;
use Illuminate\Auth\Access\HandlesAuthorization;

class MediaPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_media');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function viewAll(User $user): bool
    {
        return $user->can('view_all_media');
    }

    public function viewByUnitKerja(User $user): bool
    {
        return $user->can('view_by_unit_kerja_media');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_media');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user): bool
    {
        return $user->can('update_media');
    }
}
