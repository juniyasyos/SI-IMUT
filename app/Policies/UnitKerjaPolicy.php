<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UnitKerja;
use Illuminate\Auth\Access\HandlesAuthorization;

class UnitKerjaPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_unit_kerja');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, UnitKerja $unitKerja): bool
    {
        return $user->can('view_unit_kerja');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_unit_kerja');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, UnitKerja $unitKerja): bool
    {
        return $user->can('update_unit_kerja');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, UnitKerja $unitKerja): bool
    {
        return $user->can('delete_unit_kerja');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_unit_kerja');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, UnitKerja $unitKerja): bool
    {
        return $user->can('force_delete_unit_kerja');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_unit_kerja');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, UnitKerja $unitKerja): bool
    {
        return $user->can('restore_unit_kerja');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_unit_kerja');
    }
}
