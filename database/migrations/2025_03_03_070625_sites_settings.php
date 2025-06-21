<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

class SitesSettings extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('sites.site_name', 'SIIMUT RS');
        $this->migrator->add('sites.site_description', 'Platform pemantau dan analisis indikator mutu rumah sakit.');
        $this->migrator->add('sites.site_keywords', 'SIIMUT, mutu layanan, KARS, SNARS, rumah sakit');
        $this->migrator->add('sites.site_profile', 'Sistem Indikator Mutu (SIIMUT)');
        $this->migrator->add('sites.site_logo', '');
        $this->migrator->add('sites.site_author', 'Rumah Sakit Citra Husada');
        $this->migrator->add('sites.site_address', 'Indonesia');
        $this->migrator->add('sites.site_email', 'support@siimutoptimize.id');
        $this->migrator->add('sites.site_phone', '+62-xxx-xxxx-xxx');
        $this->migrator->add('sites.site_phone_code', '+62');
        $this->migrator->add('sites.site_location', 'Indonesia');
        $this->migrator->add('sites.site_currency', 'IDR');
        $this->migrator->add('sites.site_language', 'id');
        $this->migrator->add('sites.site_social', [
            'github' => 'https://github.com/juniyasyos/siimut-optimize',
            'github-packagist' => 'https://packagist.org/packages/juniyasyos/siimut-optimize',
        ]);
    }
}