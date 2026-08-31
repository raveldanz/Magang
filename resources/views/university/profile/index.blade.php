<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight flex items-center gap-2">
                    <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    {{ __('Pengaturan Profil & Kop Surat Kampus') }}
                </h2>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">
                    🏛️ Kelola data identitas resmi, alamat, pejabat penandatangan, dan logo untuk Kop Surat Tugas Magang
                </p>
            </div>

            <div>
                <a href="{{ route('university.dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-xl transition">
                     Kembali ke Dashboard
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

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

            <!-- Form Validation Errors -->
            @if ($errors->any())
                <div class="p-4 bg-rose-50 border-l-4 border-rose-500 rounded-r-xl shadow-xs text-rose-900 text-sm">
                    <div class="font-bold flex items-center gap-1.5 mb-1 text-rose-800">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        Terdapat kesalahan pada isian form:
                    </div>
                    <ul class="list-disc list-inside text-xs space-y-0.5 text-rose-700">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('university.profile.update') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Card 1: Identitas & Logo Perguruan Tinggi -->
                <div class="bg-white rounded-2xl shadow-xs border border-gray-200 p-6 space-y-5">
                    <div class="border-b border-gray-100 pb-3 flex items-center gap-2">
                        <div>
                            <h3 class="font-bold text-base text-gray-900">Identitas Resmi Perguruan Tinggi</h3>
                            <p class="text-xs text-gray-400">Data ini akan tercantum pada kop surat tugas dan dokumen resmi kampus</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start">
                        
                        <!-- Preview & Upload Logo Kampus -->
                        <div class="flex flex-col items-center p-4 bg-slate-50 rounded-2xl border border-dashed border-gray-300 text-center">
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                                Logo Resmi Kampus
                            </label>
                            
                            <div class="w-32 h-32 rounded-2xl bg-white p-2 border border-gray-200 shadow-xs flex items-center justify-center overflow-hidden mb-3">
                                @if ($university->logo && file_exists(public_path($university->logo)))
                                    <img src="{{ asset($university->logo) }}" alt="Logo {{ $university->name }}" class="max-h-full max-w-full object-contain">
                                @else
                                    <span class="text-4xl text-gray-300">🏛️</span>
                                @endif
                            </div>

                            <input type="file" name="logo" id="logo" accept="image/*" class="text-xs text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                            <p class="text-[10px] text-gray-400 mt-1.5">Format: PNG, JPG, WEBP. Maks 2MB.</p>
                        </div>

                        <!-- Nama & Singkatan Kampus -->
                        <div class="md:col-span-2 space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                    Nama Lengkap Perguruan Tinggi <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" name="name" value="{{ old('name', $university->name) }}" required placeholder="Contoh: Universitas Dr. Soetomo" class="w-full text-sm border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs font-semibold">
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                        Singkatan / Kode Kampus
                                    </label>
                                    <input type="text" name="code" value="{{ old('code', $university->code) }}" placeholder="Contoh: UNITOMO" class="w-full text-sm border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs font-mono uppercase">
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                        Email Resmi Kampus / Humas
                                    </label>
                                    <input type="email" name="email" value="{{ old('email', $university->email) }}" placeholder="info@unitomo.ac.id" class="w-full text-sm border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs font-mono">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                        Nomor Telepon / Hotline Kampus
                                    </label>
                                    <input type="text" name="phone" value="{{ old('phone', $university->phone) }}" placeholder="(031) 5925970" class="w-full text-sm border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                    Alamat Lengkap Kampus (Untuk Header Kop Surat)
                                </label>
                                <textarea name="address" rows="2" placeholder="Jl. Semolowaru No. 84, Menur Pumpungan, Kec. Sukolilo, Surabaya, Jawa Timur 60118" class="w-full text-xs sm:text-sm border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs">{{ old('address', $university->address) }}</textarea>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Card 2: Pejabat Penandatangan Surat Tugas (PIC / Rektor / Dekan) -->
                <div class="bg-white rounded-2xl shadow-xs border border-gray-200 p-6 space-y-5">
                    <div class="border-b border-gray-100 pb-3 flex items-center gap-2">
                        <span class="text-xl">✍️</span>
                        <div>
                            <h3 class="font-bold text-base text-gray-900">Pejabat Resmi Penandatangan Surat Tugas</h3>
                            <p class="text-xs text-gray-400">Nama dan jabatan pejabat yang otomatis tertera di bagian tanda tangan Surat Pengantar Magang</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                Nama Lengkap & Gelar Pejabat
                            </label>
                            <input type="text" name="pic_name" value="{{ old('pic_name', $university->pic_name) }}" placeholder="Dr. Siti Marwiyah, S.H., M.H." class="w-full text-sm border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                NIP / NIDN Pejabat
                            </label>
                            <input type="text" name="pic_nip" value="{{ old('pic_nip', $university->pic_nip) }}" placeholder="196808281993032001" class="w-full text-sm border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs font-mono">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                Jabatan Penandatangan
                            </label>
                            <input type="text" name="pic_position" value="{{ old('pic_position', $university->pic_position) }}" placeholder="Rektor Universitas Dr. Soetomo" class="w-full text-sm border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs">
                        </div>
                    </div>
                </div>

                <!-- Card 3: Skema Kebijakan & Pembobotan Nilai Kampus Adaptif -->
                <div x-data="{ 
                        scheme: '{{ old('evaluation_scheme', $university->evaluation_scheme ?? 'dual_evaluation') }}',
                        weightMentor: {{ old('weight_mentor', $university->weight_mentor ?? 40) }},
                        weightLecturer: {{ old('weight_lecturer', $university->weight_lecturer ?? 60) }},
                        updateLecturer() {
                            this.weightLecturer = Math.max(0, Math.min(100, 100 - this.weightMentor));
                        },
                        updateMentor() {
                            this.weightMentor = Math.max(0, Math.min(100, 100 - this.weightLecturer));
                        }
                     }" 
                     class="bg-white rounded-2xl shadow-xs border border-gray-200 p-6 space-y-5">
                    
                    <div class="border-b border-gray-100 pb-3 flex items-center justify-between flex-wrap gap-2">
                        <div class="flex items-center gap-2">
                            <span class="text-xl">⚙️</span>
                            <div>
                                <h3 class="font-bold text-base text-gray-900">Skema Kebijakan Evaluasi & Pengawasan Magang</h3>
                                <p class="text-xs text-gray-400">Atur porsi pembobotan nilai akhir dan mekanisme persetujuan logbook mahasiswa magang</p>
                            </div>
                        </div>
                        <span class="text-xs font-bold px-3 py-1 rounded-full bg-blue-50 text-blue-700 border border-blue-100">
                            Kebijakan Kampus Adaptif
                        </span>
                    </div>

                    <!-- Pilihan 2 Model Skema Evaluasi -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        
                        <!-- Option 1: Dual Evaluation (Kemitraan Dua Pihak) -->
                        <label class="relative flex flex-col p-4 rounded-2xl border-2 cursor-pointer transition select-none"
                               :class="scheme === 'dual_evaluation' ? 'border-blue-600 bg-blue-50/40 shadow-xs' : 'border-gray-200 hover:border-gray-300 bg-white'">
                            <div class="flex items-start gap-3">
                                <input type="radio" name="evaluation_scheme" value="dual_evaluation" x-model="scheme" class="mt-1 text-blue-600 focus:ring-blue-500">
                                <div>
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-bold text-xs sm:text-sm text-gray-900">⚖️ Kemitraan Terpadu (Dua Pihak)</span>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800">Standar</span>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1 leading-relaxed">
                                        Mahasiswa dibimbing & dinilai oleh <strong>Mentor Dinas</strong> dan <strong>Dosen Pembimbing (DPL)</strong>. Logbook diverifikasi 2 arah.
                                    </p>
                                    <div class="mt-2.5 pt-2 border-t border-gray-100/80 flex items-center gap-3 text-[11px] text-gray-600">
                                        <span>✓ Wajib Pilih DPL</span>
                                        <span>✓ Verifikasi Logbook 2 Pihak</span>
                                    </div>
                                </div>
                            </div>
                        </label>

                        <!-- Option 2: Mentor Only (Penilaian Penuh Dinas 100%) -->
                        <label class="relative flex flex-col p-4 rounded-2xl border-2 cursor-pointer transition select-none"
                               :class="scheme === 'mentor_only' ? 'border-blue-600 bg-blue-50/40 shadow-xs' : 'border-gray-200 hover:border-gray-300 bg-white'">
                            <div class="flex items-start gap-3">
                                <input type="radio" name="evaluation_scheme" value="mentor_only" x-model="scheme" class="mt-1 text-blue-600 focus:ring-blue-500">
                                <div>
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-bold text-xs sm:text-sm text-gray-900">🏢 Penilaian Mandiri Instansi (100% Dinas)</span>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1 leading-relaxed">
                                        Kampus mempercayakan 100% penilaian dan pengawasan kepada <strong>Mentor Lapangan Dinas</strong>. Dosen tidak diwajibkan menilai.
                                    </p>
                                    <div class="mt-2.5 pt-2 border-t border-gray-100/80 flex items-center gap-3 text-[11px] text-gray-600">
                                        <span>✓ Tidak Wajib DPL</span>
                                        <span>✓ Cukup ACC Mentor Dinas</span>
                                    </div>
                                </div>
                            </div>
                        </label>

                    </div>

                    <!-- Pengaturan Pembobotan Nilai (Jika Dual Evaluation) -->
                    <div x-show="scheme === 'dual_evaluation'" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="p-5 rounded-2xl bg-slate-50 border border-slate-200 space-y-4">
                        
                        <div class="flex items-center justify-between">
                            <h4 class="font-bold text-xs sm:text-sm text-gray-800 flex items-center gap-1.5">
                                <span>📊</span>
                                <span>Persentase Pembobotan Nilai Akhir Mahasiswa (Total Wajib 100%)</span>
                            </h4>
                            <span class="text-xs font-mono font-bold text-blue-600 bg-blue-50 px-2.5 py-1 rounded-lg border border-blue-200"
                                  :class="(Number(weightMentor) + Number(weightLecturer)) === 100 ? 'text-emerald-700 bg-emerald-50 border-emerald-300' : 'text-rose-700 bg-rose-50 border-rose-300'">
                                Total: <span x-text="Number(weightMentor) + Number(weightLecturer)"></span>%
                            </span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            
                            <!-- Bobot Mentor Dinas -->
                            <div class="space-y-1.5">
                                <div class="flex justify-between items-center text-xs">
                                    <label class="font-bold text-slate-700">🏢 Bobot Mentor Lapangan Dinas</label>
                                    <span class="font-mono font-bold text-blue-700 text-sm" x-text="weightMentor + '%'"></span>
                                </div>
                                <input type="range" min="0" max="100" step="5" x-model="weightMentor" @input="updateLecturer()" class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-blue-600">
                                <input type="number" name="weight_mentor" min="0" max="100" x-model="weightMentor" @input="updateLecturer()" class="w-full text-xs border-gray-300 rounded-xl font-mono text-center font-bold">
                                <p class="text-[11px] text-gray-400">Porsi nilai kinerja teknis, kedisiplinan, dan inisiatif di kantor dinas.</p>
                            </div>

                            <!-- Bobot DPL Kampus -->
                            <div class="space-y-1.5">
                                <div class="flex justify-between items-center text-xs">
                                    <label class="font-bold text-slate-700">👨‍🏫 Bobot Dosen Pembimbing (DPL)</label>
                                    <span class="font-mono font-bold text-purple-700 text-sm" x-text="weightLecturer + '%'"></span>
                                </div>
                                <input type="range" min="0" max="100" step="5" x-model="weightLecturer" @input="updateMentor()" class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-purple-600">
                                <input type="number" name="weight_lecturer" min="0" max="100" x-model="weightLecturer" @input="updateMentor()" class="w-full text-xs border-gray-300 rounded-xl font-mono text-center font-bold">
                                <p class="text-[11px] text-gray-400">Porsi nilai penguasaan materi, laporan akademik, dan sikap.</p>
                            </div>

                        </div>

                        <!-- Checkbox Wajib DPL -->
                        <div class="pt-2 border-t border-slate-200 flex items-center gap-2">
                            <input type="checkbox" name="require_dpl" value="1" id="require_dpl" {{ old('require_dpl', $university->require_dpl ?? true) ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <label for="require_dpl" class="text-xs text-gray-700 font-medium">
                                <strong>Kunci Pengisian Logbook</strong> hingga mahasiswa memilih Dosen Pembimbing Lapangan (DPL).
                            </label>
                        </div>
                    </div>

                    <!-- Notifikasi Banner Jika Skema 100% Dinas -->
                    <div x-show="scheme === 'mentor_only'" class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-xs text-amber-900 space-y-1">
                        <p class="font-bold">ℹ️ Informasi Mode Penilaian Penuh Instansi:</p>
                        <p class="text-amber-800 leading-relaxed">
                            Mahasiswa dari <strong>{{ $university->name }}</strong> dapat langsung mengisi logbook harian begitu diterima di dinas tanpa terhalang status DPL. Nilai akhir di sertifikat magang dihitung murni 100% dari Mentor Dinas.
                        </p>
                    </div>

                </div>

                <!-- Submit Button -->
                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-bold rounded-xl shadow-md transition active:scale-95 cursor-pointer">
                        💾 Simpan Perubahan Profil & Kebijakan Kampus
                    </button>
                </div>

            </form>

        </div>
    </div>
</x-app-layout>
