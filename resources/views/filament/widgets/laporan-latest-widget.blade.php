<x-filament-widgets::widget>
    <x-filament::section>
        @php
            $laporan = $this->getLaporan();

            $statusColors = [
                'process' => 'bg-blue-100 text-blue-800 ring-1 ring-inset ring-blue-300',
                'complete' => 'bg-green-100 text-green-800 ring-1 ring-inset ring-green-300',
                'canceled' => 'bg-red-100 text-red-800 ring-1 ring-inset ring-red-300',
            ];
        @endphp

        @if (!$laporan)
            <div class="text-center text-gray-500">
                <p class="text-lg font-semibold">Belum ada laporan tersedia</p>
                <p class="text-sm">Silakan buat laporan terlebih dahulu.</p>
            </div>
        @else
            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-bold">{{ $laporan->name }}</h2>
                    <p class="text-sm text-gray-500">
                        Periode: {{ $laporan->assessment_period_start->format('M Y') }} -
                        {{ $laporan->assessment_period_end->format('M Y') }}
                    </p>
                </div>

                <span
                    class="text-sm font-medium px-4 py-1 rounded-full
                    {{ $statusColors[$laporan->status] ?? 'bg-gray-100 text-gray-800 ring-1 ring-inset ring-gray-300' }}">
                    Status: {{ ucfirst($laporan->status) }}
                </span>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
