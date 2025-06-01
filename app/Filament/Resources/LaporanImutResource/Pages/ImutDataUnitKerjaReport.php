<?php

namespace App\Filament\Resources\LaporanImutResource\Pages;

use App\Models\User;
use App\Models\ImutData;
use App\Models\LaporanImut;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\Cache;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\DatePicker;
use App\Filament\Resources\LaporanImutResource;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;

class ImutDataUnitKerjaReport extends Page
{
    protected static string $resource = LaporanImutResource::class;
    protected static string $view = 'filament.resources.laporan-imut-resource.pages.imut-data-unit-kerja-report';
    protected static bool $shouldRegisterNavigation = false;

    public array $data = [];

    public static function canAccess(array $parameters = []): bool
    {
        /** @var User $user */
        $user = Auth::user();

        return $user->can('view_imut_data_report_detail_laporan::imut');
    }

    protected ?LaporanImut $laporan = null;
    protected ?ImutData $imutData = null;

    public function mount(): void
    {
        $laporanId = request('laporan_id');
        $imutDataId = request('imut_data_id');

        if (!$laporanId || !$imutDataId)
            return;

        $this->laporan = LaporanImut::select('id', 'status', 'assessment_period_start', 'assessment_period_end', 'name')
            ->with(['unitKerjas:id'])
            ->where('id', $laporanId)
            ->first();

        $this->imutData = ImutData::select('id', 'title')->where('id', $imutDataId)->first();

        if (
            !$this->laporan ||
            !$this->imutData
            // !$this->laporan->imutPenilaians()->contains('id', $imutDataId)
        ) {
            return;
        }

        $cacheKey = \App\Support\CacheKey::laporanImutDetail($laporanId, $imutDataId);

        $this->data = Cache::remember($cacheKey, now()->addMinutes(30), fn() => [
            'laporanId' => $this->laporan->id,
            'status' => $this->laporan->status,
            'start_date' => $this->laporan->assessment_period_start,
            'end_date' => $this->laporan->assessment_period_end,
            'imut_data_id' => $imutDataId,
        ]);

        $this->form->fill($this->data);
    }

    public function getTitle(): string
    {
        return 'Summary Laporan IMUT Data : ' . $this->imutData->title;
    }

    protected function getFormStatePath(): string
    {
        return 'data';
    }

    public function getBreadcrumbs(): array
    {

        $laporanId = $this->data['laporanId'] ?? null;
        $imutDataId = $this->data['imut_data_id'] ?? null;

        $laporan = LaporanImut::select('name')->find($laporanId);

        $breadcrumbs = [
            LaporanImutResource::getUrl('index') => 'Daftar Laporan IMUT',
        ];


        if ($laporanId ?? null) {
            $laporan = LaporanImut::select('name')->find($laporanId);
            $breadcrumbs[LaporanImutResource::getUrl('edit', ['record' => $laporanId])] = $laporan->name ?? 'Detail Laporan';
            $breadcrumbs[ImutDataReport::getUrl(['laporan_id' => $laporanId])] = 'Summary IMUT Data';
        }

        if ($imutDataId ?? null) {
            $imutData = ImutData::select('title')->find($imutDataId);
            $breadcrumbs[] = $imutData?->title ?? 'Detail IMUT Data';
        }

        return $breadcrumbs;
    }
}
