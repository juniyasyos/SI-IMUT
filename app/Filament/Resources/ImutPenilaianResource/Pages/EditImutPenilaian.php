<?php

namespace App\Filament\Resources\ImutPenilaianResource\Pages;

use App\Filament\Resources\ImutPenilaianResource;
use App\Models\ImutPenilaian;
use App\Models\LaporanImut;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Juniyasyos\FilamentMediaManager\Models\Folder;

class EditImutPenilaian extends EditRecord
{
    protected static string $resource = ImutPenilaianResource::class;

    public ?Model $laporan = null;

    public ?Model $unitKerja = null;

    public ?Model $profile = null;

    public ?Model $imutData = null;

    protected function resolveRecord(int|string $key): Model
    {
        $laporanSlug = request()->route('laporanSlug');

        $this->laporan = LaporanImut::where('slug', $laporanSlug)->firstOrFail();

        $penilaian = ImutPenilaian::with(['profile.imutData', 'laporanUnitKerja.unitKerja'])
            ->whereHas('laporanUnitKerja', function ($query) {
                $query->where('laporan_imut_id', $this->laporan->id);
            })
            ->findOrFail($key);

        // Inisialisasi properti untuk digunakan di fungsi lain
        $this->profile = $penilaian->profile;
        $this->imutData = $penilaian->profile?->imutData;
        $this->unitKerja = $penilaian->laporanUnitKerja?->unitKerja;

        return $penilaian;
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $penilaian = $this->record;
        $profile = $penilaian->profile;
        $imutData = $profile?->imutData;
        $unitKerja = $penilaian->laporanUnitKerja?->unitKerja;
        $folder = Folder::where('collection', Str::slug($unitKerja->unit_name))->first();

        return array_merge([
            'penilaian_id' => $penilaian->id,
            'imut_profile_id' => $profile?->id,
            'imut_data_id' => $imutData?->id,
            'analysis' => $penilaian->analysis,
            'recommendations' => $penilaian->recommendations,
            'numerator_value' => $penilaian->numerator_value,
            'denominator_value' => $penilaian->denominator_value,
            // 'document_upload' => $penilaian->getMedia('documents')->pluck('uuid')->toArray(),
            'responsible_person' => $profile?->responsible_person,
            'indicator_type' => $profile?->indicator_type,
            'rationale' => $profile?->rationale,
            'objective' => $profile?->objective,
            'operational_definition' => $profile?->operational_definition,
            'quality_dimension' => $profile?->quality_dimension,
            'numerator_formula' => $profile?->numerator_formula,
            'denominator_formula' => $profile?->denominator_formula,
            'inclusion_criteria' => $profile?->inclusion_criteria,
            'exclusion_criteria' => $profile?->exclusion_criteria,
            'data_source' => $profile?->data_source,
            'data_collection_frequency' => $profile?->data_collection_frequency,
            'data_collection_method' => $profile?->data_collection_method,
            'sampling_method' => $profile?->sampling_method,
            'analysis_period_type' => $profile?->analysis_period_type,
            'analysis_period_value' => $profile?->analysis_period_value,
            'target_operator' => $profile?->target_operator,
            'target_value' => $profile?->target_value,
            'start_periode' => $profile?->start_periode,
            'end_periode' => $profile?->end_periode,
            'data_collection_tool' => $profile?->data_collection_tool,
            'analysis_plan' => $profile?->analysis_plan,
            'selected_collection' => $folder?->collection ?? 'default',
        ], $data);
    }

    // public function getRedirectUrl(): string
    // {
    //     return LaporanImutResource::getUrl('edit-penilaian', [
    //         'record' => $this->record->laporanUnitKerja->laporan->slug,
    //     ]);
    // }

    public function getBreadcrumbs(): array
    {
        $laporanName = $this->laporan?->name ?? 'Detail Laporan';
        $unitKerjaName = $this->unitKerja?->unit_name ?? 'Unit Kerja';
        $imutDataTitleShort = \Illuminate\Support\Str::limit($this->imutData?->title ?? 'Data IMUT', 35);
        $profileVersion = \Illuminate\Support\Str::limit($this->profile?->version ?? 'Versi Profil', 15);

        return [
            \App\Filament\Resources\LaporanImutResource::getUrl('index') => 'Daftar Laporan IMUT',
            \App\Filament\Resources\LaporanImutResource::getUrl('edit', ['record' => $this->laporan->slug]) => $laporanName,
            'Penilaian Laporan',
            \App\Filament\Resources\LaporanImutResource\Pages\UnitKerjaImutDataReport::getUrl([
                'laporan_id' => $this->laporan->id,
                'unit_kerja_id' => $this->unitKerja->id,
            ]) => $unitKerjaName,
            "{$imutDataTitleShort} | {$profileVersion}",
        ];
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function isLaporanPeriodClosed(): bool
    {
        return ! $this->isLaporanEditable();
    }

    public function isLaporanEditable(): bool
    {
        if (! $this->laporan) {
            return false;
        }

        $today = Carbon::today();
        $start = Carbon::parse($this->laporan->assessment_period_start);
        $end = Carbon::parse($this->laporan->assessment_period_end);

        return $today->betweenIncluded($start, $end);
    }
}
