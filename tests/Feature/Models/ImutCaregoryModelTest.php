<?php

use App\Models\ImutCategory;
use App\Models\ImutData;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('ImutCategory Model', function () {

    it('has the correct fillable attributes', function () {
        $category = new ImutCategory;

        expect($category->getFillable())->toMatchArray([
            'category_name',
            'scope',
            'short_name',
            'description',
            'is_use_global',
            'is_benchmark_category',
        ]);
    });

    it('hides specific attributes when serialized', function () {
        $category = ImutCategory::factory()->create();

        $array = $category->toArray();

        expect($array)->not()->toHaveKeys(['created_at', 'updated_at', 'deleted_at']);
    });

    it('casts deleted_at as datetime', function () {
        $category = ImutCategory::factory()->create([
            'deleted_at' => now(),
        ]);

        expect($category->deleted_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
    });

    it('has many imutData', function () {
        $user = User::factory()->create();

        $category = ImutCategory::factory()->create();
        $data1 = ImutData::factory()->create(['imut_kategori_id' => $category->id, 'created_by' => $user->id]);
        $data2 = ImutData::factory()->create(['imut_kategori_id' => $category->id, 'created_by' => $user->id]);

        expect($category->imutData)->toHaveCount(2);
        expect($category->imutData->first())->toBeInstanceOf(ImutData::class);
    });

});