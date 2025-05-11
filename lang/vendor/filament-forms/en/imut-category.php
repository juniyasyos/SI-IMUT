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
        'description_helpertext' => 'Enter a brief description of the category',
        'description_placeholder' => 'Enter description here',
        'data_count' => 'IMUT Data Count',

    ],

    'form' => [
        'title' => 'Category Information',
        'description' => 'Please provide the category name for this indicator group.',
        'name_placeholder' => 'Enter category name',
        'helper_text' => 'The category name must be unique and no longer than 100 characters.',
        'short_name' => 'Short Name',
        'short_placeholder' => 'Example: IMP-RS',
        'short_helper_text' => 'The short name must be unique and no longer than 50 characters.',
    ],
];
