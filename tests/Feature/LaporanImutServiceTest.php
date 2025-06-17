<?php

use App\Facades\LaporanImut as LaporanImutFacade;
use App\Models\LaporanImut;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
});

function benchmark(string $functionName, callable $callback): void
{
    DB::flushQueryLog();
    DB::enableQueryLog();

    // Inisialisasi counter model
    $totalModelCount = 0;
    $modelTypes = [];

    // Listener global model retrieved
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
        'execution_time' => round($duration, 4).'s',
    ]);

    expect($result)->not()->toBeNull();
}

/**
 * Menghitung jumlah instance model Eloquent yang sedang ada di memory.
 * Menggunakan Reflection ke Eloquent\Model::$instances.
 */
function getLoadedModelCount(): int
{
    $total = 0;
    foreach (get_declared_classes() as $class) {
        if (is_subclass_of($class, \Illuminate\Database\Eloquent\Model::class)) {
            $instances = (new \ReflectionClass($class))->getStaticProperties()['instances'] ?? null;
            if (is_array($instances)) {
                $total += count($instances);
            }
        }
    }

    return $total;
}

test('benchmark getCurrentLaporanData with real data', function () {
    $laporan = LaporanImut::where('status', LaporanImut::STATUS_COMPLETE)->first();

    if (! $laporan) {
        $this->markTestSkipped('Tidak ada laporan dengan status COMPLETE di database.');
    }

    benchmark('getCurrentLaporanData', function () use ($laporan) {
        return LaporanImutFacade::getCurrentLaporanData($laporan);
    });
});

test('benchmark getChartDataForLastLaporan with real data', function () {
    benchmark('getChartDataForLastLaporan', function () {
        return LaporanImutFacade::getChartDataForLastLaporan(6);
    });
});

test('benchmark getPenilaianGroupedByProfile with real data', function () {
    $laporan = LaporanImut::first();

    if (! $laporan) {
        $this->markTestSkipped('Tidak ada laporan di database.');
    }

    benchmark('getPenilaianGroupedByProfile', function () use ($laporan) {
        return LaporanImutFacade::getPenilaianGroupedByProfile($laporan->id);
    });
});

test('benchmark getLaporanList with real data', function () {
    benchmark('getLaporanList', function () {
        return LaporanImutFacade::getLaporanList();
    });
});
