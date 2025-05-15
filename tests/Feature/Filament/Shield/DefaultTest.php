<?php

use BezhanSalleh\FilamentShield\Facades\FilamentShield;
use BezhanSalleh\FilamentShield\Resources\RoleResource;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\assertDatabaseHas;

it('can check if package testing is configured', function () {
    expect(true)->toBeTrue();
});

it('can check if the permission name can be configured using the closure', function () {
    $resource = RoleResource::class;

    FilamentShield::configurePermissionIdentifierUsing(
        fn($resource) => str('Spatie\\Permission\\Models\\Role')
            ->afterLast('\\')
            ->lower()
            ->toString()
    );

    expect(FilamentShield::getPermissionIdentifier($resource))->toBe('role');
});

uses(RefreshDatabase::class);

// Test Seeder Berjalan dan Role/Permission Terbentuk
it('seeds roles and permissions correctly', function () {
    $this->seed(\Database\Seeders\ShieldSeeder::class);

    // Role checks
    expect(Role::where('name', 'Tim Mutu')->exists())->toBeTrue();
    expect(Role::where('name', 'Unit Kerja')->exists())->toBeTrue();

    // Permission check
    expect(Permission::where('name', 'view_user')->exists())->toBeTrue();
    expect(Permission::where('name', 'create_media')->exists())->toBeTrue();

    // Database assertion
    assertDatabaseHas('roles', ['name' => 'Tim Mutu']);
    assertDatabaseHas('permissions', ['name' => 'delete_any_backup']);
});

// Test User Bisa Diberikan Role dan Mewarisi Permissions
it('can assign role to user and access permission', function () {
    $this->seed(\Database\Seeders\ShieldSeeder::class);

    $user = User::factory()->create();
    $user->assignRole('Tim Mutu');

    expect($user->hasRole('Tim Mutu'))->toBeTrue();
    expect($user->can('view_user'))->toBeTrue();
    expect($user->can('create_user'))->toBeTrue();
    expect($user->can('delete_any_user'))->toBeTrue();
    expect($user->can('force_delete_user'))->toBeFalse(); // tidak diset di seeder
});

// Test Role Tidak Mewarisi Permission yang Tidak Ditentukan
it('does not have undefined permission', function () {
    $this->seed(\Database\Seeders\ShieldSeeder::class);

    $user = User::factory()->create();
    $user->assignRole('Unit Kerja');

    expect($user->can('view_user'))->toBeFalse();
    expect($user->can('page_MyProfilePage'))->toBeTrue();
});
