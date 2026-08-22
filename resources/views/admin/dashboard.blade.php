<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

        {{-- 1. HERO BANNER GOVERNANCE HUB (PALING ATAS - ELEGAN BIRU PEMKOT SURABAYA) --}}
        <div class="rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden" style="background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 50%, #312e81 100%) !important; color: #ffffff !important;">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6 relative z-10">
                <div class="space-y-2 max-w-2xl">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider" style="background-color: rgba(251, 191, 36, 0.2) !important; color: #fde047 !important; border: 1px solid rgba(251, 191, 36, 0.4) !important;">
                        👑 SUPER ADMIN GOVERNANCE HUB
                    </span>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color: #ffffff !important;">
                        Pusat Kendali & Tata Kelola Eksekutif
                    </h1>
                    <p class="text-xs sm:text-sm leading-relaxed" style="color: #dbeafe !important;">
                        Pantau ekosistem magang Pemkot Surabaya secara terpusat: kuota multi-instansi, kemitraan universitas, alur penilaian multi-role, dan jejak aktivitas audit sistem.
                    </p>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 shrink-0">
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
                </div>
            </div>
        </div>

        {{-- 2. ENAM KARTU METRIK EKSEKUTIF (ROUNDED 2XL & SURABAYA BLUE ACCENT) --}}
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            {{-- Pendaftar --}}
            <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition flex flex-col justify-between" style="background-color: #ffffff !important; border: 1px solid #f1f5f9 !important;">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Pendaftar</span>
                    <span class="w-9 h-9 rounded-xl flex items-center justify-center text-base" style="background-color: #eff6ff !important; color: #2563eb !important;">🎓</span>
                </div>
                <div class="text-2xl font-black text-slate-800">{{ $stats['total_students'] ?? $totalApplicants ?? 0 }}</div>
                <div class="text-[11px] text-slate-500 mt-1">Mahasiswa terdaftar</div>
            </div>

            {{-- Verifikasi --}}
            <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition flex flex-col justify-between" style="background-color: #ffffff !important; border: 1px solid #f1f5f9 !important;">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold text-amber-500 uppercase tracking-wider">Verifikasi</span>
                    <span class="w-9 h-9 rounded-xl flex items-center justify-center text-base" style="background-color: #fffbeb !important; color: #d97706 !important;">⏳</span>
                </div>
                <div class="text-2xl font-black text-amber-600">{{ $stats['total_pending'] ?? $pendingCount ?? 0 }}</div>
                <div class="text-[11px] text-slate-500 mt-1">Menunggu dinas</div>
            </div>

            {{-- Diterima --}}
            <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition flex flex-col justify-between" style="background-color: #ffffff !important; border: 1px solid #f1f5f9 !important;">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold text-blue-600 uppercase tracking-wider">Diterima</span>
                    <span class="w-9 h-9 rounded-xl flex items-center justify-center text-base" style="background-color: #eff6ff !important; color: #2563eb !important;">📋</span>
                </div>
                <div class="text-2xl font-black text-blue-600">{{ $stats['total_accepted'] ?? $acceptedCount ?? 0 }}</div>
                <div class="text-[11px] text-slate-500 mt-1">Persiapan magang</div>
            </div>

            {{-- Aktif --}}
            <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition flex flex-col justify-between" style="background-color: #ffffff !important; border: 1px solid #f1f5f9 !important;">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider">Aktif</span>
                    <span class="w-9 h-9 rounded-xl flex items-center justify-center text-base" style="background-color: #ecfdf5 !important; color: #059669 !important;">⚡</span>
                </div>
                <div class="text-2xl font-black text-emerald-600">{{ $stats['total_active'] ?? $activeCount ?? 0 }}</div>
                <div class="text-[11px] text-slate-500 mt-1">Sedang di lapangan</div>
            </div>

            {{-- Lulus --}}
            <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition flex flex-col justify-between" style="background-color: #ffffff !important; border: 1px solid #f1f5f9 !important;">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold text-indigo-600 uppercase tracking-wider">Lulus</span>
                    <span class="w-9 h-9 rounded-xl flex items-center justify-center text-base" style="background-color: #eef2ff !important; color: #4f46e5 !important;">🏆</span>
                </div>
                <div class="text-2xl font-black text-indigo-600">{{ $stats['total_completed'] ?? $completedCount ?? 0 }}</div>
                <div class="text-[11px] text-slate-500 mt-1">Tersertifikasi</div>
            </div>

            {{-- Sisa Kuota --}}
            <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition flex flex-col justify-between" style="background-color: #ffffff !important; border: 1px solid #f1f5f9 !important;">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Sisa Kuota</span>
                    <span class="w-9 h-9 rounded-xl flex items-center justify-center text-base" style="background-color: #f1f5f9 !important; color: #475569 !important;">🏢</span>
                </div>
                <div class="text-2xl font-black text-slate-800">{{ $stats['total_quota_available'] ?? $totalRemainingQuota ?? 0 }}</div>
                <div class="text-[11px] text-slate-500 mt-1">Slot tersedia kota</div>
            </div>
        </div>

        {{-- 3. DISTRIBUSI SEBARAN KAMPUS & INSTANSI DENGAN TRACK SOLID ABU-ABU --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            {{-- Distribusi Penempatan Instansi --}}
            <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm space-y-4" style="background-color: #ffffff !important; border: 1px solid #f1f5f9 !important;">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl flex items-center justify-center text-lg" style="background-color: #eff6ff !important; color: #2563eb !important;">🏢</div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-800">Distribusi Penempatan Instansi Dinas</h3>
                            <p class="text-xs text-slate-400">Sebaran mahasiswa magang di dinas Pemkot Surabaya</p>
                        </div>
                    </div>
                    @if($isSuperAdmin)
                        <a href="{{ route('admin.agencies.index') }}" class="text-xs font-bold text-blue-600 hover:text-blue-800">Kelola Dinas →</a>
                    @endif
                </div>

                <div class="space-y-4 pt-1">
                    @php
                        $agenciesList = $agencyDistribution ?? $agencyStats ?? [];
                    @endphp
                    @forelse($agenciesList as $agency)
                        @php
                            $agName = is_array($agency) ? $agency['name'] : ($agency->name ?? '-');
                            $agCount = is_array($agency) ? $agency['count'] : ($agency->count ?? 0);
                            $agPercentage = is_array($agency) ? $agency['percentage'] : ($agency->percentage ?? 0);
                        @endphp
                        <div>
                            <div class="flex items-center justify-between text-xs font-semibold mb-1.5">
                                <span class="text-slate-700">{{ $agName }}</span>
                                <span class="text-blue-700 font-bold">{{ $agCount }} Mahasiswa <span class="text-slate-400 font-normal">({{ $agPercentage }}%)</span></span>
                            </div>
                            <div style="background-color: #e2e8f0; height: 10px; width: 100%; border-radius: 9999px; overflow: hidden; margin-top: 6px;">
                                <div style="background-color: #2563eb; height: 10px; border-radius: 9999px; width: {{ max((float)$agPercentage, 2) }}%; transition: width 0.5s ease-in-out;"></div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-6 text-xs text-slate-400">Belum ada data penempatan instansi.</div>
                    @endforelse
                </div>
            </div>

            {{-- Distribusi Asal Perguruan Tinggi --}}
            <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm space-y-4" style="background-color: #ffffff !important; border: 1px solid #f1f5f9 !important;">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl flex items-center justify-center text-lg" style="background-color: #f0f9ff !important; color: #0284c7 !important;">🎓</div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-800">Distribusi Asal Kampus Surabaya</h3>
                            <p class="text-xs text-slate-400">Sebaran perguruan tinggi mitra resmi program magang</p>
                        </div>
                    </div>
                    @if($isSuperAdmin)
                        <a href="{{ route('admin.universities.index') }}" class="text-xs font-bold text-sky-600 hover:text-sky-800">Kelola Kampus →</a>
                    @endif
                </div>

                <div class="space-y-4 pt-1">
                    @php
                        $campusList = $campusDistribution ?? $universityStats ?? [];
                    @endphp
                    @forelse($campusList as $campus)
                        @php
                            $campName = is_array($campus) ? $campus['name'] : ($campus->name ?? '-');
                            $campCount = is_array($campus) ? $campus['count'] : ($campus->count ?? 0);
                            $campPercentage = is_array($campus) ? $campus['percentage'] : ($campus->percentage ?? 0);
                        @endphp
                        <div>
                            <div class="flex items-center justify-between text-xs font-semibold mb-1.5">
                                <span class="text-slate-700">{{ $campName }}</span>
                                <span class="text-sky-700 font-bold">{{ $campCount }} Mahasiswa <span class="text-slate-400 font-normal">({{ $campPercentage }}%)</span></span>
                            </div>
                            <div style="background-color: #e2e8f0; height: 10px; width: 100%; border-radius: 9999px; overflow: hidden; margin-top: 6px;">
                                <div style="background-color: #0284c7; height: 10px; border-radius: 9999px; width: {{ max((float)$campPercentage, 2) }}%; transition: width 0.5s ease-in-out;"></div>
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
