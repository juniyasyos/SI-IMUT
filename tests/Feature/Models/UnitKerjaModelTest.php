<?php

use App\Models\ImutCategory;
use App\Models\ImutData;
use App\Models\UnitKerja;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

describe('UnitKerja Model', function () {

    it('has correct fillable attributes', function () {
        $unit = new UnitKerja;

        expect($unit->getFillable())->toMatchArray([
            'unit_name',
            'description',
            'slug',
        ]);
    });

    it('hides specific attributes when serialized', function () {
        $unit = UnitKerja::factory()->make();

        $array = $unit->toArray();

        expect($array)->not()->toHaveKey('created_at');
        expect($array)->not()->toHaveKey('updated_at');
        expect($array)->not()->toHaveKey('deleted_at');
    });

    it('casts deleted_at as datetime', function () {
        $unit = UnitKerja::factory()->create([
            'deleted_at' => now(),
        ]);

        expect($unit->deleted_at)->toBeInstanceOf(\Carbon\Carbon::class);
    });

    it('automatically generates slug from unit_name', function () {
        $unit = UnitKerja::factory()->create([
            'unit_name' => 'Unit Gawat Darurat',
        ]);

        expect($unit->slug)->toBe(Str::slug('Unit Gawat Darurat'));
    });

    it('uses slug as route key name', function () {
        $unit = new UnitKerja;

        expect($unit->getRouteKeyName())->toBe('slug');
    });

    it('has many users through pivot', function () {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $unit = UnitKerja::factory()->create();

        $unit->users()->attach([$user1->id, $user2->id]);

        expect($unit->users)->toHaveCount(2);
    });

    it('has many imutData through pivot with additional fields', function () {
        $user1 = User::factory()->create();
        $kategori = ImutCategory::factory()->create();

        $imut1 = ImutData::factory()->create([
            'created_by' => $user1->id,
            'imut_kategori_id' => $kategori->id,
        ]);

        $imut2 = ImutData::factory()->create([
            'created_by' => $user1->id,
            'imut_kategori_id' => $kategori->id,
        ]);

        $unit = UnitKerja::factory()->create();

        $unit->imutData()->attach([
            $imut1->id => ['assigned_by' => $user1->id, 'assigned_at' => now()],
            $imut2->id => ['assigned_by' => $user1->id, 'assigned_at' => now()],
        ]);

        $unit->refresh()->load('imutData');

        expect($unit->imutData)->toHaveCount(2);
    });
});
