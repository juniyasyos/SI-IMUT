<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Buat permissions terkait folder
    Permission::firstOrCreate(['name' => 'view_folder']);
    Permission::firstOrCreate(['name' => 'view_any_folder']);
    Permission::firstOrCreate(['name' => 'create_folder']);
    Permission::firstOrCreate(['name' => 'update_folder']);

    // Role Tim Mutu: hanya bisa update folder
    $timMutu = Role::firstOrCreate(['name' => 'Tim Mutu']);
    $timMutu->syncPermissions(['update_folder']);

    // Role Staff: tidak punya permission folder
    Role::firstOrCreate(['name' => 'Staff']);

    // Role Admin IT: akses penuh ke folder
    $adminIT = Role::firstOrCreate(['name' => 'Admin IT']);
    $adminIT->syncPermissions([
        'view_folder',
        'view_any_folder',
        'create_folder',
        'update_folder'
    ]);
});

it('Tim Mutu can only update folder', function () {
    $user = User::factory()->create();
    $user->assignRole('Tim Mutu');

    expect($user->can('update_folder'))->toBeTrue();
    expect($user->can('create_folder'))->toBeFalse();
    expect($user->can('view_folder'))->toBeFalse();
    expect($user->can('view_any_folder'))->toBeFalse();
});

it('Admin IT has full access to folder permissions', function () {
    $user = User::factory()->create();
    $user->assignRole('Admin IT');

    expect($user->can('view_folder'))->toBeTrue();
    expect($user->can('view_any_folder'))->toBeTrue();
    expect($user->can('create_folder'))->toBeTrue();
    expect($user->can('update_folder'))->toBeTrue();
});

it('Staff cannot access any folder permissions', function () {
    $user = User::factory()->create();
    $user->assignRole('Staff');

    expect($user->can('view_folder'))->toBeFalse();
    expect($user->can('create_folder'))->toBeFalse();
    expect($user->can('update_folder'))->toBeFalse();
});

it('User with no role has no folder permissions', function () {
    $user = User::factory()->create();

    expect($user->can('view_folder'))->toBeFalse();
    expect($user->can('update_folder'))->toBeFalse();
});

it('User can be granted direct folder permission without role', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('create_folder');

    expect($user->can('create_folder'))->toBeTrue();
    expect($user->can('view_folder'))->toBeFalse();
});

it('User with multiple roles inherits all folder permissions', function () {
    $user = User::factory()->create();
    $user->assignRole('Tim Mutu');
    $user->assignRole('Admin IT');

    expect($user->can('view_folder'))->toBeTrue();
    expect($user->can('update_folder'))->toBeTrue();
    expect($user->can('create_folder'))->toBeTrue();
});

it('Revoking permission from role updates user ability', function () {
    $user = User::factory()->create();
    $user->assignRole('Admin IT');

    expect($user->can('create_folder'))->toBeTrue();

    $role = Role::findByName('Admin IT');
    $role->revokePermissionTo('create_folder');

    $user->refresh();
    expect($user->can('create_folder'))->toBeFalse();
});

it('Removing role removes related folder permissions', function () {
    $user = User::factory()->create();
    $user->assignRole('Admin IT');

    expect($user->can('update_folder'))->toBeTrue();

    $user->removeRole('Admin IT');

    expect($user->can('update_folder'))->toBeFalse();
});

it('User with direct permission still retains it after role removed', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('view_any_folder');
    $user->assignRole('Tim Mutu');

    expect($user->can('view_any_folder'))->toBeTrue();

    $user->removeRole('Tim Mutu');

    expect($user->can('view_any_folder'))->toBeTrue(); // masih ada dari direct permission
});

it('User loses permission after revoking direct permission', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('view_folder');

    expect($user->can('view_folder'))->toBeTrue();

    $user->revokePermissionTo('view_folder');

    expect($user->can('view_folder'))->toBeFalse();
});
