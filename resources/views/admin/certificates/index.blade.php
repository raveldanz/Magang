<x-app-layout>
    <x-mobile-list-style />
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-black text-xl sm:text-2xl text-gray-900 tracking-tight flex items-center gap-2">
                    <span>Manajemen Sertifikat Kelulusan {{ auth()->user()->unit_id && !auth()->user()->isSuperAdmin() ? '• ' . (auth()->user()->unit->name ?? 'Bidang') : '' }}</span>
                </h2>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">
                    Supervisi, penerbitan sertifikat resmi, dan pengaturan desain tema tahunan terintegrasi
                </p>
            </div>
            
            <!-- Quick Badge Tema Aktif -->
            <div class="flex items-center gap-2">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-cyan-50 border border-cyan-200 text-cyan-900 text-xs font-bold shadow-2xs">
                    <span class="w-2.5 h-2.5 rounded-full" style="background-color: {{ $activeTheme->primary_color ?? '#00A3C4' }};"></span>
                    <span>Tema Aktif {{ $activeTheme->year ?? date('Y') }}:</span>
                    <strong class="text-cyan-950">{{ $activeTheme->theme_name ?? 'Dispusip' }}</strong>
                </div>
            </div>
        </div>
    </x-slot>

    @php
        $currentTab = request('tab', 'students');
    @endphp

    <div class="py-8" x-data="{ activeTab: '{{ $currentTab }}' }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if (session('success'))
                <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 rounded-xl shadow-xs text-sm font-bold flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div class="p-4 bg-rose-50 border-l-4 border-rose-500 text-rose-800 rounded-xl shadow-xs text-sm">
                    <p class="font-bold flex items-center gap-2">
                        <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>Gagal Menyimpan:</span>
                    </p>
                    <ul class="mt-1 list-disc list-inside text-xs space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- NAVIGASI TAB UTAMA (TERKUNCI DI ATAS) -->
            <div class="flex items-center gap-3 border-b border-gray-200 pb-3">
                <button type="button" 
                        @click="activeTab = 'students'; const u = new URL(window.location); u.searchParams.delete('tab'); window.history.replaceState({}, '', u);" 
                        :class="activeTab === 'students' ? 'bg-blue-600 text-white shadow-sm' : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-200'"
                        class="px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition flex items-center gap-2 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Daftar Mahasiswa & Cetak</span>
                    <span class="ml-1.5 px-2 py-0.5 text-[10px] rounded-full" :class="activeTab === 'students' ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-600'">
                        {{ $applications->total() ?? 0 }}
                    </span>
                </button>

                <button type="button" 
                        @click="activeTab = 'settings'; const u = new URL(window.location); u.searchParams.set('tab', 'settings'); window.history.replaceState({}, '', u);" 
                        :class="activeTab === 'settings' ? 'bg-blue-600 text-white shadow-sm' : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-200'"
                        class="px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition flex items-center gap-2 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>Desain & TTE Tahunan</span>
                    <span class="inline-flex w-2 h-2 rounded-full bg-emerald-400"></span>
                </button>
            </div>

            <!-- ================================================================= -->
            <!-- TAB 1: DAFTAR MAHASISWA & PENERBITAN SERTIFIKAT                   -->
            <!-- ================================================================= -->
            <div x-show="activeTab === 'students'" x-cloak class="space-y-4">
                
                <!-- Filter & Pencarian Cepat Mahasiswa -->
                <div class="bg-white rounded-2xl border border-gray-100 p-4 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-3">
                    <form method="GET" action="{{ route('admin.certificates.index') }}" class="w-full sm:w-auto flex-1 flex items-center gap-2">
                        <input type="hidden" name="tab" value="students">
                        <div class="relative flex-1 max-w-md">
                            <input type="text" name="search" value="{{ request('search') }}" 
                                   placeholder="Cari nama mahasiswa, NIM, atau universitas..."
                                   class="w-full text-xs rounded-xl border-gray-200 pl-9 pr-4 py-2 focus:border-blue-600 focus:ring-blue-600">
                            <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer">
                            Cari
                        </button>
                        @if(request('search'))
                            <a href="{{ route('admin.certificates.index') }}" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-xs font-bold transition">
                                Reset
                            </a>
                        @endif
                    </form>

                    <div class="text-xs text-gray-500 font-medium">
                        Menampilkan <strong>{{ $applications->count() }}</strong> dari total <strong>{{ $applications->total() }}</strong> mahasiswa siap cetak
                    </div>
                </div>

                <!-- Kontainer Tabel Mahasiswa -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
                    <div class="p-6 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <h3 class="text-base font-bold text-gray-900">Mahasiswa Memenuhi Kriteria Kelulusan</h3>
                            <p class="text-xs text-gray-500 mt-0.5">Seluruh laporan akhir telah disetujui dan nilai evaluasi lengkap</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.certificates.batchPrint') }}" target="_blank" 
                               class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer active:scale-95"
                               title="Buka seluruh sertifikat yang telah disahkan (ACC) untuk dicetak serentak dalam 1 berkas">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                <span>Cetak Semua Ter-ACC</span>
                            </a>
                        </div>
                    </div>

                    {{-- Tampilan HP: kartu --}}
                    <div class="m-only mlist">
                        @forelse ($applications as $app)
                            @php
                                $mEval = $app->placement?->evaluation;
                                $mScore = $mEval ? (float) $mEval->nilai_akhir : 0;
                                $mGrade = $mEval ? $mEval->grade_calculated : '-';
                                $mInitials = collect(explode(' ', trim(preg_replace('/\s*\([^)]*\)/', '', $app->user->name))))->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');
                                $mCert = $app->placement?->certificate;
                            @endphp
                            <div class="mcard">
                                <div class="mcard-head">
                                    <div class="mavatar">{{ $mInitials ?: 'M' }}</div>
                                    <div class="mcard-main">
                                        <div class="mcard-title">{{ $app->user->name }}</div>
                                        <div class="mcard-sub">
                                            <span class="mono">{{ $app->user->studentProfile->nim ?? '-' }}</span> · {{ $app->user->studentProfile->universitas ?? '-' }}
                                        </div>
                                    </div>
                                    <div class="mscore">
                                        <b>{{ $mScore > 0 ? rtrim(rtrim(number_format($mScore, 2, '.', ''), '0'), '.') : '-' }}</b>
                                        <span>{{ $mGrade !== '-' ? 'Grade ' . $mGrade : 'Nilai' }}</span>
                                    </div>
                                </div>
                                <div class="mcard-body space-y-2">
                                    <div>
                                        <div class="mfield-label">Unit Tempat Magang</div>
                                        <div class="mfield-value">{{ $app->unit->name ?? '-' }}</div>
                                    </div>
                                    <div>
                                        <div class="mfield-label">Status ACC Atasan (Supervisor)</div>
                                        <div class="mfield-value mt-1">
                                            @if(!$mCert)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600">
                                                    Belum Diterbitkan
                                                </span>
                                            @elseif($mCert->approval_status === 'approved')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                    Disahkan (ACC)
                                                </span>
                                            @elseif($mCert->approval_status === 'revision')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                                    Perlu Revisi
                                                </span>
                                                @if($mCert->notes)
                                                    <div class="text-[11px] text-rose-600 mt-1 leading-snug font-medium">
                                                        "{{ $mCert->notes }}"
                                                    </div>
                                                @endif
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200">
                                                    Menunggu ACC Atasan
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="mcard-foot">
                                    @if(!$mCert || $mCert->approval_status === 'waiting_approval')
                                        <a href="{{ route('admin.certificates.show', $app->placement->id) }}" target="_blank" class="mbtn mbtn-block bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold border border-slate-300">
                                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            Pratinjau Draft
                                        </a>
                                    @elseif($mCert->approval_status === 'revision')
                                        <a href="{{ route('admin.certificates.revision', $app->placement->id) }}" class="mbtn mbtn-block bg-amber-600 hover:bg-amber-700 text-white font-black shadow-xs">
                                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            Perbaiki Sertifikat
                                        </a>
                                    @else
                                        <a href="{{ route('admin.certificates.generate', $app->placement->id) }}" target="_blank" class="mbtn mbtn-primary mbtn-block">
                                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                            Cetak E-Sertifikat
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="mempty">
                                <p style="font-weight:700;color:#475569">Belum ada mahasiswa yang memenuhi syarat kelulusan.</p>
                                <p style="margin-top:4px">Pastikan mahasiswa telah mengumpulkan laporan dan dinilai oleh pembimbing.</p>
                            </div>
                        @endforelse
                    </div>

                    {{-- Tampilan desktop: tabel --}}
                    <div class="d-only overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="border-b border-gray-100 bg-gray-50/75 text-gray-500 font-bold uppercase tracking-wider text-[11px]">
                                    <th class="py-3.5 px-4">Nama Mahasiswa</th>
                                    <th class="py-3.5 px-4">Universitas / NIM</th>
                                    <th class="py-3.5 px-4">Unit Tempat Magang</th>
                                    <th class="py-3.5 px-4 text-center">Nilai Akhir</th>
                                    <th class="py-3.5 px-4 text-center">Status ACC Atasan</th>
                                    <th class="py-3.5 px-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($applications as $app)
                                    @php
                                        $eval = $app->placement?->evaluation;
                                        $rataRata = $eval ? (float) $eval->nilai_akhir : 0;
                                        $cert = $app->placement?->certificate;
                                    @endphp
                                    <tr class="hover:bg-slate-50/80 transition">
                                        <td class="py-4 px-4 font-bold text-gray-900 text-xs sm:text-sm">
                                            {{ $app->user->name }}
                                        </td>
                                        <td class="py-4 px-4">
                                            <div class="text-gray-800 font-semibold">{{ $app->user->studentProfile->universitas ?? '-' }}</div>
                                            <div class="text-[11px] text-blue-600 font-mono">NIM: {{ $app->user->studentProfile->nim ?? '-' }}</div>
                                        </td>
                                        <td class="py-4 px-4 text-gray-700 font-medium">
                                            {{ $app->unit->name ?? '-' }}
                                        </td>
                                        <td class="py-4 px-4 text-center">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-black bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                {{ $rataRata > 0 ? $rataRata : '-' }}
                                            </span>
                                        </td>
                                        <td class="py-4 px-4 text-center">
                                            @if(!$cert)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600">
                                                    Belum Diterbitkan
                                                </span>
                                            @elseif($cert->approval_status === 'approved')
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                    Disahkan (ACC)
                                                </span>
                                            @elseif($cert->approval_status === 'revision')
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200" title="{{ $cert->notes }}">
                                                    Perlu Revisi
                                                </span>
                                                @if($cert->notes)
                                                    <div class="text-[11px] text-rose-600 mt-1 max-w-[180px] mx-auto truncate font-medium" title="{{ $cert->notes }}">
                                                        "{{ $cert->notes }}"
                                                    </div>
                                                @endif
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-purple-50 text-purple-700 border border-purple-200">
                                                    Menunggu ACC Atasan
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-4 px-4 text-right whitespace-nowrap">
                                            @if(!$cert || $cert->approval_status === 'waiting_approval')
                                                <a href="{{ route('admin.certificates.show', $app->placement->id) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold border border-slate-300 shadow-2xs transition active:scale-95 cursor-pointer" title="Pratinjau Draf Sertifikat">
                                                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                    <span>Pratinjau Draft</span>
                                                </a>
                                            @elseif($cert->approval_status === 'revision')
                                                <a href="{{ route('admin.certificates.revision', $app->placement->id) }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-black shadow-xs transition active:scale-95 cursor-pointer">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                    <span>Perbaiki Sertifikat</span>
                                                </a>
                                            @else
                                                <a href="{{ route('admin.certificates.generate', $app->placement->id) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-xs transition active:scale-95 cursor-pointer">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                                    <span>Cetak E-Sertifikat</span>
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-12 text-center text-gray-400">
                                            <p class="font-bold text-gray-600 text-sm">Belum ada mahasiswa yang memenuhi syarat kelulusan.</p>
                                            <p class="text-xs text-gray-400 mt-1">Pastikan mahasiswa telah mengumpulkan laporan dan dinilai oleh pembimbing.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination Links -->
                    @if ($applications->hasPages())
                        <div class="p-4 border-t border-gray-100 bg-gray-50/50">
                            {{ $applications->appends(request()->query())->links() }}
                        </div>
                    @endif

                </div>
            </div>

            <!-- ================================================================= -->
            <!-- TAB 2: PENGATURAN DESAIN & TTE RESMI TAHUNAN                      -->
            <!-- ================================================================= -->
            <div x-show="activeTab === 'settings'" x-cloak class="space-y-6">
                
                <form action="{{ route('admin.certificates.updateTheme') }}" method="POST" class="space-y-6">
                    @csrf
                    
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        
                        <!-- Kolom Kiri & Tengah: Form Konfigurasi Tema & Pejabat (2 Kolom) -->
                        <div class="lg:col-span-2 space-y-6">
                            
                            <!-- 1. KARTU IDENTITAS TEMA & WARNA TAHUNAN -->
                            <div class="bg-white rounded-3xl p-6 sm:p-7 border border-slate-100 shadow-sm space-y-5">
                                <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-xl bg-cyan-50 border border-cyan-200 flex items-center justify-center text-cyan-600 font-bold">
                                            🎨
                                        </div>
                                        <div>
                                            <h3 class="text-base font-bold text-slate-800">Tema Desain & Palet Warna Tahunan</h3>
                                            <p class="text-[11px] text-slate-500">Atur tahun angkatan dan warna ornamen sertifikat yang berganti tiap periode</p>
                                        </div>
                                    </div>

                                    <!-- Switch Tema Aktif -->
                                    <label class="inline-flex items-center gap-2 cursor-pointer text-xs font-bold text-slate-700">
                                        <input type="checkbox" name="is_active" value="1" {{ ($activeTheme->is_active ?? true) ? 'checked' : '' }} class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                        <span>Aktifkan Tema Ini</span>
                                    </label>
                                </div>

                                <!-- Preset Pilihan Cepat (cerah & kontras tinggi) -->
                                @php
                                    $colorPresets = [
                                        ['name' => 'Tema Dispusip Cerah', 'label' => 'Dispusip Cerah', 'desc' => 'Cyan & Jingga', 'c' => ['#00B4E6', '#FF7A1A', '#FFB800']],
                                        ['name' => 'Tema Biru Samudra', 'label' => 'Biru Samudra', 'desc' => 'Biru & Aqua', 'c' => ['#2F80ED', '#00C2D1', '#FFC53D']],
                                        ['name' => 'Tema Hijau Zamrud', 'label' => 'Hijau Zamrud', 'desc' => 'Hijau & Emas', 'c' => ['#10B981', '#FBBF24', '#F59E0B']],
                                        ['name' => 'Tema Segar Lime', 'label' => 'Segar Lime', 'desc' => 'Lime & Langit', 'c' => ['#84CC16', '#0EA5E9', '#FACC15']],
                                        ['name' => 'Tema Magenta Surya', 'label' => 'Magenta Surya', 'desc' => 'Pink & Jingga', 'c' => ['#EC4899', '#FF8A3D', '#FFC53D']],
                                        ['name' => 'Tema Ungu Senja', 'label' => 'Ungu Senja', 'desc' => 'Ungu & Rose', 'c' => ['#8B5CF6', '#F472B6', '#FBBF24']],
                                        ['name' => 'Tema Merah Berani', 'label' => 'Merah Berani', 'desc' => 'Merah & Kuning', 'c' => ['#F43F5E', '#FF8A00', '#FFCB2E']],
                                        ['name' => 'Tema Resmi Dispusip 2026', 'label' => 'Dispusip 2026', 'desc' => 'Versi resmi (klasik)', 'c' => ['#00A3C4', '#F36F21', '#D97706']],
                                    ];
                                @endphp
                                <div>
                                    <label class="text-[11px] font-bold text-slate-600 uppercase tracking-wider block mb-2">Preset Warna Siap Pakai:</label>
                                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                        @foreach ($colorPresets as $pr)
                                            <button type="button"
                                                    onclick="applyPreset(@js($pr['name']), @js($pr['c'][0]), @js($pr['c'][1]), @js($pr['c'][2]))"
                                                    class="px-3 py-2 rounded-xl border border-slate-200 hover:border-slate-400 hover:shadow-sm bg-white text-left transition cursor-pointer">
                                                <div class="flex h-3.5 rounded-full overflow-hidden mb-1.5 ring-1 ring-black/5">
                                                    @foreach ($pr['c'] as $hex)
                                                        <span class="flex-1" style="background-color: {{ $hex }};"></span>
                                                    @endforeach
                                                </div>
                                                <div class="text-[11px] font-bold text-slate-800 truncate">{{ $pr['label'] }}</div>
                                                <div class="text-[9.5px] text-slate-500 truncate">{{ $pr['desc'] }}</div>
                                            </button>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                                    <div>
                                        <label for="year" class="text-xs font-bold text-slate-700 uppercase">Tahun Angkatan / Sertifikat</label>
                                        <input type="number" id="year" name="year" min="2020" max="2099" 
                                               value="{{ old('year', $activeTheme->year ?? date('Y')) }}" 
                                               class="mt-1 block w-full rounded-xl border-slate-200 focus:border-blue-600 focus:ring-blue-600 text-xs sm:text-sm" required>
                                    </div>

                                    <div>
                                        <label for="theme_name" class="text-xs font-bold text-slate-700 uppercase">Nama Label Tema</label>
                                        <input type="text" id="theme_name" name="theme_name" 
                                               value="{{ old('theme_name', $activeTheme->theme_name ?? 'Tema Resmi Dispusip 2026 — Cyan & Orange') }}" 
                                               class="mt-1 block w-full rounded-xl border-slate-200 focus:border-blue-600 focus:ring-blue-600 text-xs sm:text-sm" required>
                                    </div>
                                </div>

                                <!-- Color Pickers (3 Palet Utama) -->
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
                                    <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200/70">
                                        <label for="primary_color" class="text-[11px] font-bold text-slate-700 block mb-1.5">Warna Utama (Sudut Kiri):</label>
                                        <div class="flex items-center gap-2">
                                            <input type="color" id="primary_color_picker" 
                                                   value="{{ old('primary_color', $activeTheme->primary_color ?? '#00A3C4') }}" 
                                                   oninput="document.getElementById('primary_color').value = this.value; updateLivePreview();" 
                                                   class="w-9 h-9 p-0.5 rounded-lg border border-slate-300 cursor-pointer">
                                            <input type="text" id="primary_color" name="primary_color" 
                                                   value="{{ old('primary_color', $activeTheme->primary_color ?? '#00A3C4') }}" 
                                                   oninput="document.getElementById('primary_color_picker').value = this.value; updateLivePreview();"
                                                   class="text-xs font-mono rounded-lg border-slate-200 flex-1 uppercase">
                                        </div>
                                    </div>

                                    <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200/70">
                                        <label for="secondary_color" class="text-[11px] font-bold text-slate-700 block mb-1.5">Warna Sekunder (Sudut Kanan):</label>
                                        <div class="flex items-center gap-2">
                                            <input type="color" id="secondary_color_picker" 
                                                   value="{{ old('secondary_color', $activeTheme->secondary_color ?? '#F36F21') }}" 
                                                   oninput="document.getElementById('secondary_color').value = this.value; updateLivePreview();" 
                                                   class="w-9 h-9 p-0.5 rounded-lg border border-slate-300 cursor-pointer">
                                            <input type="text" id="secondary_color" name="secondary_color" 
                                                   value="{{ old('secondary_color', $activeTheme->secondary_color ?? '#F36F21') }}" 
                                                   oninput="document.getElementById('secondary_color_picker').value = this.value; updateLivePreview();"
                                                   class="text-xs font-mono rounded-lg border-slate-200 flex-1 uppercase">
                                        </div>
                                    </div>

                                    <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200/70">
                                        <label for="accent_color" class="text-[11px] font-bold text-slate-700 block mb-1.5">Warna Aksen Garis Tepi:</label>
                                        <div class="flex items-center gap-2">
                                            <input type="color" id="accent_color_picker" 
                                                   value="{{ old('accent_color', $activeTheme->accent_color ?? '#D97706') }}" 
                                                   oninput="document.getElementById('accent_color').value = this.value; updateLivePreview();" 
                                                   class="w-9 h-9 p-0.5 rounded-lg border border-slate-300 cursor-pointer">
                                            <input type="text" id="accent_color" name="accent_color" 
                                                   value="{{ old('accent_color', $activeTheme->accent_color ?? '#D97706') }}" 
                                                   oninput="document.getElementById('accent_color_picker').value = this.value; updateLivePreview();"
                                                   class="text-xs font-mono rounded-lg border-slate-200 flex-1 uppercase">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 2. KARTU PEJABAT PENANDATANGAN TTE RESMI (STANDAR BSrE) -->
                            <div class="bg-white rounded-3xl p-6 sm:p-7 border border-slate-100 shadow-sm space-y-4">
                                <div class="border-b border-slate-100 pb-3 flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-xl bg-purple-50 border border-purple-200 flex items-center justify-center text-purple-600 font-bold">
                                        ✍️
                                    </div>
                                    <div>
                                        <h3 class="text-base font-bold text-slate-800">Pejabat Penandatangan TTE Resmi (BSrE/Dispusip)</h3>
                                        <p class="text-[11px] text-slate-500">Tercetak otomatis di dalam kotak tanda tangan elektronik ber-QR Code di Halaman 1</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label for="signee_name" class="text-xs font-bold text-slate-700 uppercase">Nama Pejabat (Lengkap Gelar)</label>
                                        <input type="text" id="signee_name" name="signee_name" 
                                               value="{{ old('signee_name', $activeTheme->signee_name ?? 'Dra. NOVELIJA, SH') }}" 
                                               oninput="updateLivePreview()"
                                               class="mt-1 block w-full rounded-xl border-slate-200 focus:border-blue-600 focus:ring-blue-600 text-xs sm:text-sm font-bold" required>
                                    </div>

                                    <div>
                                        <label for="signee_nip" class="text-xs font-bold text-slate-700 uppercase">NIP Pejabat</label>
                                        <input type="text" id="signee_nip" name="signee_nip" 
                                               value="{{ old('signee_nip', $activeTheme->signee_nip ?? '197111141992012001') }}" 
                                               oninput="updateLivePreview()"
                                               class="mt-1 block w-full rounded-xl border-slate-200 focus:border-blue-600 focus:ring-blue-600 text-xs sm:text-sm font-mono">
                                    </div>

                                    <div>
                                        <label for="signee_position" class="text-xs font-bold text-slate-700 uppercase">Jabatan Penandatangan</label>
                                        <input type="text" id="signee_position" name="signee_position" 
                                               value="{{ old('signee_position', $activeTheme->signee_position ?? 'Sekretaris') }}" 
                                               oninput="updateLivePreview()"
                                               class="mt-1 block w-full rounded-xl border-slate-200 focus:border-blue-600 focus:ring-blue-600 text-xs sm:text-sm" required>
                                    </div>

                                    <div>
                                        <label for="signee_rank" class="text-xs font-bold text-slate-700 uppercase">Pangkat / Golongan</label>
                                        <input type="text" id="signee_rank" name="signee_rank" 
                                               value="{{ old('signee_rank', $activeTheme->signee_rank ?? 'PEMBINA TK I') }}" 
                                               oninput="updateLivePreview()"
                                               class="mt-1 block w-full rounded-xl border-slate-200 focus:border-blue-600 focus:ring-blue-600 text-xs sm:text-sm">
                                    </div>

                                    <div class="sm:col-span-2">
                                        <label for="mandate_text" class="text-xs font-bold text-slate-700 uppercase">Kalimat Mandat / a.n. Kepala Dinas</label>
                                        <input type="text" id="mandate_text" name="mandate_text" 
                                               value="{{ old('mandate_text', $activeTheme->mandate_text ?? 'a.n. Kepala Dinas Perpustakaan dan Kearsipan Kota Surabaya') }}" 
                                               oninput="updateLivePreview()"
                                               class="mt-1 block w-full rounded-xl border-slate-200 focus:border-blue-600 focus:ring-blue-600 text-xs sm:text-sm">
                                    </div>

                                    <div>
                                        <label for="city" class="text-xs font-bold text-slate-700 uppercase">Kota Penerbitan</label>
                                        <input type="text" id="city" name="city" 
                                               value="{{ old('city', $activeTheme->city ?? 'Surabaya') }}" 
                                               oninput="updateLivePreview()"
                                               class="mt-1 block w-full rounded-xl border-slate-200 focus:border-blue-600 focus:ring-blue-600 text-xs sm:text-sm" required>
                                    </div>

                                    <div>
                                        <label for="number_format_pattern" class="text-xs font-bold text-slate-700 uppercase">Pola Format Nomor Surat Dinas</label>
                                        <input type="text" id="number_format_pattern" name="number_format_pattern" 
                                               value="{{ old('number_format_pattern', $activeTheme->number_format_pattern ?? '400.14.5.4/{NUMBER}/436.7.17/{YEAR}') }}" 
                                               class="mt-1 block w-full rounded-xl border-slate-200 focus:border-blue-600 focus:ring-blue-600 text-xs sm:text-sm font-mono" required>
                                        <p class="text-[10.5px] text-slate-500 mt-1">Gunakan tag <code>{NUMBER}</code> dan <code>{YEAR}</code></p>
                                    </div>
                                </div>
                            </div>

                            <!-- Tombol Simpan -->
                            <div class="flex items-center justify-end gap-3 pt-2">
                                <button type="submit" class="w-full sm:w-auto px-7 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs sm:text-sm font-bold shadow-md transition transform active:scale-95 cursor-pointer flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    <span>Simpan Pengaturan Tema & TTE</span>
                                </button>
                            </div>

                        </div>

                        <!-- Kolom Kanan: Live Interactive Preview & Riwayat Tema (1 Kolom) -->
                        <div class="space-y-6">
                            
                            <!-- LIVE MINI PREVIEW SERTIFIKAT HALAMAN 1 -->
                            <div class="bg-white rounded-3xl p-5 border border-slate-100 shadow-sm space-y-3 sticky top-24">
                                <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                                    <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                                        <span>👁️</span>
                                        <span>Pratinjau Langsung (Live)</span>
                                    </h4>
                                    <span class="text-[10px] px-2 py-0.5 rounded-md bg-blue-50 text-blue-700 font-bold">Halaman 1</span>
                                </div>

                                <!-- Box Visual Mini Sertifikat Presisi (Replika Halaman 1) -->
                                @php
                                    $pPal = method_exists($activeTheme, 'getPrimaryPalette') ? $activeTheme->getPrimaryPalette() : [
                                        'darkest' => '#00587a', 'darker' => '#007ca5', 'dark' => '#0097c9',
                                        'base' => $activeTheme->primary_color ?? '#00A8E8', 'medium' => '#40c4e4', 'light' => '#80daf0'
                                    ];
                                    $sPal = method_exists($activeTheme, 'getSecondaryPalette') ? $activeTheme->getSecondaryPalette() : [
                                        'darkest' => '#b84a00', 'darker' => '#d95700', 'dark' => '#f26200',
                                        'base' => $activeTheme->secondary_color ?? '#FF6700', 'medium' => '#ff8d38', 'light' => '#ffb37a'
                                    ];
                                @endphp
                                <div id="previewCard" class="relative w-full aspect-[297/210] bg-white border border-slate-300 rounded-xl overflow-hidden shadow-sm flex flex-col justify-between p-2 select-none text-[8px] leading-tight" style="font-family: 'Times New Roman', Times, serif;">
                                    
                                    <!-- Dynamic SVG Vector Artwork -->
                                    <svg viewBox="0 0 297 210" class="absolute inset-0 w-full h-full pointer-events-none z-0" xmlns="http://www.w3.org/2000/svg">
                                        
                                        <rect id="prevTopStripe" x="0" y="0" width="297" height="2.6" fill="{{ $pPal['darkest'] }}" />
                                        <rect id="prevBottomStripe" x="0" y="207.6" width="297" height="2.4" fill="{{ $activeTheme->accent_color ?? '#D97706' }}" />
                                        @include('certificates.partials.corner-ornaments', ['pPalette' => $pPal, 'sPalette' => $sPal, 'lite' => true])
                                    </svg>

                                    <!-- Mini Header Logos -->
                                    <div class="relative z-10 flex items-center justify-center pt-1">
                                        <img src="{{ asset('images/certificates/logos_official_strip.png') }}" class="h-4 w-auto object-contain mx-auto" alt="Logos">
                                    </div>

                                    <!-- Mini Body Text -->
                                    <div class="relative z-10 text-center my-auto space-y-0.5">
                                        <div class="font-bold text-[10.5px] tracking-[0.05em] uppercase text-black" style="font-family: 'Times New Roman', Times, serif;">SERTIFIKAT</div>
                                        <div class="font-bold text-[7px] tracking-[0.16em] uppercase text-black" style="font-family: 'Times New Roman', Times, serif;">MAGANG</div>
                                        <div class="text-[6.5px] font-bold text-black" style="font-family: 'Times New Roman', Times, serif;">Nomor : 400.14.5.4/4692/436.7.17/<span id="prevYearText">{{ $activeTheme->year ?? date('Y') }}</span></div>
                                        <div class="pt-1">
                                            <span class="inline-block border-b border-black pb-0.5 font-bold text-[10px] text-black">Achmad Dhafa Sholahudin F</span>
                                        </div>
                                        <div class="text-[6px] text-black">Mahasiswa Program Studi Manajemen Fakultas Ekonomi dan Bisnis</div>
                                        <div class="text-[6px] text-black">Universitas 17 Agustus 1945 Surabaya</div>
                                        <div class="text-[6px] text-black italic pt-0.5">10 Agustus {{ $activeTheme->year ?? date('Y') }} s.d. 10 Oktober {{ $activeTheme->year ?? date('Y') }}</div>
                                    </div>

                                    <!-- Mini TTE Box -->
                                    <div class="relative z-10 flex flex-col items-center pb-0.5">
                                        <div class="text-[5.5px] text-black" id="prevCityDateText">{{ $activeTheme->city ?? 'Surabaya' }}, 27 Oktober {{ $activeTheme->year ?? date('Y') }}</div>
                                        <div class="text-[5.5px] text-black" id="prevMandateText">{{ $activeTheme->mandate_text ?? 'a.n. Kepala Dinas Perpustakaan dan Kearsipan Kota Surabaya' }}</div>
                                        <div class="mt-0.5 flex items-center gap-1.5 p-1 bg-white border border-black text-left w-48 shadow-2xs">
                                            <div class="w-6 h-6 border border-slate-300 flex items-center justify-center shrink-0 bg-white">
                                                <div class="w-2.5 h-2.5 bg-blue-600 rounded-2xs"></div>
                                            </div>
                                            <div class="text-[5.5px] leading-tight text-black flex-1 font-sans">
                                                <div class="text-[4.5px] text-slate-500">Ditandatangani secara elektronik:</div>
                                                <div class="font-bold text-[6px] text-black" id="prevSigneeName">{{ $activeTheme->signee_name ?? 'Dra. NOVELIJA, SH' }}</div>
                                                <div class="text-[4.5px] text-slate-600" id="prevSigneePos">{{ $activeTheme->signee_position ?? 'Sekretaris' }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-[11px] text-slate-500 leading-relaxed text-center">
                                    Warna dan teks di atas berubah secara real-time saat Anda mengedit form di samping.
                                </div>
                            </div>

                            <!-- RIWAYAT TEMA TAHUNAN -->
                            <div class="bg-white rounded-3xl p-5 border border-slate-100 shadow-sm space-y-3">
                                <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-1.5 border-b border-slate-100 pb-2">
                                    <span>📚</span>
                                    <span>Riwayat Tema Tahunan</span>
                                </h4>

                                <div class="space-y-2 max-h-60 overflow-y-auto">
                                    @forelse($allThemes as $t)
                                        <div class="p-3 rounded-2xl border {{ $t->is_active ? 'border-blue-400 bg-blue-50/40' : 'border-slate-200 bg-white' }} flex items-center justify-between gap-2">
                                            <div class="min-w-0 flex-1">
                                                <div class="flex items-center gap-1.5 flex-wrap">
                                                    <span class="w-3 h-3 rounded-full shrink-0" style="background-color: {{ $t->primary_color }};"></span>
                                                    <span class="w-3 h-3 rounded-full shrink-0" style="background-color: {{ $t->secondary_color }};"></span>
                                                    <span class="font-bold text-xs text-slate-800">Tahun {{ $t->year }}</span>
                                                    @if($t->is_active)
                                                        <span class="px-1.5 py-0.5 rounded text-[9px] font-black bg-blue-600 text-white">AKTIF</span>
                                                    @endif
                                                </div>
                                                <div class="text-[10.5px] text-slate-500 mt-0.5 truncate">{{ $t->theme_name }}</div>
                                            </div>

                                            <div class="flex items-center gap-1.5 shrink-0">
                                                <button type="button" 
                                                        onclick="loadThemeData({{ json_encode($t) }})"
                                                        class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 text-[10.5px] font-bold rounded-lg transition cursor-pointer"
                                                        title="Muat data tema tahun {{ $t->year }} ke form editor">
                                                    Edit
                                                </button>
                                                @if(!$t->is_active)
                                                    <form action="{{ route('admin.certificates.activateTheme', $t->id) }}" method="POST" class="m-0">
                                                        @csrf
                                                        <button type="submit" class="px-2.5 py-1 bg-blue-50 hover:bg-blue-600 text-blue-700 hover:text-white border border-blue-200 text-[10.5px] font-bold rounded-lg transition cursor-pointer">
                                                            Aktifkan
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                    @empty
                                        <p class="text-xs text-slate-400 text-center py-2">Belum ada riwayat tema lain.</p>
                                    @endforelse
                                </div>
                            </div>

                        </div>

                    </div>
                </form>

            </div>

        </div>
    </div>

    @push('scripts')
    <script>
        function loadThemeData(theme) {
            if (!theme) return;
            if (theme.year) document.getElementById('year').value = theme.year;
            if (theme.theme_name) document.getElementById('theme_name').value = theme.theme_name;
            if (theme.primary_color) {
                document.getElementById('primary_color').value = theme.primary_color;
                document.getElementById('primary_color_picker').value = theme.primary_color;
            }
            if (theme.secondary_color) {
                document.getElementById('secondary_color').value = theme.secondary_color;
                document.getElementById('secondary_color_picker').value = theme.secondary_color;
            }
            if (theme.accent_color) {
                document.getElementById('accent_color').value = theme.accent_color;
                document.getElementById('accent_color_picker').value = theme.accent_color;
            }
            if (theme.number_format_pattern && document.getElementById('number_format_pattern')) {
                document.getElementById('number_format_pattern').value = theme.number_format_pattern;
            }
            if (theme.signee_name) document.getElementById('signee_name').value = theme.signee_name;
            if (theme.signee_position) document.getElementById('signee_position').value = theme.signee_position;
            if (theme.signee_rank && document.getElementById('signee_rank')) {
                document.getElementById('signee_rank').value = theme.signee_rank;
            }
            if (theme.signee_nip && document.getElementById('signee_nip')) {
                document.getElementById('signee_nip').value = theme.signee_nip;
            }
            if (theme.mandate_text && document.getElementById('mandate_text')) {
                document.getElementById('mandate_text').value = theme.mandate_text;
            }
            if (theme.city && document.getElementById('city')) {
                document.getElementById('city').value = theme.city;
            }
            updateLivePreview();
        }

        function applyPreset(name, primary, secondary, accent) {
            document.getElementById('theme_name').value = name;
            document.getElementById('primary_color').value = primary;
            document.getElementById('primary_color_picker').value = primary;
            document.getElementById('secondary_color').value = secondary;
            document.getElementById('secondary_color_picker').value = secondary;
            document.getElementById('accent_color').value = accent;
            document.getElementById('accent_color_picker').value = accent;
            updateLivePreview();
        }

        function adjustBrightnessJs(hex, percent) {
            hex = (hex || '#000000').replace('#', '');
            if (hex.length === 3) hex = hex[0] + hex[0] + hex[1] + hex[1] + hex[2] + hex[2];
            var r = parseInt(hex.substring(0, 2), 16) || 0;
            var g = parseInt(hex.substring(2, 4), 16) || 0;
            var b = parseInt(hex.substring(4, 6), 16) || 0;
            if (percent < 0) {
                var factor = 1 + (percent / 100);
                r = Math.max(0, Math.min(255, Math.round(r * factor)));
                g = Math.max(0, Math.min(255, Math.round(g * factor)));
                b = Math.max(0, Math.min(255, Math.round(b * factor)));
            } else {
                var factor = percent / 100;
                r = Math.max(0, Math.min(255, Math.round(r + (255 - r) * factor)));
                g = Math.max(0, Math.min(255, Math.round(g + (255 - g) * factor)));
                b = Math.max(0, Math.min(255, Math.round(b + (255 - b) * factor)));
            }
            return '#' + ((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1);
        }

        function updateLivePreview() {
            var primary = document.getElementById('primary_color').value || '#00A3C4';
            var secondary = document.getElementById('secondary_color').value || '#F36F21';
            var accent = document.getElementById('accent_color').value || '#D97706';
            var year = document.getElementById('year').value;
            var signeeName = document.getElementById('signee_name').value;
            var signeePos = document.getElementById('signee_position').value;
            var mandate = document.getElementById('mandate_text').value;
            var city = document.getElementById('city').value;

            // Palet dinamis — persentase sama persis dengan CertificateTheme::getPrimaryPalette()
            function buildPalette(hex) {
                return {
                    darkest: adjustBrightnessJs(hex, -22), darker: adjustBrightnessJs(hex, -13), dark: adjustBrightnessJs(hex, -6),
                    base: hex, medium: adjustBrightnessJs(hex, 20), light: adjustBrightnessJs(hex, 42), lighter: adjustBrightnessJs(hex, 64)
                };
            }
            var pal = { p: buildPalette(primary), s: buildPalette(secondary) };

            var topStripe = document.getElementById('prevTopStripe');
            if (topStripe) topStripe.setAttribute('fill', pal.p.darkest);
            var bottomStripe = document.getElementById('prevBottomStripe');
            if (bottomStripe) bottomStripe.setAttribute('fill', accent);

            // Mosaik sudut: warnai ulang tiap segitiga sesuai nuansa (data-pal / data-shade)
            document.querySelectorAll('#previewCard [data-shade]').forEach(function (el) {
                var c = pal[el.dataset.pal][el.dataset.shade];
                el.setAttribute('fill', c);
                el.setAttribute('stroke', c);
            });
            document.querySelectorAll('#previewCard [data-wave]').forEach(function (el) {
                el.setAttribute('stroke', pal[el.dataset.wave].medium);
            });

            // Update Text Data
            var yr = document.getElementById('prevYearText');
            if (yr) yr.textContent = year;

            var sn = document.getElementById('prevSigneeName');
            if (sn) sn.textContent = signeeName;

            var sp = document.getElementById('prevSigneePos');
            if (sp) sp.textContent = signeePos;

            var md = document.getElementById('prevMandateText');
            if (md) md.textContent = mandate;

            var cd = document.getElementById('prevCityDateText');
            if (cd) cd.textContent = (city || 'Surabaya') + ', 27 Oktober ' + year;
        }
    </script>
    @endpush
</x-app-layout>
