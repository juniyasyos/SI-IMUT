@php
    use Carbon\Carbon;

    $user = filament()->auth()->user();
    $hour = now()->format('H');

    $greeting = match (true) {
        $hour < 11 => 'Selamat pagi',
        $hour < 15 => 'Selamat siang',
        $hour < 18 => 'Selamat sore',
        default => 'Selamat malam',
    };

    $quotesByTime = [
        'pagi' => [
            'Selamat pagi! Saatnya buka semangat dengan secangkir produktivitas.',
            'Hari baru, peluang baru. Ayo mulai dengan senyum!',
            'Pagi yang baik dimulai dari niat yang baik.',
            'Bangkit! Dunia tidak menunggu yang terlambat bangun.',
            'Kopi pertama hari ini bukan untuk dinikmati, tapi untuk bertahan.',
            'Mulai harimu dengan tekad, bukan keluhan.',
            'Jangan biarkan bantal jadi alasan tertinggal.',
            'Pagi adalah kesempatan kedua — gunakan sebaik mungkin.',
            'Semangatmu pagi ini menentukan ritme seharian.',
        ],
        'siang' => [
            'Selamat siang! Jangan lupa makan, tapi jangan makan waktu kerja juga.',
            'Siang-siang ngantuk itu biasa, semangat terus yang luar biasa.',
            'Waktu terbaik untuk menyelesaikan tugas adalah… sekarang.',
            'Jangan biarkan semangat pagi padam di siang hari.',
            'Segelas air putih dan tekad bisa menyelamatkan siangmu.',
            'Kalau mulai lelah, ingat: gajian makin dekat.',
            'Tetap fokus, setengah hari sudah terlewati!',
            'Tantangan siang hari? Hadapi, bukan hindari.',
            'Kerja bagus siang ini bikin malam tenang.',
        ],
        'sore' => [
            'Sore bukan alasan untuk menyerah, tapi jeda untuk melesat lagi.',
            'Sudah sejauh ini, tinggal sedikit lagi — ayo tuntaskan!',
            'Tenang, pulang sebentar lagi… tapi kerjaan jangan ditinggal dulu.',
            'Sore adalah saat yang tepat untuk refleksi dan resolusi.',
            'Jangan biarkan deadline mengalahkan niat baikmu.',
            'Lelah itu wajar, menyerah bukan pilihan.',
            'Bekerja dengan hati — hasilnya lebih nikmat dari kopi.',
            'Sore yang produktif = malam yang damai.',
            'Fokus terakhir sebelum layar dimatikan!',
        ],
        'malam' => [
            'Selamat malam! Evaluasi hari ini, rancang esok hari.',
            'Waktunya tenang sejenak, recharge sebelum perang esok.',
            'Hari ini mungkin melelahkan, tapi kamu luar biasa.',
            'Senyum sebelum tidur, besok mulai lagi dengan lebih kuat.',
            'Tidur itu juga bagian dari produktivitas.',
            'Istirahat yang baik = esok yang hebat.',
            'Kalahkan penyesalan hari ini dengan perencanaan malam ini.',
            'Lupakan stres, ingat progres.',
            'Hargai dirimu — kamu sudah berjuang hari ini.',
        ],
    ];

    $timeKey = match (true) {
        $hour < 11 => 'pagi',
        $hour < 15 => 'siang',
        $hour < 18 => 'sore',
        default => 'malam',
    };

    $quotes = $quotesByTime[$timeKey];
    $quote = $quotes[array_rand($quotes)];
@endphp

<x-filament-widgets::widget class="fi-account-widget">
    <x-filament::section>
        <div class="flex items-center gap-x-3">
            <x-filament-panels::avatar.user size="lg" :user="$user" />

            <div class="flex-1">
                <h2 class="text-base font-semibold leading-6 text-gray-950 dark:text-white">
                    {{ $greeting }}, {{ filament()->getUserName($user) }} 👋
                </h2>

                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ $quote }}
                </p>
            </div>

            <form action="{{ filament()->getLogoutUrl() }}" method="post" class="my-auto">
                @csrf

                <x-filament::button color="gray" icon="heroicon-m-arrow-left-on-rectangle"
                    icon-alias="panels::widgets.account.logout-button" labeled-from="sm" tag="button" type="submit">
                    {{ __('Keluar') }}
                </x-filament::button>
            </form>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
