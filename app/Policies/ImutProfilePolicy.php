<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ImutProfile;
use Illuminate\Auth\Access\HandlesAuthorization;

class ImutProfilePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_imut::profile');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ImutProfile $imutProfile): bool
    {
        return $user->can('view_imut::profile');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_imut::profile');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ImutProfile $imutProfile): bool
    {
        return $user->can('update_imut::profile');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ImutProfile $imutProfile): bool
    {
        return $user->can('delete_imut::profile');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_imut::profile');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, ImutProfile $imutProfile): bool
    {
        return $user->can('force_delete_imut::profile');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_imut::profile');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, ImutProfile $imutProfile): bool
    {
        return $user->can('restore_imut::profile');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_imut::profile');
    }
}