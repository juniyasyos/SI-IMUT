<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\UnitKerja;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class UnitKerjaSeeder extends Seeder
{
    public function run(): void
    {
        $filePath = database_path('data/unit_kerja.json');

        if (!File::exists($filePath)) {
            Log::warning('File "unit_kerja.json" tidak ditemukan di folder database/data.');
            return;
        }

        $json = File::get($filePath);
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('Gagal mendecode file JSON: ' . json_last_error_msg());
            return;
        }

        foreach ($data as $item) {
            // Simpan unit kerja (hanya nama dan deskripsi)
            $unitKerja = UnitKerja::firstOrCreate([
                'unit_name' => $item['Unit Kerja'],
                'description' => $item['Deskripsi'],
            ]);

            $users = User::all();

            // Fuzzy match untuk Pengumpul
            $pengumpul = $users->sortByDesc(function ($user) use ($item) {
                similar_text(strtolower($user->name), strtolower($item['Pengumpul Data']), $percent);
                return $percent;
            })->first();

            // Fuzzy match juga untuk PIC
            $pic = $users->sortByDesc(function ($user) use ($item) {
                similar_text(strtolower($user->name), strtolower($item['PIC Indikator']), $percent);
                return $percent;
            })->first();


            // Relasikan jika ditemukan
            $userIds = collect([$pengumpul, $pic])
                ->filter() // buang null
                ->pluck('id')
                ->unique();

            if ($userIds->isNotEmpty()) {
                $unitKerja->users()->syncWithoutDetaching($userIds);
            }
        }
    }
}
