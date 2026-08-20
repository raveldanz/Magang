<x-app-layout>
    <div class="py-8 bg-[#F5F8FC] min-h-screen text-slate-900 font-sans">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Page Header -->
            <div>
                <h2 class="text-xl font-bold tracking-tight text-slate-900">
                    Daftar Pengajuan Magang
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Verifikasi berkas, pantau kelulusan, dan atur persetujuan mahasiswa pendaftar
                </p>
            </div>

            <!-- Form Filter & Search (Modern Single Row) -->
            <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-100 shadow-sm shadow-slate-200/50">
                <form method="GET" action="{{ route('admin.applications.index') }}">
                    <div class="flex flex-col lg:flex-row items-center gap-3">
                        
                        <!-- Input Search -->
                        <div class="flex-1 w-full relative">
                            <input type="text" 
                                   name="search" 
                                   value="{{ request('search') }}" 
                                   placeholder="Cari nama mahasiswa, NIM, atau universitas..." 
                                   class="w-full h-10 text-xs rounded-xl border border-slate-200 bg-slate-50 text-slate-900 px-3.5 pl-9 placeholder-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-400/40 focus:border-blue-400 transition-all duration-200" />
                            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>

                        <!-- Filter Unit / Divisi -->
                        <div class="w-full lg:w-64">
                            <select name="unit_id" class="w-full h-10 text-xs rounded-xl border border-slate-200 bg-slate-50 text-slate-900 px-3 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-400/40 focus:border-blue-400 transition-all duration-200">
                                <option value="">-- Semua Unit/Divisi --</option>
                                @if (isset($groupedUnits) && $groupedUnits !== null)
                                    @foreach ($groupedUnits as $agencyName => $agencyUnits)
                                        <optgroup label="🏛️ {{ $agencyName }}">
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

                        <!-- Filter Status -->
                        <div class="w-full lg:w-44">
                            <select name="status" class="w-full h-10 text-xs rounded-xl border border-slate-200 bg-slate-50 text-slate-900 px-3 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-400/40 focus:border-blue-400 transition-all duration-200">
                                <option value="">-- Semua Status --</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>PENDING</option>
                                <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>VERIFIED</option>
                                <option value="accepted" {{ request('status') == 'accepted' ? 'selected' : '' }}>ACCEPTED</option>
                                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>REJECTED</option>
                            </select>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center gap-2 w-full lg:w-auto shrink-0">
                            <button type="submit" class="inline-flex items-center justify-center gap-1.5 px-4 h-10 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-xs font-semibold uppercase tracking-wider rounded-xl shadow-sm shadow-blue-200 hover:scale-[1.01] active:scale-[0.99] transition-all duration-200 w-full lg:w-auto">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                <span>Filter</span>
                            </button>

                            @if(request('search') || request('status') || request('unit_id'))
                                <a href="{{ route('admin.applications.index') }}" class="inline-flex items-center justify-center px-4 h-10 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl border border-slate-200 transition-all duration-200">
                                    Reset
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>

            <!-- Main Table Card -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm shadow-slate-200/50 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50/60 text-xs font-semibold uppercase tracking-wider text-slate-500">
                                <th class="py-3.5 px-5">Tgl Pengajuan</th>
                                <th class="py-3.5 px-5">Mahasiswa</th>
                                <th class="py-3.5 px-5">Universitas / Jurusan</th>
                                <th class="py-3.5 px-5">Unit Tujuan</th>
                                <th class="py-3.5 px-5">Status</th>
                                <th class="py-3.5 px-5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            @forelse ($applications as $app)
                                <tr class="hover:bg-slate-50/70 transition-colors duration-150">
                                    <td class="py-4 px-5 text-xs text-slate-400 font-mono whitespace-nowrap">
                                        {{ $app->created_at->format('d M Y, H:i') }}
                                    </td>
                                    <td class="py-4 px-5">
                                        <div class="font-bold text-slate-900 leading-snug">{{ $app->user->name }}</div>
                                        @if ($app->status === 'accepted' && optional($app->placement)->evaluation && optional(optional($app->placement)->finalreport)->status === 'approved')
                                            <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-full mt-1 bg-purple-50 text-purple-700 border border-purple-200">
                                                🎉 SIAP CETAK SERTIFIKAT
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-5 text-slate-700 text-xs">
                                        <span class="font-medium text-slate-900">{{ $app->user->studentProfile->universitas ?? '-' }}</span>
                                        <div class="text-slate-400 mt-0.5">{{ $app->user->studentProfile->jurusan ?? '-' }}</div>
                                    </td>
                                    <td class="py-4 px-5 font-medium text-slate-800 text-xs">
                                        {{ $app->unit->name ?? '-' }}
                                    </td>
                                    <td class="py-4 px-5 whitespace-nowrap">
                                        @php $st = strtolower($app->status); @endphp
                                        <span class="inline-flex items-center px-2.5 py-1 text-xs font-bold rounded-full border
                                            {{ $st === 'accepted' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : '' }}
                                            {{ $st === 'pending' ? 'bg-amber-50 text-amber-700 border-amber-200' : '' }}
                                            {{ $st === 'rejected' ? 'bg-red-50 text-red-700 border-red-200' : '' }}
                                            {{ $st === 'verified' ? 'bg-blue-50 text-blue-700 border-blue-200' : '' }}">
                                            {{ strtoupper($app->status) }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-5 text-right whitespace-nowrap">
                                        <a href="{{ route('admin.applications.show', $app->id) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold text-blue-600 bg-blue-50 border border-blue-100 hover:bg-blue-100 transition-all duration-200">
                                            <span>Detail & Verifikasi</span>
                                            <span>&rarr;</span>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-12 text-center text-slate-400 text-sm">
                                        Tidak ada pengajuan magang yang sesuai kriteria pencarian.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Paginasi -->
                @if($applications->hasPages())
                    <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                        {{ $applications->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>