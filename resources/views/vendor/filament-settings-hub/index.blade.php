<x-filament-panels::page>
    @php
        $settings = \Juniyasyos\FilamentSettingsHub\Facades\FilamentSettingsHub::load()
            ->sortBy('order')
            ->groupBy('group');
        $tenant = \Filament\Facades\Filament::getTenant();
    @endphp

    @foreach ($settings as $settingGroup => $setting)
        <div class="fi-page">
            <section class="grid grid-cols-12 gap-4 shadow-sm">
                <!-- System Info (4 kolom di desktop, 12 di mobile) -->
                <div class="col-span-12 md:col-span-4 flex flex-col">
                    <h3
                        class="fi-header-heading text-2xl font-bold tracking-tight text-gray-950 dark:text-white sm:text-3xl">
                        Sistem Indikator Mutu
                    </h3>
                    <p class="text-sm text-gray-700 dark:text-gray-400">{{ setting('site_name') }}</p>
                    <a href="https://github.com/juniyasyos"
                        class="fi-link text-primary-600 dark:text-primary-400 text-sm">Version 1.0.0</a>
                </div>

                <!-- Kartu Informasi -->
                <div class="col-span-12 grid sm:grid-cols-3 gap-4 text-sm">
                    <!-- OS Server -->
                    <div class="fi-section flex p-4 bg-white dark:fi-section-content rounded-lg shadow-sm">
                        <div class="flex flex-col">
                            <span class="font-semibold text-gray-900 dark:text-white">Server OS</span>
                            <span class="text-gray-600 dark:text-gray-400">{{ php_uname('s') }}
                                {{ php_uname('r') }}</span>
                        </div>
                    </div>

                    <!-- Versi PHP -->
                    <div class="fi-section flex p-4 bg-white dark:fi-section-content rounded-lg shadow-sm">
                        <div class="flex flex-col">
                            <span class="font-semibold text-gray-900 dark:text-white">PHP Version</span>
                            <span class="text-gray-600 dark:text-gray-400">{{ PHP_VERSION }}</span>
                        </div>
                    </div>

                    <!-- Informasi Pengembang -->
                    <div class="fi-section flex p-4 bg-white dark:fi-section-content rounded-lg shadow-sm">
                        <div class="flex flex-col">
                            <span class="font-semibold text-gray-900 dark:text-white">Pengembang</span>
                            <span class="text-gray-600 dark:text-gray-400">4 Daun Serangkai</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Section: Anak langsung dari .fi-page agar mendapatkan gap-y-6 -->
            <div class="pt-6">
                <h1 class="filament-header-heading p-2"></h1>
                <section>
                    @foreach ($setting as $item)
                        @php
                            $canAccess = true;
                            $routeUrl = $item->route
                                ? ($tenant
                                    ? route($item->route, $tenant)
                                    : route($item->route))
                                : null;
                            $pageUrl = $item->page ? app($item->page)::getUrl() : null;

                            if ($item->route && filament('filament-settings-hub')->isShieldAllowed()) {
                                $page =
                                    optional(
                                        \Illuminate\Support\Facades\Route::getRoutes()->getRoutesByName()[$item->route],
                                    )->action['controller'] ?? null;
                                $page = $page ? str($page)->afterLast('\\') : null;
                                $canAccess = $page
                                    ? \Filament\Facades\Filament::auth()
                                        ->user()
                                        ->can('page_' . $page)
                                    : false;
                            } elseif ($item->page && filament('filament-settings-hub')->isShieldAllowed()) {
                                $canAccess = \Filament\Facades\Filament::auth()
                                    ->user()
                                    ->can('page_' . str($item->page)->afterLast('\\'));
                            }
                        @endphp

                        @if ($canAccess && ($routeUrl || $pageUrl))
                            <!-- Masing-masing item menggunakan .fi-section dan ditata dengan flex serta padding -->
                            <a href="{{ $routeUrl ?? $pageUrl }}"
                                class="group fi-section flex items-center p-4 mb-2 rounded-lg bg-white dark:fi-section-content
                                   hover:bg-gray-100 dark:hover:bg-gray-700
                                   border border-gray-200 dark:border-gray-600
                                   transition duration-200 ease-in-out">

                                <!-- Ikon -->
                                <div class="p-2">
                                    @if (isset($item->icon))
                                        <x-icon name="{{ $item->icon }}"
                                            class="fi-icon-btn w-10 h-10 text-gray-800 dark:text-gray-200
                                               group-hover:text-gray-900 dark:group-hover:text-gray-100"
                                            style="color: {{ $item->color ?? 'inherit' }}" />
                                    @else
                                        <x-heroicon-s-cog
                                            class="fi-icon-btn w-10 h-10 text-gray-800 dark:text-gray-200
                                               group-hover:text-gray-900 dark:group-hover:text-gray-100" />
                                    @endif
                                </div>

                                <!-- Teks -->
                                <div class="flex-1">
                                    <h1 class="font-bold text-lg text-gray-900 dark:text-white">
                                        {{ str($item->label)->contains(['.', '::']) ? trans($item->label) : $item->label }}
                                    </h1>
                                    <p class="text-sm text-gray-700 dark:text-gray-400">
                                        {{ str($item->description)->contains(['.', '::']) ? trans($item->description) : $item->description }}
                                    </p>
                                </div>

                                <!-- Ikon di pojok kanan tengah -->
                                <div class="ml-auto flex items-center">
                                    <x-icon name="heroicon-s-chevron-right"
                                        class="fi-icon-btn w-5 h-5 text-gray-800 dark:text-gray-200
                                           group-hover:text-gray-900 dark:group-hover:text-gray-100"
                                        style="color: {{ $item->color ?? 'inherit' }}" />
                                </div>
                            </a>
                        @endif
                    @endforeach
                </section>
            </div>
        </div>
    @endforeach
</x-filament-panels::page>
