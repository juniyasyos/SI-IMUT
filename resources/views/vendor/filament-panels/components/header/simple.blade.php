@props([
    'heading' => null,
    'logo' => true,
    'subheading' => null,
])

<header class="fi-simple-header flex flex-col items-start">
    {{-- @if ($logo)
        <x-filament-panels::logo class="mb-4" />
    @endif --}}

    @if (filled($heading))
        <h1
            class="fi-simple-header-heading text-center text-2xl font-bold tracking-tight text-gray-950 dark:text-white">
            {{ $heading }}
        </h1>

        <p class="fi-simple-header-subheading text-start text-base text-gray-600 dark:text-gray-300 mb-5">
            Hello! May today bring you clarity, focus, and success.
        </p>
    @endif

    @if (filled($subheading))
        <p class="fi-simple-header-subheading mt-2 text-center text-sm text-gray-500 dark:text-gray-400">
            {{ $subheading }}
        </p>
    @endif
</header>
