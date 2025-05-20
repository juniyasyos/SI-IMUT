<x-filament-panels::page class="fi-dashboard-page">
    @if (method_exists($this, 'filtersForm'))
        {{ $this->filtersForm }}
    @endif

    {{-- Sapaan selamat pagi/siang/sore --}}
    {{-- <div class="mb-6 rounded-xl shadow-sm">
        <h2 class="text-2xl font-bold text-gray-800">
            👋 Selamat
            {{ now()->format('H') < 12 ? 'Pagi' : (now()->format('H') < 17 ? 'Siang' : 'Sore') }},
            {{ auth()->user()?->name ?? 'Pengguna' }}!
        </h2>
        <p class="text-sm text-gray-600 mt-1">
            Semoga harimu menyenangkan dan produktif 🌤️
        </p>
    </div> --}}

    <x-filament-widgets::widgets :columns="$this->getColumns()" :data="[...property_exists($this, 'filters') ? ['filters' => $this->filters] : [], ...$this->getWidgetData()]" :widgets="$this->getVisibleWidgets()" />
</x-filament-panels::page>
