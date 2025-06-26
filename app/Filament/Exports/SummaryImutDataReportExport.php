<?php

namespace App\Filament\Exports;

use App\Models\LaporanUnitKerja;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Database\Eloquent\Builder;

class SummaryImutDataReportExport extends Exporter
{
    protected static ?string $model = LaporanUnitKerja::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('imut_data_title')
                ->label('Unit Kerja'),

            ExportColumn::make('total_numerator')
                ->label('Total N'),

            ExportColumn::make('total_denominator')
                ->label('Total D'),

            ExportColumn::make('percentage')
                ->label('Persentase (%)'),
        ];
    }

    // Override query untuk pakai custom query
    public static function getEloquentQuery(Export $export): Builder
    {
        $laporanId = $export->options['laporan_id'] ?? null;

        return LaporanUnitKerja::getReportByImutData($laporanId);
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Export summary laporan imut data selesai. ' . number_format($export->successful_rows) . ' ' . str('baris')->plural($export->successful_rows) . ' berhasil diekspor.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('baris')->plural($failedRowsCount) . ' gagal diekspor.';
        }

        return $body;
    }
}
