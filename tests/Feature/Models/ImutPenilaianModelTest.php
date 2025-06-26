<?php

use App\Models\ImutPenilaian;
use App\Models\ImutProfile;
use App\Models\LaporanUnitKerja;
use App\Models\UnitKerja;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('has correct fillable fields', function () {
    $model = new ImutPenilaian;

    expect($model->getFillable())->toMatchArray([
        'imut_profil_id',
        'laporan_unit_kerja_id',
        'analysis',
        'recommendations',
        'numerator_value',
        'denominator_value',
    ]);
});

it('hides created_at, updated_at when serialized', function () {
    $penilaian = ImutPenilaian::factory()->create();
    $array = $penilaian->toArray();

    expect($array)->not()->toHaveKeys(['created_at', 'updated_at']);
});

it('has relationship to profile, laporanUnitKerja, and unitKerja', function () {
    $profile = ImutProfile::factory()->create();
    $laporan = LaporanUnitKerja::factory()->create();
    $unit = UnitKerja::factory()->create();

    $penilaian = ImutPenilaian::factory()->create([
        'imut_profil_id' => $profile->id,
        'laporan_unit_kerja_id' => $laporan->id,
    ]);

    expect($penilaian->profile)->toBeInstanceOf(ImutProfile::class);
    expect($penilaian->laporanUnitKerja)->toBeInstanceOf(LaporanUnitKerja::class);
});

it('can call clearCache without error', function () {
    $penilaian = ImutPenilaian::factory()->create();
    $penilaian->clearCache();
    expect(true)->toBeTrue();
});
