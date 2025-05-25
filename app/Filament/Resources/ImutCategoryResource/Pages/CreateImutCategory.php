<?php

namespace App\Filament\Resources\ImutCategoryResource\Pages;

use App\Filament\Resources\ImutCategoryResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateImutCategory extends CreateRecord
{
    protected static string $resource = ImutCategoryResource::class;
    protected static bool $canCreateAnother = false;

    // Customize redirect after create
    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    // public function mount(): void
    // {
    //     $this->authorizeAccess();

    //     $this->fillForm();

    //     $this->previousUrl = url()->previous();
    //     $user = Auth::user();
    //     dd($user->can('create_imut::category'));
    //     // dd(Auth::user()->getAllPermissions()->pluck('name'));
    // }
}
