<?php

namespace App\Filament\Resources\ImutDataResource\Pages;

use App\Filament\Resources\ImutDataResource;
use App\Models\ImutData;
use App\Models\ImutDataUnitKerja;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CreateImutData extends CreateRecord
{
    protected static string $resource = ImutDataResource::class;
    protected static bool $canCreateAnother = false;

    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', [
            'record' => $this->record->slug,
        ]);
    }


    /**
     * @param array<string, mixed> $data
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function handleRecordCreation(array $data): Model
    {
        $record = ImutData::create($data);

        $user = Auth::user();

        $unitKerjaIds = $user->can('attach_imut_data_to_unit_kerja_unit::kerja')
            ? ($data['unitKerjaIds'] ?? [])
            : $user->unitKerjas()->pluck('unit_kerja.id')->toArray();

        foreach ($unitKerjaIds as $unitKerjaId) {
            \App\Models\ImutDataUnitKerja::firstOrCreate([
                'imut_data_id' => $record->id,
                'unit_kerja_id' => $unitKerjaId,
            ], [
                'assigned_by' => $user->id,
                'assigned_at' => now(),
            ]);
        }

        // dd([
        //     'user' => $user,
        //     'record' => $record,
        //     'attach' => $unitKerjaIds,
        //     'unit_kerja_pivot' => $record->unitKerja,
        //     'cek' => ImutDataUnitKerja::where('imut_data_id', $record->id)
        //         ->whereIn('unit_kerja_id', $unitKerjaIds)
        //         ->get(),
        // ]);

        return $record;
    }
}