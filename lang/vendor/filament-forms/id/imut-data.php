<?php

return [
    // Navigasi & Label Umum
    'navigation' => [
        'group' => 'Quality Indicators',
        'title' => 'Data IMUT',
        'plural' => 'Data IMUT',
        'description' => 'Kelola data indikator mutu secara efisien.',
    ],

    // Field
    'fields' => [
        'id' => 'ID',
        'title' => 'Judul Indikator',
        'imut_kategori_id' => 'Kategori',
        'created_at' => 'Dibuat Pada',
        'updated_at' => 'Diperbarui Pada',
        'deleted_at' => 'Dihapus Pada',
    ],

    // Bagian Formulir
    'form' => [
        'main' => [
            'title' => 'Informasi Indikator',
            'description' => 'Silakan isi detail indikator dengan benar.',
            'title_placeholder' => 'Masukkan judul indikator',
            'category_placeholder' => 'Pilih kategori',
            'helper_text' => 'Pastikan judul bersifat deskriptif dan unik.',
        ],
    ],
];
