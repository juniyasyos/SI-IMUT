<?php

namespace App\Filament\Resources\ImutDataResource\Pages;

use App\Filament\Resources\ImutDataResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListImutData extends ListRecords
{
    protected static string $resource = ImutDataResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('goto_region_type_list')
                ->icon('heroicon-m-list-bullet')
                ->color('gray')
                ->tooltip('Lihat daftar semua Region Type')
                ->url(fn() => ImutDataResource::getUrl('bencmarking')),
            Actions\CreateAction::make()
        ];
    }
}
