<x-guest-layout>
    <div class="mx-auto w-full text-center mb-2.5">
        <!-- Logo Pemkot Surabaya & Dispusip -->
        <div class="flex items-center justify-center gap-3 mb-2">
            <a href="/" class="group transition-transform duration-300 hover:scale-105 inline-block focus:outline-none" title="Pemerintah Kota Surabaya">
                <img src="{{ asset('images/logos/surabaya.png') }}" 
                     alt="Logo Pemkot Surabaya"
                     class="h-11 w-auto object-contain drop-shadow-2xs transition-all"
                     style="aspect-ratio: 960/1234; height: 44px; width: auto; object-fit: contain;">
            </a>
            <div class="h-6 w-px bg-slate-200"></div>
            <a href="/" class="group transition-transform duration-300 hover:scale-105 inline-block focus:outline-none" title="Dinas Perpustakaan dan Kearsipan Kota Surabaya">
                <img src="{{ asset('images/logos/dispusip.png') }}" 
                     alt="Logo Dispusip Surabaya"
                     class="h-11 w-auto object-contain drop-shadow-2xs transition-all"
                     style="height: 44px; width: auto; object-fit: contain;">
            </a>
        </div>

        <!-- Header Text -->
        <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight leading-tight">
            Pendaftaran Akun Portal Magang
        </h2>
        <p class="text-[11px] font-bold uppercase tracking-wider text-blue-700 mt-0.5">
            Dinas Perpustakaan dan Kearsipan Kota Surabaya
        </p>
        <p class="text-xs text-slate-500 mt-0.5">
            Pilih jenis akun sesuai peran Anda dalam pelaksanaan magang / PKL
        </p>
    </div>

    @php
        $universities = \App\Models\University::orderBy('name')->get();
    @endphp

    <div class="bg-white pt-3.5 pb-5 px-5 sm:px-8 shadow-xl shadow-slate-200/60 rounded-3xl border border-slate-100"
         x-data="{ 
            roleType: '{{ old('register_as', 'student') }}',
            email: '{{ old('email') }}',
            isBlockedEmail(val) {
                const v = val.trim().toLowerCase();
                if (!v || !v.includes('@')) return false;
                return /@(gmail|yahoo|ymail|hotmail|outlook|live|icloud|aol|mail|protonmail|yandex)\./i.test(v);
            },
            checkEmailValidity(el) {
                const v = el.value.trim().toLowerCase();
                if (!v) {
                    el.setCustomValidity('Harap isi alamat email institusi Anda.');
                } else if (el.validity.typeMismatch) {
                    el.setCustomValidity('Masukkan format alamat email yang valid.');
                } else if (this.isBlockedEmail(v)) {
                    el.setCustomValidity('Gunakan email resmi kampus (.ac.id), sekolah (.sch.id), atau instansi (.go.id). Email pribadi (@gmail.com, dll) tidak diperbolehkan.');
                } else {
                    el.setCustomValidity('');
                }
            },
            handleFormSubmit(e) {
                if (this.isBlockedEmail(this.email)) {
                    e.preventDefault();
                    alert('Peringatan: Harap gunakan alamat email resmi institusi pendidikan (.ac.id / .sch.id) atau kedinasan (.go.id). Email pribadi seperti @gmail.com tidak diizinkan.');
                    document.getElementById('email').focus();
                }
            }
         }">

        <form method="POST" action="{{ route('register') }}" @submit="handleFormSubmit($event)" class="space-y-3">
            @csrf

            <!-- Input Hidden Penentu Role -->
            <input type="hidden" name="register_as" :value="roleType">

            <!-- Tab Switcher: Peserta Magang vs Pembimbing -->
            <div>
                <label class="block font-bold text-slate-700 text-xs uppercase tracking-wider mb-1.5 text-center">
                    Daftar Sebagai:
                </label>
                <div class="grid grid-cols-2 gap-1.5 p-1 bg-slate-100 rounded-2xl border border-slate-200/80">
                    <button type="button"
                            @click="roleType = 'student'"
                            :class="roleType === 'student' 
                                ? 'bg-blue-600 text-white shadow-sm font-bold' 
                                : 'text-slate-600 hover:text-slate-900 font-semibold hover:bg-white/60'"
                            class="py-2 px-2.5 rounded-xl text-xs transition-all duration-150 flex flex-col items-center justify-center cursor-pointer">
                        <span>Peserta Magang</span>
                        <span class="text-[9.5px] opacity-85 font-normal">Mahasiswa / Siswa PKL</span>
                    </button>

                    <button type="button"
                            @click="roleType = 'advisor'"
                            :class="roleType === 'advisor' 
                                ? 'bg-blue-600 text-white shadow-sm font-bold' 
                                : 'text-slate-600 hover:text-slate-900 font-semibold hover:bg-white/60'"
                            class="py-2 px-2.5 rounded-xl text-xs transition-all duration-150 flex flex-col items-center justify-center cursor-pointer">
                        <span>Pembimbing</span>
                        <span class="text-[9.5px] opacity-85 font-normal">Dosen / Guru Sekolah</span>
                    </button>
                </div>
            </div>

            <!-- Name -->
            <div>
                <label for="name" class="block font-bold text-slate-700 text-xs uppercase tracking-wider leading-snug"
                       x-text="roleType === 'advisor' ? 'NAMA LENGKAP & GELAR (DOSEN / GURU)' : 'NAMA LENGKAP PESERTA (SESUAI KTP/KTM/KARTU PELAJAR)'">
                </label>
                <input id="name" 
                       class="block mt-1.5 w-full rounded-xl border-slate-200 focus:border-blue-600 focus:ring-blue-600 text-xs sm:text-sm shadow-2xs py-2.5 px-3.5 transition" 
                       type="text" 
                       name="name" 
                       value="{{ old('name') }}" 
                       required 
                       autofocus 
                       autocomplete="name" 
                       :placeholder="roleType === 'advisor' ? 'Contoh: Dr. Budi Santoso, S.Kom., M.Kom.' : 'Nama lengkap Anda'"
                       oninvalid="this.setCustomValidity('Harap isi nama lengkap Anda terlebih dahulu.')"
                       oninput="this.setCustomValidity('')" />
                <x-input-error :messages="$errors->get('name')" class="mt-1.5" />
            </div>

            <!-- Asal Instansi Pendidikan (Hanya Muncul Jika Daftar Sebagai Pembimbing) -->
            <div x-show="roleType === 'advisor'" x-cloak>
                <label for="university" class="block font-bold text-slate-700 text-xs uppercase tracking-wider leading-snug">
                    ASAL PERGURUAN TINGGI / SEKOLAH <span class="text-rose-500">*</span>
                </label>
                <input type="text" 
                       id="university" 
                       name="university" 
                       list="institution-list"
                       value="{{ old('university') }}"
                       :required="roleType === 'advisor'"
                       :disabled="roleType !== 'advisor'"
                       placeholder="Ketik atau pilih nama Kampus / SMK asal..."
                       oninvalid="this.setCustomValidity('Harap isi nama Perguruan Tinggi atau Sekolah asal Anda.')"
                       oninput="this.setCustomValidity('')"
                       class="block mt-1.5 w-full rounded-xl border-slate-200 focus:border-blue-600 focus:ring-blue-600 text-xs sm:text-sm shadow-2xs py-2.5 px-3.5 transition" />
                <datalist id="institution-list">
                    @foreach($universities as $univ)
                        <option value="{{ $univ->name }}"></option>
                    @endforeach
                </datalist>
                <x-input-error :messages="$errors->get('university')" class="mt-1.5" />
            </div>

            <!-- Email Address -->
            <div>
                <label for="email" class="block font-bold text-slate-700 text-xs uppercase tracking-wider leading-snug">
                    ALAMAT EMAIL INSTITUSI
                </label>
                <input id="email" 
                       x-model="email"
                       class="block mt-1.5 w-full rounded-xl border-slate-200 focus:border-blue-600 focus:ring-blue-600 text-xs sm:text-sm shadow-2xs py-2.5 px-3.5 transition" 
                       type="email" 
                       name="email" 
                       value="{{ old('email') }}" 
                       required 
                       autocomplete="username" 
                       :placeholder="roleType === 'advisor' ? 'nama@kampus.ac.id atau nama@sekolah.sch.id' : 'nim@student.kampus.ac.id atau siswa@sekolah.sch.id'"
                       @invalid="checkEmailValidity($event.target)"
                       @input="checkEmailValidity($event.target)" />

                <template x-if="isBlockedEmail(email)">
                    <div class="mt-1.5 p-2 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-[11px] font-semibold flex items-start gap-1.5">
                        <span class="shrink-0">⚠️</span>
                        <span>Email pribadi tidak diizinkan. Gunakan email resmi kampus (<strong>.ac.id</strong>) atau sekolah (<strong>.sch.id</strong>).</span>
                    </div>
                </template>

                <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
            </div>

            <!-- Password & Confirm Password -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div>
                    <label for="password" class="block font-bold text-slate-700 text-xs uppercase tracking-wider leading-snug">
                        KATA SANDI
                    </label>
                    <div class="relative mt-1.5" x-data="{ show: false }">
                        <input id="password"
                            class="block w-full rounded-xl border-slate-200 focus:border-blue-600 focus:ring-blue-600 text-xs sm:text-sm shadow-2xs py-2.5 pl-3.5 pr-9 transition"
                            :type="show ? 'text' : 'password'"
                            type="password"
                            name="password"
                            required
                            minlength="8"
                            autocomplete="new-password"
                            placeholder="Min. 8 karakter"
                            oninvalid="if(this.validity.valueMissing){this.setCustomValidity('Harap buat kata sandi terlebih dahulu.')}else if(this.validity.tooShort){this.setCustomValidity('Kata sandi minimal harus 8 karakter.')}"
                            oninput="this.setCustomValidity('')" />
                        <button type="button"
                            @click="show = !show"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none transition cursor-pointer"
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
                    <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
                </div>

                <div>
                    <label for="password_confirmation" class="block font-bold text-slate-700 text-xs uppercase tracking-wider leading-snug">
                        KONFIRMASI SANDI
                    </label>
                    <div class="relative mt-1.5" x-data="{ show: false }">
                        <input id="password_confirmation"
                            class="block w-full rounded-xl border-slate-200 focus:border-blue-600 focus:ring-blue-600 text-xs sm:text-sm shadow-2xs py-2.5 pl-3.5 pr-9 transition"
                            :type="show ? 'text' : 'password'"
                            type="password"
                            name="password_confirmation"
                            required
                            autocomplete="new-password"
                            placeholder="Ulangi kata sandi"
                            oninvalid="this.setCustomValidity('Harap ulangi kata sandi Anda untuk konfirmasi.')"
                            oninput="this.setCustomValidity('')" />
                        <button type="button"
                            @click="show = !show"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none transition cursor-pointer"
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
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1.5" />
                </div>
            </div>

            <!-- Submit Button -->
            <div class="pt-1.5">
                <button type="submit"
                    class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-md shadow-blue-600/20 text-xs sm:text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 ease-in-out uppercase tracking-wider cursor-pointer">
                    <span x-text="roleType === 'advisor' ? 'Daftar Sebagai Pembimbing' : 'Daftar Sebagai Peserta Magang'"></span>
                </button>
            </div>
        </form>

        <div class="mt-4 pt-4 border-t border-slate-100 text-center">
            <p class="text-xs text-slate-500">
                Sudah memiliki akun?
                <a href="{{ route('login') }}" class="font-bold text-blue-600 hover:text-blue-800 ml-1">
                    Masuk ke portal
                </a>
            </p>
        </div>
    </div>

    <!-- Footer Tagline -->
    <p class="text-center text-[11px] text-slate-400 mt-4">
        &copy; {{ date('Y') }} Dinas Perpustakaan dan Kearsipan Kota Surabaya. Hak Cipta Dilindungi.
    </p>
</x-guest-layout>