<?php

namespace App\Filament\Resources\ImutProfileResource\Pages;

use Filament\Actions;
use App\Models\ImutData;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\ImutProfileResource;

class CreateImutProfile extends CreateRecord
{
    protected static string $resource = ImutProfileResource::class;
    protected static bool $canCreateAnother = false;

    protected ImutData $imutData;

    public function mount(): void
    {
        $imutDataSlug = request()->route('imutDataSlug');
        $this->imutData = ImutData::where('slug', $imutDataSlug)->firstOrFail();
    }

    public function getBreadcrumbs(): array
    {
        $imutData = $this->imutData;

        return [
            route('filament.admin.resources.imut-datas.index') => __('IMUT Data'),
            $imutData ? route('filament.admin.resources.imut-datas.edit', ['record' => $imutData->slug]) : '#' => $imutData->title ?? 'Data Tidak Ditemukan',
            null => __('Create Profile'),
        ];
    }

    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit-profile', [
            'imutDataSlug' => $this->imutData->slug,
            'record' => $this->imutData->slug,
        ]);
    }
}
