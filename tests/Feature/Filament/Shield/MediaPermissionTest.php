<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Buat permission terkait media
    Permission::firstOrCreate(['name' => 'view_media']);
    Permission::firstOrCreate(['name' => 'view_,media']);
    Permission::firstOrCreate(['name' => 'create_media']);
    Permission::firstOrCreate(['name' => 'update_media']);
    Permission::firstOrCreate(['name' => 'delete_media']);
    Permission::firstOrCreate(['name' => 'delete_any_media']);

    // Role Tim Mutu: hanya update_media
    $timMutu = Role::firstOrCreate(['name' => 'Tim Mutu']);
    $timMutu->syncPermissions(['update_media']);

    // Role Staff: tidak memiliki hak akses media
    Role::firstOrCreate(['name' => 'Staff']);

    // Role Admin IT: semua hak akses media
    $adminIT = Role::firstOrCreate(['name' => 'Admin IT']);
    $adminIT->syncPermissions([
        'view_media',
        'view_,media',
        'create_media',
        'update_media',
        'delete_media',
        'delete_any_media',
    ]);
});

it('Tim Mutu can only update media', function () {
    $user = User::factory()->create();
    $user->assignRole('Tim Mutu');

    expect($user->can('update_media'))->toBeTrue();
    expect($user->can('create_media'))->toBeFalse();
    expect($user->can('view_media'))->toBeFalse();
    expect($user->can('view_,media'))->toBeFalse();
    expect($user->can('delete_media'))->toBeFalse();
});

it('Admin IT can do everything related to media', function () {
    $user = User::factory()->create();
    $user->assignRole('Admin IT');

    expect($user->can('view_media'))->toBeTrue();
    expect($user->can('view_,media'))->toBeTrue();
    expect($user->can('create_media'))->toBeTrue();
    expect($user->can('update_media'))->toBeTrue();
    expect($user->can('delete_media'))->toBeTrue();
    expect($user->can('delete_any_media'))->toBeTrue();
});

it('Staff has no access to media', function () {
    $user = User::factory()->create();
    $user->assignRole('Staff');

    expect($user->can('view_media'))->toBeFalse();
    expect($user->can('update_media'))->toBeFalse();
    expect($user->can('delete_media'))->toBeFalse();
});

it('User without role cannot access media', function () {
    $user = User::factory()->create();

    expect($user->can('view_,media'))->toBeFalse();
    expect($user->can('create_media'))->toBeFalse();
});

it('User can be given media permission directly', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('create_media');

    expect($user->can('create_media'))->toBeTrue();
    expect($user->can('view_media'))->toBeFalse();
});

it('User with multiple roles inherits combined media permissions', function () {
    $user = User::factory()->create();
    $user->assignRole('Tim Mutu');
    $user->assignRole('Admin IT');

    expect($user->can('view_media'))->toBeTrue();
    expect($user->can('update_media'))->toBeTrue();
    expect($user->can('delete_any_media'))->toBeTrue();
});

it('Revoke permission from Admin IT updates access', function () {
    $user = User::factory()->create();
    $user->assignRole('Admin IT');
    expect($user->can('delete_any_media'))->toBeTrue();

    Role::findByName('Admin IT')->revokePermissionTo('delete_any_media');
    $user->refresh();

    expect($user->can('delete_any_media'))->toBeFalse();
});

it('Removing role revokes access to media permissions', function () {
    $user = User::factory()->create();
    $user->assignRole('Admin IT');

    expect($user->can('update_media'))->toBeTrue();

    $user->removeRole('Admin IT');
    expect($user->can('update_media'))->toBeFalse();
});

it('Direct media permission stays after removing all roles', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('view_media');
    $user->assignRole('Admin IT');

    expect($user->can('view_media'))->toBeTrue();

    $user->removeRole('Admin IT');
    expect($user->can('view_media'))->toBeTrue();
});

it('User loses media permission after revoking direct permission', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('delete_media');

    expect($user->can('delete_media'))->toBeTrue();

    $user->revokePermissionTo('delete_media');
    expect($user->can('delete_media'))->toBeFalse();
});