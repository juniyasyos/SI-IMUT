<?php

namespace App\Console\Commands;

use App\Models\LaporanImut;
use Filament\Notifications\Notification;
use Illuminate\Console\Command;

class NotifikasiDeadlineLaporan extends Command
{
    protected $signature = 'laporan:ingatkan-deadline';

    protected $description = 'Mengirim notifikasi H-2 dan H-1 sebelum batas waktu pengisian laporan';

    public function handle(): int
    {
        $today = now()->startOfDay();

        collect([
            'H-2' => $today->copy()->addDays(2),
            'H-1' => $today->copy()->addDay(),
        ])->each(function ($targetDate, $label) {
            LaporanImut::query()
                ->where('status', LaporanImut::STATUS_PROCESS)
                ->whereDate('assessment_period_end', $targetDate)
                ->with(['unitKerjas.users', 'createdBy'])
                ->lazy()
                ->each(function ($laporan) use ($label) {
                    $dateStr = $laporan->assessment_period_end->format('d M Y');

                    // Kirim ke semua user di unit kerja
                    $laporan->unitKerjas
                        ->flatMap->users
                        ->unique('id')
                        ->each(function ($user) use ($laporan, $label, $dateStr) {
                            Notification::make()
                                ->title("⏰ $label – Tenggat Laporan Hampir Habis")
                                ->body("Laporan \"{$laporan->name}\" akan berakhir pada $dateStr.")
                                ->icon('heroicon-o-clock')
                                ->color('warning')
                                ->persistent()
                                ->sendToDatabase($user);
                        });

                    // Kirim juga ke creator laporan
                    if ($laporan->createdBy) {
                        Notification::make()
                            ->title("👤 $label – Notifikasi untuk Pembuat Laporan")
                            ->body("Laporan yang Anda buat \"{$laporan->name}\" akan berakhir pada $dateStr.")
                            ->icon('heroicon-o-user')
                            ->color('warning')
                            ->persistent()
                            ->sendToDatabase($laporan->createdBy);
                    }
                });
        });

        $this->info('Notifikasi deadline berhasil dikirim.');

        return self::SUCCESS;
    }
}
