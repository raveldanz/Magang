<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-xl text-slate-800 leading-tight">
                {{ __('Pengajuan Magang & Riwayat') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-8 bg-[#F5F8FC] min-h-screen text-slate-900 font-sans">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Success Message -->
            @if (session('success'))
                <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl flex items-center gap-3 text-sm font-semibold shadow-sm">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <!-- Error Validasi Input -->
            @if ($errors->any())
                <div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-2xl text-xs space-y-1 shadow-sm">
                    <p class="font-bold">Pengajuan Gagal Dikirim:</p>
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Alert Penolakan dari Admin -->
            @if ($applicationHistory->first() && $applicationHistory->first()->status === 'rejected')
                <div class="p-5 bg-red-50 border border-red-200 rounded-2xl shadow-sm space-y-2">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <h3 class="text-sm font-bold text-red-900">Mohon Maaf, Pengajuan Magang Anda Ditolak</h3>
                    </div>
                    <div class="text-xs text-red-800">
                        <p class="font-semibold">Catatan / Alasan dari Admin:</p>
                        <p class="mt-1 italic bg-white p-3 rounded-xl border border-red-200 font-mono text-slate-800">
                            "{{ $applicationHistory->first()->rejection_note ?? 'Tidak ada catatan spesifik dari admin.' }}"
                        </p>
                    </div>
                    <p class="text-[11px] text-red-600">
                        *Silakan buat pengajuan baru di bawah dengan melengkapi/memperbaiki berkas sesuai catatan admin di atas.
                    </p>
                </div>
            @endif

            <!-- Alert Pengunduran Diri -->
            @if ($applicationHistory->first() && $applicationHistory->first()->status === 'resigned')
                <div class="p-5 bg-slate-50 border border-slate-200 rounded-2xl shadow-sm space-y-2">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-slate-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h3 class="text-sm font-bold text-slate-800">
                            Anda Telah Mengundurkan Diri dari Program Magang Sebelumnya
                        </h3>
                    </div>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Anda dapat mendaftar kembali ke program magang dengan memilih instansi & divisi kerja yang tersedia di bawah serta mengirimkan berkas pengajuan baru.
                    </p>
                </div>
            @endif

            <!-- 1. FORM PENGAJUAN BARU -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm shadow-slate-200/50 p-6 sm:p-8 space-y-6">

                @if ($activeApplication)
                    <div class="p-5 rounded-2xl border {{ $activeApplication->status === 'accepted' ? 'bg-emerald-50 border-emerald-200 text-emerald-950' : ($activeApplication->status === 'completed' ? 'bg-indigo-50 border-indigo-200 text-indigo-950' : 'bg-amber-50 border-amber-200 text-amber-950') }} shadow-xs space-y-2">
                        <div class="flex items-center gap-2 font-bold text-sm">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>
                                @if(in_array($activeApplication->status, ['pending', 'verified']))
                                    Pengajuan Magang Sedang Diproses (Status: {{ strtoupper($activeApplication->status) }})
                                @elseif($activeApplication->status === 'accepted')
                                    Anda Sudah Memiliki Penempatan Magang Aktif
                                @elseif($activeApplication->status === 'completed')
                                    Program Magang MBKM Telah Selesai
                                @endif
                            </span>
                        </div>
                        <p class="text-xs leading-relaxed">
                            @if(in_array($activeApplication->status, ['pending', 'verified']))
                                Berkas pengajuan magang Anda di <strong>{{ $activeApplication->unit->agencyProfile->agency_name ?? 'Pemerintah Kota Surabaya' }}</strong> (Divisi: {{ $activeApplication->unit->name ?? '-' }}) saat ini sedang dalam tahap seleksi & verifikasi oleh Tim Admin Dinas. Anda belum dapat mengajukan magang baru sampai proses ini selesai.
                            @elseif($activeApplication->status === 'accepted')
                                Selamat! Anda telah resmi diterima magang di <strong>{{ $activeApplication->unit->agencyProfile->agency_name ?? 'Pemerintah Kota Surabaya' }}</strong> (Divisi: {{ $activeApplication->unit->name ?? '-' }}). Silakan fokus pada pelaksanaan kegiatan magang harian Anda.
                            @elseif($activeApplication->status === 'completed')
                                Anda telah menyelesaikan seluruh rangkaian kegiatan magang MBKM serta menerima penilaian akhir resmi. Anda dapat mengunduh E-Sertifikat dan arsip laporan melalui menu <strong>Laporan Akhir</strong>.
                            @endif
                        </p>
                    </div>
                @else
                    <form action="{{ route('student.application.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                        @csrf

                        <!-- Bagian A: Pilihan Unit Kerja -->
                        <div class="space-y-1.5">
                            <x-input-label for="unit_id" value="Pilih Instansi & Unit Kerja / Divisi Magang *" class="text-xs font-bold uppercase tracking-wider text-slate-700" />
                            @php
                                $totalAvailable = $units->filter(fn($unit) => $unit->remaining_quota > 0)->count();
                            @endphp

                            <select id="unit_id" name="unit_id"
                                class="block w-full border-slate-200 bg-slate-50 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-400/40 rounded-xl shadow-xs text-xs sm:text-sm p-3 font-medium"
                                {{ $totalAvailable === 0 ? 'disabled' : '' }} required>
                                
                                @if ($totalAvailable === 0)
                                    <option value="" disabled selected>-- Maaf, saat ini seluruh kuota divisi magang sudah penuh --</option>
                                @else
                                    <option value="">-- Pilih Instansi & Bidang/Divisi Magang --</option>
                                    @foreach ($groupedUnits as $agencyName => $agencyUnits)
                                        <optgroup label="{{ strtoupper($agencyName) }}">
                                            @foreach ($agencyUnits as $unit)
                                                <option value="{{ $unit->id }}" 
                                                    {{ old('unit_id') == $unit->id ? 'selected' : '' }}
                                                    {{ $unit->remaining_quota <= 0 ? 'disabled class=text-slate-400' : '' }}>
                                                    {{ $unit->name }} &bull; Sisa Kuota: {{ $unit->remaining_quota }} {{ $unit->remaining_quota <= 0 ? '(PENUH)' : 'orang' }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                @endif
                            </select>
                            @error('unit_id')
                                <p class="text-xs text-red-600 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Bagian B: Periode Magang -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6" 
                             x-data="{ 
                                 startDate: '{{ old('start_date', '') }}',
                                 endDate: '{{ old('end_date', '') }}',
                                 today: '{{ date('Y-m-d') }}'
                             }">
                            
                            <!-- Tanggal Mulai -->
                            <div class="space-y-1.5">
                                <x-input-label for="start_date" value="Tanggal Mulai Magang *" class="text-xs font-bold uppercase tracking-wider text-slate-700" />
                                <x-text-input id="start_date" 
                                              name="start_date" 
                                              type="date" 
                                              x-model="startDate"
                                              class="block w-full text-xs sm:text-sm rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-400/40 focus:border-blue-500 p-2.5"
                                              :value="old('start_date')"
                                              required />
                                <x-input-error :messages="$errors->get('start_date')" class="mt-1" />
                            </div>

                            <!-- Tanggal Selesai -->
                            <div class="space-y-1.5">
                                <x-input-label for="end_date" value="Tanggal Selesai Magang *" class="text-xs font-bold uppercase tracking-wider text-slate-700" />
                                <x-text-input id="end_date" 
                                              name="end_date" 
                                              type="date" 
                                              x-bind:min="startDate"
                                              x-model="endDate"
                                              class="block w-full text-xs sm:text-sm rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-400/40 focus:border-blue-500 p-2.5"
                                              :value="old('end_date')"
                                              required />
                                <x-input-error :messages="$errors->get('end_date')" class="mt-1" />
                            </div>
                        </div>

                        <hr class="border-slate-100">

                        <!-- Upload Dokumen Persyaratan Magang -->
<div class="space-y-4">
    <h3 class="text-sm font-bold text-slate-800">Upload Dokumen Persyaratan Magang</h3>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        
        <!-- 1. Surat Pengantar / Proposal Kampus -->
        <div x-data="{ 
                fileName: '', 
                fileUrl: null, 
                handleFile(e) {
                    const file = e.target.files[0];
                    if (file) {
                        this.fileName = file.name;
                        this.fileUrl = URL.createObjectURL(file);
                    } else {
                        this.fileName = '';
                        this.fileUrl = null;
                    }
                } 
             }" 
             class="p-4 rounded-2xl border border-slate-200 bg-white shadow-2xs space-y-2">
            <label class="block text-xs font-bold text-slate-700">
                1. Surat Pengantar / Proposal Kampus <span class="text-red-500">*</span>
            </label>
            
            <div class="flex items-center gap-2">
                <input type="file" 
                       id="surat_pengantar" 
                       name="surat_pengantar" 
                       accept=".pdf" 
                       required 
                       @change="handleFile($event)" 
                       class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3.5 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer transition" />
            </div>

            <!-- Area Preview Feedback -->
            <div x-show="fileUrl" x-cloak class="flex items-center justify-between pt-1 border-t border-slate-100 text-xs">
                <a :href="fileUrl" target="_blank" class="inline-flex items-center gap-1 font-bold text-blue-600 hover:text-blue-800 hover:underline">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    Lihat Berkas
                </a>
            </div>
            <p x-show="!fileUrl" class="text-[11px] text-slate-400">Format: PDF (Maks. 2MB)</p>
            <x-input-error :messages="$errors->get('surat_pengantar')" class="mt-1" />
        </div>

        <!-- 2. Curriculum Vitae (CV) -->
        <div x-data="{ 
                fileName: '', 
                fileUrl: null, 
                handleFile(e) {
                    const file = e.target.files[0];
                    if (file) {
                        this.fileName = file.name;
                        this.fileUrl = URL.createObjectURL(file);
                    } else {
                        this.fileName = '';
                        this.fileUrl = null;
                    }
                } 
             }" 
             class="p-4 rounded-2xl border border-slate-200 bg-white shadow-2xs space-y-2">
            <label class="block text-xs font-bold text-slate-700">
                2. Curriculum Vitae (CV) <span class="text-red-500">*</span>
            </label>
            
            <div class="flex items-center gap-2">
                <input type="file" 
                       id="cv" 
                       name="cv" 
                       accept=".pdf" 
                       required 
                       @change="handleFile($event)" 
                       class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3.5 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer transition" />
            </div>

            <div x-show="fileUrl" x-cloak class="flex items-center justify-between pt-1 border-t border-slate-100 text-xs">
                <a :href="fileUrl" target="_blank" class="inline-flex items-center gap-1 font-bold text-blue-600 hover:text-blue-800 hover:underline">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    Lihat Berkas
                </a>
            </div>
            <p x-show="!fileUrl" class="text-[11px] text-slate-400">Format: PDF (Maks. 2MB)</p>
            <x-input-error :messages="$errors->get('cv')" class="mt-1" />
        </div>

        <!-- 3. Transkrip Nilai Akademik Terakhir -->
        <div x-data="{ 
                fileName: '', 
                fileUrl: null, 
                handleFile(e) {
                    const file = e.target.files[0];
                    if (file) {
                        this.fileName = file.name;
                        this.fileUrl = URL.createObjectURL(file);
                    } else {
                        this.fileName = '';
                        this.fileUrl = null;
                    }
                } 
             }" 
             class="p-4 rounded-2xl border border-slate-200 bg-white shadow-2xs space-y-2">
            <label class="block text-xs font-bold text-slate-700">
                3. Transkrip Nilai Akademik Terakhir <span class="text-red-500">*</span>
            </label>
            
            <div class="flex items-center gap-2">
                <input type="file" 
                       id="transkrip" 
                       name="transkrip" 
                       accept=".pdf" 
                       required 
                       @change="handleFile($event)" 
                       class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3.5 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer transition" />
            </div>

            <div x-show="fileUrl" x-cloak class="flex items-center justify-between pt-1 border-t border-slate-100 text-xs">
                <a :href="fileUrl" target="_blank" class="inline-flex items-center gap-1 font-bold text-blue-600 hover:text-blue-800 hover:underline">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    Lihat Berkas
                </a>
            </div>
            <p x-show="!fileUrl" class="text-[11px] text-slate-400">Format: PDF (Maks. 2MB)</p>
            <x-input-error :messages="$errors->get('transkrip')" class="mt-1" />
        </div>

        <!-- 4. Kartu Tanda Mahasiswa (KTM / ID Card) -->
        <div x-data="{ 
                fileName: '', 
                fileUrl: null, 
                handleFile(e) {
                    const file = e.target.files[0];
                    if (file) {
                        this.fileName = file.name;
                        this.fileUrl = URL.createObjectURL(file);
                    } else {
                        this.fileName = '';
                        this.fileUrl = null;
                    }
                } 
             }" 
             class="p-4 rounded-2xl border border-slate-200 bg-white shadow-2xs space-y-2">
            <label class="block text-xs font-bold text-slate-700">
                4. Kartu Tanda Mahasiswa (KTM / ID Card) <span class="text-red-500">*</span>
            </label>
            
            <div class="flex items-center gap-2">
                <input type="file" 
                       id="id_card" 
                       name="id_card" 
                       accept=".pdf,image/png,image/jpeg,image/jpg" 
                       required 
                       @change="handleFile($event)" 
                       class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3.5 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer transition" />
            </div>

            <div x-show="fileUrl" x-cloak class="flex items-center justify-between pt-1 border-t border-slate-100 text-xs">
                <a :href="fileUrl" target="_blank" class="inline-flex items-center gap-1 font-bold text-blue-600 hover:text-blue-800 hover:underline">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    Lihat Berkas
                </a>
            </div>
            <p x-show="!fileUrl" class="text-[11px] text-slate-400">Format: PDF, JPG, PNG (Maks. 2MB)</p>
            <x-input-error :messages="$errors->get('id_card')" class="mt-1" />
        </div>

    </div>
