<?php

namespace App\Filament\Resources\UnitKerjaResource\RelationManagers;

use App\Models\User;
use Filament\Tables;
use App\Models\ImutData;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Resources\RelationManagers\RelationManager;
use Illuminate\Support\Facades\Gate;

class ImutDataRelationManager extends RelationManager
{
    protected static string $relationship = 'imutData';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('title')
                    ->label(__('filament-forms::imut-data-relationship-user.columns.title'))
                    ->weight(FontWeight::Bold),

                TextColumn::make('categories.short_name')
                    ->label(__('filament-forms::imut-data-relationship-user.columns.category'))
                    ->badge()
                    ->color('success'),
            ])
            ->filters([
                SelectFilter::make('imut_kategori_id')
                    ->label(__('filament-forms::imut-data-relationship-user.filters.category'))
                    ->multiple()
                    ->relationship('categories', 'short_name'),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label(__('filament-forms::imut-data-relationship-user.actions.attach.label'))
                    ->color('primary')
                    ->recordSelect(function ($livewire) {
                        // Ambil ID yang sudah terkait
                        $relatedIds = $livewire->ownerRecord->imutData()->pluck('id')->toArray();

                        return Select::make('recordId')
                            ->label(__('filament-forms::imut-data-relationship-user.form.select_imut.label'))
                            ->placeholder(__('filament-forms::imut-data-relationship-user.form.select_imut.placeholder'))
                            ->helperText(__('filament-forms::imut-data-relationship-user.form.select_imut.helper'))
                            ->options(
                                ImutData::with('categories')
                                    ->whereNotIn('id', $relatedIds)
                                    ->get()
                                    ->mapWithKeys(fn($imut) => [
                                        $imut->id => "({$imut->categories->short_name}) - {$imut->title}",
                                    ])
                                    ->toArray()
                            )
                            ->searchable()
                            ->preload()
                            ->required();
                    })
                    ->visible(fn() => Gate::allows('attach_imut_data_to_unit_kerja_unit::kerja', User::class))
                    ->modalHeading(__('filament-forms::imut-data-relationship-user.modal.heading'))
                    ->modalSubmitActionLabel(__('filament-forms::imut-data-relationship-user.modal.submit_label'))
                    ->preloadRecordSelect()
                    ->attachAnother(false)
                    ->recordSelectSearchColumns(['title'])
            ])
            ->actions([
                Tables\Actions\DetachAction::make()
                    ->requiresConfirmation()
                    ->visible(fn() => Gate::allows('attach_imut_data_to_unit_kerja_unit::kerja', User::class))
                    ->label(__('filament-forms::imut-data-relationship-user.actions.detach.label'))
                    ->modalHeading(__('filament-forms::imut-data-relationship-user.actions.detach.heading'))
                    ->modalDescription(__('filament-forms::imut-data-relationship-user.actions.detach.description')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make()
                        ->label(__('filament-forms::imut-data-relationship-user.actions.detach_bulk.label')),
                ]),
            ]);
    }
}
