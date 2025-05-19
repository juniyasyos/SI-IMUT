<?php

namespace App\Filament\Resources\LaporanImutResource\Pages;

use Filament\Actions;
use App\Models\ImutData;
use App\Models\ImutPenilaian;
use App\Models\LaporanUnitKerja;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\LaporanImutResource;
use Illuminate\Database\Eloquent\Builder;

class CreateLaporanImut extends CreateRecord
{
    protected static string $resource = LaporanImutResource::class;
    protected static bool $canCreateAnother = false;

    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
    {
        $record = $this->record;
        $unitKerjaIds = $record->unitKerjas()->pluck('unit_kerja_id')->toArray();

        $imutDataWithLatestProfile = ImutData::whereHas('categories', function (Builder $query) {
            $query->where('scope', 'global');
        })->with([
                    'latestProfile' => fn($query) => $query->latest('created_at')->limit(1),
                ])->get();

        foreach ($unitKerjaIds as $unitKerjaId) {
            $laporanUnitKerja = LaporanUnitKerja::firstOrCreate([
                'laporan_imut_id' => $record->id,
                'unit_kerja_id' => $unitKerjaId,
            ]);

            foreach ($imutDataWithLatestProfile as $imutData) {
                $latestProfile = $imutData->latestProfile;

                if (!$latestProfile) {
                    continue;
                }

                $imutStandard = $latestProfile->imutStandards()->latest('created_at')->first();

                if (!$imutStandard) {
                    continue;
                }

                ImutPenilaian::firstOrCreate([
                    'imut_profil_id' => $latestProfile->id,
                    'laporan_unit_kerja_id' => $laporanUnitKerja->id,
                    'imut_standar_id' => $imutStandard->id,
                ]);
            }
        }
    }


    public function getBreadcrumbs(): array
    {
        return [
            route('filament.admin.resources.laporan-imuts.index') => 'Laporan IMUT',
            null => 'Buat Laporan Baru',
        ];
    }
}
