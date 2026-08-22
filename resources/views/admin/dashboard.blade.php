<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-black text-xl sm:text-2xl text-gray-900 tracking-tight flex items-center gap-2.5">
                    <span class="text-2xl">🏛️</span>
                    <span>Executive Dashboard {{ $isSuperAdmin ? 'Pemerintah Kota Surabaya' : ($currentAgency->agency_name ?? 'Admin Instansi') }}</span>
                </h2>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">
                    {{ $isSuperAdmin ? 'Pusat Kendali Eksekutif, Tata Kelola Multi-Instansi, dan Pemantauan Seluruh Mahasiswa Magang' : 'Pusat Kendali Pengajuan, Penugasan Mentor, dan Pemantauan Mahasiswa Magang Instansi' }}
                </p>
            </div>

            <!-- Quick Filter Super Admin -->
            @if($isSuperAdmin)
                <form method="GET" action="{{ route('admin.dashboard') }}" class="flex items-center gap-2">
                    <select name="agency_id" onchange="this.form.submit()" class="text-xs rounded-2xl border-gray-200 shadow-2xs font-semibold focus:ring-blue-500 focus:border-blue-500 py-2.5 px-3.5 bg-white text-slate-700">
                        <option value="">🏢 Semua Instansi Dinas Kota</option>
                        @foreach($agencies as $ag)
                            <option value="{{ $ag->id }}" {{ request('agency_id') == $ag->id ? 'selected' : '' }}>
                                {{ $ag->agency_name }}
                            </option>
                        @endforeach
                    </select>
                </form>
            @endif
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            <!-- Flash Alert Messages -->
            @if (session('success'))
                <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl shadow-xs flex items-center justify-between text-emerald-900 text-sm font-medium">
                    <div class="flex items-center gap-2.5">
                        <span class="text-lg">✅</span>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if (session('info'))
                <div class="p-4 bg-blue-50 border border-blue-200 rounded-2xl shadow-xs flex items-center justify-between text-blue-900 text-sm font-medium">
                    <div class="flex items-center gap-2.5">
                        <span class="text-lg">ℹ️</span>
                        <span>{{ session('info') }}</span>
                    </div>
                </div>
            @endif

            <!-- 1. STATS METRICS GRID (DESAIN MELENGKUNG MODERN & ELEGAN DENGAN SENTUHAN BIRU SURABAYA) -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                
                <!-- 1. Total Pendaftar -->
                <div class="bg-white p-5 rounded-3xl border border-slate-100 shadow-sm hover:shadow-md transition flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Pendaftar</span>
                        <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg font-bold shadow-2xs">
                            👨‍🎓
                        </div>
                    </div>
                    <div class="mt-4">
                        <span class="text-2xl font-black text-slate-900">{{ $stats['total_students'] }}</span>
                        <span class="text-[11px] text-slate-400 block mt-0.5 font-medium">Mahasiswa terdaftar</span>
                    </div>
                </div>

                <!-- 2. Menunggu Verifikasi -->
                <div class="bg-white p-5 rounded-3xl border border-slate-100 shadow-sm hover:shadow-md transition flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-amber-600 uppercase tracking-wider">Verifikasi</span>
                        <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg font-bold shadow-2xs">
                            ⏳
                        </div>
                    </div>
                    <div class="mt-4">
                        <span class="text-2xl font-black text-amber-600">{{ $stats['total_pending'] }}</span>
                        <span class="text-[11px] text-slate-400 block mt-0.5 font-medium">Menunggu dinas</span>
                    </div>
                </div>

                <!-- 3. Diterima / Persiapan -->
                <div class="bg-white p-5 rounded-3xl border border-slate-100 shadow-sm hover:shadow-md transition flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-blue-600 uppercase tracking-wider">Diterima</span>
                        <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg font-bold shadow-2xs">
                            📋
                        </div>
                    </div>
                    <div class="mt-4">
                        <span class="text-2xl font-black text-blue-600">{{ $stats['total_accepted'] }}</span>
                        <span class="text-[11px] text-slate-400 block mt-0.5 font-medium">Persiapan magang</span>
                    </div>
                </div>

                <!-- 4. Sedang Magang (Aktif) -->
                <div class="bg-white p-5 rounded-3xl border border-slate-100 shadow-sm hover:shadow-md transition flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-emerald-600 uppercase tracking-wider">Aktif</span>
                        <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg font-bold shadow-2xs">
                            🟢
                        </div>
                    </div>
                    <div class="mt-4">
                        <span class="text-2xl font-black text-emerald-600">{{ $stats['total_active'] }}</span>
                        <span class="text-[11px] text-slate-400 block mt-0.5 font-medium">Aktif di dinas</span>
                    </div>
                </div>

                <!-- 5. Lulus Magang -->
                <div class="bg-white p-5 rounded-3xl border border-slate-100 shadow-sm hover:shadow-md transition flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-indigo-600 uppercase tracking-wider">Lulus</span>
                        <div class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg font-bold shadow-2xs">
                            🎓
                        </div>
                    </div>
                    <div class="mt-4">
                        <span class="text-2xl font-black text-indigo-600">{{ $stats['total_completed'] }}</span>
                        <span class="text-[11px] text-slate-400 block mt-0.5 font-medium">Tersertifikasi</span>
                    </div>
                </div>

                <!-- 6. Total Kuota Tersedia -->
                <div class="bg-white p-5 rounded-3xl border border-slate-100 shadow-sm hover:shadow-md transition flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-teal-600 uppercase tracking-wider">Sisa Kuota</span>
                        <div class="w-10 h-10 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center text-lg font-bold shadow-2xs">
                            🏢
                        </div>
                    </div>
                    <div class="mt-4">
                        <span class="text-2xl font-black text-teal-600">{{ $stats['total_quota_available'] }}</span>
                        <span class="text-[11px] text-slate-400 block mt-0.5 font-medium">Slot dari {{ $stats['total_units'] }} unit</span>
                    </div>
                </div>

            </div>

            <!-- 2. HERO BANNER: SUPER ADMIN GOVERNANCE HUB (GRADASI BIRU ELEGAN KHAS PEMKOT SURABAYA & GLASSMORPHISM BUTTONS) -->
            @if($isSuperAdmin)
                <div class="rounded-3xl p-6 sm:p-8 shadow-xl relative overflow-hidden text-white" style="background: linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 50%, #312e81 100%) !important; color: #ffffff !important;">
                    <div class="relative z-10 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
                        <div>
                            <span class="px-3.5 py-1 rounded-full text-xs font-black uppercase tracking-wider inline-flex items-center gap-1.5" style="background-color: rgba(255, 255, 255, 0.2) !important; color: #ffffff !important; border: 1px solid rgba(255, 255, 255, 0.3) !important;">
                                <span>👑</span>
                                <span>Super Admin Governance Hub</span>
                            </span>
                            <h3 class="text-xl sm:text-2xl font-black mt-2.5 tracking-tight" style="color: #ffffff !important;">
                                Pusat Kendali & Tata Kelola Master Sistem
                            </h3>
                            <p class="text-xs sm:text-sm max-w-2xl mt-1.5 leading-relaxed" style="color: #e0e7ff !important;">
                                Kelola entitas multi-instansi dinas, master universitas di Surabaya, manajemen akun seluruh pengguna, serta audit riwayat aktivitas sistem secara real-time.
                            </p>
                        </div>
                        
                        <!-- 4 Tombol Cepat Aksi (Glassmorphism rounded-2xl) -->
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 w-full lg:w-auto shrink-0">
                            
                            <!-- Master Dinas -->
                            <a href="{{ route('admin.agencies.index') }}" class="px-4 py-3.5 rounded-2xl text-center text-xs font-bold transition flex flex-col items-center gap-1.5 shadow-md hover:scale-105 cursor-pointer backdrop-blur-md" style="background: rgba(255, 255, 255, 0.15) !important; color: #ffffff !important; border: 1px solid rgba(255, 255, 255, 0.25) !important;">
                                <span class="text-2xl">🏢</span>
                                <span class="font-extrabold">Master Dinas</span>
                            </a>

                            <!-- Unit & Kuota -->
                            <a href="{{ route('admin.units.index') }}" class="px-4 py-3.5 rounded-2xl text-center text-xs font-bold transition flex flex-col items-center gap-1.5 shadow-md hover:scale-105 cursor-pointer backdrop-blur-md" style="background: rgba(255, 255, 255, 0.15) !important; color: #ffffff !important; border: 1px solid rgba(255, 255, 255, 0.25) !important;">
                                <span class="text-2xl">📁</span>
                                <span class="font-extrabold">Unit & Kuota</span>
                            </a>

                            <!-- Kelola Pengguna -->
                            <a href="{{ route('admin.users.index') }}" class="px-4 py-3.5 rounded-2xl text-center text-xs font-bold transition flex flex-col items-center gap-1.5 shadow-md hover:scale-105 cursor-pointer backdrop-blur-md" style="background: rgba(255, 255, 255, 0.15) !important; color: #ffffff !important; border: 1px solid rgba(255, 255, 255, 0.25) !important;">
                                <span class="text-2xl">👥</span>
                                <span class="font-extrabold">Pengguna</span>
                            </a>

                            <!-- Log Audit -->
                            <a href="{{ route('admin.audit_logs.index') }}" class="px-4 py-3.5 rounded-2xl text-center text-xs font-bold transition flex flex-col items-center gap-1.5 shadow-md hover:scale-105 cursor-pointer backdrop-blur-md" style="background: rgba(255, 255, 255, 0.15) !important; color: #ffffff !important; border: 1px solid rgba(255, 255, 255, 0.25) !important;">
                                <span class="text-2xl">📜</span>
                                <span class="font-extrabold">Log Audit</span>
                            </a>

                        </div>
                    </div>
                </div>
            @endif

            <!-- 3. DUA KOLOM: SEBARAN DINAS & SEBARAN UNIVERSITAS (SOLID CONSISTENT PROGRESS BARS) -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                <!-- Sebaran Instansi Dinas (Solid Blue-600) -->
                <div class="bg-white rounded-3xl border border-slate-100 p-6 sm:p-7 shadow-xs flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg font-bold">
                                    🏢
                                </div>
                                <div>
                                    <h4 class="font-black text-sm text-gray-900">Distribusi Penempatan Instansi Dinas</h4>
                                    <p class="text-xs text-gray-400">Sebaran mahasiswa magang di instansi Pemkot Surabaya</p>
                                </div>
                            </div>
                            @if($isSuperAdmin)
                                <a href="{{ route('admin.agencies.index') }}" class="text-xs text-blue-600 hover:text-blue-800 font-bold">Kelola Dinas →</a>
                            @endif
                        </div>

                        <div class="mt-5 space-y-4">
                            @foreach($agencyStats as $ag)
                                <div>
                                    <div class="flex items-center justify-between text-xs mb-1.5">
                                        <span class="font-bold text-gray-800">{{ $ag['name'] }}</span>
                                        <div class="flex items-center gap-2 font-mono">
                                            <span class="text-blue-600 font-bold">{{ $ag['count'] }} Mahasiswa</span>
                                            <span class="text-gray-400">({{ $ag['percentage'] }}%)</span>
                                        </div>
                                    </div>
                                    <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden mt-1.5">
                                        <div class="bg-blue-600 h-2.5 rounded-full transition-all duration-500" style="width: {{ $ag['percentage'] }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Sebaran Universitas Asal (Solid Sky-500) -->
                <div class="bg-white rounded-3xl border border-slate-100 p-6 sm:p-7 shadow-xs flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-2xl bg-sky-50 text-sky-600 flex items-center justify-center text-lg font-bold">
                                    🎓
                                </div>
                                <div>
                                    <h4 class="font-black text-sm text-gray-900">Distribusi Asal Kampus Surabaya</h4>
                                    <p class="text-xs text-gray-400">Sebaran asal perguruan tinggi mitra resmi MBKM</p>
                                </div>
                            </div>
                            @if($isSuperAdmin)
                                <a href="{{ route('admin.universities.index') }}" class="text-xs text-sky-600 hover:text-sky-800 font-bold">Kelola Kampus →</a>
                            @endif
                        </div>

                        <div class="mt-5 space-y-4">
                            @foreach($universityStats as $un)
                                <div>
                                    <div class="flex items-center justify-between text-xs mb-1.5">
                                        <span class="font-bold text-gray-800">{{ $un['name'] }} ({{ $un['code'] }})</span>
                                        <div class="flex items-center gap-2 font-mono">
                                            <span class="text-sky-600 font-bold">{{ $un['count'] }} Mahasiswa</span>
                                            <span class="text-gray-400">({{ $un['percentage'] }}%)</span>
                                        </div>
                                    </div>
                                    <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden mt-1.5">
                                        <div class="bg-sky-500 h-2.5 rounded-full transition-all duration-500" style="width: {{ $un['percentage'] }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

            </div>

            <!-- 4. DUA TABEL: PENGAJUAN TERBARU & AUDIT LOGS STREAM -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Pengajuan Magang Terbaru (2 Kolom) -->
                <div class="lg:col-span-2 bg-white rounded-3xl border border-slate-100 shadow-xs p-6 sm:p-7">
                    <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg font-bold">
                                📑
                            </div>
                            <div>
                                <h4 class="font-black text-sm text-gray-900">Pengajuan Magang Masuk Terbaru</h4>
                                <p class="text-xs text-gray-400">Pendaftar terbaru yang memerlukan verifikasi atau pemantauan</p>
                            </div>
                        </div>
                        <a href="{{ route('admin.applications.index') }}" class="text-xs text-blue-600 hover:text-blue-800 font-bold">Lihat Semua ({{ $stats['total_students'] }}) →</a>
                    </div>

                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100 text-left text-xs">
                            <thead class="bg-slate-50/70 text-gray-500 font-bold">
                                <tr>
                                    <th class="py-3 px-3 rounded-l-xl">Mahasiswa</th>
                                    <th class="py-3 px-3">Instansi / Unit</th>
                                    <th class="py-3 px-3">Status</th>
                                    <th class="py-3 px-3 text-right rounded-r-xl">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($recentApplications as $app)
                                    <tr class="hover:bg-slate-50 transition">
                                        <td class="py-3 px-3">
                                            <div class="font-bold text-gray-900">{{ $app->user->name ?? '-' }}</div>
                                            <div class="text-[11px] text-gray-500 font-mono">{{ $app->user?->studentProfile?->universitas ?? '-' }}</div>
                                        </td>
                                        <td class="py-3 px-3">
                                            <div class="font-semibold text-slate-800">{{ $app->unit?->agencyProfile?->agency_name ?? '-' }}</div>
                                            <div class="text-[11px] text-gray-500">{{ $app->unit?->name ?? '-' }}</div>
                                        </td>
                                        <td class="py-3 px-3">
                                            @if(in_array($app->status, ['pending', 'submitted']))
                                                <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-amber-100 text-amber-800 border border-amber-200">Menunggu</span>
                                            @elseif(in_array($app->status, ['accepted', 'verified']))
                                                <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-blue-100 text-blue-800 border border-blue-200">Diterima</span>
                                            @elseif($app->status === 'completed')
                                                <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-indigo-100 text-indigo-800 border border-indigo-200">Lulus</span>
                                            @elseif($app->status === 'rejected')
                                                <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-rose-100 text-rose-800 border border-rose-200">Ditolak</span>
                                            @else
                                                <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-gray-100 text-gray-700">{{ $app->status }}</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-3 text-right">
                                            <a href="{{ route('admin.applications.show', $app->id) }}" class="text-xs font-bold text-blue-600 hover:text-blue-800">Detail →</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-6 text-center text-gray-400">Belum ada pengajuan magang</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Riwayat Audit Log Terkini (1 Kolom) -->
                <div class="bg-white rounded-3xl border border-slate-100 shadow-xs p-6 sm:p-7 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg font-bold">
                                    📜
                                </div>
                                <div>
                                    <h4 class="font-black text-sm text-gray-900">Audit Trail Terkini</h4>
                                    <p class="text-xs text-gray-400">Riwayat aksi sistem real-time</p>
                                </div>
                            </div>
                            <a href="{{ route('admin.audit_logs.index') }}" class="text-xs text-blue-600 hover:text-blue-800 font-bold">Semua →</a>
                        </div>

                        <div class="mt-4 space-y-3">
                            @forelse($recentAuditLogs as $log)
                                <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100 text-xs">
                                    <div class="flex items-center justify-between">
                                        <span class="font-black text-slate-800 text-[11px]">{{ $log->action }}</span>
                                        <span class="text-[10px] text-slate-400 font-mono">{{ $log->created_at->diffForHumans() }}</span>
                                    </div>
                                    <div class="text-[11px] text-slate-600 mt-1">
                                        Oleh: <strong>{{ $log->user_name }}</strong> <span class="text-slate-400">({{ $log->user_role }})</span>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-6 text-gray-400 text-xs">Belum ada catatan audit log</div>
                            @endforelse
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>
