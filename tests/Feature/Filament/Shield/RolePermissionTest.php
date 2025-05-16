<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Buat semua permission terkait Role
    Permission::firstOrCreate(['name' => 'view_role']);
    Permission::firstOrCreate(['name' => 'view_any_role']);
    Permission::firstOrCreate(['name' => 'create_role']);
    Permission::firstOrCreate(['name' => 'update_role']);
    Permission::firstOrCreate(['name' => 'delete_role']);
    Permission::firstOrCreate(['name' => 'delete_any_role']);

    // Role Tim Mutu hanya boleh delete_any_role
    $timMutu = Role::firstOrCreate(['name' => 'Tim Mutu']);
    $timMutu->syncPermissions(['delete_any_role']);

    // Role Staff tidak ada hak akses Role
    Role::firstOrCreate(['name' => 'Staff']);

    // Role Admin IT: punya semua hak akses role
    $adminIT = Role::firstOrCreate(['name' => 'Admin IT']);
    $adminIT->syncPermissions([
        'view_role',
        'view_any_role',
        'create_role',
        'update_role',
        'delete_role',
        'delete_any_role',
    ]);
});

it('Tim Mutu can only delete any role', function () {
    $user = User::factory()->create();
    $user->assignRole('Tim Mutu');

    expect($user->can('delete_any_role'))->toBeTrue();
    expect($user->can('view_role'))->toBeFalse();
    expect($user->can('create_role'))->toBeFalse();
    expect($user->can('update_role'))->toBeFalse();
    expect($user->can('delete_role'))->toBeFalse();
});

it('Admin IT has full access to roles', function () {
    $user = User::factory()->create();
    $user->assignRole('Admin IT');

    expect($user->can('view_role'))->toBeTrue();
    expect($user->can('view_any_role'))->toBeTrue();
    expect($user->can('create_role'))->toBeTrue();
    expect($user->can('update_role'))->toBeTrue();
    expect($user->can('delete_role'))->toBeTrue();
    expect($user->can('delete_any_role'))->toBeTrue();
});

it('Staff has no access to role management', function () {
    $user = User::factory()->create();
    $user->assignRole('Staff');

    expect($user->can('view_role'))->toBeFalse();
    expect($user->can('update_role'))->toBeFalse();
    expect($user->can('delete_role'))->toBeFalse();
});

it('User with no role cannot manage roles', function () {
    $user = User::factory()->create();

    expect($user->can('create_role'))->toBeFalse();
    expect($user->can('view_any_role'))->toBeFalse();
});

it('User with direct permission can manage roles', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(['create_role', 'view_any_role']);

    expect($user->can('create_role'))->toBeTrue();
    expect($user->can('view_any_role'))->toBeTrue();
    expect($user->can('update_role'))->toBeFalse();
});

it('User with multiple roles combines permissions', function () {
    $user = User::factory()->create();
    $user->assignRole('Tim Mutu');
    $user->assignRole('Admin IT');

    expect($user->can('view_role'))->toBeTrue();
    expect($user->can('delete_any_role'))->toBeTrue();
    expect($user->can('update_role'))->toBeTrue();
});

it('Revoking role removes access to role permissions', function () {
    $user = User::factory()->create();
    $user->assignRole('Admin IT');

    expect($user->can('update_role'))->toBeTrue();
    $user->removeRole('Admin IT');

    expect($user->can('update_role'))->toBeFalse();
});

it('User keeps access after getting direct permission even without role', function () {
    $user = User::factory()->create();
    $user->assignRole('Admin IT');
    $user->givePermissionTo('delete_role');

    $user->removeRole('Admin IT');
    expect($user->can('delete_role'))->toBeTrue();
    expect($user->can('update_role'))->toBeFalse(); // dari role, jadi hilang
});

it('Revoking direct permission updates user access', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('create_role');

    expect($user->can('create_role'))->toBeTrue();

    $user->revokePermissionTo('create_role');
    expect($user->can('create_role'))->toBeFalse();
});
