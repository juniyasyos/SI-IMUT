<?php

return [
    // Navigation & General Labels
    'navigation' => [
        'group' => 'Quality Indicators',
        'title' => 'Work Units',
        'plural' => 'Work Units',
        'description' => 'Manage work units within the system efficiently.',
    ],

    // Actions
    'actions' => [
        'create' => 'Add Work Unit',
        'edit' => 'Edit Work Unit',
        'delete' => 'Delete Work Unit',
        'restore' => 'Restore Work Unit',
        'force_delete' => 'Permanently Delete',
        'view' => 'View Details',
        'manage_users' => 'Manage Users',
        'save' => 'Save Changes',
        'cancel' => 'Cancel',
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

    // Messages & Notifications
    'messages' => [
        'created' => 'The work unit has been successfully added.',
        'updated' => 'The work unit has been successfully updated.',
        'deleted' => 'The work unit has been successfully deleted.',
        'restored' => 'The work unit has been successfully restored.',
        'force_deleted' => 'The work unit has been permanently deleted!',
        'confirm_delete' => 'Are you sure you want to delete this work unit?',
    ],

    // Filters & Search
    'filters' => [
        'search' => 'Search Work Units...',
        'show_deleted' => 'Show Deleted',
    ],

    // Errors
    'errors' => [
        'not_found' => 'The work unit could not be found.',
        'cannot_delete' => 'This work unit cannot be deleted as it is still in use.',
    ],
];
