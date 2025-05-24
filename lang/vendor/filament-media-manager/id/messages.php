<?php

return [
    'empty' => [
        'title' => "Tidak ada Media atau Folder ditemukan",
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
        'group' => 'Konten',
    ],
    'media' => [
        'title' => 'Media',
        'single' => 'Media',
        'columns' => [
            'name' => 'Nama',
            'file_name' => 'Nama Berkas',
            'mime_type' => 'Tipe MIME',
            'disk' => 'Disk',
            'conversions_disk' => 'Disk Konversi',
            'collection_name' => 'Nama Koleksi',
            'size' => 'Ukuran',
            'order_column' => 'Urutan',
            'image' => 'Gambar',
            'model' => 'Model',
        ],
        'actions' => [
            'sub_folder' => [
                'label' => "Buat Sub Folder"
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
                'label' => 'Ubah Folder',
            ],
        ],
        'notifications' => [
            'create-media' => 'Media berhasil dibuat',
            'delete-folder' => 'Folder berhasil dihapus',
            'edit-folder' => 'Folder berhasil diubah',
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
