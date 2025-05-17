<?php

namespace App\Filament\Resources\UnitKerjaResource\RelationManagers;

use App\Models\User;
use Filament\Tables;
use App\Models\Position;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Filters\SelectFilter;
use Filament\Resources\RelationManagers\RelationManager;
use Illuminate\Support\Facades\Gate;

class UsersRelationManager extends RelationManager
{
    protected static string $relationship = 'users';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Split::make([
                    ImageColumn::make('avatar_url')
                        ->searchable()
                        ->circular()
                        ->grow(false)
                        ->getStateUsing(fn($record) => $record->avatar_url ?: "https://ui-avatars.com/api/?name=" . urlencode($record->name)),
                    Stack::make([
                        TextColumn::make('name')
                            ->label(__('filament-forms::users.fields.name'))
                            ->searchable()
                            ->weight(FontWeight::Bold),
                        TextColumn::make('position.name')
                            ->label(__('filament-forms::users.fields.position'))
                            ->searchable()
                            ->sortable()
                            ->icon('heroicon-o-briefcase')
                            ->badge()
                            ->color(''),
                    ])->alignStart()->space(1),
                    Stack::make([
                        TextColumn::make('roles.name')
                            ->label(__('filament-forms::users.fields.roles'))
                            ->searchable()
                            ->icon('heroicon-o-shield-check')
                            ->grow(false),
                        TextColumn::make('nik')
                            ->label(__('filament-forms::users.fields.email'))
                            ->icon('heroicon-m-finger-print')
                            ->searchable()
                            ->grow(false),
                    ])->alignStart()->visibleFrom('lg')->space(1)
                ])
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->color('primary')
                    ->form(fn() => [
                        Select::make('recordId')
                            ->options(function () {
                                $relatedIds = $this->getRelationship()->pluck('id')->toArray();

                                return User::with('position')
                                    ->whereNotIn('id', $relatedIds)
                                    ->get()
                                    ->mapWithKeys(fn($user) => [
                                        $user->id => "{$user->name} - " . ($user->position->name ?? '-'),
                                    ])
                                    ->toArray();
                            })
                            ->searchable()
                            ->preload()
                            ->required(),
                    ])
                    ->attachAnother(false)
                    ->preloadRecordSelect()
                    ->visible(fn() => Gate::allows('attach_user_to_unit::kerja', User::class))
                    ->recordSelectSearchColumns(['name']),

            ])
            ->actions([
                Tables\Actions\DetachAction::make()
                    ->visible(fn() => Gate::allows('attach_user_to_unit::kerja', User::class))
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ]);
    }
}
