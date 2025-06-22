<?php

use BezhanSalleh\FilamentShield\Facades\FilamentShield;
use BezhanSalleh\FilamentShield\Resources\RoleResource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\assertDatabaseHas;

it('can check if the permission name can be configured using the closure', function () {
    $resource = RoleResource::class;

    FilamentShield::configurePermissionIdentifierUsing(
        fn ($resource) => str('Spatie\\Permission\\Models\\Role')
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
});
