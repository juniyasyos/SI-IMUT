<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use BezhanSalleh\FilamentShield\Support\Utils;
use Spatie\Permission\PermissionRegistrar;

class ShieldSeeder extends Seeder
{
    /**
     * run seeder
     *
     * @return void
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Define roles with their respective permissions
        $rolesWithPermissions = [
            [
                "name" => "Tim Mutu",
                "guard_name" => "web",
                "permissions" => [
                    "view_user",
                    "view_any_user",
                    "create_user",
                    "update_user",
                    "restore_user",
                    "restore_any_user",
                    "delete_user",
                    "delete_any_user",
                    "page_MyProfilePage"
                ]
            ],
            [
                "name" => "Unit Kerja",
                "guard_name" => "web",
                "permissions" => [
                    "page_MyProfilePage"
                ]
            ]
        ];

        // Define direct permissions
        $directPermissions = [
            ["name" => "view_backup", "guard_name" => "web"],
            ["name" => "view_any_backup", "guard_name" => "web"],
            ["name" => "create_backup", "guard_name" => "web"],
            ["name" => "update_backup", "guard_name" => "web"],
            ["name" => "delete_backup", "guard_name" => "web"],
            ["name" => "delete_any_backup", "guard_name" => "web"],

            ["name" => "view_media", "guard_name" => "web"],
            ["name" => "view_any_media", "guard_name" => "web"],
            ["name" => "create_media", "guard_name" => "web"],
            ["name" => "update_media", "guard_name" => "web"],
            ["name" => "delete_media", "guard_name" => "web"],
            ["name" => "delete_any_media", "guard_name" => "web"]
        ];

        // Seed roles and permissions
        $this->makeRolesWithPermissions($rolesWithPermissions);
        $this->makeDirectPermissions($directPermissions);

        $this->command->info('Shield Seeding Completed.');
    }

    /**
     * makeRolesWithPermissions
     * Create roles with permissions
     *
     * @param  mixed $rolesWithPermissions
     * @return void
     */
    protected function makeRolesWithPermissions(array $rolesWithPermissions): void
    {
        if (blank($rolesWithPermissions))
            return;

        $roleModel = Utils::getRoleModel();
        $permissionModel = Utils::getPermissionModel();

        foreach ($rolesWithPermissions as $roleData) {
            $role = $roleModel::where([
                'name' => $roleData['name'],
                'guard_name' => $roleData['guard_name'],
            ])->first() ?? $roleModel::create([
                            'name' => $roleData['name'],
                            'guard_name' => $roleData['guard_name'],
                        ]);

            if (!empty($roleData['permissions'])) {
                $permissions = collect($roleData['permissions'])->map(fn($permission) => $permissionModel::firstOrCreate([
                    'name' => $permission,
                    'guard_name' => $roleData['guard_name'],
                ]));

                $role->permissions()->syncWithoutDetaching($permissions);
            }
        }
    }

    /**
     * makeDirectPermissions
     * Create direct permissions
     *
     * @param  mixed $directPermissions
     * @return void
     */
    protected function makeDirectPermissions(array $directPermissions): void
    {
        if (blank($directPermissions))
            return;

        $permissionModel = Utils::getPermissionModel();

        foreach ($directPermissions as $permission) {
            $permissionModel::firstOrCreate([
                'name' => $permission['name'],
                'guard_name' => $permission['guard_name'],
            ]);
        }
    }
}
