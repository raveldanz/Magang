<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf
    
        <!-- Logo Pemkot Surabaya -->
        <div class="flex justify-center mb-4">
            <img src="{{ asset('images/logoPemkotSBY.png') }}" 
                 alt="Logo Pemkot Surabaya"
                 class="h-20 w-auto object-contain drop-shadow-md hover:scale-105 transition-transform duration-300">
        </div>
        <!-- Header Text -->
        <h2 class="text-2xl font-extrabold text-gray-900 tracking-tight text-center">
            Portal Magang Mahasiswa
        </h2>
        <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600 mt-1 text-center">
            Pemerintah Kota Surabaya
        </p>
        <p class="text-xs text-gray-500 mt-1 text-center">
            Silakan daftar terlebih dahulu untuk masuk ke menu selanjutnya
        </p>


        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Nama')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Konfirmasi Password')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                {{ __('Sudah daftar?') }}
            </a>

            <x-primary-button class="ms-4 flex justify-center py-2.5 px-4 border border-transparent rounded-xl shadow-md text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out uppercase tracking-wide">
                {{ __('Daftar') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
