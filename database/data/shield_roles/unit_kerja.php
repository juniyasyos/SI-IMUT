<?php

return [
    'name' => 'Unit Kerja',
    'guard_name' => 'web',
    'description' => 'Role untuk pengguna Unit Kerja, akses terbatas pada laporan, media, dan folder milik unit kerja.',
    'permissions' => [
        // Pages
        'page_MyProfilePage',

        // Widget
        'widget_UnitKerjaInfo',
        'widget_StatsForUnitKerja',
        'widget_LaporanLatestWidget',

        // Folder
        'view_any_folder::custom',
        'view_folder::custom',
        'view_by_unit_kerja_folder::custom',
        'create_folder::custom',
        'update_folder::custom',
        'delete_folder::custom',

        // Media
        'view_media::custom',
        'view_by_unit_kerja_media::custom',
        'create_media::custom',
        'update_media::custom',

        // IMUT Data
        'view_imut::data',
        'view_any_imut::data',
        'create_imut::data',
        'update_imut::data',
        'view_by_unit_kerja_imut::data',
        'delete_imut::data',
        'force_delete_imut::data',
        'force_delete_any_imut::data',
        'restore_imut::data',
        'restore_any_imut::data',

        // IMUT Category
        'view_any_imut::category',
        'view_imut::category',

        // Laporan
        'view_any_laporan::imut',

        // Report
        'view_unit_kerja_report_laporan::imut',
        'view_unit_kerja_report_detail_laporan::imut',
        'view_imut_data_report_laporan::imut',
        'view_imut_data_report_detail_laporan::imut',
        'view_imut_penilaian_imut::penilaian',
        'update_numerator_denominator_imut::penilaian',
    ],
];
