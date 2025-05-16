<?php

return [
    // Navigasi & Label Umum
    'navigation' => [
        'group' => 'Quality Indicators',
        'title' => 'Data IMUT',
        'plural' => 'Data IMUT',
        'description' => 'Kelola data indikator mutu dengan efisien.',
    ],

    // Field
    'fields' => [
        'id' => 'ID',
        'title' => 'Judul Indikator',
        'imut_kategori_id' => 'Kategori',
        'created_at' => 'Dibuat Pada',
        'updated_at' => 'Diperbarui Pada',
        'deleted_at' => 'Dihapus Pada',

        // Tambahan yang umum
        'slug' => 'Slug',
        'year' => 'Tahun',
        'version' => 'Versi',

        // Tambahan untuk field deskripsi
        'description' => 'Deskripsi',

        // Tambahan untuk field status
        'status' => 'Status',
        'status_helper' => 'Aktif atau Tidak Aktif',
    ],

    // Bagian Formulir
    'form' => [
        'main' => [
            'title' => 'Informasi Indikator',
            'description' => 'Silakan lengkapi detail indikator dengan benar.',
            'title_placeholder' => 'Masukkan judul indikator',
            'category_placeholder' => 'Pilih kategori',
            'helper_text' => 'Pastikan judul bersifat deskriptif dan unik.',

            // Tambahan untuk field baru
            'slug_placeholder' => 'Otomatis dihasilkan dari judul',
            'slug_helper' => 'Digunakan dalam URL, harus unik dan huruf kecil.',
            'year_placeholder' => 'cth. 2024',
            'year_helper' => 'Tentukan tahun untuk indikator ini.',
            'status_placeholder' => 'Pilih status',
            'status_helper' => 'Tentukan apakah indikator ini aktif atau tidak.',
            'version_placeholder' => 'cth. v1.0',
            'version_helper' => 'Menunjukkan versi data untuk pelacakan perubahan.',
            'category_hint' => 'Pilih kategori yang paling sesuai dengan indikator ini.',

            // Tambahan untuk field deskripsi
            'description_placeholder' => 'Masukkan deskripsi singkat tentang indikator',
            'description_helper' => 'Berikan penjelasan rinci mengenai tujuan dan penggunaan indikator.',
        ],
    ],
];
