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
        return $user->can('view_any::unit_kerja');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, UnitKerja $unitKerja): bool
    {
        return $user->can('view::unit_kerja');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create::unit_kerja');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, UnitKerja $unitKerja): bool
    {
        return $user->can('update::unit_kerja');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, UnitKerja $unitKerja): bool
    {
        return $user->can('delete::unit_kerja');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any::unit_kerja');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, UnitKerja $unitKerja): bool
    {
        return $user->can('force_delete::unit_kerja');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any::unit_kerja');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, UnitKerja $unitKerja): bool
    {
        return $user->can('restore::unit_kerja');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any::unit_kerja');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, UnitKerja $unitKerja): bool
    {
        return $user->can('replicate::unit_kerja');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder::unit_kerja');
    }
}
