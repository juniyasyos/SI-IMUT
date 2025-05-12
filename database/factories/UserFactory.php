<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\Position;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Data Pribadi
            'nik' => fake()->unique()->numerify('###########'),  // Generasi NIK
            'name' => fake()->name(),
            'place_of_birth' => fake()->city(),
            'date_of_birth' => fake()->date(),
            'gender' => fake()->randomElement(['Laki-laki', 'Perempuan']),

            // Kontak & Alamat
            'address_ktp' => fake()->address(),
            'phone_number' => fake()->phoneNumber(),
            'email' => fake()->unique()->safeEmail(),

            // Keamanan & Status
            'password' => static::$password ??= Hash::make('password'),
            'email_verified_at' => now(),
            'status' => 'active',
            'remember_token' => Str::random(10),

            // Relasi
            'position_id' => Position::inRandomOrder()->first()?->id,  // Mengambil posisi secara acak
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn(array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
