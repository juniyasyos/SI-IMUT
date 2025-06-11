<?php

namespace App\Services;

use App\Models\ImutPenilaian;
use Illuminate\Support\Collection;

class ImutPenilaianService
{
    public function getByLaporanId(int $laporanId): Collection
    {
        return ImutPenilaian::with(['profile', 'laporanUnitKerja'])
            ->whereHas('laporanUnitKerja', fn ($q) => $q->where('laporan_imut_id', $laporanId))
            ->get();
    }

    public function getFilledByLaporan(int $laporanId): Collection
    {
        return ImutPenilaian::with(['profile', 'laporanUnitKerja'])
            ->whereNotNull('numerator_value')
            ->whereNotNull('denominator_value')
            ->whereHas('laporanUnitKerja', fn ($q) => $q->where('laporan_imut_id', $laporanId))
            ->get();
    }

    public function getByProfileId(int $profileId): Collection
    {
        return ImutPenilaian::with(['laporanUnitKerja', 'profile'])
            ->where('imut_profil_id', $profileId)
            ->get();
    }

    public function groupByProfileId(int $laporanId): Collection
    {
        return $this->getFilledByLaporan($laporanId)
            ->groupBy('imut_profil_id');
    }

    public function groupByUnitKerja(int $laporanId): Collection
    {
        return $this->getFilledByLaporan($laporanId)
            ->groupBy('laporan_unit_kerja_id');
    }

    public function countBelumDinilai(int $laporanId): int
    {
        return ImutPenilaian::whereHas('laporanUnitKerja', fn ($q) => $q->where('laporan_imut_id', $laporanId))
            ->where(function ($q) {
                $q->whereNull('numerator_value')->orWhereNull('denominator_value');
            })
            ->count();
    }

    public function summaryByLaporan(int $laporanId): array
    {
        $data = $this->getFilledByLaporan($laporanId);

        $numerator = $data->sum('numerator_value');
        $denominator = $data->sum('denominator_value');
        $percentage = $denominator > 0 ? round(($numerator / $denominator) * 100, 2) : null;

        return compact('numerator', 'denominator', 'percentage');
    }

    public function summaryByUnitKerja(int $laporanId): Collection
    {
        return $this->groupByUnitKerja($laporanId)->map(function ($items, $unitKerjaId) {
            $numerator = $items->sum('numerator_value');
            $denominator = $items->sum('denominator_value');
            $percentage = $denominator > 0 ? round(($numerator / $denominator) * 100, 2) : null;

            return compact('unitKerjaId', 'numerator', 'denominator', 'percentage');
        })->values();
    }

    public function summaryByProfile(int $laporanId): Collection
    {
        return $this->groupByProfileId($laporanId)->map(function ($items, $profileId) {
            $numerator = $items->sum('numerator_value');
            $denominator = $items->sum('denominator_value');
            $percentage = $denominator > 0 ? round(($numerator / $denominator) * 100, 2) : null;

            return compact('profileId', 'numerator', 'denominator', 'percentage');
        })->values();
    }
}
