<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ImutCategoryResource\Pages;
use App\Models\ImutCategory;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Actions\{
    EditAction,
    ViewAction,
    DeleteAction,
    RestoreAction,
    ForceDeleteAction,
    BulkActionGroup,
    DeleteBulkAction,
    RestoreBulkAction,
    ForceDeleteBulkAction
};

class ImutCategoryResource extends Resource implements HasShieldPermissions
{
    use \App\Traits\HasActiveIcon;
    protected static ?string $model = ImutCategory::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-group';
    protected static ?int $navigationSort = 2;

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['category_name'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->category_name ?? '';
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'ID' => $record->id,
        ];
    }

    public static function getGlobalSearchResultUrl(Model $record): ?string
    {
        return static::getUrl(name: 'edit', parameters: ['record' => $record]);
    }

    public static function getLabel(): ?string
    {
        return __('filament-forms::imut-category.navigation.title');
    }

    public static function getPluralLabel(): ?string
    {
        return __('filament-forms::imut-category.navigation.plural');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('filament-forms::imut-category.navigation.group');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make()->schema([
                Grid::make(2)->schema([
                    TextInput::make('category_name')
                        ->label(__('filament-forms::imut-category.fields.category_name'))
                        ->placeholder(__('filament-forms::imut-category.form.name_placeholder'))
                        ->helperText(__('filament-forms::imut-category.form.helper_text'))
                        ->required()
                        ->columnSpan(1)
                        ->maxLength(100),

                    TextInput::make('short_name')
                        ->label(__('filament-forms::imut-category.fields.short_name'))
                        ->placeholder(__('filament-forms::imut-category.form.short_placeholder'))
                        ->helperText(__('filament-forms::imut-category.form.short_helper_text'))
                        ->required()
                        ->columnSpan(1)
                        ->maxLength(50),

                    \Filament\Forms\Components\ToggleButtons::make('scope')
                        ->label(__('filament-forms::imut-category.fields.scope'))
                        ->options([
                            'internal' => __('filament-forms::imut-category.fields.scope_internal'),
                            'national' => __('filament-forms::imut-category.fields.scope_national'),
                            'unit' => __('filament-forms::imut-category.fields.scope_unit'),
                            'global' => __('filament-forms::imut-category.fields.scope_global'),
                        ])
                        ->default('internal')
                        ->required()
                        ->inline()
                        ->columnSpan(2)
                        ->colors([
                            'internal' => 'success',
                            'national' => 'warning',
                            'unit' => 'gray',
                            'global' => 'primary',
                        ])
                        ->helperText(__('filament-forms::imut-category.fields.scope_helper_text')),

                    Toggle::make('is_use_global')
                        ->label(__('filament-forms::imut-category.form.is_use_global'))
                        ->helperText(__('filament-forms::imut-category.form.is_use_global_helper'))
                        ->inline(true)
                        ->columnSpan(2)
                        ->onColor('success')
                        ->required()
                        ->default(true)
                        ->columnSpan(1),

                    Toggle::make('is_benchmark_category')
                        ->label(__('filament-forms::imut-category.form.is_benchmark_category'))
                        ->helperText(__('filament-forms::imut-category.form.is_benchmark_category_helper'))
                        ->inline(true)
                        ->columnSpan(2)
                        ->onColor('success')
                        ->required()
                        ->default(true)
                        ->columnSpan(1),

                    Textarea::make('description')
                        ->label(__('filament-forms::imut-category.fields.description'))
                        ->placeholder(__('filament-forms::imut-category.fields.description_placeholder'))
                        ->helperText(__('filament-forms::imut-category.fields.description_helpertext'))
                        ->columnSpanFull(),
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('category_name')
                    ->label(__('filament-forms::imut-category.fields.category_name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('scope')
                    ->badge()
                    ->alignCenter()
                    ->color(fn(string $state): string => match ($state) {
                        'global' => 'primary',
                        'internal' => 'success',
                        'unit' => 'warning',
                    }),

                TextColumn::make('imut_data_count')
                    ->label(__('filament-forms::imut-category.fields.data_count'))
                    ->counts('imutData')
                    ->badge()
                    ->alignCenter()
                    ->sortable(),

                \Archilex\ToggleIconColumn\Columns\ToggleIconColumn::make('is_use_global')
                    ->label(__('filament-forms::imut-category.fields.is_use_global'))
                    ->translateLabel()
                    ->alignCenter()
                    ->size('xl')
                    ->disabled()
                    ->tooltip(fn(Model $record) => $record->status ? 'Global' : 'Not Global')
                    ->sortable(),


                \Archilex\ToggleIconColumn\Columns\ToggleIconColumn::make('is_benchmark_category')
                    ->label(__('filament-forms::imut-category.fields.is_benchmark_category'))
                    ->translateLabel()
                    ->disabled()
                    ->alignCenter()
                    ->size('xl')
                    ->tooltip(fn(Model $record) => $record->status ? 'Active' : 'Unactive')
                    ->sortable(),
            ])
            ->filters([])
            ->actions([
                EditAction::make()
                    ->visible(fn($record) => method_exists($record, 'trashed') && !$record->trashed()),

                DeleteAction::make()
                    ->visible(fn($record) => method_exists($record, 'trashed') && !$record->trashed()),

                \Filament\Tables\Actions\ActionGroup::make([
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
                    RestoreBulkAction::make()
                        ->visible(fn(ImutCategory $record) => method_exists($record, 'trashed') && $record->trashed()),
                    ForceDeleteBulkAction::make()
                        ->visible(fn(ImutCategory $record) => method_exists($record, 'trashed') && $record->trashed()),
                ]),
                DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListImutCategories::route('/'),
            'create' => Pages\CreateImutCategory::route('/create'),
            'edit' => Pages\EditImutCategory::route('/{record}/edit'),
        ];
    }
}
