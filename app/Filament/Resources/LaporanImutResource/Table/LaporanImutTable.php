<?php

namespace App\Filament\Resources\LaporanImutResource\Table;

use App\Filament\Resources\LaporanImutResource\Pages\ImutDataReport;
use App\Filament\Resources\LaporanImutResource\Pages\UnitKerjaImutDataReport;
use App\Filament\Resources\LaporanImutResource\Pages\UnitKerjaReport;
use App\Models\ImutPenilaian;
use App\Support\CacheKey;
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
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

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
                ->getStateUsing(fn($record) => self::formatAssessmentPeriod($record)),

            TextColumn::make('status')
                ->label('Status')
                ->badge()
                ->alignCenter()
                ->getStateUsing(fn($record) => self::resolveStatus($record))
                ->color(fn($state) => match ($state) {
                    'coming_soon' => 'gray',
                    'process' => 'primary',
                    'complete' => 'success',
                    default => 'secondary',
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
                ->label(fn($record) => match (self::resolveStatus($record)) {
                    'coming_soon' => 'Belum Dibuka',
                    'complete' => 'Hasil Penilaian',
                    default => 'Isi Penilaian',
                })
                ->icon(fn($record) => match (self::resolveStatus($record)) {
                    'coming_soon' => 'heroicon-s-clock',
                    'complete' => 'heroicon-s-document-check',
                    default => 'heroicon-s-clipboard-document-list',
                })
                ->color(fn($record) => match (self::resolveStatus($record)) {
                    'coming_soon' => 'gray',
                    'complete' => 'gray',
                    default => 'warning',
                })
                ->disabled(fn($record) => self::resolveStatus($record) === 'coming_soon')
                ->visible(
                    fn($record) =>
                    method_exists($record, 'trashed') &&
                        ! $record->trashed() &&
                        self::userHasAccessToLaporan($record)
                )
                ->url(function ($record) {
                    $url = self::getIsiPenilaianUrl($record);
                    if (! $url) return null;

                    return self::resolveStatus($record) === 'complete'
                        ? $url . '&readonly=1'
                        : $url;
                }),

            ActionGroup::make([
                EditAction::make()
                    ->visible(fn($record) => method_exists($record, 'trashed') && ! $record->trashed()),

                DeleteAction::make()
                    ->visible(fn($record) => method_exists($record, 'trashed') && ! $record->trashed()),

                Action::make('summary')
                    ->label('Summary')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->color('success')
                    ->visible(
                        fn($record) => method_exists($record, 'trashed') &&
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

    protected static function getProgressColumn(): ProgressColumn
    {
        return ProgressColumn::make('progress')
            ->label('Progress')
            ->visible(fn() => Auth::user()?->unitKerjas()->exists())
            ->getStateUsing(fn($record) => self::calculateProgress($record))
            ->tooltip(fn($record) => self::progressTooltip($record));
    }

    protected static function getUnitKerjaTerisiColumn(): ProgressColumn
    {
        return ProgressColumn::make('unit_kerja_terisi')
            ->label('Unit Kerja Terisi')
            ->visible(
                fn() =>
                Gate::check('view_unit_kerja_report_laporan::imut') &&
                    Gate::check('view_imut_data_report_laporan::imut') &&
                    Gate::check('update_profile_penilaian_imut::penilaian') &&
                    Gate::check('create_recommendation_penilaian_imut::penilaian')
            )
            ->getStateUsing(fn($record) => self::calculateUnitKerjaTerisi($record))
            ->tooltip(fn($record) => self::tooltipUnitKerjaTerisi($record));
    }

    protected static function resolveStatus($record): string
    {
        $today = Carbon::today();
        $start = Carbon::parse($record->assessment_period_start);
        $end = Carbon::parse($record->assessment_period_end);

        return match (true) {
            $today->lt($start) => 'coming_soon',
            $today->gt($end)   => 'complete',
            default            => 'process',
        };
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
        $start = Carbon::parse($record->assessment_period_start)->translatedFormat('d M');
        $end = Carbon::parse($record->assessment_period_end)->translatedFormat('d M Y');

        return "$start - $end";
    }

    protected static function calculateProgress($record): ?float
    {
        if (!self::userHasAccessToLaporan($record)) return null;

        $stats = self::getPenilaianStats($record, true);
        return $stats['total'] > 0 ? round(($stats['filled'] / $stats['total']) * 100, 2) : 0;
    }

    protected static function progressTooltip($record): ?string
    {
        $stats = self::getPenilaianStats($record, true);

        if ($stats['total'] === 0) return null;
        return "{$stats['filled']} / {$stats['total']} Penilaian selesai";
    }

    protected static function calculateUnitKerjaTerisi($record): float
    {
        $stats = self::getPenilaianStats($record, false);
        return $stats['unit_kerja_total'] > 0
            ? round(($stats['unit_kerja_filled'] / $stats['unit_kerja_total']) * 100, 2)
            : 0;
    }

    protected static function tooltipUnitKerjaTerisi($record): string
    {
        $stats = self::getPenilaianStats($record, false);
        return "{$stats['unit_kerja_filled']} dari {$stats['unit_kerja_total']} unit kerja sudah mengisi";
    }

    protected static function getPenilaianStats($record, $filterByUserUnit = true): array
    {
        $userId = Auth::id();
        $userUnitIds = Auth::user()?->unitKerjas?->pluck('id')->toArray() ?? [];
        $cacheKey = CacheKey::getPenilaianStats($record->id, $filterByUserUnit, $userId);

        return Cache::remember($cacheKey, now()->addDays(7), function () use ($record, $filterByUserUnit, $userUnitIds) {
            // Ambil semua data penilaian sesuai kondisi
            $query = ImutPenilaian::query()
                ->select(['laporan_unit_kerja_id', 'numerator_value', 'denominator_value'])
                ->whereHas('laporanUnitKerja', function ($q) use ($record, $userUnitIds, $filterByUserUnit) {
                    $q->where('laporan_imut_id', $record->id);

                    if ($filterByUserUnit) {
                        $q->whereIn('unit_kerja_id', $userUnitIds);
                    }
                });

            $data = $query->get();

            $unitKerjaMap = [];
            $filledCount = 0;

            foreach ($data as $item) {
                $key = $item->laporan_unit_kerja_id;
                $unitKerjaMap[$key]['total'] = ($unitKerjaMap[$key]['total'] ?? 0) + 1;

                $isFilled = $item->numerator_value !== null && $item->denominator_value !== null;
                if ($isFilled) {
                    $unitKerjaMap[$key]['filled'] = ($unitKerjaMap[$key]['filled'] ?? 0) + 1;
                    $filledCount++;
                }
            }

            $unitKerjaFilledCount = collect($unitKerjaMap)->filter(function ($item) {
                return $item['total'] === ($item['filled'] ?? 0) && $item['total'] > 0;
            })->count();

            return [
                'total' => $data->count(),
                'filled' => $filledCount,
                'unit_kerja_total' => count($unitKerjaMap),
                'unit_kerja_filled' => $unitKerjaFilledCount,
            ];
        });
    }
}