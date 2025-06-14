<?php

namespace App\Observers;

use App\Models\ImutPenilaian;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Juniyasyos\FilamentMediaManager\Models\Media;
use Throwable;

class MediaObserver
{
    public function created(Media $media): void
    {
        try {
            if ($media->model_type !== ImutPenilaian::class) {
                return;
            }

            /** @var ImutPenilaian|null $penilaian */
            $penilaian = ImutPenilaian::with('laporanUnitKerja.unitKerja.folder')->find($media->model_id);

            $folder = $penilaian?->laporanUnitKerja?->unitKerja?->folder;

            if (! $folder) {
                Log::warning("⚠️ Folder tidak ditemukan untuk Media ID {$media->id}");

                return;
            }

            DB::table('folder_has_models')->updateOrInsert([
                'folder_id' => $folder->id,
                'model_type' => Media::class,
                'model_id' => $media->id,
            ]);

            Log::info("📎 Media ID {$media->id} berhasil dikaitkan ke Folder ID {$folder->id}");
        } catch (Throwable $e) {
            Log::error("❌ Gagal mengaitkan Media ID {$media->id} ke folder: {$e->getMessage()}", [
                'exception' => $e,
            ]);
        }
    }

    public function updated(Media $media): void
    {
        Log::info("✏️ Media ID {$media->id} diperbarui");
    }

    public function deleted(Media $media): void
    {
        try {
            DB::table('folder_has_models')
                ->where('model_type', Media::class)
                ->where('model_id', $media->id)
                ->delete();

            Log::warning("🗑️ Media ID {$media->id} dihapus (soft delete), relasi folder_has_models dihapus");
        } catch (Throwable $e) {
            Log::error("❌ Gagal menghapus relasi folder_has_models saat media ID {$media->id} dihapus: {$e->getMessage()}", [
                'exception' => $e,
            ]);
        }
    }

    public function restored(Media $media): void
    {
        Log::notice("♻️ Media ID {$media->id} dipulihkan");

        // Re-link folder jika masih valid
        $this->created($media);
    }

    public function forceDeleted(Media $media): void
    {
        try {
            DB::table('folder_has_models')
                ->where('model_type', Media::class)
                ->where('model_id', $media->id)
                ->delete();

            Log::error("❌ Media ID {$media->id} dihapus permanen, relasi folder_has_models dihapus");
        } catch (Throwable $e) {
            Log::error("❌ Gagal menghapus permanen data relasi untuk media ID {$media->id}: {$e->getMessage()}", [
                'exception' => $e,
            ]);
        }
    }
}
