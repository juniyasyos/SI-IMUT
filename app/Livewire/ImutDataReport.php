<?php

namespace App\Livewire;

use App\Filament\Exports\SummaryImutDataReportExport;
use App\Filament\Resources\LaporanImutResource\Pages\ImutDataUnitKerjaReport;
use App\Models\LaporanUnitKerja;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Number;
use Livewire\Component;

class ImutDataReport extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public ?int $laporanId = null;

    protected $listeners = [
        'report-changed' => 'updateReport',
    ];

    public function updateReport(int $laporanId): void
    {
        $this->laporanId = $laporanId;
        $this->dispatch('$refresh');
    }

    public function getTableRecordKey($record): string
    {
        if (! $record || ! $record->getKey()) {
            return (string) uniqid('record_', true); 
        }

        return (string) $record->getKey();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn() => LaporanUnitKerja::getReportByImutData($this->laporanId))
            ->columns([
                TextColumn::make('imut_data_title')
                    ->label('IMUT Data')
                    ->width('30%')
                    ->searchable(query: function (\Illuminate\Database\Eloquent\Builder $query, string $search) {
                        return $query->where('imut_data.title', 'like', "%{$search}%");
                    }),

                TextColumn::make('imut_kategori')
                    ->label('Imut Kategori')
                    ->toggleable()
                    ->alignCenter()
                    ->badge(),

                TextColumn::make('total_numerator')
                    ->label('N')
                    ->alignCenter()
                    ->formatStateUsing(fn($state) => Number::format($state, 2, locale: app()->getLocale()))
                    ->summarize(
                        Summarizer::make()
                            ->label('Total N')
                            ->using(fn(Builder $query) => number_format($query->sum('total_numerator'), 2))
                    ),

                TextColumn::make('total_denominator')
                    ->label('D')
                    ->alignCenter()
                    ->formatStateUsing(fn($state) => Number::format($state, 2, locale: app()->getLocale()))
                    ->summarize(
                        Summarizer::make()
                            ->label('Total D')
                            ->using(fn(Builder $query) => number_format($query->sum('total_denominator'), 2))
                    ),

                TextColumn::make('percentage')
                    ->label('Persentase (%)')
                    ->alignCenter()
                    ->suffix('%')
                    ->color(fn($record) => match (true) {
                        ! is_numeric($record->percentage) || ! is_numeric($record->avg_standard) => null,
                        $record->percentage >= $record->avg_standard => 'success',
                        $record->percentage >= $record->avg_standard * 0.8 => 'warning',
                        default => 'danger',
                    })
                    ->summarize(
                        Summarizer::make()
                            ->label('Total Persentase')
                            ->using(function (Builder $query) {
                                $n = $query->sum('total_numerator');
                                $d = $query->sum('total_denominator');

                                return $d > 0 ? round(($n / $d) * 100, 2) : 0;
                            })
                            ->suffix('%')
                    ),
            ])
            ->headerActions([
                ExportAction::make()->exporter(SummaryImutDataReportExport::class)
            ])
            ->filters([
                // ...
            ])
            ->actions([
                Action::make('details')
                    ->label('Lihat Detail')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->url(fn($record) => ImutDataUnitKerjaReport::getUrl([
                        'laporan_id' => $record->laporan_imut_id,
                        'imut_data_id' => $record->id,
                    ])),
                //
            ])
            ->recordUrl(fn($record) => ImutDataUnitKerjaReport::getUrl([
                'laporan_id' => $record->laporan_imut_id,
                'imut_data_id' => $record->id,
            ]))
            ->bulkActions([
                // ...
            ]);
    }

    public function render()
    {
        return view('livewire.imut-data-report');
    }
}