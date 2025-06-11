<?php

namespace App\Providers;

use App\Policies\ActivityPolicy;
use App\Policies\FolderCustomPolicy;
use App\Policies\MediaCustomPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Spatie\Activitylog\Models\Activity;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Activity::class => ActivityPolicy::class,
        \Juniyasyos\FilamentMediaManager\Models\Folder::class => FolderCustomPolicy::class,
        \Juniyasyos\FilamentMediaManager\Models\Media::class => MediaCustomPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
