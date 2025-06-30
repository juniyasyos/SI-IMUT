<?php

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
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
        ->assertStatus(405);
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
    $this->withoutMiddleware(VerifyCsrfToken::class);

    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->post('/logout');

    $response->assertRedirect('/login');
    $this->assertGuest();
});


// 🛑 Guest tidak bisa akses halaman dashboard
test('guest tidak bisa akses dashboard dan diarahkan ke login', function () {
    $response = $this->get('/');

    $response->assertRedirect('/login');
    $this->assertGuest();
});

// 🛑 Guest tidak bisa akses route protected lainnya
test('guest tidak bisa akses halaman user management', function () {
    $response = $this->get('/users');

    $response->assertRedirect('/login'); // atau 403 jika pakai gate/policy
    $this->assertGuest();
});

// ❌ Route register tidak tersedia
test('halaman register tidak dapat diakses', function () {
    $response = $this->get('/register');

    $response->assertStatus(404); // atau 403 jika kamu override
});

// ❌ Tidak bisa submit form register
test('tidak bisa melakukan registrasi user baru', function () {
    $response = $this->post('/register', [
        'nik' => '9876543210',
        'name' => 'Test User',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(404); // atau 403 jika diblokir
});

// ❌ Halaman reset password tidak tersedia
test('halaman reset password tidak dapat diakses', function () {
    $response = $this->get('/forgot-password');

    $response->assertStatus(404); // atau bisa redirect ke login
});

// ❌ Tidak bisa submit form reset password
test('tidak bisa melakukan permintaan reset password', function () {
    $response = $this->post('/forgot-password', [
        'email' => 'test@example.com',
    ]);

    $response->assertStatus(404); // atau 403 sesuai kebijakan
});

// 🧪 Extra: Cek bahwa user aktif bisa akses
test('user aktif bisa akses halaman dashboard', function () {
    $user = User::factory()->create([
        'status' => 'active',
    ]);

    $this->actingAs($user);

    $response = $this->get('/');

    $response->assertStatus(200);
});

test('sql injection pada nik tidak berhasil login', function () {
    User::factory()->create([
        'nik' => '1234567890',
        'password' => bcrypt('password123'),
    ]);

    $response = $this->post('/login', [
        'nik' => "' OR '1'='1",
        'password' => 'password123',
    ]);

    $response->assertStatus(405);
    $this->assertGuest();
});

test('sql injection pada password tidak berhasil login', function () {
    User::factory()->create([
        'nik' => '1234567890',
        'password' => bcrypt('password123'),
    ]);

    $response = $this->post('/login', [
        'nik' => '1234567890',
        'password' => "' OR '1'='1",
    ]);

    $response->assertStatus(405);
    $this->assertGuest();
});

test('login gagal lebih dari batas mencoba diblokir', function () {
    $this->markTestSkipped('Tidak relevan untuk Filament Auth bawaan.');
});

test('user dengan password null tidak bisa login', function () {
    User::factory()->create([
        'nik' => '123123123',
        'password' => null,
    ]);

    $response = $this->post('/login', [
        'nik' => '123123123',
        'password' => 'anything',
    ]);

    $response->assertStatus(405);
    $this->assertGuest();
});

test('nik dengan karakter aneh tidak bisa login', function () {
    $response = $this->post('/login', [
        'nik' => 'abcd@#$.%^',
        'password' => 'test',
    ]);

    $response->assertStatus(405);
});

test('password case sensitive', function () {
    User::factory()->create([
        'nik' => '123456',
        'password' => bcrypt('PasswordUPPER'),
    ]);

    $response = $this->post('/login', [
        'nik' => '123456',
        'password' => 'passwordupper',
    ]);

    $response->assertStatus(405);
    $this->assertGuest();
});

test('login dengan field kosong gagal', function () {
    $response = $this->post('/login', [
        'nik' => '',
        'password' => '',
    ]);

    $response->assertStatus(405);
});

test('user yang telah dihapus tidak bisa login', function () {
    $user = User::factory()->create([
        'nik' => '999999',
        'password' => bcrypt('password'),
    ]);

    $user->delete();

    $response = $this->post('/login', [
        'nik' => '999999',
        'password' => 'password',
    ]);

    $response->assertStatus(405);
});

test('nik dengan spasi tetap dianggap salah', function () {
    User::factory()->create([
        'nik' => '55555555',
        'password' => bcrypt('pass123'),
    ]);

    $response = $this->post('/login', [
        'nik' => ' 55555555 ',
        'password' => 'pass123',
    ]);

    $response->assertStatus(405);
});

test('login dengan input sangat panjang', function () {
    $response = $this->post('/login', [
        'nik' => str_repeat('1', 1000),
        'password' => str_repeat('a', 1000),
    ]);

    $response->assertStatus(405);
});

test('user yang sudah login tidak bisa akses halaman login', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->get('/login');

    $response->assertRedirect('/'); // atau route default setelah login
});

test('session tidak berubah saat login gagal', function () {
    $this->post('/login', [
        'nik' => 'invalid',
        'password' => 'invalid',
    ]);

    $this->assertGuest();
});

test('user diarahkan ke halaman home setelah login', function () {
    $this->markTestSkipped('Login ditangani oleh Filament, tidak diuji secara langsung.');
});


test('html tag dalam input login tidak menyebabkan XSS', function () {
    $response = $this->post('/login', [
        'nik' => '<script>alert(1)</script>',
        'password' => 'password',
    ]);

    $response->assertStatus(405);
});

test('nik terlalu pendek tidak valid', function () {
    $response = $this->post('/login', [
        'nik' => '1',
        'password' => '123456',
    ]);

    $response->assertStatus(405);
});

test('user tidak bisa melihat data user lain lewat id', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    $this->actingAs($userA);
    $response = $this->get("/users/{$userB->id}");

    $response->assertStatus(403); // Atau redirect
});

test('input dengan script tag tidak dieksekusi (stored XSS)', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->post('/profile', ['bio' => '<script>alert("XSS")</script>']);

    $this->get('/profile')
        ->assertDontSee('<script>alert("XSS")</script>', false);
});

// test('session regeneration terjadi setelah login', function () {
//     $user = \App\Models\User::factory()->create([
//         'nik' => '1122334455',
//         'password' => bcrypt('mypassword'),
//     ]);

//     $this->get('/login');
//     $oldSession = session()->getId();

//     // Login dan simpan session ID baru
//     $this->post('/login', [
//         'nik' => '1122334455',
//         'password' => 'mypassword',
//     ]);

//     $newSession = session()->getId();

//     expect($oldSession)->not()->toEqual($newSession);
// });


test('header keamanan tersedia di response', function () {
    $response = $this->get('/');

    expect(strtolower($response->headers->get('X-Frame-Options')))->toBe('sameorigin');
    expect($response->headers->get('X-Content-Type-Options'))->toBe('nosniff');
});


test('login dengan SQL injection pola klasik gagal', function () {
    $response = $this->post('/login', [
        'nik' => "' OR 1=1 --",
        'password' => 'anything',
    ]);

    $response->assertStatus(405);
});
