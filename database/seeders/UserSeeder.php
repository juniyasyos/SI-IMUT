<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Position;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Seed the user's data into the database.
     */
    public function run(): void
    {
        User::factory()->create([
            'nik' => '0000.00000',
            'name' => 'admin',
            'email' => 'admin@admin.com',
            'password' => Hash::make('adminpassword'),
            'status' => 'active',
            'position_id' => Position::first()->id,
        ]);

        // Path ke file JSON
        $filePath = database_path('data/user.json');

        // Mengecek apakah file ada
        if (!File::exists($filePath)) {
            Log::warning('File "user.json" tidak ditemukan di folder database/data.');
            return;
        }

        // Membaca data dari file JSON
        $data = json_decode(File::get($filePath), true);

        // Mengecek apakah data berhasil di-decode
        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('Gagal mendecode file JSON: ' . json_last_error_msg());
            return;
        }

        // Mendapatkan semua posisi yang ada di database
        $allPositions = Position::all();

        // Mengecek jika tidak ada posisi dalam database
        if ($allPositions->isEmpty()) {
            Log::warning('Tidak ada data posisi ditemukan di database.');
            return;
        }

        foreach ($data as $userData) {
            $usersToInsert[] = [
                'nik' => $userData['id'],
                'name' => $userData['nama'],
                'place_of_birth' => $userData['tempat_lahir'],
                'date_of_birth' => $userData['tanggal_lahir'],
                'gender' => $userData['jenis_kelamin'],
                'address_ktp' => $userData['alamat'],
                'phone_number' => null,
                'email' => Str::lower(Str::replace(' ', '', $userData['nama'])) . '@example.com',
                'password' => Hash::make('Rsch123'),
                'status' => 'active',
                'position_id' => $allPositions->random()->id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Melakukan batch insert
        if (count($usersToInsert) > 0) {
            User::insert($usersToInsert);
            Log::info('Data pengguna berhasil disematkan ke dalam database.');
        } else {
            Log::warning('Tidak ada data pengguna untuk disematkan.');
        }
    }
}
