<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-bold text-xl sm:text-2xl text-slate-800 leading-tight">
                {{ __('Profil Mahasiswa') }}
            </h2>
            <p class="text-xs text-slate-500 mt-1">
                Kelola data akademik, informasi pribadi, dan kontak darurat Anda
            </p>
        </div>
    </x-slot>

    <div class="py-5 sm:py-8 lg:py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Success Message -->
            @if (session('success'))
                <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-xl shadow-xs flex items-center justify-between text-emerald-900 text-sm font-medium">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                
                <!-- Sisi Kiri (4 Col): Ringkasan Profil Mahasiswa & Panduan -->
                <div class="lg:col-span-4 space-y-6">
                    <!-- Kartu Ringkasan Akun & Status -->
                    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-5">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white font-black text-xl flex items-center justify-center shadow-md shadow-blue-500/20 shrink-0">
                                {{ strtoupper(substr($user->name ?? 'M', 0, 2)) }}
                            </div>
                            <div class="min-w-0">
                                <h3 class="font-bold text-base text-slate-900 truncate">{{ $user->name }}</h3>
                                <p class="text-xs text-slate-500 truncate">{{ $user->email }}</p>
                                <span class="inline-flex items-center mt-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-blue-50 text-blue-700 border border-blue-200">
                                    Mahasiswa MBKM
                                </span>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-slate-100 space-y-3 text-xs">
                            <div class="flex items-center justify-between">
                                <span class="text-slate-400 font-medium">NIM</span>
                                <strong class="text-slate-800 font-bold font-mono">{{ $profile?->nim ?: 'Belum Diisi' }}</strong>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-400 font-medium">Universitas</span>
                                <strong class="text-slate-800 font-bold text-right truncate max-w-[170px]" title="{{ $profile?->universitas ?? $user->university ?? '-' }}">
                                    {{ $profile?->universitas ?? $user->university ?? '-' }}
                                </strong>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-400 font-medium">Program Studi</span>
                                <strong class="text-slate-800 font-bold text-right truncate max-w-[170px]" title="{{ $profile?->jurusan ?? $profile?->major ?? '-' }}">
                                    {{ $profile?->jurusan ?? $profile?->major ?? '-' }}
                                </strong>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-400 font-medium">Semester</span>
                                <strong class="text-slate-800 font-bold">{{ $profile?->semester ? 'Semester ' . $profile->semester : '-' }}</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Petunjuk Kelengkapan Profil -->
                    <div class="bg-blue-50/60 rounded-2xl p-5 border border-blue-100 shadow-2xs space-y-2.5">
                        <div class="flex items-center gap-2 text-blue-900 font-bold text-xs">
                            <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Ketentuan Data Profil</span>
                        </div>
                        <ul class="text-[11px] text-blue-800/90 space-y-2 list-disc list-inside leading-relaxed">
                            <li>Pastikan <strong>NIM</strong> dan <strong>Universitas</strong> sesuai dengan data perguruan tinggi resmi.</li>
                            <li>Nomor <strong>WhatsApp</strong> aktif wajib diisi untuk koordinasi dengan DPL & Mentor instansi.</li>
                            <li>Data profil ini akan otomatis tercantum pada formulir pengajuan magang dan surat rekomendasi.</li>
                        </ul>
                    </div>
                </div>

                <!-- Sisi Kanan (8 Col): Form Card -->
                <div class="lg:col-span-8">
                    <div class="bg-white rounded-2xl p-6 sm:p-8 border border-slate-200/80 shadow-xs space-y-6">
                        
                        <div class="border-b border-slate-100 pb-4">
                            <h3 class="text-base sm:text-lg font-bold text-slate-900">Data Akademik, Pribadi & Kontak Darurat</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Lengkapi informasi akademik dan kontak Anda untuk keperluan administrasi magang</p>
                        </div>

                        <form action="{{ route('student.profile.update') }}" method="POST" class="space-y-6">
                            @csrf

                            <!-- Section 1: Informasi Akun & NIM -->
                            <div class="space-y-4">
                                <h4 class="text-xs font-black uppercase text-blue-700 tracking-wider flex items-center gap-2">
                                    <span>Identitas Akun & Mahasiswa</span>
                                </h4>
                                
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <x-input-label for="name" value="Nama Lengkap (Sesuai Akun)" class="text-xs font-bold uppercase tracking-wider" />
                                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full text-xs sm:text-sm" :value="Auth::user()?->name ?? $user?->name ?? ''" required />
                                        <x-input-error :messages="$errors->get('name')" class="mt-1" />
                                    </div>

                                    <div>
                                        <x-input-label for="nim" value="Nomor Induk Mahasiswa (NIM)" class="text-xs font-bold uppercase tracking-wider" />
                                        <x-text-input id="nim" name="nim" type="text" class="mt-1 block w-full text-xs sm:text-sm font-mono" :value="old('nim', $profile?->nim ?? '')" placeholder="Contoh: 22081010001" required />
                                        <x-input-error :messages="$errors->get('nim')" class="mt-1" />
                                    </div>
                                </div>
                            </div>

                            <!-- Section 2: Data Perguruan Tinggi & Akademik -->
                            <div class="space-y-4 pt-2 border-t border-slate-100">
                                <h4 class="text-xs font-black uppercase text-blue-700 tracking-wider flex items-center gap-2">
                                    <span>Data Perguruan Tinggi & Akademik</span>
                                </h4>

                                <!-- Input Universitas dengan Searchable Datalist -->
                                <div>
                                    <x-input-label for="universitas" value="Universitas / Perguruan Tinggi" class="text-xs font-bold uppercase tracking-wider" />
                                    <div class="relative mt-1">
                                        <x-text-input 
                                            id="universitas" 
                                            name="universitas" 
                                            list="universities_list" 
                                            type="text" 
                                            class="block w-full text-xs sm:text-sm pr-10" 
                                            :value="old('universitas', $profile?->universitas ?? Auth::user()?->university ?? $user?->university ?? '')" 
                                            placeholder="Ketik atau pilih nama perguruan tinggi..." 
                                            required 
                                            autocomplete="off" />
                                        
                                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </div>
                                    </div>

                                    <!-- Daftar Saran Universitas -->
                                    <datalist id="universities_list">
                                        @foreach ($universities as $univ)
                                            <option value="{{ $univ->name }}">{{ $univ->code ? '(' . $univ->code . ')' : '' }}</option>
                                        @endforeach
                                    </datalist>

                                    <p class="text-[11px] text-slate-500 mt-1.5 flex items-center gap-1">
                                        <span>Pilih universitas dari daftar yang tersedia, atau ketik nama universitas baru jika belum terdaftar.</span>
                                    </p>
                                    <x-input-error :messages="$errors->get('universitas')" class="mt-1" />
                                </div>

                                <!-- Grid Fakultas, Jurusan, Semester -->
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                    <div>
                                        <x-input-label for="faculty" value="Fakultas" class="text-xs font-bold uppercase tracking-wider" />
                                        <x-text-input id="faculty" name="faculty" type="text" class="mt-1 block w-full text-xs sm:text-sm" :value="old('faculty', $profile?->faculty ?? $profile?->fakultas ?? '')" placeholder="Contoh: Ilmu Komputer" required />
                                        <x-input-error :messages="$errors->get('faculty')" class="mt-1" />
                                    </div>

                                    <div>
                                        <x-input-label for="jurusan" value="Program Studi / Jurusan" class="text-xs font-bold uppercase tracking-wider" />
                                        <x-text-input id="jurusan" name="jurusan" type="text" class="mt-1 block w-full text-xs sm:text-sm" :value="old('jurusan', $profile?->jurusan ?? $profile?->major ?? '')" placeholder="Contoh: Teknik Informatika" required />
                                        <x-input-error :messages="$errors->get('jurusan')" class="mt-1" />
                                    </div>

                                    <div>
                                        <x-input-label for="semester" value="Semester Saat Ini" class="text-xs font-bold uppercase tracking-wider" />
                                        <x-text-input id="semester" name="semester" type="text" class="mt-1 block w-full text-xs sm:text-sm" :value="old('semester', $profile?->semester ?? '5')" placeholder="Contoh: 5 / 6 / 7" required />
                                        <x-input-error :messages="$errors->get('semester')" class="mt-1" />
                                    </div>
                                </div>
                            </div>

                            <!-- Section 3: Kontak Pribadi & Domisili -->
                            <div class="space-y-4 pt-2 border-t border-slate-100">
                                <h4 class="text-xs font-black uppercase text-blue-700 tracking-wider flex items-center gap-2">
                                    <span>Kontak Pribadi & Domisili</span>
                                </h4>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <x-input-label for="phone" value="No. WhatsApp / HP Aktif" class="text-xs font-bold uppercase tracking-wider" />
                                        <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full text-xs sm:text-sm font-mono" :value="old('phone', $profile?->phone ?? '')" placeholder="Contoh: 081234567890" required />
                                        <x-input-error :messages="$errors->get('phone')" class="mt-1" />
                                    </div>

                                    <div>
                                        <x-input-label for="alamat" value="Alamat Domisili / Tempat Tinggal*" class="text-xs font-bold uppercase tracking-wider" />
                                        <x-text-input id="alamat" name="alamat" type="text" class="mt-1 block w-full text-xs sm:text-sm" :value="old('alamat', $profile?->alamat ?? $profile?->address ?? '')" placeholder="Contoh: Jl. Rungkut Asri Timur No. 12, Surabaya" />
                                        <x-input-error :messages="$errors->get('alamat')" class="mt-1" />
                                    </div>
                                </div>
                            </div>

                            <!-- Section 4: Kontak Darurat (Emergency Contact) -->
                            <div class="space-y-4 pt-2 border-t border-slate-100">
                                <h4 class="text-xs font-black uppercase text-blue-700 tracking-wider flex items-center gap-2">
                                    <span>Kontak Darurat (Orang Tua / Wali)</span>
                                </h4>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <x-input-label for="emergency_contact_name" value="Nama Kontak Darurat / Hubungan*" class="text-xs font-bold uppercase tracking-wider" />
                                        <x-text-input id="emergency_contact_name" name="emergency_contact_name" type="text" class="mt-1 block w-full text-xs sm:text-sm" :value="old('emergency_contact_name', $profile?->emergency_contact_name ?? '')" placeholder="Contoh: Bpk. Haryanto (Ayah)" />
                                        <x-input-error :messages="$errors->get('emergency_contact_name')" class="mt-1" />
                                    </div>

                                    <div>
                                        <x-input-label for="emergency_contact_phone" value="Nomor HP Kontak Darurat*" class="text-xs font-bold uppercase tracking-wider" />
                                        <x-text-input id="emergency_contact_phone" name="emergency_contact_phone" type="text" class="mt-1 block w-full text-xs sm:text-sm font-mono" :value="old('emergency_contact_phone', $profile?->emergency_contact_phone ?? '')" placeholder="Contoh: 081298765432" />
                                        <x-input-error :messages="$errors->get('emergency_contact_phone')" class="mt-1" />
                                    </div>
                                </div>
                                <p class="text-xs text-slate-400 italic">*opsional</p>
                            </div>

                            <!-- Tombol Aksi -->
                            <div class="pt-5 border-t border-slate-100 flex flex-col-reverse sm:flex-row items-stretch sm:items-center sm:justify-end gap-2.5 sm:gap-3">
                                <a href="{{ route('dashboard') }}" class="w-full sm:w-auto px-5 py-3 sm:py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition text-center flex items-center justify-center">
                                    {{ __('Kembali ke Dashboard') }}
                                </a>

                                <button type="submit" class="w-full sm:w-auto px-6 py-3 sm:py-2.5 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-xs font-bold uppercase tracking-wider rounded-xl shadow-xs transition cursor-pointer flex items-center justify-center">
                                    {{ __('Simpan Perubahan Profil') }}
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
