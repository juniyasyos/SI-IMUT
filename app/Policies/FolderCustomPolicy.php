<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class FolderCustomPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_folder::custom');
    }

    /**
     * Determine whether the user can view record the model.
     */
    public function view(User $user): bool
    {
        return $user->can('view_folder::custom');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function viewAllFolder(User $user): bool
    {
        return $user->can('view_all_folder::custom');
    }

    public function viewByUnitKerja(User $user): bool
    {
        return $user->can('view_by_unit_kerja_folder::custom');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_folder::custom');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user): bool
    {
        return $user->can('update_folder::custom');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function delete(User $user): bool
    {
        return $user->can('delete_folder::custom');
    }
}
