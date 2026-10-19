<x-guest-layout>
    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center mb-6">
        <!-- Logo Pemkot Surabaya & Dispusip -->
        <div class="flex items-center justify-center gap-3 mb-4">
            <a href="/" class="group transition-transform duration-300 hover:scale-105 inline-block focus:outline-none" title="Pemerintah Kota Surabaya">
                <img src="{{ asset('images/logos/surabaya.png') }}" 
                     alt="Logo Pemkot Surabaya"
                     class="h-14 w-auto object-contain drop-shadow-2xs transition-all"
                     style="aspect-ratio: 960/1234; height: 56px; width: auto; max-height: 60px; object-fit: contain;">
            </a>
            <div class="h-8 w-px bg-slate-200"></div>
            <a href="/" class="group transition-transform duration-300 hover:scale-105 inline-block focus:outline-none" title="Dinas Perpustakaan dan Kearsipan Kota Surabaya">
                <img src="{{ asset('images/logos/dispusip.png') }}" 
                     alt="Logo Dispusip Surabaya"
                     class="h-14 w-auto object-contain drop-shadow-2xs transition-all"
                     style="height: 56px; width: auto; max-height: 60px; object-fit: contain;">
            </a>
        </div>

        <!-- Header Text -->
        <h2 class="text-2xl font-black text-slate-900 tracking-tight">
            Pendaftaran Magang Dispusip
        </h2>
        <p class="text-xs font-bold uppercase tracking-wider text-blue-700 mt-1">
            Dinas Perpustakaan dan Kearsipan Kota Surabaya
        </p>
        <p class="text-xs text-slate-500 mt-1">
            Buat akun untuk mengajukan magang di Dinas Perpustakaan dan Kearsipan
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
                <div class="relative mt-1.5" x-data="{ show: false }">
                    <input id="password"
                        class="block w-full rounded-xl border-slate-200 focus:border-blue-600 focus:ring-blue-600 text-xs sm:text-sm shadow-2xs py-2.5 pl-3.5 pr-10 transition"
                        :type="show ? 'text' : 'password'"
                        type="password"
                        name="password"
                        required
                        autocomplete="new-password"
                        placeholder="Minimal 8 karakter" />
                    <button type="button"
                        @click="show = !show"
                        class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none transition cursor-pointer"
                        :aria-label="show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
                        :title="show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
                        tabindex="-1">
                        <svg x-show="!show" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg x-show="show" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                        </svg>
                    </button>
                </div>
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <!-- Confirm Password -->
            <div>
                <x-input-label for="password_confirmation" :value="__('Konfirmasi Kata Sandi')" class="font-bold text-slate-700 text-xs uppercase tracking-wider" />
                <div class="relative mt-1.5" x-data="{ show: false }">
                    <input id="password_confirmation"
                        class="block w-full rounded-xl border-slate-200 focus:border-blue-600 focus:ring-blue-600 text-xs sm:text-sm shadow-2xs py-2.5 pl-3.5 pr-10 transition"
                        :type="show ? 'text' : 'password'"
                        type="password"
                        name="password_confirmation"
                        required
                        autocomplete="new-password"
                        placeholder="Ulangi kata sandi" />
                    <button type="button"
                        @click="show = !show"
                        class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none transition cursor-pointer"
                        :aria-label="show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
                        :title="show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
                        tabindex="-1">
                        <svg x-show="!show" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg x-show="show" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                        </svg>
                    </button>
                </div>
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
        &copy; {{ date('Y') }} Dinas Perpustakaan dan Kearsipan Kota Surabaya. Hak Cipta Dilindungi.
    </p>
</x-guest-layout>
