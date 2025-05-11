<x-filament-panels::page>
    {{ $this->form }}

    {{-- {{ dd(request()->all()) }} --}}
    <livewire:unit-kerja-imut-data-report :laporan-id="$data['laporanId']" :unit-kerja-id="request()->get('unit_kerja_id')" />
</x-filament-panels::page>
