<?php

namespace App\Filament\Resources\ImutDataResource\Pages;

use App\Filament\Resources\ImutDataResource;
use App\Filament\Resources\ImutDataResource\Widgets\ImutDataUnitKerjaGrafikOverview;
use App\Models\ImutData;
use App\Models\UnitKerja;
use Filament\Resources\Pages\Page;

class ImutDataUnitKerjaOverview extends Page
{
    protected static string $resource = ImutDataResource::class;

    protected static string $view = 'filament.resources.imut-data-resource.pages.imut-data-unit-kerja-overview';

    public array $data = [];

    public ?ImutData $imutData = null;

    public ?UnitKerja $unitKerja = null;

    public function mount(): void
    {
        $imutDataId = request()->query('record_imut_data');
        $unitKerjaId = request()->query('record_unit_kerja');

        // Validasi dan load IMUT Data
        if (! $imutDataId) {
            abort(404, 'Slug Data IMUT tidak ditemukan.');
        }

        $imutData = ImutData::with(['profiles', 'categories'])->whereId($imutDataId)->first();

        if (! $imutData) {
            abort(404, 'Data IMUT tidak valid.');
        }

        // Validasi dan load Unit Kerja
        if (! $unitKerjaId) {
            abort(404, 'Slug Unit Kerja tidak ditemukan.');
        }

        $unitKerja = UnitKerja::whereId($unitKerjaId)->first();

        if (! $unitKerja) {
            abort(404, 'Unit Kerja tidak valid.');
        }

        // Simpan ke properti
        $this->imutData = $imutData;
        $this->unitKerja = $unitKerja;

        // Siapkan data untuk form/view
        $this->data = [
            'imutDataId' => $imutData->id,
            'title' => $imutData->title,
            'status' => $imutData->status,
            'kategori' => $imutData->categories?->name ?? '-',
            'jumlah_profil' => $imutData->profiles->count(),
            'unitKerjaId' => $unitKerja->id,
            'unitKerja' => $unitKerja->unit_name,
        ];
    }

    public function getTitle(): string
    {
        return 'Ikhtisar Data IMUT';
    }

    protected function getFormStatePath(): string
    {
        return 'data';
    }

    public function getBreadcrumbs(): array
    {
        return [
            ImutDataResource::getUrl('index') => 'Daftar Data IMUT',
            'Summary Grafik',
            ImutDataResource::getUrl('edit', ['record' => $this->imutData?->slug]) => $this->imutData?->title ?? 'Detail',
            'Ikhtisar',
        ];
    }

    public function getHeaderWidgets(): array
    {
        return [
            ImutDataUnitKerjaGrafikOverview::make(['imutData' => $this->imutData, 'unitKerja' => $this->unitKerja]),
        ];
    }
}
