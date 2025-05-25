<?php

namespace App\Filament\Resources;

use App\Models\{User, Position};
use App\Traits\HasActiveIcon;
use App\Filament\Exports\UserExporter;
use App\Filament\Resources\UserResource\Pages;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms\{
    Components\Actions\Action as FieldAction,
    Components\DatePicker,
    Components\Grid,
    Components\Section,
    Components\Select,
    Components\TextInput,
    Components\Textarea,
    Components\ToggleButtons,
    Form,
    Get,
    Set
};
use Filament\Infolists\{
    Components\Group,
    Components\ImageEntry,
    Components\Section as InfolistSection,
    Components\TextEntry,
    Infolist
};
use Filament\Resources\{
    Pages\CreateRecord,
    Resource
};
use Filament\Support\Enums\FontWeight;
use Filament\Tables\{
    Actions\Action,
    Actions\ActionGroup,
    Actions\BulkActionGroup,
    Actions\DeleteAction,
    Actions\DeleteBulkAction,
    Actions\EditAction,
    Actions\ExportBulkAction,
    Actions\ForceDeleteAction,
    Actions\ForceDeleteBulkAction,
    Actions\RestoreAction,
    Actions\RestoreBulkAction,
    Actions\ViewAction,
    Columns\ImageColumn,
    Columns\Layout\Split,
    Columns\Layout\Stack,
    Columns\TextColumn,
    Filters\SelectFilter,
    Table
};
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Rmsramos\Activitylog\{
    Actions\ActivityLogTimelineTableAction,
    RelationManagers\ActivitylogRelationManager
};
use STS\FilamentImpersonate\Tables\Actions\Impersonate;

class UserResource extends Resource implements HasShieldPermissions
{
    use HasActiveIcon;
    protected static ?string $model = User::class;
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $recordTitleAttribute = 'name';

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

