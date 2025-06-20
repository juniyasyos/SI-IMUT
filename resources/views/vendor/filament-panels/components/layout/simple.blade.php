@php
    use Filament\Support\Enums\MaxWidth;
@endphp

<x-filament-panels::layout.base :livewire="$livewire">
    @props([
        'after' => null,
        'heading' => null,
        'subheading' => null,
    ])

    <div class="flex flex-col items-center min-h-screen fi-simple-layout">
        {{-- @if (($hasTopbar ?? true) && filament()->auth()->check())
            <div class="absolute top-0 flex items-center h-16 end-0 gap-x-4 pe-4 md:pe-6 lg:pe-8">
                @if (filament()->hasDatabaseNotifications())
                    @livewire(Filament\Livewire\DatabaseNotifications::class, [
                        'lazy' => filament()->hasLazyLoadedDatabaseNotifications(),
                    ])
                @endif

                <x-filament-panels::user-menu />
            </div>
        @endif --}}

        <div class="flex items-center justify-center flex-grow w-full fi-simple-main-ctn">
            <main @class([
                'fi-simple-main my-16 w-full bg-white px-6 py-12 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 sm:rounded-xl sm:px-12',
                match (
                    ($maxWidth ??=
                        filament()->getSimplePageMaxContentWidth() ?? MaxWidth::Large)
                ) {
                    MaxWidth::ExtraSmall, 'xs' => 'max-w-xs',
                    MaxWidth::Small, 'sm' => 'max-w-sm',
                    MaxWidth::Medium, 'md' => 'max-w-md',
                    MaxWidth::Large, 'lg' => 'max-w-lg',
                    MaxWidth::ExtraLarge, 'xl' => 'max-w-xl',
                    MaxWidth::TwoExtraLarge, '2xl' => 'max-w-2xl',
                    MaxWidth::ThreeExtraLarge, '3xl' => 'max-w-3xl',
                    MaxWidth::FourExtraLarge, '4xl' => 'max-w-4xl',
                    MaxWidth::FiveExtraLarge, '5xl' => 'max-w-5xl',
                    MaxWidth::SixExtraLarge, '6xl' => 'max-w-6xl',
                    MaxWidth::SevenExtraLarge, '7xl' => 'max-w-7xl',
                    MaxWidth::Full, 'full' => 'max-w-full',
                    MaxWidth::MinContent, 'min' => 'max-w-min',
                    MaxWidth::MaxContent, 'max' => 'max-w-max',
                    MaxWidth::FitContent, 'fit' => 'max-w-fit',
                    MaxWidth::Prose, 'prose' => 'max-w-prose',
                    MaxWidth::ScreenSmall, 'screen-sm' => 'max-w-screen-sm',
                    MaxWidth::ScreenMedium, 'screen-md' => 'max-w-screen-md',
                    MaxWidth::ScreenLarge, 'screen-lg' => 'max-w-screen-lg',
                    MaxWidth::ScreenExtraLarge, 'screen-xl' => 'max-w-screen-xl',
                    MaxWidth::ScreenTwoExtraLarge, 'screen-2xl' => 'max-w-screen-2xl',
                    default => $maxWidth,
                },
            ])>
                {{ $slot }}
            </main>
        </div>
        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::FOOTER, scopes: $livewire->getRenderHookScopes()) }}
    </div>
</x-filament-panels::layout.base>
