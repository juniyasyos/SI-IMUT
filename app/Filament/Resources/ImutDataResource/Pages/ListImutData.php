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
            Actions\CreateAction::make(),
        ];
    }

    protected function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();

        if ($user->can('view_all_data_imut::data')) {
            return $query;
        }

        if ($user->can('view_by_unit_kerja_imut::data')) {
            return $query->whereHas('unitKerja', function ($q) use ($user) {
                $q->where('unit_kerja_id', $user->unit_kerja_id);
            });
        }

        return $query->whereRaw('1 = 0');
    }
}
