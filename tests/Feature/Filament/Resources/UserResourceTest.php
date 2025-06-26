<?php

// use App\Filament\Resources\UserResource;
// use App\Models\User;
// use Filament\Facades\Filament;
// use Illuminate\Foundation\Testing\RefreshDatabase;
// use function Pest\Laravel\actingAs;
// use function Pest\Laravel\get;
// use function Pest\Laravel\artisan;

// uses(RefreshDatabase::class);

// beforeEach(function () {
//     artisan('migrate:fresh --seed');

//     Filament::setCurrentPanel(Filament::getPanel('admin'));

//     $admin = User::where('nik', '0000.00000')->first();

//     actingAs($admin);
// });

// it('can access the filament dashboard as admin user', function () {
//     get('/')->assertSuccessful();
// });

// it('can render user index page', function () {
//     get(UserResource::getUrl('index'))->assertSuccessful();
// });