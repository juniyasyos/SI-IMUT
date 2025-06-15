<?php

namespace App\Filament\Widgets;

use App\Models\ImutData;
use App\Services\LaporanImutService;
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
    /**
     * Urutan tampilan widget di dashboard.
     */
    protected static ?int $sort = 6;

    /**
     * Lebar widget dalam grid layout.
     */
    protected int|string|array $columnSpan = 2;

    /**
     * Mengecek apakah pengguna saat ini memiliki izin untuk melihat widget.
     */
    public static function canView(): bool
    {
        return Auth::user()?->can('widget_ImutTercapai') ?? false;
    }

    /**
     * Mengambil instance dari layanan LaporanImutService.
     */
    protected function getLaporanService(): LaporanImutService
    {
        return app(LaporanImutService::class);
    }

    /**
     * Query utama untuk mengambil data indikator mutu yang aktif dan relevan dengan laporan terbaru.
     */
    protected function query(): Builder
    {
        $laporan = $this->getLaporanService()->getLatestLaporan();

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

    /**
     * Mendefinisikan struktur dan isi tabel pada widget.
     */
    public function table(Tables\Table $table): Tables\Table
    {
        $laporan = $this->getLaporanService()->getLatestLaporan();

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

    /**
     * Mengembalikan string representasi jumlah unit yang melapor.
     *
     * @param  mixed  $profile
     */
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

    /**
     * Mengembalikan string jumlah unit yang mencapai target.
     *
     * @param  mixed  $profile
     */
    protected function formatTercapai($profile): string
    {
        $grouped = $this->getGroupedPenilaian($profile);

        if ($grouped->isEmpty()) {
            return 'Belum ada data';
        }

        $tercapai = $this->countTercapai($grouped, $profile);

        return "$tercapai dari {$grouped->count()} Unit";
    }

    /**
     * Menentukan warna badge berdasarkan persentase ketercapaian.
     *
     * @param  mixed  $profile
     */
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

    /**
     * Mengelompokkan data penilaian berdasarkan ID laporan unit kerja.
     *
     * @param  mixed  $profile
     */
    protected function getGroupedPenilaian($profile): Collection
    {
        return ! $profile
            ? collect()
            : $profile->penilaian
                ->whereNotNull('numerator_value')
                ->whereNotNull('denominator_value')
                ->groupBy('laporan_unit_kerja_id');
    }

    /**
     * Menghitung jumlah unit kerja yang mencapai target.
     *
     * @param  mixed  $profile
     */
    protected function countTercapai(Collection $grouped, $profile): int
    {
        return $grouped->filter(fn (Collection $penilaians) => $penilaians->contains(fn ($p) => $p->denominator_value != 0 && $this->isTercapai($p, $profile)
        )
        )->count();
    }

    /**
     * Menentukan apakah nilai penilaian mencapai target berdasarkan operator dan nilai target.
     *
     * @param  mixed  $penilaian
     * @param  mixed  $profile
     */
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
