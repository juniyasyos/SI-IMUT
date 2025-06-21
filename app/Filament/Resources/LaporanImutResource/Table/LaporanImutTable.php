<?php

namespace App\Filament\Resources\LaporanImutResource\Table;

use App\Filament\Resources\LaporanImutResource\Pages\ImutDataReport;
use App\Filament\Resources\LaporanImutResource\Pages\UnitKerjaImutDataReport;
use App\Filament\Resources\LaporanImutResource\Pages\UnitKerjaReport;
use App\Models\ImutPenilaian;
use App\Tables\Columns\ProgressColumn;
use Filament\Forms\Components\Select;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class LaporanImutTable
{
    public static function columns(): array
    {
        return [
            TextColumn::make('name')
                ->label('Nama Laporan')
                ->sortable()
                ->searchable(),

            TextColumn::make('createdBy.name')
                ->label('Pembuat Laporan')
                ->alignCenter()
                ->sortable()
                ->searchable(),

            TextColumn::make('assessment_period')
                ->label('Periode Asesmen')
                ->alignCenter()
                ->getStateUsing(fn ($record) => self::formatAssessmentPeriod($record)),

            TextColumn::make('status')
                ->label('Status')
                ->badge()
                ->alignCenter()
                ->color(fn (string $state): string => match ($state) {
                    'canceled' => 'danger',
                    'process' => 'primary',
                    'complete' => 'success',
                }),

            self::getProgressColumn(),
            self::getUnitKerjaTerisiColumn(),
        ];
    }

    public static function filters(): array
    {
        return [];
    }

    public static function actions(): array
    {
        return [
            Action::make('isi_penilaian')
                ->label('Isi Penilaian')
                ->icon('heroicon-s-clipboard-document-list')
                ->color('warning')
                ->visible(
                    fn ($record) => method_exists($record, 'trashed')
                        && ! $record->trashed()
                        && self::userHasAccessToLaporan($record)
                        && \Carbon\Carbon::today()->lte($record->assessment_period_end)
                )
                ->url(fn ($record) => self::getIsiPenilaianUrl($record)),

            ActionGroup::make([
                EditAction::make()
                    ->visible(fn ($record) => method_exists($record, 'trashed') && ! $record->trashed()),
                DeleteAction::make()
                    ->visible(fn ($record) => method_exists($record, 'trashed') && ! $record->trashed()),
                Action::make('summary')
                    ->label('Summary')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->color('success')
                    ->visible(
                        fn ($record) => method_exists($record, 'trashed') &&
                            ! $record->trashed() &&
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
                    }),
            ]),

            // Aksi hanya jika record dalam trash
            RestoreAction::make()
                ->visible(
                    fn ($record) => Gate::allows('restore', $record) &&
                        method_exists($record, 'trashed') &&
                        $record->trashed()
                ),

            ForceDeleteAction::make()
                ->visible(
                    fn ($record) => Gate::allows('forceDelete', $record) &&
                        method_exists($record, 'trashed') &&
                        $record->trashed()
                ),
        ];
    }

    protected static function getProgressColumn(): ProgressColumn
    {
        return ProgressColumn::make('progress')
            ->label('Progress')
            ->visible(fn () => Auth::user()?->unitKerjas()->exists())
            ->getStateUsing(fn ($record) => self::calculateProgress($record))
            ->tooltip(fn ($record) => self::progressTooltip($record));
    }

    protected static function getUnitKerjaTerisiColumn(): ProgressColumn
    {
        return ProgressColumn::make('unit_kerja_terisi')
            ->label('Unit Kerja Terisi')
            ->visible(
                fn () => Gate::check('view_unit_kerja_report_laporan::imut') &&
                    Gate::check('view_imut_data_report_laporan::imut') &&
                    Gate::check('update_profile_penilaian_laporan::imut') &&
                    Gate::check('create_recommendation_penilaian_laporan::imut')
            )
            ->getStateUsing(fn ($record) => self::calculateUnitKerjaTerisi($record))
            ->tooltip(fn ($record) => self::tooltipUnitKerjaTerisi($record));
    }

    protected static function userHasAccessToLaporan($record): bool
    {
        $user = Auth::user();

        $userUnitKerjaIds = $user->unitKerjas->pluck('id')->toArray();
        $laporanUnitKerjaIds = $record->unitKerjas->pluck('id')->toArray();

        return ! empty(array_intersect($userUnitKerjaIds, $laporanUnitKerjaIds));
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

        protected static function formatAssessmentPeriod($record): string
    {
        $start = \Carbon\Carbon::parse($record->assessment_period_start)->translatedFormat('d M');
        $end = \Carbon\Carbon::parse($record->assessment_period_end)->translatedFormat('d M Y');

        return "$start - $end";
    }

    protected static function calculateProgress($record): ?float
    {
        $userUnitIds = Auth::user()?->unitKerjas?->pluck('id')->toArray() ?? [];

        $laporanUnitKerjaIds = DB::table('laporan_unit_kerjas')
            ->where('laporan_imut_id', $record->id)
            ->whereIn('unit_kerja_id', $userUnitIds)
            ->pluck('id')
            ->toArray();

        if (empty($laporanUnitKerjaIds) || ! self::userHasAccessToLaporan($record)) {
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

}