<?php

use App\Models\ImutBenchmarking;
use App\Models\ImutCategory;
use App\Models\ImutData;
use App\Models\ImutProfile;
use App\Models\RegionType;
use App\Models\UnitKerja;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

describe('ImutData Model', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->category = ImutCategory::factory()->create();
    });

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
            'created_by' => $this->user->id,
            'imut_kategori_id' => $this->category->id,
        ]);

        expect($imut->status)->toBeTrue();
        expect($imut->deleted_at)->not()->toBeNull();
    });

    it('generates slug from title when saving', function () {
        $imut = ImutData::factory()->create([
            'title' => 'Indikator Utama',
            'slug' => null,
            'created_by' => $this->user->id,
            'imut_kategori_id' => $this->category->id,
        ]);

        expect($imut->slug)->toBe(Str::slug('Indikator Utama'));
    });

    it('hides timestamps and deleted_at in serialization', function () {
        $imut = ImutData::factory()->create([
            'created_by' => $this->user->id,
            'imut_kategori_id' => $this->category->id,
        ]);

        $data = $imut->toArray();
        expect($data)->not()->toHaveKeys(['created_at', 'updated_at', 'deleted_at']);
    });

    it('clears cache on save and delete', function () {
        Cache::shouldReceive('forget')->atLeast()->times(3);

        $imut = ImutData::factory()->create([
            'created_by' => $this->user->id,
            'imut_kategori_id' => $this->category->id,
        ]);
        $imut->save();
        $imut->delete();
    });

    it('belongs to category', function () {
        $imut = ImutData::factory()->create([
            'imut_kategori_id' => $this->category->id,
            'created_by' => $this->user->id,
        ]);

        expect($imut->categories)->toBeInstanceOf(ImutCategory::class);
    });

    it('has many profiles', function () {
        $imut = ImutData::factory()->create([
            'created_by' => $this->user->id,
            'imut_kategori_id' => $this->category->id,
        ]);

        ImutProfile::factory()->count(2)->create(['imut_data_id' => $imut->id, 'version' => 'versi 01']);

        expect($imut->profiles)->toHaveCount(2);
    });

    it('has many benchmarkings', function () {
        $imut = ImutData::factory()->create([
            'created_by' => $this->user->id,
            'imut_kategori_id' => $this->category->id,
        ]);

        $benchmarking = ImutBenchmarking::factory()->create([
            'region_type_id' => RegionType::factory()->create()->id,
            'imut_data_id' => $imut->id,
        ]);
        expect($imut->benchmarkings)->toHaveCount(1);
    });

    it('has many-to-many relation with unit kerja', function () {
        $imut = ImutData::factory()->create([
            'created_by' => $this->user->id,
            'imut_kategori_id' => $this->category->id,
        ]);

        $unit = UnitKerja::factory()->create();
        $imut->unitKerja()->attach($unit->id, [
            'assigned_by' => $this->user->id,
            'assigned_at' => now(),
        ]);

        expect($imut->unitKerja)->toHaveCount(1);
    });

    it('has one latest profile', function () {
        $imut = ImutData::factory()->create([
            'created_by' => $this->user->id,
            'imut_kategori_id' => $this->category->id,
        ]);

        ImutProfile::factory()->create(['imut_data_id' => $imut->id, 'version' => 'version 1']);
        ImutProfile::factory()->create(['imut_data_id' => $imut->id, 'version' => 'version 5']);

        expect($imut->latestProfile)->version->toBe('version 5');
    });

    // it('gets profile by id using profileById()', function () {
    //     $imut = ImutData::factory()->create([
    //         'created_by' => $this->user->id,
    //         'imut_kategori_id' => $this->category->id,
    //     ]);

    //     $profile = ImutProfile::factory(2)->create(['imut_data_id' => $imut->id, 'version' => 'versi 01']);

    //     $fetched = $imut->profileById($profile->id)->first();
    //     expect($fetched->id)->toBe($profile->id);
    // });

    it('belongs to creator (User)', function () {
        $imut = ImutData::factory()->create([
            'created_by' => $this->user->id,
            'imut_kategori_id' => $this->category->id,
        ]);

        expect($imut->creator)->toBeInstanceOf(User::class);
    });
});
