<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl sm:text-2xl text-slate-800 leading-tight">
            {{ __('Laporan Akhir Magang') }}
        </h2>
    </x-slot>

    <div class="py-5 sm:py-8 lg:py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Success / Error Messages -->
            @if (session('success'))
                <div class="p-3.5 sm:p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-xl sm:rounded-r-2xl shadow-xs flex items-center justify-between text-emerald-900 text-xs sm:text-sm font-medium">
                    <div class="flex items-center gap-2">
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="p-3.5 sm:p-4 bg-rose-50 border-l-4 border-rose-500 rounded-r-xl sm:rounded-r-2xl shadow-xs flex items-center justify-between text-rose-900 text-xs sm:text-sm font-medium">
                    <div class="flex items-center gap-2">
                        <span>{{ session('error') }}</span>
                    </div>
                </div>
            @endif

            @if ($errors->any())
                <div class="p-3.5 sm:p-4 bg-rose-50 border-l-4 border-rose-500 rounded-r-xl sm:rounded-r-2xl shadow-xs text-rose-900 text-xs space-y-1">
                    <p class="font-bold text-xs sm:text-sm">Terjadi kesalahan validasi berkas:</p>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- KONDISI 0: BELUM ADA PENGAJUAN MAGANG SAMA SEKALI --}}
            @if (!$application || $lifecycle === 'NONE' || $lifecycle === 'DRAFT')
                <div class="bg-amber-50 border-l-4 border-amber-400 p-6 rounded-2xl shadow-xs">
                    <div class="flex items-start justify-between flex-wrap gap-4">
                        <div class="flex items-center gap-3">
                            <div>
                                <h4 class="font-bold text-amber-900 text-sm">Belum Ada Pengajuan Magang Aktif</h4>
                                <p class="text-xs text-amber-700 mt-0.5">Anda belum mengajukan permohonan magang. Silakan lakukan pendaftaran magang terlebih dahulu untuk membuka fitur laporan akhir.</p>
                            </div>
                        </div>
                        <a href="{{ route('student.application.create') }}" class="px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-xl transition shadow-xs shrink-0">
                            Ajukan Magang Sekarang 
                        </a>
                    </div>
                </div>

            {{-- KONDISI 1: JIKA STATUS MASIH DALAM VERIFIKASI SELEKSI INSTANSI --}}
            @elseif (in_array(strtoupper($lifecycle ?? ''), ['PENDING', 'VERIFIED']) || !$placement)
                
                <!-- Status Banner Kuning / Amber -->
                <div class="bg-amber-50 border-l-4 border-amber-400 p-6 rounded-2xl shadow-xs">
                    <div class="flex items-start justify-between flex-wrap gap-4">
                        <div class="flex items-start gap-3">
                            <div>
                                <h4 class="font-bold text-amber-900 text-sm sm:text-base">Pengajuan Magang Sedang Diverifikasi</h4>
                                <p class="text-xs sm:text-sm text-amber-700 mt-0.5">
                                    Pengajuan magang Anda saat ini sedang dalam proses verifikasi dan seleksi oleh instansi <strong>{{ $application?->unit?->agencyProfile?->agency_name ?? 'Pemerintah Kota Surabaya' }}</strong>.
                                </p>
                            </div>
                        </div>
                        <span class="px-3 py-1 bg-amber-200 text-amber-900 text-xs font-black rounded-full uppercase tracking-wider">
                            Status: {{ $lifecycle }}
                        </span>
                    </div>
                </div>

                <!-- Stepper Alur Proses Magang Terpadu -->
                <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-100 shadow-sm space-y-6">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                        <div class="flex items-center gap-3">
                            <div>
                                <h3 class="font-black text-base text-gray-900">Alur Proses & Informasi Pengajuan Magang</h3>
                            </div>
                        </div>
                        <a href="{{ route('dashboard') }}" class="text-xs font-bold text-blue-600 hover:text-blue-800">
                            Buka Dashboard Utama 
                        </a>
                    </div>

                    <!-- 4 Steps Timeline Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full bg-emerald-600 text-white text-xs font-bold flex items-center justify-center">✓</span>
                                <h4 class="font-bold text-xs text-emerald-900">Berkas Dikirim</h4>
                            </div>
                            <p class="text-[11px] text-emerald-700">Formulir, CV, & Transkrip terkirim ke sistem.</p>
                        </div>

                        <div class="p-4 rounded-2xl bg-amber-50 border-2 border-amber-400 shadow-xs space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full bg-amber-500 text-white text-xs font-bold flex items-center justify-center animate-pulse">2</span>
                                <h4 class="font-bold text-xs text-amber-900">Verifikasi Dinas</h4>
                            </div>
                            <p class="text-[11px] text-amber-700">Pemeriksaan berkas & kualifikasi oleh admin dinas.</p>
                        </div>

                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 opacity-60 space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full bg-slate-300 text-slate-700 text-xs font-bold flex items-center justify-center">3</span>
                                <h4 class="font-bold text-xs text-slate-700">Penugasan Pembimbing</h4>
                            </div>
                            <p class="text-[11px] text-slate-500">Penetapan Mentor Dinas & Dosen Pembimbing Lapangan.</p>
                        </div>

                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 opacity-60 space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full bg-slate-300 text-slate-700 text-xs font-bold flex items-center justify-center">4</span>
                                <h4 class="font-bold text-xs text-slate-700">Laporan Akhir Magang</h4>
                            </div>
                            <p class="text-[11px] text-slate-500">Pengunggahan naskah laporan & penilaian akhir magang.</p>
                        </div>
                    </div>

                    <!-- Detail Pendaftaran Mahasiswa -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 text-xs pt-2">
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                            <span class="text-slate-400 uppercase font-semibold text-[10px] block mb-1">Instansi Penempatan</span>
                            <span class="font-bold text-slate-800 text-sm block">{{ $application?->unit?->agencyProfile?->agency_name ?? 'Instansi Dinas' }}</span>
                            <span class="text-slate-500 text-xs mt-0.5 block">{{ $application?->unit?->name ?? '-' }}</span>
                        </div>
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                            <span class="text-slate-400 uppercase font-semibold text-[10px] block mb-1">Universitas / Kampus</span>
                            <span class="font-bold text-slate-800 text-sm block">{{ Auth::user()?->studentProfile?->universitas ?? Auth::user()?->university ?? '-' }}</span>
                            <span class="text-slate-500 text-xs mt-0.5 block">{{ Auth::user()?->studentProfile?->jurusan ?? '-' }}</span>
                        </div>
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                            <span class="text-slate-400 uppercase font-semibold text-[10px] block mb-1">Periode Magang Diajukan</span>
                            <span class="font-bold text-slate-800 text-xs block">{{ $application?->start_date ? \Carbon\Carbon::parse($application->start_date)->translatedFormat('d M Y') : '-' }}</span>
                            <span class="text-slate-500 text-xs block">s/d {{ $application?->end_date ? \Carbon\Carbon::parse($application->end_date)->translatedFormat('d M Y') : '-' }}</span>
                        </div>
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                            <span class="text-slate-400 uppercase font-semibold text-[10px] block mb-1">Tanggal Diajukan</span>
                            <span class="font-bold text-slate-800 text-xs block">{{ $application?->created_at ? $application->created_at->translatedFormat('d F Y, H:i') : '-' }}</span>
                            <span class="text-amber-600 font-semibold text-[11px] block mt-1">Menunggu Keputusan</span>
                        </div>
                    </div>

                    <!-- Notice Information Box -->
                    <div class="p-4 rounded-2xl bg-blue-50/80 border border-blue-200 flex items-start gap-3">
                        <svg class="w-5 h-5 text-blue-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                        <div class="text-xs text-blue-900 space-y-1">
                            <p class="font-bold">Informasi Akses Laporan Akhir:</p>
                            <p class="text-blue-800 leading-relaxed">
                                Formulir pengunggahan naskah laporan ilmiah dan tautan luaran proyek magang akan otomatis aktif dan terbuka untuk diisi setelah pengajuan magang Anda <strong>diterima (disetujui)</strong> oleh pihak instansi kedinasan dan data DPL telah dilengkapi. Anda dapat mengecek pembaruan status secara berkala di portal ini.
                            </p>
                        </div>
                    </div>
                </div>

            {{-- KONDISI 2: JIKA SUDAH MEMILIKI PENEMPATAN AKTIF (BISA UNGGAH / LIHAT STATUS LAPORAN) --}}
            @else

                {{-- Card 1: Informasi Penempatan Magang (Identik dengan Logbook) --}}
                <div class="bg-blue-600 rounded-2xl p-6 text-white shadow-lg">
                    <div class="flex items-center justify-between mb-4 border-b border-blue-500 pb-3">
                        <h3 class="text-base font-bold flex items-center gap-2">
                            Informasi Penempatan Magang
                        </h3>
                        <span class="text-xs font-bold px-3 py-1 bg-white/20 text-white rounded-full">
                            Status: {{ $lifecycle }}
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 text-xs">
                        <div class="p-3 bg-white/10 rounded-xl">
                            <p class="text-blue-200 uppercase tracking-wider mb-0.5">Instansi & Unit Kerja</p>
                            <p class="font-bold text-sm text-white">{{ $application?->unit?->agencyProfile?->agency_name ?? '-' }}</p>
                            <p class="text-blue-200 font-medium">{{ $application?->unit?->name ?? '-' }}</p>
                        </div>
                        <div class="p-3 bg-white/10 rounded-xl">
                            <p class="text-blue-200 uppercase tracking-wider mb-0.5">Mentor Lapangan Dinas</p>
                            <p class="font-bold text-sm text-white">{{ $placement?->mentor?->name ?? $placement?->pembimbing?->name ?? 'Belum Ditentukan' }}</p>
                        </div>
                        <div class="p-3 bg-white/10 rounded-xl">
                            <p class="text-blue-200 uppercase tracking-wider mb-0.5">Dosen Pembimbing</p>
                            <p class="font-bold text-sm text-white">{{ $placement?->academicAdvisor?->name ?? 'Belum Ditentukan' }}</p>
                        </div>
                        <div class="p-3 bg-white/10 rounded-xl">
                            <p class="text-blue-200 uppercase tracking-wider mb-0.5">Periode Magang</p>
                            <p class="font-bold text-sm text-white">{{ $application?->start_date ? \Carbon\Carbon::parse($application->start_date)->format('d M Y') : '-' }} s/d {{ $application?->end_date ? \Carbon\Carbon::parse($application->end_date)->format('d M Y') : '-' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Informational Banner for Early & Parallel Upload -->
                <div class="p-3.5 sm:p-4 bg-blue-50/60 border border-blue-200/80 rounded-2xl flex items-start gap-3 text-blue-900 text-xs sm:text-sm font-medium leading-relaxed shadow-2xs">
                    <div class="w-7 h-7 rounded-xl bg-blue-600 text-white flex items-center justify-center shrink-0 mt-0.5 shadow-2xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="space-y-0.5">
                        <span class="font-bold block text-blue-950 text-xs sm:text-sm">Informasi Pengunggahan Laporan Akhir</span>
                        <p class="text-[11px] sm:text-xs text-blue-900">Anda dapat mengunggah atau memperbarui draf naskah laporan ilmiah & repositori proyek magang Anda <strong>kapan saja</strong> selama periode magang berlangsung. Pastikan juga untuk tetap mengisi logbook kegiatan harian secara teratur.</p>
                    </div>
                </div>

                <!-- 1. STATUS LAPORAN SAAT INI -->
                @if ($finalReport)
                    <div class="bg-white rounded-2xl sm:rounded-3xl p-4 sm:p-6 lg:p-8 border border-slate-200/80 shadow-xs space-y-4">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-4 border-b border-slate-100">
                            <div class="min-w-0">
                                <span class="text-[10px] sm:text-xs font-bold text-slate-400 uppercase tracking-wider block">Status Verifikasi Naskah</span>
                                <h3 class="font-black text-base sm:text-lg text-slate-900 mt-0.5 break-words">
                                    <span>{{ $finalReport->title ?? 'Naskah Laporan Akhir Magang' }}</span>
                                </h3>
                                @if($finalReport->repository_url)
                                    <a href="{{ $finalReport->repository_url }}" target="_blank" class="text-xs text-blue-600 hover:text-blue-800 font-semibold mt-1 inline-flex items-center gap-1.5 break-all">
                                        <svg class="w-3.5 h-3.5 text-blue-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                                        <span>Tautan Proyek: {{ $finalReport->repository_url }}</span>
                                    </a>
                                @endif
                            </div>

                            <div class="shrink-0">
                                <x-status-badge type="review" :status="$finalReport->status" stacked />
                            </div>
                        </div>

                        <!-- Detail & Unduh File -->
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3.5 sm:p-4 bg-slate-50 rounded-xl sm:rounded-2xl border border-slate-100">
                            <div class="text-xs text-slate-600">
                                Terakhir diunggah: <strong>{{ $finalReport->updated_at ? $finalReport->updated_at->format('d F Y, H:i') : '-' }}</strong>
                            </div>
                            <a href="{{ route('final_reports.show', $finalReport->id) }}" target="_blank" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <span>Buka / Unduh Berkas Laporan</span>
                            </a>
                        </div>

                        <!-- Feedback / Catatan Revisi jika ada -->
                        @if ($finalReport->feedback)
                            <div class="p-3.5 sm:p-4 rounded-xl sm:rounded-2xl {{ $finalReport->status === 'revision' ? 'bg-rose-50/80 border-rose-200 text-rose-900' : 'bg-emerald-50/80 border-emerald-200 text-emerald-900' }} border text-xs space-y-1">
                                <span class="font-bold uppercase tracking-wider text-[11px] flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                                    {{ $finalReport->status === 'revision' ? 'Catatan Perbaikan / Masukan Revisi:' : 'Catatan Pembimbing:' }}
                                </span>
                                <p class="whitespace-pre-line leading-relaxed italic">
                                    "{{ $finalReport->feedback }}"
                                </p>
                            </div>
                        @endif
                    </div>
                @endif

                <!-- 2. FORM UNGGAH / PERBAHARUI LAPORAN -->
                @if (!$finalReport || $finalReport->status !== 'approved')
                    <div class="bg-white rounded-2xl sm:rounded-3xl p-4 sm:p-6 lg:p-8 border border-slate-200/80 shadow-xs space-y-5 sm:space-y-6">
                        <div class="border-b border-slate-100 pb-3">
                            <h3 class="font-black text-sm sm:text-base text-slate-900">
                                {{ $finalReport ? 'Perbarui / Unggah Ulang Laporan Revisi' : 'Formulir Pengunggahan Laporan Akhir' }}
                            </h3>
                        </div>

                        <form action="{{ route('student.final_report.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4 sm:space-y-5">
                            @csrf

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                    Judul Naskah Laporan Akhir Magang
                                </label>
                                <input type="text" name="title" value="{{ old('title', $finalReport->title ?? 'Laporan Akhir Praktik Kerja Lapangan (PKL) / Magang MBKM') }}" placeholder="Contoh: Rancang Bangun Sistem Monitoring Layanan Publik..." class="w-full text-xs sm:text-sm border-slate-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs">
                            </div>

                            <div>
                                <div class="flex items-center justify-between mb-1.5 gap-2">
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider truncate">
                                        Tautan Repositori Proyek / Luaran Kerja (Opsional)
                                    </label>
                                    <span id="repoUrlBadge" class="hidden text-[10px] sm:text-[11px] font-bold text-blue-700 bg-blue-50 px-2 py-0.5 rounded-md border border-blue-200 shrink-0">
                                        ✓ Valid
                                    </span>
                                </div>
                                <div class="relative flex items-center">
                                    <input type="url" name="repository_url" id="repository_url" value="{{ old('repository_url', $finalReport->repository_url ?? '') }}" 
                                           oninput="handleRepoUrlChange(this)"
                                           placeholder="https://github.com/username/project atau link Google Drive" 
                                           class="w-full text-xs sm:text-sm border-slate-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs font-mono pr-24 sm:pr-28">
                                    <a id="repoTestBtn" href="#" target="_blank" class="hidden absolute right-1.5 px-2.5 sm:px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-lg transition items-center gap-1 shadow-xs">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                        <span>Uji</span>
                                    </a>
                                </div>
                            </div>

                            <!-- Himbauan Format Penamaan Berkas Responsif Mobile -->
                            <div class="p-3.5 sm:p-4 rounded-xl sm:rounded-2xl bg-slate-50 border border-slate-200/80 flex items-start gap-3 shadow-2xs">
                                <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-xl bg-blue-600 text-white flex items-center justify-center shrink-0 mt-0.5 shadow-xs">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div class="text-xs space-y-1.5 flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="font-bold text-slate-900 text-xs sm:text-sm">Himbauan Format Penamaan Berkas</span>
                                        <span class="px-2 py-0.5 rounded-md bg-slate-200 text-slate-700 text-[10px] font-bold uppercase tracking-wider shrink-0">Format Baku</span>
                                    </div>
                                    <p class="text-slate-600 leading-relaxed text-[11px] sm:text-xs">
                                        Agar memudahkan DPL dan Pembimbing Dinas dalam memeriksa berkas Anda, pastikan nama file telah berformat standar:
                                    </p>
                                    <div class="p-2 sm:p-2.5 bg-white border border-slate-200 rounded-xl space-y-1">
                                        <div class="flex items-center gap-1.5 font-mono text-[11px] sm:text-xs font-bold text-blue-700">
                                            <svg class="w-3.5 h-3.5 text-blue-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            <span class="truncate">Format: Laporan_Akhir_[NIM]_[Nama].[ext]</span>
                                        </div>
                                        <div class="text-[10px] sm:text-[11px] text-slate-500 break-all leading-normal">
                                            Contoh: <strong class="text-slate-800 font-mono">Laporan_Akhir_{{ preg_replace('/[^A-Za-z0-9]/', '', Auth::user()->studentProfile->nim ?? '22051204001') }}_{{ \Illuminate\Support\Str::slug(Auth::user()->name, '_') }}.pdf</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Interactive Drag & Drop Dropzone -->
                            <div>
                                <div class="flex items-center justify-between mb-1.5 gap-2">
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider truncate">
                                        Unggah File Naskah Laporan (PDF) 
                                        @if(!$finalReport || !$finalReport->file_path)
                                            <span class="text-rose-500">*</span>
                                        @endif
                                    </label>
                                    @if($finalReport && $finalReport->file_path)
                                        <span class="text-[10px] sm:text-[11px] text-blue-600 font-medium shrink-0">
                                            Berkas tersimpan tersedia
                                        </span>
                                    @endif
                                </div>

                                <div id="dropzoneArea" 
                                     onclick="document.getElementById('file_laporan').click()"
                                     ondragover="handleDragOver(event)" 
                                     ondragleave="handleDragLeave(event)" 
                                     ondrop="handleFileDrop(event)"
                                     class="relative border-2 border-dashed border-slate-300 hover:border-blue-600 rounded-2xl sm:rounded-3xl p-4 sm:p-6 lg:p-8 bg-slate-50/50 hover:bg-blue-50/40 transition cursor-pointer text-center group space-y-2 sm:space-y-3">
                                    <input type="file" name="file_laporan" id="file_laporan" accept=".pdf,application/pdf" 
                                           {{ $finalReport && $finalReport->file_path ? '' : 'required' }}
                                           onchange="handleFinalReportPreview(this)"
                                           class="hidden">
                                    
                                    <div class="w-10 h-10 sm:w-12 sm:h-12 mx-auto rounded-xl sm:rounded-2xl bg-blue-50 group-hover:bg-blue-600 text-blue-600 group-hover:text-white border border-blue-100 flex items-center justify-center transition shadow-2xs">
                                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                        </svg>
                                    </div>
                                    <div class="space-y-1">
                                        <p class="text-xs sm:text-sm font-bold text-slate-800">
                                            <span class="text-blue-600 underline">Pilih berkas</span> atau tarik & lepas di sini
                                        </p>
                                        <p class="text-[10px] sm:text-[11px] text-slate-400">
                                            Format: <strong>PDF</strong> (Maksimal 5 MB)
                                        </p>
                                    </div>
                                </div>
                                <!-- Selected File Preview Feedback Card -->
                                <div id="reportFileBox" class="hidden mt-3 p-3.5 sm:p-4 bg-blue-50/70 border border-blue-200 rounded-2xl items-center justify-between gap-3 shadow-2xs">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-blue-600 text-white font-black text-xs flex items-center justify-center shrink-0 shadow-xs uppercase" id="reportFileIcon">
                                            PDF
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p id="reportFileName" class="text-xs sm:text-sm font-bold text-slate-800 truncate"></p>
                                            <p id="reportFileSize" class="text-[10px] sm:text-xs text-slate-500 font-mono"></p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 shrink-0">
                                        <a id="reportViewFileBtn" href="#" target="_blank" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white border border-blue-600 rounded-xl text-xs font-bold transition shadow-2xs inline-flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            <span>Lihat Berkas</span>
                                        </a>
                                        <button type="button" onclick="document.getElementById('file_laporan').click()" class="px-3 py-1.5 bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 rounded-xl text-xs font-bold transition shadow-2xs">
                                            Ganti Berkas
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="pt-3 border-t border-slate-100 flex flex-col-reverse sm:flex-row items-stretch sm:items-center sm:justify-end gap-2.5 sm:gap-3">
                                <a href="{{ route('dashboard') }}" class="w-full sm:w-auto px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition text-center justify-center flex items-center">
                                    Batal
                                </a>
                                <button type="submit" class="w-full sm:w-auto px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-md transition active:scale-95 cursor-pointer text-center justify-center flex items-center">
                                    {{ $finalReport ? 'Unggah Ulang Naskah Revisi' : 'Kirim Laporan Akhir' }}
                                </button>
                            </div>
                        </form>
                    </div>
                @else
                    <!-- TAMPILAN JIKA SUDAH ACC LAPORAN (LULUS & TAMPIL NILAI) -->
                    <div class="bg-white rounded-2xl sm:rounded-3xl p-4 sm:p-6 lg:p-8 border border-emerald-200 shadow-xs space-y-5 sm:space-y-6">
                        <div class="p-5 sm:p-6 bg-gradient-to-r from-emerald-50 via-teal-50 to-emerald-50 border border-emerald-200/80 rounded-2xl text-center space-y-2.5 shadow-2xs">
                            <div class="w-12 h-12 sm:w-14 sm:h-14 mx-auto rounded-2xl bg-emerald-600 text-white flex items-center justify-center shadow-md shadow-emerald-600/30">
                                <svg class="w-6 h-6 sm:w-7 sm:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <h3 class="font-black text-base sm:text-xl text-emerald-950">Laporan Akhir Anda Telah Disetujui Secara Resmi (ACC)</h3>
                            <p class="text-xs sm:text-sm text-emerald-800 max-w-xl mx-auto leading-relaxed">
                                Selamat! Naskah laporan ilmiah dan luaran magang Anda telah diverifikasi dan disetujui oleh Dosen Pembimbing Lapangan dan Mentor Instansi Pemerintah Kota Surabaya.
                            </p>
                        </div>

                        @php
                            $univ = $evaluation?->getUniversity();
                            $scheme = $evaluation?->evaluation_scheme ?? ($univ->evaluation_scheme ?? 'dual_evaluation');
                            $isMentorOnly = ($scheme === 'mentor_only');

                            $hasMentorScore = $evaluation && ($evaluation->nilai_pembimbing ?? 0) > 0;
                            $hasDosenScore = $evaluation && (($evaluation->nilai_dosen_calculated ?? 0) > 0 || ($evaluation->nilai_dosen ?? 0) > 0 || ($evaluation->nilai_akademik ?? 0) > 0);

                            $isEvalComplete = $evaluation && $evaluation->is_complete;
                            $hasFilledLogbook = (bool) ($application->has_filled_logbook ?? false);
                            $canGraduate = $isEvalComplete && $hasFilledLogbook && ($finalReport && $finalReport->status === 'approved');
                        @endphp

                        @if ($canGraduate)
                            <div class="space-y-4 pt-1">
                                <div class="flex items-center justify-between border-b border-gray-100 pb-3 gap-2">
                                    <div>
                                        <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-indigo-600 block">Hasil Evaluasi Magang MBKM</span>
                                        <h4 class="font-black text-sm sm:text-base text-gray-900">Rekapitulasi Nilai Kelulusan & Prestasi</h4>
                                    </div>
                                    <span class="px-2.5 py-1 bg-emerald-100 text-emerald-800 font-bold text-[11px] sm:text-xs rounded-full border border-emerald-200 shrink-0">
                                        Lulus Magang
                                    </span>
                                </div>

                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 sm:gap-3">
                                    <div class="p-3 sm:p-4 rounded-xl sm:rounded-2xl bg-slate-50 border border-slate-100 text-center">
                                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Nilai Disiplin</span>
                                        <span class="text-lg sm:text-xl font-black text-slate-800 mt-1 block">{{ number_format($evaluation->nilai_disiplin ?? 0, 1) }}</span>
                                        <span class="text-[10px] text-slate-400">Kehadiran</span>
                                    </div>
                                    <div class="p-3 sm:p-4 rounded-xl sm:rounded-2xl bg-slate-50 border border-slate-100 text-center">
                                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Nilai Kinerja</span>
                                        <span class="text-lg sm:text-xl font-black text-slate-800 mt-1 block">{{ number_format($evaluation->nilai_kinerja ?? 0, 1) }}</span>
                                        <span class="text-[10px] text-slate-400">Keahlian</span>
                                    </div>
                                    <div class="p-3 sm:p-4 rounded-xl sm:rounded-2xl bg-slate-50 border border-slate-100 text-center">
                                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Nilai Laporan</span>
                                        <span class="text-lg sm:text-xl font-black text-slate-800 mt-1 block">{{ number_format($evaluation->nilai_laporan ?? 0, 1) }}</span>
                                        <span class="text-[10px] text-slate-400">Kualitas Naskah</span>
                                    </div>
                                    <div class="p-3 sm:p-4 rounded-xl sm:rounded-2xl bg-indigo-50 border border-indigo-100 text-center">
                                        <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-700 block">Nilai Akhir</span>
                                        <span class="text-lg sm:text-xl font-black text-indigo-950 mt-1 block">
                                            {{ number_format((float) $evaluation->nilai_akhir, 1) }}
                                            <span class="text-xs font-bold text-indigo-600 font-mono">({{ $evaluation->grade_calculated }})</span>
                                        </span>
                                        <span class="text-[10px] text-indigo-600 font-semibold">Predikat</span>
                                    </div>
                                </div>

                                @if($evaluation->catatan || $evaluation->feedback_dosen)
                                    <div class="p-3.5 sm:p-4 rounded-xl sm:rounded-2xl bg-slate-50 border border-slate-100 space-y-2 text-xs">
                                        @if($evaluation->catatan)
                                            <p class="text-slate-700"><strong class="text-slate-900">Ulasan Pembimbing Lapangan:</strong> <span class="italic text-slate-600">"{{ $evaluation->catatan }}"</span></p>
                                        @endif
                                        @if($evaluation->feedback_dosen)
                                            <p class="text-slate-700"><strong class="text-slate-900">Ulasan Dosen Pembimbing:</strong> <span class="italic text-slate-600">"{{ $evaluation->feedback_dosen }}"</span></p>
                                        @endif
                                    </div>
                                @endif

                                @php $certBlockers = $application ? $application->certificateBlockers() : ['Belum ada pengajuan magang.']; @endphp
                                @if ($certBlockers === [])
                                <div class="pt-3 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-end gap-2.5 sm:gap-3">
                                    <a href="{{ route('student.certificate.show', $placement->id) }}" target="_blank"
                                       class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl text-xs transition shadow-md shadow-blue-600/20 active:scale-95 cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        <span>Pratinjau E-Sertifikat</span>
                                    </a>

                                    <a href="{{ route('student.certificate.download', $placement->id) }}" target="_blank"
                                       class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs transition shadow-md shadow-emerald-600/20 active:scale-95 cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                        <span>Unduh E-Sertifikat & Transkrip</span>
                                    </a>
                                @else
                                    <div class="pt-3 border-t border-gray-100">
                                        <div class="p-3.5 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-900 space-y-1">
                                            <p class="font-bold">Sertifikat belum dapat diunduh karena nilai evaluasi dinas atau laporan akhir belum disetujui.</p>
                                            <ul class="list-disc list-inside">
                                                @foreach ($certBlockers as $blocker)
                                                    <li>{{ $blocker }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                @endif
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="p-5 rounded-2xl bg-amber-50/90 border border-amber-200 space-y-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-xs">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <div>
                                    <span class="text-xs sm:text-sm font-bold text-amber-950 block">Menunggu Kelengkapan Syarat Kelulusan & Lembar Penilaian</span>
                                    <p class="text-xs text-amber-800 leading-relaxed">
                                        E-Sertifikat resmi akan otomatis terbit begitu seluruh 4 indikator kelayakan kelulusan di bawah terpenuhi.
                                    </p>
                                </div>
                            </div>

                            @if($isEvalComplete && !$hasFilledLogbook)
                                <div class="p-3.5 rounded-xl bg-rose-100/80 border border-rose-200 text-xs text-rose-900 flex items-start justify-between gap-3">
                                    <div class="flex items-start gap-2">
                                        <svg class="w-4 h-4 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <div>
                                            <strong class="font-bold">Perhatian: Logbook Belum Diisi</strong>
                                            <p class="text-[11px] text-rose-800 mt-0.5">Penilaian dan laporan akhir Anda telah selesai, namun Anda belum pernah mengisi logbook kegiatan harian. Anda wajib mengisi logbook untuk dapat lulus magang.</p>
                                        </div>
                                    </div>
                                    <a href="{{ route('student.logbook.index') }}" class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-bold shrink-0 transition shadow-2xs">
                                        Isi Logbook 
                                    </a>
                                </div>
                            @endif

                            <!-- 4-Item Progress Checklist -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs pt-1">
                                <!-- 1. Logbook Aktivitas -->
                                <div class="p-3 rounded-xl border {{ $hasFilledLogbook ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : 'bg-rose-50 border-rose-200 text-rose-900' }} space-y-1">
                                    <div class="flex items-center gap-1.5 font-bold">
                                        @if($hasFilledLogbook)
                                            <span class="text-emerald-600 font-bold">✓</span>
                                            <span>1. Logbook Harian</span>
                                        @else
                                            <span class="text-rose-500 font-bold">✗</span>
                                            <span>1. Logbook Harian</span>
                                        @endif
                                    </div>
                                    <p class="text-[11px] {{ $hasFilledLogbook ? 'text-emerald-700' : 'text-rose-700' }}">
                                        {{ $hasFilledLogbook ? 'Logbook Terisi' : 'Belum Diisi (Wajib)' }}
                                    </p>
                                </div>

                                <!-- 2. Pembimbing Lapangan -->
                                <div class="p-3 rounded-xl border {{ $hasMentorScore ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : 'bg-white border-amber-200 text-slate-700' }} space-y-1">
                                    <div class="flex items-center gap-1.5 font-bold">
                                        @if($hasMentorScore)
                                            <span class="text-emerald-600 font-bold">✓</span>
                                            <span>2. Pembimbing Lapangan</span>
                                        @else
                                            <span class="text-amber-500 font-bold">⏳</span>
                                            <span>2. Pembimbing Lapangan</span>
                                        @endif
                                    </div>
                                    <p class="text-[11px] {{ $hasMentorScore ? 'text-emerald-700' : 'text-slate-500' }}">
                                        {{ $hasMentorScore ? 'Selesai Menilai' : 'Menunggu Penilaian Dinas' }}
                                    </p>
                                </div>

                                <!-- 3. DPL Kampus -->
                                <div class="p-3 rounded-xl border {{ ($hasDosenScore || $isMentorOnly) ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : 'bg-white border-amber-200 text-slate-700' }} space-y-1">
                                    <div class="flex items-center gap-1.5 font-bold">
                                        @if($hasDosenScore || $isMentorOnly)
                                            <span class="text-emerald-600 font-bold">✓</span>
                                            <span>3. DPL Kampus</span>
                                        @else
                                            <span class="text-amber-500 font-bold">⏳</span>
                                            <span>3. DPL Kampus</span>
                                        @endif
                                    </div>
                                    <p class="text-[11px] {{ ($hasDosenScore || $isMentorOnly) ? 'text-emerald-700' : 'text-slate-500' }}">
                                        {{ $isMentorOnly ? 'Skema Kampus 100% Dinas' : ($hasDosenScore ? 'Selesai Menilai' : 'Menunggu Nilai Akademik DPL') }}
                                    </p>
                                </div>

                                <!-- 4. Naskah Laporan Akhir -->
                                <div class="p-3 rounded-xl border {{ ($finalReport && $finalReport->status === 'approved') ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : 'bg-white border-amber-200 text-slate-700' }} space-y-1">
                                    <div class="flex items-center gap-1.5 font-bold">
                                        @if($finalReport && $finalReport->status === 'approved')
                                            <span class="text-emerald-600 font-bold">✓</span>
                                            <span>4. Laporan Akhir</span>
                                        @else
                                            <span class="text-amber-500 font-bold">⏳</span>
                                            <span>4. Laporan Akhir</span>
                                        @endif
                                    </div>
                                    <p class="text-[11px] {{ ($finalReport && $finalReport->status === 'approved') ? 'text-emerald-700' : 'text-slate-500' }}">
                                        {{ ($finalReport && $finalReport->status === 'approved') ? 'Disetujui (ACC)' : 'Menunggu Persetujuan Naskah' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @endif
            @endif

        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/mammoth@1.6.0/mammoth.browser.min.js"></script>
    <script>
        let currentObjectUrl = null;

        function handleRepoUrlChange(input) {
            const badge = document.getElementById('repoUrlBadge');
            const testBtn = document.getElementById('repoTestBtn');
            const val = input.value.trim();

            if (val.length > 5 && (val.startsWith('http://') || val.startsWith('https://'))) {
                badge.classList.remove('hidden');
                testBtn.classList.remove('hidden');
                testBtn.classList.add('inline-flex');
                testBtn.href = val;
            } else {
                badge.classList.add('hidden');
                testBtn.classList.add('hidden');
                testBtn.classList.remove('inline-flex');
                testBtn.href = '#';
            }
        }

        function handleDragOver(e) {
            e.preventDefault();
            e.stopPropagation();
            const dz = document.getElementById('dropzoneArea');
            dz.classList.add('border-blue-600', 'bg-blue-100/50');
        }

        function handleDragLeave(e) {
            e.preventDefault();
            e.stopPropagation();
            const dz = document.getElementById('dropzoneArea');
            dz.classList.remove('border-blue-600', 'bg-blue-100/50');
        }

        function handleFileDrop(e) {
            e.preventDefault();
            e.stopPropagation();
            const dz = document.getElementById('dropzoneArea');
            dz.classList.remove('border-blue-600', 'bg-blue-100/50');

            if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
                const fileInput = document.getElementById('file_laporan');
                fileInput.files = e.dataTransfer.files;
                handleFinalReportPreview(fileInput);
            }
        }

        function handleFinalReportPreview(input) {
            const box = document.getElementById('reportFileBox');
            const nameEl = document.getElementById('reportFileName');
            const sizeEl = document.getElementById('reportFileSize');
            const iconEl = document.getElementById('reportFileIcon');
            const viewFileBtn = document.getElementById('reportViewFileBtn');

            if (currentObjectUrl) {
                URL.revokeObjectURL(currentObjectUrl);
                currentObjectUrl = null;
            }

            if (input.files && input.files[0]) {
                const file = input.files[0];
                if (file.size > 5 * 1024 * 1024) {
                    notify('Ukuran berkas naskah laporan melebihi batas maksimal 5MB.');
                    input.value = '';
                    box.classList.add('hidden');
                    box.classList.remove('flex');
                    return;
                }

                const ext = file.name.split('.').pop().toLowerCase();
                nameEl.textContent = file.name;
                sizeEl.textContent = (file.size / (1024 * 1024)).toFixed(2) + ' MB';
                iconEl.textContent = ext.toUpperCase();

                currentObjectUrl = URL.createObjectURL(file);
                if (viewFileBtn) {
                    viewFileBtn.href = currentObjectUrl;
                }

                box.classList.remove('hidden');
                box.classList.add('flex');
            } else {
                box.classList.add('hidden');
                box.classList.remove('flex');
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            const repoInput = document.getElementById('repository_url');
            if (repoInput && repoInput.value) {
                handleRepoUrlChange(repoInput);
            }
        });
    </script>
    @endpush
</x-app-layout>