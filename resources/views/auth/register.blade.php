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
        // Hanya nama institusi yang sudah diverifikasi yang ditawarkan; nama baru tetap boleh diketik.
        // Master sekolah berisi puluhan ribu sekolah se-Indonesia (direktori NPSN), jadi saran dibatasi ke kampus,
        // sekolah di Jawa Timur, dan institusi yang sudah punya pengguna. Nama/NPSN lain tetap dicocokkan persis saat daftar.
        $likeOp = \Illuminate\Support\Facades\DB::getDriverName() === 'pgsql' ? 'ilike' : 'like';
        $institutionNames = \App\Models\University::where('is_verified', true)
            ->where(fn ($q) => $q->where('institution_type', '!=', \App\Models\University::TYPE_SCHOOL)
                ->orWhereNull('institution_type')
                ->orWhere('address', $likeOp, '%Jawa Timur%')
                ->orWhereIn('id', \App\Models\User::whereNotNull('university_id')->select('university_id')))
            ->orderBy('name')
            ->pluck('name')
            ->values()
            ->all();
    @endphp

    <div class="bg-white p-5 sm:px-8 sm:py-6 shadow-xl shadow-slate-200/60 rounded-3xl border border-slate-200/80"
         x-data="{
            roleType: @js(old('register_as') === 'advisor' ? 'advisor' : 'student'),
            institutionQuery: @js((string) old('university', '')),
            institutionOpen: false,
            institutions: @js($institutionNames),
            get filteredInstitutions() {
                const q = (this.institutionQuery || '').trim().toLowerCase();
                if (!q) return this.institutions.slice(0, 20);
                return this.institutions.filter((name) => name.toLowerCase().includes(q)).slice(0, 25);
            },
            pickInstitution(name) {
                this.institutionQuery = name;
                this.$refs.institutionInput.focus();
                this.institutionOpen = false;
            }
         }">

        <form method="POST" action="{{ route('register') }}" class="space-y-4">
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
                            @click="roleType = 'student'; institutionOpen = false"
                            :class="roleType === 'student' 
                                ? 'bg-blue-600 text-white shadow-sm font-bold' 
                                : 'text-slate-600 hover:text-slate-900 font-semibold hover:bg-white/60'"
                            class="min-h-[50px] py-2 px-2.5 rounded-xl text-xs transition-all duration-150 flex flex-col items-center justify-center cursor-pointer">
                        <span>Peserta Magang</span>
                        <span class="text-[11px] opacity-85 font-normal">Mahasiswa / Siswa PKL</span>
                    </button>

                    <button type="button"
                            @click="roleType = 'advisor'"
                            :class="roleType === 'advisor' 
                                ? 'bg-blue-600 text-white shadow-sm font-bold' 
                                : 'text-slate-600 hover:text-slate-900 font-semibold hover:bg-white/60'"
                            class="min-h-[50px] py-2 px-2.5 rounded-xl text-xs transition-all duration-150 flex flex-col items-center justify-center cursor-pointer">
                        <span>Pembimbing</span>
                        <span class="text-[11px] opacity-85 font-normal">Dosen / Guru Sekolah</span>
                    </button>
                </div>
            </div>

            <!-- Name -->
            <div>
                <label for="name" class="block font-bold text-slate-700 text-xs uppercase tracking-wider leading-snug"
                       x-text="roleType === 'advisor' ? 'NAMA LENGKAP & GELAR (DOSEN / GURU)' : 'NAMA LENGKAP PESERTA (SESUAI KTP/KTM/KARTU PELAJAR)'">
                </label>
                <input id="name" 
                       class="block mt-1.5 w-full min-h-[44px] rounded-xl bg-white border-slate-300 text-slate-900 placeholder:text-slate-400 focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20 text-sm shadow-2xs py-2.5 px-3.5 transition"
                       type="text" 
                       name="name" 
                       value="{{ old('name') }}" 
                       required 
                       autofocus 
                       autocomplete="name" 
                       :placeholder="roleType === 'advisor' ? 'Contoh: Dr. Budi Santoso, S.Kom.' : 'Nama lengkap Anda'"
                       oninvalid="this.setCustomValidity('Harap isi nama lengkap Anda terlebih dahulu.')"
                       oninput="this.setCustomValidity('')" />
                <x-input-error :messages="$errors->get('name')" class="mt-1.5" />
            </div>

            <!-- Asal Instansi Pendidikan (Hanya Muncul Jika Daftar Sebagai Pembimbing) -->
            <div x-show="roleType === 'advisor'" x-cloak class="relative" @click.outside="institutionOpen = false" @keydown.escape.stop="$refs.institutionInput.focus(); institutionOpen = false">
                <label for="university" class="block font-bold text-slate-700 text-xs uppercase tracking-wider leading-snug">
                    ASAL PERGURUAN TINGGI / SEKOLAH <span class="text-rose-500">*</span>
                </label>
                <div class="relative mt-1.5">
                    <input type="text"
                           id="university"
                           name="university"
                           x-ref="institutionInput" x-model="institutionQuery"
                           role="combobox"
                           aria-autocomplete="list"
                           aria-controls="institution-list"
                           :aria-expanded="institutionOpen.toString()"
                           @keydown.down.prevent="institutionOpen = true; $nextTick(() => $refs.institutionList.querySelector('button')?.focus())"
                           @focus="institutionOpen = true"
                           @input="institutionOpen = true; $el.setCustomValidity('')"
                           autocomplete="off"
                           :required="roleType === 'advisor'"
                           :disabled="roleType !== 'advisor'"
                           placeholder="Ketik atau pilih Kampus / SMK asal..."
                           oninvalid="this.setCustomValidity('Harap isi nama Perguruan Tinggi atau Sekolah asal Anda.')"
                           class="block w-full min-h-[44px] rounded-xl bg-white border-slate-300 text-slate-900 placeholder:text-slate-400 focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20 text-sm shadow-2xs py-2.5 pl-3.5 pr-10 transition" />
                    <button type="button"
                            @click="institutionOpen = !institutionOpen"
                            :disabled="roleType !== 'advisor'"
                            aria-label="Tampilkan daftar perguruan tinggi atau sekolah"
                            tabindex="-1"
                            class="absolute top-0 right-0 h-11 w-11 sm:w-10 flex items-center justify-center text-slate-400 hover:text-slate-600 cursor-pointer">
                        <svg class="w-4 h-4 transition-transform duration-150" :class="{ 'rotate-180 text-blue-600': institutionOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <!-- Dropdown Autocomplete Responsif (Anti-Potong Mobile & Desktop) -->
                    <div x-show="institutionOpen"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 -translate-y-1"
                         x-cloak
                         id="institution-list"
                         x-ref="institutionList"
                         role="listbox"
                         aria-label="Pilihan instansi pendidikan"
                         class="relative sm:absolute sm:left-0 sm:right-0 sm:top-full mt-1.5 z-30 w-full max-w-full bg-white rounded-2xl border border-slate-200 shadow-xl overflow-hidden">
                        <div class="max-h-56 overflow-y-auto scrollbar-thin divide-y divide-slate-100">
                            <template x-for="inst in filteredInstitutions" :key="inst">
                                <button type="button"
                                        role="option"
                                        :aria-selected="(inst === institutionQuery).toString()"
                                        @keydown.down.prevent="$el.nextElementSibling?.closest('button')?.focus()"
                                        @keydown.up.prevent="($el.previousElementSibling?.closest('button') || $refs.institutionInput).focus()"
                                        @click="pickInstitution(inst)"
                                        class="w-full min-h-[44px] text-left px-3.5 py-2.5 text-sm text-slate-800 hover:bg-blue-50 hover:text-blue-700 transition cursor-pointer whitespace-normal break-words leading-snug">
                                    <span x-text="inst"></span>
                                </button>
                            </template>
                            <div x-show="filteredInstitutions.length === 0" class="px-3.5 py-3 text-xs text-slate-600 leading-relaxed bg-slate-50">
                                Institusi belum ada di daftar. Lanjutkan mengetik nama lengkap kampus/sekolah Anda — akun tetap dapat didaftarkan.
                            </div>
                        </div>
                    </div>
                </div>

                <x-input-error :messages="$errors->get('university')" class="mt-1.5" />
            </div>

            <!-- Email Address -->
            <div>
                <label for="email" class="block font-bold text-slate-700 text-xs uppercase tracking-wider leading-snug">
                    ALAMAT EMAIL INSTITUSI
                </label>
                <input id="email" 
                       class="block mt-1.5 w-full min-h-[44px] rounded-xl bg-white border-slate-300 text-slate-900 placeholder:text-slate-400 focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20 text-sm shadow-2xs py-2.5 px-3.5 transition"
                       type="email" 
                       name="email" 
                       value="{{ old('email') }}" 
                       required 
                       autocomplete="username" 
                       :placeholder="roleType === 'advisor' ? 'email@kampus.ac.id / email aktif' : 'nama@email.com / email institusi'"
                       oninvalid="if(this.validity.valueMissing){this.setCustomValidity('Harap isi alamat email Anda.')}else if(this.validity.typeMismatch){this.setCustomValidity('Masukkan format alamat email yang valid.')}"
                       oninput="this.setCustomValidity('')" />
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
                            class="block w-full min-h-[44px] rounded-xl bg-white border-slate-300 text-slate-900 placeholder:text-slate-400 focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20 text-sm shadow-2xs py-2.5 pl-3.5 pr-11 transition"
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
                            :aria-label="show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
                            :title="show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
                            class="absolute inset-y-0 right-0 w-11 flex items-center justify-center text-slate-500 hover:text-slate-700 focus:outline-none transition cursor-pointer"
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
                            class="block w-full min-h-[44px] rounded-xl bg-white border-slate-300 text-slate-900 placeholder:text-slate-400 focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20 text-sm shadow-2xs py-2.5 pl-3.5 pr-11 transition"
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
                            :aria-label="show ? 'Sembunyikan konfirmasi kata sandi' : 'Tampilkan konfirmasi kata sandi'"
                            :title="show ? 'Sembunyikan konfirmasi kata sandi' : 'Tampilkan konfirmasi kata sandi'"
                            class="absolute inset-y-0 right-0 w-11 flex items-center justify-center text-slate-500 hover:text-slate-700 focus:outline-none transition cursor-pointer"
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
            <div class="pt-1">
                <button type="submit"
                    class="w-full min-h-[46px] sm:min-h-[44px] flex items-center justify-center py-3 px-4 border border-transparent rounded-xl shadow-md shadow-blue-600/20 text-xs sm:text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 ease-in-out uppercase tracking-wider cursor-pointer">
                    <span x-text="roleType === 'advisor' ? 'Daftar Sebagai Pembimbing' : 'Daftar Sebagai Peserta Magang'"></span>
                </button>
            </div>
        </form>

        <div class="mt-5 pt-4 border-t border-slate-100 text-center">
            <p class="text-xs sm:text-sm text-slate-600">
                Sudah memiliki akun?
                <a href="{{ route('login') }}" class="font-bold text-blue-600 hover:text-blue-800 ml-1">
                    Masuk ke portal
                </a>
            </p>
        </div>
    </div>

    <!-- Footer Tagline -->
    <p class="text-center text-xs text-slate-500 mt-4">
        &copy; {{ date('Y') }} Dinas Perpustakaan dan Kearsipan Kota Surabaya. Hak Cipta Dilindungi.
    </p>
</x-guest-layout>