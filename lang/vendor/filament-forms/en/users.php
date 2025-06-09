<?php

return [

    // Navigation & General Labels
    'navigation' => [
        'group' => 'User Access Management',
        'title' => 'Users',
        'plural' => 'Users',
        'description' => 'Manage user accounts and access rights within the system.',
    ],

    // Fields
    'fields' => [
        'id' => 'ID',
        'name' => 'Full Name',
        'nik' => 'Staff ID (NIK)', // NIK field
        'email' => 'Email Address',
        'password' => 'Password',
        'created_at' => 'Created At',
        'updated_at' => 'Updated At',
        'avatar_url' => 'Profile Photo',
        'roles' => 'Roles',
        'role' => 'Role',
        'position_id' => 'Position',
        'position' => 'Position',
        'place_of_birth' => 'Place of Birth', // Tempat Lahir
        'date_of_birth' => 'Date of Birth', // Tanggal Lahir
        'gender' => 'Gender', // Gender
        'address_ktp' => 'KTP Address', // Alamat KTP
        'phone_number' => 'Phone Number', // Nomor Telepon
        'status' => 'Status', // Status Pengguna
    ],

    'status' => [
        'active' => 'Active',
        'inactive' => 'Inactive',
        'suspended' => 'Suspended'
    ],

    // Form Sections
    'form' => [
        'user' => [
            'title' => 'User Information',
            'description' => 'Fill in the user data completely. Ensure the role is correctly selected.',
            'name_placeholder' => 'Enter full name',
            'nik_placeholder' => 'Enter NIK', // NIK Placeholder
            'email_placeholder' => 'example@email.com',
            'password_placeholder' => 'Enter password',
            'helper_text' => 'Make sure the email is unique and the password is secure.',
        ],
        'position' => [
            'title' => 'Position & Access',
            'description' => 'Select the user’s position in the organization.',
            'select_placeholder' => 'Select position',
            'create_label' => 'Position Name',
            'create_description' => 'Position Description',
            'no_position' => 'No position assigned',
            'edit_modal_title' => 'Edit Position'
        ],
        'personal_info' => [
            'title' => 'Personal Information',
            'description' => 'Provide the personal details of the user.',
            'place_of_birth_placeholder' => 'Enter place of birth',
            'date_of_birth_placeholder' => 'Enter date of birth',
            'gender_placeholder' => 'Select gender',
            'gender_male' => 'Male',
            'gender_female' => 'Female'
        ],
        'contact_info' => [
            'title' => 'Contact Information',
            'description' => 'Provide the contact details of the user.',
            'address_placeholder' => 'Enter KTP address',
            'phone_number_placeholder' => 'Enter phone number',
        ],

        'account' => [
            'title' => 'Account Settings',
            'description' => 'Configure credentials'
        ]
    ],

    // Buttons / UI
    'buttons' => [
        'add_role' => 'Add Role',
        'remove_role' => 'Remove Role',
        'impersonate' => 'Impersonate User',
        'set_role' => 'Set Role',
        'actions' => 'Actions',
        'add_user' => 'Create User',
        'update_user' => 'Update User',
    ],

    'filters' => [
        'roles' => 'Filter by Role',
        'position' => 'Filter by Position',
        'status' => 'Filter by Status', // Menambahkan filter berdasarkan status
    ],

    'actions' => [
        'activities' => 'Activities',
        'set_role' => 'Set Role',
        'impersonate' => 'Impersonate',
        'group' => 'Actions',
        'change_status' => 'Change Status', // Tambahan aksi untuk mengubah status
        'delete_user' => 'Delete User', // Aksi untuk menghapus user
    ],

    'infolist' => [
        'personal_info_title' => 'User Profile',
        'copy_email' => 'Click to copy email',
        'status' => 'Status',
        'gender' => 'Gender',
        'place_of_birth' => 'Place of Birth', // Tempat Lahir di infolist
        'date_of_birth' => 'Date of Birth', // Tanggal Lahir di infolist
        'phone_number' => 'Phone Number', // Nomor Telepon di infolist
        'address_ktp' => 'KTP Address',
        'contact_info_title' => 'Contact Information',
        'account_info_title' => 'Account Information',
    ],

    'model' => [
        'label' => 'User Management',
        'plural_label' => 'User Managements',
    ],
];
