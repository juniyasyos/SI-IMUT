<?php

namespace App\Services;

use App\Models\ImutPenilaian;
use App\Models\LaporanUnitKerja;
use Illuminate\Support\Facades\Auth;

class UnitKerjaStatService
{
    public function getStats(): array
    {
        $user = Auth::user();
        $unitKerjaIds = $user->unitKerjas->pluck('id');

        $laporanUnitKerjaIds = LaporanUnitKerja::whereIn('unit_kerja_id', $unitKerjaIds)->pluck('id');

        $penilaians = ImutPenilaian::with('profile')
            ->whereIn('laporan_unit_kerja_id', $laporanUnitKerjaIds)
            ->get();

        $totalIndikator = $penilaians->pluck('imut_profil_id')->unique()->count();

        $averagePercentage = $penilaians->avg(function ($penilaian) {
            return $penilaian->denominator_value > 0
                ? ($penilaian->numerator_value * 100 / $penilaian->denominator_value)
                : 0;
        });

        $jumlahMemenuhiTarget = $penilaians->filter(function ($penilaian) {
            $profil = $penilaian->profile;
            if (! $profil || $penilaian->denominator_value == 0) {
                return false;
            }

            $value = $penilaian->numerator_value * 100 / $penilaian->denominator_value;

            return match ($profil->target_operator) {
                '>=' => $value >= $profil->target_value,
                '<=' => $value <= $profil->target_value,
                '=' => round($value, 2) === round($profil->target_value, 2),
                default => false,
            };
        })->count();

        return [
            'totalIndikator' => $totalIndikator,
            'averagePercentage' => $averagePercentage,
            'jumlahMemenuhiTarget' => $jumlahMemenuhiTarget,
        ];
    }
}
