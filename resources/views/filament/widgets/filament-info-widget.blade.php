<x-filament-widgets::widget class="fi-filament-info-widget">
    <x-filament::section>
        <div class="flex items-center justify-between gap-x-6">
            <div class="flex items-center gap-x-3">
                @if (setting('site_logo'))
                    <img src="{{ setting('site_logo') }}" alt="{{ setting('site_name') }}" class="w-auto h-10 rounded">
                @endif

                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ setting('site_name', 'SIIMUT RS') }}
                    </h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ setting('site_description', 'Dashboard Monitoring dan Evaluasi Rumah Sakit') }}
                    </p>
                </div>
            </div>

            <div class="flex flex-col items-end text-right gap-y-1">
                {{-- Versi Aplikasi --}}
                <span class="text-sm text-gray-500 dark:text-gray-400">
                    Versi: {{ config('app.version', 'v1.0.0') }}
                </span>

                {{-- Dokumentasi --}}
                <x-filament::link color="gray" href="https://github.com/juniyasyos" icon="heroicon-m-book-open"
                    rel="noopener noreferrer" target="_blank">
                    Panduan Penggunaan
                </x-filament::link>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
