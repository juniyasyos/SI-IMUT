<?php

namespace App\Filament\Resources\ImutProfileResource\Pages;

use App\Models\ImutData;
use App\Models\ImutProfile;
use App\Filament\Resources\ImutProfileResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditImutProfile extends EditRecord
{
    protected static string $resource = ImutProfileResource::class;

    protected function resolveRecord(int|string $key): Model
    {
        $imutDataSlug = request()->route('imutDataSlug');

        $imutData = ImutData::where('slug', $imutDataSlug)->firstOrFail();

        return ImutProfile::where('imut_data_id', $imutData->id)
            ->where('slug', $key)
            ->firstOrFail();
    }

    public function getRedirectUrl(): string
    {

        return \App\Filament\Resources\ImutDataResource::getUrl('edit', [
            'record' => ImutData::where('id', $this->record->imut_data_id)->firstOrFail()->slug,
        ]);
    }

    public function getBreadcrumbs(): array
    {
        $imutDataSlug = request()->route('imutDataSlug');
        $imutData = ImutData::where('slug', $imutDataSlug)->first();

        $label = $imutData
            ? "{$imutData->title}"
            : 'Data Tidak Ditemukan';

        return [
            route('filament.admin.resources.imut-datas.index') => 'Imut Datas',
            $imutData
                ? route('filament.admin.resources.imut-datas.edit', ['record' => $imutData->slug])
                : '#' => $label,
            null => 'Edit Profile | ' . $this->record->version,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
