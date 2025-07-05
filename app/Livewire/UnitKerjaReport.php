<?php

namespace App\Livewire;

use App\Filament\Exports\SummaryUnitKerjaReportExport;
use App\Filament\Resources\LaporanImutResource\Pages\UnitKerjaImutDataReport;
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
use Illuminate\Container\Attributes\DB;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Number;
use Livewire\Component;

class UnitKerjaReport extends Component implements HasForms, HasTable
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

    public function getColumnsForView()
    {
        return $this->table->getColumns();
    }


    public function table(Table $table): Table
    {
        return $table
            ->query(fn() => LaporanUnitKerja::getReportByUnitKerja($this->laporanId))
            ->columns([
                TextColumn::make('unit_name')
                    ->label('Unit Kerja')
                    ->width('30%')
                    ->searchable(),

                TextColumn::make('completion_summary')
                    ->label('Capaian')
                    ->alignCenter()
                    ->toggleable()
                    ->state(fn($record) => number_format($record->filled_count ?? 0) . ' dari ' . number_format($record->total_count ?? 0) . ' imut sudah terisi')
                    ->tooltip(fn($record) => 'Persentase: ' . Number::format($record->percentage ?? 0, 2, locale: app()->getLocale()) . '%')
                    ->color(fn($record) => match (true) {
                        !is_numeric($record->percentage) => null,
                        $record->percentage >= ($record->avg_standard ?? 100) => 'success',
                        $record->percentage >= (($record->avg_standard ?? 100) * 0.8) => 'warning',
                        default => 'danger',
                    })
                    ->summarize(
                        Summarizer::make()
                            ->label('Total Capaian')
                            ->using(function (Builder $query) {
                                $n = $query->sum('filled_count');
                                $d = $query->sum('total_count');
                                return $d > 0 ? Number::format(($n / $d) * 100, 2, locale: app()->getLocale()) . '%' : '0%';
                            })
                    ),

            ])
            ->headerActions([
                ExportAction::make()->exporter(SummaryUnitKerjaReportExport::class)->color('gray')
            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('details')
                    ->label('Lihat Detail')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->url(fn($record) => UnitKerjaImutDataReport::getUrl([
                        'laporan_id' => $record->laporan_imut_id,
                        'unit_kerja_id' => $record->unit_kerja_id,
                    ])),
                //
            ])
            ->recordUrl(fn($record) => UnitKerjaImutDataReport::getUrl([
                'laporan_id' => $record->laporan_imut_id,
                'unit_kerja_id' => $record->unit_kerja_id,
            ]))
            ->bulkActions([
                // ...
            ]);
    }

    public function render()
    {
        return view('livewire.unit-kerja-report');
    }
}