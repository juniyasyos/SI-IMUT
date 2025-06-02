<?php

namespace App\Filament\Resources\LaporanImutResource\Pages;

use Filament\Actions\Action;
use Illuminate\Support\Facades\Gate;
use App\Filament\Resources\LaporanImutResource\Pages\Reports\UnitKerjaReport;
use App\Filament\Resources\LaporanImutResource\Pages\Reports\ImutDataReport;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\LaporanImutResource;

class EditLaporanImut extends EditRecord
{
    protected static string $resource = LaporanImutResource::class;

    protected function resolveRecord(int|string $key): \Illuminate\Database\Eloquent\Model
    {
        return \App\Models\LaporanImut::where('slug', $key)->firstOrFail();
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
                ->openUrlInNewTab()
                ->visible(fn() => Gate::any([
                    'view_imut_data_report_laporan::imut',
                    'view_imut_data_report_detail_laporan::imut',
                ])),

            Action::make('unitKerjaSummary')
                ->label('Summary Unit Kerja')
                ->icon('heroicon-o-clipboard-document-list')
                ->color('success')
                ->url(fn($record) => \App\Services\LaporanRedirectService::getRedirectUrlForUnitKerja($record->id))
                ->openUrlInNewTab()
                ->visible(fn() => Gate::any([
                    'view_unit_kerja_report_laporan::imut',
                    'view_unit_kerja_report_detail_laporan::imut',
                ]))
        ];
    }
}
