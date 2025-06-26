<?php

use App\Models\UnitKerja;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Juniyasyos\FilamentMediaManager\Models\Folder;

uses(RefreshDatabase::class);

// Test 1 – Setup dan Permission Role Tim Mutu
it('verifies that Tim Mutu role has all required folder and media permissions', function () {
    $this->seed(\Database\Seeders\ShieldSeeder::class);

    $user = User::factory()->create();
    $user->assignRole('Tim Mutu');

    $permissions = [
        'view_any_folder::custom',
        'view_all_folder::custom',
        'view_folder::custom',
        'create_folder::custom',
        'update_folder::custom',
        'delete_folder::custom',
        'view_all_media::custom',
        'view_media::custom',
        'create_media::custom',
        'update_media::custom',
    ];

    foreach ($permissions as $permission) {
        expect($user->can($permission))->toBeTrue("Tim Mutu tidak memiliki permission: {$permission}");
    }
});

// Test 2 – Folder Otomatis Dibuat untuk Tiap Unit Kerja
it('automatically creates folders when Unit Kerja is created', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $unitKerja = UnitKerja::factory()->create(['unit_name' => 'Laboratorium Mikrobiologi']);
    $expectedSlug = Str::slug($unitKerja->unit_name);

    $folder = Folder::where('collection', $expectedSlug)->first();

    expect($folder)->not->toBeNull('Folder tidak dibuat secara otomatis');
    expect($folder->name)->toBe($expectedSlug);
    expect($folder->description)->toContain($unitKerja->unit_name);
});

// Test 3 – Tim Mutu Bisa Melihat Semua Folder Unit Kerja
it('allows Tim Mutu to view all folders from any unit kerja', function () {
    $this->seed(\Database\Seeders\ShieldSeeder::class);

    $user = User::factory()->create();
    $user->assignRole('Tim Mutu');
    $this->actingAs($user);

    $units = UnitKerja::factory()->count(3)->sequence(
        ['unit_name' => 'IGD'],
        ['unit_name' => 'Radiologi'],
        ['unit_name' => 'Farmasi']
    )->create();

    foreach ($units as $unit) {
        $slug = Str::slug($unit->unit_name);
        $folder = Folder::where('collection', $slug)->first();
        expect($folder)->not->toBeNull("Folder tidak ditemukan untuk: {$unit->unit_name}");
        expect($user->can('view_all_folder::custom'))->toBeTrue("Tim Mutu tidak bisa lihat folder {$folder->name}");
    }
});

// Test 4 – Tim Mutu Dapat Membuat Folder Sendiri
it('allows Tim Mutu to create a new folder manually', function () {
    $this->seed(\Database\Seeders\ShieldSeeder::class);
    $user = User::factory()->create();
    $user->assignRole('Tim Mutu');
    $this->actingAs($user);

    $folder = Folder::create([
        'name' => 'laporan-mutakhir',
        'collection' => 'mutakhir',
        'description' => 'Folder uji coba tim mutu',
        'user_id' => $user->id,
        'user_type' => get_class($user),
    ]);

    expect($folder)->not->toBeNull();
    expect($folder->collection)->toBe('mutakhir');
});

// Test 5 – Tim Mutu Bisa Update dan Delete Folder
it('allows Tim Mutu to update and delete folders', function () {
    $this->seed(\Database\Seeders\ShieldSeeder::class);
    $user = User::factory()->create();
    $user->assignRole('Tim Mutu');
    $this->actingAs($user);

    $folder = Folder::create([
        'name' => 'test-folder',
        'collection' => 'test-folder',
        'description' => 'Untuk pengujian update/delete',
        'user_id' => $user->id,
        'user_type' => get_class($user),
    ]);

    // Update
    $folder->update(['description' => 'Updated by Tim Mutu']);
    expect($folder->description)->toBe('Updated by Tim Mutu');

    // Delete
    $deleted = $folder->delete();
    expect($deleted)->toBeTrue();
});
