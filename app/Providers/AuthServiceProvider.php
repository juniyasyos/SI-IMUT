<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Policies\ActivityPolicy;
use App\Policies\FolderPolicy;
use App\Policies\MediaPolicy;
use Spatie\Activitylog\Models\Activity;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Activity::class => ActivityPolicy::class,
        \TomatoPHP\FilamentMediaManager\Models\Folder::class => FolderPolicy::class,
        \TomatoPHP\FilamentMediaManager\Models\Media::class => MediaPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
