<?php

namespace App\Observers;

use App\Models\UnitKerja;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Juniyasyos\FilamentMediaManager\Models\Folder;
use Throwable;

class UnitKerjaObserver
{
    public function created(UnitKerja $unitKerja): void
    {
        try {
            $this->createAssociatedFolder($unitKerja);
            Log::info("✅ UnitKerja berhasil dibuat: ID {$unitKerja->id} - {$unitKerja->unit_name}");
        } catch (Throwable $e) {
            Log::error("❌ Gagal membuat folder untuk UnitKerja ID {$unitKerja->id}: ".$e->getMessage(), [
                'exception' => $e,
            ]);
        }
    }

    public function updated(UnitKerja $unitKerja): void
    {
        try {
            $this->updateAssociatedFolder($unitKerja);
            Log::info("✏️ UnitKerja diperbarui: ID {$unitKerja->id}");
        } catch (Throwable $e) {
            Log::error("❌ Gagal memperbarui folder untuk UnitKerja ID {$unitKerja->id}: ".$e->getMessage(), [
                'exception' => $e,
            ]);
        }
    }

    public function deleted(UnitKerja $unitKerja): void
    {
        try {
            $this->markFolderAsDeleted($unitKerja);
            Log::warning("⚠️ UnitKerja dihapus (soft delete): ID {$unitKerja->id}");
        } catch (Throwable $e) {
            Log::error("❌ Gagal menandai folder sebagai dihapus untuk UnitKerja ID {$unitKerja->id}: ".$e->getMessage(), [
                'exception' => $e,
            ]);
        }
    }

    public function restored(UnitKerja $unitKerja): void
    {
        try {
            $this->restoreFolderNameAndStyle($unitKerja);
            Log::notice("🔄 UnitKerja dipulihkan: ID {$unitKerja->id}");
        } catch (Throwable $e) {
            Log::error("❌ Gagal memulihkan folder UnitKerja ID {$unitKerja->id}: ".$e->getMessage(), [
                'exception' => $e,
            ]);
        }
    }

    public function forceDeleted(UnitKerja $unitKerja): void
    {
        Log::info("🗑️ UnitKerja dihapus permanen: ID {$unitKerja->id}. Folder tetap dipertahankan.");
    }

    // ==== Helper Methods ====

    protected function createAssociatedFolder(UnitKerja $unitKerja): void
    {
        $user = Auth::user();

        Folder::create([
            'name' => Str::slug($unitKerja->unit_name),
            'description' => "Media untuk Unit Kerja: {$unitKerja->unit_name}",
            'collection' => Str::slug($unitKerja->unit_name),
            'color' => null,
            'is_protected' => false,
            'is_hidden' => false,
            'is_favorite' => false,
            'is_public' => true,
            'has_user_access' => false,
            'model_type' => null,
            'model_id' => null,
            'user_id' => $user?->id ?? 1,
            'user_type' => $user ? get_class($user) : User::class,
        ]);
    }

    protected function updateAssociatedFolder(UnitKerja $unitKerja): void
    {
        $slug = Str::slug($unitKerja->unit_name);

        $folder = Folder::where('name', $slug)->first();

        if ($folder) {
            $folder->update([
                'name' => $slug,
                'description' => "Updated folder for Unit Kerja: {$unitKerja->unit_name}",
            ]);
        } else {
            Log::warning("⚠️ Folder tidak ditemukan saat update UnitKerja ID {$unitKerja->id}");
        }
    }

    protected function markFolderAsDeleted(UnitKerja $unitKerja): void
    {
        $slug = Str::slug($unitKerja->unit_name);

        $folder = Folder::where('name', $slug)->first();

        if ($folder && ! str_starts_with($folder->name, '[Dihapus]')) {
            $folder->update([
                'name' => '[Dihapus] '.$folder->name,
                'color' => 'gray',
            ]);
        } elseif (! $folder) {
            Log::warning("⚠️ Folder tidak ditemukan saat mencoba menandai sebagai dihapus untuk UnitKerja ID {$unitKerja->id}");
        }
    }

    protected function restoreFolderNameAndStyle(UnitKerja $unitKerja): void
    {
        $slug = '[Dihapus] '.Str::slug($unitKerja->unit_name);

        $folder = Folder::where('name', $slug)->first();

        if ($folder) {
            $folder->update([
                'name' => Str::slug($unitKerja->unit_name),
                'color' => null,
            ]);
        } else {
            Log::warning("⚠️ Folder tidak ditemukan saat mencoba memulihkan nama untuk UnitKerja ID {$unitKerja->id}");
        }
    }
}
