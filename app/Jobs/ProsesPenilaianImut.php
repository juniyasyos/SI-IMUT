<?php

namespace App\Jobs;

use App\Filament\Resources\ImutDataResource;
use App\Filament\Resources\LaporanImutResource;
use App\Models\ImutPenilaian;
use App\Models\LaporanImut;
use App\Models\LaporanUnitKerja;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProsesPenilaianImut implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $laporanId) {}

    public function handle(): void
    {
        try {
            DB::transaction(function () {
                $laporan = LaporanImut::with('unitKerjas.imutData.latestProfile', 'unitKerjas.users')
                    ->findOrFail($this->laporanId);

                $indikatorKurangProfil = [];

                foreach ($laporan->unitKerjas as $unitKerja) {
                    $laporanUnitKerja = LaporanUnitKerja::firstOrCreate([
                        'laporan_imut_id' => $laporan->id,
                        'unit_kerja_id'   => $unitKerja->id,
                    ]);

                    foreach ($unitKerja->imutData as $imutData) {
                        if (! $imutData->status) {
                            continue;
                        }

                        $latestProfile = $imutData->latestProfile;

                        if (! $latestProfile) {
                            $indikatorKurangProfil[] = $imutData->title;
                            continue;
                        }

                        ImutPenilaian::firstOrCreate([
                            'imut_profil_id'        => $latestProfile->id,
                            'laporan_unit_kerja_id' => $laporanUnitKerja->id,
                        ]);
                    }
                }

                // Notifikasi ke pembuat laporan jika ada indikator tanpa profil
                if (! empty($indikatorKurangProfil)) {
                    collect($indikatorKurangProfil)
                        ->unique()
                        ->each(function (string $title) use ($laporan) {
                            $searchUrl = ImutDataResource::getUrl('index');

                            Notification::make()
                                ->title('⚠️ Indikator Belum Memiliki Profil')
                                ->body("Indikator {$title} belum memiliki profil.")
                                ->icon('heroicon-m-exclamation-triangle')
                                ->color('warning')
                                ->persistent()
                                ->sendToDatabase($laporan->createdBy);
                        });
                }

                // Notifikasi umum ke semua user unit kerja
                $users = $laporan->unitKerjas->flatMap->users->unique('id');

                foreach ($users as $user) {
                    Notification::make()
                        ->title('📄 Laporan Baru Dibuat')
                        ->body('Laporan baru membutuhkan perhatian unit kerja Anda.')
                        ->icon('heroicon-m-clipboard-document-check')
                        ->color('success')
                        ->persistent()
                        ->sendToDatabase($user);
                }
            });

            // Notifikasi akhir proses penilaian ke pembuat laporan
            $laporan = LaporanImut::findOrFail($this->laporanId);

            Notification::make()
                ->title('✅ Proses Penilaian Selesai')
                ->body('Semua data penilaian berhasil dibuat.')
                ->status('success')
                ->sendToDatabase($laporan->createdBy);
        } catch (\Throwable $e) {
            Log::error('Job ProsesPenilaianImut gagal: ' . $e->getMessage(), [
                'laporan_id' => $this->laporanId,
                'exception' => $e,
            ]);
            throw $e;
        }
    }
}