<?php

namespace App\Observers;

use App\Models\UnitKerja;
use TomatoPHP\FilamentMediaManager\Models\Folder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class UnitKerjaObserver
{
    /**
     * Handle the UnitKerja "created" event.
     */
    public function created(UnitKerja $unitKerja): void
    {
        try {
            $existingFolder = Folder::where('model_type', UnitKerja::class)
                ->where('model_id', $unitKerja->id)
                ->first();

            if ($existingFolder) {
                return;
            }

            // Buat folder
            Folder::create([
                'name' => Str::slug($unitKerja->unit_name) . '-' . $unitKerja->id,
                'description' => 'Media untuk Unit Kerja: ' . $unitKerja->unit_name,
                'model_type' => UnitKerja::class,
                'model_id' => $unitKerja->id,
                'collection' => 'unitkerja',
                'is_protected' => false,
                'is_hidden' => false,
                'is_favorite' => false,
                'is_public' => true,
            ]);
        } catch (\Throwable $e) {
            Log::error('Gagal membuat folder untuk UnitKerja ID: ' . $unitKerja->id, [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle the UnitKerja "updated" event.
     */
    public function updated(UnitKerja $unitKerja): void
    {
        //
    }

    /**
     * Handle the UnitKerja "deleted" event.
     */
    public function deleted(UnitKerja $unitKerja): void
    {
        //
    }

    /**
     * Handle the UnitKerja "restored" event.
     */
    public function restored(UnitKerja $unitKerja): void
    {
        //
    }

    /**
     * Handle the UnitKerja "force deleted" event.
     */
    public function forceDeleted(UnitKerja $unitKerja): void
    {
        //
    }
}
