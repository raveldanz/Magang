<x-guest-layout>
    <div class="mb-5 text-sm sm:text-base text-slate-700 leading-relaxed">
        Lupa kata sandi akun Anda? Masukkan alamat email yang terdaftar di bawah ini, lalu kami akan mengirimkan tautan untuk mengatur ulang kata sandi Anda.
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf

        <!-- Alamat Email -->
        <div>
            <x-input-label for="email" value="Alamat Email" class="text-sm font-semibold text-slate-800" />
            <x-text-input id="email" class="block mt-1.5 w-full min-h-[44px] text-sm sm:text-base" type="email" name="email" :value="old('email')" required autofocus placeholder="nama@instansi.go.id" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pt-2">
            <a href="{{ route('login') }}" class="text-sm font-semibold text-blue-700 hover:text-blue-900 underline underline-offset-2">
                Kembali ke Halaman Masuk
            </a>
            <x-primary-button class="min-h-[44px] justify-center">
                Kirim Tautan Atur Ulang
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
