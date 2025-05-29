<?php

namespace App\Livewire;

use Filament\Tables\Actions\Action;
use Livewire\Component;
use App\Models\UnitKerja;
use Filament\Tables\Table;
use App\Models\ImutCategory;
use Illuminate\Support\Number;
use App\Models\LaporanUnitKerja;
use Pest\Arch\Options\LayerOptions;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Database\Query\Builder;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Columns\TextInputColumn;
use Illuminate\Database\Eloquent\Collection;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

class UnitKerjaImutDataReport extends Component implements HasTable, HasForms
{
    use InteractsWithForms;
    use InteractsWithTable;

    public ?int $laporanId = null;
    public ?int $unitKerjaId = null;

    protected $listeners = [
        'report-changed' => 'updateReport',
    ];

    public function updateReport(int $laporanId, int $unitKerjaId): void
    {


        $this->laporanId = $laporanId;
        $this->unitKerjaId = $unitKerjaId;
        $this->dispatch('$refresh');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn() => LaporanUnitKerja::getReportByUnitKerjaDetails($this->laporanId, $this->unitKerjaId))
            ->columns([
                TextColumn::make('imut_data')
                    ->label('Imut Data')
                    ->searchable(query: fn(EloquentBuilder $query, string $search) => $query->where('imut_data.title', 'like', "%{$search}%")),

                TextColumn::make('imut_kategori')
                    ->label('Imut Kategori')
                    ->toggleable()
                    ->alignCenter()
                    ->badge(),

                TextColumn::make('imut_profil')
                    ->label('Imut Profil')
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
                        !is_numeric($record->percentage) || !is_numeric($record->imut_standard) => null,

                        match ($record->imut_standard_type_operator) {
                            '='  => $record->percentage == $record->imut_standard,
                            '>=' => $record->percentage >= $record->imut_standard,
                            '<=' => $record->percentage <= $record->imut_standard,
                            '<'  => $record->percentage < $record->imut_standard,
                            '>'  => $record->percentage > $record->imut_standard,
                            default => false,
                        } => 'success',

                        match ($record->imut_standard_type_operator) {
                            '='  => $record->percentage == ($record->imut_standard * 0.8),
                            '>=' => $record->percentage >= ($record->imut_standard * 0.8),
                            '<=' => $record->percentage <= ($record->imut_standard * 1.2),
                            '<'  => $record->percentage <  ($record->imut_standard * 1.2),
                            '>'  => $record->percentage >  ($record->imut_standard * 0.8),
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
                // ->summarize(Summarizer::make()
                //     ->label('Standar Min')
                //     ->suffix('%')
                //     ->using(fn(Builder $query) => $query->min('standard'))),

                $this->makeSearchableColumn('analysis', 'Analisis', 'imut_penilaians.analysis'),
                $this->makeSearchableColumn('document_upload', 'Dokumen Upload', 'imut_penilaians.document_upload'),
                $this->makeSearchableColumn('recommendations', 'Rekomendasi', 'imut_penilaians.recommendations'),
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
                    // ->action(dd(fn($record) => dd($record->laporan_imut_id) ))
                    ->url(fn($record) => url()->route('filament.admin.resources.laporan-imuts.edit-penilaian') .
                        '?laporan_id=' . $record->laporan_imut_id .
                        '&penilaian_id=' . $record->id)
            ])
            ->recordUrl(fn($record) => url()->route('filament.admin.resources.laporan-imuts.edit-penilaian') .
                '?laporan_id=' . $record->laporan_imut_id .
                '&penilaian_id=' . $record->id)
            ->bulkActions([]);
    }

    protected function makeSearchableColumn(string $name, string $label, string $dbColumn): TextColumn
    {
        return TextColumn::make($name)
            ->label($label)
            ->toggleable()
            ->searchable(
                query: fn(Builder $query, string $search) =>
                $query->where($dbColumn, 'like', "%{$search}%")
            );
    }

    public function render()
    {
        return view('livewire.unit-kerja-imut-data-report');
    }
}
