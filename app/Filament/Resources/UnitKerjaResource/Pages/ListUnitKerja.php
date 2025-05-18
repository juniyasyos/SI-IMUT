<?php

namespace App\Filament\Resources\UnitKerjaResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\UnitKerjaResource;
use Illuminate\Support\Facades\Auth;

class ListUnitKerja extends ListRecords
{
    protected static string $resource = UnitKerjaResource::class;

    // public function mount(): void
    // {
    //     parent::mount();

    //     $user = Auth::user();

    //     // Menampilkan semua permission user yang aktif
    //     $permissions = $user->getAllPermissions()->pluck('name');

    //     dd($permissions); // Lihat daftar permission yang dimiliki
    // }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label(__('filament-forms::unit-kerja.actions.add')),
        ];
    }
}
