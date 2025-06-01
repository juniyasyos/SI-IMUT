<?php

namespace App\Services;

use App\Models\ImutData;
use App\Models\ImutPenilaian;
use App\Models\LaporanImut;
use App\Support\CacheKey;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;

class LaporanImutService
{
    public function getLatestLaporan(): ?LaporanImut
    {
        return Cache::remember(CacheKey::latestLaporan(), now()->addMinutes(30), function () {
            return LaporanImut::where('status', LaporanImut::STATUS_PROCESS)
                ->latest('assessment_period_start')
                ->first()
                ?? LaporanImut::latest('assessment_period_start')->first();
        });
    }

    public function getLatestLaporanId(): int
    {
        return $this->getLatestLaporan()?->id ?? 0;
    }

    /**
     * Ambil data chart indikator tercapai, unit melapor, dan belum dinilai
     * @param int $limit
     * @return array
     */
    public function getChartDataForLastLaporan(int $limit = 6): array
    {
        return Cache::remember(CacheKey::dashboardSiimutAllChartData(), now()->addDays(7), function () use ($limit) {
            $laporanList = $this->getRecentLaporanList($limit);

            $laporanIds = $laporanList->pluck('id');
            $indikatorAktif = $this->getAktifIndikatorWithProfiles($laporanIds);
            $penilaianAll = $this->getGroupedPenilaianByLaporan($laporanIds);
            $penilaianByProfile = $this->getGroupedPenilaianByProfile($laporanIds, $indikatorAktif);

            $result = [
                'tercapai' => [],
                'unitMelapor' => [],
                'belumDinilai' => [],
            ];

            foreach ($laporanList as $laporan) {
                $result['tercapai'][] = $this->countTercapai($indikatorAktif, $penilaianByProfile, $laporan->id);
                $result['unitMelapor'][] = $penilaianAll->get($laporan->id, collect())
                    ->pluck('laporanUnitKerja.unit_kerja_id')
                    ->unique()
                    ->count();

                $result['belumDinilai'][] = $penilaianAll->get($laporan->id, collect())
                    ->filter(
                        fn($p) =>
                        !is_null($p->numerator_value) &&
                            !is_null($p->denominator_value) &&
                            is_null($p->recommendations)
                    )->count();
            }

            return $result;
        });
    }

    /**
     * Ambil ringkasan laporan saat ini.
     * @param LaporanImut $laporan
     * @return array|null
     */
    public function getCurrentLaporanData(LaporanImut $laporan): ?array
    {
        $laporanId = $laporan->id;
        $imutPenilaians = ImutPenilaian::with(['profile', 'laporanUnitKerja.unitKerja'])
            ->whereHas('laporanUnitKerja', fn($q) => $q->where('laporan_imut_id', $laporanId))
            ->get();

        $indikatorAktif = $this->getAktifIndikatorWithProfiles(collect([$laporanId]));
        $profileIds = $this->getLatestProfileIds($indikatorAktif);

        $penilaianByProfile = ImutPenilaian::with('profile', 'laporanUnitKerja')
            ->whereIn('imut_profil_id', $profileIds)
            ->whereHas('laporanUnitKerja', fn($q) => $q->where('laporan_imut_id', $laporanId))
            ->get()
            ->groupBy('imut_profil_id');

        return [
            'totalIndikator' => $indikatorAktif->count(),
            'tercapai'       => $this->countTercapai($indikatorAktif, $penilaianByProfile, $laporanId),
            'unitMelapor'    => $imutPenilaians->pluck('laporanUnitKerja.unit_kerja_id')->unique()->count(),
            'totalUnit'      => $laporan->unitKerjas()->count(),
            'belumDinilai'   => $imutPenilaians->filter(
                fn($p) => is_null($p->numerator_value) || is_null($p->denominator_value)
            )->count(),
        ];
    }

