<?php

namespace App\Providers;

use App\Models\UnitKerja;
use App\Observers\UnitKerjaObserver;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Filament\Support\Facades\FilamentView;
use BezhanSalleh\FilamentLanguageSwitch\LanguageSwitch;
use BezhanSalleh\FilamentLanguageSwitch\Enums\Placement;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        FilamentView::registerRenderHook(
            'panels::body.end',
            fn(): string =>
            Blade::render("@vite('resources/js/app.js')")
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

        UnitKerja::observe(UnitKerjaObserver::class);

        collect(glob(app_path('Models') . '/*.php'))
            ->map(fn($file) => [
                'model' => "App\\Models\\" . pathinfo($file, PATHINFO_FILENAME),
                'policy' => "App\\Policies\\" . pathinfo($file, PATHINFO_FILENAME) . "Policy"
            ])
            ->each(
                fn($item) => class_exists($item['model']) && class_exists($item['policy'])
                ? Gate::policy($item['model'], $item['policy'])
                : null
            );

        Event::listen(function (\SocialiteProviders\Manager\SocialiteWasCalled $event) {
            $event->extendSocialite('discord', \SocialiteProviders\Google\Provider::class);
        });

        $vendorLangPath = base_path('lang/vendor');

        collect(File::directories($vendorLangPath))
            ->each(function ($packagePath) {
                $namespace = basename($packagePath);

                $hasTranslationFiles = collect(File::directories($packagePath))
                    ->contains(function ($localePath) {
                        return collect(File::files($localePath))
                            ->contains(fn($file) => in_array($file->getExtension(), ['php', 'json']));
                    });

                if ($hasTranslationFiles) {
                    $this->loadTranslationsFrom($packagePath, $namespace);
                }
            });


        LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
            $switch
                ->locales(['id', 'en'])
                ->outsidePanelPlacement(Placement::BottomRight);
        });

        // // Ngrok For Development
        // if (config('app.env') === 'production') {
        //     URL::forceScheme('https');
        // }
    }
}
