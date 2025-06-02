<?php

namespace App\Filament\Resources;

use Closure;
use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\UnitKerja;
use Filament\Tables\Table;
use App\Models\LaporanImut;
use App\Models\ImutPenilaian;
use App\Models\LaporanUnitKerja;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\DB;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\Cache;
use App\Tables\Columns\ProgressColumn;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\ActionGroup;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\ToggleButtons;
use App\Filament\Resources\LaporanImutResource\Pages;
use Symfony\Component\HttpKernel\Exception\HttpException;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use App\Filament\Resources\LaporanImutResource\Pages\ImutDataReport;
use App\Filament\Resources\LaporanImutResource\Pages\UnitKerjaReport;
use App\Filament\Resources\LaporanImutResource\Pages\ImutDataUnitKerjaReport;
use App\Filament\Resources\LaporanImutResource\Pages\UnitKerjaImutDataReport;
use Filament\Tables\Actions\{EditAction, ViewAction, DeleteAction, RestoreAction, ForceDeleteAction};
use Filament\Tables\Actions\{BulkActionGroup, DeleteBulkAction, RestoreBulkAction, ForceDeleteBulkAction};

class LaporanImutResource extends Resource implements HasShieldPermissions
{
    use \App\Traits\HasActiveIcon;
    protected static ?string $model = LaporanImut::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationLabel = 'Laporan IMUT';
    protected static ?string $modelLabel = 'Laporan IMUT';

    public static function getGloballySearchableAttributes(): array
    {
        return ['assessment_period'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Periode Asesmen' => $record->assessment_period,
        ];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return "Laporan {$record->assessment_period}";
    }

    public static function getGlobalSearchResultUrl(Model $record): ?string
    {
        return static::getUrl(name: 'edit', parameters: ['record' => $record]);
    }

    public static function getPermissionPrefixes(): array
    {
        return array_merge([
            // default Filament Shield permissions
            'view',
            'view_any',
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

            // custom laporan report
            'view_unit_kerja_report',
            'view_unit_kerja_report_detail',
            'view_imut_data_report',
            'view_imut_data_report_detail',

            // custom penilaian actions
            'view_imut_penilaian',
            'update_numerator_denominator',
            'update_profile_penilaian',
            'create_recommendation_penilaian',
        ]);
    }

    public static function getLabel(): ?string
    {
        return __('Laporan IMUT');
    }

