<?php

namespace App\Filament\Resources\LaporanImutResource\Pages;

use App\Models\User;
use App\Models\UnitKerja;
use App\Models\LaporanImut;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\Cache;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\DatePicker;
use App\Filament\Resources\LaporanImutResource;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;

class UnitKerjaImutDataReport extends Page
{
    protected static string $resource = LaporanImutResource::class;
    protected static string $view = 'filament.resources.laporan-imut-resource.pages.unit-kerja-imut-data-report';
    protected static bool $shouldRegisterNavigation = false;

    public static function canAccess(array $parameters = []): bool
    {
        /** @var User $user */
        $user = Auth::user();

        return $user->can('view_unit_kerja_report_detail_laporan::imut');
    }

    public array $data = [];

    protected ?LaporanImut $laporan = null;
    protected ?UnitKerja $unitKerja = null;

    public function mount(): void
    {
        $laporanId = request('laporan_id');
        $unitKerjaId = request('unit_kerja_id');

        if (!$laporanId || !$unitKerjaId) {
            return;
        }

        $this->laporan = LaporanImut::select('id', 'status', 'assessment_period_start', 'assessment_period_end', 'name')
            ->with(['unitKerjas:id'])
            ->where('id', $laporanId)
            ->first();

        $this->unitKerja = UnitKerja::select('id', 'unit_name')->where('id', $unitKerjaId)->first();

        if (
            !$this->laporan ||
            !$this->unitKerja ||
            !$this->laporan->unitKerjas->contains('id', $unitKerjaId)
        ) {
            return;
        }

        $cacheKey = \App\Support\CacheKey::laporanUnitDetail($laporanId, $unitKerjaId);

        $this->data = Cache::remember($cacheKey, now()->addMinutes(30), fn() => [
            'laporanId' => $this->laporan->id,
            'status' => $this->laporan->status,
            'start_date' => $this->laporan->assessment_period_start,
            'end_date' => $this->laporan->assessment_period_end,
            'unit_kerja_id' => $unitKerjaId,
        ]);

        $this->form->fill($this->data);
    }


    public function getTitle(): string
    {
        return 'Summary Laporan Unit Kerja : ' . $this->unitKerja->unit_name;
    }

    protected function getFormStatePath(): string
    {
        return 'data';
    }

    private function getLaporanOptions(?int $unitKerjaId): array
    {
        if (!$unitKerjaId) {
            return [];
        }

        return LaporanImut::whereHas(
            'unitKerjas',
            fn($q) => $q->where('unit_kerja_id', $unitKerjaId)
        )->pluck('name', 'id')->toArray();
    }

    public function getBreadcrumbs(): array
    {
        $breadcrumbs = [
            LaporanImutResource::getUrl('index') => 'Daftar Laporan IMUT',
        ];

        $laporanId = $this->data['laporanId'] ?? null;
        $unitKerjaId = $this->data['unit_kerja_id'] ?? null;

        if ($laporanId) {
            $laporan = LaporanImut::select('name', 'slug')->find($laporanId);

            if ($laporan) {
                // Link ke halaman edit berdasarkan slug
                $breadcrumbs[LaporanImutResource::getUrl('edit', ['record' => $laporan->slug])] = $laporan->name;
            } else {
                $breadcrumbs[] = 'Detail Laporan';
            }

            $breadcrumbs[UnitKerjaReport::getUrl([
                'laporan_id' => $laporanId,
            ])] = "Summary Unit Kerja";
        }

        if ($unitKerjaId) {
            $unitKerja = UnitKerja::select('unit_name')->find($unitKerjaId);
            $breadcrumbs[] = $unitKerja?->unit_name ?? 'Detail Unit Kerja';
        }

        return $breadcrumbs;
    }
}
