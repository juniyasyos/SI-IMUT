<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('KaidoSetting.site_name', 'Spatie');
        $this->migrator->add('KaidoSetting.site_active', true);
        $this->migrator->add('KaidoSetting.registration_enabled', false);
        $this->migrator->add('KaidoSetting.login_enabled', false);
        $this->migrator->add('KaidoSetting.password_reset_enabled', false);
        $this->migrator->add('KaidoSetting.sso_enabled', false);
    }
};
