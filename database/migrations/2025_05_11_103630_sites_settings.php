<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

class SitesSettings extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('sites.site_name', 'Juniyasyos');
        $this->migrator->add('sites.site_description', 'Solusi Kreatif dan Teknologi');
        $this->migrator->add('sites.site_keywords', 'Desain, Pemasaran, Pemrograman');
        $this->migrator->add('sites.site_profile', '');
        $this->migrator->add('sites.site_logo', '');
        $this->migrator->add('sites.site_author', 'Juniyasyos');
        $this->migrator->add('sites.site_address', 'Indonesia');
        $this->migrator->add('sites.site_email', 'info@juniyasyos.com');
        $this->migrator->add('sites.site_phone', '+62xxxxxxxxxxx');
        $this->migrator->add('sites.site_phone_code', '+62');
        $this->migrator->add('sites.site_location', 'Indonesia');
        $this->migrator->add('sites.site_currency', 'IDR');
        $this->migrator->add('sites.site_language', 'Indonesia');
        $this->migrator->add('sites.site_social', []);
    }
}
