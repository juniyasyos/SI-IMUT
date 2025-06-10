<?php

namespace App\Jobs;

use App\Models\ImutPenilaian;
use App\Models\LaporanImut;
use App\Models\LaporanUnitKerja;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class ProsesPenilaianImut implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public LaporanImut $laporan) {}

    public function handle(): void
    {
        DB::transaction(function () {
            $laporan = $this->laporan;

            $unitKerjas = $laporan->unitKerjas()->with('imutData.latestProfile')->get();

            $unitKerjas->each(function ($unitKerja) use ($laporan) {
                $laporanUnitKerja = LaporanUnitKerja::firstOrCreate([
                    'laporan_imut_id' => $laporan->id,
                    'unit_kerja_id' => $unitKerja->id,
                ]);

                $unitKerja->imutData->each(function ($imutData) use ($laporanUnitKerja) {
                    $latestProfile = $imutData->latestProfile;

                    if (! $latestProfile) {
                        return;
                    }

                    ImutPenilaian::firstOrCreate([
                        'imut_profil_id' => $latestProfile->id,
                        'laporan_unit_kerja_id' => $laporanUnitKerja->id,
                    ]);
                });
            });
        });

        Notification::make()
            ->title('Proses Penilaian Selesai')
            ->body('Semua data penilaian berhasil dibuat.')
            ->status('success')
            ->sendToDatabase($this->laporan->createdBy);
    }
}
