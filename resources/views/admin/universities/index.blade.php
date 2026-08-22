<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-black text-xl sm:text-2xl text-gray-900 tracking-tight flex items-center gap-2">
                    <span>🎓</span>
                    <span>Master Perguruan Tinggi Mitra (Universitas)</span>
                </h2>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">
                    Kelola profil kampus, PIC Rektorat/Fakultas, data logo, dan statistik mahasiswa magang
                </p>
            </div>

            <a href="{{ route('admin.universities.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-md transition active:scale-95 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
                <span>Tambah Universitas Baru</span>
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Alert -->
            @if (session('success'))
                <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-xl shadow-xs flex items-center justify-between text-emerald-900 text-sm font-medium">
                    <div class="flex items-center gap-2">
                        <span>✅</span>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="p-4 bg-rose-50 border-l-4 border-rose-500 rounded-r-xl shadow-xs flex items-center justify-between text-rose-900 text-sm font-medium">
                    <div class="flex items-center gap-2">
                        <span>⚠️</span>
                        <span>{{ session('error') }}</span>
                    </div>
                </div>
            @endif

            <!-- Search Card -->
            <div class="bg-white rounded-2xl border border-slate-100 p-4 shadow-2xs">
                <form method="GET" action="{{ route('admin.universities.index') }}" class="flex items-center gap-3">
                    <div class="relative flex-1">
                        <div class="absolute inset-y-0 left-0 flex items-center pointer-events-none text-slate-400" style="padding-left: 1rem !important;">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama perguruan tinggi, kode kampus (e.g. UNESA, ITS), email..." 
                               class="w-full text-xs sm:text-sm border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs"
                               style="padding-left: 2.75rem !important; padding-right: 1rem !important; padding-top: 0.65rem !important; padding-bottom: 0.65rem !important;">
                    </div>
                    <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-sm transition cursor-pointer">
                        Cari
                    </button>
                    @if(request('search'))
                        <a href="{{ route('admin.universities.index') }}" class="px-3 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-xl transition">
                            Reset
                        </a>
                    @endif
                </form>
            </div>

            <!-- Universities Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($universities as $univ)
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-xs hover:shadow-md transition p-6 flex flex-col justify-between">
                        <div>
                            <div class="flex items-start justify-between gap-4">
                                <div class="w-14 h-14 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-center p-2 shrink-0 overflow-hidden" style="width: 56px; height: 56px; min-width: 56px; min-height: 56px; max-width: 56px; max-height: 56px;">
                                    @if($univ->logo && file_exists(public_path($univ->logo)))
                                        <img src="{{ asset($univ->logo) }}" alt="{{ $univ->name }}" class="w-10 h-10 object-contain shrink-0" style="width: 40px; height: 40px; max-width: 40px; max-height: 40px; object-fit: contain;">
                                    @else
                                        <span class="text-2xl">🏛️</span>
                                    @endif
                                </div>
                                <span class="px-2.5 py-1 bg-purple-50 text-purple-700 border border-purple-200 rounded-full text-xs font-black shrink-0">
                                    {{ $univ->code }}
                                </span>
                            </div>

                            <div class="mt-4">
                                <h3 class="font-black text-base text-gray-900">{{ $univ->name }}</h3>
                                <p class="text-xs text-gray-500 mt-1 line-clamp-2">{{ $univ->address ?? 'Alamat belum diatur' }}</p>
                                @if($univ->pic_name)
                                    <div class="mt-2 text-[11px] text-indigo-700 font-semibold">
                                        👔 PIC: {{ $univ->pic_name }} ({{ $univ->pic_position ?? 'Pimpinan' }})
                                    </div>
                                @endif
                            </div>

                            <!-- Stat Counts -->
                            <div class="grid grid-cols-3 gap-2 mt-4 pt-4 border-t border-gray-100 text-center">
                                <div class="p-2 rounded-xl bg-purple-50 border border-purple-100">
                                    <div class="text-xs font-black text-purple-700">{{ $univ->students_count }}</div>
                                    <div class="text-[10px] text-purple-600 font-semibold uppercase">Mahasiswa</div>
                                </div>
                                <div class="p-2 rounded-xl bg-indigo-50 border border-indigo-100">
                                    <div class="text-xs font-black text-indigo-700">{{ $univ->dosens_count }}</div>
                                    <div class="text-[10px] text-indigo-600 font-semibold uppercase">Dosen DPL</div>
                                </div>
                                <div class="p-2 rounded-xl bg-slate-50 border border-slate-100">
                                    <div class="text-xs font-black text-slate-700">{{ $univ->users_count }}</div>
                                    <div class="text-[10px] text-slate-500 font-semibold uppercase">Akun</div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="mt-6 pt-4 border-t border-gray-100 flex items-center justify-between gap-2">
                            <a href="{{ route('admin.users.index', ['university_id' => $univ->id]) }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-bold">
                                Lihat Akun →
                            </a>

                            <div class="flex items-center gap-2">
                                <a href="{{ route('admin.universities.edit', $univ->id) }}" class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 rounded-lg text-xs font-bold transition">
                                    Edit
                                </a>

                                <form action="{{ route('admin.universities.destroy', $univ->id) }}" method="POST" onsubmit="return confirm('Hapus universitas {{ $univ->name }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-lg text-xs font-bold transition">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-12 text-center text-gray-400 bg-white rounded-2xl border border-gray-100">
                        <p class="font-bold text-gray-600">Belum ada universitas terdaftar</p>
                    </div>
                @endforelse
            </div>

        </div>
    </div>
</x-app-layout>
