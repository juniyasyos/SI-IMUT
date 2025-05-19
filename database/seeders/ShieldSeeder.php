<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use BezhanSalleh\FilamentShield\Support\Utils;
use Spatie\Permission\PermissionRegistrar;

class ShieldSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $rolesWithPermissions = [
            [
                "name" => "Unit Kerja",
                "guard_name" => "web",
                "permissions" => [
                    "page_MyProfilePage",

                    // IMUT Data
                    "view_imut::data",
                    "view_any_imut::data",
                    "create_imut::data",
                    "update_imut::data",

                    // Laporan
                    "view_laporan::imut",
                    "view_any_laporan::imut",
                    "create_laporan::imut",
                    "update_laporan::imut",
                    "restore_laporan::imut",
                    "delete_laporan::imut",
                    "replicate_laporan::imut",
                    "reorder_laporan::imut",

                    // Report
                    "view_unit_kerja_report_laporan::imut",
                    "view_unit_kerja_report_detail_laporan::imut",
                    "view_imut_data_report_laporan::imut",
                    "view_imut_data_report_detail_laporan::imut",
                    "update_numerator_denominator_laporan::imut",

                    // Unit Info
                    "view_unit::kerja",
                    "view_any_unit::kerja",
                ]
            ],
            [
                "name" => "Tim Mutu",
                "guard_name" => "web",
                "permissions" => [
                    "page_MyProfilePage",

                    // User Management
                    "view_user",
                    "view_any_user",
                    "create_user",
                    "update_user",
                    "restore_user",
                    "restore_any_user",
                    "delete_user",
                    "delete_any_user",
                    "export_user",

                    // IMUT Profile, Category, Data
                    "view_imut::data",
                    "view_any_imut::data",
                    "create_imut::data",
                    "update_imut::data",
                    "restore_imut::data",
                    "restore_any_imut::data",
                    "delete_imut::data",
                    "delete_any_imut::data",
                    "replicate_imut::data",
                    "reorder_imut::data",
                    "force_delete_imut::data",
                    "force_delete_any_imut::data",

                    "view_imut::profile",
                    "view_any_imut::profile",
                    "create_imut::profile",
                    "update_imut::profile",
                    "restore_imut::profile",
                    "restore_any_imut::profile",
                    "delete_imut::profile",
                    "delete_any_imut::profile",
                    "replicate_imut::profile",
                    "reorder_imut::profile",
                    "force_delete_imut::profile",
                    "force_delete_any_imut::profile",

                    "view_imut::category",
                    "view_any_imut::category",
                    "create_imut::category",
                    "update_imut::category",
                    "delete_imut::category",
                    "delete_any_imut::category",

                    // Penilaian
                    "view_imut_penilaian_laporan::imut",
                    "update_profile_penilaian_laporan::imut",
                    "create_recommendation_penilaian_laporan::imut",

                    // Laporan
                    "view_laporan::imut",
                    "view_any_laporan::imut",
                    "create_laporan::imut",
                    "update_laporan::imut",
                    "restore_laporan::imut",
                    "restore_any_laporan::imut",
                    "delete_laporan::imut",
                    "delete_any_laporan::imut",
                    "replicate_laporan::imut",
                    "reorder_laporan::imut",
                    "force_delete_laporan::imut",
                    "force_delete_any_laporan::imut",

                    // Report
                    "view_unit_kerja_report_laporan::imut",
                    "view_unit_kerja_report_detail_laporan::imut",
                    "view_imut_data_report_laporan::imut",
                    "view_imut_data_report_detail_laporan::imut",

                    // Folder & Media Viewer
                    "view_media",
                    "view_any_media",
                    "view_folder",
                    "view_any_folder",
                ]
            ],
            [
                "name" => "IT",
                "guard_name" => "web",
                "permissions" => [
                    "page_MyProfilePage",

                    // User Management Penuh
                    "view_user", "view_any_user", "create_user", "update_user",
                    "restore_user", "restore_any_user", "delete_user", "delete_any_user",
                    "force_delete_user", "force_delete_any_user",
                    "impersonate_user", "set_role_user",
                    "view_activities_user", "export_user",

                    // Role Management
                    "view_role", "view_any_role", "create_role", "update_role", "delete_role", "delete_any_role",

                    // Backup & Settings
                    "page_Backups",
                    "page_SiteSettings",
                    "page_PWASettingsPage",
                    "page_SocialMenuSettings",
                    "page_AuthenticationSettings",
                    "page_LocationSettings",
                    "page_SettingsHub",

                    // Media & Folder (semua akses)
                    "view_media", "view_any_media", "create_media", "update_media", "delete_media", "delete_any_media",
                    "restore_media", "restore_any_media", "replicate_media", "reorder_media",
                    "force_delete_media", "force_delete_any_media",

                    "view_folder", "view_any_folder", "create_folder", "update_folder",
                    "restore_folder", "restore_any_folder", "replicate_folder", "reorder_folder",
                    "delete_folder", "delete_any_folder", "force_delete_folder", "force_delete_any_folder",

                    // Region Benchmarking
                    "view_region::type::bencmarking", "view_any_region::type::bencmarking",
                    "create_region::type::bencmarking", "update_region::type::bencmarking",
                    "restore_region::type::bencmarking", "restore_any_region::type::bencmarking",
                    "replicate_region::type::bencmarking", "reorder_region::type::bencmarking",
                    "delete_region::type::bencmarking", "delete_any_region::type::bencmarking",
                    "force_delete_region::type::bencmarking", "force_delete_any_region::type::bencmarking",

                    // Optional (akses penuh entitas)
                    "view_unit::kerja", "view_any_unit::kerja",
                    "create_unit::kerja", "update_unit::kerja",
                    "delete_unit::kerja", "delete_any_unit::kerja",
                    "force_delete_unit::kerja", "force_delete_any_unit::kerja",
                ]
            ]
        ];

        $this->makeRolesWithPermissions($rolesWithPermissions);
        $this->command->info('Shield Seeding Completed.');
    }

    protected function makeRolesWithPermissions(array $rolesWithPermissions): void
    {
        if (blank($rolesWithPermissions)) return;

        $roleModel = Utils::getRoleModel();
        $permissionModel = Utils::getPermissionModel();

        foreach ($rolesWithPermissions as $roleData) {
            $role = $roleModel::firstOrCreate([
                'name' => $roleData['name'],
                'guard_name' => $roleData['guard_name'],
            ]);

            if (!empty($roleData['permissions'])) {
                $permissions = collect($roleData['permissions'])->map(fn($perm) =>
                    $permissionModel::firstOrCreate([
                        'name' => $perm,
                        'guard_name' => $roleData['guard_name'],
                    ])
                );
                $role->permissions()->syncWithoutDetaching($permissions);
            }
        }
    }
}
