<?php

namespace App\Policies;

use App\Models\User;
use App\Models\LaporanImut;
use Illuminate\Auth\Access\HandlesAuthorization;

class LaporanImutPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_laporan::imut');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, LaporanImut $laporanImut): bool
    {
        return $user->can('view_laporan::imut');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_laporan::imut');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, LaporanImut $laporanImut): bool
    {
        return $user->can('update_laporan::imut');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, LaporanImut $laporanImut): bool
    {
        return $user->can('delete_laporan::imut');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_laporan::imut');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, LaporanImut $laporanImut): bool
    {
        return $user->can('force_delete_laporan::imut');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_laporan::imut');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, LaporanImut $laporanImut): bool
    {
        return $user->can('restore_laporan::imut');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_laporan::imut');
    }
}
