<?php

namespace App\Filament\Resources;

use App\Models\Role;
use App\Models\User;
use Filament\Forms\Components\ToggleButtons;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Models\Position;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Forms\Components\Grid;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Select;
use App\Filament\Exports\UserExporter;
use Filament\Forms\Components\Section;
use Filament\Support\Enums\FontWeight;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\Group;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Filters\SelectFilter;
use Filament\Resources\Pages\CreateRecord;
use Filament\Infolists\Components\TextEntry;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Infolists\Components\ImageEntry;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\ExportBulkAction;
use App\Filament\Resources\UserResource\Pages;
use STS\FilamentImpersonate\Tables\Actions\Impersonate;
use Filament\Forms\Components\Actions\Action as FieldAction;
use Filament\Infolists\Components\Section as InfolistSection;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Rmsramos\Activitylog\Actions\ActivityLogTimelineTableAction;
use Rmsramos\Activitylog\RelationManagers\ActivitylogRelationManager;

class UserResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $recordTitleAttribute = 'name';

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
        return $form->schema([

            // === Section: Data Pribadi ===
            Section::make('Data Pribadi')
                ->description('Lengkapi informasi pribadi karyawan.')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('nik')
                            ->label('NIK')
                            ->required()
                            ->unique('users', 'nik', ignoreRecord: true)
                            ->maxLength(20),

                        TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->required(),
                    ]),
                    Grid::make(2)->schema([
                        TextInput::make('place_of_birth')
                            ->label('Tempat Lahir')
                            ->nullable(),

                        DatePicker::make('date_of_birth')
                            ->label('Tanggal Lahir')
                            ->nullable(),
                    ]),
                    ToggleButtons::make('gender')
                        ->label('Jenis Kelamin')
                        ->options([
                            'Laki-laki' => 'Laki-laki',
                            'Perempuan' => 'Perempuan',
                        ])
                        ->required()
                        ->inline()
                        ->colors([
                            'Laki-laki' => 'primary',
                            'Perempuan' => 'success',
                        ])
                ]),

            // === Section: Kontak & Alamat ===
            Section::make('Kontak & Alamat')
                ->description('Informasi kontak dan domisili.')
                ->schema([
                    Textarea::make('address_ktp')
                        ->label('Alamat KTP')
                        ->required(),

                    Grid::make(2)->schema([
                        TextInput::make('phone_number')
                            ->label('Nomor Telepon')
                            ->tel()
                            ->nullable(),

                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->nullable()
                            ->unique('users', 'email', ignoreRecord: true),
                    ]),
                ]),

            // === Section: Keamanan & Status ===
            Section::make('Keamanan & Status Akun')
                ->description('Kelola status dan kata sandi akun.')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('password')
                            ->label('Kata Sandi')
                            ->password()
                            ->placeholder('Masukkan kata sandi')
                            ->dehydrateStateUsing(fn($state) => $state ? bcrypt($state) : null)
                            ->required(fn($livewire) => $livewire instanceof CreateRecord),

                        ToggleButtons::make('status')
                            ->label('Status')
                            ->options([
                                'active' => 'Aktif',
                                'inactive' => 'Tidak Aktif',
                                'suspended' => 'Ditangguhkan',
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

            // === Section: Jabatan ===
            Section::make('Posisi & Jabatan')
                ->description('Atur jabatan pengguna.')
                ->schema([
                    Select::make('position_id')
                        ->label('Pilih Posisi')
                        ->relationship('position', 'name')
                        ->preload()
                        ->searchable()
                        ->placeholder('Pilih posisi')
                        ->createOptionForm(fn(Form $form) => $form->schema([
                            TextInput::make('name')
                                ->required()
                                ->label('Nama Posisi'),
                            TextInput::make('description')
                                ->label('Deskripsi Posisi')
                                ->nullable(),
                        ]))
                        ->suffixActions([
                            FieldAction::make('editPosition')
                                ->icon('heroicon-o-pencil-square')
                                ->visible(fn(Get $get) => filled($get('position_id')))
                                ->modalHeading('Edit Posisi')
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
                ]),
        ]);
    }

    public static function canCreate(): bool
    {
        return true;
    }

    public static function table(Table $table): Table
    {
        return $table
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
                ]),
            ])
            ->filters([
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
            ->actions([
                ActivityLogTimelineTableAction::make(__('filament-forms::users.actions.activities')),
                Action::make(__('filament-forms::users.actions.set_role'))
                    ->icon('heroicon-m-adjustments-vertical')
                    ->form(form: [
                        Select::make('role')
                            ->label(__('filament-forms::users.fields.roles'))
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->optionsLimit(10)
                            ->getOptionLabelFromRecordUsing(fn($record) => $record->name),
                    ]),
                Impersonate::make()->label(__('filament-forms::users.actions.impersonate')),
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ])->button()->label(__('filament-forms::users.actions.group')),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
                ExportBulkAction::make()
                    ->exporter(UserExporter::class)
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
        return $infolist->schema([
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
        ]);
    }


    public static function getModelLabel(): string
    {
        return __('filament-forms::users.model.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament-forms::users.model.plural_label');
    }

}
