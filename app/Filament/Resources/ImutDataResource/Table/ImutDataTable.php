<?php

namespace App\Filament\Resources\ImutDataResource\Table;

use App\Filament\Resources\ImutDataResource\Pages\ImutDataUnitKerjaOverview;
use App\Filament\Resources\ImutDataResource\Pages\SummaryImutDataDiagram;
use App\Filament\Resources\ImutDataResource\RelationManagers\ProfilesRelationManager;
use App\Models\ImutData;
use App\Models\User;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Filament\Tables\Actions\Action as ActionTable;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\ViewAction;
use Illuminate\Support\Facades\Auth;

class ImutDataTable
{
    public static function query(): Builder
    {
        $user = \Illuminate\Support\Facades\Auth::user();

        if ($user->can('view_all_data_imut::data')) {
            return ImutData::query();
        }

        if ($user->can('view_by_unit_kerja_imut::data')) {
            $unitKerjaIds = $user->unitKerjas->pluck('id')->toArray();

            return ImutData::query()
                ->whereHas('unitKerja', function ($query) use ($unitKerjaIds) {
                    $query->whereIn('unit_kerja.id', $unitKerjaIds);
                })->orWhere('created_by', $user->id);
        }

        return ImutData::query()->whereRaw('1 = 0');
    }

    public static function columns(): array
    {
        return [
            TextColumn::make('title')
                ->label(__('filament-forms::imut-data.fields.title'))
                ->tooltip(fn(ImutData $record): string => $record->description ?? '-')
                ->searchable()
                ->sortable()
                ->limit(60),

            TextColumn::make('categories.short_name')
                ->label(__('filament-forms::imut-data.fields.imut_kategori_id'))
                ->badge()
                ->sortable()
                ->color(function ($record) {
                    $colors = ['primary', 'success', 'warning', 'danger', 'info', 'gray'];
                    $id = $record->categories->id ?? 0;

                    return $colors[$id % count($colors)];
                })
                ->toggleable(isToggledHiddenByDefault: false),

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

            TextColumn::make('created_at')
                ->label(__('filament-forms::imut-data.fields.created_at'))
                ->dateTime('d M Y H:i')
                ->sortable()
                ->icon('heroicon-o-calendar')
                ->toggleable(isToggledHiddenByDefault: true),
        ];
    }

    public static function actions(): array
    {
        return [
            \Guava\FilamentModalRelationManagers\Actions\Table\RelationManagerAction::make('user-relation-manager')
                ->slideOver()
                ->label('Imut Profile')
                ->color('success')
                ->icon('heroicon-c-document-plus')
                ->relationManager(ProfilesRelationManager::make())
                ->visible(fn() => \Illuminate\Support\Facades\Gate::allows('view_any_imut::profile', User::class)),

            ActionTable::make('lihat_berdasarkan_unit_kerja')
                ->label('🏢 Lihat Grafik')
                ->color('success')
                ->visible(function () {
                    $user = Auth::user();

                    return $user->can('view_by_unit_kerja_imut::data') &&
                        ! $user->can('view_all_data_imut::data') &&
                        $user->unitKerjas->isNotEmpty();
                })
                ->url(function ($record) {
                    $user = Auth::user();
                    $unitKerja = $user->unitKerjas->first();

                    if (! $unitKerja) {
                        return '#';
                    }

                    return ImutDataUnitKerjaOverview::getUrl([
                        'record_imut_data' => $record->id,
                        'record_unit_kerja' => $unitKerja->id,
                    ]);
                }),

            EditAction::make(),

            ActionGroup::make([
                ActionTable::make('lihat_berdasarkan_imut_data')
                    ->label('📊 IMUT DATA')
                    ->color('primary')
                    ->visible(fn() => \Illuminate\Support\Facades\Gate::allows('view_all_data_imut::data', User::class))
                    ->url(fn($record) => SummaryImutDataDiagram::getUrl(['record' => $record->slug])),

                \Guava\FilamentModalRelationManagers\Actions\Table\RelationManagerAction::make('unit-kerja-relation')
                    ->slideOver()
                    ->label('🏢 Unit Kerja')
                    ->color('primary')
                    ->visible(fn() => \Illuminate\Support\Facades\Gate::allows('view_all_data_imut::data', User::class))
                    ->relationManager(\App\Filament\Resources\ImutDataResource\RelationManagers\UnitKerjaRelationManager::make()),
            ])
                ->visible(fn() => \Illuminate\Support\Facades\Gate::allows('view_all_data_imut::data', User::class))
                ->icon('heroicon-s-chart-bar')
                ->label('Lihat Grafik')
                ->button(),

            ActionGroup::make([
                RestoreAction::make()
                    ->visible(
                        fn($record) => \Illuminate\Support\Facades\Gate::allows('restore', $record) &&
                            method_exists($record, 'trashed') &&
                            $record->trashed()
                    ),

                ForceDeleteAction::make()
                    ->visible(
                        fn($record) => \Illuminate\Support\Facades\Gate::allows('forceDelete', $record) &&
                            method_exists($record, 'trashed') &&
                            $record->trashed()
                    ),
            ]),
        ];
    }
}