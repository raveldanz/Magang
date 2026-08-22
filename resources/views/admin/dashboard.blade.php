<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

        {{-- 1. HERO BANNER DINAMIS SESUAI ROLE (SUPER ADMIN VS ADMIN DINAS) --}}
        <div class="rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden" 
             style="background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 50%, #312e81 100%) !important; color: #ffffff !important;">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6 relative z-10">
                <div class="space-y-2 max-w-2xl">
                    @if($isSuperAdmin)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider" style="background-color: rgba(251, 191, 36, 0.2) !important; color: #fde047 !important; border: 1px solid rgba(251, 191, 36, 0.4) !important;">
                            👑 SUPER ADMIN GOVERNANCE HUB
                        </span>
                        <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color: #ffffff !important;">
                            Pusat Kendali & Tata Kelola Eksekutif
                        </h1>
                        <p class="text-xs sm:text-sm leading-relaxed" style="color: #dbeafe !important;">
                            Pantau ekosistem magang Pemkot Surabaya secara terpusat: kuota multi-instansi, kemitraan universitas, alur penilaian multi-role, dan jejak aktivitas audit sistem.
                        </p>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider" style="background-color: rgba(56, 189, 248, 0.2) !important; color: #7dd3fc !important; border: 1px solid rgba(56, 189, 248, 0.4) !important;">
                            🏢 PORTAL TATA KELOLA DINAS
                        </span>
                        <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color: #ffffff !important;">
                            {{ $currentAgency->agency_name ?? 'Panel Pengelola Instansi Dinas' }}
                        </h1>
                        <p class="text-xs sm:text-sm leading-relaxed" style="color: #dbeafe !important;">
                            Kelola verifikasi berkas pendaftaran magang dinas, alokasi kuota divisi, penugasan mentor lapangan, monitoring logbook, dan penerbitan sertifikat kelulusan resmi.
                        </p>
                    @endif
                </div>

                {{-- Quick Actions Hero Sesuai Role --}}
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 shrink-0">
                    @if($isSuperAdmin)
                        <a href="{{ route('admin.agencies.index') }}" class="p-3.5 rounded-2xl text-center transition group shadow-sm hover:scale-105 cursor-pointer" style="background-color: rgba(255, 255, 255, 0.15) !important; border: 1px solid rgba(255, 255, 255, 0.25) !important; color: #ffffff !important;">
                            <div class="text-xl mb-1 group-hover:scale-110 transition-transform">🏢</div>
                            <div class="text-xs font-bold" style="color: #ffffff !important;">Instansi</div>
                            <div class="text-[10px]" style="color: #bfdbfe !important;">Kelola Kuota</div>
                        </a>
                        <a href="{{ route('admin.universities.index') }}" class="p-3.5 rounded-2xl text-center transition group shadow-sm hover:scale-105 cursor-pointer" style="background-color: rgba(255, 255, 255, 0.15) !important; border: 1px solid rgba(255, 255, 255, 0.25) !important; color: #ffffff !important;">
                            <div class="text-xl mb-1 group-hover:scale-110 transition-transform">🎓</div>
                            <div class="text-xs font-bold" style="color: #ffffff !important;">Kampus</div>
                            <div class="text-[10px]" style="color: #bfdbfe !important;">Mitra MBKM</div>
                        </a>
                        <a href="{{ route('admin.users.index') }}" class="p-3.5 rounded-2xl text-center transition group shadow-sm hover:scale-105 cursor-pointer" style="background-color: rgba(255, 255, 255, 0.15) !important; border: 1px solid rgba(255, 255, 255, 0.25) !important; color: #ffffff !important;">
                            <div class="text-xl mb-1 group-hover:scale-110 transition-transform">👥</div>
                            <div class="text-xs font-bold" style="color: #ffffff !important;">Pengguna</div>
                            <div class="text-[10px]" style="color: #bfdbfe !important;">Semua Akun</div>
                        </a>
                        <a href="{{ route('admin.audit_logs.index') }}" class="p-3.5 rounded-2xl text-center transition group shadow-sm hover:scale-105 cursor-pointer" style="background-color: rgba(255, 255, 255, 0.15) !important; border: 1px solid rgba(255, 255, 255, 0.25) !important; color: #ffffff !important;">
                            <div class="text-xl mb-1 group-hover:scale-110 transition-transform">📜</div>
                            <div class="text-xs font-bold" style="color: #ffffff !important;">Log Audit</div>
                            <div class="text-[10px]" style="color: #bfdbfe !important;">Rekam Jejak</div>
                        </a>
                    @else
                        <a href="{{ route('admin.applications.index') }}" class="p-3.5 rounded-2xl text-center transition group shadow-sm hover:scale-105 cursor-pointer" style="background-color: rgba(255, 255, 255, 0.15) !important; border: 1px solid rgba(255, 255, 255, 0.25) !important; color: #ffffff !important;">
                            <div class="text-xl mb-1 group-hover:scale-110 transition-transform">📋</div>
                            <div class="text-xs font-bold" style="color: #ffffff !important;">Verifikasi</div>
                            <div class="text-[10px]" style="color: #bfdbfe !important;">Berkas Masuk</div>
                        </a>
                        <a href="{{ route('admin.units.index') }}" class="p-3.5 rounded-2xl text-center transition group shadow-sm hover:scale-105 cursor-pointer" style="background-color: rgba(255, 255, 255, 0.15) !important; border: 1px solid rgba(255, 255, 255, 0.25) !important; color: #ffffff !important;">
                            <div class="text-xl mb-1 group-hover:scale-110 transition-transform">🏢</div>
                            <div class="text-xs font-bold" style="color: #ffffff !important;">Unit & Kuota</div>
                            <div class="text-[10px]" style="color: #bfdbfe !important;">Alokasi Slot</div>
                        </a>
                        <a href="{{ route('admin.mentors.index') }}" class="p-3.5 rounded-2xl text-center transition group shadow-sm hover:scale-105 cursor-pointer" style="background-color: rgba(255, 255, 255, 0.15) !important; border: 1px solid rgba(255, 255, 255, 0.25) !important; color: #ffffff !important;">
                            <div class="text-xl mb-1 group-hover:scale-110 transition-transform">👔</div>
                            <div class="text-xs font-bold" style="color: #ffffff !important;">Mentor</div>
                            <div class="text-[10px]" style="color: #bfdbfe !important;">Pembimbing</div>
                        </a>
                        <a href="{{ route('admin.certificates.index') }}" class="p-3.5 rounded-2xl text-center transition group shadow-sm hover:scale-105 cursor-pointer" style="background-color: rgba(255, 255, 255, 0.15) !important; border: 1px solid rgba(255, 255, 255, 0.25) !important; color: #ffffff !important;">
                            <div class="text-xl mb-1 group-hover:scale-110 transition-transform">🏆</div>
                            <div class="text-xs font-bold" style="color: #ffffff !important;">Sertifikat</div>
                            <div class="text-[10px]" style="color: #bfdbfe !important;">Penerbitan</div>
                        </a>
                    @endif
                </div>
            </div>
        </div>

        {{-- 2. ENAM KARTU METRIK EKSEKUTIF (TERISOLASI OTOMATIS BERDASARKAN DINAS) --}}
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            {{-- Pendaftar --}}
            <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition flex flex-col justify-between" style="background-color: #ffffff !important; border: 1px solid #f1f5f9 !important;">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Pendaftar</span>
                    <span class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-sm">🎓</span>
                </div>
                <div class="text-2xl font-black text-slate-800">{{ $stats['total_students'] ?? 0 }}</div>
                <div class="text-[11px] text-slate-500 mt-1">{{ $isSuperAdmin ? 'Mahasiswa kota' : 'Pendaftar dinas' }}</div>
            </div>

            {{-- Verifikasi --}}
            <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition flex flex-col justify-between" style="background-color: #ffffff !important; border: 1px solid #f1f5f9 !important;">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold text-amber-500 uppercase tracking-wider">Verifikasi</span>
                    <span class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-sm">⏳</span>
                </div>
                <div class="text-2xl font-black text-amber-600">{{ $stats['total_pending'] ?? 0 }}</div>
                <div class="text-[11px] text-slate-500 mt-1">Menunggu dinas</div>
            </div>

            {{-- Diterima --}}
            <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition flex flex-col justify-between" style="background-color: #ffffff !important; border: 1px solid #f1f5f9 !important;">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold text-blue-600 uppercase tracking-wider">Diterima</span>
                    <span class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-sm">📋</span>
                </div>
                <div class="text-2xl font-black text-blue-600">{{ $stats['total_accepted'] ?? 0 }}</div>
                <div class="text-[11px] text-slate-500 mt-1">Persiapan magang</div>
            </div>

            {{-- Aktif --}}
            <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition flex flex-col justify-between" style="background-color: #ffffff !important; border: 1px solid #f1f5f9 !important;">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider">Aktif</span>
                    <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm">⚡</span>
                </div>
                <div class="text-2xl font-black text-emerald-600">{{ $stats['total_active'] ?? 0 }}</div>
                <div class="text-[11px] text-slate-500 mt-1">Sedang di lapangan</div>
            </div>

            {{-- Lulus --}}
            <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition flex flex-col justify-between" style="background-color: #ffffff !important; border: 1px solid #f1f5f9 !important;">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold text-indigo-600 uppercase tracking-wider">Lulus</span>
                    <span class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm">🏆</span>
                </div>
                <div class="text-2xl font-black text-indigo-600">{{ $stats['total_completed'] ?? 0 }}</div>
                <div class="text-[11px] text-slate-500 mt-1">Tersertifikasi</div>
            </div>

            {{-- Sisa Kuota --}}
            <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition flex flex-col justify-between" style="background-color: #ffffff !important; border: 1px solid #f1f5f9 !important;">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Sisa Kuota</span>
                    <span class="w-8 h-8 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center text-sm">🏢</span>
                </div>
                <div class="text-2xl font-black text-slate-800">{{ $stats['total_quota_available'] ?? 0 }}</div>
                <div class="text-[11px] text-slate-500 mt-1">{{ $isSuperAdmin ? 'Slot kuota kota' : 'Slot kuota dinas' }}</div>
            </div>
        </div>

        {{-- 3. DISTRIBUSI SEBARAN SESUAI SCOPE ROLE --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            {{-- Distribusi Penempatan: Instansi (Jika Super Admin) atau Unit Divisi (Jika Admin Dinas) --}}
            <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm space-y-4" style="background-color: #ffffff !important; border: 1px solid #f1f5f9 !important;">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg">🏢</div>
                        <div>
                            @if($isSuperAdmin)
                                <h3 class="text-sm font-bold text-slate-800">Distribusi Penempatan Instansi Dinas</h3>
                                <p class="text-xs text-slate-400">Sebaran mahasiswa magang di dinas Pemkot Surabaya</p>
                            @else
                                <h3 class="text-sm font-bold text-slate-800">Distribusi Divisi & Unit Kerja Dinas</h3>
                                <p class="text-xs text-slate-400">Sebaran mahasiswa magang di unit/bidang {{ $currentAgency->agency_name ?? 'Dinas' }}</p>
                            @endif
                        </div>
                    </div>
                    @if($isSuperAdmin)
                        <a href="{{ route('admin.agencies.index') }}" class="text-xs font-bold text-blue-600 hover:text-blue-800">Kelola Dinas →</a>
                    @else
                        <a href="{{ route('admin.units.index') }}" class="text-xs font-bold text-blue-600 hover:text-blue-800">Kelola Unit →</a>
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
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-sky-50 text-sky-600 flex items-center justify-center text-lg">🎓</div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-800">
                                {{ $isSuperAdmin ? 'Distribusi Asal Kampus Surabaya' : 'Distribusi Asal Kampus Mahasiswa Dinas' }}
                            </h3>
                            <p class="text-xs text-slate-400">
                                {{ $isSuperAdmin ? 'Sebaran perguruan tinggi mitra resmi program magang' : 'Sebaran kampus pendaftar di ' . ($currentAgency->agency_name ?? 'dinas ini') }}
                            </p>
                        </div>
                    </div>
                    @if($isSuperAdmin)
                        <a href="{{ route('admin.universities.index') }}" class="text-xs font-bold text-sky-600 hover:text-sky-800">Kelola Kampus →</a>
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
