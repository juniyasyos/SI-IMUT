<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ImutDataResource\Pages;
use App\Filament\Resources\ImutDataResource\Pages\ImutDataOverview;
use App\Filament\Resources\ImutDataResource\Pages\ImutDataUnitKerjaOverview;
use App\Filament\Resources\ImutDataResource\Pages\SummaryImutDataDiagram;
use App\Filament\Resources\ImutDataResource\RelationManagers\ProfilesRelationManager;
use App\Models\ImutData;
use App\Models\RegionType;
use App\Models\User;
use Awcodes\TableRepeater\Components\TableRepeater;
use Awcodes\TableRepeater\Header;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action as ActionTable;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

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
            Tabs::make('')
                ->columnSpan(['lg' => 2])
                ->tabs([
                    Tab::make('📋 Form Profil Indikator')
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
                                        if (! ($user->can('create_imut::category') && $user->can('update_imut::category'))) {
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

                                TextInput::make('created_by_display')
                                    ->label('Dibuat oleh')
                                    ->default(fn () => Auth::user()?->name)
                                    ->readOnly()
                                    ->dehydrated(false),

                                Hidden::make('created_by')
                                    ->default(fn () => Auth::id()),

                            ]),
                        ]),

                    Tab::make('📍 Benchmarking')
                        ->schema([
                            Tabs::make('Benchmark Tabs')
                                ->tabs(
                                    RegionType::all()->map(function ($regionType) {
                                        $headers = [
                                            Header::make('year')->label('Tahun')->width('100px'),
                                            Header::make('month')->label('Bulan')->width('100px'),
                                            Header::make('benchmark_value')->label('Nilai Benchmark (%)')->width('180px'),
                                        ];

                                        $schema = [
                                            TextInput::make('year')
                                                ->numeric()
                                                ->minValue(2000)
                                                ->maxValue(now()->year + 1)
                                                ->placeholder(now()->year)
                                                ->required(),

                                            Select::make('month')
                                                ->options([
                                                    1 => 'Januari',
                                                    2 => 'Februari',
                                                    3 => 'Maret',
                                                    4 => 'April',
                                                    5 => 'Mei',
                                                    6 => 'Juni',
                                                    7 => 'Juli',
                                                    8 => 'Agustus',
                                                    9 => 'September',
                                                    10 => 'Oktober',
                                                    11 => 'November',
                                                    12 => 'Desember',
                                                ])
                                                ->required(),

                                            TextInput::make('benchmark_value')
                                                ->numeric()
                                                ->step(0.01)
                                                ->suffix('%')
                                                ->placeholder('Contoh: 85.5')
                                                ->required(),
                                        ];

                                        $schema = array_merge([
                                            TextInput::make('region_name')
                                                ->placeholder($regionType->type === 'provinsi' ? 'Contoh: Jawa Barat' : 'Contoh: RS Harapan Sehat')
                                                ->required(),
                                            Hidden::make('region_type_id')
                                                ->default($regionType->id),
                                        ], $schema);

                                        array_unshift($headers, Header::make('region_name')->label($regionType->type)->width('200px'));

                                        return Tab::make(ucfirst($regionType->type))
                                            ->schema([
                                                TableRepeater::make("{$regionType->type}_benchmarkings")
                                                    ->label('')
                                                    ->streamlined()
                                                    ->relationship('benchmarkings', fn ($query) => $query->where('region_type_id', $regionType->id))
                                                    ->headers($headers)
                                                    ->schema($schema)
                                                    ->defaultItems(1)
                                                    ->cloneable()
                                                    ->reorderable()
                                                    ->addable()
                                                    ->deletable()
                                                    ->columnSpan('full'),
                                                // ->extraActions([
                                                //     Action::make('exportData')
                                                //         ->icon('heroicon-m-inbox-arrow-down')
                                                //         ->action(function (TableRepeater $component): void {
                                                //             Notification::make('export_data')
                                                //                 ->success()
                                                //                 ->title('Data exported.')
                                                //                 ->send();
                                                //         }),
                                                // ]),
                                            ]);
                                    })->push(
                                        Tab::make('➕ Tambah Region Type')->schema([
                                            Actions::make([
                                                Action::make('create_region_type')
                                                    ->icon('heroicon-m-plus')
                                                    ->tooltip('Tambah Region Type baru')
                                                    ->modalHeading('Tambah Region Type')
                                                    ->form([
                                                        TextInput::make('type')
                                                            ->required()
                                                            ->label('Nama Region Type')
                                                            ->placeholder('Contoh: provinsi, rumah sakit, nasional'),
                                                    ])
                                                    ->action(function (array $data) {
                                                        RegionType::create([
                                                            'type' => $data['type'],
                                                        ]);

                                                        Notification::make()
                                                            ->title('Berhasil')
                                                            ->body('Region Type berhasil ditambahkan.')
                                                            ->success()
                                                            ->send();

                                                        redirect(request()->header('Referer'));
                                                    }),

                                                Action::make('goto_region_type_list')
                                                    ->icon('heroicon-m-list-bullet')
                                                    ->tooltip('Lihat daftar semua Region Type')
                                                    ->url(fn () => ImutDataResource::getUrl('bencmarking-region-type'))
                                                    ->openUrlInNewTab(),
                                            ])
                                                ->label('Aksi'),
                                        ])

                                    )
                                        ->toArray()
                                ),

                        ]),
                    // ->visible(fn (?Model $record) => $record !== null
                    //     // && request()->is('imut-datas/*/profile/edit=*')
                    //     && $record->imutData->categories->is_benchmark_category === 1),

                ]),
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

                    return ImutData::query()
                        ->whereHas('unitKerja', function ($query) use ($unitKerjaIds) {
                            $query->whereIn('unit_kerja.id', $unitKerjaIds);
                        });
                }

                return ImutData::query()->whereRaw('1 = 0');
            })
            ->columns([
                TextColumn::make('title')
                    ->label(__('filament-forms::imut-data.fields.title'))
                    ->tooltip(fn (ImutData $record): string => $record->description ?? '-')
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
                    ->disabled(fn () => \Illuminate\Support\Facades\Gate::any([
                        'update_imut::data',
                    ]))
                    ->tooltip(fn (Model $record) => $record->status ? 'Active' : 'Unactive')
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
                    ->visible(fn () => \Illuminate\Support\Facades\Gate::allows('view_any_imut::profile', User::class)),

                ViewAction::make()->slideOver(),
                EditAction::make(),

                ActionGroup::make([
                    ActionTable::make('lihat_berdasarkan_imut_data')
                        ->label('📊 IMUT DATA')
                        ->color('primary')
                        ->url(fn ($record) => SummaryImutDataDiagram::getUrl(['record' => $record->slug])),

                    // ActionTable::make('lihat_berdasarkan_unit_kerja')
                    //     ->label('🏢 Unit Kerja')
                    //     ->color('success')
                    //     ->url(fn ($record) => ImutDataOverview::getUrl(['record' => $record->slug])),

                    \Guava\FilamentModalRelationManagers\Actions\Table\RelationManagerAction::make('unit-kerja-relation')
                        ->slideOver()
                        ->label('🏢 Unit Kerja')
                        ->color('primary')
                        ->relationManager(\App\Filament\Resources\ImutDataResource\RelationManagers\UnitKerjaRelationManager::make()),
                ])->icon('heroicon-s-chart-bar')->label('Lihat Grafik')->button(),

                ActionGroup::make([
                    RestoreAction::make()
                        ->visible(
                            fn ($record) => \Illuminate\Support\Facades\Gate::allows('restore', $record) &&
                                method_exists($record, 'trashed') &&
                                $record->trashed()
                        ),

                    ForceDeleteAction::make()
                        ->visible(
                            fn ($record) => \Illuminate\Support\Facades\Gate::allows('forceDelete', $record) &&
                                method_exists($record, 'trashed') &&
                                $record->trashed()
                        ),
                ]),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),

                    RestoreBulkAction::make()
                        ->visible(fn () => method_exists(static::getModel(), 'bootSoftDeletes')),

                    ForceDeleteBulkAction::make()
                        ->visible(fn () => method_exists(static::getModel(), 'bootSoftDeletes')),
                ]),
            ]);
    }

    public static function getTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = static::getModel()::query();
        $user = \Illuminate\Support\Facades\Auth::user();

        // dd($user); // <-- Ini akan jalan di Filament v3

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
            'edit' => Pages\EditImutData::route('/edit={record:slug}'),
            'create-profile' => \App\Filament\Resources\ImutProfileResource\Pages\CreateImutProfile::route('/{imutDataSlug}/profile/create'),
            'edit-profile' => \App\Filament\Resources\ImutProfileResource\Pages\EditImutProfile::route('/{imutDataSlug}/profile/edit={record}'),
            'bencmarking-region-type' => \App\Filament\Resources\RegionTypeBencmarkingResource\Pages\ListRegionTypeBencmarkings::route('/bencmarkings/region-type'),
            'overview-unit-kerja' => ImutDataUnitKerjaOverview::route('/overview/unit-kerja'),
            'overview-imut-data' => SummaryImutDataDiagram::route('overview/summary-imut-data'),
        ];
    }
}
