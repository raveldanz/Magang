<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard Mahasiswa') }}
        </h2>
    </x-slot>

    <div class="py-8" x-data="{ openNewDosenModal: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Success Message -->
            @if (session('success'))
                <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-lg shadow-sm flex items-center justify-between text-emerald-900 text-sm font-medium">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            <!-- Welcome Banner -->
            <div class="bg-gradient-to-r from-indigo-600 to-indigo-800 rounded-2xl p-6 text-white shadow-md flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h3 class="text-2xl font-bold">Selamat Datang, {{ Auth::user()->name }}!</h3>
                    <p class="text-indigo-100 text-xs sm:text-sm mt-1">
                        Sistem Informasi Penerimaan Magang Instansi Pemerintah Kota Surabaya
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="bg-indigo-900/60 border border-indigo-400/30 text-indigo-100 text-xs font-semibold px-3 py-1.5 rounded-full uppercase">
                        🎓 {{ $univName ?? 'Perguruan Tinggi Mahasiswa' }}
                    </span>
                </div>
            </div>

            @php
                $isPassed = $application && $application->status === 'accepted' && 
                            optional($application->placement)->evaluation && 
                            optional(optional($application->placement)->finalreport)->status === 'approved';
                $placement = $application ? $application->placement : null;
                $mentor = $placement ? ($placement->mentor ?? $placement->pembimbing) : null;
                $academicAdvisor = $placement ? ($placement->academicAdvisor ?? $placement->dosen) : null;
            @endphp

            <!-- Banner Kelulusan & Unduh E-Sertifikat -->
            @if ($isPassed)
                <div class="bg-gradient-to-r from-emerald-600 to-teal-700 rounded-2xl p-6 text-white shadow-lg flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                    <div class="flex items-center space-x-4">
                        <div class="p-3 bg-white/20 rounded-2xl shadow-inner">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold">🎉 Selamat! Anda telah menyelesaikan seluruh rangkaian magang.</h3>
                            <p class="text-emerald-100 mt-1 text-sm">Laporan akhir Anda telah disetujui dan seluruh evaluasi telah lengkap. Anda dapat mengunduh E-Sertifikat resmi.</p>
                        </div>
                    </div>
                    <div class="flex-shrink-0">
                        <a href="{{ route('student.certificate.download', $placement->id) }}" 
                           class="inline-flex items-center space-x-2 px-5 py-2.5 bg-white text-emerald-800 font-extrabold text-sm rounded-xl shadow-md hover:bg-emerald-50 transition transform hover:-translate-y-0.5">
                            <span>📜 Unduh E-Sertifikat</span>
                        </a>
                    </div>
                </div>
            @endif

            <!-- 3. Form / Modal Pemilihan Dosen Pembimbing Kampus (Jika Diterima) -->
            @if ($application && $application->status === 'accepted')
                <div class="bg-white rounded-2xl p-6 border-2 {{ $academicAdvisor ? 'border-emerald-200' : 'border-indigo-300 bg-indigo-50/20' }} shadow-sm space-y-4">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 border-b border-gray-100 pb-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 {{ $academicAdvisor ? 'bg-emerald-100 text-emerald-700' : 'bg-indigo-100 text-indigo-700' }} rounded-xl flex items-center justify-center font-bold">
                                🎓
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900 text-base flex items-center gap-2">
                                    Dosen Pembimbing Lapangan (DPL Kampus)
                                    @if ($academicAdvisor)
                                        <span class="px-2.5 py-0.5 text-xs font-bold bg-emerald-100 text-emerald-800 rounded-full">
                                            ✅ Terpilih
                                        </span>
                                    @else
                                        <span class="px-2.5 py-0.5 text-xs font-bold bg-amber-100 text-amber-800 rounded-full animate-pulse">
                                            ⚠️ Perlu Ditentukan
                                        </span>
                                    @endif
                                </h3>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    Dosen pembimbing dari <strong>{{ $univName ?? 'Perguruan Tinggi Anda' }}</strong> yang bertugas memonitor dan memberikan nilai akademik
                                </p>
                            </div>
                        </div>

                        <!-- Tombol Modal Input Dosen Baru -->
                        <button type="button" @click="openNewDosenModal = true" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 text-xs font-bold rounded-xl transition shadow-xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            <span>Input Dosen Baru</span>
                        </button>
                    </div>

                    @if ($academicAdvisor)
                        <!-- Tampilan Dosen yang Sudah Dipilih -->
                        <div class="p-4 bg-emerald-50/50 rounded-xl border border-emerald-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                            <div>
                                <div class="font-bold text-gray-900 text-sm flex items-center gap-2">
                                    👨‍🏫 {{ $academicAdvisor->name }}
                                </div>
                                <div class="text-xs text-gray-600 mt-1">
                                    Email: <span class="font-mono text-gray-800">{{ $academicAdvisor->email }}</span> &bull; Kampus: <strong>{{ $academicAdvisor->university?->name ?? $academicAdvisor->university ?? $univName }}</strong>
                                </div>
                            </div>

                            <button type="button" onclick="document.getElementById('change-advisor-box').classList.toggle('hidden')" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 bg-white px-3 py-1.5 rounded-lg border border-gray-200 shadow-xs transition">
                                Ganti Dosen Pembimbing
                            </button>
                        </div>
                    @else
                        <!-- Alert Belum Memilih Dosen -->
                        <div class="p-4 bg-indigo-50/80 rounded-xl border border-indigo-200 text-xs text-indigo-950 flex items-start gap-3">
                            <span class="text-lg">📢</span>
                            <div>
                                <p class="font-bold">Pengajuan magang Anda telah DITERIMA di {{ $application->unit->name ?? '-' }} ({{ $application->unit->agencyProfile->agency_name ?? '-' }}).</p>
                                <p class="text-indigo-800 mt-1">Silakan pilih Dosen Pembimbing terdaftar di kampus Anda atau klik tombol <strong>"Input Dosen Baru"</strong> jika nama dosen belum tertera pada daftar.</p>
                            </div>
                        </div>
                    @endif

                    <!-- Form Pilihan Dosen (Selalu tampil jika belum memilih, atau toggle jika ingin ganti) -->
                    <div id="change-advisor-box" class="{{ $academicAdvisor ? 'hidden' : '' }} pt-2">
                        <form action="{{ route('student.select_advisor') }}" method="POST" class="space-y-3">
                            @csrf
                            <div>
                                <div class="flex justify-between items-center mb-1">
                                    <label for="academic_advisor_id" class="block text-xs font-bold text-gray-700 uppercase tracking-wider">
                                        Pilih Dosen Terdaftar ({{ $univName ?? 'Kampus Mahasiswa' }}):
                                    </label>
                                    <button type="button" @click="openNewDosenModal = true" class="text-[11px] text-indigo-600 hover:text-indigo-800 font-semibold underline">
                                        Dosen tidak ditemukan? Input Baru
                                    </button>
                                </div>
                                <select id="academic_advisor_id" name="academic_advisor_id" required class="w-full text-xs sm:text-sm border-gray-300 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 shadow-xs">
                                    <option value="">-- Pilih Dosen Pembimbing Kampus --</option>
                                    @foreach ($availableDosens as $dosen)
                                        <option value="{{ $dosen->id }}" {{ optional($placement)->academic_advisor_id == $dosen->id ? 'selected' : '' }}>
                                            {{ $dosen->name }} — {{ $dosen->university?->name ?? $dosen->university ?? 'Dosen' }} ({{ $dosen->email }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="flex items-center gap-3">
                                <x-primary-button type="submit" class="text-xs">
                                    {{ __('Simpan Dosen Pembimbing') }}
                                </x-primary-button>
                                @if ($academicAdvisor)
                                    <button type="button" onclick="document.getElementById('change-advisor-box').classList.add('hidden')" class="px-3 py-2 text-xs font-bold text-gray-600 hover:text-gray-800">
                                        Batal
                                    </button>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            <!-- Modal Input Dosen Baru -->
            <div x-show="openNewDosenModal" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
                
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity" @click="openNewDosenModal = false"></div>

                <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                    <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-gray-100 p-6 space-y-4">
                        
                        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                            <div class="flex items-center gap-2">
                                <span class="p-2 bg-indigo-100 text-indigo-700 rounded-xl font-bold text-sm">👨‍🏫</span>
                                <h3 class="text-lg font-bold text-gray-900 leading-6">Input Dosen Pembimbing Baru</h3>
                            </div>
                            <button type="button" @click="openNewDosenModal = false" class="text-gray-400 hover:text-gray-600 text-sm">
                                ✕
                            </button>
                        </div>

                        <p class="text-xs text-gray-500 leading-relaxed">
                            Jika dosen pembimbing Anda belum terdaftar di sistem, silakan isi data di bawah ini. Akun portal dosen akan otomatis dibuatkan dan terhubung ke universitas Anda.
                        </p>

                        <form action="{{ route('student.create_advisor') }}" method="POST" class="space-y-4">
                            @csrf
                            <input type="hidden" name="university_id" value="{{ Auth::user()->university_id }}">

                            <div>
                                <label for="modal_name" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                    Nama Lengkap Beserta Gelar <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" id="modal_name" name="name" required placeholder="Contoh: Dr. Ir. Ahmad Sudrajat, M.Kom" 
                                    class="w-full text-xs sm:text-sm border-gray-300 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 shadow-xs">
                            </div>

                            <div>
                                <label for="modal_email" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                    Email Resmi / Kampus Dosen <span class="text-rose-500">*</span>
                                </label>
                                <input type="email" id="modal_email" name="email" required placeholder="Contoh: ahmad.sudrajat@kampus.ac.id" 
                                    class="w-full text-xs sm:text-sm border-gray-300 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 shadow-xs">
                            </div>

                            <div>
                                <label for="modal_nidn" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                    NIDN / NIP Dosen (Opsional)
                                </label>
                                <input type="text" id="modal_nidn" name="nidn" placeholder="Contoh: 0012345678" 
                                    class="w-full text-xs sm:text-sm border-gray-300 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 shadow-xs">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                    Asal Perguruan Tinggi:
                                </label>
                                <input type="text" value="{{ $univName ?? 'Universitas Mahasiswa' }}" disabled 
                                    class="w-full text-xs bg-gray-100 text-gray-600 border-gray-300 rounded-xl shadow-xs cursor-not-allowed">
                            </div>

                            <div class="pt-3 border-t border-gray-100 flex items-center justify-end gap-3">
                                <button type="button" @click="openNewDosenModal = false" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-xl transition">
                                    Batal
                                </button>
                                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-sm transition">
                                    Daftarkan & Pilih Sebagai DPL
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>

            <!-- Status Card Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                
                <!-- Card 1: Profil -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200 border-l-4 {{ $profile ? 'border-l-emerald-500' : 'border-l-amber-500' }} space-y-3">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase">Status Profil</p>
                            <p class="text-lg font-black mt-1 text-gray-800">
                                {{ $profile ? 'Lengkap' : 'Belum Lengkap' }}
                            </p>
                        </div>
                        <div class="p-3 bg-gray-50 text-gray-600 rounded-xl">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                    </div>
                    <a href="{{ route('student.profile.edit') }}" class="inline-block text-xs font-bold text-indigo-600 hover:text-indigo-800">
                        {{ $profile ? 'Edit Data Profil →' : 'Lengkapi Profil Sekarang →' }}
                    </a>
                </div>

                <!-- Card 2: Status -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200 border-l-4 
                    {{ optional($application)->lifecycle_status === 'ACTIVE' ? 'border-l-emerald-500' : '' }}
                    {{ optional($application)->lifecycle_status === 'COMPLETED' ? 'border-l-purple-500' : '' }}
                    {{ optional($application)->lifecycle_status === 'ACCEPTED' ? 'border-l-blue-500' : '' }}
                    {{ optional($application)->lifecycle_status === 'PENDING' ? 'border-l-amber-500' : '' }}
                    {{ optional($application)->lifecycle_status === 'REJECTED' ? 'border-l-rose-500' : '' }}
                    {{ !$application ? 'border-l-gray-300' : '' }} space-y-3">
                    <div class="flex justify-between items-center">
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">STATUS</div>
                            <div class="mt-1 flex items-center gap-2">
                                @if(!$application)
                                    <span class="inline-flex items-center gap-1.5 rounded-lg bg-gray-50 px-2.5 py-1 text-sm font-bold text-gray-600 border border-gray-200">
                                        Belum Mengajukan
                                    </span>
                                @elseif($application->lifecycle_status === 'ACTIVE')
                                    <span class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-50 px-2.5 py-1 text-sm font-bold text-emerald-700 border border-emerald-200">
                                        <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                        ACTIVE (Sedang Magang)
                                    </span>
                                @elseif($application->lifecycle_status === 'COMPLETED')
                                    <span class="inline-flex items-center gap-1.5 rounded-lg bg-purple-50 px-2.5 py-1 text-sm font-bold text-purple-700 border border-purple-200">
                                        COMPLETED (Lulus)
                                    </span>
                                @elseif($application->lifecycle_status === 'ACCEPTED')
                                    <span class="inline-flex items-center gap-1.5 rounded-lg bg-blue-50 px-2.5 py-1 text-sm font-bold text-blue-700 border border-blue-200">
                                        ACCEPTED (Calon Peserta)
                                    </span>
                                @elseif($application->lifecycle_status === 'REJECTED')
                                    <span class="inline-flex items-center gap-1.5 rounded-lg bg-rose-50 px-2.5 py-1 text-sm font-bold text-rose-700 border border-rose-200">
                                        REJECTED
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-lg bg-amber-50 px-2.5 py-1 text-sm font-bold text-amber-700 border border-amber-200">
                                        PENDING
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="p-3 bg-gray-50 text-gray-600 rounded-xl">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                    </div>
                    @if(!$application)
                        <a href="{{ route('student.application.create') }}" class="inline-block text-xs font-bold text-indigo-600 hover:text-indigo-800">
                            Buat Pengajuan Baru →
                        </a>
                    @else
                        <div class="text-xs text-gray-500">Unit: <strong>{{ $application->unit->name ?? '-' }}</strong></div>
                    @endif
                </div>

                <!-- Card 3: Pembimbing Lapangan Dinas -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200 border-l-4 border-l-blue-500 space-y-3">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase">Pembimbing Lapangan (Dinas)</p>
                            <p class="text-base font-bold mt-1 text-gray-800">
                                {{ $mentor ? $mentor->name : 'Belum Diplot Dinas' }}
                            </p>
                        </div>
                        <div class="p-3 bg-blue-50 text-blue-600 rounded-xl">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <p class="text-[11px] text-gray-400">Ditugaskan resmi oleh instansi penempatan magang Anda.</p>
                </div>

            </div>

            <!-- Detail Banner Pengajuan Aktif -->
            @if ($application)
                <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm space-y-4">
                    <div class="flex justify-between items-center border-b border-gray-100 pb-3">
                        <h4 class="font-bold text-gray-800 text-base">Detail Pengajuan Magang Aktif</h4>
                        @if ($application->status === 'accepted')
                            <a href="{{ route('student.application.letter', $application->id) }}" target="_blank" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 inline-flex items-center gap-1">
                                <span>📄 Unduh Surat Balasan Dinas</span>
                            </a>
                        @endif
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs sm:text-sm">
                        <p><span class="text-gray-500">Instansi Penempatan:</span> <strong>{{ $application->unit->agencyProfile->agency_name ?? '-' }}</strong></p>
                        <p><span class="text-gray-500">Unit Kerja:</span> <strong>{{ $application->unit->name ?? '-' }}</strong></p>
                        <p><span class="text-gray-500">Periode Magang:</span> <strong>{{ \Carbon\Carbon::parse($application->start_date)->translatedFormat('d M Y') }} s/d {{ \Carbon\Carbon::parse($application->end_date)->translatedFormat('d M Y') }}</strong></p>
                        <p><span class="text-gray-500">Status Saat Ini:</span> 
                            @if($application->lifecycle_status === 'ACTIVE')
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-bold text-emerald-800 border border-emerald-200">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    ACTIVE (Sedang Magang)
                                </span>
                            @elseif($application->lifecycle_status === 'COMPLETED')
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-purple-100 px-2.5 py-0.5 text-xs font-bold text-purple-800 border border-purple-200">
                                    COMPLETED (Lulus)
                                </span>
                            @elseif($application->lifecycle_status === 'ACCEPTED')
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-bold text-blue-800 border border-blue-200">
                                    ACCEPTED (Calon Peserta)
                                </span>
                            @elseif($application->lifecycle_status === 'REJECTED')
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-rose-100 px-2.5 py-0.5 text-xs font-bold text-rose-800 border border-rose-200">
                                    REJECTED
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-bold text-amber-800 border border-amber-200">
                                    PENDING
                                </span>
                            @endif
                        </p>
                        @if ($application->status === 'rejected' || $application->lifecycle_status === 'REJECTED')
                            <div class="col-span-1 md:col-span-2 p-3 bg-rose-50 border border-rose-200 rounded-xl text-rose-800 text-xs">
                                <strong>Catatan Penolakan:</strong> {{ $application->rejection_reason ?? $application->rejection_note }}
                            </div>
                        @endif
                    </div>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>