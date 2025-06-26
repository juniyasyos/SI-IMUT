<?php

namespace App\Services;

use App\Models\ImutData;
use App\Models\ImutPenilaian;
use App\Models\ImutProfile;
use App\Models\LaporanImut;
use App\Support\CacheKey;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class LaporanImutService
{
    public function getLatestLaporanId(): int
    {
        return $this->getLatestLaporan()?->id ?? 0;
    }

    public function getLatestLaporan(): ?LaporanImut
    {
        $today = Carbon::today();

        // return Cache::remember(CacheKey::latestLaporan(), now()->addMinutes(30), function () {
        try {
            $laporan = LaporanImut::select(['id', 'assessment_period_start', 'status'])
                ->where('status', LaporanImut::STATUS_PROCESS)
                ->whereDate('assessment_period_start', '<=', $today)
                ->whereDate('assessment_period_end', '>=', $today)
                ->orderByDesc('assessment_period_start')
                ->first();

            if ($laporan) {
                return $laporan;
            }

            // fallback jika tidak ada laporan dengan status PROCESS di periode aktif
            return LaporanImut::select(['id', 'assessment_period_start', 'status'])
                ->latest('assessment_period_start')
                ->first();
        } catch (\Throwable $e) {
            Log::error('Gagal mengambil laporan terbaru: ' . $e->getMessage());
            return null;
        }
        // });
    }


    public function getChartDataForLastLaporan(int $limit = 6): array
    {
        $cacheKey = CacheKey::dashboardSiimutChartData();

        return Cache::remember($cacheKey, now()->addDays(7), function () use ($limit) {
            $laporanList = $this->getRecentLaporanList($limit);

            // 1. Ambil semua laporan ID
            $laporanIds = $laporanList->pluck('id');

            // 2. Ambil semua indikator & profile sekali
            $indikatorAktif = $this->getAktifIndikatorWithProfiles($laporanIds);
            $profileIds = $this->getLatestProfileIds($indikatorAktif);

            // 3. Ambil semua penilaian sekali
            $penilaianByLaporan = $this->getGroupedPenilaian(
                laporanIds: $laporanIds->all(),
                groupBy: 'laporan_unit_kerjas.laporan_imut_id'
            );

            $penilaianByProfile = $this->getGroupedPenilaian(
                laporanIds: $laporanIds->all(),
                profileIds: $profileIds->all(),
                groupBy: 'imut_penilaians.imut_profil_id'
            );

            return $laporanList->map(function ($laporan) use (
                $indikatorAktif,
                $penilaianByLaporan,
                $penilaianByProfile
            ) {
                $laporanId = $laporan->id;
                $penilaian = $penilaianByLaporan->get($laporanId, collect());

                return [
                    'tercapai' => $this->countTercapai($indikatorAktif, $penilaianByProfile, $laporanId),
                    'unitMelapor' => $penilaian->pluck('laporanUnitKerja.unit_kerja_id')->unique()->count(),
                    'belumDinilai' => $this->countBelumDinilai($penilaian),
                ];
            })->toArray();
        });
    }

    public function getCurrentLaporanData(LaporanImut $laporan): ?array
    {
        try {
            $cacheKey = CacheKey::dashboardSiimutAllData($laporan->id);

            return Cache::remember($cacheKey, now()->addHours(12), function () use ($laporan) {
                $laporanId = $laporan->id;
                $laporanIds = [$laporanId];

                // Ambil indikator aktif dan profil terbaru
                $indikatorAktif = $this->getAktifIndikatorWithProfiles(collect($laporanIds));
                $profileIds = $this->getLatestProfileIds($indikatorAktif);

                // Query efisien penilaian
                $penilaian = $this->getGroupedPenilaian(
                    laporanIds: $laporanIds,
                    profileIds: $profileIds->all(),
                    groupBy: 'imut_penilaians.imut_profil_id'
                );

                // Hitung unit melapor dan belum dinilai
                $allPenilaian = $penilaian->flatten();
                $unitMelapor = $allPenilaian
                    ->pluck('laporanUnitKerja.unit_kerja_id')
                    ->unique()
                    ->count();

                $belumDinilai = $allPenilaian->filter(
                    fn($p) => is_null($p->numerator_value) || is_null($p->denominator_value)
                )->count();

                // Load total unit kerja
                $laporan->loadCount('unitKerjas');

                return [
                    'totalIndikator' => $indikatorAktif->count(),
                    'tercapai' => $this->countTercapai($indikatorAktif, $penilaian, $laporanId),
                    'unitMelapor' => $unitMelapor,
                    'totalUnit' => $laporan->unit_kerjas_count,
                    'belumDinilai' => $belumDinilai,
                ];
            });
        } catch (\Throwable $e) {
            Log::error('Gagal mengambil current laporan data: ' . $e->getMessage());

            return null;
        }
    }

    public function getPenilaianGroupedByProfile(int $laporanId): Collection
    {
        return ImutPenilaian::select([
            'id',
            'imut_profil_id',
            'laporan_unit_kerja_id',
            'numerator_value',
            'denominator_value',
        ])
            ->whereHas('laporanUnitKerja', fn($q) => $q->where('laporan_imut_id', $laporanId))
            ->whereNotNull('numerator_value')
            ->whereNotNull('denominator_value')
            ->get()
            ->groupBy('imut_profil_id');
    }

    public function getLaporanList(array $filters = [], ?int $limit = null): Collection
    {
        $query = LaporanImut::with('unitKerjas')
            ->orderByDesc('assessment_period_start');

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['start_date'])) {
            $query->whereDate('assessment_period_start', '>=', $filters['start_date']);
        }

        if (! empty($filters['end_date'])) {
            $query->whereDate('assessment_period_end', '<=', $filters['end_date']);
        }

        return $limit
            ? $query->limit($limit)->get()->sortBy('assessment_period_start')->values()
            : $query->get()->sortBy('assessment_period_start')->values();
    }

    /** ================= PRIVATE HELPERS ================= */
    private function isTercapai(ImutPenilaian $p, $profile): bool
    {
        if ($p->denominator_value == 0) {
            return false;
        }

        $result = round(($p->numerator_value / $p->denominator_value) * 100, 2);

        return match ($profile->target_operator) {
            '=' => $result == $profile->target_value,
            '>=' => $result >= $profile->target_value,
            '<=' => $result <= $profile->target_value,
            '>' => $result > $profile->target_value,
            '<' => $result < $profile->target_value,
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
        $imutDataIds = ImutData::where('status', true)
            ->whereHas('profiles.penilaian.laporanUnitKerja', fn($q) => $q->whereIn('laporan_imut_id', $laporanIds))
            ->pluck('id');

        $latestProfiles = ImutProfile::whereIn('imut_data_id', $imutDataIds)
            ->select('imut_data_id', 'id', 'target_operator', 'target_value', 'version')
            ->orderByDesc('version')
            ->get()
            ->groupBy('imut_data_id')
            ->map(fn($profiles) => $profiles->first());

        $indikatorAktif = ImutData::whereIn('id', $imutDataIds)->get();
        $indikatorAktif->each(fn($indikator) => $indikator->setRelation('profile', $latestProfiles->get($indikator->id)));

        return $indikatorAktif;
    }

    private function getGroupedPenilaian(array $laporanIds, ?array $profileIds = null, string $groupBy = 'laporan_unit_kerjas.laporan_imut_id'): Collection
    {
        $query = ImutPenilaian::query()
            ->select('imut_penilaians.*', 'laporan_unit_kerjas.laporan_imut_id as laporan_id')
            ->with('laporanUnitKerja')
            ->join('laporan_unit_kerjas', 'laporan_unit_kerjas.id', '=', 'imut_penilaians.laporan_unit_kerja_id')
            ->whereIn('laporan_unit_kerjas.laporan_imut_id', $laporanIds);

        if ($profileIds) {
            $query->whereIn('imut_penilaians.imut_profil_id', $profileIds);
        }

        $data = $query->get();

        return $data->groupBy(match ($groupBy) {
            'laporan_unit_kerjas.laporan_imut_id' => 'laporan_id',
            'imut_penilaians.imut_profil_id' => 'imut_profil_id',
            default => $groupBy,
        });
    }

    private function getLatestProfileIds(Collection $indikatorAktif): Collection
    {
        $imutDataIds = $indikatorAktif->pluck('id');

        $allProfiles = ImutProfile::whereIn('imut_data_id', $imutDataIds)
            ->orderByDesc('version')
            ->get()
            ->groupBy('imut_data_id');

        return $imutDataIds
            ->map(fn($dataId) => $allProfiles[$dataId]->first()?->id)
            ->filter()
            ->unique()
            ->values();
    }

    private function countTercapai(Collection $indikatorAktif, Collection $penilaianByProfile, int $laporanId): int
    {
        return $indikatorAktif->reduce(function (int $carry, $indikator) use ($penilaianByProfile, $laporanId) {
            // $profile = $indikator->profiles->sortByDesc('version')->first();
            $profile = $indikator->profile;

            if (! $profile) {
                return $carry;
            }

            $penilaians = $penilaianByProfile->get($profile->id, collect())
                ->filter(fn($p) => $p->laporanUnitKerja->laporan_imut_id === $laporanId);

            $tercapai = $penilaians->filter(fn($p) => $this->isTercapai($p, $profile))->count();

            return ($penilaians->count() > 0 && $tercapai / $penilaians->count() >= 1) ? $carry + 1 : $carry;
        }, 0);
    }

    private function countBelumDinilai(Collection $penilaians): int
    {
        return $penilaians->filter(
            fn($p) => ! is_null($p->numerator_value) &&
                ! is_null($p->denominator_value) &&
                is_null($p->recommendations)
        )->count();
    }
}
