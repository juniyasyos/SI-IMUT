<?php

namespace App\Filament\Resources;

use App\Filament\Forms\ImutProfileForm;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\ImutProfile;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Tables\Filters\TrashedFilter;
use App\Filament\Resources\ImutProfileResource\Pages;
use Filament\Tables\Actions\{EditAction, ViewAction, DeleteAction, RestoreAction, ForceDeleteAction};
use Filament\Tables\Actions\{BulkActionGroup, DeleteBulkAction, RestoreBulkAction, ForceDeleteBulkAction};

class ImutProfileResource extends Resource
{
    protected static ?string $model = ImutProfile::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static bool $shouldRegisterNavigation = false;

    public static function getLabel(): ?string
    {
        return 'Profil IMUT';
    }

    public static function getPluralLabel(): ?string
    {
        return 'Profil IMUT';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            ...ImutProfileForm::make()
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('version')
                    ->label('Versi')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('indicator_type')
                    ->label('Tipe Indikator')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'process' => 'info',
                        'output' => 'warning',
                        'outcome' => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('responsible_person')
                    ->label('Penanggung Jawab')
                    ->searchable()
                    ->limit(20),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
                RestoreAction::make()->visible(fn(Model $record) => method_exists($record, 'trashed') && $record->trashed()),
                ForceDeleteAction::make()->visible(fn(Model $record) => method_exists($record, 'trashed') && $record->trashed()),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make()
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListImutProfiles::route('/'),
        ];
    }
}
