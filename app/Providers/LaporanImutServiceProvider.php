<?php

namespace App\Providers;

use App\Services\LaporanImutService;
use Illuminate\Support\ServiceProvider;

class LaporanImutServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register()
    {
        $this->app->singleton('laporanimut', function ($app) {
            return new LaporanImutService;
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
