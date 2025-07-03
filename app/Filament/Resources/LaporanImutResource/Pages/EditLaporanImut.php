<?php

namespace App\Filament\Resources\LaporanImutResource\Pages;

use App\Filament\Resources\LaporanImutResource;
use App\Jobs\ProsesPenilaianImut;
use App\Models\ImutPenilaian;
use App\Models\LaporanUnitKerja;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class EditLaporanImut extends EditRecord
{
    protected static string $resource = LaporanImutResource::class;

    protected array $originalUnitKerjaIds = [];

    protected function resolveRecord(int|string $key): \Illuminate\Database\Eloquent\Model
    {
        return \App\Models\LaporanImut::where('slug', $key)->firstOrFail();
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Simpan daftar unit_kerja_id sebelum update
        $this->originalUnitKerjaIds = $this->record->unitKerjas->pluck('id')->toArray();

        return $data;
    }

    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->record->slug]);
    }

    public function getBreadcrumbs(): array
    {
        return [
            route('filament.admin.resources.laporan-imuts.index') => 'Laporan IMUT',
            null => 'Edit: ' . $this->record->name,
        ];
    }

    protected function getHeaderActions(): array
    {
        $laporan = $this->record;

        return [
            Action::make('imutDataSummary')
                ->label('Summary IMUT Data')
                ->icon('heroicon-o-clipboard-document-list')
                ->color('primary')
                ->url(fn($record) => \App\Services\LaporanRedirectService::getRedirectUrlForImutData($record->id))
                ->disabled(fn($record) => is_null($record->imutPenilaians))
                ->visible(fn() => Gate::any([
                    'view_imut_data_report_laporan::imut',
                    'view_imut_data_report_detail_laporan::imut',
                ])),

            Action::make('unitKerjaSummary')
                ->label('Summary Unit Kerja')
                ->icon('heroicon-o-clipboard-document-list')
                ->color('success')
                ->url(fn($record) => \App\Services\LaporanRedirectService::getRedirectUrlForUnitKerja($record->id))

                ->visible(fn() => Gate::any([
                    'view_unit_kerja_report_laporan::imut',
                    'view_unit_kerja_report_detail_laporan::imut',
                ])),
        ];
    }

    // protected function afterSave(): void
    // {
    //     $currentUnitKerjaIds = $this->record->unitKerjas()->pluck('unit_kerja_id')->toArray();

    //     $removedUnitKerjaIds = array_diff($this->originalUnitKerjaIds, $currentUnitKerjaIds);
    //     $addedUnitKerjaIds = array_diff($currentUnitKerjaIds, $this->originalUnitKerjaIds);

    //     DB::transaction(function () use ($removedUnitKerjaIds, $addedUnitKerjaIds) {
    //         // 🔴 1. Hapus data dari unit kerja yang di-uncheck
    //         foreach ($removedUnitKerjaIds as $unitKerjaId) {
    //             $laporanUnitKerja = LaporanUnitKerja::where('laporan_imut_id', $this->record->id)
    //                 ->where('unit_kerja_id', $unitKerjaId)
    //                 ->first();

    //             if ($laporanUnitKerja) {
    //                 ImutPenilaian::where('laporan_unit_kerja_id', $laporanUnitKerja->id)->delete();
    //                 $laporanUnitKerja->delete();
    //             }

    //             $this->record->unitKerjas()->detach($unitKerjaId);
    //         }

    //         // ✅ 2. Tambah data penilaian dan laporan_unit_kerja untuk unit kerja baru
    //         $laporan = $this->record->load('unitKerjas.imutData.latestProfile');

    //         foreach ($laporan->unitKerjas as $unitKerja) {
    //             if (! in_array($unitKerja->id, $addedUnitKerjaIds)) {
    //                 continue; // Skip unit kerja yang tidak baru
    //             }

    //             $laporanUnitKerja = LaporanUnitKerja::firstOrCreate([
    //                 'laporan_imut_id' => $laporan->id,
    //                 'unit_kerja_id' => $unitKerja->id,
    //             ]);

    //             foreach ($unitKerja->imutData as $imutData) {
    //                 $latestProfile = $imutData->latestProfile;

    //                 if (! $latestProfile) {
    //                     continue;
    //                 }

    //                 ImutPenilaian::firstOrCreate([
    //                     'imut_profil_id' => $latestProfile->id,
    //                     'laporan_unit_kerja_id' => $laporanUnitKerja->id,
    //                 ]);
    //             }
    //         }
    //     });
    // }

    protected function afterSave(): void
    {
        $newUnitKerjaIds = $this->record->unitKerjas()->pluck('unit_kerja_id')->toArray();
        $removedUnitKerjaIds = array_diff($this->originalUnitKerjaIds, $newUnitKerjaIds);

        DB::transaction(function () use ($removedUnitKerjaIds) {
            foreach ($removedUnitKerjaIds as $unitKerjaId) {
                $laporanUnitKerja = LaporanUnitKerja::where('laporan_imut_id', $this->record->id)
                    ->where('unit_kerja_id', $unitKerjaId)
                    ->first();

                if ($laporanUnitKerja) {
                    ImutPenilaian::where('laporan_unit_kerja_id', $laporanUnitKerja->id)->delete();
                    $laporanUnitKerja->delete();
                }
            }
        });

        ProsesPenilaianImut::dispatch($this->record->id);
    }
}
