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
            @elseif (in_array(strtoupper($lifecycle ?? ''), ['PENDING', 'WAITING_APPROVAL']) || !$placement)
                
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
                                Formulir pengunggahan naskah laporan ilmiah dan tautan luaran proyek magang akan otomatis aktif dan terbuka untuk diisi setelah pengajuan magang Anda <strong>diterima (disetujui)</strong> oleh pihak instansi kedinasan dan data Dosen Pembimbing telah dilengkapi. Anda dapat mengecek pembaruan status secara berkala di portal ini.
                            </p>
                        </div>
                    </div>
                </div>

            {{-- KONDISI 2: JIKA SUDAH MEMILIKI PENEMPATAN AKTIF (BISA UNGGAH / LIHAT STATUS LAPORAN) --}}
            @else

                <!-- Informational Banner: Panduan & Ketentuan Laporan Akhir -->
                <div class="p-4 sm:p-5 bg-gradient-to-r from-blue-50/90 via-indigo-50/40 to-blue-50/90 border border-blue-200/90 rounded-2xl shadow-2xs space-y-3">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-xl bg-blue-600 text-white flex items-center justify-center shrink-0 mt-0.5 shadow-2xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between flex-wrap gap-2">
                                <h4 class="font-bold text-blue-950 text-sm sm:text-base">Panduan dan Ketentuan Laporan Akhir Magang</h4>
                                <span class="text-[10px] font-bold px-2.5 py-0.5 bg-blue-100 text-blue-800 rounded-full border border-blue-200">Tahap Akhir Magang</span>
                            </div>
                            <p class="text-xs text-blue-800 mt-0.5 leading-relaxed">
                                Halaman ini digunakan untuk mengunggah naskah laporan ilmiah dan portofolio proyek inovasi sebagai syarat kelulusan magang.
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-2.5 pt-1 text-xs">
                        <div class="p-3 bg-white/80 rounded-xl border border-blue-100/80 space-y-1">
                            <div class="flex items-center gap-1.5 font-bold text-blue-950 text-xs">
                                <span class="w-5 h-5 rounded-md bg-blue-100 text-blue-700 flex items-center justify-center text-[10px] font-bold">1</span>
                                <span>Fleksibel & Bisa Dicicil</span>
                            </div>
                            <p class="text-[11px] text-slate-600 leading-relaxed">
                                Draf naskah laporan dan karya proyek inovasi dapat diunggah atau diperbarui kapan saja selama masa magang tanpa menunggu hari terakhir.
                            </p>
                        </div>

                        <div class="p-3 bg-white/80 rounded-xl border border-blue-100/80 space-y-1">
                            <div class="flex items-center gap-1.5 font-bold text-blue-950 text-xs">
                                <span class="w-5 h-5 rounded-md bg-blue-100 text-blue-700 flex items-center justify-center text-[10px] font-bold">2</span>
                                <span>Evaluasi 2 Pihak</span>
                            </div>
                            <p class="text-[11px] text-slate-600 leading-relaxed">
                                Naskah dan karya yang diunggah akan dinilai langsung oleh <strong>Mentor Dinas</strong> dan <strong>Dosen Pembimbing (DPL)</strong> sebagai penentu nilai akhir.
                            </p>
                        </div>

                        <div class="p-3 bg-white/80 rounded-xl border border-blue-100/80 space-y-1">
                            <div class="flex items-center gap-1.5 font-bold text-blue-950 text-xs">
                                <span class="w-5 h-5 rounded-md bg-blue-100 text-blue-700 flex items-center justify-center text-[10px] font-bold">3</span>
                                <span>Penerbitan E-Sertifikat</span>
                            </div>
                            <p class="text-[11px] text-slate-600 leading-relaxed">
                                E-Sertifikat resmi ber-QR Code Pemkot Surabaya akan otomatis aktif dapat diunduh setelah kedua pembimbing menyelesaikan penilaian.
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 pt-1 text-[11px] text-blue-900/90 font-medium border-t border-blue-100">
                        <svg class="w-3.5 h-3.5 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>Catatan: Pastikan logbook kegiatan harian tetap diisi secara teratur sampai periode magang berakhir.</span>
                    </div>
                </div>

                <!-- ========================================================================= -->
                <!-- BAGIAN 1: PROYEK INOVASI MAGANG (BISA DITAMBAHKAN LEBIH DARI 1) -->
                <!-- ========================================================================= -->
                <div class="space-y-4" x-data="{ showAllProjects: false }">

                    <!-- Header Bagian Proyek -->
                    <div class="bg-white rounded-2xl sm:rounded-3xl p-5 sm:p-6 border border-slate-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <span class="text-[10px] sm:text-xs font-bold text-slate-400 uppercase tracking-wider block">Bagian 1</span>
                            <h3 class="font-bold text-base sm:text-lg text-slate-900 mt-0.5">
                                Proyek Inovasi Magang
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Dokumentasi karya, tangkapan layar, buku panduan, dan dokumentasi manfaat nyata kegiatan magang bagi masyarakat
                            </p>
                        </div>

                        @if(!$finalReport || $finalReport->status !== 'approved')
                            <a href="{{ route('student.projects.create') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                <span>Tambah Proyek</span>
                            </a>
                        @endif
                    </div>

                    <!-- Daftar Kartu Proyek -->
                    @if($projects->isEmpty())
                        <div class="bg-white rounded-2xl sm:rounded-3xl p-8 border border-slate-200/80 text-center space-y-3">
                            <div class="w-12 h-12 mx-auto rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                            <div class="space-y-1">
                                <h4 class="font-bold text-sm text-slate-800">Belum Ada Proyek Ditambahkan</h4>
                                <p class="text-xs text-slate-500 max-w-md mx-auto">
                                    Silakan tekan tombol Tambah Proyek untuk mengunggah dokumentasi proyek dan manfaat bagi masyarakat di halaman khusus. Anda dapat menambahkan lebih dari satu proyek.
                                </p>
                            </div>
                        </div>
                    @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                            @foreach($projects as $index => $project)
                                @php
                                    $ssCount = is_array($project->screenshots) ? count($project->screenshots) : 0;
                                    $cpCount = is_array($project->community_photos) ? count($project->community_photos) : 0;
                                    $docCount = is_array($project->documents) ? count($project->documents) : 0;
                                @endphp
                                <div @if($index >= 3) x-show="showAllProjects" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" @endif 
                                     class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs hover:shadow-md hover:border-blue-200 transition-all duration-200 flex flex-col justify-between overflow-hidden group">
                                    <!-- Bagian Atas: Gambar & Informasi Utama -->
                                    <div>
                                        <!-- Foto Sampul Proyek (Proporsional & Anti-Pecah) -->
                                        <div class="relative w-full h-44 overflow-hidden bg-slate-100 shrink-0">
                                            <a href="{{ route('projects.show', $project->id) }}" class="block w-full h-full" title="Klik untuk melihat detail lengkap karya ini">
                                                <img src="{{ $project->photo_url }}" alt="{{ $project->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                            </a>
                                            
                                            <!-- Floating Badge Media (Kanan Bawah) -->
                                            <div class="absolute bottom-2.5 right-2.5 flex items-center gap-1">
                                                @if($ssCount > 0)
                                                    <span class="px-2 py-0.5 bg-slate-900/80 backdrop-blur-xs text-white text-[10px] font-bold rounded-md shadow-xs">
                                                        +{{ $ssCount }} Layar
                                                    </span>
                                                @endif
                                                @if($project->video_url)
                                                    <span class="px-2 py-0.5 bg-rose-600/90 backdrop-blur-xs text-white text-[10px] font-bold rounded-md flex items-center gap-1 shadow-xs">
                                                        <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                                        Video
                                                    </span>
                                                @endif
                                            </div>

                                            <!-- Badge Nomor Indeks Proyek (Kiri Atas) -->
                                            <div class="absolute top-2.5 left-2.5">
                                                <span class="px-2 py-0.5 bg-white/90 backdrop-blur-xs text-slate-800 text-[10px] font-bold rounded-md border border-slate-200/60 shadow-2xs">
                                                    Proyek #{{ $index + 1 }}
                                                </span>
                                            </div>
                                        </div>

                                        <!-- Konten Judul & Deskripsi Singkat -->
                                        <div class="p-4 sm:p-5 space-y-2.5">
                                            <div class="space-y-1.5">
                                                <span class="inline-block px-2 py-0.5 rounded-md text-[10px] font-bold border {{ $project->category_badge_class }}">
                                                    {{ $project->category_label }}
                                                </span>
                                                <a href="{{ route('projects.show', $project->id) }}" class="block">
                                                    <h4 class="font-bold text-sm sm:text-base text-slate-900 leading-snug line-clamp-2 hover:text-blue-600 transition min-h-[2.5rem]">
                                                        {{ $project->title }}
                                                    </h4>
                                                </a>
                                                <p class="text-xs text-slate-500 mt-1 leading-relaxed line-clamp-2">
                                                    {{ $project->description }}
                                                </p>
                                            </div>

                                            <!-- Indikator Berkas / Akses Cepat -->
                                            <div class="flex flex-wrap items-center gap-1.5 text-[10px] pt-1">
                                                @if($project->demo_url)
                                                    <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 font-semibold rounded-md border border-emerald-100 flex items-center gap-1">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                        Akses Daring
                                                    </span>
                                                @endif
                                                @if($project->repository_url)
                                                    <span class="px-2 py-0.5 bg-indigo-50 text-indigo-700 font-semibold rounded-md border border-indigo-100">
                                                        Repo / Cloud
                                                    </span>
                                                @endif
                                                @if($project->source_code_path)
                                                    <span class="px-2 py-0.5 bg-amber-50 text-amber-700 font-semibold rounded-md border border-amber-100">
                                                        ZIP Berkas
                                                    </span>
                                                @endif
                                                @if($cpCount > 0)
                                                    <span class="px-2 py-0.5 bg-teal-50 text-teal-700 font-semibold rounded-md border border-teal-100">
                                                        {{ $cpCount }} Lapangan
                                                    </span>
                                                @endif
                                                @if($docCount > 0)
                                                    <span class="px-2 py-0.5 bg-blue-50 text-blue-700 font-semibold rounded-md border border-blue-100">
                                                        {{ $docCount }} Dokumen
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Bagian Bawah: Tombol Aksi -->
                                    <div class="px-4 sm:px-5 pb-4 pt-3 border-t border-slate-100 flex items-center justify-between gap-2 mt-auto">
                                        <a href="{{ route('projects.show', $project->id) }}" class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-bold rounded-lg transition cursor-pointer">
                                            <span>Lihat Detail</span>
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                        </a>

                                        @if(!$finalReport || $finalReport->status !== 'approved')
                                            <div class="flex items-center gap-1.5">
                                                <a href="{{ route('student.projects.edit', $project->id) }}" class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-lg transition cursor-pointer" title="Ubah data proyek">
                                                    Ubah
                                                </a>
                                                <form action="{{ route('student.projects.destroy', $project->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus proyek ini?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="px-2.5 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-bold rounded-lg transition cursor-pointer" title="Hapus proyek">
                                                        Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Tombol Tampilkan Semua / Tampilkan Lebih Sedikit jika > 3 proyek -->
                        @if(count($projects) > 3)
                            <div class="text-center pt-2">
                                <button type="button" 
                                        @click="showAllProjects = !showAllProjects" 
                                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold transition shadow-2xs hover:shadow-xs cursor-pointer">
                                    <span x-text="showAllProjects ? 'Tampilkan Lebih Sedikit (3 Proyek Teratas)' : 'Tampilkan Semua ({{ count($projects) }} Proyek)'"></span>
                                    <svg class="w-4 h-4 transition-transform duration-200 text-slate-500" :class="{ 'rotate-180': showAllProjects }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                            </div>
                        @endif
                    @endif
                </div>

                <!-- ========================================================================= -->
                <!-- BAGIAN 2: NASKAH LAPORAN AKHIR -->
                <!-- ========================================================================= -->
                <div class="bg-white rounded-2xl sm:rounded-3xl p-5 sm:p-6 border border-slate-200/80 shadow-xs">
                    <div>
                        <span class="text-[10px] sm:text-xs font-bold text-slate-400 uppercase tracking-wider block">Bagian 2</span>
                        <h3 class="font-bold text-base sm:text-lg text-slate-900 mt-0.5">
                            Naskah Laporan Akhir
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Pengunggahan berkas naskah laporan ilmiah Praktik Kerja Lapangan / Magang MBKM
                        </p>
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
                                        Agar memudahkan Dosen Pembimbing dan Mentor Dinas dalam memeriksa berkas Anda, pastikan nama file telah berformat standar:
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

                                <!-- 3. Dosen Pembimbing Kampus -->
                                <div class="p-3 rounded-xl border {{ ($hasDosenScore || $isMentorOnly) ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : 'bg-white border-amber-200 text-slate-700' }} space-y-1">
                                    <div class="flex items-center gap-1.5 font-bold">
                                        @if($hasDosenScore || $isMentorOnly)
                                            <span class="text-emerald-600 font-bold">✓</span>
                                            <span>3. Dosen Pembimbing</span>
                                        @else
                                            <span class="text-amber-500 font-bold">⏳</span>
                                            <span>3. Dosen Pembimbing</span>
                                        @endif
                                    </div>
                                    <p class="text-[11px] {{ ($hasDosenScore || $isMentorOnly) ? 'text-emerald-700' : 'text-slate-500' }}">
                                        {{ $isMentorOnly ? 'Skema Kampus 100% Dinas' : ($hasDosenScore ? 'Selesai Menilai' : 'Menunggu Nilai Dosen Pembimbing') }}
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

        function handleProjectPhotoPreview(input) {
            if (input.files && input.files[0]) {
                const file = input.files[0];
                if (!['image/jpeg', 'image/png', 'image/jpg', 'image/webp'].includes(file.type)) {
                    alert('Format foto harus berupa JPG, PNG, atau WebP.');
                    input.value = '';
                    return;
                }
                if (file.size > 3 * 1024 * 1024) {
                    alert('Ukuran foto maksimal 3 MB.');
                    input.value = '';
                    return;
                }
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('projectPhotoPreview');
                    const placeholder = document.getElementById('projectPhotoPlaceholder');
                    if (preview) {
                        preview.src = e.target.result;
                        preview.classList.remove('hidden');
                    }
                    if (placeholder) {
                        placeholder.classList.add('hidden');
                    }
                };
                reader.readAsDataURL(file);
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