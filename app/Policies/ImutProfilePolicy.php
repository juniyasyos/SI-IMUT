<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ImutProfile;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Policy untuk mengatur izin akses terhadap model ImutProfile.
 * Mendukung pengecekan berbasis permission + kepemilikan data (created_by via imutData).
 */
class ImutProfilePolicy
{
    use HandlesAuthorization;

    /**
     * Cek apakah user dapat melihat daftar ImutProfile.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_imut::profile');
    }

    /**
     * Cek apakah user dapat melihat detail dari ImutProfile tertentu.
     */
    public function view(User $user, ImutProfile $imutProfile): bool
    {
        return $this->isOwner($user, $imutProfile) || $user->can('view_imut::profile');
    }

    /**
     * Cek apakah user dapat membuat ImutProfile.
     */
    public function create(User $user): bool
    {
        return $user->can('create_imut::profile');
    }

    /**
     * Cek apakah user dapat mengupdate ImutProfile.
     */
    public function update(User $user, ImutProfile $imutProfile): bool
    {
        return $this->isOwner($user, $imutProfile) || $this->forceEditable($user) || $user->can('update_imut::profile');
    }

    /**
     * Cek apakah user dapat menghapus ImutProfile.
     */
    public function delete(User $user, ImutProfile $imutProfile): bool
    {
        return $this->isOwner($user, $imutProfile) || $user->can('delete_imut::profile');
    }

    /**
     * Cek apakah user dapat menghapus banyak ImutProfile sekaligus.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_imut::profile');
    }

    /**
     * Cek apakah user dapat menghapus permanen ImutProfile.
     */
    public function forceDelete(User $user, ImutProfile $imutProfile): bool
    {
        return $this->isOwner($user, $imutProfile) || $user->can('force_delete_imut::profile');
    }

    /**
     * Cek apakah user dapat menghapus permanen banyak data sekaligus.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_imut::profile');
    }

    /**
     * Cek apakah user dapat me-restore ImutProfile.
     */
    public function restore(User $user, ImutProfile $imutProfile): bool
    {
        return $this->isOwner($user, $imutProfile) || $user->can('restore_imut::profile');
    }

    /**
     * Cek apakah user dapat me-restore banyak ImutProfile sekaligus.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_imut::profile');
    }

    /**
     * Cek apakah user memiliki hak akses paksa untuk edit data yang bukan miliknya.
     */
    public function forceEditable(User $user): bool
    {
        return $user->can('force_editable_imut::profile');
    }

    /**
     * Mengecek apakah user adalah pemilik dari data ImutProfile melalui relasi imutData->created_by.
     */
    protected function isOwner(User $user, ImutProfile $imutProfile): bool
    {
        return $imutProfile->imutData?->created_by === $user->id;
    }
}
