<?php

declare(strict_types=1);

namespace App\Filament\Resources\LaporanImutResource\Pages;

use App\Models\ImutPenilaian;
use App\Models\LaporanImut;
use App\Filament\Resources\LaporanImutResource;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Resources\Pages\Page;

class PenilaianLaporan extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = LaporanImutResource::class;
    protected static string $view = 'filament.resources.laporan-imut-resource.pages.penilaian-laporan';

    /**
     * The LaporanImut model instance related to this page.
     */
    public ?LaporanImut $laporan = null;

    /**
     * Form data keyed by ImutPenilaian ID.
     *
     * @var array<string, array<string, mixed>>
     */
    public array $formData = [];

    /**
     * Mount the page, load laporan and penilaian data.
     *
     * @return void
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function mount(): void
    {
        $laporanId = request()->integer('laporan_id');
        $penilaianId = request()->integer('penilaian_id');

        // dd(request());

        if (!$laporanId || !$penilaianId) {
            abort(404, 'Invalid request parameters.');
        }

        // Verify laporan has the penilaian with the given ID
        $this->laporan = LaporanImut::select(['id', 'name'])
            ->whereHas('imutPenilaians', fn($query) => $query->where('imut_penilaians.id', $penilaianId))
            ->findOrFail($laporanId);


        // Fetch the specific penilaian for this laporan
        $penilaian = ImutPenilaian::where('id', $penilaianId)
            ->where('laporan_unit_kerja_id', $laporanId) // adjust foreign key if needed
            ->firstOrFail();

        // dd($penilaian);

        $this->formData = [
            'analysis' => $penilaian->analysis,
            'recommendations' => $penilaian->recommendations,
            'numerator_value' => $penilaian->numerator_value,
            'denominator_value' => $penilaian->denominator_value,
        ];

        $this->form->fill($this->formData);
    }

    /**
     * Get the form schema for the penilaian fields.
     *
     * @return array<int, Forms\Components\Component>
     */
    protected function getFormSchema(): array
    {
        return [
            Forms\Components\Textarea::make('analysis')
                ->label('Analysis')
                ->rows(3)
                ->required(),

            Forms\Components\Textarea::make('recommendations')
                ->label('Recommendations')
                ->rows(3)
                ->required(),

            Forms\Components\TextInput::make('numerator_value')
                ->numeric()
                ->label('Numerator')
                ->required(),

            Forms\Components\TextInput::make('denominator_value')
                ->numeric()
                ->label('Denominator')
                ->required()
        ];
    }

    /**
     * Get the form state path.
     *
     * @return string
     */
    protected function getFormStatePath(): string
    {
        return 'formData';
    }

    /**
     * Save the updated penilaian data to the database.
     *
     * @return void
     */
    public function save(): void
    {
        foreach ($this->formData as $id => $data) {
            $penilaian = ImutPenilaian::find($id);

            if (!$penilaian) {
                continue;
            }

            $penilaian->fill($data);

            if ($penilaian->isDirty()) {
                $penilaian->save();
            }
        }

        $this->notify('success', 'All penilaian changes have been saved successfully.');
    }

    /**
     * Get the page title.
     *
     * @return string
     */
    public function getTitle(): string
    {
        return 'Penilaian Laporan: ' . ($this->laporan->name ?? 'Unknown');
    }

    /**
     * Generate breadcrumbs array for navigation.
     *
     * @return array<string, string|array<string, mixed>>
     */
    public function getBreadcrumbs(): array
    {
        $laporanId = $this->laporan?->id;
        $laporanName = $this->laporan?->name ?? 'Detail Laporan';

        return [
            LaporanImutResource::getUrl('index') => 'Daftar Laporan IMUT',
            LaporanImutResource::getUrl('edit', ['record' => $laporanId]) => $laporanName,
            url()->current() => 'Penilaian Laporan',
        ];
    }
}
