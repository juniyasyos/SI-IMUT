<?php

return [

    // Navigation & General Labels
    'navigation' => [
        'group' => 'Manajemen Akses Pengguna',
        'title' => 'Pengguna',
        'plural' => 'Pengguna',
        'description' => 'Kelola akun pengguna dan hak akses di dalam sistem.',
    ],

    // Fields
    'fields' => [
        'id' => 'ID',
        'name' => 'Nama Lengkap',
        'nik' => 'Nomor Induk Kepegawaian (NIK)', // Field NIK
        'email' => 'Alamat Email',
        'password' => 'Kata Sandi',
        'created_at' => 'Tanggal Dibuat',
        'updated_at' => 'Tanggal Diperbarui',
        'avatar_url' => 'Foto Profil',
        'roles' => 'Peran',
        'role' => 'Peran',
        'position_id' => 'Posisi',
        'position' => 'Posisi',
        'place_of_birth' => 'Tempat Lahir', // Tempat Lahir
        'date_of_birth' => 'Tanggal Lahir', // Tanggal Lahir
        'gender' => 'Jenis Kelamin', // Jenis Kelamin
        'address_ktp' => 'Alamat KTP', // Alamat KTP
        'phone_number' => 'Nomor Telepon', // Nomor Telepon
        'status' => 'Status', // Status Pengguna
    ],

    'status' => [
        'active' => 'Aktif',
        'inactive' => 'Tidak Aktif',
        'suspended' => 'Ditangguhkan'
    ],

    // Form Sections
    'form' => [
        'user' => [
            'title' => 'Informasi Pengguna',
            'description' => 'Isi data pengguna dengan lengkap. Pastikan peran dipilih dengan benar.',
            'name_placeholder' => 'Masukkan nama lengkap',
            'nik_placeholder' => 'Masukkan NIK',
            'email_placeholder' => 'contoh@email.com',
            'password_placeholder' => 'Masukkan kata sandi',
            'helper_text' => 'Pastikan email unik dan kata sandi aman.',
        ],
        'position' => [
            'title' => 'Jabatan & Akses',
            'description' => 'Pilih jabatan pengguna dalam organisasi.',
            'select_placeholder' => 'Pilih jabatan',
            'create_label' => 'Nama Jabatan',
            'create_description' => 'Deskripsi Jabatan',
            'no_position' => 'Belum ada jabatan',
            'edit_modal_title' => 'Ubah Jabatan'
        ],
        'personal_info' => [
            'title' => 'Informasi Pribadi',
            'description' => 'Isi data pribadi pengguna.',
            'place_of_birth_placeholder' => 'Masukkan tempat lahir',
            'date_of_birth_placeholder' => 'Masukkan tanggal lahir',
            'gender_placeholder' => 'Pilih jenis kelamin',
            'gender_male' => 'Laki-laki',
            'gender_female' => 'Perempuan'
        ],
        'contact_info' => [
            'title' => 'Informasi Kontak',
            'description' => 'Isi detail kontak pengguna.',
            'address_placeholder' => 'Masukkan alamat KTP',
            'phone_number_placeholder' => 'Masukkan nomor telepon',
        ],

        'account' => [
            'title' => 'Pengaturan Akun',
            'description' => 'Atur kredensial login dan izin akses.'
        ]
    ],


    // Buttons / UI
    'buttons' => [
        'add_role' => 'Tambah Peran',
        'remove_role' => 'Hapus Peran',
        'impersonate' => 'Masuk Sebagai Pengguna',
        'set_role' => 'Tetapkan Peran',
        'actions' => 'Tindakan',
        'add_user' => 'Buat Pengguna',
        'update_user' => 'Perbarui Pengguna', // Tombol untuk memperbarui pengguna
    ],

    'filters' => [
        'roles' => 'Filter berdasarkan Peran',
        'position' => 'Filter berdasarkan Posisi',
        'status' => 'Filter berdasarkan Status', // Menambahkan filter berdasarkan status
    ],

    'actions' => [
        'activities' => 'Aktivitas',
        'set_role' => 'Tetapkan Peran',
        'impersonate' => 'Masuk Sebagai',
        'group' => 'Tindakan',
        'change_status' => 'Ubah Status',
        'delete_user' => 'Hapus Pengguna', // Aksi untuk menghapus pengguna
    ],

    'infolist' => [
        'personal_info_title' => 'Profil Pengguna',
        'copy_email' => 'Klik untuk menyalin email',
        'status' => 'Status',
        'gender' => 'Jenis Kelamin',
        'place_of_birth' => 'Tempat Lahir',
        'date_of_birth' => 'Tanggal Lahir', // Tanggal Lahir di infolist
        'phone_number' => 'Nomor Telepon', // Nomor Telepon di infolist
        'address_ktp' => 'Alamat KTP',
        'contact_info_title' => 'Informasi Kontak',
        'account_info_title' => 'Informasi Akun',
    ],

    'model' => [
        'label' => 'Manajemen Pengguna',
        'plural_label' => 'Manajemen Pengguna',
    ],
];
