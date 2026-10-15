<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

        {{-- 1. HERO BANNER DINAMIS & SEIMBANG SESUAI ROLE (SUPER ADMIN VS ADMIN DINAS) --}}
        <div class="rounded-3xl p-6 sm:p-8 lg:p-10 text-white shadow-xl relative overflow-hidden"
            style="background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 50%, #0369a1 100%) !important; color: #ffffff !important;">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-center relative z-10">

                {{-- Kolom Kiri: Judul & Deskripsi Tata Kelola (7 Kolom) --}}
                <div class="lg:col-span-7 space-y-3">
                    @if($isSuperAdmin)
                        <span
                            class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full text-xs font-bold tracking-wider"
                            style="background-color: rgba(251, 191, 36, 0.2) !important; color: #fde047 !important; border: 1px solid rgba(251, 191, 36, 0.4) !important;">
                            Super Admin Dispusip &bull; Administrator Utama
                        </span>
                        <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight leading-tight"
                            style="color: #ffffff !important;">
                            Pusat Kendali & Tata Kelola Dispusip Surabaya
                        </h1>
                        <p class="text-xs sm:text-sm leading-relaxed" style="color: #dbeafe !important;">
                            Pantau ekosistem magang Dinas Perpustakaan dan Kearsipan Surabaya secara terpusat: alokasi kuota bidang, penugasan tim kerja pembimbing (mentor), integrasi kemitraan perguruan tinggi, supervisi penilaian multi-role, dan pengesahan sertifikat.
                        </p>
                    @else
                        <span
                            class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider"
                            style="background-color: rgba(56, 189, 248, 0.2) !important; color: #7dd3fc !important; border: 1px solid rgba(56, 189, 248, 0.4) !important;">
                            PORTAL TATA KELOLA DINAS
                        </span>
                        <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight leading-tight"
                            style="color: #ffffff !important;">
                            {{ $currentAgency->agency_name ?? 'Panel Pengelola Instansi Dinas' }}
                        </h1>
                        <p class="text-xs sm:text-sm leading-relaxed" style="color: #dbeafe !important;">
                            Kelola verifikasi berkas pendaftaran magang dinas, alokasi kuota divisi, penugasan mentor
                            lapangan, monitoring logbook, dan penerbitan sertifikat kelulusan resmi.
                        </p>
                    @endif
                </div>

                {{-- Kolom Kanan: 4 Kartu Aksi Cepat (5 Kolom - Mengisi Seluruh Sisi Kanan Secara Seimbang & Mewah) --}}
                <div class="lg:col-span-5 grid grid-cols-2 gap-3.5">
                    @if($isSuperAdmin)
                        <a href="{{ route('admin.agencies.index') }}"
                            class="p-4 rounded-2xl transition-all duration-200 group shadow-sm hover:scale-[1.03] hover:shadow-md cursor-pointer flex flex-col justify-between"
                            style="background-color: rgba(255, 255, 255, 0.12) !important; border: 1px solid rgba(255, 255, 255, 0.22) !important; color: #ffffff !important;">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full"
                                    style="background-color: rgba(255, 255, 255, 0.18); color: #ffffff;">{{ $stats['total_agencies'] ?? 0 }}
                                    Dinas</span>
                            </div>
                            <div>
                                <div class="text-xs sm:text-sm font-bold" style="color: #ffffff !important;">Instansi</div>
                                <div class="text-[11px]" style="color: #bfdbfe !important;">Kelola Kuota Unit</div>
                            </div>
                        </a>

                        <a href="{{ route('admin.universities.index') }}"
                            class="p-4 rounded-2xl transition-all duration-200 group shadow-sm hover:scale-[1.03] hover:shadow-md cursor-pointer flex flex-col justify-between"
                            style="background-color: rgba(255, 255, 255, 0.12) !important; border: 1px solid rgba(255, 255, 255, 0.22) !important; color: #ffffff !important;">
                            <div class="flex items-center justify-start mb-2">
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full"
                                    style="background-color: rgba(255, 255, 255, 0.18); color: #ffffff;">{{ $stats['total_universities'] ?? 0 }}
                                    Kampus</span>
                            </div>
                            <div>
                                <div class="text-xs sm:text-sm font-bold" style="color: #ffffff !important;">Kampus</div>
                                <div class="text-[11px]" style="color: #bfdbfe !important;">Mitra MBKM</div>
                            </div>
                        </a>

                        <a href="{{ route('admin.users.index') }}"
                            class="p-4 rounded-2xl transition-all duration-200 group shadow-sm hover:scale-[1.03] hover:shadow-md cursor-pointer flex flex-col justify-between"
                            style="background-color: rgba(255, 255, 255, 0.12) !important; border: 1px solid rgba(255, 255, 255, 0.22) !important; color: #ffffff !important;">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full"
                                    style="background-color: rgba(255, 255, 255, 0.18); color: #ffffff;">{{ $stats['total_users'] ?? 0 }}
                                    User</span>
                            </div>
                            <div>
                                <div class="text-xs sm:text-sm font-bold" style="color: #ffffff !important;">Pengguna</div>
                                <div class="text-[11px]" style="color: #bfdbfe !important;">Semua Akun & Role</div>
                            </div>
                        </a>

                        <a href="{{ route('admin.audit_logs.index') }}"
                            class="p-4 rounded-2xl transition-all duration-200 group shadow-sm hover:scale-[1.03] hover:shadow-md cursor-pointer flex flex-col justify-between"
                            style="background-color: rgba(255, 255, 255, 0.12) !important; border: 1px solid rgba(255, 255, 255, 0.22) !important; color: #ffffff !important;">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full"
                                    style="background-color: rgba(255, 255, 255, 0.18); color: #ffffff;">Aktivitas</span>
                            </div>
                            <div>
                                <div class="text-xs sm:text-sm font-bold" style="color: #ffffff !important;">Log Audit</div>
                                <div class="text-[11px]" style="color: #bfdbfe !important;">Rekam Jejak Sistem</div>
                            </div>
                        </a>
                    @else
                        <a href="{{ route('admin.applications.index') }}"
                            class="p-4 rounded-2xl transition-all duration-200 group shadow-sm hover:scale-[1.03] hover:shadow-md cursor-pointer flex flex-col justify-between"
                            style="background-color: rgba(255, 255, 255, 0.12) !important; border: 1px solid rgba(255, 255, 255, 0.22) !important; color: #ffffff !important;">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full"
                                    style="background-color: rgba(255, 255, 255, 0.18); color: #ffffff;">{{ $stats['total_pending'] ?? 0 }}
                                    Antrean</span>
                            </div>
                            <div>
                                <div class="text-xs sm:text-sm font-bold" style="color: #ffffff !important;">Verifikasi
                                </div>
                                <div class="text-[11px]" style="color: #bfdbfe !important;">Berkas Pendaftar</div>
                            </div>
                        </a>

                        <a href="{{ route('admin.units.index') }}"
                            class="p-4 rounded-2xl transition-all duration-200 group shadow-sm hover:scale-[1.03] hover:shadow-md cursor-pointer flex flex-col justify-between"
                            style="background-color: rgba(255, 255, 255, 0.12) !important; border: 1px solid rgba(255, 255, 255, 0.22) !important; color: #ffffff !important;">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full"
                                    style="background-color: rgba(255, 255, 255, 0.18); color: #ffffff;">{{ $stats['total_units'] ?? 0 }}
                                    Unit</span>
                            </div>
                            <div>
                                <div class="text-xs sm:text-sm font-bold" style="color: #ffffff !important;">Divisi & Kuota
                                </div>
                                <div class="text-[11px]" style="color: #bfdbfe !important;">Alokasi Slot Magang</div>
                            </div>
                        </a>

                        <a href="{{ route('admin.mentors.index') }}"
                            class="p-4 rounded-2xl transition-all duration-200 group shadow-sm hover:scale-[1.03] hover:shadow-md cursor-pointer flex flex-col justify-between"
                            style="background-color: rgba(255, 255, 255, 0.12) !important; border: 1px solid rgba(255, 255, 255, 0.22) !important; color: #ffffff !important;">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full"
                                    style="background-color: rgba(255, 255, 255, 0.18); color: #ffffff;">{{ $stats['total_mentors'] ?? 0 }}
                                    Mentor</span>
                            </div>
                            <div>
                                <div class="text-xs sm:text-sm font-bold" style="color: #ffffff !important;">Mentor Dinas
                                </div>
                                <div class="text-[11px]" style="color: #bfdbfe !important;">Pembimbing Lapangan</div>
                            </div>
                        </a>

                        <a href="{{ route('admin.certificates.index') }}"
                            class="p-4 rounded-2xl transition-all duration-200 group shadow-sm hover:scale-[1.03] hover:shadow-md cursor-pointer flex flex-col justify-between"
                            style="background-color: rgba(255, 255, 255, 0.12) !important; border: 1px solid rgba(255, 255, 255, 0.22) !important; color: #ffffff !important;">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full"
                                    style="background-color: rgba(255, 255, 255, 0.18); color: #ffffff;">{{ $stats['total_completed'] ?? 0 }}
                                    Lulus</span>
                            </div>
                            <div>
                                <div class="text-xs sm:text-sm font-bold" style="color: #ffffff !important;">Sertifikat
                                </div>
                                <div class="text-[11px]" style="color: #bfdbfe !important;">Penerbitan Resmi</div>
                            </div>
                        </a>
                    @endif
                </div>

            </div>
        </div>

        {{-- Kesehatan sistem: scheduler & queue worker (Super Admin) --}}
        @if($isSuperAdmin && !empty($systemIssues))
            <div class="bg-white border-l-4 border-rose-500 p-5 rounded-2xl shadow-xs">
                <h4 class="font-bold text-slate-900 text-sm">Proses latar belakang server perlu diperiksa</h4>
                <ul class="mt-1.5 space-y-1 text-xs text-slate-600 list-disc list-inside">
                    @foreach($systemIssues as $issue)
                        <li>{{ $issue }}</li>
                    @endforeach
                </ul>
                <p class="mt-2 text-[11px] text-slate-400">Panduan pemasangan: <code>docs/DEPLOYMENT.md</code> &bull; cek cepat: <code>php artisan app:health</code></p>
            </div>
        @endif

        {{-- Alert Notifikasi Kampus Baru Tanpa Akun Portal (Super Admin) --}}
        @if($isSuperAdmin && isset($pendingUniversities) && $pendingUniversities->count() > 0)
            <div
                class="bg-gradient-to-r from-amber-500/10 via-amber-500/5 to-transparent border-l-4 border-amber-500 p-5 rounded-2xl bg-white shadow-xs flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                <div class="flex items-start gap-3">
                    <div class="p-2 bg-amber-100 rounded-xl text-amber-700 shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-900 text-sm">
                            Pemberitahuan: Terdapat {{ $pendingUniversities->count() }} Perguruan Tinggi Baru yang Terdaftar
                            Otomatis
                        </h4>
                        <p class="text-xs text-slate-600 mt-0.5">
                            Mahasiswa mendaftar dari kampus baru:
                            <strong>{{ $pendingUniversities->pluck('name')->implode(', ') }}</strong>. Silakan lengkapi
                            profil kampus dan buatkan akun login PIC/Admin Kampus.
                        </p>
                    </div>
                </div>
                <a href="{{ route('admin.universities.index') }}"
                    class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-xl shadow-xs transition shrink-0">
                    Kelola Kampus & Buat Akun
                </a>
            </div>
        @endif

        {{-- Alert Notifikasi Instansi Dinas Baru Tanpa Akun Admin Dinas (Super Admin) --}}
        @if($isSuperAdmin && isset($pendingAgencies) && $pendingAgencies->count() > 0)
            <div
                class="bg-gradient-to-r from-amber-500/10 via-amber-500/5 to-transparent border-l-4 border-amber-500 p-5 rounded-2xl bg-white shadow-xs flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                <div class="flex items-start gap-3">
                    <div class="p-2 bg-amber-100 rounded-xl text-amber-700 shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-900 text-sm">
                            Pemberitahuan: Terdapat {{ $pendingAgencies->count() }} Instansi Dinas Baru yang Belum Memiliki Akun Admin Dinas
                        </h4>
                        <p class="text-xs text-slate-600 mt-0.5">
                            Instansi dinas:
                            <strong>{{ $pendingAgencies->pluck('agency_name')->implode(', ') }}</strong>. Silakan buatkan akun agar perwakilan dinas dapat login dan mengelola unit kerja & kuota.
                        </p>
                    </div>
                </div>
                <a href="{{ route('admin.agencies.index') }}"
                    class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-xl shadow-xs transition shrink-0">
                    Kelola Instansi & Buat Akun 
                </a>
            </div>
        @endif

        {{-- Fase 1: Kampus baru (input mandiri mahasiswa) menunggu verifikasi --}}
        @if($isSuperAdmin && isset($unverifiedUniversities) && $unverifiedUniversities->count() > 0)
            <div class="bg-white border-l-4 border-yellow-500 p-5 rounded-2xl shadow-xs flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                <div>
                    <h4 class="font-bold text-slate-900 text-sm">
                        {{ $unverifiedUniversities->count() }} Perguruan Tinggi Menunggu Verifikasi
                    </h4>
                    <p class="text-xs text-slate-600 mt-0.5">
                        Didaftarkan mandiri oleh mahasiswa melalui opsi "Perguruan Tinggi Lainnya":
                        <strong>{{ $unverifiedUniversities->pluck('name')->implode(', ') }}</strong>.
                    </p>
                </div>
                <a href="{{ route('admin.universities.index', ['verification' => 'pending']) }}"
                    class="px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white text-xs font-bold rounded-xl shadow-xs transition shrink-0">
                    Tinjau &amp; Verifikasi
                </a>
            </div>
        @endif


        {{-- 2. ENAM KARTU METRIK EKSEKUTIF (TERISOLASI OTOMATIS BERDASARKAN DINAS) --}}
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 items-stretch">
            {{-- Pendaftar --}}
            <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition h-full flex flex-col justify-between"
                style="background-color: #ffffff !important; border: 1px solid #f1f5f9 !important;">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Pendaftar</span>
                    </div>
                    <div class="text-2xl font-black text-slate-800">{{ $stats['total_students'] ?? 0 }}</div>
                </div>
                <div class="text-[10px] text-slate-500 mt-2 leading-tight truncate">
                    {{ $isSuperAdmin ? 'Mahasiswa kota' : 'Pendaftar dinas' }}
                </div>
            </div>

            {{-- Verifikasi --}}
            <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition h-full flex flex-col justify-between"
                style="background-color: #ffffff !important; border: 1px solid #f1f5f9 !important;">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Verifikasi</span>
                    </div>
                    <div class="text-2xl font-black text-slate-800">{{ $stats['total_pending'] ?? 0 }}</div>
                </div>
                <div class="text-[10px] text-slate-500 mt-2 leading-tight truncate">PENDING dan VERIFIED</div>
            </div>

            {{-- Diterima --}}
            <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition h-full flex flex-col justify-between"
                style="background-color: #ffffff !important; border: 1px solid #f1f5f9 !important;">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">{{ \App\Enums\ApplicationStatus::ACCEPTED->label() }}</span>
                    </div>
                    <div class="text-2xl font-black text-slate-800">{{ $stats['total_accepted'] ?? 0 }}</div>
                </div>
                <div class="text-[10px] text-slate-500 mt-2 leading-tight truncate" title="Diterima, Belum Mulai">Diterima, Belum Mulai</div>
            </div>

            {{-- Aktif --}}
            <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition h-full flex flex-col justify-between"
                style="background-color: #ffffff !important; border: 1px solid #f1f5f9 !important;">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">{{ \App\Enums\ApplicationStatus::ACTIVE->label() }}</span>
                    </div>
                    <div class="text-2xl font-black text-slate-800">{{ $stats['total_active'] ?? 0 }}</div>
                </div>
                <div class="text-[10px] text-slate-500 mt-2 leading-tight truncate">{{ \App\Enums\ApplicationStatus::ACTIVE->description() }}</div>
            </div>

            {{-- Lulus --}}
            <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition h-full flex flex-col justify-between"
                style="background-color: #ffffff !important; border: 1px solid #f1f5f9 !important;">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">{{ \App\Enums\ApplicationStatus::COMPLETED->label() }}</span>
                    </div>
                    <div class="text-2xl font-black text-slate-800">{{ $stats['total_completed'] ?? 0 }}</div>
                </div>
                <div class="text-[10px] text-slate-500 mt-2 leading-tight truncate">{{ \App\Enums\ApplicationStatus::COMPLETED->description() }}</div>
            </div>

            {{-- Sisa Kuota --}}
            <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition h-full flex flex-col justify-between"
                style="background-color: #ffffff !important; border: 1px solid #f1f5f9 !important;">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Sisa Kuota</span>
                    </div>
                    <div class="text-2xl font-black text-slate-800">{{ $stats['total_quota_available'] ?? 0 }}</div>
                </div>
                <div class="text-[10px] text-slate-500 mt-2 leading-tight truncate">
                    {{ $isSuperAdmin ? 'Slot kuota kota' : 'Slot kuota dinas' }}
                </div>
            </div>
        </div>

        {{-- MATRIKS 4 BIDANG RESMI DISPUSIP (MONITORING ALOKASI KUOTA & STATUS MAHASISWA) --}}
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="p-5 sm:p-6 bg-slate-50/80 border-b border-slate-200">
                <h2 class="text-base sm:text-lg font-black text-slate-900">
                    Bidang Devisi Dinas Perpustakaan dan Kearsipan
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Rekapitulasi alokasi kuota dan status peserta magang per bidang / devisi. Klik nama bidang untuk melihat detail dashboard bidang.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-100/50 text-slate-600 font-bold uppercase tracking-wider text-[11px]">
                            <th class="py-3.5 px-6">Nama Bidang / Devisi</th>
                            <th class="py-3.5 px-4 text-center">Kuota Total</th>
                            <th class="py-3.5 px-4 text-center">Mhs Aktif</th>
                            <th class="py-3.5 px-4 text-center">Sisa Kuota</th>
                            <th class="py-3.5 px-4 text-center">Menunggu ACC</th>
                            <th class="py-3.5 px-4 text-center">Mhs Lulus</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($dispusipUnits ?? [] as $dUnit)
                            @php
                                $uActive = $dUnit->applications->whereIn('status', ['accepted', 'active'])->count();
                                $uWaiting = $dUnit->applications->where('status', \App\Enums\ApplicationStatus::WAITING_APPROVAL->value)->count();
                                $uCompleted = $dUnit->applications->where('status', \App\Enums\ApplicationStatus::COMPLETED->value)->count();
                                $uRemaining = max(0, $dUnit->quota - $uActive);
                            @endphp
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-4 px-6">
                                    <a href="{{ route('admin.units.show', $dUnit->id) }}"
                                       class="font-bold text-slate-900 text-sm hover:text-blue-600 transition inline-flex items-center gap-1.5 group"
                                       title="Lihat Dashboard Bidang {{ $dUnit->name }}">
                                        <span>{{ $dUnit->name }}</span>
                                        <svg class="w-3.5 h-3.5 text-slate-400 group-hover:text-blue-600 group-hover:translate-x-0.5 transition shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </a>
                                </td>
                                <td class="py-4 px-4 text-center font-bold text-slate-700">
                                    <span class="px-2.5 py-1 rounded-lg bg-slate-100">{{ $dUnit->quota }}</span>
                                </td>
                                <td class="py-4 px-4 text-center">
                                    <span class="px-2.5 py-1 rounded-lg font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        {{ $uActive }}
                                    </span>
                                </td>
                                <td class="py-4 px-4 text-center font-bold {{ $uRemaining > 0 ? 'text-blue-600' : 'text-rose-600' }}">
                                    {{ $uRemaining }}
                                </td>
                                <td class="py-4 px-4 text-center">
                                    @if($uWaiting > 0)
                                        <span class="px-2.5 py-1 rounded-lg font-bold bg-purple-50 text-purple-700 border border-purple-200">
                                            {{ $uWaiting }}
                                        </span>
                                    @else
                                        <span class="text-slate-400">0</span>
                                    @endif
                                </td>
                                <td class="py-4 px-4 text-center font-bold text-slate-700">
                                    {{ $uCompleted }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-400">Data 4 Bidang Dispusip belum dimuat.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- 3. DISTRIBUSI SEBARAN SESUAI SCOPE ROLE --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- Distribusi Penempatan: Unit Kerja --}}
            <div class="bg-white p-5 sm:p-6 rounded-3xl border border-slate-100 shadow-sm space-y-4"
                style="background-color: #ffffff !important; border: 1px solid #f1f5f9 !important;"
                x-data="{ expanded: false }">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 sm:gap-3 pb-3 border-b border-slate-100">
                    <div class="min-w-0 flex-1">
                        <h3 class="text-sm font-bold text-slate-800 leading-snug">Distribusi Penempatan Unit Kerja</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Sebaran mahasiswa magang di unit/bidang {{ $currentAgency->agency_name ?? 'Dispusip Surabaya' }}</p>
                    </div>
                    <a href="{{ route('admin.units.index') }}"
                        class="self-start sm:self-auto shrink-0 whitespace-nowrap inline-flex items-center gap-1.5 text-xs font-bold text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-xl transition border border-blue-200 shadow-2xs">
                        <span>Kelola Unit</span>
                    </a>
                </div>

                @php
                    // Urutkan dari jumlah mahasiswa terbanyak; tampilkan 10 teratas, sisanya bisa dibuka.
                    $distList = collect($unitStats ?? [])
                        ->sortByDesc(fn ($row) => data_get($row, 'count', 0))->values()->all();
                @endphp
                <div class="space-y-4 pt-1">
                    @forelse($distList as $item)
                        @php
                            $itemName = is_array($item) ? $item['name'] : ($item->name ?? '-');
                            $itemCount = is_array($item) ? $item['count'] : ($item->count ?? 0);
                            $itemPercentage = is_array($item) ? $item['percentage'] : ($item->percentage ?? 0);
                            $itemQuota = is_array($item) ? ($item['quota'] ?? 0) : ($item->quota ?? 0);
                        @endphp
                        <div @if($loop->index >= 10) x-show="expanded" x-cloak @endif>
                            <div class="flex items-center justify-between text-xs font-semibold mb-1.5 gap-2">
                                <a href="{{ isset($item['id']) ? route('admin.units.show', $item['id']) : '#' }}"
                                   class="text-slate-700 font-medium truncate flex-1 min-w-0 hover:text-blue-600 transition">
                                    {{ $itemName }}
                                </a>
                                <span class="text-blue-700 font-bold shrink-0">
                                    {{ $itemCount }} Mahasiswa
                                    @if($itemQuota > 0)
                                        <span class="text-slate-400 font-normal">/ Kuota {{ $itemQuota }}</span>
                                    @endif
                                    <span class="text-slate-400 font-normal">({{ $itemPercentage }}%)</span>
                                </span>
                            </div>
                            <div class="w-full rounded-full overflow-hidden mt-1.5"
                                style="background-color: #f1f5f9; height: 8px; border-radius: 9999px; overflow: hidden;">
                                <div class="h-2 rounded-full transition-all duration-500"
                                    style="background-color: #2563eb; height: 8px; border-radius: 9999px; width: {{ max((float) $itemPercentage, 2) }}%">
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-6 text-xs text-slate-400">Belum ada data penempatan unit kerja.</div>
                    @endforelse
                </div>
                @if(count($distList) > 10)
                    <div class="text-center">
                        <button type="button" @click="expanded = !expanded"
                            class="inline-flex items-center gap-1.5 text-xs font-bold text-blue-600 hover:text-blue-800 bg-slate-50 hover:bg-slate-100 px-4 py-2 rounded-xl transition border border-slate-200 cursor-pointer">
                            <span x-text="expanded ? 'Tutup' : 'Tampilkan Semua ({{ count($distList) }})'"></span>
                            <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                    </div>
                @endif
            </div>

            {{-- Distribusi Asal Perguruan Tinggi --}}
            <div class="bg-white p-5 sm:p-6 rounded-3xl border border-slate-100 shadow-sm space-y-4"
                style="background-color: #ffffff !important; border: 1px solid #f1f5f9 !important;"
                x-data="{ expanded: false }">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 sm:gap-3 pb-3 border-b border-slate-100">
                    <div class="min-w-0 flex-1">
                        <h3 class="text-sm font-bold text-slate-800 leading-snug">
                            {{ $isSuperAdmin ? 'Distribusi Asal Kampus Surabaya' : 'Distribusi Asal Kampus Mahasiswa Dinas' }}
                        </h3>
                        <p class="text-xs text-slate-400 mt-0.5">
                            {{ $isSuperAdmin ? 'Sebaran perguruan tinggi mitra resmi program magang' : 'Sebaran kampus pendaftar di ' . ($currentAgency->agency_name ?? 'dinas ini') }}
                        </p>
                    </div>
                    @if($isSuperAdmin)
                        <a href="{{ route('admin.universities.index') }}"
                            class="self-start sm:self-auto shrink-0 whitespace-nowrap inline-flex items-center gap-1.5 text-xs font-bold text-sky-700 hover:text-sky-900 bg-sky-50 hover:bg-sky-100 px-3 py-1.5 rounded-xl transition border border-sky-200 shadow-2xs">
                            <span>Kelola Kampus</span>
                        </a>
                    @endif
                </div>

                @php
                    $campusList = collect($universityStats ?? [])
                        ->sortByDesc(fn ($row) => data_get($row, 'count', 0))->values()->all();
                @endphp
                <div class="space-y-4 pt-1">
                    @forelse($campusList as $campus)
                        @php
                            $campName = is_array($campus) ? $campus['name'] : ($campus->name ?? '-');
                            $campCount = is_array($campus) ? $campus['count'] : ($campus->count ?? 0);
                            $campPercentage = is_array($campus) ? $campus['percentage'] : ($campus->percentage ?? 0);
                        @endphp
                        <div @if($loop->index >= 10) x-show="expanded" x-cloak @endif>
                            <div class="flex items-center justify-between text-xs font-semibold mb-1.5">
                                <span class="text-slate-700 font-medium truncate max-w-[240px]">{{ $campName }}</span>
                                <span class="text-sky-700 font-bold shrink-0">{{ $campCount }} Mahasiswa <span
                                        class="text-slate-400 font-normal">({{ $campPercentage }}%)</span></span>
                            </div>
                            <div class="w-full rounded-full overflow-hidden mt-1.5"
                                style="background-color: #f1f5f9; height: 8px; border-radius: 9999px; overflow: hidden;">
                                <div class="h-2 rounded-full transition-all duration-500"
                                    style="background-color: #0284c7; height: 8px; border-radius: 9999px; width: {{ max((float) $campPercentage, 2) }}%">
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-6 text-xs text-slate-400">Belum ada data distribusi kampus.</div>
                    @endforelse
                </div>
                @if(count($campusList) > 10)
                    <div class="text-center">
                        <button type="button" @click="expanded = !expanded"
                            class="inline-flex items-center gap-1.5 text-xs font-bold text-sky-700 hover:text-sky-900 bg-slate-50 hover:bg-slate-100 px-4 py-2 rounded-xl transition border border-slate-200 cursor-pointer">
                            <span x-text="expanded ? 'Tutup' : 'Tampilkan Semua ({{ count($campusList) }})'"></span>
                            <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                    </div>
                @endif
            </div>

        </div>

    </div>
</x-app-layout>