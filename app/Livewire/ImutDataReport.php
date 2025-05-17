<?php

namespace App\Livewire;

use Livewire\Component;
use Filament\Tables\Table;
use Illuminate\Support\Number;
use App\Models\LaporanUnitKerja;
use Filament\Tables\Actions\Action;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Database\Query\Builder;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Columns\Summarizers\Summarizer;
use App\Filament\Resources\LaporanImutResource\Pages\ImutDataUnitKerjaReport;

class ImutDataReport extends Component implements HasTable, HasForms
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


    public function table(Table $table): Table
    {
        return $table
            ->query(fn() => LaporanUnitKerja::getReportByImutData($this->laporanId))
            ->columns([
                TextColumn::make('imut_data_title')
                    ->label('IMUT Data')
                    ->width('30%')
                    ->searchable(),

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
                        !is_numeric($record->percentage) || !is_numeric($record->avg_standard) => null,
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

                TextColumn::make('avg_standard')
                    ->label('S')
                    ->suffix('%')
                    ->color('info')
                    ->badge()
                    ->alignCenter()
                // ->summarize(Summarizer::make()
                //     ->label('Standar Maks')
                //     ->suffix('%')
                //     ->using(fn(Builder $query) => $query->max('avg_standard'))),
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
                    ]))
                // ->openUrlInNewTab()
            ])
            ->bulkActions([
                // ...
            ]);
    }

    public function render()
    {
        return view('livewire.imut-data-report');
    }
}
