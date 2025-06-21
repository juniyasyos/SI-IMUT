@php
    $quotes = [
        // Motivasi kerja
        'Kerja keras hari ini, payday bahagia nanti.',
        'Bangun, kerja, kopi, ulangi!',
        'Jangan takut gagal, takutlah kalau nggak ngopi dulu.',
        'Bekerjalah seolah-olah kamu suka. Bahkan kalau cuma akting.',
        'Sukses itu 1% inspirasi, 99% ngopi sambil mikir.',
        'Setiap detik di kantor adalah detik mendekat ke akhir bulan (dan gajian).',
        'Lelah bekerja lebih baik daripada lelah rebahan tapi stres.',
        'Berhenti scroll, mulai kerja. Bos ngeliatin tuh.',
        'Fokuslah pada progres, bukan kesempurnaan.',
        'Satu tugas selesai lebih baik daripada sepuluh wacana.',
        'Tiap kerja keras hari ini adalah bekal bahagia esok hari.',
        'Kegagalan bukan lawan sukses, tapi bagian dari proses.',
        'Kalau kamu lelah, itu tandanya kamu sedang berjuang.',
        'Mulailah hari ini dengan niat baik dan langkah pasti.',
        'Rencana tanpa aksi itu cuma mimpi.',
        'Kerja cerdas + kerja ikhlas = kerja berkelas.',

        // Komedi ringan, tetap sopan
        'Motivasi hari ini: jangan tidur di depan HRD.',
        'Multitasking level: kerja sambil mikir makan siang.',
        'Senin bukan musuhmu, hanya pengingat bahwa weekend sudah selesai.',
        'Jangan sedih... kerjaan kamu tetap setia nungguin kok.',
        'Selalu ingat: kopi dan to-do list adalah sahabat sejati.',
        'Jangan kebanyakan meeting, nanti lupa ngoding.',
        'Kerja itu penting, tapi jangan lupa senyum juga.',
        'Kadang semangat datang bukan dari target, tapi dari aroma nasi goreng tetangga.',
        'Kalau hari ini capek, ingat: besok Jumat (semoga).',
        'Kamu nggak sendiri... kita semua juga nunggu jam pulang.',
        'Motivasi kantor: refill air galon biar bisa ketemu orang baru.',
        'Jangan takut gagal, takutlah kalau gak ada sinyal WiFi.',

        // Penyemangat ringan
        'Langkah kecil hari ini bisa jadi lompatan besar bulan depan.',
        'Hari ini mungkin berat, tapi kamu lebih kuat.',
        'Semangat! Gagal itu proses, bukan akhir.',
        'Ngantuk boleh, nyerah jangan.',
        'Jangan tunggu mood datang — mulai dulu, mood bakal nyusul.',
        'Percaya proses, hasil akan menyusul.',
        'Jadilah versi terbaik dirimu, bukan tiruan orang lain.',
        'Waktu terus berjalan, tapi kamu juga bisa terus berkembang.',
        'Setiap hari adalah kesempatan baru untuk melangkah lebih baik.',
        'Semangat itu bukan bawaan lahir — kadang butuh diingatkan.',
    ];

    // Buat random harian tapi konsisten: misalnya dari hari ke-berapa dalam tahun ini
    // $dayOfYear = date('z');
    // $index = $dayOfYear % count($quotes);
    // $quote = $quotes[$index];
    $quote = $quotes[array_rand($quotes)];
@endphp

<header class="flex flex-col items-start fi-simple-header">
    @if (filled($heading))
        <h1 class="text-2xl font-bold tracking-tight text-center fi-simple-header-heading text-gray-950 dark:text-white">
            {{ $heading }}
        </h1>

        <p class="mb-5 text-base text-gray-600 fi-simple-header-subheading text-start dark:text-gray-300">
            {{ $quote }}
        </p>
    @endif

    @if (filled($subheading))
        <p class="mt-2 text-sm text-center text-gray-500 fi-simple-header-subheading dark:text-gray-400">
            {{ $subheading }}
        </p>
    @endif
</header>
