<?php

namespace App\Policies;

use App\Models\User;
use App\Models\LaporanImut;
use App\Models\ImutPenilaian;
use Illuminate\Auth\Access\HandlesAuthorization;

class LaporanImutPolicy
{
    use HandlesAuthorization;

    public function before(User $user, $ability): bool|null
    {
        return $user->can('imut_penilaian_access_all') ? true : null;
    }

    protected function userCanAccessPenilaian(User $user, ImutPenilaian $penilaian): bool
    {
        $unitKerjaId = $penilaian->laporanUnitKerja?->unitKerja?->id;

        return $unitKerjaId && $user->unitKerjas()->where('unit_kerja.id', $unitKerjaId)->exists();
    }

    // LaporanImut permissions
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_laporan::imut');
    }
    public function view(User $user, LaporanImut $model): bool
    {
        return $user->can('view_laporan::imut');
    }
    public function create(User $user): bool
    {
        return $user->can('create_laporan::imut');
    }
    public function update(User $user, LaporanImut $model): bool
    {
        return $user->can('update_laporan::imut');
    }
    public function delete(User $user, LaporanImut $model): bool
    {
        return $user->can('delete_laporan::imut');
    }
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_laporan::imut');
    }
    public function forceDelete(User $user, LaporanImut $model): bool
    {
        return $user->can('force_delete_laporan::imut');
    }
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_laporan::imut');
    }
    public function restore(User $user, LaporanImut $model): bool
    {
        return $user->can('restore_laporan::imut');
    }
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_laporan::imut');
    }

    // Report-related
    public function viewUnitKerjaReport(User $user): bool
    {
        return $user->can('view_unit_kerja_report_laporan::imut');
    }
    public function viewUnitKerjaReportDetail(User $user): bool
    {
        return $user->can('view_unit_kerja_report_detail_laporan::imut');
    }
    public function viewImutDataReport(User $user): bool
    {
        return $user->can('view_imut_data_report_laporan::imut');
    }
    public function viewImutDataReportDetail(User $user): bool
    {
        return $user->can('view_imut_data_report_detail_laporan::imut');
    }

    // ImutPenilaian (child) permissions
    // public function viewPenilaian(User $user, ImutPenilaian $penilaian): bool
    // {
    //     return $user->can('view_imut_penilaian_imut::penilaian') && $this->userCanAccessPenilaian($user, $penilaian);
    // }

    // public function updateNumeratorDenominator(User $user, ImutPenilaian $penilaian): bool
    // {
    //     return $user->can('update_numerator_denominator_imut::penilaian') && $this->userCanAccessPenilaian($user, $penilaian);
    // }

    // public function updateProfile(User $user, ImutPenilaian $penilaian): bool
    // {
    //     return $user->can('update_profile_penilaian_imut::penilaian') && $this->userCanAccessPenilaian($user, $penilaian);
    // }

    // public function createRecommendation(User $user, ImutPenilaian $penilaian): bool
    // {
    //     return $user->can('create_recommendation_penilaian_imut::penilaian') && $this->userCanAccessPenilaian($user, $penilaian);
    // }
}
