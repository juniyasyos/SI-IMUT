<?php

namespace App\Filament\Resources\LaporanImutResource\Pages;

use Filament\Actions;
use App\Models\ImutData;
use App\Models\ImutPenilaian;
use App\Models\LaporanUnitKerja;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\LaporanImutResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class CreateLaporanImut extends CreateRecord
{
    protected static string $resource = LaporanImutResource::class;
    protected static bool $canCreateAnother = false;

    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = Auth::id();
        return $data;
    }


    protected function afterCreate(): void
    {
        Notification::make()
            ->title('Proses Penilaian Dimulai')
            ->body('Data sedang diproses di latar belakang...')
            ->status('info')
            ->send();

        dispatch(new \App\Jobs\ProsesPenilaianImut($this->record));
    }

    public function getBreadcrumbs(): array
    {
        return [
            route('filament.admin.resources.laporan-imuts.index') => 'Laporan IMUT',
            null => 'Buat Laporan Baru',
        ];
    }
}
