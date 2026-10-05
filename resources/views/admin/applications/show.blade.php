<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.applications.index') }}" 
                   class="p-2 rounded-xl bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition shadow-2xs" 
                   title="Kembali ke Daftar Pengajuan">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
                <div>
                    <h2 class="font-black text-xl sm:text-2xl text-gray-900 tracking-tight flex items-center gap-2">
                        <span>Verifikasi Pengajuan: {{ $application->user->name }}</span>
                    </h2>
                    <p class="text-xs sm:text-sm text-gray-500 mt-0.5">
                        Tinjau profil pemohon, verifikasi kelengkapan berkas, dan tetapkan status seleksi
                    </p>
                </div>
            </div>

            <!-- Current Status Badge in Header -->
            <div class="flex items-center gap-2">
                <x-chat-button :user="$application->user" label="Chat Pelamar" />
                <x-status-badge :status="$application->status" />
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Notifications -->
            @if (session('success'))
                <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-xl shadow-xs flex items-center justify-between text-emerald-900 text-sm font-medium">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="p-4 bg-rose-50 border-l-4 border-rose-500 rounded-r-xl shadow-xs flex items-center justify-between text-rose-900 text-sm font-medium">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span>{{ session('error') }}</span>
                    </div>
                </div>
            @endif

            @if ($errors->any())
                <div class="p-4 bg-rose-50 border-l-4 border-rose-500 rounded-r-xl shadow-xs text-rose-900 text-sm font-medium space-y-1">
                    <div class="font-bold flex items-center gap-2">
                        <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span>Terdapat kesalahan validasi pada formulir:</span>
                    </div>
                    <ul class="list-disc list-inside text-xs text-rose-700 pl-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- 1. Data Profil & Pengajuan Mahasiswa -->
            <div class="bg-white p-6 sm:p-7 rounded-2xl border border-slate-200/80 shadow-xs">
                <div class="flex items-center gap-2.5 pb-4 border-b border-slate-100 mb-5">
                    <div class="w-8 h-8 rounded-xl bg-blue-50 border border-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Informasi Pemohon Magang</h3>
                        <p class="text-xs text-slate-500">Data identitas mahasiswa, kampus asal, dan posisi unit magang yang diajukan</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 text-xs">
                    <div class="p-3.5 rounded-xl bg-slate-50/80 border border-slate-100 space-y-1">
                        <span class="text-slate-400 font-semibold uppercase text-[10px] tracking-wider block">Mahasiswa</span>
                        <div class="font-bold text-slate-900 text-sm">{{ $application->user->name }}</div>
                        <div class="text-slate-500 font-mono">NIM: {{ $application->user->studentProfile->nim ?? '-' }}</div>
                        <div class="text-slate-500">Telp: {{ $application->user->studentProfile->phone ?? '-' }}</div>
                    </div>

                    <div class="p-3.5 rounded-xl bg-slate-50/80 border border-slate-100 space-y-1">
                        <span class="text-slate-400 font-semibold uppercase text-[10px] tracking-wider block">Asal Perguruan Tinggi</span>
                        <div class="font-bold text-slate-900 text-sm">{{ $application->user->studentProfile->universitas ?? $application->user->university ?? '-' }}</div>
                        <div class="text-slate-500">{{ $application->user->studentProfile->faculty ?? $application->user->studentProfile->fakultas ?? '-' }}</div>
                        <div class="text-slate-500 font-medium text-blue-700">{{ $application->user->studentProfile->major ?? $application->user->studentProfile->jurusan ?? '-' }}</div>
                    </div>

                    <div class="p-3.5 rounded-xl bg-blue-50/50 border border-blue-100 space-y-1 md:col-span-2 lg:col-span-1">
                        <span class="text-blue-600 font-semibold uppercase text-[10px] tracking-wider block">Tujuan Magang</span>
                        <div class="font-bold text-slate-900 text-sm">{{ $application->unit->name ?? '-' }}</div>
                        <div class="text-xs font-semibold text-blue-800">
                            {{ $application->unit->agencyProfile->agency_name ?? $application->unit->agencyProfile->name ?? 'Pemerintah Kota Surabaya' }}
                        </div>
                        <div class="text-slate-500 mt-1 font-mono text-[11px]">
                            {{ \Carbon\Carbon::parse($application->start_date)->translatedFormat('d M Y') }} &mdash; {{ \Carbon\Carbon::parse($application->end_date)->translatedFormat('d M Y') }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Dokumen Persyaratan Berkas -->
            <div class="bg-white p-6 sm:p-7 rounded-2xl border border-slate-200/80 shadow-xs">
                <div class="flex items-center gap-2.5 pb-4 border-b border-slate-100 mb-5">
                    <div class="w-8 h-8 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Dokumen Persyaratan</h3>
                        <p class="text-xs text-slate-500">Berkas administrasi yang diunggah mahasiswa untuk diverifikasi</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @forelse ($application->documents as $doc)
                        <div class="flex items-center justify-between p-3.5 rounded-xl bg-slate-50/80 border border-slate-100 hover:border-blue-200 transition">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-9 h-9 rounded-lg bg-rose-50 border border-rose-100 text-rose-600 flex items-center justify-center shrink-0 font-bold text-[10px]">
                                    PDF
                                </div>
                                <div class="min-w-0">
                                    <div class="font-bold text-xs text-slate-800 truncate">{{ $doc->document_type }}</div>
                                    <div class="text-[11px] text-slate-400 mt-0.5">Format dokumen terverifikasi</div>
                                </div>
                            </div>
                            <a href="{{ route('documents.application', $doc->id) }}" target="_blank"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold shadow-2xs transition active:scale-95 shrink-0 cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                <span>Lihat PDF</span>
                            </a>
                            <a href="{{ route('documents.application.download', $doc->id) }}"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 rounded-lg text-xs font-bold shadow-2xs transition active:scale-95 shrink-0 cursor-pointer"
                               title="Unduh berkas">
                                <span>Unduh</span>
                            </a>
                        </div>
                    @empty
                        <div class="col-span-2 p-6 text-center text-xs text-slate-400">
                            Tidak ada dokumen yang diunggah untuk pengajuan ini.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- 3. Laporan Akhir Mahasiswa (Jika Ada) -->
            @if ($application->placement && $application->placement->finalreport)
                <div class="bg-white p-6 sm:p-7 rounded-2xl border border-slate-200/80 shadow-xs">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-4">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-purple-50 border border-purple-100 text-purple-600 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900">Laporan Akhir Mahasiswa</h3>
                                <p class="text-xs text-slate-500">Naskah laporan akhir sebagai syarat kelulusan magang</p>
                            </div>
                        </div>

                        <a href="{{ route('final_reports.show', $application->placement->finalreport->id) }}" target="_blank"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-xs transition active:scale-95 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            <span>Buka Naskah Laporan</span>
                        </a>
                    </div>
                    <div class="text-xs text-slate-600 flex items-center gap-2">
                        <span class="text-slate-400">Status Validasi Laporan:</span>
                        <x-status-badge type="review" :status="$application->placement->finalreport->status" />
                    </div>
                </div>
            @endif

            <!-- 4. Evaluasi Akhir & Transkrip Nilai -->
            @if ($application->placement && $application->placement->evaluation)
                @php $eval = $application->placement->evaluation; @endphp
                <div class="bg-white p-6 sm:p-7 rounded-2xl border border-indigo-200 shadow-xs space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-indigo-100 pb-3">
                        <div>
                            <span class="text-[11px] font-bold uppercase tracking-wider text-indigo-600 block">Evaluasi & Transkrip Kelulusan Magang</span>
                            <h3 class="text-base font-black text-gray-900 mt-0.5">Rekapitulasi Penilaian Akhir Mahasiswa</h3>
                        </div>
                        <a href="{{ route('admin.certificates.show', $application->placement->id) }}" target="_blank"
                            class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition shadow-xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span>Pratinjau E-Sertifikat & Transkrip</span>
                        </a>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-100 text-center">
                            <div class="text-[11px] font-semibold text-slate-500 uppercase">Nilai Disiplin</div>
                            <div class="text-lg font-black text-slate-800 mt-1">{{ number_format($eval->nilai_disiplin ?? 0, 1) }}</div>
                        </div>
                        <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-100 text-center">
                            <div class="text-[11px] font-semibold text-slate-500 uppercase">Nilai Kinerja</div>
                            <div class="text-lg font-black text-slate-800 mt-1">{{ number_format($eval->nilai_kinerja ?? 0, 1) }}</div>
                        </div>
                        <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-100 text-center">
                            <div class="text-[11px] font-semibold text-slate-500 uppercase">Nilai Laporan</div>
                            <div class="text-lg font-black text-slate-800 mt-1">{{ number_format($eval->nilai_laporan ?? 0, 1) }}</div>
                        </div>
                        <div class="p-3.5 bg-indigo-50 rounded-xl border border-indigo-100 text-center">
                            <div class="text-[11px] font-bold text-indigo-700 uppercase">Nilai Akhir (Predikat)</div>
                            <div class="text-lg font-black text-indigo-900 mt-1">
                                {{ number_format((float) $eval->nilai_akhir, 1) }}
                                <span class="text-xs font-bold text-indigo-600 font-mono">({{ $eval->grade_calculated }})</span>
                            </div>
                        </div>
                    </div>
                </div>
            @elseif($application->placement && in_array($application->statusValue(), ['accepted', 'active'], true) && $application->has_approved_report)
                <div class="bg-amber-50/80 p-5 sm:p-6 rounded-2xl border border-amber-200/90 flex items-start gap-3.5">
                    <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0 mt-0.5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-amber-900">Menunggu Input Nilai Evaluasi Magang</h4>
                        <p class="text-xs text-amber-800/90 mt-1 leading-relaxed">
                            Naskah laporan akhir mahasiswa telah <strong>disetujui</strong>, namun penilaian magang dari <strong>Pembimbing Lapangan / Dosen Pembimbing</strong> belum diisi secara lengkap. Tombol status keputusan <strong>{{ \App\Enums\ApplicationStatus::COMPLETED->label() }}</strong> di bawah akan otomatis aktif setelah seluruh komponen penilaian diisi.
                        </p>
                    </div>
                </div>
            @endif

            <!-- 5. Form Aksi Verifikasi & Seleksi (Modern Alpine.js Segmented Selector) -->
            @php
                $currentStatus = $application->status instanceof \BackedEnum ? $application->status->value : (string)$application->status;
                $currentStatusVal = $currentStatus;
            @endphp
            <div class="bg-white p-6 sm:p-7 rounded-2xl border border-slate-200/80 shadow-xs"
                 x-data="{ 
                     status: '{{ old('status', $currentStatusVal) }}' 
                 }"
                 x-init="$watch('status', val => {
                     const sel = document.getElementById('status-select');
                     if (sel) {
                         sel.value = val;
                         sel.dispatchEvent(new Event('change'));
                     }
                 })">
                
                <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-100 mb-6 gap-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-blue-50 border border-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Aksi Verifikasi & Seleksi</h3>
                            <p class="text-xs text-slate-500">Tentukan status seleksi berkas mahasiswa dan plotting mentor lapangan</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 text-xs text-slate-500 self-start sm:self-auto">
                        <span>Status saat ini</span>
                        <x-status-badge :status="$application->status" />
                    </div>
                </div>

                <form action="{{ route('admin.applications.updateStatus', $application->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    @php
                        $canComplete = false;
                        if ($application->placement) {
                            $hasApprovedReport = $application->placement->finalreport && in_array(strtolower($application->placement->finalreport->status instanceof \BackedEnum ? $application->placement->finalreport->status->value : ($application->placement->finalreport->status ?? '')), ['approved', 'disetujui']);
                            $eval = $application->placement->evaluation;
                            $hasCompleteEval = $eval && (($eval->nilai_pembimbing > 0 && $eval->nilai_dosen_calculated > 0) || $eval->nilai_akhir > 0);
                            $canComplete = $hasApprovedReport && $hasCompleteEval;
                        }
                    @endphp

                    <!-- Form Status Pipeline (Synchronized with visual cards) -->
                    <select id="status-select" name="status" x-model="status" class="hidden" aria-hidden="true">
                        <option value="pending" {{ $currentStatus == 'pending' ? 'selected' : '' }}>{{ \App\Enums\ApplicationStatus::PENDING->label() }}</option>
                        <option value="verified" {{ $currentStatus == 'verified' ? 'selected' : '' }}>{{ \App\Enums\ApplicationStatus::VERIFIED->label() }}</option>
                        <option value="accepted" {{ $currentStatus == 'accepted' ? 'selected' : '' }}>{{ \App\Enums\ApplicationStatus::ACCEPTED->label() }}</option>
                        <option value="active" {{ $currentStatus == 'active' ? 'selected' : '' }}>{{ \App\Enums\ApplicationStatus::ACTIVE->label() }}</option>
                        <option value="completed" {{ $currentStatus == 'completed' ? 'selected' : '' }} {{ !$canComplete && $currentStatus != 'completed' ? 'disabled' : '' }}>{{ \App\Enums\ApplicationStatus::COMPLETED->label() }}</option>
                        <option value="rejected" {{ $currentStatus == 'rejected' ? 'selected' : '' }}>{{ \App\Enums\ApplicationStatus::REJECTED->label() }}</option>
                        <option value="resigned" {{ $currentStatus == 'resigned' ? 'selected' : '' }}>{{ \App\Enums\ApplicationStatus::RESIGNED->label() }}</option>
                    </select>

                    @php
                        $isAlreadyRejected = ($currentStatusVal === 'rejected');
                        $isAcceptDisabled = !in_array($currentStatusVal, ['pending', 'verified', 'accepted'], true);
                        $isRejectDisabled = !in_array($currentStatusVal, ['pending', 'verified', 'rejected'], true);
                    @endphp

                    @if($isAlreadyRejected)
                        <div class="mb-5 p-4 rounded-2xl bg-rose-50 border border-rose-200 flex items-start gap-3">
                            <svg class="w-5 h-5 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <div>
                                <h4 class="text-xs font-bold text-rose-900 uppercase tracking-wider">Status Pengajuan Final (Ditolak)</h4>
                                <p class="text-xs text-rose-800 mt-0.5 leading-relaxed">
                                    Pengajuan magang ini telah ditolak dengan catatan: <em>"{{ $application->rejection_note }}"</em>. Status seleksi tidak dapat diubah kembali.
                                </p>
                            </div>
                        </div>
                    @endif

                    <!-- Interactive Status Selection Cards (7 Status Pipeline Baku) -->
                    <div class="mb-6">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2.5">
                            Pilih Status Keputusan Pengajuan (Pipeline Terpadu)
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-7 gap-2.5">
                            
                            <!-- 1. PENDING -->
                            <button type="button" 
                                    @if(!$isAlreadyRejected) @click="status = 'pending'" @endif
                                    {{ $isAlreadyRejected ? 'disabled title="Pengajuan telah ditolak"' : '' }}
                                    :class="status === 'pending' ? 'border-amber-400 bg-amber-50/70 ring-2 ring-amber-400/20 shadow-xs' : '{{ $isAlreadyRejected ? 'opacity-50 bg-slate-50 border-slate-200 cursor-not-allowed' : 'border-slate-200 bg-white hover:border-amber-200 hover:bg-amber-50/20 cursor-pointer' }}'"
                                    class="p-3 rounded-2xl border text-left transition-all duration-150 flex flex-col justify-between relative group">
                                <div class="flex items-center justify-between mb-1.5">
                                    <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
                                    <span :class="status === 'pending' ? 'opacity-100 text-amber-600' : 'opacity-0'" class="transition-opacity">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                    </span>
                                </div>
                                <div>
                                    <div class="font-extrabold text-xs text-slate-900 tracking-tight">{{ \App\Enums\ApplicationStatus::PENDING->label() }}</div>
                                    <div class="text-[11px] text-slate-500 mt-0.5 leading-snug">{{ \App\Enums\ApplicationStatus::PENDING->description() }}</div>
                                </div>
                            </button>

                            <!-- 2. VERIFIED -->
                            <button type="button" 
                                    @if(!$isAlreadyRejected) @click="status = 'verified'" @endif
                                    {{ $isAlreadyRejected ? 'disabled title="Pengajuan telah ditolak"' : '' }}
                                    :class="status === 'verified' ? 'border-sky-400 bg-sky-50/70 ring-2 ring-sky-400/20 shadow-xs' : '{{ $isAlreadyRejected ? 'opacity-50 bg-slate-50 border-slate-200 cursor-not-allowed' : 'border-slate-200 bg-white hover:border-sky-200 hover:bg-sky-50/20 cursor-pointer' }}'"
                                    class="p-3 rounded-2xl border text-left transition-all duration-150 flex flex-col justify-between relative group">
                                <div class="flex items-center justify-between mb-1.5">
                                    <span class="w-2.5 h-2.5 rounded-full bg-sky-400"></span>
                                    <span :class="status === 'verified' ? 'opacity-100 text-sky-600' : 'opacity-0'" class="transition-opacity">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                    </span>
                                </div>
                                <div>
                                    <div class="font-extrabold text-xs text-slate-900 tracking-tight">{{ \App\Enums\ApplicationStatus::VERIFIED->label() }}</div>
                                    <div class="text-[11px] text-slate-500 mt-0.5 leading-snug">{{ \App\Enums\ApplicationStatus::VERIFIED->description() }}</div>
                                </div>
                            </button>

                            <!-- 3. ACCEPTED -->
                            <button type="button" 
                                    @if(!$isAcceptDisabled) @click="status = 'accepted'" @endif
                                    {{ $isAcceptDisabled ? 'disabled title="Aksi persetujuan hanya dapat dilakukan pada pengajuan berstatus PENDING atau VERIFIED"' : '' }}
                                    :class="status === 'accepted' ? 'border-indigo-500 bg-indigo-50/70 ring-2 ring-indigo-500/20 shadow-xs' : '{{ $isAcceptDisabled ? 'opacity-50 bg-slate-50 border-slate-200 cursor-not-allowed' : 'border-slate-200 bg-white hover:border-indigo-200 hover:bg-indigo-50/20 cursor-pointer' }}'"
                                    class="p-3 rounded-2xl border text-left transition-all duration-150 flex flex-col justify-between relative group">
                                <div class="flex items-center justify-between mb-1.5">
                                    <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                                    <span :class="status === 'accepted' ? 'opacity-100 text-indigo-600' : 'opacity-0'" class="transition-opacity">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                    </span>
                                </div>
                                <div>
                                    <div class="font-extrabold text-xs text-slate-900 tracking-tight">{{ \App\Enums\ApplicationStatus::ACCEPTED->label() }}</div>
                                    <div class="text-[11px] text-slate-500 mt-0.5 leading-snug">
                                        {{ $isAcceptDisabled && $currentStatusVal !== 'accepted' ? 'Hanya dari PENDING atau VERIFIED' : \App\Enums\ApplicationStatus::ACCEPTED->description() }}
                                    </div>
                                </div>
                            </button>

                            <!-- 4. ACTIVE -->
                            <button type="button" 
                                    @if(!$isAlreadyRejected) @click="status = 'active'" @endif
                                    {{ $isAlreadyRejected ? 'disabled title="Pengajuan telah ditolak"' : '' }}
                                    :class="status === 'active' ? 'border-emerald-500 bg-emerald-50/70 ring-2 ring-emerald-500/20 shadow-xs' : '{{ $isAlreadyRejected ? 'opacity-50 bg-slate-50 border-slate-200 cursor-not-allowed' : 'border-slate-200 bg-white hover:border-emerald-200 hover:bg-emerald-50/20 cursor-pointer' }}'"
                                    class="p-3 rounded-2xl border text-left transition-all duration-150 flex flex-col justify-between relative group">
                                <div class="flex items-center justify-between mb-1.5">
                                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                                    <span :class="status === 'active' ? 'opacity-100 text-emerald-600' : 'opacity-0'" class="transition-opacity">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                    </span>
                                </div>
                                <div>
                                    <div class="font-extrabold text-xs text-slate-900 tracking-tight">{{ \App\Enums\ApplicationStatus::ACTIVE->label() }}</div>
                                    <div class="text-[11px] text-slate-500 mt-0.5 leading-snug">{{ \App\Enums\ApplicationStatus::ACTIVE->description() }}</div>
                                </div>
                            </button>

                            <!-- 5. COMPLETED -->
                            @php
                                $canComplete = $application->can_complete;
                                $isCompleteDisabled = (!$canComplete && $currentStatusVal !== 'completed') || $isAlreadyRejected;

                                $completedSubtitle = \App\Enums\ApplicationStatus::COMPLETED->description();
                                if ($isAlreadyRejected) {
                                    $completedSubtitle = 'Pengajuan Ditolak';
                                } elseif ($isCompleteDisabled) {
                                    if (!$application->has_approved_report && !$application->has_complete_evaluation) {
                                        $completedSubtitle = 'Laporan & Nilai Belum Lengkap';
                                    } elseif (!$application->has_approved_report) {
                                        $completedSubtitle = 'Laporan Belum Disetujui';
                                    } elseif (!$application->has_complete_evaluation) {
                                        $completedSubtitle = 'Nilai Belum Lengkap';
                                    } else {
                                        $completedSubtitle = 'Syarat Belum Terpenuhi';
                                    }
                                }
                            @endphp
                            <button type="button" 
                                    @if(!$isCompleteDisabled)
                                        @click="status = 'completed'"
                                    @endif
                                    @if($isCompleteDisabled)
                                        disabled
                                        title="{{ $completedSubtitle }}"
                                    @endif
                                    :class="status === 'completed' ? 'border-blue-600 bg-blue-50/70 ring-2 ring-blue-600/20 shadow-xs' : '{{ $isCompleteDisabled ? 'opacity-50 bg-slate-50 border-slate-200 cursor-not-allowed' : 'border-slate-200 bg-white hover:border-blue-200 hover:bg-blue-50/20 cursor-pointer' }}'"
                                    class="p-3 rounded-2xl border text-left transition-all duration-150 flex flex-col justify-between relative group">
                                <div class="flex items-center justify-between mb-1.5">
                                    <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                                    <span :class="status === 'completed' ? 'opacity-100 text-blue-600' : 'opacity-0'" class="transition-opacity">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                    </span>
                                </div>
                                <div>
                                    <div class="font-extrabold text-xs text-slate-900 tracking-tight flex items-center gap-1">
                                        <span>{{ \App\Enums\ApplicationStatus::COMPLETED->label() }}</span>
                                        @if($isCompleteDisabled)
                                            <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                        @endif
                                    </div>
                                    <div class="text-[11px] {{ $isCompleteDisabled ? 'text-amber-600 font-semibold' : 'text-slate-500' }} mt-0.5 leading-snug">
                                        {{ $completedSubtitle }}
                                    </div>
                                </div>
                            </button>

                            <!-- 6. REJECTED -->
                            <button type="button" 
                                    @if(!$isRejectDisabled) @click="status = 'rejected'" @endif
                                    {{ $isRejectDisabled ? 'disabled title="Aksi penolakan hanya dapat dilakukan pada pengajuan berstatus PENDING atau VERIFIED"' : '' }}
                                    :class="status === 'rejected' ? 'border-rose-500 bg-rose-50/70 ring-2 ring-rose-500/20 shadow-xs' : '{{ $isRejectDisabled ? 'opacity-50 bg-slate-50 border-slate-200 cursor-not-allowed' : 'border-slate-200 bg-white hover:border-rose-200 hover:bg-rose-50/20 cursor-pointer' }}'"
                                    class="p-3 rounded-2xl border text-left transition-all duration-150 flex flex-col justify-between relative group">
                                <div class="flex items-center justify-between mb-1.5">
                                    <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                                    <span :class="status === 'rejected' ? 'opacity-100 text-rose-600' : 'opacity-0'" class="transition-opacity">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                    </span>
                                </div>
                                <div>
                                    <div class="font-extrabold text-xs text-slate-900 tracking-tight">{{ \App\Enums\ApplicationStatus::REJECTED->label() }}</div>
                                    <div class="text-[11px] text-slate-500 mt-0.5 leading-snug">
                                        {{ $isRejectDisabled && $currentStatusVal !== 'rejected' ? 'Hanya dari PENDING atau VERIFIED' : \App\Enums\ApplicationStatus::REJECTED->description() }}
                                    </div>
                                </div>
                            </button>

                            <!-- 7. RESIGNED -->
                            <button type="button" 
                                    @if(!$isAlreadyRejected) @click="status = 'resigned'" @endif
                                    {{ $isAlreadyRejected ? 'disabled title="Pengajuan telah ditolak"' : '' }}
                                    :class="status === 'resigned' ? 'border-slate-600 bg-slate-100 ring-2 ring-slate-600/20 shadow-xs' : '{{ $isAlreadyRejected ? 'opacity-50 bg-slate-50 border-slate-200 cursor-not-allowed' : 'border-slate-200 bg-white hover:border-slate-300 hover:bg-slate-50 cursor-pointer' }}'"
                                    class="p-3 rounded-2xl border text-left transition-all duration-150 flex flex-col justify-between relative group">
                                <div class="flex items-center justify-between mb-1.5">
                                    <span class="w-2.5 h-2.5 rounded-full bg-slate-500"></span>
                                    <span :class="status === 'resigned' ? 'opacity-100 text-slate-700' : 'opacity-0'" class="transition-opacity">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                    </span>
                                </div>
                                <div>
                                    <div class="font-extrabold text-xs text-slate-900 tracking-tight">{{ \App\Enums\ApplicationStatus::RESIGNED->label() }}</div>
                                    <div class="text-[11px] text-slate-500 mt-0.5 leading-snug">{{ \App\Enums\ApplicationStatus::RESIGNED->description() }}</div>
                                </div>
                            </button>

                        </div>
                    </div>

                    <!-- Container Khusus Jika Status = ACCEPTED / ACTIVE / COMPLETED -->
                    <div id="acceptance-box"
                         x-show="status === 'accepted' || status === 'active' || status === 'completed'" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="space-y-4 mb-5 p-5 rounded-2xl bg-emerald-50/50 border border-emerald-200/80 shadow-2xs">
                        <div class="flex items-center gap-2 border-b border-emerald-200/60 pb-3">
                            <svg class="w-4 h-4 text-emerald-700 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            <h4 class="font-bold text-emerald-900 text-xs sm:text-sm">Data Balasan Penerimaan & Penempatan Magang</h4>
                        </div>
                        
                        <!-- Surat Pengantar dari Kampus untuk Pengecekan Cepat Admin -->
                        @php
                            $suratPengantar = $application->documents->first(function($doc) {
                                return stripos($doc->document_type, 'pengantar') !== false || stripos($doc->document_type, 'proposal') !== false;
                            }) ?? $application->documents->first();
                        @endphp
                        @if ($suratPengantar)
                            <div class="p-3.5 bg-white rounded-xl border border-emerald-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-2xs">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-xs font-bold text-emerald-950 truncate">Surat Pengantar Kampus: {{ $suratPengantar->document_type }}</div>
                                        <div class="text-[11px] text-slate-500">Tinjau permohonan resmi dari perguruan tinggi sebelum menetapkan balasan</div>
                                    </div>
                                </div>
                                <a href="{{ route('documents.application', $suratPengantar->id) }}" target="_blank"
                                   class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold shadow-2xs transition active:scale-95 shrink-0 cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    <span>Lihat Surat Pengantar</span>
                                </a>
                            </div>
                        @endif

                        @php
                            $selectedMentorId = old('mentor_id', optional($application->placement)->mentor_id ?? optional($application->placement)->pembimbing_id ?? '');
                            $selectedDosenId = old('academic_advisor_id', optional($application->placement)->academic_advisor_id ?? '');

                            $mentorItems = $pembimbings->map(function ($m) {
                                return [
                                    'id' => $m->id,
                                    'name' => $m->name,
                                    'acronym' => 'Mentor',
                                    'meta' => $m->email . ($m->phone ? ' • ' . $m->phone : ''),
                                ];
                            })->values()->all();

                            $dosenItems = $dosens->map(function ($d) {
                                $univName = $d->universityRelation?->name ?? (is_string($d->university) ? $d->university : '');
                                return [
                                    'id' => $d->id,
                                    'name' => $d->name,
                                    'acronym' => 'Dosen',
                                    'meta' => $d->email . ($univName ? ' • ' . $univName : ''),
                                ];
                            })->values()->all();
                        @endphp

                        <!-- Dropdown Pembimbing Lapangan (Searchable Combobox) -->
                        <div>
                            <x-searchable-select
                                name="mentor_id"
                                :items="$mentorItems"
                                :selected="$selectedMentorId"
                                label="Plotting Pembimbing Lapangan (Mentor Dinas Terkait)"
                                placeholder="-- Cari atau Pilih Mentor Lapangan (Ketik nama / email) --"
                            />
                            <p class="text-[11px] text-slate-500 mt-1 font-medium">Hanya menampilkan akun mentor resmi yang terdaftar di {{ $application->unit->agencyProfile->agency_name ?? 'instansi ini' }}.</p>
                        </div>

                        <!-- Dropdown Dosen Pembimbing Lapangan (Searchable Combobox) -->
                        <div>
                            <x-searchable-select
                                name="academic_advisor_id"
                                :items="$dosenItems"
                                :selected="$selectedDosenId"
                                label="Plotting Dosen Pembimbing Lapangan (DPL Kampus)"
                                placeholder="-- Cari atau Pilih Dosen Pembimbing (Ketik nama / email / kampus) --"
                            />
                            <p class="text-[11px] text-slate-500 mt-1 font-medium">Dosen DPL resmi dari perguruan tinggi mahasiswa yang bersangkutan.</p>
                        </div>

                        <!-- Grid Nomor Surat & Tanggal Surat -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-1">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nomor Surat Balasan Dinas</label>
                                <input type="text" name="letter_number" value="{{ old('letter_number', $application->letter_number) }}" 
                                    placeholder="Contoh: 500/123/APTIKA/2026"
                                    class="w-full text-xs border-slate-200 rounded-xl shadow-2xs focus:ring-emerald-500 focus:border-emerald-500 bg-white py-2.5">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Tanggal Surat Balasan</label>
                                <input type="date" name="letter_date" value="{{ old('letter_date', $application->letter_date ? \Carbon\Carbon::parse($application->letter_date)->format('Y-m-d') : date('Y-m-d')) }}" 
                                    class="w-full text-xs border-slate-200 rounded-xl shadow-2xs focus:ring-emerald-500 focus:border-emerald-500 bg-white py-2.5">
                            </div>
                        </div>
                    </div>

                    <!-- Field Alasan Penolakan (Tampil KHUSUS kalau REJECTED) -->
                    <div id="rejection-box"
                         x-show="status === 'rejected'"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="mb-5 p-4 rounded-2xl bg-rose-50/70 border border-rose-200 shadow-2xs">
                        <label class="block text-xs font-bold text-rose-800 uppercase tracking-wider mb-1.5 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <span>Alasan Penolakan Pengajuan <span class="text-rose-600 text-xs font-bold">* (Wajib Diisi)</span></span>
                        </label>
                        <textarea name="rejection_note" rows="3" 
                            :required="status === 'rejected'"
                            placeholder="Tuliskan alasan pengajuan ditolak secara jelas (minimal 5 karakter) agar dapat dipahami oleh pihak mahasiswa..."
                            class="w-full text-xs border-rose-200 rounded-xl focus:ring-rose-500 focus:border-rose-500 bg-white">{{ old('rejection_note', $application->rejection_note) }}</textarea>
                        @error('rejection_note')
                            <p class="text-xs text-rose-600 font-semibold mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Action Buttons -->
                    <div class="mt-6 pt-5 border-t border-slate-100 flex flex-wrap items-center gap-3">
                        @if(!$isAlreadyRejected)
                            <button type="submit" 
                                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-md transition active:scale-95 cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                <span>Simpan Perubahan Status</span>
                            </button>
                        @else
                            <button type="button" disabled
                                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-slate-200 text-slate-500 text-xs font-bold rounded-xl shadow-none cursor-not-allowed">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                <span>Status Final (Tidak Dapat Diubah)</span>
                            </button>
                        @endif

                        @if (in_array($currentStatus, ['accepted', 'active', 'completed']))
                            <a href="{{ route('admin.applications.letter', $application->id) }}" target="_blank" 
                                class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition active:scale-95 cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                <span>Cetak Surat Balasan</span>
                            </a>
                        @endif

                        @if ($application->placement && ($currentStatus === 'completed' || $application->placement->evaluation))
                            <a href="{{ route('admin.certificates.show', $application->placement->id) }}" target="_blank" 
                                class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-xs transition active:scale-95 cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                                <span>E-Sertifikat & Transkrip</span>
                            </a>
                        @endif

                        <a href="{{ route('admin.applications.index') }}" 
                           class="inline-flex items-center px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition cursor-pointer">
                            Kembali
                        </a>
                    </div>
                </form>
            </div>

            {{-- 6. Penugasan Pembimbing Susulan (penempatan sudah berjalan: ACCEPTED / ACTIVE) --}}
            @if ($application->placement && in_array($currentStatus, \App\Services\PlacementAssignmentService::ASSIGNABLE_STATUSES, true))
                @php
                    $asgPlacement = $application->placement;
                    $asgMentor = $asgPlacement->mentor ?? $asgPlacement->pembimbing;
                    $asgAdvisor = $asgPlacement->academicAdvisor;
                @endphp
                <div id="penugasan-pembimbing" class="bg-white p-6 sm:p-7 rounded-2xl border border-slate-200/80 shadow-xs">
                    <div class="pb-4 border-b border-slate-100 mb-5">
                        <h3 class="text-base font-bold text-slate-900">Penugasan Pembimbing (Susulan / Ganti)</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Tetapkan atau ganti Mentor Dinas &amp; DPL tanpa mengulang verifikasi. Histori logbook yang sudah divalidasi tetap tersimpan.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-5 text-xs">
                        <div class="p-3 rounded-xl border {{ $asgMentor ? 'border-emerald-200 bg-emerald-50/50' : 'border-amber-200 bg-amber-50/60' }}">
                            <div class="text-slate-500 font-semibold">Mentor Dinas saat ini</div>
                            <div class="font-bold text-slate-900 mt-0.5">{{ $asgMentor?->name ?? 'Belum Ditugaskan' }}</div>
                            @unless ($asgMentor)
                                <div class="text-[11px] text-amber-700 mt-1">Sementara logbook divalidasi oleh Admin Dinas instansi.</div>
                            @endunless
                        </div>
                        <div class="p-3 rounded-xl border {{ $asgAdvisor ? 'border-emerald-200 bg-emerald-50/50' : 'border-amber-200 bg-amber-50/60' }}">
                            <div class="text-slate-500 font-semibold">DPL Kampus saat ini</div>
                            <div class="font-bold text-slate-900 mt-0.5">{{ $asgAdvisor?->name ?? 'Belum Ditugaskan' }}</div>
                            @unless ($asgAdvisor)
                                <div class="text-[11px] text-amber-700 mt-1">Menunggu SK / penugasan dari kampus.</div>
                            @endunless
                        </div>
                    </div>

                    <form action="{{ route('admin.applications.assignment', $application->id) }}" method="POST" class="space-y-4">
                        @csrf
                        @method('PATCH')
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="asg_mentor_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Mentor Dinas</label>
                                <select id="asg_mentor_id" name="mentor_id" class="w-full text-xs border-slate-200 rounded-xl bg-white py-2.5">
                                    <option value="">-- Tidak diubah --</option>
                                    @foreach ($pembimbings as $pembimbing)
                                        <option value="{{ $pembimbing->id }}" @selected((int) ($asgMentor?->id ?? 0) === (int) $pembimbing->id)>{{ $pembimbing->name }}</option>
                                    @endforeach
                                </select>
                                @error('mentor_id') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="asg_advisor_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Dosen Pembimbing Lapangan (DPL)</label>
                                <select id="asg_advisor_id" name="academic_advisor_id" class="w-full text-xs border-slate-200 rounded-xl bg-white py-2.5">
                                    <option value="">-- Tidak diubah --</option>
                                    @foreach ($dosens as $dosen)
                                        <option value="{{ $dosen->id }}" @selected((int) ($asgAdvisor?->id ?? 0) === (int) $dosen->id)>{{ $dosen->name }}</option>
                                    @endforeach
                                </select>
                                @error('academic_advisor_id') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        @error('assignment') <p class="text-xs text-rose-600 font-semibold">{{ $message }}</p> @enderror
                        <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition cursor-pointer">
                            Simpan Penugasan Pembimbing
                        </button>
                    </form>
                </div>
            @endif

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const statusSelect = document.getElementById('status-select');
            const acceptanceBox = document.getElementById('acceptance-box');
            const rejectionBox = document.getElementById('rejection-box');

            function toggleFields() {
                if (!statusSelect || !acceptanceBox || !rejectionBox) return;
                const val = statusSelect.value;
                if (val === 'rejected') {
                    rejectionBox.style.display = '';
                    acceptanceBox.style.display = 'none';
                } else if (['accepted', 'active', 'completed'].includes(val)) {
                    acceptanceBox.style.display = '';
                    rejectionBox.style.display = 'none';
                } else {
                    rejectionBox.style.display = 'none';
                    acceptanceBox.style.display = 'none';
                }
            }

            window.toggleFields = toggleFields;

            if (statusSelect) {
                statusSelect.addEventListener('change', toggleFields);
            }
        });
    </script>
</x-app-layout>