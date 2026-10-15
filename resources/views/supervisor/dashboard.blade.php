<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold tracking-wide bg-blue-100 text-blue-800 border border-blue-200">
                        Atasan — Tim Kerja Umum & Kepegawaian
                    </span>
                    <span class="text-xs text-slate-500 font-medium">Dinas Perpustakaan dan Kearsipan Kota Surabaya</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                    Monitoring Bidang & Kepegawaian Magang
                </h1>
                <p class="text-sm text-slate-600 mt-1">
                    Pemantauan beban pembimbing tim kerja, alokasi kuota 4 bidang resmi, serta persetujuan berkas magang & pengesahan sertifikat.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('supervisor.applications.index') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold text-sm shadow-sm transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Persetujuan Pengajuan</span>
                    @if($pendingApplicationsCount > 0)
                        <span class="px-2 py-0.5 rounded-full text-xs font-black bg-white text-purple-700">{{ $pendingApplicationsCount }}</span>
                    @endif
                </a>
                <a href="{{ route('supervisor.certificates.index') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm shadow-sm transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Pengesahan Sertifikat</span>
                    @if($pendingCertificatesCount > 0)
                        <span class="px-2 py-0.5 rounded-full text-xs font-black bg-white text-blue-700">{{ $pendingCertificatesCount }}</span>
                    @endif
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            {{-- 1. KARTU STATISTIK UTAMA (SENIOR-FRIENDLY: TEKS BESAR, KONTRAS TINGGI, MUDAH DIBACA) --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                <!-- Card 1: Pengajuan Menunggu ACC -->
                <div class="bg-purple-50/70 border-2 border-purple-200 rounded-3xl p-6 shadow-xs relative overflow-hidden">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-black uppercase tracking-wider text-purple-700">Perlu Persetujuan (ACC)</span>
                        <div class="w-10 h-10 rounded-2xl bg-purple-600 text-white flex items-center justify-center font-bold">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="text-4xl font-black text-purple-950">{{ $pendingApplicationsCount }}</div>
                        <p class="text-xs font-semibold text-purple-800 mt-1">Pengajuan magang menunggu ACC atasan</p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-purple-200/80">
                        <a href="{{ route('supervisor.applications.index') }}" class="text-xs font-bold text-purple-700 hover:text-purple-900 inline-flex items-center gap-1">
                            Buka Kotak Masuk ACC &rarr;
                        </a>
                    </div>
                </div>

                <!-- Card 2: Pengesahan Sertifikat -->
                <div class="bg-blue-50/70 border-2 border-blue-200 rounded-3xl p-6 shadow-xs relative overflow-hidden">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-black uppercase tracking-wider text-blue-700">Sertifikat Menunggu ACC</span>
                        <div class="w-10 h-10 rounded-2xl bg-blue-600 text-white flex items-center justify-center font-bold">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/></svg>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="text-4xl font-black text-blue-950">{{ $pendingCertificatesCount }}</div>
                        <p class="text-xs font-semibold text-blue-800 mt-1">Sertifikat selesai magang perlu disahkan</p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-blue-200/80">
                        <a href="{{ route('supervisor.certificates.index') }}" class="text-xs font-bold text-blue-700 hover:text-blue-900 inline-flex items-center gap-1">
                            Buka Pengesahan Sertifikat &rarr;
                        </a>
                    </div>
                </div>

                <!-- Card 3: Mahasiswa Aktif -->
                <div class="bg-emerald-50/70 border-2 border-emerald-200 rounded-3xl p-6 shadow-xs relative overflow-hidden">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-black uppercase tracking-wider text-emerald-700">Mahasiswa Aktif</span>
                        <div class="w-10 h-10 rounded-2xl bg-emerald-600 text-white flex items-center justify-center font-bold">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="text-4xl font-black text-emerald-950">{{ $totalActiveInterns }}</div>
                        <p class="text-xs font-semibold text-emerald-800 mt-1">Sedang bertugas di 4 Bidang Dispusip</p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-emerald-200/80">
                        <span class="text-xs text-emerald-700 font-semibold">{{ $totalMentors }} Mentor Tim Kerja Pembimbing</span>
                    </div>
                </div>

                <!-- Card 4: Mahasiswa Lulus -->
                <div class="bg-amber-50/70 border-2 border-amber-200 rounded-3xl p-6 shadow-xs relative overflow-hidden">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-black uppercase tracking-wider text-amber-700">Alumni Selesai Magang</span>
                        <div class="w-10 h-10 rounded-2xl bg-amber-600 text-white flex items-center justify-center font-bold">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="text-4xl font-black text-amber-950">{{ $totalCompletedInterns }}</div>
                        <p class="text-xs font-semibold text-amber-800 mt-1">Total mahasiswa telah tuntas magang</p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-amber-200/80">
                        <span class="text-xs text-amber-700 font-semibold">Tercatat di arsip Dispusip</span>
                    </div>
                </div>
            </div>

            {{-- 2. MATRIKS 4 BIDANG RESMI DISPUSIP (REKAPITULASI KEPEGAWAIAN & BEBAN BIMBINGAN) --}}
            <div class="bg-white rounded-3xl border-2 border-slate-200 shadow-sm overflow-hidden">
                <div class="p-6 bg-slate-50 border-b border-slate-200 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <h2 class="text-lg sm:text-xl font-black text-slate-900">
                            Matriks 4 Bidang Resmi Dinas Perpustakaan dan Kearsipan
                        </h2>
                        <p class="text-xs sm:text-sm text-slate-600 mt-0.5">
                            Distribusi kuota penempatan, kepala bidang, anggota tim kerja pembimbing (mentor), serta status peserta magang.
                        </p>
                    </div>
                    <div class="flex items-center gap-2 text-xs font-bold text-slate-600 bg-white px-3 py-1.5 rounded-xl border border-slate-200">
                        <span>Status Sistem:</span>
                        <span class="text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200">Dispusip Aktif</span>
                    </div>
                </div>

                {{-- Tabel Matriks Bidang (Desktop & Tablet) --}}
                <div class="hidden lg:block overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-200 bg-slate-100/60 text-slate-700 text-xs font-bold uppercase tracking-wider">
                                <th class="py-4 px-6">Nama Bidang & Kepala</th>
                                <th class="py-4 px-6">Tim Kerja (Mentor & Beban Bimbingan)</th>
                                <th class="py-4 px-4 text-center">Kuota Total</th>
                                <th class="py-4 px-4 text-center">Mhs Aktif</th>
                                <th class="py-4 px-4 text-center">Sisa Kuota</th>
                                <th class="py-4 px-4 text-center">Menunggu ACC</th>
                                <th class="py-4 px-4 text-center">Mhs Lulus</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 text-sm">
                            @forelse($units as $unit)
                                @php
                                    $unitActiveCount = $unit->applications->whereIn('status', ['accepted', 'active'])->count();
                                    $unitWaitingCount = $unit->applications->where('status', \App\Enums\ApplicationStatus::WAITING_APPROVAL->value)->count();
                                    $unitCompletedCount = $unit->applications->where('status', \App\Enums\ApplicationStatus::COMPLETED->value)->count();
                                    $remainingQuota = max(0, $unit->quota - $unitActiveCount);
                                @endphp
                                <tr class="hover:bg-slate-50/80 transition">
                                    {{-- Nama Bidang & Kepala Bidang --}}
                                    <td class="py-5 px-6">
                                        <div class="font-black text-slate-900 text-base">{{ $unit->name }}</div>
                                        <div class="mt-1 flex items-center gap-1.5 text-xs text-slate-600">
                                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                            <span class="font-medium text-slate-500">Kepala Bidang:</span>
                                            <span class="font-bold text-slate-800">{{ $unit->headUser?->name ?? 'Belum Ditugaskan' }}</span>
                                        </div>
                                    </td>

                                    {{-- Tim Kerja (Mentors) --}}
                                    <td class="py-5 px-6">
                                        @if($unit->mentors && $unit->mentors->count() > 0)
                                            <div class="space-y-1.5">
                                                @foreach($unit->mentors as $mentor)
                                                    <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-lg bg-slate-100 border border-slate-200 text-xs mr-1 mb-1">
                                                        <span class="font-bold text-slate-800">{{ $mentor->name }}</span>
                                                        <span class="px-1.5 py-0.5 rounded-md text-[10px] font-black bg-blue-100 text-blue-800 border border-blue-200" title="Beban bimbingan aktif">
                                                            {{ $mentor->active_students_count ?? 0 }} mhs
                                                        </span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-xs text-slate-400 italic">Belum ada mentor yang ditugaskan</span>
                                        @endif
                                    </td>

                                    {{-- Kuota Total --}}
                                    <td class="py-5 px-4 text-center font-bold text-slate-700">
                                        <span class="px-2.5 py-1 rounded-lg bg-slate-100 border border-slate-200">{{ $unit->quota }}</span>
                                    </td>

                                    {{-- Mhs Aktif --}}
                                    <td class="py-5 px-4 text-center">
                                        <span class="px-2.5 py-1 rounded-lg font-black bg-emerald-50 text-emerald-800 border border-emerald-200">
                                            {{ $unitActiveCount }}
                                        </span>
                                    </td>

                                    {{-- Sisa Kuota --}}
                                    <td class="py-5 px-4 text-center">
                                        <span class="px-2.5 py-1 rounded-lg font-black {{ $remainingQuota > 0 ? 'bg-blue-50 text-blue-800 border border-blue-200' : 'bg-rose-50 text-rose-800 border border-rose-200' }}">
                                            {{ $remainingQuota }}
                                        </span>
                                    </td>

                                    {{-- Menunggu ACC --}}
                                    <td class="py-5 px-4 text-center">
                                        @if($unitWaitingCount > 0)
                                            <a href="{{ route('supervisor.applications.index', ['unit_id' => $unit->id, 'status' => 'waiting_approval']) }}"
                                               class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg font-black bg-purple-100 hover:bg-purple-200 text-purple-900 border border-purple-300 transition">
                                                <span>{{ $unitWaitingCount }}</span>
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                            </a>
                                        @else
                                            <span class="text-xs font-semibold text-slate-400">0</span>
                                        @endif
                                    </td>

                                    {{-- Mahasiswa Lulus --}}
                                    <td class="py-5 px-4 text-center font-semibold text-slate-600">
                                        {{ $unitCompletedCount }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-8 text-center text-slate-400 font-medium">
                                        Belum ada data bidang resmi Dispusip.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Tampilan Mobile / Tablet untuk 4 Bidang Dispusip --}}
                <div class="lg:hidden p-4 space-y-4">
                    @foreach($units as $unit)
                        @php
                            $unitActiveCount = $unit->applications->whereIn('status', ['accepted', 'active'])->count();
                            $unitWaitingCount = $unit->applications->where('status', \App\Enums\ApplicationStatus::WAITING_APPROVAL->value)->count();
                            $unitCompletedCount = $unit->applications->where('status', \App\Enums\ApplicationStatus::COMPLETED->value)->count();
                            $remainingQuota = max(0, $unit->quota - $unitActiveCount);
                        @endphp
                        <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200 space-y-3">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h3 class="font-black text-slate-900 text-base">{{ $unit->name }}</h3>
                                    <p class="text-xs text-slate-600 mt-0.5">Kepala Bidang: <strong class="text-slate-800">{{ $unit->headUser?->name ?? 'Belum Ditugaskan' }}</strong></p>
                                </div>
                                <span class="px-2.5 py-1 rounded-lg text-xs font-bold {{ $remainingQuota > 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                    Sisa: {{ $remainingQuota }}
                                </span>
                            </div>

                            <div class="grid grid-cols-4 gap-2 text-center text-xs py-2 border-y border-slate-200">
                                <div class="bg-white p-2 rounded-xl border border-slate-200">
                                    <div class="text-[10px] text-slate-500 font-bold uppercase">Kuota</div>
                                    <div class="font-black text-slate-800 text-sm">{{ $unit->quota }}</div>
                                </div>
                                <div class="bg-white p-2 rounded-xl border border-slate-200">
                                    <div class="text-[10px] text-slate-500 font-bold uppercase">Mhs Aktif</div>
                                    <div class="font-black text-emerald-700 text-sm">{{ $unitActiveCount }}</div>
                                </div>
                                <div class="bg-white p-2 rounded-xl border border-slate-200">
                                    <div class="text-[10px] text-slate-500 font-bold uppercase">ACC</div>
                                    <div class="font-black text-purple-700 text-sm">{{ $unitWaitingCount }}</div>
                                </div>
                                <div class="bg-white p-2 rounded-xl border border-slate-200">
                                    <div class="text-[10px] text-slate-500 font-bold uppercase">Mhs Lulus</div>
                                    <div class="font-black text-blue-700 text-sm">{{ $unitCompletedCount }}</div>
                                </div>
                            </div>

                            <div>
                                <div class="text-xs font-bold text-slate-700 mb-1">Anggota Tim Kerja (Mentor):</div>
                                @if($unit->mentors && $unit->mentors->count() > 0)
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach($unit->mentors as $mentor)
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white border border-slate-200 text-xs">
                                                <span class="font-semibold text-slate-800">{{ $mentor->name }}</span>
                                                <span class="px-1.5 py-0.5 rounded text-[10px] font-black bg-blue-100 text-blue-800">
                                                    {{ $mentor->active_students_count ?? 0 }} mhs
                                                </span>
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-xs text-slate-400 italic">Belum ada mentor ditugaskan.</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- 3. PANDUAN PIMPINAN (SENIOR-FRIENDLY HELP PANEL) --}}
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-3xl p-6 sm:p-8 border border-blue-200 text-slate-800">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-blue-600 text-white flex items-center justify-center shrink-0 shadow-md">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div class="space-y-2">
                        <h3 class="text-base sm:text-lg font-black text-blue-950">
                            Pedoman Kerja Operasional Atasan (Double ACC)
                        </h3>
                        <p class="text-xs sm:text-sm text-slate-700 leading-relaxed">
                            Sebagai Pimpinan Tim Kerja Umum & Kepegawaian Dispusip, Anda bertugas mengesahkan dua tahapan penting:
                        </p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
                            <div class="p-4 rounded-2xl bg-white border border-blue-200">
                                <h4 class="font-bold text-sm text-slate-900 flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-purple-600"></span>
                                    1. Penerbitan Surat Penerimaan Magang
                                </h4>
                                <p class="text-xs text-slate-600 mt-1">
                                    Admin Dinas telah memverifikasi berkas dan memplot bidang serta mentor pembimbing. Tugas Anda memeriksa kesesuaian formasi lalu menekan <strong>Setujui & Terbitkan Surat (ACC)</strong>.
                                </p>
                            </div>
                            <div class="p-4 rounded-2xl bg-white border border-blue-200">
                                <h4 class="font-bold text-sm text-slate-900 flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                                    2. Pengesahan Sertifikat Kelulusan
                                </h4>
                                <p class="text-xs text-slate-600 mt-1">
                                    Mahasiswa yang telah menuntaskan magang dan dievaluasi nilainya oleh mentor tim kerja memerlukan persetujuan atasan sebelum sertifikat resmi dapat diunduh mahasiswa.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
