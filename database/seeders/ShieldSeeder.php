<?php

namespace Database\Seeders;

use BezhanSalleh\FilamentShield\Support\Utils;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class ShieldSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $path = database_path('data/shield_roles');
        $roleFiles = glob("$path/*.php");

        $rolesWithPermissions = collect($roleFiles)
            ->map(fn ($file) => require $file)
            ->toArray();

        $this->makeRolesWithPermissions($rolesWithPermissions);

        $this->command->info('Shield Seeding Completed.');
    }

    protected function makeRolesWithPermissions(array $rolesWithPermissions): void
    {
        if (blank($rolesWithPermissions)) {
            return;
        }

        $roleModel = Utils::getRoleModel();
        $permissionModel = Utils::getPermissionModel();

        foreach ($rolesWithPermissions as $roleData) {
            $role = $roleModel::firstOrCreate([
                'name' => $roleData['name'],
                'guard_name' => $roleData['guard_name'],
            ]);

            if (! empty($roleData['permissions'])) {
                $permissions = collect($roleData['permissions'])->map(fn ($perm) => $permissionModel::firstOrCreate([
                    'name' => $perm,
                    'guard_name' => $roleData['guard_name'],
                ])
                );
                $role->permissions()->syncWithoutDetaching($permissions);
            }
        }
    }
}
