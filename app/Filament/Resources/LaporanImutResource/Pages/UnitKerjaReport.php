<?php

namespace App\Filament\Resources\LaporanImutResource\Pages;

use App\Models\User;
use App\Models\LaporanImut;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\Cache;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\DatePicker;
use App\Filament\Resources\LaporanImutResource;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;

class UnitKerjaReport extends Page
{
    use HasPageShield;

    protected static string $resource = LaporanImutResource::class;

    protected static string $view = 'filament.resources.laporan-imut-resource.pages.unit-kerja-report';

    protected static bool $shouldRegisterNavigation = false;

    public static function canAccess(array $parameters = []): bool
    {
        /** @var User $user */
        $user = Auth::user();

        return $user->can('view_unit_kerja_report_laporan::imut');
    }

    public $data = [];

    public function mount()
    {
        $laporanId = request()->query('laporan_id');

        if (!$laporanId)
            return;

        // Cache laporan dan relasi-relasinya
        $cacheKey = "laporan_imut_detail_{$laporanId}";
        $this->data = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($laporanId) {
            $laporan = LaporanImut::with('unitKerjas', 'imutPenilaians')->find($laporanId);

            if (!$laporan)
                return [];

            $unitKerjaDetails = $laporan->unitKerjas()->get();

            return [
                'laporanId' => $laporan->id,
                'unit_kerja' => '',
                'start_date' => $laporan->assessment_period_start,
                'end_date' => $laporan->assessment_period_end,
                'status' => $laporan->status,
                'unit_kerja_details' => $unitKerjaDetails,
            ];
        });

        // Pre-fill form data with cached data
        $this->form->fill($this->data);
    }

    protected function getFormSchema(): array
    {
        return [
            Section::make('Filter Laporan')
                ->collapsible()
                ->schema([
                    Select::make('laporanId')
                        ->label('Nama Laporan')
                        ->options(function ($get) {
                            $laporanId = $get('laporanId');
                            if ($laporanId) {
                                $laporan = LaporanImut::find($laporanId);
                                return $laporan ? [$laporan->id => $laporan->name] : [];
                            }

                            // Jika belum ada laporanId, kosongkan atau ambil berdasarkan unit_kerja_id
                            if ($unitKerjaId = $get('unit_kerja_id')) {
                                return LaporanImut::whereHas('unitKerjas', function ($query) use ($unitKerjaId) {
                                    $query->where('unit_kerja_id', $unitKerjaId);
                                })->pluck('name', 'id')->toArray();
                            }

                            return [];
                        })
                        ->columnSpan(3)
                        ->disabled()
                        ->reactive()
                        ->required(),


                    Select::make('status')
                        ->label('Status Laporan')
                        ->options([
                            'process' => 'Proses',
                            'complete' => 'Selesai',
                            'canceled' => 'Dibatalkan',
                        ])
                        ->default('process')
                        ->disabled()
                        ->required(),

                    DatePicker::make('start_date')
                        ->label('Tanggal Mulai')
                        ->disabled(),

                    DatePicker::make('end_date')
                        ->label('Tanggal Akhir')
                        ->disabled(),
                ])
                ->columns(3),
        ];
    }

    protected function getFormStatePath(): string
    {
        return 'data';
    }

    // Function to dynamically update laporan options based on selected unit kerja
    protected function updateLaporanOptions($unitKerjaId)
    {
        $laporanOptions = LaporanImut::whereHas('unitKerjas', function ($query) use ($unitKerjaId) {
            $query->where('unit_kerja_id', $unitKerjaId);
        })->pluck('name', 'id')->toArray();

        $this->form->get('laporanId')->options($laporanOptions)->enable();
    }

    public function getBreadcrumbs(): array
    {
        $breadcrumbs = [
            LaporanImutResource::getUrl('index') => 'Daftar Laporan IMUT',
        ];

        if (!empty($this->data['laporanId'])) {
            $laporan = LaporanImut::select('name')->find($this->data['laporanId']);

            $breadcrumbs[] = "Summary Unit Kerja";

            $breadcrumbs[] = $laporan ? "{$laporan->name}" : 'Detail Laporan';
        } else {
            $breadcrumbs[] = 'Detail Laporan';
        }

        return $breadcrumbs;
    }


}
