<?php

return [
    // Navigasi & Label Umum
    'navigation' => [
        'group' => 'Quality Indicators',
        'title' => 'Unit Kerja',
        'plural' => 'Unit Kerja',
        'description' => 'Kelola unit kerja dalam sistem dengan efisien.',
    ],

    // Kolom/Form Field
    'fields' => [
        'id' => 'ID',
        'unit_name' => 'Nama Unit Kerja',
        'description' => 'Deskripsi',
        'created_at' => 'Dibuat Pada',
        'updated_at' => 'Diperbarui Pada',
        'users' => 'Pengguna',
        'user_id' => 'Pengguna',
        'position' => 'Jabatan',
    ],

    // Bagian Formulir
    'form' => [
        'unit' => [
            'title' => 'Informasi Unit Kerja',
            'description' => 'Isi detail unit kerja dengan benar.',
            'name_placeholder' => 'Masukkan nama unit kerja',
            'description_placeholder' => 'Tambahkan deskripsi singkat tentang unit kerja ini',
            'helper_text' => 'Nama unit harus unik dan maksimal 100 karakter.',
        ],
        'users' => [
            'title' => 'Pengguna dalam Unit Kerja',
            'description' => 'Tambahkan pengguna ke unit kerja ini.',
            'search_placeholder' => 'Cari pengguna...',
            'add_button' => 'Tambahkan Pengguna',
            'remove_button' => 'Hapus Pengguna',
        ],
    ],

    'actions' => [
       'attach' => 'Kaitkan Pengguna',
       'add' => 'Tambah Unit Kerja'
    ]
];
