<?php

use App\Models\User;
use App\Models\UnitKerja;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

// Setup sebelum setiap test
beforeEach(function () {
    // Membuat permission lengkap untuk unit_kerja
    $permissions = [
        'view_unit_kerja',
        'view_any_unit_kerja',
        'create_unit_kerja',
        'update_unit_kerja',
        'delete_unit_kerja',
        'delete_any_unit_kerja',
        'restore_unit_kerja',
        'restore_any_unit_kerja',
        'force_delete_unit_kerja',
        'force_delete_any_unit_kerja',
    ];

    foreach ($permissions as $perm) {
        Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
    }

    // Buat role dan assign semua permission
    $role = Role::create(['name' => 'Manajer IT', 'guard_name' => 'web']);
    $role->syncPermissions($permissions);

    // Buat user dan assign role
    $this->userWithAccess = User::factory()->create();
    $this->userWithAccess->assignRole($role);

    // User tanpa permission
    $this->userWithoutAccess = User::factory()->create();

    // Dummy UnitKerja untuk model-based test
    $this->unitKerja = UnitKerja::factory()->create();
});

describe('Unit Kerja Permissions (Direct)', function () {
    it('grants all permissions to user with role', function () {
        $permissions = [
            'view_unit_kerja',
            'view_any_unit_kerja',
            'create_unit_kerja',
            'update_unit_kerja',
            'delete_unit_kerja',
            'delete_any_unit_kerja',
            'restore_unit_kerja',
            'restore_any_unit_kerja',
            'force_delete_unit_kerja',
            'force_delete_any_unit_kerja',
        ];

        foreach ($permissions as $permission) {
            expect(
                $this->userWithAccess->can($permission)
            )->toBeTrue();
        }
    });

    it('denies all permissions to user without role', function () {
        $permissions = [
            'view_unit_kerja',
            'view_any_unit_kerja',
            'create_unit_kerja',
            'update_unit_kerja',
            'delete_unit_kerja',
            'delete_any_unit_kerja',
            'restore_unit_kerja',
            'restore_any_unit_kerja',
            'force_delete_unit_kerja',
            'force_delete_any_unit_kerja',
        ];

        foreach ($permissions as $permission) {
            expect(
                $this->userWithoutAccess->can($permission)
            )->toBeFalse();
        }
    });

});

describe('Unit Kerja Policies', function () {
    it('allows full policy access for authorized user', function () {
        $policy = new \App\Policies\UnitKerjaPolicy;

        expect($policy->viewAny($this->userWithAccess))->toBeTrue();
        expect($policy->view($this->userWithAccess))->toBeTrue();
        expect($policy->create($this->userWithAccess))->toBeTrue();
        expect($policy->update($this->userWithAccess))->toBeTrue();
        expect($policy->delete($this->userWithAccess))->toBeTrue();
        expect($policy->deleteAny($this->userWithAccess))->toBeTrue();
        expect($policy->restore($this->userWithAccess))->toBeTrue();
        expect($policy->restoreAny($this->userWithAccess))->toBeTrue();
        expect($policy->forceDelete($this->userWithAccess))->toBeTrue();
        expect($policy->forceDeleteAny($this->userWithAccess))->toBeTrue();
    });

    it('denies all policy access for unauthorized user', function () {
        $policy = new \App\Policies\UnitKerjaPolicy;

        expect($policy->viewAny($this->userWithoutAccess))->toBeFalse();
        expect($policy->view($this->userWithoutAccess))->toBeFalse();
        expect($policy->create($this->userWithoutAccess))->toBeFalse();
        expect($policy->update($this->userWithoutAccess))->toBeFalse();
        expect($policy->delete($this->userWithoutAccess))->toBeFalse();
        expect($policy->deleteAny($this->userWithoutAccess))->toBeFalse();
        expect($policy->restore($this->userWithoutAccess))->toBeFalse();
        expect($policy->restoreAny($this->userWithoutAccess))->toBeFalse();
        expect($policy->forceDelete($this->userWithoutAccess))->toBeFalse();
        expect($policy->forceDeleteAny($this->userWithoutAccess))->toBeFalse();
    });
});
