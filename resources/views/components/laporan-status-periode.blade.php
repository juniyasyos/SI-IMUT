<section class="bg-white shadow-sm fi-section rounded-xl ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10"
    style="margin-bottom: -4%; margin-top: -1%;">
    <div class="fi-section-content-ctn">
        <div class="grid items-center grid-cols-1 gap-4 p-6 md:grid-cols-2">
            <div>
                <p class="mb-1 text-sm font-medium text-gray-500 dark:text-gray-400">Periode Penilaian</p>
                <p
                    class="text-lg font-semibold leading-snug tracking-wide text-gray-900 sm:text-xl md:text-2xl dark:text-white">
                    {{ $periode }}
                </p>
            </div>

            @if ($current)
                <div class="flex justify-start mt-2 md:mt-0">
                    <div>
                        <span
                            class="fi-badge inline-flex items-center justify-center gap-x-2 rounded-md text-sm md:text-base font-semibold ring-1 ring-inset px-4 md:px-6 py-1.5 md:py-2 min-w-[5rem] fi-color-custom
                            bg-custom-50 text-custom-600 ring-custom-600/10
                            dark:bg-custom-400/10 dark:text-custom-400 dark:ring-custom-400/30"
                            style="--c-400:var(--{{ $current['color'] }}-400); --c-500:var(--{{ $current['color'] }}-500); --c-600:var(--{{ $current['color'] }}-600);">
                            {{ $current['label'] }}
                        </span>
                    </div>
                </div>
            @endif
        </div>
    </div>
</section>
