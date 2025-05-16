<?php

namespace App\Providers\Filament;

use Filament\Pages;
use Filament\Panel;
use App\Models\User;
use Filament\Widgets;
use Filament\PanelProvider;
use App\Filament\Pages\Login;
use App\Settings\KaidoSetting;
use Filament\Support\Colors\Color;
use Filament\Forms\Components\FileUpload;
use Filament\Http\Middleware\Authenticate;
use Jeffgreco13\FilamentBreezy\BreezyCore;
use Rmsramos\Activitylog\ActivitylogPlugin;
use Juniyasyos\FilamentPWA\FilamentPWAPlugin;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Cookie\Middleware\EncryptCookies;
use DutchCodingCompany\FilamentSocialite\Provider;
use Juniyasyos\DashStackTheme\DashStackThemePlugin;
use DiogoGPinto\AuthUIEnhancer\AuthUIEnhancerPlugin;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Laravel\Socialite\Contracts\User as SocialiteUserContract;
use DutchCodingCompany\FilamentSocialite\FilamentSocialitePlugin;
use Juniyasyos\FilamentLaravelBackup\FilamentLaravelBackupPlugin;

class AdminPanelProvider extends PanelProvider
{
    private ?KaidoSetting $settings = null;
    //constructor
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
            ->when($this->settings->login_enabled ?? true, fn($panel) => $panel->login(Login::class))
            ->when($this->settings->registration_enabled ?? true, fn($panel) => $panel->registration())
            ->when($this->settings->password_reset_enabled ?? true, fn($panel) => $panel->passwordReset())
            ->emailVerification()
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                // Widgets\AccountWidget::class,
                // Widgets\FilamentInfoWidget::class,
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
                'System & Configurations'
            ])
            ->plugins(
                $this->getPlugins()
            )
            ->databaseNotifications();
    }

    private function getPlugins(): array
    {
        $plugins = [
            DashStackThemePlugin::make(),
            FilamentShieldPlugin::make(),
            FilamentPWAPlugin::make(),
            FilamentLaravelBackupPlugin::make(),
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
                ->avatarUploadComponent(fn($fileUpload) => $fileUpload->disableLabel())
                ->enableBrowserSessions(condition: true)
                ->avatarUploadComponent(
                    fn() => FileUpload::make('avatar_url')
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
                            ->stateless(false)
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
