<?php

namespace App\Repositories;

use App\Models\UnitKerja;
use App\Models\User;
use App\Repositories\Interfaces\UnitKerjaFolderRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Juniyasyos\FilamentMediaManager\Models\Folder;

class UnitKerjaFolderRepository implements UnitKerjaFolderRepositoryInterface
{
    public function createFolder(UnitKerja $unitKerja): void
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

    public function updateFolder(UnitKerja $unitKerja): void
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

    public function markFolderAsDeleted(UnitKerja $unitKerja): void
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

    public function restoreFolder(UnitKerja $unitKerja): void
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