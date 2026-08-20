<x-app-layout>
    <div class="py-8 bg-[#F5F8FC] min-h-screen text-slate-900 font-sans">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

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

            @if (!$application || $lifecycle === 'NONE')
                <div class="bg-amber-50 border-l-4 border-amber-400 p-6 rounded-2xl shadow-xs">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">📝</span>
                            <div>
                                <h4 class="font-bold text-amber-900 text-sm">Belum Ada Pengajuan Magang Aktif</h4>
                                <p class="text-xs text-amber-700 mt-0.5">Anda belum mengajukan permohonan magang. Silakan lakukan pendaftaran magang terlebih dahulu untuk membuka fitur logbook.</p>
                            </div>
                        </div>
                        <a href="{{ route('student.application.create') }}" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-xl transition shadow-xs shrink-0">
                            Ajukan Magang Sekarang &rarr;
                        </a>
                    </div>
                </div>

            @elseif ($lifecycle === 'PENDING')
                <div class="bg-amber-50 border-l-4 border-amber-400 p-6 rounded-2xl shadow-xs">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl">⏳</span>
                        <div>
                            <h4 class="font-bold text-amber-900 text-sm">Pengajuan Sedang Diverifikasi</h4>
                            <p class="text-xs text-amber-700 mt-0.5">Pengajuan magang Anda sedang dalam proses verifikasi dan seleksi oleh instansi kedinasan (<strong>{{ $application->unit->agencyProfile->agency_name ?? 'Pemkot Surabaya' }}</strong>).</p>
                        </div>
                    </div>
                </div>

            @elseif ($lifecycle === 'REJECTED')
                <div class="bg-rose-50 border-l-4 border-rose-500 p-6 rounded-2xl shadow-xs">
                    <div class="flex items-start justify-between">
                        <div class="flex items-start gap-3">
                            <span class="text-2xl">❌</span>
                            <div>
                                <h4 class="font-bold text-rose-900 text-sm">Pengajuan Magang Ditolak</h4>
                                <p class="text-xs text-rose-700 mt-0.5">
                                    Catatan: <em>"{{ $application->rejection_reason ?? $application->rejection_note ?? 'Berkas / kualifikasi belum memenuhi kriteria instansi.' }}"</em>
                                </p>
                            </div>
                        </div>
                        <a href="{{ route('student.application.create') }}" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl transition shadow-xs shrink-0">
                            Ajukan Ulang &rarr;
                        </a>
                    </div>
                </div>

            @elseif ($lifecycle === 'ACCEPTED')
                <div class="bg-blue-50 border-l-4 border-blue-500 p-6 rounded-2xl shadow-xs">
                    <div class="flex items-start justify-between">
                        <div class="flex items-start gap-3">
                            <span class="text-2xl">ℹ️</span>
                            <div>
                                <h4 class="font-bold text-blue-900 text-sm">Pengajuan Telah Disetujui (Menunggu Masa Aktif / DPL)</h4>
                                @if (!$placement || empty($placement->academic_advisor_id))
                                    <p class="text-xs text-blue-700 mt-0.5">
                                        Pengajuan Anda telah <strong>DITERIMA</strong>. Silakan tentukan/pilih <strong>Dosen Pembimbing Lapangan (DPL)</strong> pada dashboard untuk mengaktifkan logbook harian.
                                    </p>
                                @else
                                    <p class="text-xs text-blue-700 mt-0.5">
                                        Pengajuan Anda telah <strong>DITERIMA</strong> dan pembimbing telah lengkap. Logbook harian akan terbuka otomatis saat tanggal mulai magang pada tanggal <strong>{{ \Carbon\Carbon::parse($application->start_date)->translatedFormat('d F Y') }}</strong>.
                                    </p>
                                @endif
                            </div>
                        </div>
                        @if (!$placement || empty($placement->academic_advisor_id))
                            <a href="{{ route('dashboard') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl transition shadow-xs shrink-0">
                                Pilih DPL Sekarang &rarr;
                            </a>
                        @endif
                    </div>
                </div>

            @elseif ($lifecycle === 'COMPLETED')
                <div class="bg-emerald-50 border-l-4 border-emerald-500 p-6 rounded-2xl shadow-xs">
                    <div class="flex items-start justify-between flex-wrap gap-3">
                        <div class="flex items-start gap-3">
                            <span class="text-2xl">🎉</span>
                            <div>
                                <h4 class="font-bold text-emerald-900 text-sm">Selamat! Anda Telah Menyelesaikan Magang</h4>
                                <p class="text-xs text-emerald-700 mt-0.5">
                                    Seluruh rangkaian kegiatan magang dan evaluasi telah selesai disetujui. Riwayat logbook kini tersimpan rapi dalam mode arsip.
                                </p>
                            </div>
                        </div>
                        @if ($placement)
                            <div class="flex items-center gap-2">
                                <a href="{{ route('student.certificate.download', $placement->id) }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition shadow-xs">
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
                <div class="bg-indigo-600 rounded-2xl p-6 text-white shadow-lg">
                    <div class="flex items-center justify-between mb-4 border-b border-indigo-500 pb-3">
                        <h3 class="text-base font-bold flex items-center gap-2">
                            <span>📑</span> Informasi Penempatan Magang
                        </h3>
                        <span class="text-xs font-bold px-3 py-1 bg-white/20 text-white rounded-full">
                            Status: {{ $lifecycle }}
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 text-xs">
                        <div class="p-3 bg-white/10 rounded-xl">
                            <p class="text-indigo-200 uppercase tracking-wider mb-0.5">Instansi & Unit Kerja</p>
                            <p class="font-bold text-sm text-white">{{ $application->unit->agencyProfile->agency_name ?? '-' }}</p>
                            <p class="text-indigo-200 font-medium">{{ $application->unit->name ?? '-' }}</p>
                        </div>
                        <div class="p-3 bg-white/10 rounded-xl">
                            <p class="text-indigo-200 uppercase tracking-wider mb-0.5">Mentor Lapangan Dinas</p>
                            <p class="font-bold text-sm text-white">{{ $placement->mentor->name ?? $placement->pembimbing->name ?? 'Belum Diplot' }}</p>
                        </div>
                        <div class="p-3 bg-white/10 rounded-xl">
                            <p class="text-indigo-200 uppercase tracking-wider mb-0.5">Dosen Pembimbing (DPL)</p>
                            <p class="font-bold text-sm text-white">{{ $placement->academicAdvisor->name ?? 'Belum Ditentukan' }}</p>
                        </div>
                        <div class="p-3 bg-white/10 rounded-xl">
                            <p class="text-indigo-200 uppercase tracking-wider mb-0.5">Periode Magang</p>
                            <p class="font-bold text-sm text-white">{{ \Carbon\Carbon::parse($application->start_date)->format('d M Y') }} s/d {{ \Carbon\Carbon::parse($application->end_date)->format('d M Y') }}</p>
                        </div>
                    </div>
                </div>

                {{-- Card 2: 4 Kartu Statistik Logbook --}}
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-white p-5 rounded-2xl shadow-xs border-l-4 border-indigo-500">
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
                                📒 Daftar Logbook Harian
                            </h3>
                            <p class="text-xs text-gray-400 mt-0.5">Catatan aktivitas dan verifikasi dua arah (Mentor Dinas & Dosen Kampus)</p>
                        </div>

                        {{-- Tombol Tambah Logbook (Hanya Tampil Jika Status ACTIVE) --}}
                        @if ($lifecycle === 'ACTIVE')
                            <a href="{{ route('student.logbook.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-xs transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Tambah Aktivitas Logbook
                            </a>
                        @elseif ($lifecycle === 'COMPLETED')
                            <span class="px-3 py-1.5 bg-emerald-50 text-emerald-700 text-xs font-bold rounded-xl border border-emerald-200">
                                🔒 Mode Arsip Magang Selesai
                            </span>
                        @else
                            <span class="px-3 py-1.5 bg-gray-100 text-gray-500 text-xs font-bold rounded-xl">
                                🔒 Logbook Terkunci
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
                            <tbody class="text-sm divide-y divide-slate-100">
                                @forelse ($logbooks as $index => $log)
                                    <tr class="hover:bg-gray-50/50 transition">
                                        <td class="p-4 text-gray-500">{{ $index + 1 }}</td>
                                        <td class="p-4 font-semibold text-gray-700 whitespace-nowrap">
                                            {{ \Carbon\Carbon::parse($log->date)->format('d M Y') }}
                                        </td>
                                        <td class="py-4 px-5 text-slate-700 max-w-xs leading-relaxed">
                                            <p class="truncate" title="{{ $log->activity }}">{{ Str::limit($log->activity, 80) }}</p>
                                        </td>
                                        <td class="py-4 px-5 whitespace-nowrap">
                                            @if ($log->attachment)
                                                <a href="{{ asset('storage/' . $log->attachment) }}" target="_blank" class="text-indigo-600 hover:text-indigo-800 underline text-xs font-medium inline-flex items-center gap-1">
                                                    <span>📎</span> Lihat File
                                                </a>
                                            @else
                                                <span class="text-slate-400 text-xs">-</span>
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
                                            <span class="px-2.5 py-1 text-[11px] font-bold rounded-full 
                                                {{ strtolower($log->lecturer_status ?? 'pending') === 'approved' ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : '' }}
                                                {{ strtolower($log->lecturer_status ?? 'pending') === 'pending' ? 'bg-amber-100 text-amber-800 border border-amber-300' : '' }}
                                                {{ strtolower($log->lecturer_status ?? 'pending') === 'rejected' ? 'bg-rose-100 text-rose-800 border border-rose-300' : '' }}">
                                                {{ strtoupper($log->lecturer_status ?? 'PENDING') }}
                                            </span>
                                        </td>
                                        <td class="p-4 text-center">
                                            @if ($lifecycle === 'ACTIVE' && (strtolower($log->status) === 'pending' || strtolower($log->status) === 'rejected'))
                                                <a href="{{ route('student.logbook.edit', $log->id) }}" class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-xs rounded-lg transition">
                                                    Edit
                                                </a>
                                            @else
                                                <span class="text-slate-400 text-xs">—</span>
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