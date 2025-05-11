@php
    $user = filament()->auth()->user();
    $greetings = [
        'Hari baru, semangat baru! Tetap sehat dan terus berkembang! 💪',
        'Jaga kesehatan, tetap semangat, dan raih impianmu hari ini! 🚀',
        'Setiap langkah kecil membawa perubahan besar. Ayo mulai harimu dengan energi positif! 🌞',
        'Kesuksesan dimulai dari tubuh yang sehat dan pikiran yang kuat. Kamu luar biasa! 🌟',
        'Hari ini adalah kesempatan baru untuk menjadi versi terbaik dari dirimu! ✨',
        'Sehat itu investasi jangka panjang. Jangan lupa tersenyum dan bersyukur! 😊',
        'Jangan menyerah, semua butuh proses! Satu langkah kecil lebih baik daripada diam. 🔥',
        'Semangat pagi! Ingat, kebahagiaan dimulai dari hati yang sehat dan pikiran yang jernih. 💖',
        'Hidup sehat, hati bahagia! Mulai hari ini dengan penuh semangat dan syukur. 🌿',
        'Tetap fokus pada tujuanmu, tetap sehat, dan tetap bersinar! 🌟',
    ];
    $randomGreeting = $greetings[array_rand($greetings)];
@endphp

<x-filament-widgets::widget class="fi-account-widget">
    <x-filament::section>
        <div class="flex items-start gap-x-4 p-4">
            {{-- <x-filament-panels::avatar.user size="xl" :user="$user" /> --}}
            <div class="flex-grow text-center md:text-left">
                <h3 class="text-xl font-bold heading">Welcome Back</h3>
                {{-- <div class="flex space-x-2 items-center">
                    <x-heroicon-m-calendar class="size-5" />
                    <p class="text-sm">{{ \Carbon\Carbon::now()->format('F d, Y') }}</p>
                </div> --}}
                <p class="text-sm">{{ $user->name }}</p>
                <div class="mb-2 text-sm font-semibold text-gray-700 dark:text-gray-300">
                    {{ trans($randomGreeting) }}
                </div>

                <div class="mt-4 flex flex-wrap gap-1">
                    {{-- Looping untuk menampilkan semua role yang dimiliki user --}}
                    @foreach ($user->roles as $role)
                        <span style="--c-50:var(--warning-50);--c-400:var(--warning-400);--c-600:var(--warning-600);"
                            class="fi-badge rounded-md text-xs font-medium ring-1 ring-inset px-2 py-1 fi-color-custom
                            bg-custom-50 text-custom-600 ring-custom-600/10 dark:bg-custom-400/10 dark:text-custom-400 dark:ring-custom-400/30 fi-color-warning">
                            {{ trans($role->name) }}
                        </span>
                    @endforeach
                </div>
            </div>
        </div>

        <form action="{{ filament()->getLogoutUrl() }}" method="post" class="my-auto flex justify-end">
            @csrf

            <x-filament::button color="gray" icon="heroicon-m-arrow-left-on-rectangle"
                icon-alias="panels::widgets.account.logout-button" labeled-from="sm" tag="button" type="submit">
                {{ trans('filament-panels::widgets/account-widget.actions.logout.label') }}
            </x-filament::button>
        </form>
    </x-filament::section>
</x-filament-widgets::widget>
