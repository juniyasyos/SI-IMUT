<?php

use App\Models\User;
use App\Models\ImutCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Daftar permission yang tersedia untuk ImutCategory
    $permissions = [
        'view_imut::category',
        'view_any_imut::category',
        'create_imut::category',
        'update_imut::category',
        'delete_imut::category',
        'delete_any_imut::category',
    ];

    foreach ($permissions as $perm) {
        Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
    }

    // Role dan user dengan akses penuh
    $role = Role::create(['name' => 'Manajer Mutu', 'guard_name' => 'web']);
    $role->syncPermissions($permissions);

    $this->userWithAccess = User::factory()->create();
    $this->userWithAccess->assignRole($role);

    $this->userWithoutAccess = User::factory()->create();

    $this->imutCategory = ImutCategory::factory()->create();
});

describe('ImutCategory Permissions (Direct)', function () {
    it('grants all permissions to user with role', function () {
        $permissions = [
            'view_imut::category',
            'view_any_imut::category',
            'create_imut::category',
            'update_imut::category',
            'delete_imut::category',
            'delete_any_imut::category',
        ];

        foreach ($permissions as $permission) {
            expect(
                $this->userWithAccess->can($permission)
            )->toBeTrue();
        }
    });

    it('denies all permissions to user without role', function () {
        $permissions = [
            'view_imut::category',
            'view_any_imut::category',
            'create_imut::category',
            'update_imut::category',
            'delete_imut::category',
            'delete_any_imut::category',
        ];

        foreach ($permissions as $permission) {
            expect(
                $this->userWithoutAccess->can($permission)
            )->toBeFalse();
        }
    });
});

describe('ImutCategory Policies', function () {
    it('allows full policy access for authorized user', function () {
        $policy = new \App\Policies\ImutCategoryPolicy;

        expect($policy->viewAny($this->userWithAccess))->toBeTrue();
        expect($policy->view($this->userWithAccess, $this->imutCategory))->toBeTrue();
        expect($policy->create($this->userWithAccess))->toBeTrue();
        expect($policy->update($this->userWithAccess, $this->imutCategory))->toBeTrue();
        expect($policy->delete($this->userWithAccess, $this->imutCategory))->toBeTrue();
        expect($policy->deleteAny($this->userWithAccess))->toBeTrue();
    });

    it('denies all policy access for unauthorized user', function () {
        $policy = new \App\Policies\ImutCategoryPolicy;

        expect($policy->viewAny($this->userWithoutAccess))->toBeFalse();
        expect($policy->view($this->userWithoutAccess, $this->imutCategory))->toBeFalse();
        expect($policy->create($this->userWithoutAccess))->toBeFalse();
        expect($policy->update($this->userWithoutAccess, $this->imutCategory))->toBeFalse();
        expect($policy->delete($this->userWithoutAccess, $this->imutCategory))->toBeFalse();
        expect($policy->deleteAny($this->userWithoutAccess))->toBeFalse();
    });
});
