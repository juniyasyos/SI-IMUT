<?php

namespace App\Observers;

use App\Models\UnitKerja;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Juniyasyos\FilamentMediaManager\Models\Folder;

class UnitKerjaObserver
{
    public function created(UnitKerja $unitKerja): void
    {
        $this->createAssociatedFolder($unitKerja);
        Log::info("UnitKerja created: {$unitKerja->id} - {$unitKerja->unit_name}");
    }

    public function updated(UnitKerja $unitKerja): void
    {
        $this->updateAssociatedFolder($unitKerja);
        Log::info("UnitKerja updated: {$unitKerja->id}");
    }

    public function deleted(UnitKerja $unitKerja): void
    {
        $this->deleteAssociatedFolder($unitKerja);
        Log::warning("UnitKerja soft-deleted: {$unitKerja->id}");
    }

    public function restored(UnitKerja $unitKerja): void
    {
        $this->restoreAssociatedFolder($unitKerja);
        Log::notice("UnitKerja restored: {$unitKerja->id}");
    }

    public function forceDeleted(UnitKerja $unitKerja): void
    {
        $this->forceDeleteAssociatedFolder($unitKerja);
        Log::error("UnitKerja permanently deleted: {$unitKerja->id}");
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
        $folder = $unitKerja->folder;
        if ($folder) {
            $folder->update([
                'name' => Str::slug($unitKerja->unit_name),
                'description' => "Updated folder for Unit Kerja: {$unitKerja->unit_name}",
            ]);
        }
    }

    protected function deleteAssociatedFolder(UnitKerja $unitKerja): void
    {
        $folder = $unitKerja->folder;
        if ($folder) {
            $folder->delete(); // Soft delete
        }
    }

    protected function restoreAssociatedFolder(UnitKerja $unitKerja): void
    {
        $folder = $unitKerja->folder()->withTrashed()->first();
        if ($folder && $folder->trashed()) {
            $folder->restore();
        }
    }

    protected function forceDeleteAssociatedFolder(UnitKerja $unitKerja): void
    {
        $folder = $unitKerja->folder()->withTrashed()->first();
        if ($folder) {
            $folder->forceDelete(); // Permanent delete
        }
    }
}