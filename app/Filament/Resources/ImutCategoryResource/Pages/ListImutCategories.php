<?php

namespace App\Filament\Resources\ImutCategoryResource\Pages;

use Filament\Actions;
use App\Models\ImutCategory;
use Illuminate\Support\Facades\Gate;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\ImutCategoryResource;

class ListImutCategories extends ListRecords
{
    protected static string $resource = ImutCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label(__('filament-forms::imut-category.buttons.add_data'))
                ->visible(fn() => Gate::allows('create_imut::category', ImutCategory::class))
                ->icon('heroicon-m-plus'),
        ];
    }
}
