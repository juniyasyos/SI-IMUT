<?php

namespace App\Observers;

use App\Models\ImutPenilaian;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Juniyasyos\FilamentMediaManager\Models\Media;

class MediaObserver
{
    public function creating(Media $media): void
    {
        Log::info("⏳ Creating media: {$media->original_name}");
    }

    public function created(Media $media): void
    {
        try {
            Log::info("✅ Media created: {$media->id}");

            if ($media->model_type !== ImutPenilaian::class) {
                Log::debug("Media {$media->id} tidak terhubung dengan ImutPenilaian.");

                return;
            }

            /** @var ImutPenilaian|null $penilaian */
            $penilaian = ImutPenilaian::with('laporanUnitKerja.unitKerja.folder')->find($media->model_id);

            if (! $penilaian?->laporanUnitKerja?->unitKerja?->folder) {
                Log::warning("Folder tidak ditemukan untuk media {$media->id}");

                return;
            }

            $folder = $penilaian->laporanUnitKerja->unitKerja->folder;

            // Cek dan insert relasi folder-media
            DB::table('folder_has_models')->updateOrInsert([
                'folder_id' => $folder->id,
                'model_type' => Media::class,
                'model_id' => $media->id,
            ]);

            Log::info("📁 Media {$media->id} dikaitkan ke folder {$folder->id}");
        } catch (\Throwable $e) {
            Log::error("❌ Gagal memproses Media {$media->id} pada created(): {$e->getMessage()}");
        }
    }

    public function updating(Media $media): void
    {
        Log::info("✏️ Media {$media->id} sedang diperbarui");
    }

    public function updated(Media $media): void
    {
        Log::info("✅ Media {$media->id} berhasil diperbarui");
    }

    public function deleting(Media $media): void
    {
        Log::warning("⚠️ Media {$media->id} akan dihapus (soft delete)");
    }

    public function deleted(Media $media): void
    {
        Log::warning("🗑️ Media {$media->id} dihapus (soft delete)");

        DB::table('folder_has_models')
            ->where('model_type', Media::class)
            ->where('model_id', $media->id)
            ->delete();

        Log::info("🧹 Relasi folder_has_models untuk media {$media->id} dihapus");
    }

    public function restoring(Media $media): void
    {
        Log::info("♻️ Media {$media->id} akan di-restore");
    }

    public function restored(Media $media): void
    {
        Log::info("✅ Media {$media->id} berhasil di-restore");

        $this->created($media); // Re-link folder
    }

    public function forceDeleted(Media $media): void
    {
        Log::error("❌ Media {$media->id} dihapus permanen");

        DB::table('folder_has_models')
            ->where('model_type', Media::class)
            ->where('model_id', $media->id)
            ->delete();

        Log::info("🧨 Data folder_has_models untuk media {$media->id} dihapus secara permanen");
    }
}
