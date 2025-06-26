<?php

return [
    'name' => 'Tim Mutu',
    'guard_name' => 'web',
    'description' => 'Role untuk Tim Mutu, memiliki akses manajemen pengguna, IMUT, laporan, dan laporan penilaian.',
    'permissions' => [
        // Pages
        'page_MyProfilePage',

        // Widget
        'widget_LaporanLatestWidget',
        'widget_DashboardSiimutOverview',
        'widget_ImutCapaianWidget',
        'widget_ImutTercapai',

        // User Management
        'view_user',
        'view_any_user',
        'export_user',

        // Unit Kerja
        'view_any_unit::kerja',
        'view_unit::kerja',
        'attach_user_to_unit_kerja_unit::kerja',
        'attach_imut_data_to_unit_kerja_unit::kerja',

        // IMUT Category
        'view_any_imut::category',
        'view_imut::category',
        'create_imut::category',
        'update_imut::category',
        'delete_imut::category',
        'delete_any_imut::category',
        'force_delete_imut::category',
        'force_delete_any_imut::category',
        'restore_imut::category',
        'restore_any_imut::category',

        // IMUT Data
        'view_imut::data',
        'view_any_imut::data',
        'create_imut::data',
        'update_imut::data',
        'restore_imut::data',
        'restore_any_imut::data',
        'delete_imut::data',
        'delete_any_imut::data',
        'replicate_imut::data',
        'reorder_imut::data',
        'force_delete_imut::data',
        'force_delete_any_imut::data',

        // IMUT Profile
        'view_imut::profile',
        'view_any_imut::profile',
        'create_imut::profile',
        'update_imut::profile',
        'restore_imut::profile',
        'restore_any_imut::profile',
        'delete_imut::profile',
        'delete_any_imut::profile',
        'replicate_imut::profile',
        'reorder_imut::profile',
        'force_delete_imut::profile',
        'force_delete_any_imut::profile',

        // Region Type
        'view_any_region::type::bencmarking',
        'view_region::type::bencmarking',
        'create_region::type::bencmarking',
        'update_region::type::bencmarking',
        'delete_region::type::bencmarking',
        'delete_any_region::type::bencmarking',
        'force_delete_region::type::bencmarking',
        'force_delete_any_region::type::bencmarking',
        'restore_region::type::bencmarking',
        'restore_any_region::type::bencmarking',

        // Laporan
        'view_laporan::imut',
        'view_any_laporan::imut',
        'create_laporan::imut',
        'update_laporan::imut',
        'restore_laporan::imut',
        'restore_any_laporan::imut',
        'delete_laporan::imut',
        'delete_any_laporan::imut',
        'replicate_laporan::imut',
        'reorder_laporan::imut',
        'force_delete_laporan::imut',
        'force_delete_any_laporan::imut',

        // Report
        'view_unit_kerja_report_laporan::imut',
        'view_unit_kerja_report_detail_laporan::imut',
        'view_imut_data_report_laporan::imut',
        'view_imut_data_report_detail_laporan::imut',

        // Penilaian
        'view_imut_penilaian_imut::penilaian',
        'update_profile_penilaian_imut::penilaian',
        'create_recommendation_penilaian_imut::penilaian',

        // Folder
        'view_any_folder::custom',
        'view_folder::custom',
        'view_all_folder::custom',
        'create_folder::custom',
        'update_folder::custom',
        'delete_folder::custom',

        // Media
        'view_media::custom',
        'view_all_media::custom',
        'create_media::custom',
        'update_media::custom',

    ],
];
