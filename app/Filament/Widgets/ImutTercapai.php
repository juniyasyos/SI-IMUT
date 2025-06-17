<?php

namespace App\Filament\Widgets;

use App\Facades\LaporanImut as LaporanImutFacade;
use App\Models\ImutData;
use Filament\Tables;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

/**
 * Widget untuk menampilkan indikator mutu yang telah tercapai pada dashboard.
 */
class ImutTercapai extends BaseWidget
{
    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 2;

    public static function canView(): bool
    {
        return Auth::user()?->can('widget_ImutTercapai') ?? false;
    }

    protected function query(): Builder
    {
        $laporan = LaporanImutFacade::getLatestLaporan();

        if (! $laporan) {
            return ImutData::query()->whereRaw('1 = 0');
        }

        $laporanId = $laporan->id;

        return ImutData::query()
            ->where('status', true)
            ->whereHas('latestProfile.penilaian', fn ($q) => $q->whereHas('laporanUnitKerja', fn ($q) => $q->where('laporan_imut_id', $laporanId))
                ->whereNotNull('numerator_value')
                ->whereNotNull('denominator_value')
            )
            ->with([
                'latestProfile' => fn ($q) => $q->with([
                    'penilaian' => fn ($q) => $q->whereHas('laporanUnitKerja', fn ($q) => $q->where('laporan_imut_id', $laporanId))
                        ->whereNotNull('numerator_value')
                        ->whereNotNull('denominator_value'),
                ]),
            ]);
    }

    public function table(Tables\Table $table): Tables\Table
    {
        $laporan = LaporanImutFacade::getLatestLaporan();

        if (! $laporan) {
            return $table
                ->query(ImutData::query()->whereRaw('1 = 0'))
                ->columns([
                    Tables\Columns\TextColumn::make('message')
                        ->label('Informasi')
                        ->getStateUsing(fn () => 'Tidak ada laporan terbaru')
                        ->extraAttributes(['class' => 'text-center text-gray-500']),
                ]);
        }

        $totalUnit = $laporan->unitKerjas->count();

        return $table
            ->query($this->query())
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5)
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Indikator')
                    ->wrap(),

                Tables\Columns\TextColumn::make('unit_melapor')
                    ->label('Unit Melapor')
                    ->getStateUsing(fn ($record) => $this->formatUnitMelapor($record->latestProfile, $totalUnit)),

                Tables\Columns\TextColumn::make('tercapai')
                    ->label('Unit Tercapai')
                    ->tooltip('Jumlah unit kerja yang mencapai target dari yang sudah menilai')
                    ->badge()
                    ->getStateUsing(fn ($record) => $this->formatTercapai($record->latestProfile))
                    ->color(fn ($record) => $this->getBadgeColor($record->latestProfile)),
            ]);
    }

    protected function formatUnitMelapor($profile, int $totalUnit): string
    {
        if (! $profile || $totalUnit === 0) {
            return '0/0';
        }

        $filled = $profile->penilaian
            ->pluck('laporan_unit_kerja_id')
            ->unique()
            ->count();

        return "$filled/$totalUnit";
    }

    protected function formatTercapai($profile): string
    {
        $grouped = $this->getGroupedPenilaian($profile);

        if ($grouped->isEmpty()) {
            return 'Belum ada data';
        }

        $tercapai = $this->countTercapai($grouped, $profile);

        return "$tercapai dari {$grouped->count()} Unit";
    }

    protected function getBadgeColor($profile): string
    {
        $grouped = $this->getGroupedPenilaian($profile);

        if ($grouped->isEmpty()) {
            return 'gray';
        }

        $tercapai = $this->countTercapai($grouped, $profile);
        $percentage = $tercapai / $grouped->count();

        return match (true) {
            $percentage >= 1 => 'success',
            $percentage >= 0.6 => 'warning',
            default => 'danger',
        };
    }

    protected function getGroupedPenilaian($profile): Collection
    {
        return ! $profile
            ? collect()
            : $profile->penilaian
                ->whereNotNull('numerator_value')
                ->whereNotNull('denominator_value')
                ->groupBy('laporan_unit_kerja_id');
    }

    protected function countTercapai(Collection $grouped, $profile): int
    {
        return $grouped->filter(fn (Collection $penilaians) => $penilaians->contains(fn ($p) => $p->denominator_value != 0 && $this->isTercapai($p, $profile)))->count();
    }

    protected function isTercapai($penilaian, $profile): bool
    {
        if ($penilaian->denominator_value == 0) {
            return false;
        }

        $hasil = round(($penilaian->numerator_value / $penilaian->denominator_value) * 100, 2);

        return match ($profile->target_operator) {
            '=' => $hasil == $profile->target_value,
            '>=' => $hasil >= $profile->target_value,
            '<=' => $hasil <= $profile->target_value,
            '>' => $hasil > $profile->target_value,
            '<' => $hasil < $profile->target_value,
            default => false,
        };
    }
}
