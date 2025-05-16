<?php

namespace App\Filament\Resources\RegionTypeBencmarkingResource\Pages;

use App\Models\ImutData;
use App\Models\RegionTypeBencmarking;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\RegionTypeBencmarkingResource;

class ListRegionTypeBencmarkings extends ListRecords
{
    protected static string $resource = RegionTypeBencmarkingResource::class;

    public function getBreadcrumbs(): array
    {
        return [
            route('filament.admin.resources.imut-datas.index') => 'Imut Datas',
            url()->current() => 'Benchmarkings',
        ];
    }
}
