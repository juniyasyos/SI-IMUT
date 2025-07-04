<?php

namespace App\Support;

use App\Facades\LaporanImut;

class CacheKey
{
    public static function laporanImutDetail(int $laporanId, int $imutDataId): string
    {
        return "laporan:imut:detail:{$laporanId}:imut_data:{$imutDataId}";
    }

    public static function laporanUnitDetail(int $laporanId, int $unitKerjaId): string
    {
        return "laporan:imut:detail:{$laporanId}:unit:{$unitKerjaId}";
    }

    public static function imutLaporans(): string
    {
        return 'imut:laporans';
    }

    public static function dashboardSiimutAllData(int $laporanId): string
    {
        return "dashboard:siimut:all_data:{$laporanId}";
    }

    public static function latestLaporan(): string
    {
        return 'laporan:latest';
    }

    public static function dashboardSiimutChartData(): string
    {
        return 'dashboard:siimut:chart_data_dashboard';
    }

    public static function imutPenilaian(int $imutDataId, int $year): string
    {
        return "imut:penilaian:{$imutDataId}:{$year}";
    }

    public static function imutBenchmarking(int $year, array|int|null $regionTypeId = null, array|int|null $imutDataId = null): string
    {
        $regionPart = is_array($regionTypeId)
            ? implode(',', $regionTypeId)
            : ($regionTypeId ?? 'all');

        $imutPart = is_array($imutDataId)
            ? implode(',', $imutDataId)
            : ($imutDataId ?? 'all');

        return "imut:benchmarking:{$year}:region:{$regionPart}:imut:{$imutPart}";
    }

    public static function imutPenilaianImutDataUnitKerja($imutDataId, $year, $unitKerjaId = null): string
    {
        return 'imut_penilaian_' . $imutDataId . '_' . $year . ($unitKerjaId ? '_uk_' . $unitKerjaId : '');
    }

    public static function recentLaporanList(int $limit = 6): string
    {
        return "laporan.recent_list.limit_{$limit}";
    }

    public static function penilaianGroupedByProfile(int $laporanId)
    {
        return 'penilaian_grouped_profile_' . $laporanId;
    }

    public static function laporanList(array $filters = [], ?int $limit = null): string
    {
        $keyData = [
            'filters' => $filters,
            'limit' => $limit,
        ];

        return 'laporan_list_' . md5(json_encode($keyData));
    }

    public static function getPenilaianStats(int $laporanId, bool $filterByUserUnit): string
    {
        return "penilaian_stats:laporan:{$laporanId}:filter_by_user_unit:" . ($filterByUserUnit ? 'yes' : 'no');
    }

    public static function imutLaporansForUnitKerjas(array $unitKerjaIds): string
    {
        sort($unitKerjaIds);
        $joinedIds = implode('_', $unitKerjaIds);
        return "imut_laporans_unit_kerjas_{$joinedIds}";
    }
}