    public static function getPluralLabel(): ?string
    {
        return __('Daftar Laporan IMUT');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('filament-forms::imut-data.navigation.group');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Informasi Laporan')
                ->description('Lengkapi data laporan di bawah ini.')
                ->schema([
                    TextInput::make('name')
                        ->label('Nama Laporan')
                        ->required()
                        ->maxLength(255)
                        ->unique('laporan_imuts', 'name', ignoreRecord: true)
                        ->columnSpanFull()
                        ->default(function () {
                            $count = LaporanImut::count();
                            return 'Laporan IMUT Periode ' . ($count + 1);
                        }),

                    DatePicker::make('assessment_period_start')
                        ->label('Dimulainya Periode Asesmen')
                        ->placeholder('YYYY-MM-DD')
                        ->required()
                        ->reactive()
                        ->default(now()->format('Y-m-d')),

                    DatePicker::make('assessment_period_end')
                        ->label('Berakhirnya Periode Asesmen')
                        ->placeholder('YYYY-MM-DD')
                        ->required()
                        ->minDate(fn(callable $get) => $get('assessment_period_start'))
                        ->rule('after_or_equal:assessment_period_start'),


                    Select::make('created_by')
                        ->label('Dibuat oleh')
                        ->options(User::pluck('name', 'id'))
                        ->default(fn() => Auth::id())
                        ->disabled()
                        ->columnSpanFull(),

                    Section::make('Unit Kerja')
                        ->description('Pilih unit kerja yang akan mengisi indikator mutu.')
                        ->columnSpanFull()
                        ->schema([
                            CheckboxList::make('unitKerjas')
                                ->relationship('unitKerjas', 'unit_name')
                                ->label('Unit Kerja yang Bisa Menilai')
                                ->columns(3)
                                ->required()
                                ->disabledOn('edit')
                                ->default(UnitKerja::pluck('id')->toArray()),
                        ]),
                ])
                ->columns(2),
        ]);
    }

    // ===================== Table Start Component =======================
    public static function table(Table $table): Table
    {
        return $table
            ->columns(self::getColumns())
            ->filters(self::getFilters())
            ->actions(self::getActions())
            ->bulkActions(
                [
                    Tables\Actions\BulkActionGroup::make([
                        Tables\Actions\RestoreBulkAction::make()
                            ->visible(fn(LaporanImut $record) => method_exists($record, 'trashed') && $record->trashed()),
                        Tables\Actions\ForceDeleteBulkAction::make()
                            ->visible(fn(LaporanImut $record) => method_exists($record, 'trashed') && $record->trashed()),
                    ]),
                    Tables\Actions\DeleteBulkAction::make(),
                ]
            );
    }

    // Columns Logic Start
    protected static function getColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('name')
                ->label('Nama Laporan')
                ->sortable()
                ->searchable(),

            Tables\Columns\TextColumn::make('createdBy.name')
                ->label('Pembuat Laporan')
                ->alignCenter()
                ->sortable()
                ->searchable(),

            Tables\Columns\TextColumn::make('assessment_period')
                ->label('Periode Asesmen')
                ->alignCenter()
                ->getStateUsing(fn($record) => self::formatAssessmentPeriod($record)),

            Tables\Columns\TextColumn::make('status')
                ->label('Status')
                ->badge()
                ->alignCenter()
                ->color(fn(string $state): string => match ($state) {
                    'canceled' => 'danger',
                    'process' => 'primary',
                    'complete' => 'success',
                }),

            self::getProgressColumn(),
            self::getUnitKerjaTerisiColumn(),
        ];
    }

    protected static function formatAssessmentPeriod($record): string
    {
        $start = \Carbon\Carbon::parse($record->assessment_period_start)->translatedFormat('d M');
        $end = \Carbon\Carbon::parse($record->assessment_period_end)->translatedFormat('d M Y');

        return "$start - $end";
    }

    protected static function getProgressColumn(): ProgressColumn
    {
        return ProgressColumn::make('progress')
            ->label('Progress')
            ->visible(fn() => Auth::user()?->unitKerjas()->exists())
            ->getStateUsing(fn($record) => self::calculateProgress($record))
            ->tooltip(fn($record) => self::progressTooltip($record));
    }

    protected static function calculateProgress($record): ?float
    {
        $userUnitIds = Auth::user()?->unitKerjas?->pluck('id')->toArray() ?? [];

        $laporanUnitKerjaIds = DB::table('laporan_unit_kerjas')
            ->where('laporan_imut_id', $record->id)
            ->whereIn('unit_kerja_id', $userUnitIds)
            ->pluck('id')
            ->toArray();

        if (empty($laporanUnitKerjaIds) || !self::userHasAccessToLaporan($record)) {
            return null;
        }

        $total = ImutPenilaian::whereIn('laporan_unit_kerja_id', $laporanUnitKerjaIds)->count();
        $filled = ImutPenilaian::whereIn('laporan_unit_kerja_id', $laporanUnitKerjaIds)
            ->whereNotNull('numerator_value')
            ->whereNotNull('denominator_value')
            ->count();

        return $total > 0 ? round(($filled / $total) * 100, 2) : 0;
    }

    protected static function progressTooltip($record): ?string
    {
        $userUnitIds = Auth::user()?->unitKerjas?->pluck('id')->toArray() ?? [];

        $laporanUnitKerjaIds = DB::table('laporan_unit_kerjas')
            ->where('laporan_imut_id', $record->id)
            ->whereIn('unit_kerja_id', $userUnitIds)
            ->pluck('id')
            ->toArray();

        if (empty($laporanUnitKerjaIds)) {
            return null;
        }

        $total = ImutPenilaian::whereIn('laporan_unit_kerja_id', $laporanUnitKerjaIds)->count();
        $filled = ImutPenilaian::whereIn('laporan_unit_kerja_id', $laporanUnitKerjaIds)
            ->whereNotNull('numerator_value')
            ->whereNotNull('denominator_value')
            ->count();

        return "$filled / $total Penilaian selesai";
    }

    protected static function getUnitKerjaTerisiColumn(): ProgressColumn
    {
        return ProgressColumn::make('unit_kerja_terisi')
            ->label('Unit Kerja Terisi')
            ->visible(
                fn() =>
                Gate::check('view_unit_kerja_report_laporan::imut') &&
                    Gate::check('view_imut_data_report_laporan::imut') &&
                    Gate::check('update_profile_penilaian_laporan::imut') &&
                    Gate::check('create_recommendation_penilaian_laporan::imut')
            )
            ->getStateUsing(fn($record) => self::calculateUnitKerjaTerisi($record))
            ->tooltip(fn($record) => self::tooltipUnitKerjaTerisi($record));
    }

    protected static function calculateUnitKerjaTerisi($record): float
    {
        $laporanUnitKerjas = DB::table('laporan_unit_kerjas')
            ->where('laporan_imut_id', $record->id)
            ->get(['id']);

        $totalUnitKerja = $laporanUnitKerjas->count();
        $filledCount = 0;

        foreach ($laporanUnitKerjas as $unit) {
            $total = ImutPenilaian::where('laporan_unit_kerja_id', $unit->id)->count();
            $filled = ImutPenilaian::where('laporan_unit_kerja_id', $unit->id)
                ->whereNotNull('numerator_value')
                ->whereNotNull('denominator_value')
                ->count();

            if ($total > 0 && $total === $filled) {
                $filledCount++;
            }
        }

        return $totalUnitKerja > 0 ? round(($filledCount / $totalUnitKerja) * 100, 2) : 0;
    }

    protected static function tooltipUnitKerjaTerisi($record): string
    {
        $laporanUnitKerjas = DB::table('laporan_unit_kerjas')
            ->where('laporan_imut_id', $record->id)
            ->get(['id']);

        $totalUnitKerja = $laporanUnitKerjas->count();
        $filledCount = 0;

        foreach ($laporanUnitKerjas as $unit) {
            $total = ImutPenilaian::where('laporan_unit_kerja_id', $unit->id)->count();
            $filled = ImutPenilaian::where('laporan_unit_kerja_id', $unit->id)
                ->whereNotNull('numerator_value')
                ->whereNotNull('denominator_value')
                ->count();

            if ($total > 0 && $total === $filled) {
                $filledCount++;
            }
        }

        return "{$filledCount} dari {$totalUnitKerja} unit kerja sudah mengisi";
    }

    // Columns Logic End

    protected static function getFilters(): array
    {
        return [
            Tables\Filters\TrashedFilter::make(),
        ];
    }

    protected static function getActions(): array
    {
        return [
            Action::make('isi_penilaian')
                ->label('Isi Penilaian')
                ->icon('heroicon-s-clipboard-document-list')
                ->color('warning')
                ->visible(
                    fn($record) =>
                    method_exists($record, 'trashed')
                        && !$record->trashed()
                        && self::userHasAccessToLaporan($record)
                        && \Carbon\Carbon::today()->lte($record->assessment_period_end)
                )
                ->url(fn($record) => self::getIsiPenilaianUrl($record)),

            Tables\Actions\ActionGroup::make([
                Tables\Actions\EditAction::make()
                    ->visible(fn($record) => method_exists($record, 'trashed') && !$record->trashed()),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn($record) => method_exists($record, 'trashed') && !$record->trashed()),
                Action::make('summary')
                    ->label('Summary')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->color('success')
                    ->visible(
                        fn($record) =>
                        method_exists($record, 'trashed') &&
                            !$record->trashed() &&
                            Gate::any([
                                'view_unit_kerja_report_laporan::imut',
                                'view_imut_data_report_laporan::imut',
                            ])
                    )
                    ->form([
                        Select::make('summary_type')
                            ->label('Pilih Tipe Summary')
                            ->options([
                                'unit_kerja' => 'Summary Unit Kerja – menampilkan rekapitulasi per unit kerja',
                                'imut_data' => 'Summary IMUT DATA – menampilkan detail tiap IMUT',
                            ])
                            ->required(),
                    ])
                    ->modalHeading('Pilih Summary')
                    ->modalSubmitActionLabel('Lihat')
                    ->action(function ($record, array $data) {
                        $type = $data['summary_type'];

                        $map = [
                            'unit_kerja' => [
                                'permission' => 'view_unit_kerja_report_laporan::imut',
                                'redirect' => UnitKerjaReport::getUrl(['laporan_id' => $record->id]),
                            ],
                            'imut_data' => [
                                'permission' => 'view_imut_data_report_laporan::imut',
                                'redirect' => ImutDataReport::getUrl(['laporan_id' => $record->id]),
                            ],
                        ];

                        abort_unless(
                            isset($map[$type]) && Gate::allows($map[$type]['permission']),
                            403,
                            'Anda tidak memiliki izin untuk mengakses summary ini.'
                        );

                        return redirect()->to($map[$type]['redirect']);
                    })
            ]),

            // Aksi hanya jika record dalam trash
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
        ];
    }

    protected static function getBulkActions(): array
    {
        return [
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\RestoreBulkAction::make()
                    ->visible(fn(LaporanImut $record) => method_exists($record, 'trashed') && $record->trashed()),
                Tables\Actions\ForceDeleteBulkAction::make()
                    ->visible(fn(LaporanImut $record) => method_exists($record, 'trashed') && $record->trashed()),
            ]),
            Tables\Actions\DeleteBulkAction::make(),
        ];
    }

    // ===================== Table End Component =======================

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLaporanImuts::route('/'),
            'create' => Pages\CreateLaporanImut::route('/create'),
            'edit' => Pages\EditLaporanImut::route('/{record:slug}/edit'),
            'unit-kerja-report' => UnitKerjaReport::route('/unit-kerja-report'),
            'unit-kerja-imut-data-report-detail' => UnitKerjaImutDataReport::route('/unit-kerja-imut-data-report'),
            'imut-data-report' => ImutDataReport::route('/imut-data-report'),
            'imut-data-unit-kerja-report-detail' => ImutDataUnitKerjaReport::route('/imut-data-unit-kerja-report'),
            'edit-penilaian' => Pages\PenilaianLaporan::route('/penilaian'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->orderByDesc('assessment_period_start');
    }

    protected static function userHasAccessToLaporan($record): bool
    {
        $user = Auth::user();

        $userUnitKerjaIds = $user->unitKerjas->pluck('id')->toArray();
        $laporanUnitKerjaIds = $record->unitKerjas->pluck('id')->toArray();

        return !empty(array_intersect($userUnitKerjaIds, $laporanUnitKerjaIds));
    }

    protected static function getIsiPenilaianUrl($record): ?string
    {
        $user = Auth::user();

        $matchingUnitKerja = $user->unitKerjas()
            ->whereIn('unit_kerja.id', $record->unitKerjas->pluck('id'))
            ->first();

        return $matchingUnitKerja
            ? UnitKerjaImutDataReport::getUrl([
                'laporan_id' => $record->id,
                'unit_kerja_id' => $matchingUnitKerja->id,
            ])
            : null;
    }
}
