<x-filament-widgets::widget class="fi-filament-info-widget">
    <x-filament::section>
        @if ($unitKerja)
            <div class="flex flex-col gap-6">
                {{-- Header --}}
                <div class="flex items-center justify-between gap-x-6">
                    <div class="flex items-center gap-x-4">
                        <div
                            class="flex items-center justify-center text-2xl font-bold text-white rounded-full w-14 h-14 bg-primary-600">
                            {{ Str::substr($unitKerja->unit_name, 0, 1) }}
                        </div>

                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ $unitKerja->unit_name }}
                            </h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $unitKerja->description ?? 'Tidak ada deskripsi unit.' }}
                            </p>
                            <p class="mt-1 text-xs text-gray-400">
                                Dibuat pada {{ $unitKerja->created_at->translatedFormat('d F Y') }}
                            </p>
                        </div>
                    </div>

                    <div class="text-sm text-right">
                        <p class="text-gray-500 dark:text-gray-400">Kode Unit</p>
                        <p class="font-medium text-gray-800 dark:text-white">#{{ $unitKerja->id }}</p>
                        <p class="mt-1 text-gray-500 dark:text-gray-400">
                            {{ $unitKerja->users->count() }} Penanggung Jawab
                        </p>
                    </div>
                </div>

                {{-- Penanggung Jawab --}}
                <div>
                    <h3 class="mb-2 text-sm font-semibold text-gray-900 dark:text-white">
                        Daftar Penanggung Jawab
                    </h3>
                    <ul class="space-y-1">
                        @forelse ($unitKerja->users as $user)
                            <li class="flex items-center gap-2 text-gray-700 dark:text-gray-300">
                                <x-heroicon-m-user class="w-4 h-4 text-gray-400" />
                                <div>
                                    <p class="text-sm font-medium">{{ $user->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $user->email }}</p>
                                </div>
                            </li>
                        @empty
                            <li class="text-gray-500 dark:text-gray-400">
                                Belum ada user terdaftar sebagai penanggung jawab.
                            </li>
                        @endforelse
                    </ul>
                </div>

                {{-- Info Ringkas --}}
                <div class="pt-4 text-xs text-gray-500 border-t dark:text-gray-400">
                    Informasi ini hanya untuk ditampilkan, tidak ada tindakan yang dapat dilakukan dari sini.
                </div>
            </div>
        @else
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Anda belum memiliki unit kerja yang terdaftar.
            </p>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
