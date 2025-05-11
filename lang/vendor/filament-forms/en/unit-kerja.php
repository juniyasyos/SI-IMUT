<?php

return [
    // Navigation & General Labels
    'navigation' => [
        'group' => 'Quality Indicators',
        'title' => 'Work Units',
        'plural' => 'Work Units',
        'description' => 'Manage work units within the system efficiently.',
    ],

    // Fields
    'fields' => [
        'id' => 'ID',
        'unit_name' => 'Work Unit Name',
        'description' => 'Description',
        'created_at' => 'Created At',
        'updated_at' => 'Updated At',
        'users' => 'Users',
        'user_id' => 'User',
        'position' => 'Position',
    ],

    // Form Sections
    'form' => [
        'unit' => [
            'title' => 'Work Unit Information',
            'description' => 'Fill in the work unit details correctly.',
            'name_placeholder' => 'Enter work unit name',
            'description_placeholder' => 'Add a brief description of this work unit',
            'helper_text' => 'The unit name must be unique and up to 100 characters long.',
        ],
        'users' => [
            'title' => 'Users in Work Unit',
            'description' => 'Add users to this work unit.',
            'search_placeholder' => 'Search users...',
            'add_button' => 'Add User',
            'remove_button' => 'Remove User',
        ],
    ],
];
