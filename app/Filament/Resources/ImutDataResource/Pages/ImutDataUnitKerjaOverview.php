<?php

namespace App\Filament\Resources\ImutDataResource\Pages;

use App\Filament\Resources\ImutDataResource;
use App\Filament\Resources\ImutDataResource\Widgets\ImutDataUnitKerjaGrafikOverview;
use App\Models\ImutData;
use App\Models\UnitKerja;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Auth;

class ImutDataUnitKerjaOverview extends Page
{
    protected static string $resource = ImutDataResource::class;

    protected static string $view = 'filament.resources.imut-data-resource.pages.imut-data-unit-kerja-overview';

    public array $data = [];

    public ?ImutData $imutData = null;

    public ?UnitKerja $unitKerja = null;

    public static function canAccess(array $parameters = []): bool
    {
        $user = Auth::user();

        $unitKerjaId = $parameters['record_unit_kerja'] ?? request('record_unit_kerja');

        if (! $unitKerjaId) {
            return false;
        }

        // Izin global
        if ($user->can('view_all_data_imut::data')) {
            return true;
        }

        // Izin terbatas berdasarkan unit kerja
        if ($user->can('view_by_unit_kerja_imut::data')) {
            return $user->unitKerjas()->where('unit_kerja_id', $unitKerjaId)->exists();
        }

        return false;
    }

    public function mount(): void
    {
        $imutDataId = request()->query('record_imut_data');
        $unitKerjaId = request()->query('record_unit_kerja');

        $this->imutData = ImutData::with(['profiles', 'categories'])->findOrFail($imutDataId);
        $this->unitKerja = UnitKerja::findOrFail($unitKerjaId);

        $this->data = [
            'imutDataId' => $this->imutData->id,
            'title' => $this->imutData->title,
            'status' => $this->imutData->status,
            'kategori' => $this->imutData->categories?->name ?? '-',
            'jumlah_profil' => $this->imutData->profiles->count(),
            'unitKerjaId' => $this->unitKerja->id,
            'unitKerja' => $this->unitKerja->unit_name,
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
            ImutDataUnitKerjaGrafikOverview::make([
                'imutData' => $this->imutData,
                'unitKerja' => $this->unitKerja,
            ]),
        ];
    }
}
