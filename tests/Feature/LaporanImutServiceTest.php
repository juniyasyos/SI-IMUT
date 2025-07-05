<?php

use App\Facades\LaporanImut as LaporanImutFacade;
use App\Models\LaporanImut;
use Database\Seeders\DatabaseProductionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

beforeEach(function () {
    Cache::flush();
    $this->seed(DatabaseProductionSeeder::class);
});

function benchmark(string $functionName, callable $callback): void
{
    DB::flushQueryLog();
    DB::enableQueryLog();

    $totalModelCount = 0;
    $modelTypes = [];

    Event::listen('eloquent.retrieved: *', function ($eventName, $data) use (&$totalModelCount, &$modelTypes) {
        $model = $data[0] ?? null;
        if ($model instanceof \Illuminate\Database\Eloquent\Model) {
            $totalModelCount++;
            $class = get_class($model);
            $modelTypes[$class] = ($modelTypes[$class] ?? 0) + 1;
        }
    });

    $start = microtime(true);
    $result = $callback();
    $duration = microtime(true) - $start;

    dump([
        'function' => $functionName,
        'query_count' => count(DB::getQueryLog()),
        'total_model_count' => $totalModelCount,
        'model_types' => $modelTypes,
        'execution_time' => round($duration, 4) . 's',
    ]);

    expect($result)->not()->toBeNull();
}

// ===============================================
// BENCHMARKED TESTS
// ===============================================

test('benchmark getCurrentLaporanData with and without cache', function () {
    $laporan = LaporanImut::where('status', LaporanImut::STATUS_COMPLETE)->first();

    if (! $laporan) {
        $this->markTestSkipped('Tidak ada laporan dengan status COMPLETE di database.');
    }

    // Pertama: tanpa cache
    benchmark('getCurrentLaporanData - first run (no cache)', fn() => LaporanImutFacade::getCurrentLaporanData($laporan));

    // Kedua: dengan cache
    benchmark('getCurrentLaporanData - second run (cached)', fn() => LaporanImutFacade::getCurrentLaporanData($laporan));
});

test('benchmark getChartDataForLastLaporan with and without cache', function () {
    // Pertama
    benchmark('getChartDataForLastLaporan - first run (no cache)', fn() => LaporanImutFacade::getChartDataForLastLaporan(6));

    // Kedua
    benchmark('getChartDataForLastLaporan - second run (cached)', fn() => LaporanImutFacade::getChartDataForLastLaporan(6));
});

test('benchmark getPenilaianGroupedByProfile with and without cache', function () {
    $laporan = LaporanImut::first();

    if (! $laporan) {
        $this->markTestSkipped('Tidak ada laporan di database.');
    }

    benchmark('getPenilaianGroupedByProfile - first run (no cache)', fn() => LaporanImutFacade::getPenilaianGroupedByProfile($laporan->id));

    benchmark('getPenilaianGroupedByProfile - second run (cached)', fn() => LaporanImutFacade::getPenilaianGroupedByProfile($laporan->id));
});

test('benchmark getLaporanList with and without cache', function () {
    benchmark('getLaporanList - first run (no cache)', fn() => LaporanImutFacade::getLaporanList());

    benchmark('getLaporanList - second run (cached)', fn() => LaporanImutFacade::getLaporanList());
});