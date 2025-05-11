<?php

return [
    'empty' => [
        'title' => "Tidak Ada Media atau Folder yang Ditemukan",
    ],
    'folders' => [
        'title' => 'Manajer Media',
        'single' => 'Folder',
        'columns' => [
            'name' => 'Nama',
            'collection' => 'Koleksi',
            'description' => 'Deskripsi',
            'is_public' => 'Publik',
            'has_user_access' => 'Memiliki Akses Pengguna',
            'users' => 'Pengguna',
            'icon' => 'Ikon',
            'color' => 'Warna',
            'is_protected' => 'Dilindungi',
            'password' => 'Kata Sandi',
            'password_confirmation' => 'Konfirmasi Kata Sandi',
        ],
        'group' => '',
    ],
    'media' => [
        'title' => 'Media',
        'single' => 'Media',
        'columns' => [
            'image' => 'Gambar',
            'model' => 'Model',
            'collection_name' => 'Nama Koleksi',
            'size' => 'Ukuran',
            'order_column' => 'Kolom Urutan',
        ],
        'actions' => [
            'sub_folder' => [
                'label' => "Buat Subfolder"
            ],
            'create' => [
                'label' => 'Tambah Media',
                'form' => [
                    'file' => 'Berkas',
                    'title' => 'Judul',
                    'description' => 'Deskripsi',
                ],
            ],
            'delete' => [
                'label' => 'Hapus Folder',
            ],
            'edit' => [
                'label' => 'Edit Folder',
            ],
        ],
        'notifications' => [
            'create-media' => 'Media berhasil dibuat',
            'delete-folder' => 'Folder berhasil dihapus',
            'edit-folder' => 'Folder berhasil diedit',
        ],
        'meta' => [
            'model' => 'Model',
            'file-name' => 'Nama Berkas',
            'type' => 'Tipe',
            'size' => 'Ukuran',
            'disk' => 'Disk',
            'url' => 'URL',
            'delete-media' => 'Hapus Media',
        ],
    ],
];
