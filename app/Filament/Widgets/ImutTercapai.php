<?php

namespace App\Filament\Widgets;

use App\Models\ImutData;
use App\Models\ImutPenilaian;
use App\Models\LaporanImut;
use App\Services\DashboardImutService;
use Filament\Tables;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Collection;

class ImutTercapai extends BaseWidget
{
    protected static ?int $sort = 6;
    protected int|string|array $columnSpan = 'full';

    protected function getDashboardService(): DashboardImutService
    {
        return app(DashboardImutService::class);
    }

    public function table(Tables\Table $table): Tables\Table
    {
        $laporanId = $this->getDashboardService()->getLatestLaporanId();

        $query = ImutData::query()
            ->where('status', true)
            ->whereHas(
                'profiles.penilaian.laporanUnitKerja',
                fn($q) =>
                $q->where('laporan_imut_id', $laporanId)
            )
            ->with('profiles.penilaian.laporanUnitKerja');

        return $table
            ->query($query)
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5)
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Indikator')
                    ->wrap(),

                Tables\Columns\TextColumn::make('unit_melapor')
                    ->label('Unit Melapor')
                    ->getStateUsing(fn($record) => $this->getUnitMelapor($record, $laporanId)),

                Tables\Columns\TextColumn::make('tercapai')
                    ->label('Unit Tercapai')
                    ->tooltip('Jumlah unit kerja yang mencapai target dari yang sudah menilai')
                    ->badge()
                    ->getStateUsing(fn($record) => $this->getTercapaiText($record, $laporanId))
                    ->color(fn($record) => $this->getTercapaiColor($record, $laporanId)),
            ]);
    }

    protected function getUnitMelapor($record, $laporanId): string
    {
        $profile = $record->profiles->sortByDesc('version')->first();
        if (!$profile) return '0/0';

        $total = LaporanImut::find($laporanId)?->unitKerjas()->count() ?? 0;

        $terisi = ImutPenilaian::query()
            ->where('imut_profil_id', $profile->id)
            ->whereHas('laporanUnitKerja', fn($q) => $q->where('laporan_imut_id', $laporanId))
            ->whereNotNull('numerator_value')
            ->whereNotNull('denominator_value')
            ->distinct('laporan_unit_kerja_id')
            ->count('laporan_unit_kerja_id');

        return "$terisi/$total";
    }

    protected function getTercapaiText($record, $laporanId): string
    {
        $profile = $record->profiles->sortByDesc('version')->first();
        if (!$profile) return 'Belum ada data';

        $grouped = $this->getPenilaianByUnit($profile->id, $laporanId);
        $total = $grouped->count();
        if ($total === 0) return 'Belum ada data';

        $tercapai = $this->countTercapai($grouped, $profile);

        return "$tercapai dari $total Unit";
    }

    protected function getTercapaiColor($record, $laporanId): string
    {
        $profile = $record->profiles->sortByDesc('version')->first();
        if (!$profile) return 'gray';

        $grouped = $this->getPenilaianByUnit($profile->id, $laporanId);
        $total = $grouped->count();
        if ($total === 0) return 'gray';

        $percent = $this->countTercapai($grouped, $profile) / $total;

        return match (true) {
            $percent >= 1    => 'success',
            $percent >= 0.6  => 'warning',
            default          => 'danger',
        };
    }

    protected function getPenilaianByUnit(int $profileId, int $laporanId): Collection
    {
        return ImutPenilaian::query()
            ->where('imut_profil_id', $profileId)
            ->whereHas('laporanUnitKerja', fn($q) => $q->where('laporan_imut_id', $laporanId))
            ->whereNotNull('numerator_value')
            ->whereNotNull('denominator_value')
            ->get()
            ->groupBy('laporan_unit_kerja_id');
    }

    protected function countTercapai(Collection $grouped, $profile): int
    {
        return $grouped->filter(function ($penilaians) use ($profile) {
            return $penilaians->contains(
                fn($p) =>
                $p->denominator_value != 0 &&
                    $this->isTercapai($p, $profile)
            );
        })->count();
    }

    protected function isTercapai($penilaian, $profile): bool
    {
        if ($penilaian->denominator_value == 0) return false;

        $hasil = round(($penilaian->numerator_value / $penilaian->denominator_value) * 100, 2);

        return match ($profile->target_operator) {
            '='  => $hasil == $profile->target_value,
            '>=' => $hasil >= $profile->target_value,
            '<=' => $hasil <= $profile->target_value,
            '>'  => $hasil > $profile->target_value,
            '<'  => $hasil < $profile->target_value,
            default => false,
        };
    }
}
