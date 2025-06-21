<x-filament-widgets::widget class="fi-filament-info-widget">
    <x-filament::section>
        @if ($unitKerja)
            <div class="flex flex-col gap-6">
                {{-- Header --}}
                <div class="flex items-center justify-between gap-x-6">
                    <div class="flex items-center gap-x-4">
                        <div
                            class="flex items-center justify-center w-12 h-12 text-xl font-bold text-white rounded-full bg-primary-500">
                            {{ Str::substr($unitKerja->unit_name, 0, 1) }}
                        </div>

                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ $unitKerja->unit_name }}
                            </h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $unitKerja->description ?? 'Tidak ada deskripsi.' }}
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-col items-end text-right gap-y-1">
                        <span class="text-sm text-gray-500 dark:text-gray-400">
                            Kode Unit: #{{ $unitKerja->id }}
                        </span>
                        <x-filament::link color="gray" href="#" icon="heroicon-m-pencil-square">
                            Kelola Unit
                        </x-filament::link>
                    </div>
                </div>

                {{-- Penanggung Jawab --}}
                <div>
                    <h3 class="mb-2 text-sm font-semibold text-gray-900 dark:text-white">Penanggung Jawab</h3>
                    <ul class="space-y-1">
                        @forelse ($unitKerja->users as $user)
                            <li class="flex items-center gap-2 text-gray-700 dark:text-gray-300">
                                <x-heroicon-m-user class="w-4 h-4 text-gray-400" />
                                {{ $user->name }}
                                <span class="text-gray-400">({{ $user->email }})</span>
                            </li>
                        @empty
                            <li class="text-gray-500 dark:text-gray-400">Belum ada user terdaftar.</li>
                        @endforelse
                    </ul>
                </div>

                {{-- Aksi Cepat --}}
                <div class="flex gap-3 mt-4">
                    <x-filament::button icon="heroicon-m-folder-open" color="gray" size="sm" href="#">
                        Lihat Indikator
                    </x-filament::button>

                    <x-filament::button icon="heroicon-m-clipboard-document-check" color="gray" size="sm"
                        href="#">
                        Lihat Laporan
                    </x-filament::button>
                </div>
            </div>
        @else
            <p class="text-sm text-gray-500 dark:text-gray-400">Anda belum memiliki unit kerja yang terdaftar.</p>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
