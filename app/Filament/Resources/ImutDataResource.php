<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\User;
use App\Models\ImutData;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\ImutKategori;
use Filament\Resources\Resource;
use Filament\Forms\Components\Grid;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Section;
use Illuminate\Database\Query\Builder;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\ActionGroup;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Forms\Components\Actions\Action;
use App\Filament\Resources\ImutDataResource\Pages;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use App\Filament\Resources\ImutDataResource\RelationManagers\ProfilesRelationManager;
use Filament\Tables\Actions\{EditAction, ViewAction, DeleteAction, RestoreAction, ForceDeleteAction};
use Filament\Tables\Actions\{BulkActionGroup, DeleteBulkAction, RestoreBulkAction, ForceDeleteBulkAction};

class ImutDataResource extends Resource implements HasShieldPermissions
{
    use \App\Traits\HasActiveIcon;
    protected static ?string $model = ImutData::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?int $navigationSort = 3;

    public static function getGloballySearchableAttributes(): array
    {
        return ['title'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->title;
    }

    public static function getGlobalSearchResultUrl(Model $record): ?string
    {
        return static::getUrl(name: 'edit', parameters: ['record' => $record]);
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            __('filament-forms::imut-data.fields.imut_kategori_id') => $record->kategori->category_name ?? '-',
        ];
    }

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'view_all_data',
            'view_by_unit_kerja',
            'create',
            'update',
            'restore',
            'restore_any',
            'replicate',
            'reorder',
            'delete',
            'delete_any',
            'force_delete',
            'force_delete_any',
        ];
    }

    public static function getLabel(): ?string
    {
        return __('filament-forms::imut-data.navigation.title');
    }

    public static function getPluralLabel(): ?string
    {
        return __('filament-forms::imut-data.navigation.plural');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('filament-forms::imut-data.navigation.group');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make()
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('title')
                            ->label(__('filament-forms::imut-data.fields.title'))
                            ->placeholder(__('filament-forms::imut-data.form.main.title_placeholder'))
                            ->helperText(__('filament-forms::imut-data.form.main.helper_text'))
                            ->prefixIcon('heroicon-o-pencil-square')
                            ->required()
                            ->columnSpan(2)
                            ->maxLength(255),

                        TextInput::make('slug')
                            ->label(__('filament-forms::imut-data.fields.slug'))
                            ->readOnly()
                            ->disabled()
                            ->extraAttributes(['class' => 'bg-gray-100 text-gray-500'])
                            ->visibleOn('edit')
                            ->columnSpan(1),

                        Select::make('imut_kategori_id')
                            ->label(__('filament-forms::imut-data.fields.imut_kategori_id'))
                            ->relationship('categories', 'category_name', function ($query) {
                                $user = \Illuminate\Support\Facades\Auth::user();
                                if (!($user->can('create_imut::category') && $user->can('update_imut::category'))) {
                                    $query->where('is_use_global', true);
                                }
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->hint(__('filament-forms::imut-data.form.main.category_hint')),

                        Toggle::make('status')
                            ->label(__('filament-forms::imut-data.fields.status'))
                            ->helperText(__('filament-forms::imut-data.form.main.status_helper'))
                            ->inline(true)
                            ->columnSpan(2)
                            ->onColor('success')
                            ->required()
                            ->default(true)
                            ->columnSpan(1),

                        RichEditor::make('description')
                            ->label(__('filament-forms::imut-data.fields.description'))
                            ->placeholder(__('filament-forms::imut-data.form.main.description_placeholder'))
                            ->helperText(__('filament-forms::imut-data.form.main.description_helper'))
                            ->columnSpan(2)
                            ->maxLength(255),

                        Select::make('created_by')
                            ->label('Dibuat oleh')
                            ->options(User::pluck('name', 'id'))
                            ->default(fn() => \Illuminate\Support\Facades\Auth::id())
                            ->disabled()
                            ->columnSpanFull(),

                    ])
                ])
                ->heading(__('filament-forms::imut-data.form.main.title')),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(function () {
                $user = \Illuminate\Support\Facades\Auth::user();

                if ($user->can('view_all_data_imut::data')) {
                    return ImutData::query();
                }

                if ($user->can('view_by_unit_kerja_imut::data')) {
                    $unitKerjaIds = $user->unitKerjas->pluck('id')->toArray();
                    // dd([
                    //     $unitKerjaIds,
                    //     ImutData::query()
                    //         ->whereHas('unitKerja', function ($query) use ($unitKerjaIds) {
                    //             $query->whereIn('imut_data_unit_kerja.unit_kerja_id', $unitKerjaIds);
                    //         })->get()
                    // ]);

                    $pivotData = \Illuminate\Support\Facades\DB::table('imut_data_unit_kerja')
                        ->whereIn('unit_kerja_id', $unitKerjaIds)
                        ->get();

                    dd($pivotData);
                    
                    return ImutData::query()
                        ->whereHas('unitKerja', function ($query) use ($unitKerjaIds) {
                            $query->whereIn('unit_kerja.id', 1);
                        });
                }

                return ImutData::query()->whereRaw('1 = 0');
            })
            ->columns([
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
            ])
            ->filters([
                TrashedFilter::make(),
                SelectFilter::make('imut_kategori_id')
                    ->label('Kategori IMUT')
                    ->preload()
                    ->multiple()
                    ->relationship('categories', 'short_name')
                    ->searchable(),

            ])
            ->actions([
                \Guava\FilamentModalRelationManagers\Actions\Table\RelationManagerAction::make('user-relation-manager')
                    ->slideOver()
                    ->label('Imut Profile')
                    ->color('success')
                    ->icon('heroicon-c-document-plus')
                    ->relationManager(ProfilesRelationManager::make())
                    ->visible(fn() => \Illuminate\Support\Facades\Gate::allows('view_any_imut::profile', User::class)),

                ViewAction::make()->slideOver(),
                EditAction::make(),
                ActionGroup::make([
                    RestoreAction::make()
                        ->visible(
                            fn($record) =>
                            \Illuminate\Support\Facades\Gate::allows('restore', $record) &&
                                method_exists($record, 'trashed') &&
                                $record->trashed()
                        ),

                    ForceDeleteAction::make()
                        ->visible(
                            fn($record) =>
                            \Illuminate\Support\Facades\Gate::allows('forceDelete', $record) &&
                                method_exists($record, 'trashed') &&
                                $record->trashed()
                        ),
                ])
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),

                    RestoreBulkAction::make()
                        ->visible(fn() => method_exists(static::getModel(), 'bootSoftDeletes')),

                    ForceDeleteBulkAction::make()
                        ->visible(fn() => method_exists(static::getModel(), 'bootSoftDeletes'))
                ]),
            ]);
    }

    // ✅ Ini cara resmi di Filament v3
    public static function getTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = static::getModel()::query();
        $user = \Illuminate\Support\Facades\Auth::user();

        dd($user); // <-- Ini akan jalan di Filament v3

        if ($user->can('view_all_data_imut::data')) {
            return $query;
        }

        if ($user->can('view_by_unit_kerja_imut::data')) {
            return $query->whereHas('unitKerja', function ($q) use ($user) {
                $q->where('unit_kerja_id', $user->unit_kerja_id);
            });
        }

        return $query->whereRaw('1 = 0');
    }


    public static function getRelations(): array
    {
        return [
            ProfilesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListImutData::route('/'),
            'create' => Pages\CreateImutData::route('/create'),
            'edit' => Pages\EditImutData::route('/{record:slug}/edit'),
            'create-profile' => \App\Filament\Resources\ImutProfileResource\Pages\CreateImutProfile::route('/{imutDataSlug}/profile/create'),
            'edit-profile' => \App\Filament\Resources\ImutProfileResource\Pages\EditImutProfile::route('/{imutDataSlug}/profile/{record}/edit'),
            'bencmarking' => \App\Filament\Resources\RegionTypeBencmarkingResource\Pages\ListRegionTypeBencmarkings::route('/bencmarkings'),
        ];
    }
}
