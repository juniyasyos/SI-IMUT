<?php

use App\Models\ImutData;
use App\Models\ImutPenilaian;
use App\Models\ImutProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

describe('ImutProfile Model', function () {

    it('has correct fillable attributes', function () {
        $model = new ImutProfile;

        expect($model->getFillable())->toContain('slug', 'imut_data_id', 'version', 'rationale');
    });

    it('casts attributes correctly', function () {
        $profile = ImutProfile::factory()->create([
            'target_value' => '15',
            'analysis_period_value' => '30',
        ]);

        expect($profile->target_value)->toBeInt();
        expect($profile->analysis_period_value)->toBeInt();
    });

    it('hides timestamps in serialization', function () {
        $profile = ImutProfile::factory()->create();
        $array = $profile->toArray();

        expect($array)->not()->toHaveKeys(['created_at', 'updated_at', 'deleted_at']);
    });

    it('generates slug automatically if empty or version changes', function () {
        $profile = ImutProfile::factory()->create(['slug' => null, 'version' => 'v1']);

        expect($profile->slug)->toContain(Str::slug('v1'));
        expect($profile->slug)->toContain('-'); // UUID
    });

    it('belongs to ImutData', function () {
        $data = ImutData::factory()->create();
        $profile = ImutProfile::factory()->create(['imut_data_id' => $data->id]);

        expect($profile->imutData)->toBeInstanceOf(ImutData::class);
    });

    it('has many penilaian', function () {
        $profile = ImutProfile::factory()->create();
        ImutPenilaian::factory()->count(2)->create(['imut_profil_id' => $profile->id]);

        expect($profile->penilaian)->toHaveCount(2);
    });

    it('filters penilaian correctly with penilaianFiltered()', function () {
        $profile = ImutProfile::factory()->create();
        $penilaian = ImutPenilaian::factory()->create([
            'imut_profil_id' => $profile->id,
            'numerator_value' => 10,
            'denominator_value' => 20,
        ]);

        $laporan = $penilaian->laporanUnitKerja->laporan_imut_id;

        $filtered = $profile->penilaianFiltered($laporan)->get();

        expect($filtered)->toHaveCount(1);
    });

    it('returns correct label for indicator type', function () {
        $profile = ImutProfile::factory()->create(['indicator_type' => 'process']);
        expect($profile->indicator_type_label)->toBe('Proses');

        $profile->indicator_type = 'output';
        expect($profile->indicator_type_label)->toBe('Hasil (Output)');

        $profile->indicator_type = 'outcome';
        expect($profile->indicator_type_label)->toBe('Dampak (Outcome)');

        $profile->indicator_type = 'unknown';
        expect($profile->indicator_type_label)->toBe('Tidak diketahui');
    });

    it('can be queried by indicator type using scope', function () {
        ImutProfile::factory()->create(['indicator_type' => 'process']);
        ImutProfile::factory()->create(['indicator_type' => 'output']);

        $result = ImutProfile::ofIndicatorType('process')->get();

        expect($result)->toHaveCount(1);
        expect($result->first()->indicator_type)->toBe('process');
    });

});