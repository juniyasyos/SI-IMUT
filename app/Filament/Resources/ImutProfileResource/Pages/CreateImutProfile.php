<?php

namespace App\Filament\Resources\ImutProfileResource\Pages;

use App\Filament\Resources\ImutDataResource;
use Filament\Actions;
use App\Models\ImutData;
use Illuminate\Support\Facades\Auth;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\ImutProfileResource;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action as NotificationAction;


class CreateImutProfile extends CreateRecord
{
    protected static string $resource = ImutProfileResource::class;
    protected static bool $canCreateAnother = false;

    protected ?ImutData $imutData = null;

    public function mount(): void
    {
        parent::mount(); // Penting untuk inisialisasi form

        $imutDataSlug = request()->route('imutDataSlug');
        $this->imutData = ImutData::where('slug', $imutDataSlug)->firstOrFail();

        // dd($this->imutData);

        // Isi semua default value di form
        // $this->form->fill([
        //     'imut_data_id' => $this->imutData->id,
        //     'version' => 'Version 1.0',
        //     'rationale' => '',
        //     'quality_dimension' => '',
        //     'objective' => '',
        //     'operational_definition' => '',
        //     'indicator_type' => 'process',
        //     'numerator_formula' => '',
        //     'denominator_formula' => '',
        //     'inclusion_criteria' => '',
        //     'exclusion_criteria' => '',
        //     'data_source' => '',
        //     'data_collection_frequency' => '',
        //     'analysis_plan' => '',
        //     'target_operator' => '>=',
        //     'target_value' => 0,
        //     'analysis_period_type' => 'monthly',
        //     'analysis_period_value' => 1,
        //     'data_collection_method' => '',
        //     'sampling_method' => '',
        //     'data_collection_tool' => '',
        //     'responsible_person' => '',
        // ]);

        $this->form->fill([
            'imut_data_id' => $this->imutData->id,
            'version' => 'Version 1.0',
            'rationale' => 'Contoh alasan',
            'quality_dimension' => 'Efektivitas',
            'objective' => 'Meningkatkan mutu layanan',
            'operational_definition' => 'Definisi operasional dummy',
            'indicator_type' => 'process',
            'numerator_formula' => 'Jumlah pasien diterapi',
            'denominator_formula' => 'Jumlah pasien total',
            'inclusion_criteria' => 'Semua pasien rawat inap',
            'exclusion_criteria' => 'Pasien pulang paksa',
            'data_source' => 'SIRS',
            'data_collection_frequency' => 'Bulanan',
            'analysis_plan' => 'Analisis tren tiap bulan',
            'target_operator' => '>=',
            'target_value' => 90,
            'analysis_period_type' => 'monthly',
            'analysis_period_value' => 3,
            'data_collection_method' => 'Observasi langsung',
            'sampling_method' => 'Random sampling',
            'data_collection_tool' => 'Checklist mutu',
            'responsible_person' => 'dr. Contoh',
        ]);
    }

    protected function handleRecordCreation(array $data): \App\Models\ImutProfile
    {
        $record = parent::handleRecordCreation($data);

        // Ambil ulang ImutData berdasarkan id dari form (pasti ada)
        $imutData = ImutData::find($data['imut_data_id']);

        Notification::make()
            ->title('Profil berhasil dibuat')
            ->body("Profil IMUT '{$record->version}' berhasil ditambahkan.")
            ->success()
            // ->actions([
            //     NotificationAction::make('Lihat Profil')
            //         ->url(route('filament.admin.resources.imut-datas.edit-profile', [
            //             'imutDataSlug' => $imutData->slug,
            //             'record' => $record->id,
            //         ]))
            //         ->button(),
            // ])
            ->send();

        return $record;
    }

    public function getBreadcrumbs(): array
    {
        $imutData = $this->imutData;

        return [
            route('filament.admin.resources.imut-datas.index') => 'IMUT Data',
            $imutData ? route('filament.admin.resources.imut-datas.edit', ['record' => $imutData->slug]) : '#' => $imutData->title ?? 'Data Tidak Ditemukan',
            null => 'Create Profile',
        ];
    }

    public function getRedirectUrl(): string
    {
        $record = $this->record; // sudah tersedia di CreateRecord
        $imutData = ImutData::find($record->imut_data_id);

        return route('filament.admin.resources.imut-datas.edit-profile', [
            'imutDataSlug' => $imutData->slug,
            'record' => $record->slug,
        ]);
    }
}
