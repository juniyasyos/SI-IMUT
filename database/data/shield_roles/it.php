<?php

return [
    'name' => 'IT',
    'guard_name' => 'web',
    'description' => 'Role untuk tim IT, memiliki akses penuh terhadap sistem, manajemen user, role, backup, dan pengaturan.',
    'permissions' => [
        'page_MyProfilePage',

        // User Management Penuh
        'view_user', 'view_any_user', 'create_user', 'update_user',
        'restore_user', 'restore_any_user', 'delete_user', 'delete_any_user',
        'force_delete_user', 'force_delete_any_user',
        'impersonate_user', 'set_role_user',
        'view_activities_user', 'export_user',

        // Role Management
        'view_role', 'view_any_role', 'create_role', 'update_role', 'delete_role', 'delete_any_role',

        // Backup & Settings
        'page_Backups',
        'page_SiteSettings',
        'page_PWASettingsPage',
        'page_SocialMenuSettings',
        'page_AuthenticationSettings',
        'page_LocationSettings',
        'page_SettingsHub',

        // Media & Folder (akses penuh)
        'view_media', 'create_media', 'update_media', 'delete_media', 'delete_any_media',
        'restore_media', 'restore_any_media', 'replicate_media', 'reorder_media',
        'force_delete_media', 'force_delete_any_media',

        'view_folder', 'view_any_folder', 'create_folder', 'update_folder',
        'restore_folder', 'restore_any_folder', 'replicate_folder', 'reorder_folder',
        'delete_folder', 'delete_any_folder', 'force_delete_folder', 'force_delete_any_folder',

        // Region Benchmarking
        'view_region::type::bencmarking', 'view_any_region::type::bencmarking',
        'create_region::type::bencmarking', 'update_region::type::bencmarking',
        'restore_region::type::bencmarking', 'restore_any_region::type::bencmarking',
        'replicate_region::type::bencmarking', 'reorder_region::type::bencmarking',
        'delete_region::type::bencmarking', 'delete_any_region::type::bencmarking',
        'force_delete_region::type::bencmarking', 'force_delete_any_region::type::bencmarking',

        // Optional (akses penuh entitas unit kerja)
        'view_unit::kerja', 'view_any_unit::kerja',
        'create_unit::kerja', 'update_unit::kerja',
        'delete_unit::kerja', 'delete_any_unit::kerja',
        'force_delete_unit::kerja', 'force_delete_any_unit::kerja',
    ],
];
