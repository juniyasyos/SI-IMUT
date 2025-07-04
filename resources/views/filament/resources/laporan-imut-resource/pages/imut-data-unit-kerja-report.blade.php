<x-filament-panels::page>
    @php
        $laporan = $this->laporan;

        $status = $laporan?->status;

        $styles = [
            'process' => ['label' => 'Proses', 'color' => 'warning'],
            'complete' => ['label' => 'Selesai', 'color' => 'success'],
            'coming_soon' => ['label' => 'Akan Datang', 'color' => 'danger'],
        ];

        $current = $status ? $styles[$status] ?? null : null;

        $start = Illuminate\Support\Carbon::parse($laporan?->assessment_period_start);
        $end = Illuminate\Support\Carbon::parse($laporan?->assessment_period_end);

        $sameMonth = $start->month === $end->month && $start->year === $end->year;

        $periode = $sameMonth
            ? $start->translatedFormat('d') . ' – ' . $end->translatedFormat('d F Y')
            : $start->translatedFormat('d M') . ' – ' . $end->translatedFormat('d F Y');
    @endphp

    <x-laporan-status-periode :periode="$periode" :current="$current" />

    {{ $this->form }}

    {{-- {{ dd(request()->all()) }} --}}
    <livewire:imut-data-unit-kerja-report :laporan-id="$data['laporanId']" :imut-data-id="request()->get('imut_data_id')" />
</x-filament-panels::page>
