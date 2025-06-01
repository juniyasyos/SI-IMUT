<?php

namespace App\Filament\Widgets;

use App\Models\ImutData;
use App\Services\LaporanImutService;
use Filament\Tables;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Collection;

class ImutTercapai extends BaseWidget
{
    protected static ?int $sort = 6;
    protected int|string|array $columnSpan = '2';

    protected function getLaporanService(): LaporanImutService
    {
        return app(LaporanImutService::class);
    }

    public function table(Tables\Table $table): Tables\Table
    {
        $laporan = $this->getLaporanService()->getLatestLaporan();
        if (!$laporan) return $table->columns([]);

        $laporanId = $laporan->id;
        $totalUnit = $laporan->unitKerjas->count();

        $query = ImutData::query()
            ->where('status', true)
            ->whereHas(
                'latestProfile.penilaian',
                fn($q) =>
                $q->whereHas('laporanUnitKerja', fn($q) => $q->where('laporan_imut_id', $laporanId))
                    ->whereNotNull('numerator_value')
                    ->whereNotNull('denominator_value')
            )
            ->with([
                'latestProfile' => fn($q) => $q->with([
                    'penilaian' => fn($q) =>
                    $q->whereHas(
                        'laporanUnitKerja',
                        fn($q) =>
                        $q->where('laporan_imut_id', $laporanId)
                    )->whereNotNull('numerator_value')->whereNotNull('denominator_value')
                ])
            ]);

        return $table
            ->query($query)
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5)
            ->columns([
                Tables\Columns\TextColumn::make('title')->label('Indikator')->wrap(),

                Tables\Columns\TextColumn::make('unit_melapor')
                    ->label('Unit Melapor')
                    ->getStateUsing(fn($record) => $this->getUnitMelaporState($record->latestProfile, $totalUnit)),

                Tables\Columns\TextColumn::make('tercapai')
                    ->label('Unit Tercapai')
                    ->tooltip('Jumlah unit kerja yang mencapai target dari yang sudah menilai')
                    ->badge()
                    ->getStateUsing(fn($record) => $this->getTercapaiState($record->latestProfile))
                    ->color(fn($record) => $this->getBadgeColor($record->latestProfile)),
            ]);
    }


    protected function getUnitMelaporState($profile, int $totalUnit): string
    {
        if (!$profile) return '0/0';

        $filled = $profile->penilaian->pluck('laporan_unit_kerja_id')->unique()->count();
        return "$filled/$totalUnit";
    }

    protected function getTercapaiState($profile): string
    {
        if (!$profile) return 'Belum ada data';

        $grouped = $profile->penilaian->groupBy('laporan_unit_kerja_id');
        $total = $grouped->count();
        if ($total === 0) return 'Belum ada data';

        $tercapai = $grouped->filter(
            fn($penilaians) =>
            $penilaians->contains(
                fn($p) =>
                $p->denominator_value != 0 && $this->isTercapai($p, $profile)
            )
        )->count();

        return "$tercapai dari $total Unit";
    }

    protected function getBadgeColor($profile): string
    {
        if (!$profile) return 'gray';

        $grouped = $profile->penilaian
            ->whereNotNull('numerator_value')
            ->whereNotNull('denominator_value')
            ->groupBy('laporan_unit_kerja_id');

        $total = $grouped->count();
        if ($total === 0) return 'gray';

        $tercapai = $grouped->filter(
            fn($penilaians) =>
            $penilaians->contains(
                fn($p) =>
                $p->denominator_value != 0 && $this->isTercapai($p, $profile)
            )
        )->count();

        $percent = $tercapai / $total;

        return match (true) {
            $percent >= 1     => 'success',
            $percent >= 0.6   => 'warning',
            default           => 'danger',
        };
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
