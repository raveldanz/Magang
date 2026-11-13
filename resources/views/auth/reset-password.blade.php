<x-guest-layout>
    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Alamat Email -->
        <div>
            <x-input-label for="email" value="Alamat Email" class="text-sm font-semibold text-slate-800" />
            <x-text-input id="email" class="block mt-1.5 w-full min-h-[44px] text-sm sm:text-base" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Kata Sandi Baru -->
        <div class="mt-4">
            <x-input-label for="password" value="Kata Sandi Baru" class="text-sm font-semibold text-slate-800" />
            <div class="relative mt-1.5" x-data="{ show: false }">
                <input id="password"
                    class="block w-full min-h-[44px] rounded-lg border-slate-300 focus:border-blue-600 focus:ring-blue-600 shadow-sm py-2.5 pl-3.5 pr-11 text-sm sm:text-base"
                    :type="show ? 'text' : 'password'"
                    type="password"
                    name="password"
                    required
                    autocomplete="new-password" />
                <button type="button"
                    @click="show = !show"
                    class="absolute inset-y-0 right-0 px-3 flex items-center text-slate-600 hover:text-slate-900 focus:outline-none transition cursor-pointer"
                    :aria-label="show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
                    :title="show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
                    tabindex="-1">
                    <svg x-show="!show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <svg x-show="show" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                    </svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Konfirmasi Kata Sandi Baru -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" value="Konfirmasi Kata Sandi Baru" class="text-sm font-semibold text-slate-800" />
            <div class="relative mt-1.5" x-data="{ show: false }">
                <input id="password_confirmation"
                    class="block w-full min-h-[44px] rounded-lg border-slate-300 focus:border-blue-600 focus:ring-blue-600 shadow-sm py-2.5 pl-3.5 pr-11 text-sm sm:text-base"
                    :type="show ? 'text' : 'password'"
                    type="password"
                    name="password_confirmation"
                    required
                    autocomplete="new-password" />
                <button type="button"
                    @click="show = !show"
                    class="absolute inset-y-0 right-0 px-3 flex items-center text-slate-600 hover:text-slate-900 focus:outline-none transition cursor-pointer"
                    :aria-label="show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
                    :title="show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
                    tabindex="-1">
                    <svg x-show="!show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <svg x-show="show" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                    </svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-5">
            <x-primary-button class="min-h-[44px]">
                Simpan Kata Sandi Baru
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
