<?php

namespace App\Services;

use App\Models\ImutData;
use App\Models\ImutPenilaian;
use App\Models\LaporanImut;
use Illuminate\Support\Facades\Cache;

class LaporanImutService
{
    public function getLatestLaporan(): ?LaporanImut
    {
        return Cache::remember('latest_laporan', now()->addMinutes(30), function () {
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
     * Menghasilkan data chart untuk beberapa laporan terakhir.
     *
     * @param int $limit jumlah laporan yang ingin diambil
     * @return array data chart yang terdiri dari tercapai, unit melapor, dan belum dinilai
     */
    public function getChartDataForLastLaporan(int $limit = 6): array
    {
        $cacheKey = "dashboard_siimut_all_chart_data";

        return Cache::remember($cacheKey, now()->addDays(7), function () use ($limit) {
            $laporanList = LaporanImut::orderBy('assessment_period_start', 'desc')
                ->limit($limit)
                ->with('unitKerjas')
                ->get();

            // Jika jumlah laporan kurang dari limit, ambil tambahan laporan dengan status bukan proses
            if ($laporanList->count() < $limit) {
                $additional = LaporanImut::where('status', '!=', LaporanImut::STATUS_PROCESS)
                    ->orderBy('assessment_period_start', 'desc')
                    ->limit($limit - $laporanList->count())
                    ->with('unitKerjas')
                    ->get();
                $laporanList = $laporanList->concat($additional);
            }

            $laporanList = $laporanList->sortBy('assessment_period_start');
            $laporanIds = $laporanList->pluck('id')->toArray();

            // Ambil semua penilaian terkait laporan
            $allPenilaians = ImutPenilaian::whereHas('laporanUnitKerja', fn($q) => $q->whereIn('laporan_imut_id', $laporanIds))
                ->with(['profile', 'laporanUnitKerja'])
                ->get()
                ->groupBy('laporanUnitKerja.laporan_imut_id');

            // Ambil indikator aktif (status true) yang terkait laporan
            $indikatorAktif = ImutData::where('status', true)
                ->whereHas('profiles.penilaian.laporanUnitKerja', fn($q) => $q->whereIn('laporan_imut_id', $laporanIds))
                ->with([
                    'profiles' => fn($q) => $q->latest('version')->take(1),
                    'profiles.penilaian' => fn($q) => $q->whereHas('laporanUnitKerja', fn($q) => $q->whereIn('laporan_imut_id', $laporanIds)),
                ])
                ->get();

            // Ambil ID profil indikator terbaru
            $profileIds = $indikatorAktif->flatMap(function ($indikator) {
                $profile = $indikator->profiles->sortByDesc('version')->first();
                return $profile ? [$profile->id] : [];
            })->unique();

            // Ambil penilaian berdasarkan profil indikator terbaru
            $penilaiansByProfile = ImutPenilaian::whereIn('imut_profil_id', $profileIds)
                ->whereHas('laporanUnitKerja', fn($q) => $q->whereIn('laporan_imut_id', $laporanIds))
                ->with('profile')
                ->get()
                ->groupBy('imut_profil_id');

            $result = [
                'tercapai' => [],
                'unitMelapor' => [],
                'belumDinilai' => [],
            ];

            // Loop tiap laporan untuk hitung data chart
            foreach ($laporanList as $laporan) {
                $currentPenilaians = $allPenilaians->get($laporan->id, collect());

                $indikatorTercapai = 0;

                foreach ($indikatorAktif as $indikator) {
                    $profile = $indikator->profiles->sortByDesc('version')->first();
                    if (!$profile) continue;

                    // Filter penilaian berdasarkan laporan saat ini
                    $penilaians = $penilaiansByProfile->get($profile->id, collect())
                        ->filter(fn($p) => $p->laporanUnitKerja->laporan_imut_id === $laporan->id);

                    if ($penilaians->isEmpty()) continue;

                    // Hitung jumlah indikator tercapai berdasarkan kriteria target operator dan nilai
                    $tercapai = $penilaians->filter(function ($p) use ($profile) {
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
                    })->count();

                    // Jika minimal 80% indikator tercapai, hitung sebagai indikator tercapai
                    if ($tercapai / $penilaians->count() >= 0.8) {
                        $indikatorTercapai++;
                    }
                }

                $result['tercapai'][] = $indikatorTercapai;
                $result['unitMelapor'][] = $currentPenilaians->pluck('laporanUnitKerja.unit_kerja_id')->unique()->count();
                $result['belumDinilai'][] = $currentPenilaians->filter(
                    fn($p) => !is_null($p->numerator_value) && !is_null($p->denominator_value) && is_null($p->recommendations)
                )->count();
            }

            return $result;
        });
    }

    /**
     * Ambil data ringkas untuk laporan terbaru (periode terbaru).
     *
     * @return array|null
     */
    public function getCurrentLaporanData(): ?array
    {
        $laporanTerbaru = LaporanImut::latest('periode')->first();

        if (!$laporanTerbaru) {
            return null;
        }

        $indikator = $laporanTerbaru->indikator;
        $totalIndikator = $indikator->count();
        $tercapai = $indikator->where('status', 'tercapai')->count();
        $belumDinilai = $indikator->whereNull('status')->count();

        $unitMelapor = $laporanTerbaru->unit()->whereHas('laporan')->count();
        $totalUnit = \App\Models\UnitKerja::count();

        return [
            'totalIndikator' => $totalIndikator,
            'tercapai' => $tercapai,
            'belumDinilai' => $belumDinilai,
            'unitMelapor' => $unitMelapor,
            'totalUnit' => $totalUnit,
        ];
    }
}
