<?php

namespace App\Filament\Resources\LaporanImutResource\Pages;

use App\Models\User;
use App\Models\LaporanImut;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Auth;
use App\Filament\Resources\LaporanImutResource;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;

class UnitKerjaReport extends Page
{
    use HasPageShield;

    protected static string $resource = LaporanImutResource::class;

    protected static string $view = 'filament.resources.laporan-imut-resource.pages.unit-kerja-report';

    protected static bool $shouldRegisterNavigation = false;

    public $data = [];
    public ?LaporanImut $laporan = null;

    public static function canAccess(array $parameters = []): bool
    {
        /** @var User $user */
        $user = Auth::user();
        return $user->can('view_unit_kerja_report_laporan::imut');
    }

    public function mount()
    {
        $laporanId = request()->query('laporan_id');

        if (!$laporanId) {
            abort(404, 'Laporan tidak ditemukan');
        }

        // Cek apakah ID ini valid
        $laporan = LaporanImut::with('unitKerjas', 'imutPenilaians')->find($laporanId);

        if (!$laporan) {
            abort(404, 'Laporan tidak ditemukan');
        }

        $this->laporan = $laporan;

        $this->data = [
            'laporanId' => $laporan->id,
            'start_date' => $laporan->assessment_period_start,
            'end_date' => $laporan->assessment_period_end,
            'status' => $laporan->status,
        ];
    }

    public function getTitle(): string
    {
        return 'Summary Unit Kerja Report';
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

        if ($this->laporan) {
            $breadcrumbs[LaporanImutResource::getUrl('edit', ['record' => $this->laporan->slug])] = $this->laporan->name;

            $breadcrumbs[] = 'Summary Unit Kerja';
        } else {
            $breadcrumbs[] = 'Detail Laporan';
        }
        return $breadcrumbs;
    }
}
