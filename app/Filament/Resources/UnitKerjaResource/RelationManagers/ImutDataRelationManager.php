<?php

namespace App\Filament\Resources\UnitKerjaResource\RelationManagers;

use App\Filament\Resources\ImutDataResource;
use App\Models\ImutData;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
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
                    ->searchable()
                    ->weight(FontWeight::Bold),

                TextColumn::make('categories.short_name')
                    ->label(__('filament-forms::imut-data-relationship-user.columns.category'))
                    ->badge()
                    ->searchable()
                    ->color(function ($record) {
                        $colors = ['primary', 'success', 'warning', 'danger', 'info', 'gray'];
                        $id = $record->categories->id ?? 0;

                        return $colors[$id % count($colors)];
                    })
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),

                \Archilex\ToggleIconColumn\Columns\ToggleIconColumn::make('status')
                    ->label(__('filament-forms::imut-data.fields.status'))
                    ->translateLabel()
                    ->alignCenter()
                    ->size('xl')
                    ->disabled(fn() => \Illuminate\Support\Facades\Gate::any([
                        'update_imut::data',
                    ]))
                    ->tooltip(fn(Model $record) => $record->status ? 'Active' : 'Unactive')
                    ->sortable(),

            ])
            ->filters([
                SelectFilter::make('imut_kategori_id')
                    ->label(__('filament-forms::imut-data-relationship-user.filters.category'))
                    ->multiple()
                    ->preload()
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
                                    ->where('status', true)
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
                    ->recordSelectSearchColumns(['title']),
            ])
            ->actions([
                Tables\Actions\Action::make('edit')
                    ->label(__('Edit'))
                    ->icon('heroicon-m-pencil-square')
                    ->url(fn($record) => ImutDataResource::getUrl('edit', ['record' => $record->slug]))
                    ->color('primary')
                    ->visible(fn() => Gate::allows('update_imut::data')),
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
