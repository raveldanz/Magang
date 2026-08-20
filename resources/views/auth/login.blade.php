<x-guest-layout>
    <!-- Brand Header -->
    <div class="text-center mb-6">
        <div class="inline-flex p-3 rounded-2xl bg-white border border-slate-100 shadow-sm shadow-slate-200/50 mb-3 transition-all duration-200 ease-in-out hover:scale-[1.02] hover:shadow-md hover:shadow-blue-100">
            <img src="{{ asset('images/logoPemkotSBY.png') }}" 
                 alt="Logo Pemkot Surabaya" 
                 class="h-14 w-auto object-contain">
        </div>
        
        <h2 class="text-2xl font-bold tracking-tight text-slate-900">
            Portal Magang Mahasiswa
        </h2>
        <div class="mt-1.5 flex items-center justify-center">
            <span class="inline-flex items-center px-3 py-0.5 rounded-full text-xs font-semibold uppercase tracking-wider bg-blue-50 text-blue-600 border border-blue-100">
                Pemerintah Kota Surabaya
            </span>
        </div>
        <p class="text-xs text-slate-500 mt-2">
            Silakan masuk untuk mengelola logbook & pengajuan magang
        </p>
    </div>

    <!-- Login Card Surface -->
    <div class="bg-white p-7 sm:p-8 rounded-2xl border border-slate-100 shadow-sm shadow-slate-200/50">
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf

            <!-- Email Address -->
            <div>
                <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">
                    Email Address
                </label>
                <input id="email" 
                       type="email" 
                       name="email" 
                       value="{{ old('email') }}" 
                       required 
                       autofocus 
                       autocomplete="username" 
                       placeholder="nama@email.com" 
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 text-slate-900 text-sm px-3.5 py-2.5 placeholder-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-400/40 focus:border-blue-400 transition-all duration-200 ease-in-out" />
                <x-input-error :messages="$errors->get('email')" class="mt-1 text-xs" />
            </div>

            <!-- Password -->
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-500">
                        Password
                    </label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-xs text-blue-600 hover:text-blue-700 font-medium transition-all duration-200">
                            Lupa password?
                        </a>
                    @endif
                </div>
                <input id="password" 
                       type="password" 
                       name="password" 
                       required 
                       autocomplete="current-password" 
                       placeholder="••••••••" 
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 text-slate-900 text-sm px-3.5 py-2.5 placeholder-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-400/40 focus:border-blue-400 transition-all duration-200 ease-in-out" />
                <x-input-error :messages="$errors->get('password')" class="mt-1 text-xs" />
            </div>

            <!-- Remember Me -->
            <div class="flex items-center">
                <input id="remember_me" 
                       type="checkbox" 
                       name="remember" 
                       class="rounded-md border-slate-300 text-blue-600 shadow-sm focus:ring-blue-400 w-4 h-4 cursor-pointer">
                <label for="remember_me" class="ml-2 text-xs text-slate-600 font-medium cursor-pointer select-none">
                    Ingat saya di perangkat ini
                </label>
            </div>

            <!-- Action Button -->
            <div class="pt-2">
                <button type="submit" class="w-full rounded-xl px-4 py-2.5 font-semibold text-sm text-white bg-blue-600 hover:bg-blue-700 active:bg-blue-800 shadow-sm shadow-blue-200 transition-all duration-200 ease-in-out hover:scale-[1.01] active:scale-[0.99]">
                    Masuk ke Akun
                </button>
            </div>
        </form>

        @if (Route::has('register'))
            <div class="mt-6 pt-5 border-t border-slate-100 text-center">
                <p class="text-xs text-slate-500">
                    Belum punya akun magang?
                    <a href="{{ route('register') }}" class="font-semibold text-blue-600 hover:text-blue-700 ml-1 transition">
                        Daftar sekarang
                    </a>
                </p>
            </div>
        @endif
    </div>

    <!-- Footer Note -->
    <p class="text-center text-xs text-slate-400 mt-6 tracking-wide">
        &copy; {{ date('Y') }} Pemerintah Kota Surabaya. All rights reserved.
    </p>
</x-guest-layout>