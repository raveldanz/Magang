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
            Pendaftaran Akun Magang
        </h2>
        <p class="text-xs font-bold uppercase tracking-wider text-blue-700 mt-1">
            Pemerintah Kota Surabaya
        </p>
        <p class="text-xs text-slate-500 mt-1">
            Buat akun untuk mengajukan magang di instansi Pemkot Surabaya
        </p>
    </div>

    <div class="bg-white py-8 px-6 shadow-xl shadow-slate-200/60 rounded-3xl border border-slate-100 sm:px-10">
        <form method="POST" action="{{ route('register') }}" class="space-y-4">
            @csrf

            <!-- Name -->
            <div>
                <x-input-label for="name" :value="__('Nama Lengkap (Sesuai KTP/KTM)')" class="font-bold text-slate-700 text-xs uppercase tracking-wider" />
                <x-text-input id="name" class="block mt-1.5 w-full rounded-xl border-slate-200 focus:border-blue-600 focus:ring-blue-600 text-xs sm:text-sm shadow-2xs py-2.5 px-3.5 transition" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" placeholder="Nama lengkap Anda" />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <!-- Email Address -->
            <div>
                <x-input-label for="email" :value="__('Alamat Email Aktif')" class="font-bold text-slate-700 text-xs uppercase tracking-wider" />
                <x-text-input id="email" class="block mt-1.5 w-full rounded-xl border-slate-200 focus:border-blue-600 focus:ring-blue-600 text-xs sm:text-sm shadow-2xs py-2.5 px-3.5 transition" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="nama@email.com" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <!-- Password -->
            <div>
                <x-input-label for="password" :value="__('Kata Sandi')" class="font-bold text-slate-700 text-xs uppercase tracking-wider" />
                <x-text-input id="password" class="block mt-1.5 w-full rounded-xl border-slate-200 focus:border-blue-600 focus:ring-blue-600 text-xs sm:text-sm shadow-2xs py-2.5 px-3.5 transition"
                                type="password"
                                name="password"
                                required autocomplete="new-password" placeholder="Minimal 8 karakter" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <!-- Confirm Password -->
            <div>
                <x-input-label for="password_confirmation" :value="__('Konfirmasi Kata Sandi')" class="font-bold text-slate-700 text-xs uppercase tracking-wider" />
                <x-text-input id="password_confirmation" class="block mt-1.5 w-full rounded-xl border-slate-200 focus:border-blue-600 focus:ring-blue-600 text-xs sm:text-sm shadow-2xs py-2.5 px-3.5 transition"
                                type="password"
                                name="password_confirmation" required autocomplete="new-password" placeholder="Ulangi kata sandi" />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>

            <!-- Submit Button -->
            <div class="pt-2">
                <button type="submit"
                    class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-md shadow-blue-600/20 text-xs sm:text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 ease-in-out uppercase tracking-wider cursor-pointer">
                    {{ __('Daftar Akun Magang') }}
                </button>
            </div>
        </form>

        <div class="mt-6 pt-5 border-t border-slate-100 text-center">
            <p class="text-xs text-slate-500">
                Sudah memiliki akun?
                <a href="{{ route('login') }}" class="font-bold text-blue-600 hover:text-blue-800 ml-1">
                    Masuk ke portal
                </a>
            </p>
        </div>
    </div>

    <!-- Footer Tagline -->
    <p class="text-center text-xs text-slate-400 mt-6">
        &copy; {{ date('Y') }} Pemerintah Kota Surabaya. All rights reserved.
    </p>
</x-guest-layout>
