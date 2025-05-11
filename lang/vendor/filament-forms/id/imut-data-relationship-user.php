<?php

return [
    'columns' => [
        'title' => 'Judul IMUT',
        'category' => 'Kategori',
    ],

    'filters' => [
        'category' => 'Kategori IMUT',
    ],

    'actions' => [
        'attach' => [
            'label' => 'Tambah IMUT',
        ],
        'detach' => [
            'label' => 'Lepaskan',
            'heading' => 'Konfirmasi Pelepasan',
            'description' => 'Apakah Anda yakin ingin melepaskan IMUT ini dari unit kerja?',
        ],
        'detach_bulk' => [
            'label' => 'Lepaskan Terpilih',
        ],
    ],

    'form' => [
        'select_imut' => [
            'label' => 'Pilih IMUT',
            'placeholder' => 'Cari dan pilih data IMUT...',
            'helper' => 'Hanya menampilkan IMUT yang belum ditambahkan.',
        ],
    ],

    'modal' => [
        'heading' => 'Hubungkan IMUT ke Unit Kerja',
        'submit_label' => 'Tambahkan',
    ],
];
