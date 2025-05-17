<?php

namespace App\Filament\Resources\UnitKerjaResource\Pages;

use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\UnitKerjaResource;
use Guava\FilamentModalRelationManagers\Actions\Action\RelationManagerAction;
use App\Filament\Resources\UnitKerjaResource\RelationManagers\UsersRelationManager;

class EditUnitKerja extends EditRecord
{
    protected static string $resource = UnitKerjaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            RelationManagerAction::make()
                ->slideOver()
                ->icon('heroicon-o-user')
                ->record($this->getRecord())
                ->label(__('filament-forms::unit-kerja.actions.attach'))
                ->relationManager(UsersRelationManager::make())
        ];
    }

    //customize redirect after create
    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
