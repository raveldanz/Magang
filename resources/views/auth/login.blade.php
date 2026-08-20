<x-guest-layout>
    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center mb-6">
        <!-- Logo Pemkot Surabaya -->
        <div class="flex justify-center mb-4">
            <img src="{{ asset('images/logoPemkotSBY.png') }}" 
                 alt="Logo Pemkot Surabaya"
                 class="h-20 w-auto object-contain drop-shadow-md hover:scale-105 transition-transform duration-300">
        </div>

        <!-- Header Text -->
        <h2 class="text-2xl font-extrabold text-gray-900 tracking-tight">
            Portal Magang Mahasiswa
        </h2>
        <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600 mt-1">
            Pemerintah Kota Surabaya
        </p>
        <p class="text-xs text-gray-500 mt-1">
            Silakan masuk untuk mengelola logbook & pengajuan magang
        </p>
    </div>

    <div class="bg-white py-8 px-6 shadow-xl shadow-gray-100/70 rounded-2xl border border-gray-100 sm:px-10">
        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            <!-- Email Address -->
            <div>
                <x-input-label for="email" value="{{ __('Email') }}"
                    class="font-semibold text-gray-700 text-xs uppercase tracking-wider" />
                <x-text-input id="email"
                    class="block mt-1.5 w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm shadow-sm py-2.5 px-3.5 transition"
                    type="email" name="email" :value="old('email')" required autofocus autocomplete="username"
                    placeholder="nama@email.com" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <!-- Password -->
            <div>
                <div class="flex items-center justify-between">
                    <x-input-label for="password" value="{{ __('Password') }}"
                        class="font-semibold text-gray-700 text-xs uppercase tracking-wider" />
                    @if (Route::has('password.request'))
                        <a class="text-xs text-indigo-600 hover:text-indigo-800 font-medium transition"
                            href="{{ route('password.request') }}">
                            {{ __('Lupa password?') }}
                        </a>
                    @endif
                </div>

                <x-text-input id="password"
                    class="block mt-1.5 w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm shadow-sm py-2.5 px-3.5 transition"
                    type="password" name="password" required autocomplete="current-password" placeholder="••••••••" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <!-- Remember Me -->
            <div class="flex items-center">
                <input id="remember_me" type="checkbox"
                    class="rounded-md border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 w-4 h-4 cursor-pointer"
                    name="remember">
                <label for="remember_me" class="ml-2 text-xs text-gray-600 font-medium cursor-pointer select-none">
                    {{ __('Ingat saya di perangkat ini') }}
                </label>
            </div>

            <!-- Submit Button -->
            <div class="pt-2">
                <button type="submit"
                    class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-xl shadow-md text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out uppercase tracking-wider">
                    {{ __('Masuk ke Akun') }}
                </button>
            </div>
        </form>

        @if (Route::has('register'))
            <div class="mt-6 pt-5 border-t border-gray-100 text-center">
                <p class="text-xs text-gray-500">
                    Belum punya akun magang?
                    <a href="{{ route('register') }}" class="font-bold text-indigo-600 hover:text-indigo-800 ml-1">
                        Daftar sekarang
                    </a>
                </p>
            </div>
        @endif
    </div>

    <!-- Footer Tagline -->
    <p class="text-center text-xs text-gray-400 mt-6">
        &copy; {{ date('Y') }} Pemerintah Kota Surabaya. All rights reserved.
    </p>
</x-guest-layout>