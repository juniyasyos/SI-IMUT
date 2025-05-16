<?php

namespace App\Filament\Resources\RegionTypeBencmarkingResource\Pages;

use App\Filament\Resources\RegionTypeBencmarkingResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateRegionTypeBencmarking extends CreateRecord
{
    protected static string $resource = RegionTypeBencmarkingResource::class;
    protected static bool $canCreateAnother = false;

    //customize redirect after create
    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
