<?php

namespace App\Filament\Exports;

use App\Models\ImutData;
use App\Models\LaporanImut;
use App\Models\LaporanUnitKerja;
use App\Models\UnitKerja;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class SummaryImutDataReportDetailExport extends Exporter
{
    protected static ?string $model = LaporanUnitKerja::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('unit_kerja')->label('Imut Data'),
            ExportColumn::make('imut_kategori')->label('Kategori IMUT'),
            ExportColumn::make('imut_profil')->label('Profil IMUT'),
            ExportColumn::make('numerator_value')->label('N'),
            ExportColumn::make('denominator_value')->label('D'),
            ExportColumn::make('percentage')->label('Persentase (%)'),
            ExportColumn::make('imut_standard')->label('Standar IMUT'),
            ExportColumn::make('analysis')->label('Analisis'),
            ExportColumn::make('recommendations')->label('Rekomendasi'),
        ];
    }

    public static function getEloquentQuery(Export $export): Builder
    {
        $laporanId = $export->options['laporan_id'] ?? null;
        $imutDataId = $export->options['imut_data_id'] ?? null;

        return LaporanUnitKerja::getReportByImutDataDetails($laporanId, $imutDataId);
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $laporanId = $export->options['laporan_id'] ?? null;
        $imutDataId = $export->options['imut_data_id'] ?? null;

        $laporanName = LaporanImut::find($laporanId)?->title ?? 'Laporan';
        $ImutdataTitle = ImutData::find($imutDataId)?->unit_name ?? 'Imut Data';

        $success = number_format($export->successful_rows);
        $fail = number_format($export->getFailedRowsCount());

        $body = "Export data detail IMUT untuk *{$ImutdataTitle}* dalam laporan *{$laporanName}* telah selesai. {$success} baris berhasil diekspor.";

        if ($fail > 0) {
            $body .= " Namun, terdapat {$fail} baris yang gagal diekspor.";
        }

        return $body;
    }

    public function getFileName(Export $export): string
    {
        $laporanId = $this->export->options['laporan_id'] ?? null;
        $imutDataId = $export->options['imut_data_id'] ?? null;

        $laporanName = LaporanImut::find($laporanId)?->slug ?? 'laporan';
        $imutData = ImutData::find($imutDataId)?->slug ?? 'imut-data';

        return 'export-imut-detail-' .
            $laporanName . '-' .
            $imutData . '-' .
            now()->format('Ymd_His');
    }
}
