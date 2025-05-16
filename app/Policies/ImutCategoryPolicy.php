<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ImutCategory;
use Illuminate\Auth\Access\HandlesAuthorization;

class ImutCategoryPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_imut::category');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ImutCategory $imutCategory): bool
    {
        return $user->can('view_imut::category');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_imut::category');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ImutCategory $imutCategory): bool
    {
        return $user->can('update_imut::category');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ImutCategory $imutCategory): bool
    {
        return $user->can('delete_imut::category');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_imut::category');
    }
}
