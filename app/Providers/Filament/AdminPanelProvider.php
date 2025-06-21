<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Dashboard;
use App\Filament\Pages\Login;
use App\Filament\Widgets\AccountWidget;
use App\Filament\Widgets\FilamentInfoWidget;
use App\Models\User;
use App\Settings\KaidoSetting;
use Asmit\ResizedColumn\ResizedColumnPlugin;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use DiogoGPinto\AuthUIEnhancer\AuthUIEnhancerPlugin;
use DutchCodingCompany\FilamentSocialite\FilamentSocialitePlugin;
use DutchCodingCompany\FilamentSocialite\Provider;
use Filament\Forms\Components\FileUpload;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Jeffgreco13\FilamentBreezy\BreezyCore;
use Juniyasyos\DashStackTheme\DashStackThemePlugin;
use Juniyasyos\FilamentLaravelBackup\FilamentLaravelBackupPlugin;
use Juniyasyos\FilamentMediaManager\FilamentMediaManagerPlugin;
use Juniyasyos\FilamentPWA\FilamentPWAPlugin;
use Laravel\Socialite\Contracts\User as SocialiteUserContract;
use Leandrocfe\FilamentApexCharts\FilamentApexChartsPlugin;
use Rmsramos\Activitylog\ActivitylogPlugin;

class AdminPanelProvider extends PanelProvider
{
    private ?KaidoSetting $settings = null;

    // constructor
    public function __construct()
    {
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
                $this->settings = app(KaidoSetting::class);
            }
        } catch (\Exception $e) {
            $this->settings = null;
        }
    }

    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('')
            ->login(Login::class)
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->when($this->settings->login_enabled ?? true, fn ($panel) => $panel->login(Login::class))
            ->when($this->settings->registration_enabled ?? true, fn ($panel) => $panel->registration())
            ->when($this->settings->password_reset_enabled ?? true, fn ($panel) => $panel->passwordReset())
            ->emailVerification()
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->sidebarCollapsibleOnDesktop(true)
            ->authMiddleware([
                Authenticate::class,
            ])
            ->middleware([])
            ->navigationGroups([
                'User & Access Control',
                'Quality Indicators',
                'System & Configurations',
            ])
            ->plugins(
                $this->getPlugins()
            )
            ->databaseNotifications();
    }

    private function getPlugins(): array
    {
        $plugins = [
            ResizedColumnPlugin::make(),
            FilamentApexChartsPlugin::make(),
            DashStackThemePlugin::make(),
            FilamentShieldPlugin::make(),
            FilamentPWAPlugin::make(),
            FilamentLaravelBackupPlugin::make(),
            FilamentMediaManagerPlugin::make()->allowUserAccess()->allowSubFolders(),
            ActivitylogPlugin::make()
                ->navigationIcon('heroicon-o-clock')
                ->navigationItem()
                ->navigationGroup('User & Access Control')
                ->label('Audit & Activity Logs'),
            AuthUIEnhancerPlugin::make()
                ->showEmptyPanelOnMobile(false)
                ->formPanelPosition('right')
                ->formPanelWidth('60%')
                ->emptyPanelView('auth.custom-page-auth'),
            BreezyCore::make()
                ->myProfile(
                    shouldRegisterUserMenu: true,
                    shouldRegisterNavigation: false,
                    navigationGroup: 'System & Configuration',
                    hasAvatars: true,
                    slug: 'my-profile'
                )
                ->avatarUploadComponent(fn ($fileUpload) => $fileUpload->disableLabel())
                ->enableBrowserSessions(condition: true)
                ->avatarUploadComponent(
                    fn () => FileUpload::make('avatar_url')
                        ->image()
                        ->disk('public')
                )
                ->enableTwoFactorAuthentication(),
        ];

        if ($this->settings->sso_enabled ?? true) {
            $plugins[] =
                FilamentSocialitePlugin::make()
                    ->providers([
                        Provider::make('google')
                            ->label('Google')
                            ->icon('fab-google')
                            ->color(Color::hex('#2f2a6b'))
                            ->outlined(true)
                            ->stateless(false),
                    ])->registration(true)
                    ->createUserUsing(function (string $provider, SocialiteUserContract $oauthUser, FilamentSocialitePlugin $plugin) {
                        $user = User::firstOrNew([
                            'email' => $oauthUser->getEmail(),
                        ]);
                        $user->name = $oauthUser->getName();
                        $user->email = $oauthUser->getEmail();
                        $user->email_verified_at = now();
                        $user->save();

                        return $user;
                    });
        }

        return $plugins;
    }
}
