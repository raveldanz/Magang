<section id="update-password" x-data="{ showCurrent: false, showNew: false, showConfirm: false }">
    <header>
        <h2 class="text-lg font-semibold text-slate-900">
            Perbarui Kata Sandi
        </h2>

        <p class="mt-1 text-sm text-slate-600 leading-relaxed">
            Gunakan kata sandi yang panjang (minimal 8 karakter) dan mudah Anda ingat namun aman.
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-5">
        @csrf
        @method('put')

        <div>
            <x-input-label for="update_password_current_password" value="Kata Sandi Sekarang" class="text-sm font-semibold text-slate-800" />
            <div class="relative mt-1.5">
                <input id="update_password_current_password"
                    name="current_password"
                    :type="showCurrent ? 'text' : 'password'"
                    type="password"
                    class="block w-full min-h-[44px] rounded-xl border-slate-300 focus:border-blue-600 focus:ring-blue-600 shadow-xs py-2.5 pl-3.5 pr-12 text-sm sm:text-base"
                    autocomplete="current-password" />
                <button type="button"
                    @click="showCurrent = !showCurrent"
                    class="absolute inset-y-0 right-0 px-3 flex items-center text-slate-600 hover:text-slate-900 focus:outline-none cursor-pointer"
                    :aria-label="showCurrent ? 'Sembunyikan kata sandi sekarang' : 'Tampilkan kata sandi sekarang'"
                    :title="showCurrent ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'">
                    <svg x-show="!showCurrent" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                    <svg x-show="showCurrent" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" /></svg>
                </button>
            </div>
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password" value="Kata Sandi Baru" class="text-sm font-semibold text-slate-800" />
            <div class="relative mt-1.5">
                <input id="update_password_password"
                    name="password"
                    :type="showNew ? 'text' : 'password'"
                    type="password"
                    class="block w-full min-h-[44px] rounded-xl border-slate-300 focus:border-blue-600 focus:ring-blue-600 shadow-xs py-2.5 pl-3.5 pr-12 text-sm sm:text-base"
                    autocomplete="new-password" />
                <button type="button"
                    @click="showNew = !showNew"
                    class="absolute inset-y-0 right-0 px-3 flex items-center text-slate-600 hover:text-slate-900 focus:outline-none cursor-pointer"
                    :aria-label="showNew ? 'Sembunyikan kata sandi baru' : 'Tampilkan kata sandi baru'"
                    :title="showNew ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'">
                    <svg x-show="!showNew" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                    <svg x-show="showNew" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" /></svg>
                </button>
            </div>
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password_confirmation" value="Konfirmasi Kata Sandi Baru" class="text-sm font-semibold text-slate-800" />
            <div class="relative mt-1.5">
                <input id="update_password_password_confirmation"
                    name="password_confirmation"
                    :type="showConfirm ? 'text' : 'password'"
                    type="password"
                    class="block w-full min-h-[44px] rounded-xl border-slate-300 focus:border-blue-600 focus:ring-blue-600 shadow-xs py-2.5 pl-3.5 pr-12 text-sm sm:text-base"
                    autocomplete="new-password" />
                <button type="button"
                    @click="showConfirm = !showConfirm"
                    class="absolute inset-y-0 right-0 px-3 flex items-center text-slate-600 hover:text-slate-900 focus:outline-none cursor-pointer"
                    :aria-label="showConfirm ? 'Sembunyikan konfirmasi kata sandi' : 'Tampilkan konfirmasi kata sandi'"
                    :title="showConfirm ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'">
                    <svg x-show="!showConfirm" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                    <svg x-show="showConfirm" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" /></svg>
                </button>
            </div>
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4 pt-1">
            <x-primary-button class="min-h-[44px]">Simpan Kata Sandi</x-primary-button>

            @if (session('status') === 'password-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 3000)"
                    class="text-sm font-semibold text-emerald-700">Tersimpan.</p>
            @endif
        </div>
    </form>
</section>