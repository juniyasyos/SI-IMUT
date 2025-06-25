<?php

namespace App\Filament\Resources;

use App\Filament\Exports\UnitKerjaExporter;
use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\UnitKerja;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use App\Models\UserUnitKerja;
use Filament\Resources\Resource;
use Awcodes\TableRepeater\Header;
use Filament\Forms\Components\Card;
use Illuminate\Support\Facades\Gate;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Illuminate\Database\Eloquent\Model;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;
use App\Filament\Resources\UnitKerjaResource\Pages;
use Awcodes\TableRepeater\Components\TableRepeater;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms\Components\{TextInput, Textarea, Select, Grid};
use Guava\FilamentModalRelationManagers\Actions\Table\RelationManagerAction;
use App\Filament\Resources\UnitKerjaResource\RelationManagers\UsersRelationManager;
use App\Filament\Resources\UnitKerjaResource\RelationManagers\ImutDataRelationManager;
use Filament\Tables\Actions\ExportAction;

class UnitKerjaResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = UnitKerja::class;

    protected static ?string $slug = 'unit-kerjas';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getPermissionPrefixes(): array
    {
        return [
            // Default permissions
            'view',
            'view_any',
            'create',
            'update',
            'restore',
            'restore_any',
            'delete',
            'delete_any',
            'force_delete',
            'force_delete_any',
            'attach_user_to_unit_kerja',
            'attach_imut_data_to_unit_kerja'
        ];
    }


    public static function getGloballySearchableAttributes(): array
    {
        return ['unit_name'];
    }

    public static function getGlobalSearchResultUrl(Model $record): ?string
    {
        return static::getUrl(name: 'edit', parameters: ['record' => $record]);
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->unit_name;
    }

    public static function getLabel(): ?string
    {
        return __('filament-forms::unit-kerja.navigation.title');
    }

    public static function getPluralLabel(): ?string
    {
        return __('filament-forms::unit-kerja.navigation.plural');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('filament-forms::unit-kerja.navigation.group');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('filament-forms::unit-kerja.form.unit.title'))
                    ->description(__('filament-forms::unit-kerja.form.unit.description'))
                    ->schema([
                        TextInput::make('unit_name')
                            ->label(__('filament-forms::unit-kerja.fields.unit_name'))
                            ->placeholder(__('filament-forms::unit-kerja.form.unit.name_placeholder'))
                            ->helperText(__('filament-forms::unit-kerja.form.unit.helper_text'))
                            ->required()
                            ->unique('unit_kerja', 'unit_name', ignoreRecord: true)
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Textarea::make('description')
                            ->label(__('filament-forms::unit-kerja.fields.description'))
                            ->placeholder(__('filament-forms::unit-kerja.form.unit.description_placeholder'))
                            ->rows(3)
                            ->columnSpanFull(),

                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('unit_name')
                    ->label(__('filament-forms::unit-kerja.fields.unit_name'))
                    ->description(fn(UnitKerja $record) => Str::limit($record->description, 60))
                    ->searchable(),

                Tables\Columns\TextColumn::make('imut_data_count')
                    ->label(__('filament-forms::imut-category.fields.data_count'))
                    ->counts('imutData')
                    ->badge()
                    ->alignCenter()
                    ->sortable(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->headerActions([
                ExportAction::make()->exporter(UnitKerjaExporter::class)
            ])
            ->actions([
                RelationManagerAction::make('user-relation-manager')
                    ->slideOver()
                    ->label('User Attach')
                    ->icon('heroicon-o-user')
                    ->relationManager(UsersRelationManager::make())
                    ->visible(
                        fn($record) =>
                        Gate::any(['attach_user_to_unit_kerja_unit::kerja'], $record)
                            && method_exists($record, 'trashed') === false
                            ? true
                            : ! $record->trashed()
                    ),

                // RelationManagerAction::make('imutData-relation-manager')
                //     ->slideOver()
                //     ->label('Imut Data Attach')
                //     ->icon('heroicon-o-chart-bar')
                //     ->relationManager(ImutDataRelationManager::make())
                //     ->visible(
                //         fn($record) =>
                //         Gate::any(['attach_imut_data_to_unit_kerja_unit::kerja'], $record)
                //             && method_exists($record, 'trashed') === false
                //             ? true
                //             : ! $record->trashed()
                //     ),

                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                    RestoreAction::make()
                        ->visible(
                            fn($record) =>
                            Gate::allows('restore', $record) &&
                                method_exists($record, 'trashed') &&
                                $record->trashed()
                        ),

                    ForceDeleteAction::make()
                        ->visible(
                            fn($record) =>
                            Gate::allows('forceDelete', $record) &&
                                method_exists($record, 'trashed') &&
                                $record->trashed()
                        ),
                ])->button()->label(__('filament-forms::users.actions.group')),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ])->visible(fn() => Gate::any(['update_imut::category', 'create_imut::category'])),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // UsersRelationManager::class,
            ImutDataRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUnitKerja::route('/'),
            'create' => Pages\CreateUnitKerja::route('/create'),
            'edit' => Pages\EditUnitKerja::route('/{record:slug}/edit'),
        ];
    }
}
