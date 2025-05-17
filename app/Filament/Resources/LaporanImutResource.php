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
            'view_unit_kerja_report',
            'view_unit_kerja_report_detail',
            'view_imut_data_report',
            'view_imut_data_report_detail',
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
                            $LaporanCount = LaporanImut::count();
                            return 'Laporan IMUT Periode' . ($LaporanCount + 1);
                        }),

                    DatePicker::make('assessment_period_start')
                        ->label('Dimulainya Periode Asesmen')
                        ->placeholder('YYYY-MM-DD')
                        ->required()
                        ->default(now()->format('Y-m-d')),

                    DatePicker::make('assessment_period_end')
                        ->label('Berakhirnya Periode Asesmen')
                        ->placeholder('YYYY-MM-DD')
                        ->required(),

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
                                ->default(UnitKerja::pluck('id')->toArray())
                        ])
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Laporan')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('assessment_period')
                    ->label('Periode Asesmen')
                    ->alignCenter()
                    ->getStateUsing(function ($record) {
                        $start = \Carbon\Carbon::parse($record->assessment_period_start)->translatedFormat('d M');
                        $end = \Carbon\Carbon::parse($record->assessment_period_end)->translatedFormat('d M Y');

                        if (\Carbon\Carbon::parse($record->assessment_period_start)->format('m') === \Carbon\Carbon::parse($record->assessment_period_end)->format('m')) {
                            return "$start - $end";
                        }
                        return "$start - $end";
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->alignCenter()
                    ->color(fn(string $state): string => match ($state) {
                        'canceled' => 'danger',
                        'process' => 'primary',
                        'complete' => 'success',
                    }),

                ProgressColumn::make('progress')
                    ->label('Progress')
                    ->getStateUsing(function ($record) {
                        $cacheKey = "imut_progress_{$record->id}";

                        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($record) {
                            $unitKerjaIds = $record->unitKerjas()->pluck('unit_kerja_id')->toArray();

                            $laporanUnitKerjaIds = DB::table('laporan_unit_kerjas')
                                ->where('laporan_imut_id', $record->id)
                                ->pluck('id')
                                ->toArray();

                            $total = ImutPenilaian::whereIn('laporan_unit_kerja_id', $laporanUnitKerjaIds)->count();

                            $filled = ImutPenilaian::whereIn('laporan_unit_kerja_id', $laporanUnitKerjaIds)
                                ->whereNotNull('numerator_value')
                                ->whereNotNull('denominator_value')
                                ->count();

                            return $total > 0 ? round(($filled / $total) * 100, 2) : 0;
                        });
                    })
                    ->tooltip(function ($record) {
                        $cacheKey = "imut_progress_{$record->id}";

                        $data = Cache::get($cacheKey);

                        if (!is_array($data)) {
                            $laporanUnitKerjaIds = DB::table('laporan_unit_kerjas')
                                ->where('laporan_imut_id', $record->id)
                                ->pluck('id')
                                ->toArray();

                            $total = ImutPenilaian::whereIn('laporan_unit_kerja_id', $laporanUnitKerjaIds)->count();
                            $filled = ImutPenilaian::whereIn('laporan_unit_kerja_id', $laporanUnitKerjaIds)
                                ->whereNotNull('numerator_value')
                                ->whereNotNull('denominator_value')
                                ->count();

                            return "$filled / $total Penilaian selesai";
                        }

                        return "{$data['progress']}%";
                    }),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Action::make('summary')
                        ->label('Summary')
                        ->icon('heroicon-o-clipboard-document-list')
                        ->color('success')
                        ->visible(fn() => Gate::any([
                            'view_unit_kerja_report_laporan::imut',
                            'view_imut_data_report_laporan::imut',
                        ]))
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
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\RestoreBulkAction::make()->visible(fn(Model $record) => method_exists($record, 'trashed') && $record->trashed()),
                    Tables\Actions\ForceDeleteBulkAction::make()->visible(fn(Model $record) => method_exists($record, 'trashed') && $record->trashed()),
                ]),
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

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
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->orderByDesc('assessment_period_start');
    }
}
