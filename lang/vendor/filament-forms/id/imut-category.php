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
        'description_helpertext' => 'Masukkan deskripsi singkat dari kategori',
        'data_count' => 'Jumlah Data IMUT',
    ],

    'form' => [
        'title' => 'Informasi Kategori',
        'description' => 'Silakan isi nama kategori untuk grup indikator ini.',
        'name_placeholder' => 'Masukkan nama kategori',
        'helper_text' => 'Nama kategori harus unik dan tidak lebih dari 100 karakter.',
    ],
];
