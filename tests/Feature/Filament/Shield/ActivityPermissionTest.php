<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Buat permission
    Permission::firstOrCreate(['name' => 'view_activitylog']);
    Permission::firstOrCreate(['name' => 'view_any_activitylog']);

    // Role Tim Mutu: hanya boleh view_any_activitylog
    $timMutu = Role::firstOrCreate(['name' => 'Tim Mutu']);
    $timMutu->syncPermissions(['view_any_activitylog']);

    // Role Unit Kerja: tanpa permission activity log
    Role::firstOrCreate(['name' => 'Unit Kerja']);

    // Role IT: full akses activity log
    $it = Role::firstOrCreate(['name' => 'IT']);
    $it->syncPermissions(['view_activitylog', 'view_any_activitylog']);
});

it('Tim Mutu can view any activity log but not individual activity log', function () {
    $user = User::factory()->create();
    $user->assignRole('Tim Mutu');

    expect($user->can('view_any_activitylog'))->toBeTrue();
    expect($user->can('view_activitylog'))->toBeFalse();
});

it('Unit Kerja cannot view any activity log', function () {
    $user = User::factory()->create();
    $user->assignRole('Unit Kerja');

    expect($user->can('view_any_activitylog'))->toBeFalse();
    expect($user->can('view_activitylog'))->toBeFalse();
});

it('IT can view both any activity log and individual activity log', function () {
    $user = User::factory()->create();
    $user->assignRole('IT');

    expect($user->can('view_any_activitylog'))->toBeTrue();
    expect($user->can('view_activitylog'))->toBeTrue();
});

it('User with no roles has no permissions', function () {
    $user = User::factory()->create();

    expect($user->can('view_any_activitylog'))->toBeFalse();
    expect($user->can('view_activitylog'))->toBeFalse();
});

it('User can be granted permission directly without role', function () {
    $user = User::factory()->create();
    $permission = Permission::firstWhere('name', 'view_activitylog');
    $user->givePermissionTo($permission);

    expect($user->can('view_activitylog'))->toBeTrue();
    expect($user->can('view_any_activitylog'))->toBeFalse();
});

it('User with multiple roles combines permissions correctly', function () {
    $user = User::factory()->create();

    // Assign role Tim Mutu (view_any_activitylog) dan IT (full access)
    $user->assignRole('Tim Mutu');
    $user->assignRole('IT');

    expect($user->can('view_any_activitylog'))->toBeTrue();
    expect($user->can('view_activitylog'))->toBeTrue();
});

it('Revoking permission from a role updates user permission accordingly', function () {
    $user = User::factory()->create();
    $user->assignRole('IT');

    expect($user->can('view_activitylog'))->toBeTrue();

    // Revoke permission from IT role
    $it = Role::firstWhere('name', 'IT');
    $it->revokePermissionTo('view_activitylog');

    $user->refresh();

    expect($user->can('view_activitylog'))->toBeFalse();
    expect($user->can('view_any_activitylog'))->toBeTrue(); // masih punya
});

it('Removing a role removes its permissions', function () {
    $user = User::factory()->create();
    $user->assignRole('IT');

    expect($user->can('view_any_activitylog'))->toBeTrue();

    $user->removeRole('IT');

    expect($user->can('view_any_activitylog'))->toBeFalse();
    expect($user->can('view_activitylog'))->toBeFalse();
});

it('User with overlapping permission from role and direct permission still has permission if one revoked', function () {
    $user = User::factory()->create();
    $user->assignRole('Tim Mutu'); // punya view_any_activitylog
    $user->givePermissionTo('view_activitylog'); // direct permission

    expect($user->can('view_any_activitylog'))->toBeTrue();
    expect($user->can('view_activitylog'))->toBeTrue();

    // Revoke direct permission
    $user->revokePermissionTo('view_activitylog');

    expect($user->can('view_activitylog'))->toBeFalse();

    // Revoke role permission
    $user->removeRole('Tim Mutu');

    expect($user->can('view_any_activitylog'))->toBeFalse();
});
