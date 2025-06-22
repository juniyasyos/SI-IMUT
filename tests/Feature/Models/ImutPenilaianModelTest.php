<?php

use App\Models\ImutPenilaian;
use App\Models\ImutProfile;
use App\Models\LaporanUnitKerja;
use App\Models\UnitKerja;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('ImutPenilaian Model', function () {

    it('has fillable attributes', function () {
        $penilaian = new ImutPenilaian;

        expect($penilaian->getFillable())->toMatchArray([
            'imut_profil_id',
            'laporan_unit_kerja_id',
            'analysis',
            'recommendations',
            'numerator_value',
            'denominator_value',
        ]);
    });

    it('casts attributes correctly', function () {
        $penilaian = ImutPenilaian::factory()->create([
            'deleted_at' => now(),
        ]);

        expect($penilaian->deleted_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
    });

    it('hides timestamps in serialization', function () {
        $penilaian = ImutPenilaian::factory()->create();

        $array = $penilaian->toArray();
        expect($array)->not()->toHaveKeys(['created_at', 'updated_at', 'deleted_at']);
    });

    it('has correct activity log options', function () {
        $model = new ImutPenilaian;
        $options = $model->getActivitylogOptions();

        expect($options->logUnguarded())->toBeTrue();
    });

    it('belongs to imut profile', function () {
        $profile = ImutProfile::factory()->create();
        $penilaian = ImutPenilaian::factory()->create(['imut_profil_id' => $profile->id]);

        expect($penilaian->profile)->toBeInstanceOf(ImutProfile::class);
    });

    it('belongs to laporan unit kerja', function () {
        $laporan = LaporanUnitKerja::factory()->create();
        $penilaian = ImutPenilaian::factory()->create(['laporan_unit_kerja_id' => $laporan->id]);

        expect($penilaian->laporanUnitKerja)->toBeInstanceOf(LaporanUnitKerja::class);
    });

    it('belongs to unit kerja', function () {
        $unit = UnitKerja::factory()->create();
        $penilaian = ImutPenilaian::factory()->create(['unit_kerja_id' => $unit->id]);

        expect($penilaian->unitKerja)->toBeInstanceOf(UnitKerja::class);
    });

    it('has latestProfile and profileById relationships', function () {
        $penilaian = ImutPenilaian::factory()->create();
        expect(method_exists($penilaian, 'latestProfile'))->toBeTrue();
        expect(method_exists($penilaian, 'profileById'))->toBeTrue();
    });

    it('registers media collections', function () {
        $penilaian = new ImutPenilaian;
        $penilaian->registerMediaCollections();

        expect($penilaian->mediaCollections)->toHaveCount(1);
        expect($penilaian->mediaCollections[0]->name)->toBe('documents');
    });

    it('calls clearCache without error', function () {
        $penilaian = ImutPenilaian::factory()->create();

        // Not testing actual cache logic, just ensures it doesn't throw
        $penilaian->clearCache();
        expect(true)->toBeTrue();
    });

});