</div>

                        <!-- Action Buttons -->
                        <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center sm:justify-end gap-2.5 sm:gap-3 pt-5 border-t border-slate-100">
                            <a href="{{ route('dashboard') }}" class="w-full sm:w-auto px-5 py-3 sm:py-2.5 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 text-xs font-semibold uppercase tracking-wider rounded-xl transition-all duration-150 text-center flex items-center justify-center">
                                {{ __('Kembali ke dashboard') }}
                            </a>
                            <button type="submit" class="w-full sm:w-auto px-6 py-3 sm:py-2.5 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-xs font-bold uppercase tracking-wider rounded-xl shadow-xs transition-all duration-150 cursor-pointer flex items-center justify-center">
                                {{ __('Kirim Pengajuan Magang') }}
                            </button>
                        </div>
                    </form>
                @endif
            </div>

            <!-- 2. TABEL RIWAYAT PENGAJUAN MAGANG -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm shadow-slate-200/50 overflow-hidden">
                <div class="p-6 border-b border-slate-100">
                    <h3 class="text-base font-bold text-slate-900">Riwayat Pengajuan Magang Anda</h3>
                </div>

                <!-- Desktop View (Table) -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-max">
                        <thead>
                            <tr class="bg-slate-50/60 border-b border-slate-100 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                <th class="py-3.5 px-5 whitespace-nowrap">Tanggal Pengajuan</th>
                                <th class="py-3.5 px-5 whitespace-nowrap">Unit Instansi</th>
                                <th class="py-3.5 px-5 whitespace-nowrap">Periode Magang</th>
                                <th class="py-3.5 px-5 whitespace-nowrap">Status</th>
                                <th class="py-3.5 px-5 whitespace-nowrap">Catatan / Alasan Admin</th>
                                <th class="py-3.5 px-5 whitespace-nowrap">Surat Penerimaan</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm divide-y divide-slate-100">
                            @forelse ($applicationHistory as $app)
                                <tr class="hover:bg-slate-50/70 transition-colors duration-150">
                                    <td class="py-4 px-5 text-slate-500 font-mono text-xs whitespace-nowrap">
                                        {{ $app->created_at->format('d M Y, H:i') }}
                                    </td>
                                    <td class="py-4 px-5">
                                        <div class="font-bold text-slate-900 leading-snug">{{ $app->unit->name ?? '-' }}</div>
                                        <div class="text-xs text-slate-400 mt-0.5">{{ $app->unit->agencyProfile->agency_name ?? '-' }}</div>
                                    </td>
                                    <td class="py-4 px-5 text-xs text-slate-700 whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($app->start_date)->translatedFormat('d M Y') }} s/d {{ \Carbon\Carbon::parse($app->end_date)->translatedFormat('d M Y') }}
                                    </td>
                                    <td class="py-4 px-5 whitespace-nowrap">
                                        @php $st = strtolower($app->status ?? ''); @endphp
                                        <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-bold rounded-full border
                                            {{ in_array($st, ['accepted', 'completed']) ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : '' }}
                                            {{ $st === 'pending' ? 'bg-amber-50 text-amber-700 border-amber-200' : '' }}
                                            {{ $st === 'verified' ? 'bg-blue-50 text-blue-700 border-blue-200' : '' }}
                                            {{ $st === 'rejected' ? 'bg-red-50 text-red-700 border-red-200' : '' }}">
                                            {{ strtoupper($app->status) }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-5 text-xs">
                                        @if ($app->status === 'rejected')
                                            <span class="text-red-700 font-medium bg-red-50 px-2.5 py-1 rounded-lg border border-red-200 inline-block">
                                                {{ $app->rejection_note ?? 'Tidak ada catatan' }}
                                            </span>
                                        @else
                                            <span class="text-slate-400">-</span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-5 whitespace-nowrap">
                                        @if (in_array($app->status, ['accepted', 'completed']))
                                            <a href="{{ route('student.application.letter', $app->id) }}" target="_blank" 
                                               class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-100 rounded-xl text-xs font-semibold transition">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                                <span>Unduh Surat PDF</span>
                                            </a>
                                        @else
                                            <span class="text-slate-400 text-xs italic">Belum tersedia</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-slate-400 text-xs">
                                        Belum ada riwayat pengajuan magang.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Mobile View (Cards) -->
                <div class="block md:hidden border-t border-slate-100 divide-y divide-slate-100">
                    @forelse ($applicationHistory as $app)
                        <div class="p-5 space-y-3 hover:bg-slate-50/70 transition-colors duration-150">
                            <!-- Header: Tanggal & Status -->
                            <div class="flex justify-between items-start gap-2">
                                <div class="text-xs text-slate-500 font-mono">
                                    {{ $app->created_at->format('d M Y, H:i') }}
                                </div>
                                @php $st = strtolower($app->status ?? ''); @endphp
                                <span class="inline-flex items-center px-2 py-0.5 text-[10px] font-bold rounded-full border
                                    {{ in_array($st, ['accepted', 'completed']) ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : '' }}
                                    {{ $st === 'pending' ? 'bg-amber-50 text-amber-700 border-amber-200' : '' }}
                                    {{ $st === 'verified' ? 'bg-blue-50 text-blue-700 border-blue-200' : '' }}
                                    {{ $st === 'rejected' ? 'bg-red-50 text-red-700 border-red-200' : '' }}">
                                    {{ strtoupper($app->status) }}
                                </span>
                            </div>

                            <!-- Body: Unit -->
                            <div>
                                <div class="font-bold text-slate-900 text-sm leading-snug">{{ $app->unit->name ?? '-' }}</div>
                                <div class="text-xs text-slate-500 mt-0.5">{{ $app->unit->agencyProfile->agency_name ?? '-' }}</div>
                            </div>
                            
                            <!-- Body: Periode Magang -->
                            <div class="text-xs text-slate-600 bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                                <span class="font-semibold block mb-1">Periode Magang:</span>
                                {{ \Carbon\Carbon::parse($app->start_date)->translatedFormat('d M Y') }} s/d {{ \Carbon\Carbon::parse($app->end_date)->translatedFormat('d M Y') }}
                            </div>

                            <!-- Footer: Catatan & Surat -->
                            @if ($app->status === 'rejected')
                                <div class="text-xs">
                                    <span class="font-semibold block text-slate-700 mb-1">Catatan Admin:</span>
                                    <span class="text-red-700 font-medium bg-red-50 px-3 py-2 rounded-xl border border-red-200 block">
                                        {{ $app->rejection_note ?? 'Tidak ada catatan' }}
                                    </span>
                                </div>
                            @endif

                            @if (in_array($app->status, ['accepted', 'completed']))
                                <div class="pt-2">
                                    <a href="{{ route('student.application.letter', $app->id) }}" target="_blank" 
                                       class="inline-flex w-full justify-center items-center gap-1.5 px-3 py-2 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-100 rounded-xl text-xs font-semibold transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                        <span>Unduh Surat PDF</span>
                                    </a>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="py-8 text-center text-slate-400 text-xs">
                            Belum ada riwayat pengajuan magang.
                        </div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
</x-app-layout>