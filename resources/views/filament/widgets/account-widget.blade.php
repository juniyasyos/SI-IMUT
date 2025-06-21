@php
    use Carbon\Carbon;
    use Illuminate\Support\Str;

    $user = filament()->auth()->user();
    $displayName = Str::limit(filament()->getUserName($user), 20);
    $hour = now()->format('H');

    $greeting = match (true) {
        $hour < 11 => 'Selamat pagi',
        $hour < 15 => 'Selamat siang',
        $hour < 18 => 'Selamat sore',
        default => 'Selamat malam',
    };

    $quotesByTime = [
        'dini' => [
            'Malam masih panjang. Tidur sebentar bukan lemah, itu bijak.',
            'Kalau kamu masih kerja jam segini, semoga karena passion — bukan tekanan.',
            'Tubuh juga butuh istirahat. Jangan paksakan kalau mata sudah berat.',
            'Kejar mimpi itu bagus. Tapi jangan lupakan tidur — itu bagian dari mimpi juga.',
            'Jam segini bukan tentang siapa paling kuat, tapi siapa paling sadar diri.',
            'Ketenangan malam seharusnya jadi pelukan, bukan tekanan.',
            'Semua yang kamu kejar masih akan ada besok. Tapi kesehatanmu tidak menunggu.',
            'Diamnya malam bukan alasan untuk terus menyiksa diri.',
            'Subuh masih lama — gunakan sisa waktu ini untuk pulih, bukan menambah beban.',
            'Jika dunia sedang tidur, mungkin kamu juga perlu mengikutinya.',
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
        'larut' => [
            'Sudah lebih dari jam 10 malam, istirahatlah sejenak. Besok masih ada hari.',
            'Tidur sekarang lebih baik daripada menyesal besok pagi.',
            'Produktif itu baik, tapi istirahat itu penting.',
            'Jangan korbankan kesehatan demi menyelesaikan sesuatu yang bisa ditunda.',
            'Tubuhmu butuh istirahat, bukan ambisi tanpa henti.',
            'Kalau kamu masih kerja, pastikan alasannya bukan karena pelarian.',
            'Jam 10 malam ke atas itu bukan waktu kerja, tapi waktu untuk pulih.',
            'Besok akan lebih ringan jika malam ini kamu tidur cukup.',
            'Kelelahan bukan lambang perjuangan, tapi tanda tubuh minta perhatian.',
            'Batas waktu itu penting — bukan hanya untuk tugas, tapi juga untuk dirimu sendiri.',
            'Pekerjaan tidak akan selesai lebih cepat dengan badan yang makin lemah.',
            'Hasil terbaik datang dari pikiran yang cukup tidur.',
            'Kalau semua orang sudah tidur, mungkin kamu juga perlu.',
            'Kerja lembur terus-menerus bukan pencapaian, itu kebiasaan yang harus dikaji ulang.',
            'Istirahat bukan buang waktu. Itu investasi untuk hari esok.',
            'Kadang produktif itu berarti tahu kapan harus berhenti.',
            'Jangan menukar tidurmu malam ini dengan penyesalan esok hari.',
            'Kalau sudah lebih dari jam 11 malam dan kamu masih bekerja, tanyakan: “Perlu atau hanya terbiasa?”',
            'Tidur itu bagian dari kerja — kerja merawat dirimu sendiri.',
            'Kamu bisa menyelesaikannya besok dengan kepala yang lebih segar.',
            'Jika kamu ingin konsisten, jangan abaikan istirahatmu.',
            'Malam itu untuk merenung, bukan untuk membakar diri sendiri.',
            'Waktu larut bukan bonus waktu kerja, tapi sinyal alami untuk berhenti.',
            'Badan lelah itu panggilan alam — dengarkan sebelum terlambat.',
            'Jangan biasakan menunda tidur untuk sesuatu yang tak mendesak.',
        ],
    ];

    $timeKey = match (true) {
        $hour >= 0 && $hour < 4 => 'dini',
        $hour < 11 => 'pagi',
        $hour < 15 => 'siang',
        $hour < 18 => 'sore',
        $hour < 22 => 'malam',
        default => 'larut',
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
                    {{ $greeting }}, {{ $displayName }} 👋
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
