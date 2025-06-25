<?php

namespace App\Filament\Exports;

use App\Models\ImutData;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class ImutDataExporter extends Exporter
{
    protected static ?string $model = ImutData::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id'),
            ExportColumn::make('title'),
            ExportColumn::make('categories.category_name')->label('Nama Kategori'),
            ExportColumn::make('categories.short_name')->label('Nama Pendek Kategori'),
            ExportColumn::make('slug'),
            ExportColumn::make('creator.name')->label('Pembuat'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your imut data export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
