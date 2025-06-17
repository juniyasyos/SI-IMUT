<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class UnitKerjaProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(
            \App\Repositories\Interfaces\UnitKerjaFolderRepositoryInterface::class,
            \App\Repositories\UnitKerjaFolderRepository::class,
        );

    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}