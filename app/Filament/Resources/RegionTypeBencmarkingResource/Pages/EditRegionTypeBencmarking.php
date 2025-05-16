<?php

namespace App\Filament\Resources\RegionTypeBencmarkingResource\Pages;

use App\Filament\Resources\RegionTypeBencmarkingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRegionTypeBencmarking extends EditRecord
{
    protected static string $resource = RegionTypeBencmarkingResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }

    //customize redirect after create
    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
