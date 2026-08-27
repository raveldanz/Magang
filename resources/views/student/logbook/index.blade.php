<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard Aktivitas & Logbook Magang') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Flash Messages --}}
            @if (session('success'))
                <div class="p-4 bg-green-100 text-green-800 rounded-lg text-sm font-semibold flex items-center justify-between">
                    <span>{{ session('success') }}</span>
                </div>
            @endif
            @if (session('warning'))
                <div class="p-4 bg-yellow-100 text-yellow-800 rounded-lg text-sm font-semibold flex items-center justify-between">
                    <span>{{ session('warning') }}</span>
                </div>
            @endif
            @if (session('error'))
                <div class="p-4 bg-red-100 text-red-800 rounded-lg text-sm font-semibold flex items-center justify-between">
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @php
                $lifecycle = $application?->lifecycle_status ?? 'NONE';
            @endphp

            {{-- ========================================================================= --}}
            {{-- 1. ALERT BANNER SESUAI DYNAMIC LIFECYCLE                                  --}}
            {{-- ========================================================================= --}}

            @if (!$application || $lifecycle === 'NONE' || $lifecycle === 'DRAFT')
                <div class="bg-amber-50 border-l-4 border-amber-400 p-6 rounded-2xl shadow-xs">
                    <div class="flex items-start justify-between flex-wrap gap-4">
                        <div class="flex items-center gap-3">
                            <div>
                                <h4 class="font-bold text-amber-900 text-sm">Belum Ada Pengajuan Magang Aktif</h4>
                                <p class="text-xs text-amber-700 mt-0.5">Anda belum mengajukan permohonan magang. Silakan lakukan pendaftaran magang terlebih dahulu untuk membuka fitur logbook.</p>
                            </div>
                        </div>
                        <a href="{{ route('student.application.create') }}" class="px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-xl transition shadow-xs shrink-0">
                            Ajukan Magang Sekarang &rarr;
                        </a>
                    </div>
                </div>

            @elseif (in_array($lifecycle, ['PENDING', 'SUBMITTED', 'VERIFIED']))
                <div class="bg-amber-50 border-l-4 border-amber-400 p-6 rounded-2xl shadow-xs">
                    <div class="flex items-start justify-between flex-wrap gap-4">
                        <div class="flex items-start gap-3">
                            <div>
                                <h4 class="font-bold text-amber-900 text-sm sm:text-base">Pengajuan Magang Sedang Diverifikasi</h4>
                                <p class="text-xs sm:text-sm text-amber-700 mt-0.5">
                                    Pengajuan magang Anda saat ini sedang dalam proses verifikasi dan seleksi oleh instansi <strong>{{ $application->unit->agencyProfile->agency_name ?? 'Pemerintah Kota Surabaya' }}</strong>.
                                </p>
                            </div>
                        </div>
                        <span class="px-3 py-1 bg-amber-200 text-amber-900 text-xs font-black rounded-full uppercase tracking-wider">
                            Status: {{ $lifecycle }}
                        </span>
                    </div>
                </div>

                <!-- Status Alur & Detail Pengajuan -->
                <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-100 shadow-sm space-y-6">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                        <div class="flex items-center gap-3">
                            <div>
                                <h3 class="font-black text-base text-gray-900">Alur Proses & Informasi Pengajuan Magang</h3>
                                <p class="text-xs text-gray-400">Pantau tahapan seleksi dan penempatan Anda</p>
                            </div>
                        </div>
                        <a href="{{ route('dashboard') }}" class="text-xs font-bold text-blue-600 hover:text-blue-800">
                            Buka Dashboard Utama &rarr;
                        </a>
                    </div>

                    <!-- Progress Stepper Tracker -->
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
                            <p class="text-[11px] text-slate-500">Penetapan Mentor Dinas & Dosen Pembimbing Lapangan (DPL).</p>
                        </div>
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 opacity-60 space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full bg-slate-300 text-slate-700 text-xs font-bold flex items-center justify-center">4</span>
                                <h4 class="font-bold text-xs text-slate-700">Logbook & Magang Aktif</h4>
                            </div>
                            <p class="text-[11px] text-slate-500">Pengisian logbook harian dibuka saat masa magang aktif.</p>
                        </div>
                    </div>

                    <!-- Detail Pendaftaran Mahasiswa -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 text-xs pt-2">
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                            <span class="text-slate-400 uppercase font-semibold text-[10px] block mb-1">Instansi Penempatan</span>
                            <span class="font-bold text-slate-800 text-sm block">{{ $application->unit->agencyProfile->agency_name ?? 'Instansi Dinas' }}</span>
                            <span class="text-slate-500 text-xs mt-0.5 block">{{ $application->unit->name ?? '-' }}</span>
                        </div>
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                            <span class="text-slate-400 uppercase font-semibold text-[10px] block mb-1">Universitas / Kampus</span>
                            <span class="font-bold text-slate-800 text-sm block">{{ Auth::user()->studentProfile->universitas ?? Auth::user()->university ?? '-' }}</span>
                            <span class="text-slate-500 text-xs mt-0.5 block">{{ Auth::user()->studentProfile->jurusan ?? '-' }}</span>
                        </div>
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                            <span class="text-slate-400 uppercase font-semibold text-[10px] block mb-1">Periode Magang Diajukan</span>
                            <span class="font-bold text-slate-800 text-xs block">{{ \Carbon\Carbon::parse($application->start_date)->translatedFormat('d M Y') }}</span>
                            <span class="text-slate-500 text-xs block">s/d {{ \Carbon\Carbon::parse($application->end_date)->translatedFormat('d M Y') }}</span>
                        </div>
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                            <span class="text-slate-400 uppercase font-semibold text-[10px] block mb-1">Tanggal Diajukan</span>
                            <span class="font-bold text-slate-800 text-xs block">{{ $application->created_at ? $application->created_at->translatedFormat('d F Y, H:i') : '-' }}</span>
                            <span class="text-amber-600 font-semibold text-[11px] block mt-1">Menunggu Keputusan</span>
                        </div>
                    </div>

                    <!-- Notice Information Box -->
                    <div class="p-4 rounded-2xl bg-blue-50/80 border border-blue-200 flex items-start gap-3">
                        <span class="text-xl shrink-0">💡</span>
                        <div class="text-xs text-blue-900 space-y-1">
                            <p class="font-bold">Informasi Akses Logbook:</p>
                            <p class="text-blue-800 leading-relaxed">
                                Fitur pengisian <strong>Logbook Harian</strong> akan otomatis aktif dan terbuka untuk diisi setelah pengajuan magang Anda <strong>diterima (disetujui)</strong> oleh instansi penempatan dan data DPL telah dilengkapi. Anda dapat mengecek pembaruan status secara berkala di portal ini.
                            </p>
                        </div>
                    </div>
                </div>

            @elseif ($lifecycle === 'REJECTED')
                <div class="bg-rose-50 border-l-4 border-rose-500 p-6 rounded-2xl shadow-xs">
                    <div class="flex items-start justify-between flex-wrap gap-4">
                        <div class="flex items-start gap-3">
                            <div>
                                <h4 class="font-bold text-rose-900 text-sm">Pengajuan Magang Ditolak</h4>
                                <p class="text-xs text-rose-700 mt-0.5">
                                    Catatan Penolakan: <em>"{{ $application->rejection_reason ?? $application->rejection_note ?? 'Berkas / kualifikasi belum memenuhi kriteria kuota instansi.' }}"</em>
                                </p>
                            </div>
                        </div>
                        <a href="{{ route('student.application.create') }}" class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl transition shadow-xs shrink-0">
                            Ajukan Ulang Magang &rarr;
                        </a>
                    </div>
                </div>

            @elseif ($lifecycle === 'ACCEPTED' || ($lifecycle === 'ACTIVE' && $requiresDpl && (!$placement || empty($placement->academic_advisor_id))))
                <div class="bg-amber-50 border-l-4 border-amber-500 p-6 rounded-2xl shadow-xs">
                    <div class="flex items-start justify-between flex-wrap gap-4">
                        <div class="flex items-start gap-3">
                            <div>
                                <h4 class="font-bold text-amber-900 text-sm sm:text-base">
                                    {{ ($requiresDpl && (!$placement || empty($placement->academic_advisor_id))) ? 'Dosen Pembimbing (DPL) Belum Dipilih' : 'Pengajuan Telah Disetujui (Menunggu Tanggal Mulai Magang)' }}
                                </h4>
                                @if ($requiresDpl && (!$placement || empty($placement->academic_advisor_id)))
                                    <p class="text-xs sm:text-sm text-amber-700 mt-1 leading-relaxed">
                                        Pengajuan magang Anda telah <strong>DITERIMA</strong> oleh dinas dan mentor lapangan telah terdaftar. Namun, Anda <strong>belum menentukan Dosen Pembimbing Lapangan (DPL)</strong> dari perguruan tinggi Anda.
                                    </p>
                                    <p class="text-xs text-amber-800 font-semibold mt-1">
                                        Fitur pengisian logbook harian baru akan terbuka setelah DPL terdaftar agar kegiatan magang dapat dipantau dan diverifikasi dua arah oleh kampus.
                                    </p>
                                @else
                                    <p class="text-xs text-amber-700 mt-0.5">
                                        Pengajuan Anda telah <strong>DITERIMA</strong>. Logbook harian akan terbuka otomatis saat tanggal mulai magang pada tanggal <strong>{{ \Carbon\Carbon::parse($application->start_date)->translatedFormat('d F Y') }}</strong>.
                                    </p>
                                @endif
                            </div>
                        </div>
                        @if ($requiresDpl && (!$placement || empty($placement->academic_advisor_id)))
                            <a href="{{ route('dashboard') }}" class="px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-xl transition shadow-xs shrink-0 flex items-center gap-2 cursor-pointer">
                                <span>Pilih DPL di Dashboard &rarr;</span>
                            </a>
                        @endif
                    </div>
                </div>

            @elseif ($lifecycle === 'COMPLETED')
                <div class="bg-emerald-50 border-l-4 border-emerald-500 p-6 rounded-2xl shadow-xs">
                    <div class="flex items-start justify-between flex-wrap gap-4">
                        <div class="flex items-start gap-3">
                            <div>
                                <h4 class="font-bold text-emerald-900 text-sm">Selamat! Anda Telah Menyelesaikan Magang</h4>
                                <p class="text-xs text-emerald-700 mt-0.5">
                                    Seluruh rangkaian kegiatan magang dan evaluasi telah selesai disetujui. Riwayat logbook kini tersimpan rapi dalam mode arsip.
                                </p>
                            </div>
                        </div>
                        @if ($placement)
                            <div class="flex items-center gap-2">
                                <a href="{{ route('student.certificate.download', $placement->id) }}" target="_blank" class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition shadow-xs cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Unduh E-Sertifikat & Nilai (PDF)
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- ========================================================================= --}}
            {{-- 2. INFORMASI PENEMPATAN & STATISTIK JIKA APPLICATION SUDAH ACCEPTED / SELESAI --}}
            {{-- ========================================================================= --}}
            @if ($application && in_array($lifecycle, ['ACTIVE', 'ACCEPTED', 'COMPLETED']))

                {{-- Card 1: Informasi Penempatan Magang --}}
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
                            <p class="font-bold text-sm text-white">{{ $application->unit->agencyProfile->agency_name ?? '-' }}</p>
                            <p class="text-blue-200 font-medium">{{ $application->unit->name ?? '-' }}</p>
                        </div>
                        <div class="p-3 bg-white/10 rounded-xl">
                            <p class="text-blue-200 uppercase tracking-wider mb-0.5">Mentor Lapangan Dinas</p>
                            <p class="font-bold text-sm text-white">{{ $placement->mentor->name ?? $placement->pembimbing->name ?? 'Belum Ditentukan' }}</p>
                        </div>
                        <div class="p-3 bg-white/10 rounded-xl">
                            <p class="text-blue-200 uppercase tracking-wider mb-0.5">Dosen Pembimbing (DPL)</p>
                            <p class="font-bold text-sm text-white">{{ $placement->academicAdvisor->name ?? 'Belum Ditentukan' }}</p>
                        </div>
                        <div class="p-3 bg-white/10 rounded-xl">
                            <p class="text-blue-200 uppercase tracking-wider mb-0.5">Periode Magang</p>
                            <p class="font-bold text-sm text-white">{{ \Carbon\Carbon::parse($application->start_date)->format('d M Y') }} s/d {{ \Carbon\Carbon::parse($application->end_date)->format('d M Y') }}</p>
                        </div>
                    </div>
                </div>

                {{-- Card 2: 4 Kartu Statistik Logbook --}}
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-white p-5 rounded-2xl shadow-xs border-l-4 border-blue-500">
                        <p class="text-xs text-gray-500 font-medium">Total Logbook</p>
                        <p class="text-3xl font-bold text-gray-800 mt-1">{{ $stats['total'] ?? 0 }}</p>
                    </div>
                    <div class="bg-white p-5 rounded-2xl shadow-xs border-l-4 border-emerald-500">
                        <p class="text-xs text-gray-500 font-medium">Disetujui</p>
                        <p class="text-3xl font-bold text-emerald-600 mt-1">{{ $stats['approved'] ?? 0 }}</p>
                    </div>
                    <div class="bg-white p-5 rounded-2xl shadow-xs border-l-4 border-amber-500">
                        <p class="text-xs text-gray-500 font-medium">Menunggu Review</p>
                        <p class="text-3xl font-bold text-amber-500 mt-1">{{ $stats['pending'] ?? 0 }}</p>
                    </div>
                    <div class="bg-white p-5 rounded-2xl shadow-xs border-l-4 border-rose-500">
                        <p class="text-xs text-gray-500 font-medium">Ditolak / Revisi</p>
                        <p class="text-3xl font-bold text-rose-600 mt-1">{{ $stats['rejected'] ?? 0 }}</p>
                    </div>
                </div>

                {{-- Card 3: Tabel Daftar Logbook Harian --}}
                <div class="bg-white rounded-2xl shadow-xs overflow-hidden border border-gray-200">
                    <div class="p-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 border-b border-gray-100">
                        <div>
                            <h3 class="text-base font-bold text-gray-800 flex items-center gap-2">
                                Daftar Logbook Harian
                            </h3>
                            <p class="text-xs text-gray-400 mt-0.5">Catatan aktivitas dan verifikasi dua arah (Mentor Dinas & Dosen Kampus)</p>
                        </div>

                        {{-- Tombol Tambah Logbook (Hanya Tampil Jika Status ACTIVE & Kebijakan DPL Terpenuhi) --}}
                        @if ($lifecycle === 'ACTIVE')
                            @if (!$requiresDpl || ($placement && !empty($placement->academic_advisor_id)))
                                <a href="{{ route('student.logbook.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-xs transition cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Tambah Aktivitas Logbook
                                </a>
                            @else
                                <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-xs font-bold shadow-xs transition cursor-pointer" title="Pilih Dosen Pembimbing Lapangan di Dashboard">
                                    <span>Pilih DPL di Dashboard Terlebih Dahulu</span>
                                </a>
                            @endif
                        @elseif ($lifecycle === 'COMPLETED')
                            <span class="px-3 py-1.5 bg-emerald-50 text-emerald-700 text-xs font-bold rounded-xl border border-emerald-200">
                                Mode Arsip Magang Selesai
                            </span>
                        @else
                            <span class="px-3 py-1.5 bg-gray-100 text-gray-500 text-xs font-bold rounded-xl">
                                Logbook Terkunci
                            </span>
                        @endif
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-gray-100 text-xs font-semibold text-gray-500 bg-gray-50/50">
                                    <th class="p-4">No</th>
                                    <th class="p-4">Tanggal</th>
                                    <th class="p-4">Kegiatan</th>
                                    <th class="p-4">Lampiran</th>
                                    <th class="p-4 text-center">Status Mentor Dinas</th>
                                    <th class="p-4 text-center">Status Dosen Kampus</th>
                                    <th class="p-4 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm divide-y divide-gray-50">
                                @forelse ($logbooks as $index => $log)
                                    <tr class="hover:bg-gray-50/50 transition">
                                        <td class="p-4 text-gray-500">{{ $index + 1 }}</td>
                                        <td class="p-4 font-semibold text-gray-700 whitespace-nowrap">
                                            {{ \Carbon\Carbon::parse($log->date)->format('d M Y') }}
                                        </td>
                                        <td class="p-4 text-gray-700 max-w-xs">
                                            <p class="truncate" title="{{ $log->activity }}">{{ Str::limit($log->activity, 80) }}</p>
                                        </td>
                                        <td class="p-4">
                                            @if ($log->attachment)
                                                <a href="{{ asset('storage/' . $log->attachment) }}" target="_blank" class="text-blue-600 hover:text-blue-800 underline text-xs font-medium inline-flex items-center gap-1">
                                                    <span>📎</span> Lihat File
                                                </a>
                                            @else
                                                <span class="text-gray-400 text-xs">-</span>
                                            @endif
                                        </td>
                                        <td class="p-4 text-center">
                                            <span class="px-2.5 py-1 text-[11px] font-bold rounded-full 
                                                {{ strtolower($log->status) === 'approved' ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : '' }}
                                                {{ strtolower($log->status) === 'pending' ? 'bg-amber-100 text-amber-800 border border-amber-300' : '' }}
                                                {{ strtolower($log->status) === 'rejected' ? 'bg-rose-100 text-rose-800 border border-rose-300' : '' }}">
                                                {{ strtoupper($log->status) }}
                                            </span>
                                        </td>
                                        <td class="p-4 text-center">
                                            @if (!$requiresDpl)
                                                <span class="px-2.5 py-1 text-[11px] font-bold rounded-full bg-slate-100 text-slate-600 border border-slate-200" title="Kebijakan Penilaian 100% Instansi Dinas">
                                                    — Dilewati
                                                </span>
                                            @else
                                                <span class="px-2.5 py-1 text-[11px] font-bold rounded-full 
                                                    {{ strtolower($log->lecturer_status ?? 'pending') === 'approved' ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : '' }}
                                                    {{ strtolower($log->lecturer_status ?? 'pending') === 'pending' ? 'bg-amber-100 text-amber-800 border border-amber-300' : '' }}
                                                    {{ strtolower($log->lecturer_status ?? 'pending') === 'rejected' ? 'bg-rose-100 text-rose-800 border border-rose-300' : '' }}">
                                                    {{ strtoupper($log->lecturer_status ?? 'PENDING') }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="p-4 text-center">
                                            @if ($lifecycle === 'ACTIVE' && (strtolower($log->status) === 'pending' || strtolower($log->status) === 'rejected'))
                                                <a href="{{ route('student.logbook.edit', $log->id) }}" class="btn-action-edit">
                                                    Edit
                                                </a>
                                            @else
                                                <span class="text-gray-400 text-xs">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="p-8 text-center text-gray-400 text-xs">
                                            @if ($lifecycle === 'ACTIVE')
                                                Belum ada catatan logbook. Klik tombol <strong class="text-gray-700">"+ Tambah Aktivitas Logbook"</strong> untuk mulai mencatat.
                                            @else
                                                Belum ada catatan logbook yang tersimpan.
                                            @endif
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            @endif

        </div>
    </div>
</x-app-layout>
