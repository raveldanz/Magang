<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-gray-900 leading-tight">
            {{ __('Daftar Pengajuan Magang') }}
        </h2>
    </x-slot>

    <div class="py-5 sm:py-8 lg:py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            <!-- Active University Filter Indicator Banner -->
            @if(isset($selectedUniversity) && $selectedUniversity)
                <div class="p-4 bg-indigo-50/80 border border-indigo-200 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-indigo-900 shadow-2xs">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-indigo-600 text-white flex items-center justify-center shrink-0 shadow-xs">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
                        </div>
                        <div>
                            <span class="font-medium text-indigo-700">Filter Aktif Perguruan Tinggi:</span>
                            <h4 class="font-black text-sm text-indigo-950">{{ $selectedUniversity->name }} ({{ $selectedUniversity->code }})</h4>
                        </div>
                    </div>
                    <div class="flex items-center flex-wrap gap-2 shrink-0">
                        <a href="{{ route('admin.universities.show', $selectedUniversity->id) }}" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-xs transition">
                            Pusat Kendali Kampus
                        </a>
                        <a href="{{ route('admin.applications.index') }}" class="px-3 py-1.5 bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 font-bold rounded-xl transition">
                            Reset Filter
                        </a>
                    </div>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm rounded-2xl sm:rounded-3xl p-4 sm:p-6 border border-slate-100">
                
                <!-- Form Filter & Search (Responsive Single-Row Flex Layout) -->
                <form method="GET" action="{{ route('admin.applications.index') }}" class="mb-6 bg-gray-50 p-4 rounded-xl border border-gray-200">
                    <div class="flex flex-col lg:flex-row items-center gap-3">
                        <!-- Input Search (flex-1) -->
                        <div class="flex-1 w-full">
                            <x-text-input type="text" name="search" value="{{ request('search') }}" 
                                placeholder="Cari nama mahasiswa, NIM, atau universitas..." class="w-full text-sm h-10" />
                        </div>

                        <!-- Filter Universitas (w-52) -->
                        @if(isset($universities))
                            <div class="w-full lg:w-52">
                                <select name="university_id" class="w-full h-10 text-xs border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm">
                                    <option value="">-- Semua Kampus --</option>
                                    @foreach ($universities as $unv)
                                        <option value="{{ $unv->id }}" {{ request('university_id') == $unv->id ? 'selected' : '' }}>
                                            {{ $unv->name }} ({{ $unv->code }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <!-- Filter Instansi Dinas (Khusus Super Admin) -->
                        @if ($isSuperAdmin && isset($agencies) && count($agencies) > 0)
                            <div class="w-full lg:w-56">
                                <select name="agency_id" class="w-full h-10 text-xs border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm">
                                    <option value="">-- Semua Instansi Dinas --</option>
                                    @foreach ($agencies as $ag)
                                        <option value="{{ $ag->id }}" {{ request('agency_id') == $ag->id ? 'selected' : '' }}>
                                            {{ $ag->agency_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <!-- Filter Unit / Divisi (w-64) -->
                        <div class="w-full lg:w-64">
                            <select name="unit_id" class="w-full h-10 text-xs border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm">
                                <option value="">-- Semua Unit/Divisi --</option>
                                @if (isset($groupedUnits) && $groupedUnits !== null)
                                    @foreach ($groupedUnits as $agencyName => $agencyUnits)
                                        <optgroup label=" {{ $agencyName }}">
                                            @foreach ($agencyUnits as $u)
                                                <option value="{{ $u->id }}" {{ request('unit_id') == $u->id ? 'selected' : '' }}>
                                                    {{ $u->name }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                @elseif(isset($units))
                                    @foreach ($units as $u)
                                        <option value="{{ $u->id }}" {{ request('unit_id') == $u->id ? 'selected' : '' }}>
                                            {{ $u->name }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>

                        <!-- Filter Status (w-44) -->
                        <div class="w-full lg:w-44">
                            <select name="status" class="w-full h-10 text-xs border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm">
                                <option value="">-- Semua Status --</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>PENDING</option>
                                <option value="accepted" {{ request('status') == 'accepted' ? 'selected' : '' }}>ACCEPTED</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>COMPLETED</option>
                                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>REJECTED</option>
                            </select>
                        </div>

                        <!-- Action Buttons (Height 10 uniform) -->
                        <div class="flex items-center gap-2 w-full lg:w-auto shrink-0">
                            <button type="submit" class="inline-flex items-center justify-center gap-1.5 px-4 h-10 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-lg shadow-sm transition w-full lg:w-auto cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                <span>Filter</span>
                            </button>

                            @if(request('search') || request('status') || request('unit_id') || request('university_id') || request('agency_id'))
                                <a href="{{ route('admin.applications.index') }}" class="inline-flex items-center justify-center px-4 h-10 bg-gray-200 hover:bg-gray-300 text-gray-700 text-xs font-bold rounded-lg shadow-sm transition">
                                    Reset
                                </a>
                            @endif
                        </div>
                    </div>
                </form>

                <!-- Tabel Data Pengajuan (Desktop & Tablet) -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b bg-gray-50 text-xs font-semibold text-gray-600 uppercase">
                                <th class="p-3">Tanggal Pengajuan</th>
                                <th class="p-3">Nama Mahasiswa</th>
                                <th class="p-3">Universitas / Jurusan</th>
                                <th class="p-3">Instansi & Unit Tujuan</th>
                                <th class="p-3">Status</th>
                                <th class="p-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm divide-y">
                            @forelse ($applications as $app)
                                <tr class="border-b hover:bg-gray-50/50">
                                    <td class="p-3 text-xs text-gray-500 font-mono">
                                        {{ $app->created_at->format('d M Y, H:i') }}
                                    </td>
                                    <td class="p-3 font-semibold text-gray-900">
                                        <div class="leading-snug">{{ $app->user->name }}</div>
                                    </td>
                                    <td class="p-3 text-gray-600">{{ $app->user->studentProfile->universitas ?? '-' }} <br><span class="text-xs text-gray-400">({{ $app->user->studentProfile->jurusan ?? '-' }})</span></td>
                                    <td class="p-3">
                                        <div class="space-y-1">
                                            <div class="font-bold text-gray-900 leading-snug">
                                                {{ $app->unit->name ?? '-' }}
                                            </div>
                                            @if($app->unit?->agencyProfile)
                                                <div class="inline-flex items-center gap-1.5 px-2 py-0.5 bg-blue-50/90 border border-blue-200/80 rounded-md text-[11px] text-blue-800 font-semibold">
                                                    <svg class="w-3 h-3 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                                    </svg>
                                                    <span class="truncate max-w-[220px]" title="{{ $app->unit->agencyProfile->agency_name }}">
                                                        {{ $app->unit->agencyProfile->agency_name }}
                                                    </span>
                                                </div>
                                            @else
                                                <span class="text-[11px] text-gray-400">Pemerintah Kota Surabaya</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="p-3 whitespace-nowrap">
                                        @php
                                            $rawStatus = strtolower($app->status);
                                            $isReadyForGraduation = ($rawStatus === 'accepted' && $app->can_complete);
                                            $isWaitingEvaluation = ($rawStatus === 'accepted' && $app->has_approved_report && !$app->has_complete_evaluation);
                                        @endphp
                                        @if(in_array($rawStatus, ['pending', 'verified', 'submitted']))
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-bold rounded-full bg-amber-50 text-amber-700 border border-amber-300 shadow-2xs">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                                <span>PENDING</span>
                                            </span>
                                        @elseif($isReadyForGraduation)
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-bold rounded-full bg-purple-50 text-purple-700 border border-purple-300 shadow-2xs" title="Laporan & Nilai Lengkap">
                                                <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>
                                                <span>SIAP LULUS</span>
                                            </span>
                                        @elseif($isWaitingEvaluation)
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-bold rounded-full bg-blue-50 text-blue-700 border border-blue-300 shadow-2xs" title="Laporan disetujui, menunggu nilai evaluasi">
                                                <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                                <span>MENUNGGU NILAI</span>
                                            </span>
                                        @elseif($rawStatus === 'accepted')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-bold rounded-full bg-emerald-50 text-emerald-700 border border-emerald-300 shadow-2xs">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                <span>ACCEPTED</span>
                                            </span>
                                        @elseif($rawStatus === 'completed')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-bold rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200 shadow-2xs">
                                                <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                                                <span>COMPLETED</span>
                                            </span>
                                        @elseif($rawStatus === 'rejected')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-bold rounded-full bg-rose-50 text-rose-700 border border-rose-200 shadow-2xs">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                                <span>REJECTED</span>
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-bold rounded-full bg-slate-100 text-slate-700 border border-slate-200 shadow-2xs">
                                                <span class="w-1.5 h-1.5 rounded-full bg-slate-500"></span>
                                                <span>{{ strtoupper($app->status) }}</span>
                                            </span>
                                        @endif
                                    </td>
                                    <td class="p-3 text-center whitespace-nowrap">
                                        <a href="{{ route('admin.applications.show', $app->id) }}" 
                                           class="inline-flex items-center justify-center gap-1 px-3 py-1.5 bg-blue-50 hover:bg-blue-600 text-blue-700 hover:text-white text-xs font-bold rounded-xl border border-blue-200/80 transition duration-150 shadow-2xs cursor-pointer group">
                                            <span>Detail</span>
                                            <svg class="w-3.5 h-3.5 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                            </svg>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="p-6 text-center text-gray-500">Tidak ada pengajuan magang yang sesuai kriteria pencarian.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Kartu Data Pengajuan Khusus Mobile (< 768px) -->
                <div class="md:hidden space-y-3">
                    @forelse ($applications as $app)
                        @php
                            $rawStatus = strtolower($app->status);
                            $isReadyForGraduation = ($rawStatus === 'accepted' && $app->can_complete);
                            $isWaitingEvaluation = ($rawStatus === 'accepted' && $app->has_approved_report && !$app->has_complete_evaluation);
                        @endphp
                        <div class="p-4 bg-white rounded-2xl border border-slate-200/80 shadow-2xs space-y-3">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <h4 class="font-bold text-sm text-slate-900 leading-snug truncate">{{ $app->user->name }}</h4>
                                    <p class="text-xs text-slate-500 mt-0.5">{{ $app->user->studentProfile->universitas ?? '-' }} <span class="text-slate-400">({{ $app->user->studentProfile->jurusan ?? '-' }})</span></p>
                                </div>
                                <div class="text-right shrink-0">
                                    @if(in_array($rawStatus, ['pending', 'verified', 'submitted']))
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[11px] font-bold rounded-full bg-amber-50 text-amber-700 border border-amber-300 shadow-2xs">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                            <span>PENDING</span>
                                        </span>
                                    @elseif($isReadyForGraduation)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[11px] font-bold rounded-full bg-purple-50 text-purple-700 border border-purple-300 shadow-2xs" title="Laporan & Nilai Lengkap">
                                            <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>
                                            <span>SIAP LULUS</span>
                                        </span>
                                    @elseif($isWaitingEvaluation)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[11px] font-bold rounded-full bg-blue-50 text-blue-700 border border-blue-300 shadow-2xs" title="Laporan disetujui, menunggu nilai evaluasi">
                                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                            <span>MENUNGGU NILAI</span>
                                        </span>
                                    @elseif($rawStatus === 'accepted')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[11px] font-bold rounded-full bg-emerald-50 text-emerald-700 border border-emerald-300 shadow-2xs">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            <span>ACCEPTED</span>
                                        </span>
                                    @elseif($rawStatus === 'completed')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[11px] font-bold rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200 shadow-2xs">
                                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                                            <span>COMPLETED</span>
                                        </span>
                                    @elseif($rawStatus === 'rejected')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[11px] font-bold rounded-full bg-rose-50 text-rose-700 border border-rose-200 shadow-2xs">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                            <span>REJECTED</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[11px] font-bold rounded-full bg-slate-100 text-slate-700 border border-slate-200 shadow-2xs">
                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-500"></span>
                                            <span>{{ strtoupper($app->status) }}</span>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100 text-xs space-y-1.5">
                                <div class="flex items-start justify-between text-slate-600 gap-2">
                                    <span class="text-slate-400 shrink-0">Instansi & Unit:</span>
                                    <div class="text-right">
                                        <div class="font-bold text-slate-900">{{ $app->unit->name ?? '-' }}</div>
                                        @if($app->unit?->agencyProfile)
                                            <div class="text-[11px] text-blue-700 font-medium mt-0.5">{{ $app->unit->agencyProfile->agency_name }}</div>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex items-center justify-between text-slate-600">
                                    <span class="text-slate-400">Diajukan:</span>
                                    <span class="font-mono text-slate-500">{{ $app->created_at->format('d M Y, H:i') }}</span>
                                </div>
                            </div>

                            <a href="{{ route('admin.applications.show', $app->id) }}" class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-2.5 bg-blue-50 hover:bg-blue-600 text-blue-700 hover:text-white text-xs font-bold rounded-xl border border-blue-200/80 transition duration-150 shadow-2xs active:scale-95 cursor-pointer group">
                                <span>Lihat Detail Pengajuan</span>
                                <svg class="w-3.5 h-3.5 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </div>
                    @empty
                        <div class="p-6 bg-white rounded-2xl border border-slate-200 text-center text-xs text-slate-500">
                            Tidak ada pengajuan magang yang sesuai kriteria pencarian.
                        </div>
                    @endforelse
                </div>

                <!-- Paginasi -->
                <div class="mt-6">
                    {{ $applications->links() }}
                </div>

            </div>
        </div>
    </div>
</x-app-layout>