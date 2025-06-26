<?php

use App\Jobs\ProsesPenilaianImut;
use App\Models\ImutData;
use App\Models\ImutPenilaian;
use App\Models\ImutProfile;
use App\Models\LaporanImut;
use App\Models\UnitKerja;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->laporan = LaporanImut::factory()->create([
        'created_by' => $this->user->id,
    ]);
});

/**
 * Hanya ImutData aktif yang boleh dinilai.
 */
test('imut penilaian hanya dibuat untuk imut data yang aktif', function () {
    $unit = UnitKerja::factory()->create();
    $unit->users()->attach($this->user);
    $this->laporan->unitKerjas()->attach($unit);

    // ✅ Imut aktif
    $imutAktif = ImutData::factory()->create(['status' => true]);
    $unit->imutData()->attach($imutAktif);
    $profilAktif = ImutProfile::factory()->create([
        'imut_data_id' => $imutAktif->id,
        'version' => 1,
    ]);

    // ❌ Imut tidak aktif
    $imutNonAktif = ImutData::factory()->create(['status' => false]);
    $unit->imutData()->attach($imutNonAktif);
    $profilNonAktif = ImutProfile::factory()->create([
        'imut_data_id' => $imutNonAktif->id,
        'version' => 1,
    ]);

    (new ProsesPenilaianImut($this->laporan->id))->handle();

    $this->assertDatabaseHas(ImutPenilaian::class, [
        'imut_profil_id' => $profilAktif->id,
    ]);

    $this->assertDatabaseMissing(ImutPenilaian::class, [
        'imut_profil_id' => $profilNonAktif->id,
    ]);
});

/**
 * Jika laporan tidak memiliki unit kerja, tidak error
 */
test('job tidak error jika tidak ada unit kerja', function () {
    expect(fn() => (new ProsesPenilaianImut($this->laporan->id))->handle())
        ->not()->toThrow(Exception::class);

    $this->assertDatabaseCount(ImutPenilaian::class, 0);
});

/**
 * Imut aktif tanpa profil tidak diproses
 */
test('imut aktif tanpa profil tidak membuat penilaian', function () {
    $unit = UnitKerja::factory()->create();
    $this->laporan->unitKerjas()->attach($unit);

    $imut = ImutData::factory()->create(['status' => true]);
    $unit->imutData()->attach($imut);

    // Tidak buat ImutProfile

    (new ProsesPenilaianImut($this->laporan->id))->handle();

    $this->assertDatabaseCount(ImutPenilaian::class, 0);
});

/**
 * Jika ada beberapa profile, hanya profile dengan versi terbaru yang digunakan
 */
test('profile versi terbaru yang dipilih', function () {
    $unit = UnitKerja::factory()->create();
    $this->laporan->unitKerjas()->attach($unit);

    $imut = ImutData::factory()->create(['status' => true]);
    $unit->imutData()->attach($imut);

    ImutProfile::factory()->create([
        'imut_data_id' => $imut->id,
        'version' => 1,
    ]);

    $latest = ImutProfile::factory()->create([
        'imut_data_id' => $imut->id,
        'version' => 2,
    ]);

    (new ProsesPenilaianImut($this->laporan->id))->handle();

    $this->assertDatabaseHas(ImutPenilaian::class, [
        'imut_profil_id' => $latest->id,
    ]);

    $this->assertDatabaseCount(ImutPenilaian::class, 1);
});

/**
 * Campuran beberapa imut: hanya yang aktif dan punya profile yang diproses
 */
test('hanya imut aktif yang punya profile yang dinilai', function () {
    $unit = UnitKerja::factory()->create();
    $this->laporan->unitKerjas()->attach($unit);

    // ✅ Imut aktif & punya profil
    $imut1 = ImutData::factory()->create(['status' => true]);
    $unit->imutData()->attach($imut1);
    $profil1 = ImutProfile::factory()->create([
        'imut_data_id' => $imut1->id,
        'version' => 1,
    ]);

    // ❌ Imut aktif tanpa profil
    $imut2 = ImutData::factory()->create(['status' => true]);
    $unit->imutData()->attach($imut2);
    // tidak ada profile

    // ❌ Imut tidak aktif, tapi punya profil
    $imut3 = ImutData::factory()->create(['status' => false]);
    $unit->imutData()->attach($imut3);
    ImutProfile::factory()->create([
        'imut_data_id' => $imut3->id,
        'version' => 1,
    ]);

    (new ProsesPenilaianImut($this->laporan->id))->handle();

    $this->assertDatabaseHas(ImutPenilaian::class, [
        'imut_profil_id' => $profil1->id,
    ]);

    $this->assertDatabaseCount(ImutPenilaian::class, 1);
});
