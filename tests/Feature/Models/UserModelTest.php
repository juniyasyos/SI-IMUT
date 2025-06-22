<?php

use App\Models\Position;
use App\Models\UnitKerja;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

describe('User Model', function () {

    it('has the correct fillable attributes', function () {
        $user = new User;

        expect($user->getFillable())->toMatchArray([
            'nik',
            'name',
            'place_of_birth',
            'date_of_birth',
            'gender',
            'address_ktp',
            'phone_number',
            'email',
            'password',
            'status',
            'position_id',
        ]);
    });

    it('hides sensitive attributes when serialized', function () {
        $user = User::factory()->make([
            'password' => 'secret',
            'remember_token' => 'some_token',
        ]);

        $array = $user->toArray();
        expect($array)->not()->toHaveKey('password');
        expect($array)->not()->toHaveKey('remember_token');
    });

    it('casts date_of_birth and email_verified_at properly', function () {
        $user = User::factory()->create([
            'date_of_birth' => '2000-01-01',
            'email_verified_at' => now(),
        ]);

        expect($user->date_of_birth)->toBeInstanceOf(\Carbon\Carbon::class);
        expect($user->email_verified_at)->toBeInstanceOf(\Carbon\Carbon::class);
    });

    it('returns capitalized status label', function () {
        $user = User::factory()->make([
            'status' => 'aktif',
        ]);

        expect($user->status_label)->toBe('Aktif');
    });

    it('has belongsTo relation with Position', function () {
        $position = Position::factory()->create();
        $user = User::factory()->create([
            'position_id' => $position->id,
        ]);

        expect($user->position)->toBeInstanceOf(Position::class);
    });

    it('has belongsToMany relation with UnitKerja', function () {
        $unit1 = UnitKerja::factory()->create();
        $unit2 = UnitKerja::factory()->create();

        $user = User::factory()->create();
        $user->unitKerjas()->attach([$unit1->id, $unit2->id]);

        expect($user->unitKerjas)->toHaveCount(2);
    });

    it('returns avatar url correctly if avatar is set', function () {
        Storage::fake('public');

        $user = User::factory()->create([
            'avatar_url' => 'avatars/user123.jpg',
        ]);

        $url = $user->getFilamentAvatarUrl();

        expect($url)->toContain('/storage/avatars/user123.jpg');
    });

    it('returns null avatar url if avatar not set', function () {
        $user = User::factory()->create([
            'avatar_url' => null,
        ]);

        expect($user->getFilamentAvatarUrl())->toBeNull();
    });

    it('can access filament panel', function () {
        $user = User::factory()->make();

        $mockPanel = Mockery::mock(\Filament\Panel::class);

        expect($user->canAccessPanel($mockPanel))->toBeTrue();
    });

    it('logs only dirty attributes (Spatie activitylog)', function () {
        $user = new User;
        $options = $user->getActivitylogOptions();

        expect($options->logOnlyDirty)->toBeTrue();
    });

    it('can assign and check roles and permissions', function () {
        $this->seed(\Database\Seeders\ShieldSeeder::class);
        
        $user = User::factory()->create();

        $user->assignRole('Tim Mutu');

        expect($user->hasRole('Tim Mutu'))->toBeTrue();
    });
});