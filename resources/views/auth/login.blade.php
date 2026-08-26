<x-guest-layout>
    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center mb-6">
        <!-- Logo Pemkot Surabaya -->
        <div class="flex justify-center mb-4">
            <a href="/" class="group transition-transform duration-300 hover:scale-105 inline-block">
                <img src="{{ asset('images/logos/surabaya.png') }}" 
                     alt="Logo Pemkot Surabaya"
                     class="h-20 w-auto object-contain drop-shadow-md"
                     style="height: 72px; width: auto; max-height: 80px;">
            </a>
        </div>

        <!-- Header Text -->
        <h2 class="text-2xl font-black text-slate-900 tracking-tight">
            Portal Magang MBKM
        </h2>
        <p class="text-xs font-bold uppercase tracking-wider text-blue-700 mt-1">
            Pemerintah Kota Surabaya
        </p>
        <p class="text-xs text-slate-500 mt-1">
            Silakan masuk untuk mengakses panel kendali & layanan magang
        </p>
    </div>

    <div class="bg-white py-8 px-6 shadow-xl shadow-slate-200/60 rounded-3xl border border-slate-100 sm:px-10">
        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf

            <!-- Email Address -->
            <div>
                <x-input-label for="email" value="{{ __('Email Resmi / Akun') }}"
                    class="font-bold text-slate-700 text-xs uppercase tracking-wider" />
                <x-text-input id="email"
                    class="block mt-1.5 w-full rounded-xl border-slate-200 focus:border-blue-600 focus:ring-blue-600 text-xs sm:text-sm shadow-2xs py-2.5 px-3.5 transition"
                    type="email" name="email" :value="old('email')" required autofocus autocomplete="username"
                    placeholder="nama@surabaya.go.id / email pendaftar" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <!-- Password -->
            <div>
                <div class="flex items-center justify-between">
                    <x-input-label for="password" value="{{ __('Kata Sandi') }}"
                        class="font-bold text-slate-700 text-xs uppercase tracking-wider" />
                    @if (Route::has('password.request'))
                        <a class="text-xs text-blue-600 hover:text-blue-800 font-semibold transition"
                            href="{{ route('password.request') }}">
                            {{ __('Lupa password?') }}
                        </a>
                    @endif
                </div>

                <x-text-input id="password"
                    class="block mt-1.5 w-full rounded-xl border-slate-200 focus:border-blue-600 focus:ring-blue-600 text-xs sm:text-sm shadow-2xs py-2.5 px-3.5 transition"
                    type="password" name="password" required autocomplete="current-password" placeholder="••••••••" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <!-- Remember Me -->
            <div class="flex items-center">
                <input id="remember_me" type="checkbox"
                    class="rounded-md border-slate-300 text-blue-600 shadow-2xs focus:ring-blue-500 w-4 h-4 cursor-pointer"
                    name="remember">
                <label for="remember_me" class="ml-2 text-xs text-slate-600 font-medium cursor-pointer select-none">
                    {{ __('Ingat saya di perangkat ini') }}
                </label>
            </div>

            <!-- Submit Button -->
            <div class="pt-2">
                <button type="submit"
                    class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-md shadow-blue-600/20 text-xs sm:text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 ease-in-out uppercase tracking-wider cursor-pointer">
                    {{ __('Masuk ke Portal') }}
                </button>
            </div>
        </form>

        @if (Route::has('register'))
            <div class="mt-6 pt-5 border-t border-slate-100 text-center">
                <p class="text-xs text-slate-500">
                    Belum punya akun pendaftaran magang?
                    <a href="{{ route('register') }}" class="font-bold text-blue-600 hover:text-blue-800 ml-1">
                        Daftar sekarang
                    </a>
                </p>
            </div>
        @endif
    </div>

    <!-- Footer Tagline -->
    <p class="text-center text-xs text-slate-400 mt-6">
        &copy; {{ date('Y') }} Pemerintah Kota Surabaya. All rights reserved.
    </p>
</x-guest-layout>