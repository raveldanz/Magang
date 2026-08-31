<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-black text-xl sm:text-2xl text-gray-900 tracking-tight flex items-center gap-2">
                    <span></span>
                    <span>Audit Trail & Log Riwayat Aktivitas Sistem</span>
                </h2>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">
                    Pencatatan real-time seluruh aksi mutasi status, impersonasi, reset password, dan manipulasi data
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Filter & Search Card -->
            <div class="bg-white rounded-2xl border border-gray-100 p-4 shadow-xs">
                <form method="GET" action="{{ route('admin.audit_logs.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="relative sm:col-span-2">
                        <div class="absolute inset-y-0 left-0 flex items-center pointer-events-none text-slate-400" style="padding-left: 1rem !important;">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama pengguna, target data, detail aksi, atau IP address..." 
                               class="w-full text-xs border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs"
                               style="padding-left: 2.75rem !important; padding-right: 1rem !important; padding-top: 0.6rem !important; padding-bottom: 0.6rem !important;">
                    </div>

                    <div class="flex items-center gap-2">
                        <select name="action" class="w-full py-2 text-xs border-gray-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs font-medium">
                            <option value="">Semua Jenis Aksi</option>
                            @foreach($actions as $act)
                                <option value="{{ $act }}" {{ request('action') === $act ? 'selected' : '' }}>
                                    {{ $act }}
                                </option>
                            @endforeach
                        </select>

                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-sm transition shrink-0 cursor-pointer">
                            Filter
                        </button>

                        @if(request()->hasAny(['search', 'action', 'user_id']))
                            <a href="{{ route('admin.audit_logs.index') }}" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-bold rounded-xl transition shrink-0">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Logs Table -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 text-left text-xs">
                        <thead class="bg-gray-50/75 text-gray-500 font-bold uppercase tracking-wider text-[11px]">
                            <tr>
                                <th class="py-3.5 px-4">Waktu (WIB)</th>
                                <th class="py-3.5 px-4">Aktor / Pengguna</th>
                                <th class="py-3.5 px-4">Jenis Aksi</th>
                                <th class="py-3.5 px-4">Target Entitas</th>
                                <th class="py-3.5 px-4">Rincian / Metadata</th>
                                <th class="py-3.5 px-4 text-right">IP Address</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($logs as $log)
                                <tr class="hover:bg-slate-50/80 transition">
                                    
                                    <!-- Timestamp -->
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        <div class="font-bold text-gray-900">{{ $log->created_at->format('d/m/Y H:i:s') }}</div>
                                        <div class="text-[10px] text-gray-400 font-mono">{{ $log->created_at->diffForHumans() }}</div>
                                    </td>

                                    <!-- User Actor -->
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        <div class="font-bold text-gray-900">{{ $log->user_name }}</div>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 uppercase">
                                            {{ $log->user_role }}
                                        </span>
                                    </td>

                                    <!-- Action -->
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        @if(str_contains($log->action, 'IMPERSONATE'))
                                            <span class="px-2.5 py-1 rounded-full text-[11px] font-black bg-amber-100 text-amber-800 border border-amber-300">
                                                 {{ $log->action }}
                                            </span>
                                        @elseif(str_contains($log->action, 'DELETE'))
                                            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                                 {{ $log->action }}
                                            </span>
                                        @elseif(str_contains($log->action, 'CREATE'))
                                            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                 {{ $log->action }}
                                            </span>
                                        @elseif(str_contains($log->action, 'EVALUATION'))
                                            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-blue-100 text-blue-800 border border-blue-200">
                                                 {{ $log->action }}
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-blue-100 text-blue-800 border border-blue-200">
                                                 {{ $log->action }}
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Target -->
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        @if($log->target_type)
                                            <div class="font-semibold text-gray-800">{{ $log->target_type }}</div>
                                            <div class="text-[10px] text-gray-400 font-mono">ID: #{{ $log->target_id ?? '-' }}</div>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>

                                    <!-- Details -->
                                    <td class="py-3.5 px-4">
                                        <div class="text-[11px] text-gray-600 max-w-md font-mono bg-slate-50 p-2 rounded-lg border border-slate-100 break-words">
                                            {{ $log->details ?? 'Tidak ada data detail' }}
                                        </div>
                                    </td>

                                    <!-- IP -->
                                    <td class="py-3.5 px-4 text-right whitespace-nowrap font-mono text-[11px] text-gray-500">
                                        {{ $log->ip_address ?? '127.0.0.1' }}
                                    </td>

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-12 text-center text-gray-400">
                                        Belum ada riwayat aktivitas yang tercatat.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($logs->hasPages())
                    <div class="p-4 border-t border-gray-100 bg-gray-50/50">
                        {{ $logs->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
