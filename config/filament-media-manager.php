<?php

return [
    "model" => [
        "folder" => \Juniyasyos\FilamentMediaManager\Models\Folder::class,
        "media" => \Juniyasyos\FilamentMediaManager\Models\Media::class,
    ],

    "api" => [
        "active" => false,
        "middlewares" => [
            "api",
            "auth:sanctum"
        ],
        "prefix" => "api/media-manager",
        "resources" => [
            "folders" => \Juniyasyos\FilamentMediaManager\Http\Resources\FoldersResource::class,
            "folder" => \Juniyasyos\FilamentMediaManager\Http\Resources\FolderResource::class,
            "media" => \Juniyasyos\FilamentMediaManager\Http\Resources\MediaResource::class
        ]
    ],

    "filament" => [
        "active" => true,
        "resources" => [
            App\Filament\Resources\FolderCustomResource::class,
            App\Filament\Resources\MediaCustomResource::class,
        ]
    ],

    "user" => [
        'column_name' => 'name',
    ],

    'allow_user_access' => true,

    'slug_folder' => 'folder',

    "navigation_sort" => 0,
];
