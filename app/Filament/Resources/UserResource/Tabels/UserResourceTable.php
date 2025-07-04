<?php

namespace App\Filament\Resources\UserResource\Tabels;

use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Illuminate\Support\Facades\Gate;
use Filament\Tables\Columns\TextColumn;
use Rmsramos\Activitylog\Actions\ActivityLogTimelineTableAction;
use STS\FilamentImpersonate\Tables\Actions\Impersonate;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\ViewAction;

class UserResourceTable
{
    public static function make(): array
    {
        return [
            Split::make([
                ImageColumn::make('avatar_url')
                    ->searchable()
                    ->circular()
                    ->grow(false)
                    ->getStateUsing(fn($record) => $record->avatar_url ?: 'https://ui-avatars.com/api/?name=' . urlencode($record->name)),
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
                        ->copyable()
                        ->copyMessage('NIK berhasil disalin!')
                        ->copyMessageDuration(1500)
                        ->grow(false),
                ])->alignStart()->visibleFrom('lg')->space(1),
            ]),
        ];
    }

    public static function actions(): array
    {
        return [
            ActivityLogTimelineTableAction::make(__('filament-forms::users.actions.activities'))
                ->visible(fn() => Gate::allows('viewActivities', User::class)),

            Action::make(__('filament-forms::users.actions.set_role'))
                ->icon('heroicon-m-adjustments-vertical')
                ->form([
                    Select::make('role')
                        ->label(__('filament-forms::users.fields.roles'))
                        ->relationship('roles', 'name')
                        ->multiple()
                        ->searchable()
                        ->preload()
                        ->optionsLimit(10)
                        ->getOptionLabelFromRecordUsing(fn($record) => $record->name),
                ])
                ->visible(fn() => Gate::allows('setRole', User::class)),

            Impersonate::make()
                ->label(__('filament-forms::users.actions.impersonate'))
                ->visible(fn() => Gate::allows('impersonate', User::class)),

            ActionGroup::make([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
                RestoreAction::make()
                    ->visible(
                        fn($record) => Gate::allows('restore', $record) &&
                            method_exists($record, 'trashed') &&
                            $record->trashed()
                    ),

                ForceDeleteAction::make()
                    ->visible(
                        fn($record) => Gate::allows('forceDelete', $record) &&
                            method_exists($record, 'trashed') &&
                            $record->trashed()
                    ),
            ])->button()->label(__('filament-forms::users.actions.group'))
        ];
    }
}