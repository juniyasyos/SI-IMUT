<?php

namespace App\Filament\Resources;

use App\Filament\Exports\LaporanImutExporter;
use App\Filament\Resources\LaporanImutResource\Pages;
use App\Filament\Resources\LaporanImutResource\Pages\ImutDataReport;
use App\Filament\Resources\LaporanImutResource\Pages\ImutDataUnitKerjaReport;
use App\Filament\Resources\LaporanImutResource\Pages\UnitKerjaImutDataReport;
use App\Filament\Resources\LaporanImutResource\Pages\UnitKerjaReport;
use App\Filament\Resources\LaporanImutResource\Schema\LaporanImutSchema;
use App\Filament\Resources\LaporanImutResource\Table\LaporanImutTable;
use App\Models\ImutPenilaian;
use App\Models\LaporanImut;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LaporanImutResource extends Resource implements HasShieldPermissions
{
    use \App\Traits\HasActiveIcon;

    protected static ?string $model = LaporanImut::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationLabel = 'Laporan IMUT';

    protected static ?string $modelLabel = 'Laporan IMUT';

    public static function getGloballySearchableAttributes(): array
    {
        return ['assessment_period'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Periode Asesmen' => $record->assessment_period,
        ];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return "Laporan {$record->assessment_period}";
    }

    public static function getGlobalSearchResultUrl(Model $record): ?string
    {
        return static::getUrl(name: 'edit', parameters: ['record' => $record]);
    }

    public static function getPermissionPrefixes(): array
    {
        return array_merge([
            // default Filament Shield permissions
            'view',
            'view_any',
            'create',
            'update',
            'restore',
            'restore_any',
            'replicate',
            'reorder',
            'delete',
            'delete_any',
            'force_delete',
            'force_delete_any',

            // custom laporan report
            'view_unit_kerja_report',
            'view_unit_kerja_report_detail',
            'view_imut_data_report',
            'view_imut_data_report_detail',
        ]);
    }

    public static function getLabel(): ?string
    {
        return __('Laporan IMUT');
    }

    public static function getPluralLabel(): ?string
    {
        return __('Daftar Laporan IMUT');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('filament-forms::imut-data.navigation.group');
    }

    public static function form(Form $form): Form
    {
        return $form->schema(LaporanImutSchema::make());
    }

    // ===================== Table Start Component =======================
    public static function table(Table $table): Table
    {
        return $table
            ->columns(LaporanImutTable::columns())
            ->filters([
                TrashedFilter::make()
                    ->default('with'),
            ])
            ->headerActions([
                ExportAction::make()->exporter(LaporanImutExporter::class)
            ])
            ->actions(LaporanImutTable::actions())
            ->bulkActions(
                [
                    Tables\Actions\BulkActionGroup::make([
                        Tables\Actions\RestoreBulkAction::make(),
                        Tables\Actions\ForceDeleteBulkAction::make(),
                    ]),
                    Tables\Actions\DeleteBulkAction::make(),
                ]
            );
    }

    protected static function getBulkActions(): array
    {
        return [
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\RestoreBulkAction::make()
                    ->visible(fn(LaporanImut $record) => method_exists($record, 'trashed') && $record->trashed()),
                Tables\Actions\ForceDeleteBulkAction::make()
                    ->visible(fn(LaporanImut $record) => method_exists($record, 'trashed') && $record->trashed()),
            ]),
            Tables\Actions\DeleteBulkAction::make(),
        ];
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLaporanImuts::route('/'),
            'create' => Pages\CreateLaporanImut::route('/create'),
            'edit' => Pages\EditLaporanImut::route('/{record:slug}/edit'),
            'unit-kerja-report' => UnitKerjaReport::route('/unit-kerja-report'),
            'unit-kerja-imut-data-report-detail' => UnitKerjaImutDataReport::route('/unit-kerja-imut-data-report'),
            'imut-data-report' => ImutDataReport::route('/imut-data-report'),
            'imut-data-unit-kerja-report-detail' => ImutDataUnitKerjaReport::route('/imut-data-unit-kerja-report'),
            // 'edit-penilaian' => Pages\PenilaianLaporan::route('/penilaian'),
            'edit-penilaian' => \App\Filament\Resources\ImutPenilaianResource\Pages\EditImutPenilaian::route('/{laporanSlug}/penilaian-edit={record}'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->orderByDesc('assessment_period_start');
    }
}