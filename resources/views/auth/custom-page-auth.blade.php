<div>
    {{-- Background Image --}}
    <div class="absolute inset-0">
        <img src="{{ asset('images/assets/133884796741265059.jpg') }}" alt="Background" class="object-cover w-full h-full">
    </div>

    <div class="relative items-center justify-center flex-1 hidden h-screen bg-gray-900 lg:flex">
        {{-- Darker Gradient Overlay --}}
        <div class="absolute inset-0 z-0 bg-gradient-to-r from-black/90 to-black/70"></div>

        {{-- Text Content --}}
        <div class="relative z-10 w-full max-w-2xl"
            style="margin-top: 250px; background-color: rgba(0, 0, 0, 0.6); padding: 40px; border-radius: 12px; backdrop-filter: blur(4px); padding-bottom:200px; padding-top: 100px">
            <div class="space-y-3">
                <h3 class="text-3xl font-bold text-white">
                    SI-IMUT
                </h3>
                <p class="text-gray-300">
                    Sistem Informasi Indikator Mutu (SI IMUT) menyediakan data terkini dan analisis kualitas layanan
                    untuk meningkatkan mutu di berbagai unit.
                </p>
                <div class="flex items-center -space-x-2 overflow-hidden">
                    <p class="italic font-medium text-white text-md">
                        {{ setting('site_author') }}
                    </p>
                </div>
            </div>
        </div>

    </div>
</div>
