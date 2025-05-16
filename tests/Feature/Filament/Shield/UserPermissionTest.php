<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Define all permissions related to user management
    $permissions = [
        'view_user',
        'view_any_user',
        'create_user',
        'update_user',
        'restore_user',
        'restore_any_user',
        'delete_user',
        'delete_any_user',
        'force_delete_user',
        'force_delete_any_user',
        'view_activities_user',
        'set_role_user',
        'impersonate_user',
        'export_user',
    ];

    foreach ($permissions as $perm) {
        Permission::firstOrCreate(['name' => $perm]);
    }

    Role::firstOrCreate(['name' => 'Tim Mutu'])
        ->syncPermissions(['export_user']);

    Role::firstOrCreate(['name' => 'IT'])
        ->syncPermissions($permissions);

    Role::firstOrCreate(['name' => 'Basic']);
});

it('Tim Mutu can only export user', function () {
    $user = User::factory()->create();
    $user->assignRole('Tim Mutu');

    expect($user->can('export_user'))->toBeTrue();
    expect($user->can('view_user'))->toBeFalse();
});

it('IT role has all permissions', function () {
    $user = User::factory()->create();
    $user->assignRole('IT');

    $permissions = Permission::pluck('name');
    foreach ($permissions as $permission) {
        expect($user->can($permission))->toBeTrue();
    }
});

it('Basic role has no user permissions', function () {
    $user = User::factory()->create();
    $user->assignRole('Basic');

    $permissions = Permission::pluck('name');
    foreach ($permissions as $permission) {
        expect($user->can($permission))->toBeFalse();
    }
});

it('User with direct permission can perform allowed actions', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(['view_user', 'impersonate_user']);

    expect($user->can('view_user'))->toBeTrue();
    expect($user->can('impersonate_user'))->toBeTrue();
    expect($user->can('delete_user'))->toBeFalse();
});

it('User with multiple roles inherits combined permissions', function () {
    $user = User::factory()->create();
    $user->assignRole('Basic', 'Tim Mutu');

    expect($user->can('export_user'))->toBeTrue();
    expect($user->can('delete_user'))->toBeFalse();
});

it('User loses access after role revoked', function () {
    $user = User::factory()->create();
    $user->assignRole('Tim Mutu');
    expect($user->can('export_user'))->toBeTrue();
    $user->removeRole('Tim Mutu');
    expect($user->can('export_user'))->toBeFalse();
});

it('User with mixed role and direct permission', function () {
    $user = User::factory()->create();
    $user->assignRole('Basic');
    $user->givePermissionTo(['set_role_user']);

    expect($user->can('set_role_user'))->toBeTrue();
    expect($user->can('export_user'))->toBeFalse();
});

it('User with duplicate permission via role and direct', function () {
    $user = User::factory()->create();
    $user->assignRole('Tim Mutu');
    $user->givePermissionTo('export_user');

    expect($user->can('export_user'))->toBeTrue();
});

it('User can’t perform action without any permission', function () {
    $user = User::factory()->create();

    expect($user->can('delete_user'))->toBeFalse();
});

it('Assigning new permission dynamically works', function () {
    $user = User::factory()->create();

    expect($user->can('update_user'))->toBeFalse();
    $user->givePermissionTo('update_user');
    expect($user->can('update_user'))->toBeTrue();
});

it('Revoking permission dynamically removes access', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('delete_user');
    expect($user->can('delete_user'))->toBeTrue();
    $user->revokePermissionTo('delete_user');
    expect($user->can('delete_user'))->toBeFalse();
});
