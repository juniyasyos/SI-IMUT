<?php

namespace App\Settings;

use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Spatie\LaravelSettings\Settings;

class KaidoSetting extends Settings
{
    public string $site_name;

    public bool $site_active;
    public bool $registration_enabled;
    public bool $login_enabled;
    public bool $password_reset_enabled;
    public bool $sso_enabled;

    public static function group(): string
    {
        return 'KaidoSetting';
    }
}
