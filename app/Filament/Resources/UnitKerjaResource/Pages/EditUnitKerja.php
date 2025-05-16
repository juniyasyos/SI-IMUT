<?php

namespace App\Filament\Resources\UnitKerjaResource\Pages;

use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\UnitKerjaResource;

class EditUnitKerja extends EditRecord
{
    protected static string $resource = UnitKerjaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label(__('filament-actions::edit.single.modal.actions.save.label'))
                ->action('save')
                ->requiresConfirmation(false),

            Action::make('delete')
                ->label(__('filament-actions::delete.single.modal.actions.delete.label'))
                ->color('danger')
                ->requiresConfirmation()
                ->action('delete'),
        ];
    }

    protected function getFormActions(): array
    {
        return [];
    }

    //customize redirect after create
    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