            // Custom permissions
            'view_activities',
            'set_role',
            'impersonate',
            'export'
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Email' => $record->email,
            'Role' => $record->roles->first()->name ?? 'No Role',
        ];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->name;
    }

    public static function getGlobalSearchResultUrl(Model $record): ?string
    {
        return route('filament.admin.resources.users.edit', $record);
    }

    public static function getGlobalSearchResultImage(Model $record): ?string
    {
        return $record->profile_photo_url ?? null;
    }

    public static function getLabel(): ?string
    {
        return __('filament-navigation::navigation.resources.users');
    }

    public static function getPluralLabel(): ?string
    {
        return __('');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('filament-navigation::navigation.group.user_access');
    }

    public static function form(Form $form): Form
    {
        return $form->schema(self::FormDefaultInformation());
    }


    public static function canCreate(): bool
    {
        return true;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(self::tableColumns())
            ->filters([
                TrashedFilter::make(),
                SelectFilter::make('roles')
                    ->label(__('filament-forms::users.filters.roles'))
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload(),
                SelectFilter::make('position')
                    ->label(__('filament-forms::users.filters.position'))
                    ->relationship('position', 'name')
                    ->multiple()
                    ->preload(),
            ])
            ->actions(self::tableActions())
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn() => Gate::allows('deleteAny', User::class)),

                    RestoreBulkAction::make()
                        ->visible(fn() => Gate::allows('restoreAny', User::class)),

                    ForceDeleteBulkAction::make()
                        ->visible(fn() => Gate::allows('forceDeleteAny', User::class)),

                    ExportBulkAction::make()
                        ->exporter(UserExporter::class)
                        ->visible(fn() => Gate::allows('export', User::class)),
                ]),
            ]);
    }


    public static function getRelations(): array
    {
        return [
            ActivitylogRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema(self::InfolistViews());
    }

    public static function getModelLabel(): string
    {
        return __('filament-forms::users.model.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament-forms::users.model.plural_label');
    }

    protected static function formDefaultInformation(): array
    {
        return [
            Section::make(__('filament-forms::users.form.user.title'))
                ->description(__('filament-forms::users.form.user.description'))
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('nik')
                            ->label(__('filament-forms::users.fields.nik'))
                            ->placeholder(__('filament-forms::users.form.user.nik_placeholder'))
                            ->required()
                            ->unique('users', 'nik', ignoreRecord: true)
                            ->maxLength(20),

                        TextInput::make('name')
                            ->label(__('filament-forms::users.fields.name'))
                            ->placeholder(__('filament-forms::users.form.user.name_placeholder'))
                            ->required(),
                    ]),
                    Grid::make(2)->schema([
                        TextInput::make('place_of_birth')
                            ->label(__('filament-forms::users.fields.place_of_birth'))
                            ->placeholder(__('filament-forms::users.form.personal_info.place_of_birth_placeholder'))
                            ->nullable(),

                        DatePicker::make('date_of_birth')
                            ->label(__('filament-forms::users.fields.date_of_birth'))
                            ->placeholder(__('filament-forms::users.form.personal_info.date_of_birth_placeholder'))
                            ->nullable(),
                    ]),
                    ToggleButtons::make('gender')
                        ->label(__('filament-forms::users.fields.gender'))
                        ->options([
                            'Laki-laki' => __('filament-forms::users.form.personal_info.gender_male'),
                            'Perempuan' => __('filament-forms::users.form.personal_info.gender_female'),
                        ])
                        ->required()
                        ->inline()
                        ->colors([
                            'Laki-laki' => 'primary',
                            'Perempuan' => 'success',
                        ])
                ]),

            Section::make(__('filament-forms::users.form.contact_info.title'))
                ->description(__('filament-forms::users.form.contact_info.description'))
                ->schema([
                    Textarea::make('address_ktp')
                        ->label(__('filament-forms::users.fields.address_ktp'))
                        ->placeholder(__('filament-forms::users.form.contact_info.address_placeholder'))
                        ->required(),

                    Grid::make(2)->schema([
                        TextInput::make('phone_number')
                            ->label(__('filament-forms::users.fields.phone_number'))
                            ->placeholder(__('filament-forms::users.form.contact_info.phone_number_placeholder'))
                            ->tel()
                            ->nullable(),

                        TextInput::make('email')
                            ->label(__('filament-forms::users.fields.email'))
                            ->placeholder(__('filament-forms::users.form.user.email_placeholder'))
                            ->email()
                            ->nullable()
                            ->unique('users', 'email', ignoreRecord: true),
                    ]),
                ]),


            Section::make(__('filament-forms::users.form.account.title'))
                ->description(__('filament-forms::users.form.account.description'))
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('password')
                            ->label(__('filament-forms::users.fields.password'))
                            ->placeholder(__('filament-forms::users.form.user.password_placeholder'))
                            ->password()
                            ->dehydrateStateUsing(fn($state) => $state ? bcrypt($state) : null)
                            ->required(fn($livewire) => $livewire instanceof CreateRecord),

                        ToggleButtons::make('status')
                            ->label(__('filament-forms::users.fields.status'))
                            ->options([
                                'active' => __('filament-forms::users.status.active'),
                                'inactive' => __('filament-forms::users.status.inactive'),
                                'suspended' => __('filament-forms::users.status.suspended'),
                            ])
                            ->required()
                            ->default('active')
                            ->inline()
                            ->colors([
                                'active' => 'success',
                                'inactive' => 'warning',
                                'suspended' => 'danger',
                            ])
                    ]),
                ]),


            Section::make(__('filament-forms::users.form.position.title'))
                ->description(__('filament-forms::users.form.position.description'))
                ->schema([
                    Select::make('position_id')
                        ->label(__('filament-forms::users.fields.position'))
                        ->relationship('position', 'name')
                        ->preload()
                        ->searchable()
                        ->placeholder(__('filament-forms::users.form.position.select_placeholder'))
                        ->createOptionForm(fn(Form $form) => $form->schema([
                            TextInput::make('name')
                                ->required()
                                ->label(__('filament-forms::users.form.position.create_label')),
                            TextInput::make('description')
                                ->label(__('filament-forms::users.form.position.create_description'))
                                ->nullable(),
                        ]))
                        ->suffixActions([
                            FieldAction::make('editPosition')
                                ->icon('heroicon-o-pencil-square')
                                ->visible(fn(Get $get) => filled($get('position_id')))
                                ->modalHeading(__('filament-forms::users.form.position.edit_modal_title'))
                                ->mountUsing(function (FieldAction $action, Get $get) {
                                    $position = Position::find($get('position_id'));
                                    if ($position) {
                                        $action->form([
                                            TextInput::make('name')
                                                ->required()
                                                ->default($position->name),
                                            TextInput::make('description')
                                                ->default($position->description),
                                        ]);
                                    }
                                })
                                ->action(function (array $data, Get $get) {
                                    $position = Position::find($get('position_id'));
                                    if ($position) {
                                        $position->update([
                                            'name' => $data['name'],
                                            'description' => $data['description'],
                                        ]);
                                    }
                                }),

                            FieldAction::make('deletePosition')
                                ->icon('heroicon-o-trash')
                                ->color('danger')
                                ->requiresConfirmation()
                                ->visible(fn(Get $get) => filled($get('position_id')))
                                ->action(function (Get $get, Set $set) {
                                    $position = Position::find($get('position_id'));
                                    if ($position) {
                                        $position->delete();
                                        $set('position_id', null);
                                    }
                                }),
                        ]),
                ])
        ];
    }

    protected static function tableColumns(): array
    {
        return [
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
                        ->copyable()
                        ->copyMessage('NIK berhasil disalin!')
                        ->copyMessageDuration(1500)
                        ->grow(false),
                ])->alignStart()->visibleFrom('lg')->space(1)
            ]),
        ];
    }

    protected static function tableActions(): array
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
        ];
    }

    protected static function InfolistViews(): array
    {
        return [
            // Section untuk informasi pribadi pengguna
            InfolistSection::make(__('filament-forms::users.infolist.personal_info_title'))
                ->columns(3)
                ->schema([
                    // Kolom pertama: Avatar Pengguna
                    ImageEntry::make('avatar_url')
                        ->label('')
                        ->circular()
                        ->size(120)
                        ->grow(false)
                        ->getStateUsing(fn($record) => $record->avatar_url ?: "https://ui-avatars.com/api/?name=" . urlencode($record->name))
                        ->columnSpan(1),

                    // Kolom kedua: Nama, NIK, Tempat Lahir, Tanggal Lahir
                    Group::make([
                        TextEntry::make('name')
                            ->label(__('filament-forms::users.fields.name'))
                            ->weight(FontWeight::Bold),
                        TextEntry::make('nik')
                            ->label(__('filament-forms::users.fields.nik'))
                            ->icon('heroicon-o-finger-print')
                            ->copyable(),
                        TextEntry::make('place_of_birth')
                            ->label(__('filament-forms::users.fields.place_of_birth'))
                            ->icon('heroicon-m-map-pin'),
                        TextEntry::make('date_of_birth')
                            ->label(__('filament-forms::users.fields.date_of_birth'))
                            ->dateTime('d M Y')
                            ->icon('heroicon-o-calendar'),
                    ])->columnSpan(1),

                    // Kolom ketiga: Gender, Status Pengguna, dan Posisi
                    Group::make([
                        TextEntry::make('gender')
                            ->label(__('filament-forms::users.fields.gender'))
                            ->icon('heroicon-s-user'),
                        TextEntry::make('status')
                            ->label(__('filament-forms::users.fields.status'))
                            ->icon('heroicon-o-check-circle')
                            ->badge()
                            ->color(fn($state) => $state === 'active' ? 'success' : ($state === 'inactive' ? 'danger' : 'gray')),
                        TextEntry::make('position.name')
                            ->label(__('filament-forms::users.fields.position'))
                            ->icon('heroicon-o-briefcase')
                            ->badge(),
                    ])->columnSpan(1),
                ]),

            // Section untuk informasi kontak pengguna
            InfolistSection::make(__('filament-forms::users.infolist.contact_info_title'))
                ->columns(2)
                ->schema([
                    TextEntry::make('phone_number')
                        ->label(__('filament-forms::users.fields.phone_number'))
                        ->icon('heroicon-o-phone'),
                    TextEntry::make('address_ktp')
                        ->label(__('filament-forms::users.fields.address_ktp'))
                        ->icon('heroicon-o-map-pin'),
                ])
                ->visible(fn($record) => filled($record->phone_number) || filled($record->address_ktp)),

            // Section untuk informasi akun dan status pengguna
            InfolistSection::make(__('filament-forms::users.infolist.account_info_title'))
                ->columns(2)
                ->schema([
                    TextEntry::make('email')
                        ->label(__('filament-forms::users.fields.email'))
                        ->icon('heroicon-m-envelope')
                        ->copyable()
                        ->tooltip(__('filament-forms::users.infolist.copy_email')),
                    TextEntry::make('created_at')
                        ->label(__('filament-forms::users.fields.created_at'))
                        ->dateTime('d M Y')
                        ->icon('heroicon-m-calendar'),
                ]),
        ];
    }
}
