<?php

return [
    'columns' => [
        'title' => 'IMUT Title',
        'category' => 'Category',
    ],

    'filters' => [
        'category' => 'IMUT Category',
    ],

    'actions' => [
        'attach' => [
            'label' => 'Add IMUT',
        ],
        'detach' => [
            'label' => 'Detach',
            'heading' => 'Detach Confirmation',
            'description' => 'Are you sure you want to detach this IMUT from the work unit?',
        ],
        'detach_bulk' => [
            'label' => 'Detach Selected',
        ],
    ],

    'form' => [
        'select_imut' => [
            'label' => 'Select IMUT',
            'placeholder' => 'Search and select IMUT data...',
            'helper' => 'Only shows IMUTs not yet added.',
        ],
    ],

    'modal' => [
        'heading' => 'Attach IMUT to Work Unit',
        'submit_label' => 'Add',
    ],
];
