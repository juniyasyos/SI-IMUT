<?php

use App\Models\UnitKerja;
use App\Models\User;
use BezhanSalleh\FilamentShield\Facades\FilamentShield;
use BezhanSalleh\FilamentShield\Resources\RoleResource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Juniyasyos\FilamentMediaManager\Models\Folder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\assertDatabaseHas;

it('can check if package testing is configured', function () {
    expect(true)->toBeTrue();
});

// Test 1: Validasi Role dan Semua Permission Terkait
it('creates Unit Kerja role with all expected permissions', function () {
    $this->seed(\Database\Seeders\ShieldSeeder::class);
    $role = Role::where('name', 'Unit Kerja')->first();
    expect($role)->not->toBeNull();

    $permissions = [
        // Halaman dan widget
        'page_MyProfilePage',
        'widget_UnitKerjaInfo',

        // Folder
        'create_folder::custom',
        'view_folder::custom',

        // Media
        'view_by_unit_kerja_media::custom',
        'create_media::custom',

        // IMUT Data
        'view_by_unit_kerja_imut::data',
        'force_delete_any_imut::data',
        'restore_any_imut::data',

        // Report
        'view_unit_kerja_report_detail_laporan::imut',
    ];

    foreach ($permissions as $permission) {
        expect(Permission::where('name', $permission)->exists())
            ->toBeTrue("Permission $permission tidak ditemukan di database");

        expect($role->permissions()->where('name', $permission)->exists())
            ->toBeTrue("Role Unit Kerja tidak memiliki permission $permission");
    }
});

// Test 2: User dengan Role Unit Kerja Memiliki dan Tidak Memiliki Permission Tertentu
it('assigns role to user and checks permission inheritance and restrictions', function () {
    $this->seed(\Database\Seeders\ShieldSeeder::class);
    $user = User::factory()->create();
    $user->assignRole('Unit Kerja');

    expect($user->hasRole('Unit Kerja'))->toBeTrue();

    // Harus bisa akses
    expect($user->can('page_MyProfilePage'))->toBeTrue();
    expect($user->can('create_media::custom'))->toBeTrue();

    // Tidak boleh bisa akses permission yang bukan miliknya
    expect($user->can('view_user'))->toBeFalse();
    expect($user->can('delete_any_user'))->toBeFalse();
    expect($user->can('page_AdminDashboard'))->toBeFalse(); // contoh permission admin
});

// Test 3: Interaksi Dunia Nyata – User Coba Akses Folder Milik Unit Kerja
it('automatically creates a folder when a UnitKerja is created', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $unitKerja = UnitKerja::factory()->create([
        'unit_name' => 'Laboratorium Mikrobiologi',
    ]);

    $expectedSlug = \Illuminate\Support\Str::slug($unitKerja->unit_name);

    // Ambil folder berdasarkan slug di 'collection'
    $folder = Folder::where('collection', $expectedSlug)->first();

    expect($folder)->not->toBeNull('Folder tidak ditemukan berdasarkan collection slug');
    expect($folder->name)->toBe($expectedSlug);
    expect($folder->description)->toContain($unitKerja->unit_name);
    expect($folder->user_id)->toBe($user->id);
});

// Test 3.2: Permission Folder for unit Kerja
it('allows Unit Kerja user to access their folder based on collection slug and permission', function () {
    // Seed roles & permissions
    $this->seed(\Database\Seeders\ShieldSeeder::class);

    // Buat user dan assign role
    $user = User::factory()->create();
    $this->actingAs($user);
    $user->assignRole('Unit Kerja');

    // Buat unit kerja dan attach ke user
    $unitKerja = UnitKerja::factory()->create([
        'unit_name' => 'Instalasi Gizi',
    ]);
    $unitKerja->users()->attach($user);

    // Ambil slug yang akan jadi collection folder
    $folderSlug = \Illuminate\Support\Str::slug($unitKerja->unit_name);

    // Ambil folder dari media manager berdasarkan collection
    $folder = Folder::where('collection', $folderSlug)->first();

    // Validasi folder memang dibuat oleh observer
    expect($folder)->not->toBeNull('Folder tidak ditemukan untuk Unit Kerja');
    expect($folder->collection)->toBe($folderSlug);

    // Validasi permission
    expect($user->can('view_by_unit_kerja_folder::custom'))->toBeTrue();

    // Simulasi business logic: boleh akses jika folder collection cocok dengan unit kerjanya
    $canAccessFolder = $unitKerja->slug === $folder->collection;

    expect($canAccessFolder)->toBeTrue('User tidak bisa akses folder karena slug tidak cocok');
});

// Test 4: User Tidak Bisa Create Folder Jika Tidak Ada Permission
it('prevents Unit Kerja user from creating folders if permission revoked', function () {
    $this->seed(\Database\Seeders\ShieldSeeder::class);
    $user = User::factory()->create();
    $user->assignRole('Unit Kerja');

    // Revoke permission untuk create_folder
    $user->getRoleNames()->each(function ($roleName) {
        $role = Role::findByName($roleName);
        $role->revokePermissionTo('create_folder::custom');
    });

    $user->refresh(); // refresh untuk sync permission cache
    expect($user->can('create_folder::custom'))->toBeFalse();
});

// Test 5: admin permission only
it('Unit Kerja role does not have admin-only permissions', function () {
    $this->seed(\Database\Seeders\ShieldSeeder::class);
    $user = User::factory()->create();
    $user->assignRole('Unit Kerja');

    $adminPermissions = [
        'delete_any_user',
        'force_delete_user',
        'create_role',
        'delete_role',
    ];

    foreach ($adminPermissions as $perm) {
        expect($user->can($perm))->toBeFalse("User Unit Kerja seharusnya tidak bisa akses $perm");
    }
});
