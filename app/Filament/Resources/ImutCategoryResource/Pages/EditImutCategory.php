<?php

namespace App\Filament\Resources\ImutCategoryResource\Pages;

use App\Filament\Resources\ImutCategoryResource;
use App\Models\ImutCategory;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Gate;

class EditImutCategory extends EditRecord
{
    protected static string $resource = ImutCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn() => Gate::allows('delete_imut::category', $this->record)),
        ];
    }

    // customize redirect after update/delete
    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
