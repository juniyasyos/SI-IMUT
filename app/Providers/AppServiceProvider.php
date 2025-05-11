<?php

namespace App\Providers;

use App\Models\User;
use Filament\Support\Facades\FilamentView;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
        parent::register();
        FilamentView::registerRenderHook('panels::body.end', fn(): string => Blade::render("@vite('resources/js/app.js')"));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

        $vendorLangPath = base_path('lang/vendor');

        collect(File::directories($vendorLangPath))
            ->each(function ($packagePath) {
                $namespace = basename($packagePath);

                $hasTranslationFiles = collect(File::directories($packagePath))
                    ->contains(fn($localePath) => collect(File::files($localePath))
                        ->contains(fn($file) => in_array($file->getExtension(), ['php', 'json'])));

                if ($hasTranslationFiles) {
                    $this->loadTranslationsFrom($packagePath, $namespace);
                }
            });

        // Gate::policy()
        Event::listen(function (\SocialiteProviders\Manager\SocialiteWasCalled $event) {
            $event->extendSocialite('discord', \SocialiteProviders\Google\Provider::class);
        });
    }
}
