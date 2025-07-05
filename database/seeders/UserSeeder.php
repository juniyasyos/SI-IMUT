<?php

namespace Database\Seeders;

use App\Models\Position;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
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
            'position_id' => Position::first()?->id,
        ]);

        $filePath = database_path('data/user.json');

        if (! File::exists($filePath)) {
            Log::warning('File "user.json" tidak ditemukan di folder database/data.');

            return;
        }

        $data = json_decode(File::get($filePath), true);

        if (json_last_error() !== \JSON_ERROR_NONE) {
            Log::error('Gagal mendecode file JSON: ' . json_last_error_msg());

            return;
        }

        $allPositions = Position::all();
        if ($allPositions->isEmpty()) {
            Log::warning('Tidak ada data posisi ditemukan di database.');

            return;
        }

        $usersToInsert = [];

        foreach ($data as $userData) {
            $rawName = $userData['nama'];

            // Hilangkan gelar akademik
            $cleanName = preg_replace('/,\s*(S\.Kep\.?|Ners|Amd\.? Kep|SKM|S\.?\.?T\.?|\w+\.)+/i', '', $rawName);
            $cleanName = trim($cleanName);

            // Generate email: nama tanpa spasi, lowercase
            $baseEmail = Str::lower(Str::slug($cleanName, '')) . '@example.com';

            // Pastikan email unik
            $email = $baseEmail;
            $suffix = 1;
            while (User::where('email', $email)->exists()) {
                $email = Str::lower(Str::slug($cleanName, '')) . $suffix++ . '@example.com';
            }

            $usersToInsert[] = [
                'nik' => $userData['id'],
                'name' => $cleanName,
                'place_of_birth' => $userData['tempat_lahir'],
                'date_of_birth' => $userData['tanggal_lahir'],
                'gender' => $userData['jenis_kelamin'],
                'address_ktp' => $userData['alamat'],
                'phone_number' => null,
                'email' => $email,
                'password' => Hash::make('Rsch123'),
                'status' => 'active',
                'position_id' => $allPositions->random()->id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (count($usersToInsert) > 0) {
            User::insert($usersToInsert);
            Log::info('Data pengguna berhasil dimasukkan ke dalam database.');

            $newUsers = User::where('nik', '!=', '0000.00000')->get();

            $unitKerjaRole = Role::where('name', 'unit_kerja')->first();

            if (! $unitKerjaRole) {
                Log::error('Role "unit_kerja" tidak ditemukan.');
                return;
            }

            foreach ($newUsers as $user) {
                $user->roles()->sync([$unitKerjaRole->id]);
            }
        } else {
            Log::warning('Tidak ada data pengguna untuk dimasukkan.');
        }
    }
}
