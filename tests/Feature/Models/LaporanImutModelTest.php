<?php

use App\Models\LaporanImut;
use App\Models\UnitKerja;
use App\Models\User;
use App\Models\LaporanUnitKerja;
use App\Models\ImutPenilaian;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

describe('LaporanImut Model', function () {

    it('has correct fillable fields', function () {
        $model = new LaporanImut();

        expect($model->getFillable())->toMatchArray([
            'name',
            'status',
            'assessment_period_start',
            'assessment_period_end',
            'created_by',
        ]);
    });

    it('hides created_at, updated_at, and deleted_at in serialization', function () {
        $model = LaporanImut::factory()->create();

        $array = $model->toArray();

        expect($array)->not()->toHaveKeys(['created_at', 'updated_at', 'deleted_at']);
    });

    it('casts date fields and soft deletes', function () {
        $model = LaporanImut::factory()->create([
            'assessment_period_start' => now(),
            'assessment_period_end' => now()->addDay(),
            'deleted_at' => now(),
        ]);

        expect($model->assessment_period_start)->toBeInstanceOf(Carbon::class);
        expect($model->deleted_at)->toBeInstanceOf(Carbon::class);
    });

    it('generates slug when creating if missing', function () {
        $model = LaporanImut::factory()->create([
            'name' => 'Evaluasi Tahunan',
            'slug' => null,
        ]);

        expect($model->slug)->toBe(Str::slug('Evaluasi Tahunan'));
    });

    it('belongs to createdBy (User)', function () {
        $user = User::factory()->create();
        $laporan = LaporanImut::factory()->create(['created_by' => $user->id]);

        expect($laporan->createdBy)->toBeInstanceOf(User::class);
    });

    it('has many unitKerjas', function () {
        $unit1 = UnitKerja::factory()->create();
        $unit2 = UnitKerja::factory()->create();
        $laporan = LaporanImut::factory()->create();

        $laporan->unitKerjas()->attach([$unit1->id, $unit2->id]);

        expect($laporan->unitKerjas)->toHaveCount(2);
    });

    it('has many laporanUnitKerjas', function () {
        $laporan = LaporanImut::factory()->create();
        LaporanUnitKerja::factory()->count(2)->create(['laporan_imut_id' => $laporan->id]);

        expect($laporan->laporanUnitKerjas)->toHaveCount(2);
    });

    it('has many imutPenilaians through laporanUnitKerja', function () {
        $laporan = LaporanImut::factory()->create();
        $laporanUnit = LaporanUnitKerja::factory()->create(['laporan_imut_id' => $laporan->id]);
        ImutPenilaian::factory()->create(['laporan_unit_kerja_id' => $laporanUnit->id]);

        expect($laporan->imutPenilaians)->toHaveCount(1);
    });

    it('automatically sets status to complete when assessment period ends', function () {
        $laporan = LaporanImut::factory()->create([
            'status' => LaporanImut::STATUS_PROCESS,
            'assessment_period_end' => now()->subDay(),
        ]);

        expect($laporan->status)->toBe(LaporanImut::STATUS_COMPLETE);
        $laporan->refresh();
        expect($laporan->status)->toBe(LaporanImut::STATUS_COMPLETE);
    });
});
