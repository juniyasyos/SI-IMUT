<?php

namespace App\Livewire;

use App\Filament\Exports\SummaryUnitKerjaReportDetailExport;
use App\Models\ImutCategory;
use App\Models\LaporanUnitKerja;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Number;
use Livewire\Component;

class ImutDataUnitKerjaReport extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public ?int $laporanId = null;

    public ?int $imutDataId = null;

    protected $listeners = [
        'report-changed' => 'updateReport',
    ];

    public function updateReport(int $laporanId, int $imutDataId): void
    {
        $this->laporanId = $laporanId;
        $this->imutDataId = $imutDataId;
        $this->dispatch('$refresh');
    }


    public function table(Table $table): Table
    {
        return $table
            ->query(fn() => LaporanUnitKerja::getReportByImutDataDetails($this->laporanId, $this->imutDataId))
            ->columns([
                TextColumn::make('unit_kerja')
                    ->label('Unit Kerja')
                    ->grow()
                    ->searchable(query: function (EloquentBuilder $query, string $search) {
                        $query->where('unit_kerja.unit_name', 'like', "%{$search}%");
                        // dd($query->toSql(), $query->getBindings(), $query->get());

                        return $query;
                    })
                    ->extraAttributes([
                        'style' => 'padding-right: 9rem;',
                    ]),

                TextColumn::make('imut_kategori')
                    ->label('Imut Kategori')
                    ->toggleable()
                    ->badge(),

                TextColumn::make('imut_profil')
                    ->label('Imut Profil')
                    ->width('20%')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),

                TextColumn::make('numerator_value')
                    ->label('N')
                    ->alignCenter()
                    ->toggleable()
                    ->formatStateUsing(fn($state) => Number::format($state, 2, locale: app()->getLocale()))
                    ->summarize(
                        Summarizer::make()
                            ->label('Total N')
                            ->using(fn(Builder $query) => number_format($query->sum('numerator_value'), 2))
                    ),

                TextColumn::make('denominator_value')
                    ->label('D')
                    ->alignCenter()
                    ->toggleable()
                    ->formatStateUsing(fn($state) => Number::format($state, 2, locale: app()->getLocale()))
                    ->summarize(
                        Summarizer::make()
                            ->label('Total D')
                            ->using(fn(Builder $query) => number_format($query->sum('denominator_value'), 2))
                    ),

                TextColumn::make('percentage')
                    ->label('Persentase (%)')
                    ->alignCenter()
                    ->toggleable()
                    ->suffix('%')
                    ->formatStateUsing(fn($state) => Number::format($state, 2, locale: app()->getLocale()))
                    ->color(fn($record) => match (true) {
                        ! is_numeric($record->percentage) || ! is_numeric($record->imut_standard) => null,

                        match ($record->imut_standard_type_operator) {
                            '=' => $record->percentage == $record->imut_standard,
                            '>=' => $record->percentage >= $record->imut_standard,
                            '<=' => $record->percentage <= $record->imut_standard,
                            '<' => $record->percentage < $record->imut_standard,
                            '>' => $record->percentage > $record->imut_standard,
                            default => false,
                        } => 'success',

                        match ($record->imut_standard_type_operator) {
                            '=' => $record->percentage == ($record->imut_standard * 0.8),
                            '>=' => $record->percentage >= ($record->imut_standard * 0.8),
                            '<=' => $record->percentage <= ($record->imut_standard * 1.2),
                            '<' => $record->percentage < ($record->imut_standard * 1.2),
                            '>' => $record->percentage > ($record->imut_standard * 0.8),
                            default => false,
                        } => 'warning',

                        default => 'danger',
                    })
                    ->summarize(
                        Summarizer::make()
                            ->label('Total Persentase')
                            ->using(function (Builder $query) {
                                $n = $query->sum('numerator_value');
                                $d = $query->sum('denominator_value');

                                return $d > 0 ? round(($n / $d) * 100, 2) : 0;
                            })
                            ->suffix('%')
                    ),

                TextColumn::make('imut_standard')
                    ->label('S (Imut Standar)')
                    ->suffix('%')
                    ->toggleable()
                    ->color('info')
                    ->badge()
                    ->alignCenter(),

                $this->makeSearchableColumn('analysis', 'Analisis', 'imut_penilaians.analysis'),
                // $this->makeSearchableColumn('document_upload', 'Dokumen Upload', 'imut_penilaians.document_upload'),
                $this->makeSearchableColumn('recommendations', 'Rekomendasi', 'imut_penilaians.recommendations'),
            ])
            ->headerActions([
                ExportAction::make()->exporter(SummaryUnitKerjaReportDetailExport::class)
            ])
            ->filters([
                SelectFilter::make('imut_kategori')
                    ->label('Imut Kategori')
                    ->options(
                        fn() => ImutCategory::query()
                            ->pluck('short_name', 'id')
                            ->toArray()
                    )
                    ->attribute('imut_kategori_id')
                    ->multiple()
                    ->placeholder('Semua Kategori'),
            ])
            ->actions([
                Action::make('edit_penilaian')
                    ->label('Edit Penilaian')
                    ->icon('heroicon-o-pencil-square')
                    ->color('info')
                    ->url(function ($record) {
                        $laporanSlug = \App\Models\LaporanImut::findOrFail($record->laporan_imut_id)->slug;

                        return \App\Filament\Resources\LaporanImutResource::getUrl('edit-penilaian', [
                            'laporanSlug' => $laporanSlug,
                            'record' => $record->id,
                        ]);
                    }),
            ])
            ->recordUrl(function ($record) {
                $laporanSlug = \App\Models\LaporanImut::findOrFail($record->laporan_imut_id)->slug;

                return \App\Filament\Resources\LaporanImutResource::getUrl('edit-penilaian', [
                    'laporanSlug' => $laporanSlug,
                    'record' => $record->id,
                ]);
            })
            ->bulkActions([
                // Define bulk actions here
            ]);
    }

    protected function makeSearchableColumn(string $name, string $label, string $dbColumn): TextColumn
    {
        return TextColumn::make($name)
            ->label($label)
            ->toggleable()
            ->searchable(
                query: fn(EloquentBuilder $query, string $search) => $query->where($dbColumn, 'like', "%{$search}%")
            );
    }

    public function render()
    {
        return view('livewire.imut-data-unit-kerja-report');
    }
}