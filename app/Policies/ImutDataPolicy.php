<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ImutData;
use Illuminate\Auth\Access\HandlesAuthorization;

class ImutDataPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_imut::data');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ImutData $imutData): bool
    {
        return $user->can('view_imut::data');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_imut::data');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ImutData $imutData): bool
    {
        return $user->can('update_imut::data');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ImutData $imutData): bool
    {
        return $user->can('delete_imut::data');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_imut::data');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, ImutData $imutData): bool
    {
        return $user->can('force_delete_imut::data');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_imut::data');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, ImutData $imutData): bool
    {
        return $user->can('restore_imut::data');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_imut::data');
    }
}
