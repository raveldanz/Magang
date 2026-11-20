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
            Atur Ulang Kata Sandi
        </h2>
        <p class="text-xs font-bold uppercase tracking-wider text-blue-700 mt-1">
            Dinas Perpustakaan dan Kearsipan Kota Surabaya
        </p>
    </div>

    <div class="bg-white py-7 px-5 sm:py-8 sm:px-9 shadow-xl shadow-slate-200/60 rounded-3xl border border-slate-200/80">
        <p class="mb-5 text-xs sm:text-sm text-slate-600 leading-relaxed">
            Lupa kata sandi akun Anda? Masukkan alamat email yang terdaftar di bawah ini, lalu kami akan mengirimkan tautan untuk mengatur ulang kata sandi Anda.
        </p>

        <!-- Session Status -->
        <x-auth-session-status class="mb-4 rounded-xl bg-emerald-50 border border-emerald-200 px-3.5 py-2.5 text-emerald-800" :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
            @csrf

            <!-- Alamat Email -->
            <div>
                <x-input-label for="email" value="Alamat Email Terdaftar"
                    class="font-bold text-slate-700 text-xs uppercase tracking-wider" />
                <x-text-input id="email"
                    class="block mt-1.5 w-full min-h-[44px] text-sm"
                    type="email"
                    name="email"
                    :value="old('email')"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="nama@instansi.go.id / email akun" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div class="pt-1">
                <x-primary-button class="w-full min-h-[46px] sm:min-h-[44px] justify-center uppercase tracking-wider">
                    Kirim Tautan Atur Ulang
                </x-primary-button>
            </div>
        </form>

        <div class="mt-6 pt-5 border-t border-slate-100 text-center">
            <a href="{{ route('login') }}"
                class="inline-flex items-center justify-center gap-1.5 min-h-[40px] px-3 text-xs sm:text-sm font-bold text-blue-600 hover:text-blue-800 rounded-xl hover:bg-blue-50/70 transition">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Kembali ke Halaman Masuk</span>
            </a>
        </div>
    </div>

    <!-- Footer Tagline -->
    <p class="text-center text-xs text-slate-500 mt-6">
        &copy; {{ date('Y') }} Dinas Perpustakaan dan Kearsipan Kota Surabaya. Hak Cipta Dilindungi.
    </p>
</x-guest-layout>
