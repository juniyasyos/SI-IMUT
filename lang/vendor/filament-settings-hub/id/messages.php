<?php

// resources/lang/id/filament-settings-hub.php

return [
    'title' => 'Sistem',
    'group' => 'System & Configuration',
    'back' => 'Kembali',
    'settings' => [
        'site' => [
            'title' => 'Pengaturan Situs',
            'description' => 'Kelola pengaturan situs Anda dengan mudah di sini.',
            'form' => [
                'site_name' => 'Nama Website',
                'site_description' => 'Deskripsi Singkat Website Anda',
                'site_logo' => 'Unggah Logo Website Anda',
                'site_profile' => 'Atur Gambar Profil untuk Website Anda',
                'site_keywords' => 'Kata Kunci untuk SEO',
                'site_email' => 'Email Resmi Website',
                'site_phone' => 'Nomor Telepon Kontak',
                'site_author' => 'Penulis Website',
            ],
            'site-map' => 'Hasilkan Peta Situs',
            'site-map-notification' => 'Peta Situs Berhasil Dihasilkan!',
        ],
        'social' => [
            'title' => 'Menu Media Sosial',
            'description' => 'Kelola tautan media sosial Anda dengan mudah.',
            'form' => [
                'site_social' => 'Tautan Media Sosial',
                'vendor' => 'Nama Platform',
                'link' => 'Tautan Profil atau Halaman',
            ],
        ],
        'location' => [
            'title' => 'Pengaturan Lokasi',
            'description' => 'Sesuaikan detail lokasi website Anda.',
            'form' => [
                'site_address' => 'Alamat Lengkap',
                'site_phone_code' => 'Kode Telepon',
                'site_location' => 'Lokasi Fisik',
                'site_currency' => 'Mata Uang yang Diinginkan',
                'site_language' => 'Bahasa Default',
            ],
        ],
        'authentication' => [
            'title' => 'Pengaturan Autentikasi',
            'description' => 'Atur opsi login dan autentikasi.',
            'form' => [
                'section_title' => 'Informasi Situs',
                'site_name' => 'Nama Situs',
                'site_active' => 'Aktifkan Situs',
                'registration_enabled' => 'Registrasi Diaktifkan',
                'password_reset_enabled' => 'Reset Kata Sandi Diaktifkan',
                'sso_enabled' => 'SSO Diaktifkan',
                'site_active_hint' => 'Aktifkan atau nonaktifkan aktivitas situs.',
                'registration_enabled_hint' => 'Izinkan pengguna untuk mendaftar di situs Anda.',
                'password_reset_enabled_hint' => 'Izinkan pengguna untuk mereset kata sandi mereka.',
                'sso_enabled_hint' => 'Aktifkan autentikasi Single Sign-On (SSO).',
            ],
        ],
    ],
];
