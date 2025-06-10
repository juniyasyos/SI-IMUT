<?php

namespace App\Providers;

use App\Models\UnitKerja;
use App\Observers\UnitKerjaObserver;
use BezhanSalleh\FilamentLanguageSwitch\Enums\Placement;
use BezhanSalleh\FilamentLanguageSwitch\LanguageSwitch;
use Filament\Support\Facades\FilamentView;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use SocialiteProviders\Manager\SocialiteWasCalled;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register Vite asset for Filament panel
        FilamentView::registerRenderHook(
            'panels::body.end',
            fn (): string => Blade::render("@vite('resources/js/app.js')")
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerModelPolicies();
        $this->registerSocialiteProviders();
        $this->registerLanguageSwitch();
        $this->registerTranslationNamespaces();
        $this->registerObservers();

        // Uncomment if you want to force HTTPS in production
        // if (config('app.env') === 'production') {
        //     URL::forceScheme('https');
        // }
    }

    /**
     * Register model-policy mappings automatically.
     */
    protected function registerModelPolicies(): void
    {
        collect(glob(app_path('Models').'/*.php'))
            ->map(fn ($file) => [
                'model' => 'App\\Models\\'.pathinfo($file, PATHINFO_FILENAME),
                'policy' => 'App\\Policies\\'.pathinfo($file, PATHINFO_FILENAME).'Policy',
            ])
            ->each(fn ($item) => class_exists($item['model']) && class_exists($item['policy'])
                ? Gate::policy($item['model'], $item['policy'])
                : null
            );
    }

    /**
     * Register socialite providers dynamically.
     */
    protected function registerSocialiteProviders(): void
    {
        Event::listen(function (SocialiteWasCalled $event) {
            $event->extendSocialite('discord', \SocialiteProviders\Google\Provider::class);
        });
    }

    /**
     * Configure the Filament Language Switch plugin.
     */
    protected function registerLanguageSwitch(): void
    {
        LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
            $switch
                ->locales(['id', 'en'])
                ->outsidePanelPlacement(Placement::BottomRight);
        });
    }

    /**
     * Load translation files from vendor packages.
     */
    protected function registerTranslationNamespaces(): void
    {
        $vendorLangPath = base_path('lang/vendor');

        collect(File::directories($vendorLangPath))->each(function ($packagePath) {
            $namespace = basename($packagePath);

            $hasTranslationFiles = collect(File::directories($packagePath))
                ->contains(function ($localePath) {
                    return collect(File::files($localePath))
                        ->contains(fn ($file) => in_array($file->getExtension(), ['php', 'json']));
                });

            if ($hasTranslationFiles) {
                $this->loadTranslationsFrom($packagePath, $namespace);
            }
        });
    }

    /**
     * Register model observers.
     */
    protected function registerObservers(): void
    {
        UnitKerja::observe(UnitKerjaObserver::class);
    }
}
