<x-filament-panels::page>
    {{ $this->form }}

    {{-- {{ dd(request()->all()) }} --}}
    <livewire:imut-data-unit-kerja-report :laporan-id="$data['laporanId']" :imut-data-id="request()->get('imut_data_id')" />
</x-filament-panels::page>
