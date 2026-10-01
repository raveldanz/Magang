<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl sm:text-2xl text-slate-800 leading-tight">
            {{ __('Profil Mahasiswa') }}
        </h2>
    </x-slot>

    <div class="py-5 sm:py-8 lg:py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6"
             x-data="{
                photoPreview: '{{ ($profile?->photo ?? $user?->studentProfile?->photo ?? $user?->photo) ? asset('storage/' . ($profile?->photo ?? $user?->studentProfile?->photo ?? $user?->photo)) : '' }}',
                photoName: '',
                showModal: false,
                handlePhotoChange(e) {
                    const file = e.target.files[0];
                    if (file) {
                        if (file.size > 2 * 1024 * 1024) {
                            alert('Ukuran foto profil maksimal 2MB.');
                            e.target.value = '';
                            return;
                        }
                        this.photoName = file.name;
                        this.photoPreview = URL.createObjectURL(file);
                    }
                }
             }">

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
                
                <!-- SISI KIRI (4 Col): KHUSUS PREVIEW IDENTITAS & AKADEMIK -->
                <div class="lg:col-span-4 space-y-6">
                    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-5">
                        <div class="flex items-start gap-4">
                            
                            <!-- Avatar Kotak dengan Icon Kamera (Hanya Muncul Saat Kursor Mengarah/Hover) -->
                            <div class="relative shrink-0 group">
                                <div @click="if (photoPreview) showModal = true"
                                     :title="photoPreview ? 'Lihat Foto Profil' : 'Belum Ada Foto'"
                                     class="w-16 h-16 rounded-2xl overflow-hidden border-2 border-slate-100 shadow-md flex items-center justify-center bg-gradient-to-tr from-blue-700 to-indigo-700 text-white font-black text-xl relative select-none"
                                     :class="photoPreview ? 'cursor-pointer' : 'cursor-default'">
                                    
                                    <!-- Foto Profil / Inisial -->
                                    <template x-if="photoPreview">
                                        <img :src="photoPreview" alt="Foto Profil" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!photoPreview">
                                        <span>{{ strtoupper(substr($user->name ?? 'M', 0, 2)) }}</span>
                                    </template>

                                    <!-- Overlay Icon Kamera di Tengah (Hanya Tampil Saat Hover & Jika Ada Foto) -->
                                    <template x-if="photoPreview">
                                        <div class="absolute inset-0 bg-blue-950/40 opacity-0 group-hover:opacity-100 transition-all duration-200 flex items-center justify-center">
                                            <svg class="w-6 h-6 text-white drop-shadow-md transition-transform duration-200 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            </svg>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Identitas Mahasiswa -->
                            <div class="min-w-0 flex-1">
                                <h3 class="font-bold text-base text-slate-900 truncate">{{ $user->name }}</h3>
                                <p class="text-xs text-slate-500 truncate">{{ $user->email }}</p>
                                
                                <div class="mt-1.5">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-blue-50 text-blue-700 border border-blue-200">
                                        Mahasiswa MBKM
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Ringkasan Akademik -->
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
                            <li>Arahkan kursor & klik <strong>foto profil</strong> untuk meninjau gambar secara penuh.</li>
                            <li>Pastikan <strong>Foto Profil</strong> formal dan jelas dengan format JPG/PNG maksimal 2MB.</li>
                            <li>Pastikan <strong>NIM</strong> dan <strong>Universitas</strong> sesuai dengan data perguruan tinggi resmi.</li>
                            <li>Nomor <strong>WhatsApp</strong> aktif wajib diisi untuk koordinasi dengan DPL & Mentor instansi.</li>
                        </ul>
                    </div>
                </div>

                <!-- SISI KANAN (8 Col): FORM CARD UTAMA -->
                <div class="lg:col-span-8">
                    <div class="bg-white rounded-2xl p-6 sm:p-8 border border-slate-200/80 shadow-xs space-y-6">
                        
                        <div class="border-b border-slate-100 pb-4">
                            <h3 class="text-base sm:text-lg font-bold text-slate-900">Data Akademik, Pribadi & Kontak Darurat</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Lengkapi informasi foto, akademik, dan kontak Anda untuk keperluan administrasi magang</p>
                        </div>

                        <form action="{{ route('student.profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                            @csrf

                            <!-- Section 1: Identitas Akun, Foto & NIM -->
                            <div class="space-y-4">
                                <h4 class="text-xs font-black uppercase text-blue-700 tracking-wider flex items-center gap-2">
                                    <span>Identitas Akun & Mahasiswa</span>
                                </h4>

                                <!-- Input Tunggal Berkas Foto Profil -->
                                <div>
                                    <x-input-label for="photo" value="Foto Profil Mahasiswa (Opsional)" class="text-xs font-bold uppercase tracking-wider" />
                                    <input id="photo" 
                                           name="photo" 
                                           type="file" 
                                           accept="image/jpeg,image/png,image/jpg"
                                           @change="handlePhotoChange($event)"
                                           class="mt-1 block w-full text-xs sm:text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs sm:file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer transition shadow-2xs" />
                                    
                                    <div class="flex items-center justify-between pt-1 text-xs">
                                        <p class="text-[11px] text-slate-400">Format: JPG, JPEG, PNG (Maks. 2MB)</p>
                                        <span x-show="photoName" class="text-[11px] text-emerald-600 font-semibold truncate max-w-[200px]" x-text="'Dipilih: ' + photoName"></span>
                                    </div>
                                    <x-input-error :messages="$errors->get('photo')" class="mt-1" />
                                </div>
                                
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <x-input-label for="name" value="Nama Lengkap (Sesuai Akun)" class="text-xs font-bold uppercase tracking-wider" />
                                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full text-xs sm:text-sm" :value="Auth::user()?->name ?? $user?->name ?? ''" required />
                                        <x-input-error :messages="$errors->get('name')" class="mt-1" />
                                    </div>

                                    <div>
                                        <x-input-label for="nim" value="Nomor Induk Mahasiswa (NIM)" class="text-xs font-bold uppercase tracking-wider" />
                                        <x-text-input id="nim" name="nim" type="text" class="mt-1 block w-full text-xs sm:text-sm" :value="old('nim', $profile?->nim ?? '')" placeholder="Contoh: 22081010001" required />
                                        <x-input-error :messages="$errors->get('nim')" class="mt-1" />
                                    </div>
                                </div>
                            </div>

                            <!-- Section 2: Data Perguruan Tinggi & Akademik -->
                            <div class="space-y-4 pt-2 border-t border-slate-100">
                                <h4 class="text-xs font-black uppercase text-blue-700 tracking-wider flex items-center gap-2">
                                    <span>Data Perguruan Tinggi & Akademik</span>
                                </h4>

                                <!-- Input Universitas Datalist -->
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
                                        <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full text-xs sm:text-sm" :value="old('phone', $profile?->phone ?? '')" placeholder="Contoh: 081234567890" required />
                                        <x-input-error :messages="$errors->get('phone')" class="mt-1" />
                                    </div>

                                    <div>
                                        <x-input-label for="alamat" value="Alamat Domisili / Tempat Tinggal*" class="text-xs font-bold uppercase tracking-wider" />
                                        <x-text-input id="alamat" name="alamat" type="text" class="mt-1 block w-full text-xs sm:text-sm" :value="old('alamat', $profile?->alamat ?? $profile?->address ?? '')" placeholder="Contoh: Jl. Rungkut Asri Timur No. 12, Surabaya" />
                                        <x-input-error :messages="$errors->get('alamat')" class="mt-1" />
                                    </div>
                                </div>
                            </div>

                            <!-- Section 4: Kontak Darurat -->
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
                                        <x-text-input id="emergency_contact_phone" name="emergency_contact_phone" type="text" class="mt-1 block w-full text-xs sm:text-sm" :value="old('emergency_contact_phone', $profile?->emergency_contact_phone ?? '')" placeholder="Contoh: 081298765432" />
                                        <x-input-error :messages="$errors->get('emergency_contact_phone')" class="mt-1" />
                                    </div>
                                </div>
                                <p class="text-xs text-slate-400 italic">*opsional</p>
                            </div>

                            <!-- Tombol Aksi Bawah -->
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

            <!-- Modal Popup / Lightbox Peninjauan Foto Penuh -->
            <div x-show="showModal" 
                 x-cloak
                 class="fixed inset-0 z-50 flex items-center justify-center p-4">
                
                <!-- Backdrop Gelap -->
                <div x-show="showModal"
                     x-transition:enter="ease-out duration-200"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-150"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     @click="showModal = false"
                     class="fixed inset-0 bg-slate-900/70 backdrop-blur-xs"></div>

                <!-- Kontainer Gambar Pop-up -->
                <div x-show="showModal"
                     x-transition:enter="ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="ease-in duration-150"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="relative bg-white rounded-3xl p-4 max-w-sm sm:max-w-md w-full shadow-2xl z-10 border border-slate-100 space-y-3">
                    
                    <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                        <span class="text-xs font-bold text-slate-800">Pratinjau Foto Profil</span>
                        <button type="button" @click="showModal = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Gambar Tampilan Penuh -->
                    <div class="w-full aspect-square rounded-2xl overflow-hidden bg-slate-50 border border-slate-100 flex items-center justify-center">
                        <img :src="photoPreview" alt="Foto Profil Lengkap" class="w-full h-full object-cover">
                    </div>

                    <div class="text-center pt-1">
                        <a :href="photoPreview" target="_blank" class="text-xs text-blue-600 hover:text-blue-800 font-bold hover:underline inline-flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                            </svg>
                            <span>Buka Ukuran Asli di Tab Baru</span>
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>