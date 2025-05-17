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

class ImutDataReport extends Page
{
    protected static string $resource = LaporanImutResource::class;

    protected static string $view = 'filament.resources.laporan-imut-resource.pages.imut-data-report';

    protected static bool $shouldRegisterNavigation = false;

    public array $data = [];

    public static function canAccess(array $parameters = []): bool
    {
        /** @var User $user */
        $user = Auth::user();

        return $user->can('view_imut_data_report_laporan::imut');
    }

    public function mount(): void
    {
        $laporanId = request()->query('laporan_id');

        if (!$laporanId)
            return;

        $cacheKey = "imut_data_report_{$laporanId}";

        $this->data = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($laporanId) {
            $laporan = LaporanImut::with(['imutPenilaians'])->find($laporanId);

            if (!$laporan)
                return [];

            return [
                'laporanId' => $laporan->id,
                'name' => $laporan->name,
                'status' => $laporan->status,
                'start_date' => $laporan->assessment_period_start,
                'end_date' => $laporan->assessment_period_end,
                'imut_penilaians' => $laporan->imutPenilaians,
            ];
        });

        $this->form->fill($this->data);
    }

    protected function getFormSchema(): array
    {
        return [
            Section::make('Informasi Laporan IMUT')
                ->collapsible()
                ->columns(3)
                ->schema([
                    Select::make('laporanId')
                        ->label('Nama Laporan')
                        ->options(fn() => $this->data['laporanId']
                            ? [$this->data['laporanId'] => $this->data['name']]
                            : [])
                        ->columnSpan(3)
                        ->disabled()
                        ->required(),

                    Select::make('status')
                        ->label('Status Laporan')
                        ->options([
                            'process' => 'Proses',
                            'complete' => 'Selesai',
                            'canceled' => 'Dibatalkan',
                        ])
                        ->disabled(),

                    DatePicker::make('start_date')
                        ->label('Tanggal Mulai')
                        ->disabled(),

                    DatePicker::make('end_date')
                        ->label('Tanggal Akhir')
                        ->disabled(),
                ]),
        ];
    }

    protected function getFormStatePath(): string
    {
        return 'data';
    }

    public function getBreadcrumbs(): array
    {
        $breadcrumbs = [
            LaporanImutResource::getUrl('index') => 'Daftar Laporan IMUT',
        ];

        $breadcrumbs[] = "Summary IMUT Data";

        $breadcrumbs[] = (!empty($this->data['laporanId'])) ? $this->data['name'] ?? 'Laporan IMUT' : 'Detail Data IMUT';

        return $breadcrumbs;
    }
}
