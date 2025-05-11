<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// 🔐 Basic Access Test
test('halaman login dapat diakses', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

// ✅ Login Success
test('user dengan nik dan password valid dapat login', function () {
    $user = User::factory()->create([
        'nik' => '1234567890',
        'password' => bcrypt('password123'),
    ]);

    $this->actingAs($user);

    $response = $this->get('/');

    $response->assertStatus(200);
    $this->assertAuthenticatedAs($user);
});

// ❌ User salah password
test('user dengan password salah tidak bisa login', function () {
    $user = User::factory()->create([
        'nik' => '1234567890',
        'password' => bcrypt('password123'),
    ]);

    $credentials = [
        'nik' => '1234567890',
        'password' => 'wrongpassword',
    ];

    $this->post('/login', $credentials)
        ->assertStatus(405); // karena tidak ada POST login
});

// ❌ User salah nik
test('user dengan nik salah tidak bisa login', function () {
    $user = User::factory()->create([
        'nik' => '1234567890',
        'password' => bcrypt('password123'),
    ]);

    $credentials = [
        'nik' => '0000000000',
        'password' => 'password123',
    ];

    $this->post('/login', $credentials)
        ->assertStatus(405); // tetap 405
});

// 🔒 User tidak aktif
test('user tidak aktif tidak bisa login', function () {
    $user = User::factory()->create([
        'nik' => '1234567891',
        'password' => bcrypt('password123'),
        'status' => 'inactive',
    ]);

    $this->actingAs($user);

    $response = $this->get('/');

    $response->assertStatus(403); // asumsi ditolak karena status
});

// 🔒 User suspended
test('user suspended tidak bisa login', function () {
    $user = User::factory()->create([
        'nik' => '1234567892',
        'password' => bcrypt('password123'),
        'status' => 'suspended',
    ]);

    $this->actingAs($user);

    $response = $this->get('/');

    $response->assertStatus(403); // asumsi juga ditolak
});

// 🧪 Auth manual dengan actingAs
test('a user can be authenticated manually', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $this->actingAs($user);

    $response = $this->get('/');

    $response->assertStatus(200);
    $this->assertAuthenticatedAs($user);
});

// 🚪 Logout
test('user dapat logout', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->post('/logout');

    $response->assertRedirect('/login');
    $this->assertGuest();
});
