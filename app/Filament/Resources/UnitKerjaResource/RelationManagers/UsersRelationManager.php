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
                        ->circular()
                        ->grow(false)
                        ->getStateUsing(fn($record) => $record->avatar_url ?: "https://ui-avatars.com/api/?name=" . urlencode($record->name)),
                    Stack::make([
                        TextColumn::make('name')
                            ->label(__('filament-forms::users.fields.name'))
                            ->weight(FontWeight::Bold),
                        TextColumn::make('position.name')
                            ->label(__('filament-forms::users.fields.position'))
                            ->sortable()
                            ->icon('heroicon-o-briefcase')
                            ->badge()
                            ->color(''),
                    ])->alignStart()->space(1),
                    Stack::make([
                        TextColumn::make('roles.name')
                            ->label(__('filament-forms::users.fields.roles'))
                            ->icon('heroicon-o-shield-check')
                            ->grow(false),
                        TextColumn::make('email')
                            ->label(__('filament-forms::users.fields.email'))
                            ->icon('heroicon-m-envelope')
                            ->grow(false),
                    ])->alignStart()->visibleFrom('lg')->space(1)
                ]),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label(__('filament-forms::users.buttons.add_user'))
                    ->color('primary')
                    ->form(fn() => [
                        Select::make('recordId')
                            ->label(__('filament-forms::users.forms.user.title'))
                            ->options(fn() => User::with('position')->get()
                                ->mapWithKeys(fn($user) => [
                                    $user->id => "{$user->name} - " . ($user->position->name ?? __('filament-forms::users.forms.position.no_position')),
                                ])
                                ->toArray())
                            ->searchable()
                            ->preload()
                            ->required(),
                    ])
                    ->preloadRecordSelect()
                    ->recordSelectSearchColumns(['name']),
            ])
            ->actions([
                Tables\Actions\DetachAction::make()
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ]);
    }
}
