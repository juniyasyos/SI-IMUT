<?php

namespace App\Policies;

use App\Models\ImutPenilaian;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ImutPenilaianPolicy
{
    use HandlesAuthorization;

    /**
     * Mengecek apakah user punya akses ke data penilaian berdasarkan unit kerja.
     */
    protected function userCanAccessPenilaian(User $user, ImutPenilaian $penilaian): bool
    {
        $unitKerjaId = $penilaian->laporanUnitKerja?->unitKerja?->id;

        if (! $unitKerjaId) {
            return false;
        }

        return $user->unitKerjas()->where('unit_kerja.id', $unitKerjaId)->exists();
    }

    /**
     * Hak akses melihat daftar penilaian.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_imut::penilaian');
    }

    /**
     * Hak akses melihat detail penilaian secara umum (tidak dibatasi unit kerja).
     */
    public function view(User $user, ImutPenilaian $imutPenilaian): bool
    {
        return $user->can('view_imut::penilaian');
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
