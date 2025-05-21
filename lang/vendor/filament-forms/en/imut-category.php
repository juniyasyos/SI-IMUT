<?php

return [
    'navigation' => [
        'group' => 'Quality Indicators',
        'title' => 'IMUT Categories',
        'plural' => 'IMUT Categories',
        'description' => 'Manage quality indicator categories in the system.',
    ],

    'fields' => [
        'id' => 'ID',
        'category_name' => 'Category Name',
        'created_at' => 'Created At',
        'updated_at' => 'Updated At',
        'description' => 'Description',
        'description_helpertext' => 'Enter a brief description for the category',
        'description_placeholder' => 'Enter description here',
        'data_count' => 'IMUT Data Count',
        'short_name' => 'Short Name',
        'scope' => 'Scope',
        'scope_internal' => 'Internal',
        'scope_national' => 'National',
        'scope_unit' => 'Unit',
        'scope_global' => 'Global',
        'scope_helper_text' => 'Specify the applicable scope for this category.',

        // 'is_standardized_category' => 'Imut Standard',
        'is_benchmark_category' => 'Imut Benchmarking',
    ],


    'form' => [
        'title' => 'Category Information',
        'description' => 'Please provide the category name for this indicator group.',
        'name_placeholder' => 'Enter category name',
        'helper_text' => 'The category name must be unique and no longer than 100 characters.',
        'short_name' => 'Short Name',
        'short_placeholder' => 'Example: IMP-RS',
        'short_helper_text' => 'The short name must be unique and no longer than 50 characters.',


        // 'is_standardized_category' => 'Standardized Category',
        // 'is_standardized_category_helper' => 'Check if this category is part of the IMUT standard.',
        'is_benchmark_category' => 'Benchmark Category',
        'is_benchmark_category_helper' => 'Check if this category is used as an IMUT benchmarking reference.',
    ],

    'buttons' => [
        'add_data' => 'Create Imut Category',
    ]
];
