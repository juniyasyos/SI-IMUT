<?php

// resources/lang/en/filament-settings-hub.php

return [
    'title' => 'System',
    'group' => 'System & Configuration',
    'back' => 'Back',
    'settings' => [
        'site' => [
            'title' => 'Site Settings',
            'description' => 'Easily manage your website settings here.',
            'form' => [
                'site_name' => 'Website Name',
                'site_description' => 'Short Description of Your Website',
                'site_logo' => 'Upload Your Website Logo',
                'site_profile' => 'Set a Profile Image for Your Site',
                'site_keywords' => 'Keywords for SEO',
                'site_email' => 'Official Website Email',
                'site_phone' => 'Contact Phone Number',
                'site_author' => 'Website Author',
            ],
            'site-map' => 'Generate a Site Map',
            'site-map-notification' => 'Site Map Successfully Generated!',
        ],
        'social' => [
            'title' => 'Social Media Menu',
            'description' => 'Manage your social media links effortlessly.',
            'form' => [
                'site_social' => 'Social Media Links',
                'vendor' => 'Platform Name',
                'link' => 'Profile or Page Link',
            ],
        ],
        'location' => [
            'title' => 'Location Settings',
            'description' => 'Adjust your website’s location details.',
            'form' => [
                'site_address' => 'Full Address',
                'site_phone_code' => 'Phone Code',
                'site_location' => 'Physical Location',
                'site_currency' => 'Preferred Currency',
                'site_language' => 'Default Language',
            ],
        ],
        'authentication' => [
            'title' => 'Authentication Settings',
            'description' => 'Configure login and authentication options.',
            'form' => [
                'section_title' => 'Site Information',
                'site_name' => 'Site Name',
                'site_active' => 'Site Active',
                'registration_enabled' => 'Registration Enabled',
                'password_reset_enabled' => 'Password Reset Enabled',
                'sso_enabled' => 'SSO Enabled',
                'site_active_hint' => 'Enable or disable site activity.',
                'registration_enabled_hint' => 'Allow users to register on your site.',
                'password_reset_enabled_hint' => 'Allow users to reset their passwords.',
                'sso_enabled_hint' => 'Enable Single Sign-On (SSO) authentication.',
            ],
        ],
    ],
];
