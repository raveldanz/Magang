<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-black text-xl sm:text-2xl text-gray-900 tracking-tight flex items-center gap-2">
                    <span>🏛️</span>
                    <span>Executive Dashboard {{ $isSuperAdmin ? 'Pemerintah Kota Surabaya' : ($currentAgency->agency_name ?? 'Admin Instansi') }}</span>
                </h2>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">
                    {{ $isSuperAdmin ? 'Pusat Kendali Eksekutif, Tata Kelola Multi-Instansi, dan Pemantauan Seluruh Mahasiswa Magang' : 'Pusat Kendali Pengajuan, Penugasan Mentor, dan Pemantauan Mahasiswa Magang Instansi' }}
                </p>
            </div>

            <!-- Quick Filter Super Admin -->
            @if($isSuperAdmin)
                <form method="GET" action="{{ route('admin.dashboard') }}" class="flex items-center gap-2">
                    <select name="agency_id" onchange="this.form.submit()" class="text-xs rounded-xl border-gray-300 shadow-2xs font-semibold focus:ring-indigo-500 focus:border-indigo-500">
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
                <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-xl shadow-xs flex items-center justify-between text-emerald-900 text-sm font-medium">
                    <div class="flex items-center gap-2">
                        <span>✅</span>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if (session('info'))
                <div class="p-4 bg-blue-50 border-l-4 border-blue-500 rounded-r-xl shadow-xs flex items-center justify-between text-blue-900 text-sm font-medium">
                    <div class="flex items-center gap-2">
                        <span>ℹ️</span>
                        <span>{{ session('info') }}</span>
                    </div>
                </div>
            @endif

            <!-- 1. STATS METRICS GRID -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                
                <!-- Total Mahasiswa -->
                <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-xs flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Total Pendaftar</span>
                        <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm font-bold">
                            👨‍🎓
                        </div>
                    </div>
                    <div class="mt-4">
                        <span class="text-2xl font-black text-gray-900">{{ $stats['total_students'] }}</span>
                        <span class="text-[11px] text-gray-400 block mt-0.5">Mahasiswa terdaftar</span>
                    </div>
                </div>

                <!-- Menunggu Verifikasi -->
                <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-xs flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-amber-600 uppercase tracking-wider">Verifikasi</span>
                        <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-sm font-bold">
                            ⏳
                        </div>
                    </div>
                    <div class="mt-4">
                        <span class="text-2xl font-black text-amber-600">{{ $stats['total_pending'] }}</span>
                        <span class="text-[11px] text-gray-400 block mt-0.5">Menunggu respon dinas</span>
                    </div>
                </div>

                <!-- Diterima / Persiapan -->
                <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-xs flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-blue-600 uppercase tracking-wider">Diterima</span>
                        <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-sm font-bold">
                            📋
                        </div>
                    </div>
                    <div class="mt-4">
                        <span class="text-2xl font-black text-blue-600">{{ $stats['total_accepted'] }}</span>
                        <span class="text-[11px] text-gray-400 block mt-0.5">Masa persiapan magang</span>
                    </div>
                </div>

                <!-- Sedang Magang -->
                <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-xs flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-emerald-600 uppercase tracking-wider">Aktif Magang</span>
                        <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm font-bold">
                            🟢
                        </div>
                    </div>
                    <div class="mt-4">
                        <span class="text-2xl font-black text-emerald-600">{{ $stats['total_active'] }}</span>
                        <span class="text-[11px] text-gray-400 block mt-0.5">Aktif di lapangan</span>
                    </div>
                </div>

                <!-- Lulus -->
                <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-xs flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-purple-600 uppercase tracking-wider">Lulus Magang</span>
                        <div class="w-8 h-8 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-sm font-bold">
                            🎓
                        </div>
                    </div>
                    <div class="mt-4">
                        <span class="text-2xl font-black text-purple-600">{{ $stats['total_completed'] }}</span>
                        <span class="text-[11px] text-gray-400 block mt-0.5">Tersertifikasi & bernilai</span>
                    </div>
                </div>

                <!-- Total Kuota Tersedia -->
                <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-xs flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-teal-600 uppercase tracking-wider">Sisa Kuota</span>
                        <div class="w-8 h-8 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center text-sm font-bold">
                            🏢
                        </div>
                    </div>
                    <div class="mt-4">
                        <span class="text-2xl font-black text-teal-600">{{ $stats['total_quota_available'] }}</span>
                        <span class="text-[11px] text-gray-400 block mt-0.5">Slot dari {{ $stats['total_units'] }} unit</span>
                    </div>
                </div>

            </div>

            <!-- 2. QUICK MANAGEMENT ACTIONS (SUPER ADMIN ONLY) -->
            @if($isSuperAdmin)
                <div class="bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 text-white rounded-3xl p-6 sm:p-8 shadow-xl relative overflow-hidden">
                    <div class="relative z-10 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
                        <div>
                            <span class="px-3 py-1 bg-white/10 rounded-full text-xs font-bold uppercase tracking-wider text-indigo-200 border border-white/10">
                                👑 Super Admin Governance Hub
                            </span>
                            <h3 class="text-xl sm:text-2xl font-black mt-2">Pusat Kendali & Tata Kelola Master Sistem</h3>
                            <p class="text-xs sm:text-sm text-slate-300 max-w-2xl mt-1">
                                Kelola entitas multi-instansi dinas, master universitas di Surabaya, manajemen akun seluruh pengguna, serta audit riwayat aktivitas sistem secara real-time.
                            </p>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 w-full lg:w-auto shrink-0">
                            <a href="{{ route('admin.agencies.index') }}" class="px-4 py-3 bg-white/10 hover:bg-white/20 border border-white/15 rounded-2xl text-center text-xs font-bold transition flex flex-col items-center gap-1.5 shadow-sm">
                                <span class="text-lg">🏢</span>
                                <span>Master Dinas</span>
                            </a>
                            <a href="{{ route('admin.units.index') }}" class="px-4 py-3 bg-white/10 hover:bg-white/20 border border-white/15 rounded-2xl text-center text-xs font-bold transition flex flex-col items-center gap-1.5 shadow-sm">
                                <span class="text-lg">📁</span>
                                <span>Unit & Kuota</span>
                            </a>
                            <a href="{{ route('admin.users.index') }}" class="px-4 py-3 bg-white/10 hover:bg-white/20 border border-white/15 rounded-2xl text-center text-xs font-bold transition flex flex-col items-center gap-1.5 shadow-sm">
                                <span class="text-lg">👥</span>
                                <span>Kelola Pengguna</span>
                            </a>
                            <a href="{{ route('admin.audit_logs.index') }}" class="px-4 py-3 bg-white/10 hover:bg-white/20 border border-white/15 rounded-2xl text-center text-xs font-bold transition flex flex-col items-center gap-1.5 shadow-sm">
                                <span class="text-lg">📜</span>
                                <span>Log Audit</span>
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            <!-- 3. DUA KOLOM: SEBARAN DINAS & SEBARAN UNIVERSITAS -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                <!-- Sebaran Instansi Dinas -->
                <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-xs flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                            <div class="flex items-center gap-2">
                                <span class="text-lg">🏢</span>
                                <div>
                                    <h4 class="font-bold text-sm text-gray-900">Distribusi Penempatan Instansi Dinas</h4>
                                    <p class="text-xs text-gray-400">Sebaran mahasiswa magang di instansi Pemkot Surabaya</p>
                                </div>
                            </div>
                            @if($isSuperAdmin)
                                <a href="{{ route('admin.agencies.index') }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-bold">Kelola Dinas →</a>
                            @endif
                        </div>

                        <div class="mt-4 space-y-4">
                            @foreach($agencyStats as $ag)
                                <div>
                                    <div class="flex items-center justify-between text-xs mb-1">
                                        <span class="font-bold text-gray-800">{{ $ag['name'] }}</span>
                                        <div class="flex items-center gap-2 font-mono">
                                            <span class="text-indigo-600 font-bold">{{ $ag['count'] }} Mahasiswa</span>
                                            <span class="text-gray-400">({{ $ag['percentage'] }}%)</span>
                                        </div>
                                    </div>
                                    <div class="w-full bg-gray-100 rounded-full h-2.5 overflow-hidden">
                                        <div class="bg-indigo-600 h-2.5 rounded-full transition-all duration-500" style="width: {{ $ag['percentage'] }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Sebaran Universitas Asal -->
                <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-xs flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                            <div class="flex items-center gap-2">
                                <span class="text-lg">🎓</span>
                                <div>
                                    <h4 class="font-bold text-sm text-gray-900">Distribusi Asal Kampus Surabaya</h4>
                                    <p class="text-xs text-gray-400">Sebaran asal perguruan tinggi mitra resmi MBKM</p>
                                </div>
                            </div>
                            @if($isSuperAdmin)
                                <a href="{{ route('admin.universities.index') }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-bold">Kelola Kampus →</a>
                            @endif
                        </div>

                        <div class="mt-4 space-y-4">
                            @foreach($universityStats as $un)
                                <div>
                                    <div class="flex items-center justify-between text-xs mb-1">
                                        <span class="font-bold text-gray-800">{{ $un['name'] }} ({{ $un['code'] }})</span>
                                        <div class="flex items-center gap-2 font-mono">
                                            <span class="text-purple-600 font-bold">{{ $un['count'] }} Mahasiswa</span>
                                            <span class="text-gray-400">({{ $un['percentage'] }}%)</span>
                                        </div>
                                    </div>
                                    <div class="w-full bg-gray-100 rounded-full h-2.5 overflow-hidden">
                                        <div class="bg-purple-600 h-2.5 rounded-full transition-all duration-500" style="width: {{ $un['percentage'] }}%"></div>
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
                <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-xs p-6">
                    <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                        <div class="flex items-center gap-2">
                            <span class="text-lg">📑</span>
                            <div>
                                <h4 class="font-bold text-sm text-gray-900">Pengajuan Magang Masuk Terbaru</h4>
                                <p class="text-xs text-gray-400">Pendaftar terbaru yang memerlukan verifikasi atau pemantauan</p>
                            </div>
                        </div>
                        <a href="{{ route('admin.applications.index') }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-bold">Lihat Semua ({{ $stats['total_students'] }}) →</a>
                    </div>

                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100 text-left text-xs">
                            <thead class="bg-gray-50/50 text-gray-500 font-bold">
                                <tr>
                                    <th class="py-2.5 px-3">Mahasiswa</th>
                                    <th class="py-2.5 px-3">Instansi / Unit</th>
                                    <th class="py-2.5 px-3">Status</th>
                                    <th class="py-2.5 px-3 text-right">Aksi</th>
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
                                            <div class="font-semibold text-indigo-900">{{ $app->unit?->agencyProfile?->agency_name ?? '-' }}</div>
                                            <div class="text-[11px] text-gray-500">{{ $app->unit?->name ?? '-' }}</div>
                                        </td>
                                        <td class="py-3 px-3">
                                            @if(in_array($app->status, ['pending', 'submitted']))
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">Menunggu</span>
                                            @elseif(in_array($app->status, ['accepted', 'verified']))
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800">Diterima</span>
                                            @elseif($app->status === 'rejected')
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">Ditolak</span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-700">{{ $app->status }}</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-3 text-right">
                                            <a href="{{ route('admin.applications.show', $app->id) }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">Detail →</a>
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
                <div class="bg-white rounded-2xl border border-gray-100 shadow-xs p-6 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                            <div class="flex items-center gap-2">
                                <span class="text-lg">📜</span>
                                <div>
                                    <h4 class="font-bold text-sm text-gray-900">Audit Trail Terkini</h4>
                                    <p class="text-xs text-gray-400">Riwayat aksi sistem real-time</p>
                                </div>
                            </div>
                            <a href="{{ route('admin.audit_logs.index') }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-bold">Semua →</a>
                        </div>

                        <div class="mt-4 space-y-3">
                            @forelse($recentAuditLogs as $log)
                                <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100 text-xs">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold text-slate-800 text-[11px]">{{ $log->action }}</span>
                                        <span class="text-[10px] text-slate-400 font-mono">{{ $log->created_at->diffForHumans() }}</span>
                                    </div>
                                    <div class="text-[11px] text-slate-600 mt-0.5">
                                        Oleh: <strong>{{ $log->user_name }}</strong> ({{ $log->user_role }})
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
