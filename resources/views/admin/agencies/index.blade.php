<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-black text-xl sm:text-2xl text-gray-900 tracking-tight flex items-center gap-2">
                    <svg class="w-6 h-6 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    <span>Master Instansi Dinas Pemerintah Kota Surabaya</span>
                </h2>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">
                    Kelola data instansi dinas resmi tempat pelaksanaan program magang MBKM
                </p>
            </div>

            @if($isSuperAdmin)
                <a href="{{ route('admin.agencies.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-md transition active:scale-95 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
                    <span>Tambah Instansi Baru</span>
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Alert -->
            @if (session('success'))
                <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-xl shadow-xs flex items-center justify-between text-emerald-900 text-sm font-medium">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="p-4 bg-rose-50 border-l-4 border-rose-500 rounded-r-xl shadow-xs flex items-center justify-between text-rose-900 text-sm font-medium">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>{{ session('error') }}</span>
                    </div>
                </div>
            @endif

            <!-- Search Bar -->
            <div class="bg-white rounded-2xl border border-slate-100 p-4 shadow-2xs">
                <form method="GET" action="{{ route('admin.agencies.index') }}" class="flex items-center gap-3">
                    <div class="relative flex-1">
                        <div class="absolute inset-y-0 left-0 flex items-center pointer-events-none text-slate-400" style="padding-left: 1rem !important;">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama instansi dinas, email, atau kota..." 
                               class="w-full text-xs sm:text-sm border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs"
                               style="padding-left: 2.75rem !important; padding-right: 1rem !important; padding-top: 0.65rem !important; padding-bottom: 0.65rem !important;">
                    </div>
                    <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-sm transition cursor-pointer">
                        Cari
                    </button>
                    @if(request('search'))
                        <a href="{{ route('admin.agencies.index') }}" class="px-3 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-xl transition">
                            Reset
                        </a>
                    @endif
                </form>
            </div>

            <!-- List Instansi Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($agencies as $agency)
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-xs hover:shadow-md transition p-6 flex flex-col justify-between">
                        <div>
                            <div class="flex items-start justify-between gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600 shrink-0">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                </div>
                                <span class="px-2.5 py-1 bg-slate-100 text-slate-700 border border-slate-200 rounded-full text-[11px] font-bold">
                                    {{ $agency->city ?? 'Surabaya' }}
                                </span>
                            </div>

                            <div class="mt-4">
                                <h3 class="font-black text-base text-gray-900">{{ $agency->agency_name }}</h3>
                                <p class="text-xs text-blue-600 font-semibold">{{ $agency->government_name }}</p>
                                <p class="text-xs text-gray-500 mt-2 line-clamp-2">{{ $agency->address }}</p>
                            </div>

                            <!-- Stat Pills -->
                            <div class="grid grid-cols-3 gap-2 mt-4 pt-4 border-t border-gray-100 text-center">
                                <div class="p-2 rounded-xl bg-slate-50 border border-slate-100">
                                    <div class="text-xs font-black text-gray-900">{{ $agency->units_count }}</div>
                                    <div class="text-[10px] text-gray-400 font-semibold uppercase">Unit Kerja</div>
                                </div>
                                <div class="p-2 rounded-xl bg-teal-50 border border-teal-100">
                                    <div class="text-xs font-black text-teal-700">{{ $agency->total_quota }}</div>
                                    <div class="text-[10px] text-teal-600 font-semibold uppercase">Sisa Kuota</div>
                                </div>
                                <div class="p-2 rounded-xl bg-blue-50 border border-blue-100">
                                    <div class="text-xs font-black text-blue-700">{{ $agency->total_mentors }}</div>
                                    <div class="text-[10px] text-blue-600 font-semibold uppercase">Mentor</div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="mt-6 pt-4 border-t border-gray-100 flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('admin.units.index', ['agency_id' => $agency->id]) }}" class="text-xs text-blue-600 hover:text-blue-800 font-bold">
                                    Lihat Unit 
                                </a>
                            </div>

                            @if($isSuperAdmin)
                                <div class="btn-action-group">
                                    <a href="{{ route('admin.agencies.edit', $agency->id) }}" class="btn-action-edit">
                                        Edit
                                    </a>

                                    <form action="{{ route('admin.agencies.destroy', $agency->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus instansi ini? Pastikan tidak ada unit atau user terkait.');" class="btn-action-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-action-delete">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-12 text-center text-gray-400 bg-white rounded-2xl border border-gray-100">
                        <p class="font-bold text-gray-600">Belum ada data instansi dinas</p>
                        <p class="text-xs mt-1">Gunakan tombol "Tambah Instansi Baru" untuk menambahkan dinas.</p>
                    </div>
                @endforelse
            </div>

        </div>
    </div>
</x-app-layout>
