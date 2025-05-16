<?php

return [
    // Navigation & General Labels
    'navigation' => [
        'group' => 'Quality Indicators',
        'title' => 'IMUT Data',
        'plural' => 'IMUT Data',
        'description' => 'Manage quality indicator data efficiently.',
    ],

    // Fields
    'fields' => [
        'id' => 'ID',
        'title' => 'Indicator Title',
        'imut_kategori_id' => 'Category',
        'created_at' => 'Created At',
        'updated_at' => 'Updated At',
        'deleted_at' => 'Deleted At',

        // Tambahan yang umum
        'slug' => 'Slug',
        'year' => 'Year',
        'version' => 'Version',

        // Tambahan untuk field description
        'description' => 'Description',

        // Tambahan untuk field status
        'status' => 'Status',
        'status_helper' => 'Active or Inactive',
    ],

    // Form Sections
    'form' => [
        'main' => [
            'title' => 'Indicator Information',
            'description' => 'Please provide accurate indicator details.',
            'title_placeholder' => 'Enter the title of the indicator',
            'category_placeholder' => 'Select a category',
            'helper_text' => 'Ensure the title is descriptive and unique.',

            // Tambahan untuk field baru
            'slug_placeholder' => 'Automatically generated from the title',
            'slug_helper' => 'Used in URLs, must be unique and lowercase.',
            'year_placeholder' => 'e.g. 2024',
            'year_helper' => 'Specify the year for this indicator.',
            'status_placeholder' => 'Select status',
            'status_helper' => 'Define whether this indicator is active or inactive.',
            'version_placeholder' => 'e.g. v1.0',
            'version_helper' => 'Indicates the data version for tracking changes.',
            'category_hint' => 'Select the category that best fits this indicator.',

            // Tambahan untuk field description
            'description_placeholder' => 'Enter a brief description of the indicator',
            'description_helper' => 'Provide a detailed explanation of the indicator\'s purpose and usage.',
        ],
    ],

    'actions' => [
        'delete' => [
            'label' => 'Hapus Data',
            'modal_heading' => 'Hapus Data ImutData',
            'modal_description' => 'Menghapus ImutData ini akan memengaruhi data terkait. Data tidak akan dihapus secara permanen, melainkan dinonaktifkan (soft delete) dan masih dapat dipulihkan kembali jika diperlukan.',
            'modal_submit_label' => 'Ya, Hapus',
        ],
    ],
    
    'notifications' => [
        'deleted' => [
            'title' => 'Data Dinonaktifkan',
            'body' => 'ImutData dan data terkait telah dinonaktifkan (soft delete). Anda masih dapat memulihkannya melalui filter di menu list IMUT Data.',
        ],
    ],
];
