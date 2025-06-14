<?php

namespace App\Providers;

use App\Policies\ActivityPolicy;
use App\Policies\FolderCustomPolicy;
use App\Policies\MediaCustomPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Spatie\Activitylog\Models\Activity;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Activity::class => ActivityPolicy::class,
        // \Juniyasyos\FilamentMediaManager\Models\Folder::class => FolderCustomPolicy::class,
        // \Juniyasyos\FilamentMediaManager\Models\Media::class => MediaCustomPolicy::class,
    ];

public function boot(): void
    {
        $this->registerPolicies();

        // Ambil model dari config
        $folderModel = config('media-manager.model.folder');
        $mediaModel = config('media-manager.model.media');

        // Daftarkan policy custom ke model dari config
        Gate::policy($folderModel, FolderCustomPolicy::class);
        Gate::policy($mediaModel, MediaCustomPolicy::class);
    }
}
