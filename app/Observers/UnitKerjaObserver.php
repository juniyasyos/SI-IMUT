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

            Folder::create([
                'name' => Str::slug($unitKerja->unit_name) . '-' . $unitKerja->id,
                'description' => 'Media untuk Unit Kerja: ' . $unitKerja->unit_name,
                'collection' => 'unitkerja',
                'color' => null, 
                'is_protected' => false,
                'is_hidden' => false,
                'is_favorite' => false,
                'is_public' => true,
                'has_user_access' => false,
                'model_type' => null,   
                'model_id' => null,     
                'user_id' => auth()->id(),
                'user_type' => get_class(auth()->user()),
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
