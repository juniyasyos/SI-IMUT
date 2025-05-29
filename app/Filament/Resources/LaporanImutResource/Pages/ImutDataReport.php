<?php

namespace App\Filament\Resources\LaporanImutResource\Pages;

use App\Models\User;
use App\Models\LaporanImut;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Auth;
use App\Filament\Resources\LaporanImutResource;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;

class ImutDataReport extends Page
{
    use HasPageShield;

    protected static string $resource = LaporanImutResource::class;

    protected static string $view = 'filament.resources.laporan-imut-resource.pages.imut-data-report';

    protected static bool $shouldRegisterNavigation = false;

    public array $data = [];

    public ?LaporanImut $laporan = null;

    public static function canAccess(array $parameters = []): bool
    {
        /** @var User $user */
        $user = Auth::user();

        return $user->can('view_imut_data_report_laporan::imut');
    }

    public function mount(): void
    {
        $laporanId = request()->query('laporan_id');

        if (!$laporanId) {
            return;
        }

        $laporan = LaporanImut::with(['imutPenilaians'])->find($laporanId);

        if (!$laporan) {
            return;
        }

        $this->laporan = $laporan;

        $this->data = [
            'laporanId' => $laporan->id,
            'name' => $laporan->name,
            'status' => $laporan->status,
            'start_date' => $laporan->assessment_period_start,
            'end_date' => $laporan->assessment_period_end,
            'imut_penilaians' => $laporan->imutPenilaians,
        ];

        $this->form->fill($this->data);
    }

    public function getTitle(): string
    {
        return 'Summary IMUT Data Report';
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

        if (!empty($this->data['laporanId'])) {
            $breadcrumbs[LaporanImutResource::getUrl('edit', ['record' => $this->laporan->slug])] = $this->laporan->name;
            $breadcrumbs[] = 'Summary IMUT Data';
        } else {
            $breadcrumbs[] = 'Detail Data IMUT';
        }

        return $breadcrumbs;
    }
}
