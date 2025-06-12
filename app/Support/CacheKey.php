<?php

namespace App\Support;

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

    public static function dashboardSiimutAllChartData(): string
    {
        return 'dashboard:siimut:all_chart_data';
    }

    public static function imutPenilaian(int $imutDataId, int $year): string
    {
        return "imut:penilaian:{$imutDataId}:{$year}";
    }

    public static function imutBenchmarking(int $year, array|int|null $regionTypeId = null): string
    {
        $idPart = is_array($regionTypeId)
            ? implode(',', $regionTypeId)
            : ($regionTypeId ?? 'all');

        return "imut:benchmarking:{$year}:{$idPart}";
    }
}
