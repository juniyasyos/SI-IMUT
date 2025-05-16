<?php

return [
    'navigation' => [
        'group' => 'Quality Indicators',
        'title' => 'Kategori IMUT',
        'plural' => 'Kategori IMUT',
        'description' => 'Kelola kategori indikator mutu dalam sistem.',
    ],

    'fields' => [
        'id' => 'ID',
        'category_name' => 'Nama Kategori',
        'created_at' => 'Dibuat Pada',
        'updated_at' => 'Diperbarui Pada',
        'description' => 'Deskripsi',
        'description_helpertext' => 'Masukkan deskripsi singkat untuk kategori',
        'description_placeholder' => 'Masukkan deskripsi di sini',
        'data_count' => 'Jumlah Data IMUT',
        'short_name' => 'Nama Singkat',
        'scope' => 'Lingkup',
        'scope_internal' => 'Internal',
        'scope_national' => 'Nasional',
        'scope_unit' => 'Unit',
        'scope_global' => 'Global',
        'scope_helper_text' => 'Tentukan lingkup yang berlaku untuk kategori ini.',
    ],

    'form' => [
        'title' => 'Informasi Kategori',
        'description' => 'Silakan isi nama kategori untuk kelompok indikator ini.',
        'name_placeholder' => 'Masukkan nama kategori',
        'helper_text' => 'Nama kategori harus unik dan tidak lebih dari 100 karakter.',
        'short_name' => 'Nama Singkat',
        'short_placeholder' => 'Contoh: IMP-RS',
        'short_helper_text' => 'Nama singkat harus unik dan tidak lebih dari 50 karakter.',
    ],

    'buttons' => [
        'add_data' => 'Buat Imut Kategori'
    ]
];
