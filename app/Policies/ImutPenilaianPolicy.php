<?php

namespace App\Policies;

use App\Models\ImutPenilaian;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ImutPenilaianPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_imut::penilaian');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ImutPenilaian $imutPenilaian): bool
    {
        return $user->can('view_imut::penilaian');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_imut::penilaian');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ImutPenilaian $imutPenilaian): bool
    {
        return $user->can('update_imut::penilaian');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ImutPenilaian $imutPenilaian): bool
    {
        return $user->can('delete_imut::penilaian');
    }

    protected function userCanAccessPenilaian(User $user, ImutPenilaian $penilaian): bool
    {
        $unitKerjaId = $penilaian->laporanUnitKerja?->unitKerja?->id;

        if (! $unitKerjaId) {
            return false;
        }

        return $user->unitKerjas()->where('unit_kerja.id', $unitKerjaId)->exists();
    }

    /**
     * Hak akses melihat detail penilaian milik unit kerjanya.
     */
    public function viewPenilaian(User $user, ImutPenilaian $penilaian): bool
    {
        return $user->can('view_imut_penilaian_imut::penilaian')
            && $this->userCanAccessPenilaian($user, $penilaian);
    }

    /**
     * Hak akses mengedit numerator & denominator untuk penilaian milik unit kerjanya.
     */
    public function updateNumeratorDenominator(User $user, ImutPenilaian $penilaian): bool
    {
        return $user->can('update_numerator_denominator_imut::penilaian')
            && $this->userCanAccessPenilaian($user, $penilaian);
    }

    /**
     * Hak akses memperbarui profil penilaian (misal metadata, indikator, dsb).
     */
    public function updateProfile(User $user, ImutPenilaian $penilaian): bool
    {
        return $user->can('update_profile_penilaian_imut::penilaian')
        && $this->userCanAccessPenilaian($user, $penilaian);
    }

    /**
     * Hak akses membuat rekomendasi dari hasil penilaian.
     */
    public function createRecommendation(User $user, ImutPenilaian $penilaian): bool
    {
        return $user->can('create_recommendation_penilaian_imut::penilaian')
            && $this->userCanAccessPenilaian($user, $penilaian);
    }
}
