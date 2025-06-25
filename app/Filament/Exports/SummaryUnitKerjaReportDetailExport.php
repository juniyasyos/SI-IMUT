<?php

namespace App\Filament\Exports;

use App\Models\LaporanImut;
use App\Models\LaporanUnitKerja;
use App\Models\UnitKerja;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class SummaryUnitKerjaReportDetailExport extends Exporter
{
    protected static ?string $model = LaporanUnitKerja::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('imut_data')->label('Imut Data'),
            ExportColumn::make('imut_kategori')->label('Kategori IMUT'),
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
        $unitKerjaId = $export->options['unit_kerja_id'] ?? null;

        return LaporanUnitKerja::getReportByUnitKerjaDetails($laporanId, $unitKerjaId);
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $laporanId = $export->options['laporan_id'] ?? null;
        $unitKerjaId = $export->options['unit_kerja_id'] ?? null;

        $laporanName = LaporanImut::find($laporanId)?->title ?? 'Laporan';
        $unitKerjaName = UnitKerja::find($unitKerjaId)?->unit_name ?? 'Unit Kerja';

        $success = number_format($export->successful_rows);
        $fail = number_format($export->getFailedRowsCount());

        $body = "Export ringkasan data unit kerja *{$unitKerjaName}* dalam laporan *{$laporanName}* selesai. {$success} baris berhasil diekspor.";

        if ($fail > 0) {
            $body .= " Terdapat {$fail} baris yang gagal diekspor.";
        }

        return $body;
    }

    public function getFileName(Export $export): string
    {
        $laporanId = $this->export->options['laporan_id'] ?? null;
        $unitKerjaId = $this->export->options['unit_kerja_id'] ?? null;

        $laporanName = LaporanImut::find($laporanId)?->title ?? 'laporan';
        $unitKerjaName = UnitKerja::find($unitKerjaId)?->unit_name ?? 'unit';

        return 'ringkasan-unit-kerja-' .
            Str::slug($laporanName) . '-' .
            Str::slug($unitKerjaName) . '-' .
            now()->format('Ymd_His') . '.xlsx';
    }
}