<?php

use App\Models\UnitKerja;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('unit kerja factory works', function () {
    $unitKerja = UnitKerja::factory()->create();

    $this->assertDatabaseHas('unit_kerja', [
        'id' => $unitKerja->id,
        'unit_name' => $unitKerja->unit_name,
    ]);
});