    public function getPenilaianGroupedByProfile(int $laporanId): Collection
    {
        return ImutPenilaian::query()
            ->select(['id', 'imut_profil_id', 'laporan_unit_kerja_id', 'numerator_value', 'denominator_value'])
            ->whereHas('laporanUnitKerja', fn($q) => $q->where('laporan_imut_id', $laporanId))
            ->whereNotNull('numerator_value')
            ->whereNotNull('denominator_value')
            ->get()
            ->groupBy('imut_profil_id');
    }

    /** ========================== PRIVATE HELPERS ========================== */

    private function isTercapai(ImutPenilaian $p, $profile): bool
    {
        if ($p->denominator_value == 0) return false;

        $result = round(($p->numerator_value / $p->denominator_value) * 100, 2);

        return match ($profile->target_operator) {
            '='  => $result == $profile->target_value,
            '>=' => $result >= $profile->target_value,
            '<=' => $result <= $profile->target_value,
            '>'  => $result > $profile->target_value,
            '<'  => $result < $profile->target_value,
            default => false,
        };
    }

    private function getRecentLaporanList(int $limit): Collection
    {
        $laporan = LaporanImut::with('unitKerjas')
            ->orderByDesc('assessment_period_start')
            ->limit($limit)
            ->get();

        if ($laporan->count() < $limit) {
            $additional = LaporanImut::where('status', '!=', LaporanImut::STATUS_PROCESS)
                ->orderByDesc('assessment_period_start')
                ->limit($limit - $laporan->count())
                ->with('unitKerjas')
                ->get();

            $laporan = $laporan->concat($additional);
        }

        return $laporan->sortBy('assessment_period_start')->values();
    }

    private function getAktifIndikatorWithProfiles(Collection $laporanIds): Collection
    {
        return ImutData::with([
            'profiles' => fn($q) => $q->latest('version')->take(1),
            'profiles.penilaian' => fn($q) => $q->whereHas('laporanUnitKerja', fn($q) => $q->whereIn('laporan_imut_id', $laporanIds)),
        ])
            ->where('status', true)
            ->whereHas('profiles.penilaian.laporanUnitKerja', fn($q) => $q->whereIn('laporan_imut_id', $laporanIds))
            ->get();
    }

    private function getGroupedPenilaianByLaporan(Collection $laporanIds): Collection
    {
        return ImutPenilaian::with(['profile', 'laporanUnitKerja'])
            ->whereHas('laporanUnitKerja', fn($q) => $q->whereIn('laporan_imut_id', $laporanIds))
            ->get()
            ->groupBy('laporanUnitKerja.laporan_imut_id');
    }

    private function getGroupedPenilaianByProfile(Collection $laporanIds, Collection $indikatorAktif): Collection
    {
        $profileIds = $this->getLatestProfileIds($indikatorAktif);

        return ImutPenilaian::with('profile', 'laporanUnitKerja')
            ->whereIn('imut_profil_id', $profileIds)
            ->whereHas('laporanUnitKerja', fn($q) => $q->whereIn('laporan_imut_id', $laporanIds))
            ->get()
            ->groupBy('imut_profil_id');
    }

    private function getLatestProfileIds(Collection $indikatorAktif): Collection
    {
        return $indikatorAktif
            ->map(fn($indikator) => $indikator->profiles->sortByDesc('version')->first()?->id)
            ->filter()
            ->unique()
            ->values();
    }

    private function countTercapai(Collection $indikatorAktif, Collection $penilaianByProfile, int $laporanId): int
    {
        return $indikatorAktif->reduce(function (int $carry, $indikator) use ($penilaianByProfile, $laporanId) {
            $profile = $indikator->profiles->sortByDesc('version')->first();
            if (!$profile) return $carry;

            $penilaians = $penilaianByProfile->get($profile->id, collect())
                ->filter(fn($p) => $p->laporanUnitKerja->laporan_imut_id === $laporanId);

            if ($penilaians->isEmpty()) return $carry;

            $tercapai = $penilaians->filter(fn($p) => $this->isTercapai($p, $profile))->count();

            return $tercapai / $penilaians->count() >= 1 ? $carry + 1 : $carry;
        }, 0);
    }
}
