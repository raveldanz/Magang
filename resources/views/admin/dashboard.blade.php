<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

        {{-- 1. HERO BANNER DINAMIS & SEIMBANG SESUAI ROLE (SUPER ADMIN VS ADMIN DINAS) --}}
        <div class="rounded-3xl p-6 sm:p-8 lg:p-10 text-white shadow-xl relative overflow-hidden" 
             style="background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 50%, #0369a1 100%) !important; color: #ffffff !important;">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-center relative z-10">
                
                {{-- Kolom Kiri: Judul & Deskripsi Tata Kelola (7 Kolom) --}}
                <div class="lg:col-span-7 space-y-3">
                    @if($isSuperAdmin)
                        <span class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider" 
                              style="background-color: rgba(251, 191, 36, 0.2) !important; color: #fde047 !important; border: 1px solid rgba(251, 191, 36, 0.4) !important;">
                            <svg class="w-4 h-4 text-amber-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            <span>SUPER ADMIN GOVERNANCE HUB</span>
                        </span>
                        <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight leading-tight" style="color: #ffffff !important;">
                            Pusat Kendali & Tata Kelola Eksekutif
                        </h1>
                        <p class="text-xs sm:text-sm leading-relaxed" style="color: #dbeafe !important;">
                            Pantau ekosistem magang Pemkot Surabaya secara terpusat: kuota multi-instansi dinas, integrasi kemitraan perguruan tinggi, supervisi penilaian multi-role, dan jejak aktivitas audit sistem secara komprehensif.
                        </p>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider" 
                              style="background-color: rgba(56, 189, 248, 0.2) !important; color: #7dd3fc !important; border: 1px solid rgba(56, 189, 248, 0.4) !important;">
                            <svg class="w-4 h-4 text-sky-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            <span>PORTAL TATA KELOLA DINAS</span>
                        </span>
                        <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight leading-tight" style="color: #ffffff !important;">
                            {{ $currentAgency->agency_name ?? 'Panel Pengelola Instansi Dinas' }}
                        </h1>
                        <p class="text-xs sm:text-sm leading-relaxed" style="color: #dbeafe !important;">
                            Kelola verifikasi berkas pendaftaran magang dinas, alokasi kuota divisi, penugasan mentor lapangan, monitoring logbook, dan penerbitan sertifikat kelulusan resmi.
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
                                <span class="w-8 h-8 rounded-xl bg-white/10 flex items-center justify-center text-white group-hover:scale-110 transition-transform">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                </span>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full" style="background-color: rgba(255, 255, 255, 0.18); color: #ffffff;">{{ $stats['total_agencies'] ?? 0 }} Dinas</span>
                            </div>
                            <div>
                                <div class="text-xs sm:text-sm font-bold" style="color: #ffffff !important;">Instansi</div>
                                <div class="text-[11px]" style="color: #bfdbfe !important;">Kelola Kuota Unit</div>
                            </div>
                        </a>

                        <a href="{{ route('admin.universities.index') }}" 
                           class="p-4 rounded-2xl transition-all duration-200 group shadow-sm hover:scale-[1.03] hover:shadow-md cursor-pointer flex flex-col justify-between" 
                           style="background-color: rgba(255, 255, 255, 0.12) !important; border: 1px solid rgba(255, 255, 255, 0.22) !important; color: #ffffff !important;">
                            <div class="flex items-center justify-between mb-2">
                                <span class="w-8 h-8 rounded-xl bg-white/10 flex items-center justify-center text-white group-hover:scale-110 transition-transform">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
                                </span>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full" style="background-color: rgba(255, 255, 255, 0.18); color: #ffffff;">{{ $stats['total_universities'] ?? 0 }} Kampus</span>
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
                                <span class="w-8 h-8 rounded-xl bg-white/10 flex items-center justify-center text-white group-hover:scale-110 transition-transform">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                                </span>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full" style="background-color: rgba(255, 255, 255, 0.18); color: #ffffff;">{{ $stats['total_users'] ?? 0 }} User</span>
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
                                <span class="w-8 h-8 rounded-xl bg-white/10 flex items-center justify-center text-white group-hover:scale-110 transition-transform">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </span>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full" style="background-color: rgba(255, 255, 255, 0.18); color: #ffffff;">Aktivitas</span>
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
                                <span class="w-8 h-8 rounded-xl bg-white/10 flex items-center justify-center text-white group-hover:scale-110 transition-transform">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                                </span>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full" style="background-color: rgba(255, 255, 255, 0.18); color: #ffffff;">{{ $stats['total_pending'] ?? 0 }} Antrean</span>
                            </div>
                            <div>
                                <div class="text-xs sm:text-sm font-bold" style="color: #ffffff !important;">Verifikasi</div>
                                <div class="text-[11px]" style="color: #bfdbfe !important;">Berkas Pendaftar</div>
                            </div>
                        </a>

                        <a href="{{ route('admin.units.index') }}" 
                           class="p-4 rounded-2xl transition-all duration-200 group shadow-sm hover:scale-[1.03] hover:shadow-md cursor-pointer flex flex-col justify-between" 
                           style="background-color: rgba(255, 255, 255, 0.12) !important; border: 1px solid rgba(255, 255, 255, 0.22) !important; color: #ffffff !important;">
                            <div class="flex items-center justify-between mb-2">
                                <span class="w-8 h-8 rounded-xl bg-white/10 flex items-center justify-center text-white group-hover:scale-110 transition-transform">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                </span>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full" style="background-color: rgba(255, 255, 255, 0.18); color: #ffffff;">{{ $stats['total_units'] ?? 0 }} Unit</span>
                            </div>
                            <div>
                                <div class="text-xs sm:text-sm font-bold" style="color: #ffffff !important;">Divisi & Kuota</div>
                                <div class="text-[11px]" style="color: #bfdbfe !important;">Alokasi Slot Magang</div>
                            </div>
                        </a>

                        <a href="{{ route('admin.mentors.index') }}" 
                           class="p-4 rounded-2xl transition-all duration-200 group shadow-sm hover:scale-[1.03] hover:shadow-md cursor-pointer flex flex-col justify-between" 
                           style="background-color: rgba(255, 255, 255, 0.12) !important; border: 1px solid rgba(255, 255, 255, 0.22) !important; color: #ffffff !important;">
                            <div class="flex items-center justify-between mb-2">
                                <span class="w-8 h-8 rounded-xl bg-white/10 flex items-center justify-center text-white group-hover:scale-110 transition-transform">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                </span>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full" style="background-color: rgba(255, 255, 255, 0.18); color: #ffffff;">{{ $stats['total_mentors'] ?? 0 }} Mentor</span>
                            </div>
                            <div>
                                <div class="text-xs sm:text-sm font-bold" style="color: #ffffff !important;">Mentor Dinas</div>
                                <div class="text-[11px]" style="color: #bfdbfe !important;">Pembimbing Lapangan</div>
                            </div>
                        </a>

                        <a href="{{ route('admin.certificates.index') }}" 
                           class="p-4 rounded-2xl transition-all duration-200 group shadow-sm hover:scale-[1.03] hover:shadow-md cursor-pointer flex flex-col justify-between" 
                           style="background-color: rgba(255, 255, 255, 0.12) !important; border: 1px solid rgba(255, 255, 255, 0.22) !important; color: #ffffff !important;">
                            <div class="flex items-center justify-between mb-2">
                                <span class="w-8 h-8 rounded-xl bg-white/10 flex items-center justify-center text-white group-hover:scale-110 transition-transform">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138z"/></svg>
                                </span>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full" style="background-color: rgba(255, 255, 255, 0.18); color: #ffffff;">{{ $stats['total_completed'] ?? 0 }} Lulus</span>
                            </div>
                            <div>
                                <div class="text-xs sm:text-sm font-bold" style="color: #ffffff !important;">Sertifikat</div>
                                <div class="text-[11px]" style="color: #bfdbfe !important;">Penerbitan Resmi</div>
                            </div>
                        </a>
                    @endif
                </div>

            </div>
        </div>

        {{-- Alert Notifikasi Kampus Baru Tanpa Akun Portal (Super Admin) --}}
        @if($isSuperAdmin && isset($pendingUniversities) && $pendingUniversities->count() > 0)
            <div class="bg-gradient-to-r from-amber-500/10 via-amber-500/5 to-transparent border-l-4 border-amber-500 p-5 rounded-2xl bg-white shadow-xs flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                <div class="flex items-start gap-3">
                    <span class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    </span>
                    <div>
                        <h4 class="font-bold text-slate-900 text-sm">
                            Pemberitahuan: Terdapat {{ $pendingUniversities->count() }} Perguruan Tinggi Baru yang Terdaftar Otomatis
                        </h4>
                        <p class="text-xs text-slate-600 mt-0.5">
                            Mahasiswa mendaftar dari kampus baru: <strong>{{ $pendingUniversities->pluck('name')->implode(', ') }}</strong>. Silakan lengkapi profil kampus dan buatkan akun login PIC/Admin Kampus.
                        </p>
                    </div>
                </div>
                <a href="{{ route('admin.universities.index') }}" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-xl shadow-xs transition shrink-0">
                    Kelola Kampus & Buat Akun &rarr;
                </a>
            </div>
        @endif

        {{-- 2. ENAM KARTU METRIK EKSEKUTIF (TERISOLASI OTOMATIS BERDASARKAN DINAS) --}}
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            {{-- Pendaftar --}}
            <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition flex flex-col justify-between" style="background-color: #ffffff !important; border: 1px solid #f1f5f9 !important;">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Pendaftar</span>
                </div>
                <div class="text-2xl font-black text-slate-800">{{ $stats['total_students'] ?? 0 }}</div>
                <div class="text-[11px] text-slate-500 mt-1">{{ $isSuperAdmin ? 'Mahasiswa kota' : 'Pendaftar dinas' }}</div>
            </div>

            {{-- Verifikasi --}}
            <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition flex flex-col justify-between" style="background-color: #ffffff !important; border: 1px solid #f1f5f9 !important;">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold text-amber-500 uppercase tracking-wider">Verifikasi</span>
                    <span class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                </div>
                <div class="text-2xl font-black text-amber-600">{{ $stats['total_pending'] ?? 0 }}</div>
                <div class="text-[11px] text-slate-500 mt-1">Menunggu dinas</div>
            </div>

            {{-- Diterima --}}
            <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition flex flex-col justify-between" style="background-color: #ffffff !important; border: 1px solid #f1f5f9 !important;">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold text-blue-600 uppercase tracking-wider">Diterima</span>
                    <span class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    </span>
                </div>
                <div class="text-2xl font-black text-blue-600">{{ $stats['total_accepted'] ?? 0 }}</div>
                <div class="text-[11px] text-slate-500 mt-1">Persiapan magang</div>
            </div>

            {{-- Aktif --}}
            <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition flex flex-col justify-between" style="background-color: #ffffff !important; border: 1px solid #f1f5f9 !important;">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider">Aktif</span>
                    <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </span>
                </div>
                <div class="text-2xl font-black text-emerald-600">{{ $stats['total_active'] ?? 0 }}</div>
                <div class="text-[11px] text-slate-500 mt-1">Sedang di lapangan</div>
            </div>

            {{-- Lulus --}}
            <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition flex flex-col justify-between" style="background-color: #ffffff !important; border: 1px solid #f1f5f9 !important;">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold text-sky-600 uppercase tracking-wider">Lulus</span>
                    <span class="w-8 h-8 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                    </span>
                </div>
                <div class="text-2xl font-black text-sky-600">{{ $stats['total_completed'] ?? 0 }}</div>
                <div class="text-[11px] text-slate-500 mt-1">Tersertifikasi</div>
            </div>

            {{-- Sisa Kuota --}}
            <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition flex flex-col justify-between" style="background-color: #ffffff !important; border: 1px solid #f1f5f9 !important;">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Sisa Kuota</span>
                    <span class="w-8 h-8 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </span>
                </div>
                <div class="text-2xl font-black text-slate-800">{{ $stats['total_quota_available'] ?? 0 }}</div>
                <div class="text-[11px] text-slate-500 mt-1">{{ $isSuperAdmin ? 'Slot kuota kota' : 'Slot kuota dinas' }}</div>
            </div>
        </div>

        {{-- 3. DISTRIBUSI SEBARAN SESUAI SCOPE ROLE --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            {{-- Distribusi Penempatan: Instansi (Jika Super Admin) atau Unit Divisi (Jika Admin Dinas) --}}
            <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm space-y-4" style="background-color: #ffffff !important; border: 1px solid #f1f5f9 !important;">
                <div class="flex items-center justify-between gap-3 pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-3 flex-1 min-w-0">
                        <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-blue-600 shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </div>
                        <div class="min-w-0">
                            @if($isSuperAdmin)
                                <h3 class="text-sm font-bold text-slate-800 truncate">Distribusi Penempatan Instansi Dinas</h3>
                                <p class="text-xs text-slate-400 truncate">Sebaran mahasiswa magang di dinas Pemkot Surabaya</p>
                            @else
                                <h3 class="text-sm font-bold text-slate-800 truncate">Distribusi Divisi & Unit Kerja Dinas</h3>
                                <p class="text-xs text-slate-400 truncate">Sebaran mahasiswa magang di unit/bidang {{ $currentAgency->agency_name ?? 'Dinas' }}</p>
                            @endif
                        </div>
                    </div>
                    @if($isSuperAdmin)
                        <a href="{{ route('admin.agencies.index') }}" class="shrink-0 whitespace-nowrap inline-flex items-center gap-1.5 text-xs font-bold text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-xl transition border border-blue-200 shadow-2xs">
                            <span>Kelola Dinas</span>
                            <span class="text-sm leading-none">&rarr;</span>
                        </a>
                    @else
                        <a href="{{ route('admin.units.index') }}" class="shrink-0 whitespace-nowrap inline-flex items-center gap-1.5 text-xs font-bold text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-xl transition border border-blue-200 shadow-2xs">
                            <span>Kelola Unit</span>
                            <span class="text-sm leading-none">&rarr;</span>
                        </a>
                    @endif
                </div>

                <div class="space-y-4 pt-1">
                    @php
                        $distList = $isSuperAdmin ? ($agencyStats ?? []) : ($unitStats ?? []);
                    @endphp
                    @forelse($distList as $item)
                        @php
                            $itemName = is_array($item) ? $item['name'] : ($item->name ?? '-');
                            $itemCount = is_array($item) ? $item['count'] : ($item->count ?? 0);
                            $itemPercentage = is_array($item) ? $item['percentage'] : ($item->percentage ?? 0);
                            $itemQuota = is_array($item) ? ($item['quota'] ?? 0) : ($item->quota ?? 0);
                        @endphp
                        <div>
                            <div class="flex items-center justify-between text-xs font-semibold mb-1.5">
                                <span class="text-slate-700 font-medium truncate max-w-[240px]">{{ $itemName }}</span>
                                <span class="text-blue-700 font-bold shrink-0">
                                    {{ $itemCount }} Mahasiswa 
                                    @if(!$isSuperAdmin)
                                        <span class="text-slate-400 font-normal">/ Kuota {{ $itemQuota }}</span>
                                    @endif
                                    <span class="text-slate-400 font-normal">({{ $itemPercentage }}%)</span>
                                </span>
                            </div>
                            <div class="w-full rounded-full overflow-hidden mt-1.5" style="background-color: #f1f5f9; height: 8px; border-radius: 9999px; overflow: hidden;">
                                <div class="h-2 rounded-full transition-all duration-500" style="background-color: #2563eb; height: 8px; border-radius: 9999px; width: {{ max((float)$itemPercentage, 2) }}%"></div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-6 text-xs text-slate-400">Belum ada data penempatan divisi/unit.</div>
                    @endforelse
                </div>
            </div>

            {{-- Distribusi Asal Perguruan Tinggi --}}
            <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm space-y-4" style="background-color: #ffffff !important; border: 1px solid #f1f5f9 !important;">
                <div class="flex items-center justify-between gap-3 pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-3 flex-1 min-w-0">
                        <div class="min-w-0">
                            <h3 class="text-sm font-bold text-slate-800 truncate">
                                {{ $isSuperAdmin ? 'Distribusi Asal Kampus Surabaya' : 'Distribusi Asal Kampus Mahasiswa Dinas' }}
                            </h3>
                            <p class="text-xs text-slate-400 truncate">
                                {{ $isSuperAdmin ? 'Sebaran perguruan tinggi mitra resmi program magang' : 'Sebaran kampus pendaftar di ' . ($currentAgency->agency_name ?? 'dinas ini') }}
                            </p>
                        </div>
                    </div>
                    @if($isSuperAdmin)
                        <a href="{{ route('admin.universities.index') }}" class="shrink-0 whitespace-nowrap inline-flex items-center gap-1.5 text-xs font-bold text-sky-700 hover:text-sky-900 bg-sky-50 hover:bg-sky-100 px-3 py-1.5 rounded-xl transition border border-sky-200 shadow-2xs">
                            <span>Kelola Kampus</span>
                            <span class="text-sm leading-none">&rarr;</span>
                        </a>
                    @endif
                </div>

                <div class="space-y-4 pt-1">
                    @forelse($universityStats as $campus)
                        @php
                            $campName = is_array($campus) ? $campus['name'] : ($campus->name ?? '-');
                            $campCount = is_array($campus) ? $campus['count'] : ($campus->count ?? 0);
                            $campPercentage = is_array($campus) ? $campus['percentage'] : ($campus->percentage ?? 0);
                        @endphp
                        <div>
                            <div class="flex items-center justify-between text-xs font-semibold mb-1.5">
                                <span class="text-slate-700 font-medium truncate max-w-[240px]">{{ $campName }}</span>
                                <span class="text-sky-700 font-bold shrink-0">{{ $campCount }} Mahasiswa <span class="text-slate-400 font-normal">({{ $campPercentage }}%)</span></span>
                            </div>
                            <div class="w-full rounded-full overflow-hidden mt-1.5" style="background-color: #f1f5f9; height: 8px; border-radius: 9999px; overflow: hidden;">
                                <div class="h-2 rounded-full transition-all duration-500" style="background-color: #0284c7; height: 8px; border-radius: 9999px; width: {{ max((float)$campPercentage, 2) }}%"></div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-6 text-xs text-slate-400">Belum ada data distribusi kampus.</div>
                    @endforelse
                </div>
            </div>

        </div>

    </div>
</x-app-layout>
