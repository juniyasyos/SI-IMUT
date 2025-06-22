<?php

use App\Models\ImutCategory;
use App\Models\ImutData;
use App\Models\ImutProfile;
use App\Models\UnitKerja;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

describe('ImutData Model', function () {

    it('has correct fillable attributes', function () {
        $model = new ImutData;

        expect($model->getFillable())->toMatchArray([
            'title',
            'imut_kategori_id',
            'slug',
            'status',
            'created_by',
        ]);
    });

    it('casts status to boolean and deleted_at to datetime', function () {
        $imut = ImutData::factory()->create([
            'status' => 1,
            'deleted_at' => now(),
        ]);

        expect($imut->status)->toBeTrue();
        expect($imut->deleted_at)->not()->toBeNull();
    });

    it('generates slug from title when saving', function () {
        $imut = ImutData::factory()->create(['title' => 'Indikator Utama', 'slug' => null]);

        expect($imut->slug)->toBe(Str::slug('Indikator Utama'));
    });

    it('hides timestamps and deleted_at in serialization', function () {
        $imut = ImutData::factory()->create();
        $data = $imut->toArray();

        expect($data)->not()->toHaveKeys(['created_at', 'updated_at', 'deleted_at']);
    });

    it('clears cache on save and delete', function () {
        Cache::shouldReceive('forget')->atLeast()->times(3);

        $imut = ImutData::factory()->create();
        $imut->save();
        $imut->delete();
    });

    it('belongs to category', function () {
        $category = ImutCategory::factory()->create();
        $imut = ImutData::factory()->create(['imut_kategori_id' => $category->id]);

        expect($imut->categories)->toBeInstanceOf(ImutCategory::class);
    });

    it('has many profiles', function () {
        $imut = ImutData::factory()->create();
        ImutProfile::factory()->count(2)->create(['imut_data_id' => $imut->id]);

        expect($imut->profiles)->toHaveCount(2);
    });

    it('has many benchmarkings', function () {
        $imut = ImutData::factory()->create();
        $imut->benchmarkings()->create(['some_field' => 'data']);

        expect($imut->benchmarkings)->toHaveCount(1);
    });

    it('has many-to-many relation with unit kerja', function () {
        $imut = ImutData::factory()->create();
        $unit = UnitKerja::factory()->create();

        $imut->unitKerja()->attach($unit->id, ['assigned_by' => 1, 'assigned_at' => now()]);

        expect($imut->unitKerja)->toHaveCount(1);
    });

    it('has one latest profile', function () {
        $imut = ImutData::factory()->create();
        ImutProfile::factory()->create(['imut_data_id' => $imut->id, 'version' => 1]);
        ImutProfile::factory()->create(['imut_data_id' => $imut->id, 'version' => 5]);

        expect($imut->latestProfile)->version->toBe(5);
    });

    it('gets profile by id using profileById()', function () {
        $imut = ImutData::factory()->create();
        $profile = ImutProfile::factory()->create(['imut_data_id' => $imut->id]);

        $fetched = $imut->profileById($profile->id)->first();

        expect($fetched->id)->toBe($profile->id);
    });

    it('belongs to creator (User)', function () {
        $user = User::factory()->create();
        $imut = ImutData::factory()->create(['created_by' => $user->id]);

        expect($imut->creator)->toBeInstanceOf(User::class);
    });

    it('uses logAll for activity logging', function () {
        $imut = new ImutData;

        $logOptions = $imut->getActivitylogOptions();

        expect($logOptions->logUnguarded())->toBeTrue();
    });

});
