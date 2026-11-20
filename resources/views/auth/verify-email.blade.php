<x-guest-layout>
    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center mb-6">
        <!-- Logo Pemkot Surabaya & Dispusip -->
        <div class="flex items-center justify-center gap-3 mb-4">
            <a href="/" class="group transition-transform duration-300 hover:scale-105 inline-block focus:outline-none" title="Pemerintah Kota Surabaya">
                <img src="{{ asset('images/logos/surabaya.png') }}" alt="Logo Pemkot Surabaya"
                    class="h-14 w-auto object-contain drop-shadow-2xs transition-all"
                    style="aspect-ratio: 960/1234; height: 56px; width: auto; max-height: 60px; object-fit: contain;">
            </a>
            <div class="h-8 w-px bg-slate-200"></div>
            <a href="/" class="group transition-transform duration-300 hover:scale-105 inline-block focus:outline-none" title="Dinas Perpustakaan dan Kearsipan Kota Surabaya">
                <img src="{{ asset('images/logos/dispusip.png') }}" alt="Logo Dispusip Surabaya"
                    class="h-14 w-auto object-contain drop-shadow-2xs transition-all"
                    style="height: 56px; width: auto; max-height: 60px; object-fit: contain;">
            </a>
        </div>

        <!-- Header Text -->
        <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
            Verifikasi Alamat Email
        </h2>
        <p class="text-xs font-bold uppercase tracking-wider text-blue-700 mt-1">
            Dinas Perpustakaan dan Kearsipan Kota Surabaya
        </p>
    </div>

    <div class="bg-white py-7 px-5 sm:py-8 sm:px-9 shadow-xl shadow-slate-200/60 rounded-3xl border border-slate-200/80 space-y-5">
        <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
            Terima kasih telah mendaftar di Portal Magang Dispusip. Sebelum mulai menggunakan layanan, silakan verifikasi alamat email Anda melalui tautan yang telah kami kirimkan. Jika belum menerima email, Anda dapat meminta kirim ulang di bawah ini.
        </p>

        @if (session('status') == 'verification-link-sent')
            <div class="rounded-xl bg-emerald-50 border border-emerald-200 px-3.5 py-3 text-xs sm:text-sm font-medium text-emerald-800 leading-relaxed">
                Tautan verifikasi baru telah dikirim ke alamat email yang Anda gunakan saat pendaftaran.
            </div>
        @endif

        <div class="flex flex-col gap-2.5 pt-1">
            <form method="POST" action="{{ route('verification.send') }}" class="w-full">
                @csrf
                <x-primary-button class="w-full min-h-[46px] sm:min-h-[44px] justify-center uppercase tracking-wider">
                    Kirim Ulang Email Verifikasi
                </x-primary-button>
            </form>

            <form method="POST" action="{{ route('logout') }}" class="w-full">
                @csrf
                <button type="submit"
                    class="w-full min-h-[44px] inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-white hover:bg-slate-50 active:bg-slate-100 border border-slate-300 text-slate-700 text-xs sm:text-sm font-bold rounded-xl shadow-2xs transition cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Keluar Akun
                </button>
            </form>
        </div>
    </div>

    <!-- Footer Tagline -->
    <p class="text-center text-xs text-slate-500 mt-6">
        &copy; {{ date('Y') }} Dinas Perpustakaan dan Kearsipan Kota Surabaya. Hak Cipta Dilindungi.
    </p>
</x-guest-layout>
