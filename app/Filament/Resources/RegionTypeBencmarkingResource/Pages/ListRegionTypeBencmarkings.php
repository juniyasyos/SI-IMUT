<?php

namespace App\Filament\Resources\RegionTypeBencmarkingResource\Pages;

use App\Filament\Resources\ImutDataResource;
use App\Filament\Resources\RegionTypeBencmarkingResource;
use Filament\Resources\Pages\ListRecords;

class ListRegionTypeBencmarkings extends ListRecords
{
    protected static string $resource = RegionTypeBencmarkingResource::class;

    public function getBreadcrumbs(): array
    {
        return [
            ImutDataResource::getUrl() => 'Imut Datas',
            'Benchmarking Region Types ',
            url()->current() => 'List',
        ];
    }
}
