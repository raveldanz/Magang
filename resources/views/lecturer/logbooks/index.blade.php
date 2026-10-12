<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-xl sm:text-2xl text-slate-900 leading-tight">
                Logbook Mahasiswa Bimbingan
            </h2>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-5">

            <!-- Flash Success Message -->
            @if (session('success'))
                <div class="p-4 bg-emerald-50 rounded-2xl flex items-center justify-between text-emerald-900 text-sm font-medium">
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <!-- Status Tabs & Filter Controls (Unified & Borderless) -->
            <div class="space-y-3">
                <!-- Status Pills (2x2 Grid on Mobile, Flex on Desktop - Zero Horizontal Scroll) -->
                @php
                    $currStatus = request('status', '');
                @endphp
                <div class="grid grid-cols-2 sm:flex sm:items-center gap-2">
                    <a href="{{ route('lecturer.logbooks.index', array_merge(request()->except('status'), ['status' => ''])) }}"
                       class="px-3.5 py-2.5 text-xs font-bold rounded-xl transition flex items-center justify-between sm:justify-start gap-1.5 {{ $currStatus === '' ? 'bg-blue-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-50 shadow-2xs' }}">
                        <span>Semua</span>
                    </a>
                    <a href="{{ route('lecturer.logbooks.index', array_merge(request()->except('status'), ['status' => 'pending'])) }}"
                       class="px-3.5 py-2.5 text-xs font-bold rounded-xl transition flex items-center justify-between sm:justify-start gap-1.5 {{ $currStatus === 'pending' ? 'bg-amber-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-50 shadow-2xs' }}">
                        <span>Menunggu</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] {{ $currStatus === 'pending' ? 'bg-amber-700 text-white' : 'bg-amber-50 text-amber-800' }}">{{ $pendingCount }}</span>
                    </a>
                    <a href="{{ route('lecturer.logbooks.index', array_merge(request()->except('status'), ['status' => 'approved'])) }}"
                       class="px-3.5 py-2.5 text-xs font-bold rounded-xl transition flex items-center justify-between sm:justify-start gap-1.5 {{ $currStatus === 'approved' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-50 shadow-2xs' }}">
                        <span>Disetujui</span>{{-- status-guard:ignore --}}
                        <span class="px-2 py-0.5 rounded-full text-[10px] {{ $currStatus === 'approved' ? 'bg-emerald-700 text-white' : 'bg-emerald-50 text-emerald-800' }}">{{ $approvedCount }}</span>
                    </a>
                    <a href="{{ route('lecturer.logbooks.index', array_merge(request()->except('status'), ['status' => 'rejected'])) }}"
                       class="px-3.5 py-2.5 text-xs font-bold rounded-xl transition flex items-center justify-between sm:justify-start gap-1.5 {{ $currStatus === 'rejected' ? 'bg-rose-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-50 shadow-2xs' }}">
                        <span>Perlu Revisi</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] {{ $currStatus === 'rejected' ? 'bg-rose-700 text-white' : 'bg-rose-50 text-rose-800' }}">{{ $rejectedCount }}</span>
                    </a>
                </div>

                <!-- Filter Controls: Mahasiswa & Search -->
                <div class="bg-white rounded-2xl p-4 sm:p-5 shadow-xs">
                    <form method="GET" action="{{ route('lecturer.logbooks.index') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 sm:gap-3">
                        @if(request('status'))
                            <input type="hidden" name="status" value="{{ request('status') }}">
                        @endif

                        <div class="w-full sm:w-72">
                            <select name="placement_id" onchange="this.form.submit()" 
                                    class="w-full text-xs sm:text-sm bg-slate-50 focus:bg-white border-0 ring-1 ring-slate-200 focus:ring-2 focus:ring-blue-600 rounded-xl py-2.5 px-3.5 font-medium transition">
                                <option value="">Semua Mahasiswa</option>
                                @foreach ($supervisedPlacements as $pl)
                                    @php
                                        $st = $pl->application?->user;
                                        $nim = $st?->studentProfile?->nim ?? '-';
                                    @endphp
                                    <option value="{{ $pl->id }}" {{ request('placement_id') == $pl->id ? 'selected' : '' }}>
                                        {{ $st?->name ?? 'Mahasiswa' }} ({{ $nim }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="relative flex-1 min-w-0">
                            <div class="absolute inset-y-0 left-0 flex items-center pointer-events-none pl-3.5 text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari isi kegiatan atau topik logbook..."
                                   class="w-full text-xs sm:text-sm bg-slate-50 focus:bg-white border-0 ring-1 ring-slate-200 focus:ring-2 focus:ring-blue-600 rounded-xl pl-9 pr-3.5 py-2.5 transition">
                        </div>

                        <div class="flex items-center gap-2">
                            <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-bold rounded-xl transition cursor-pointer shadow-xs">
                                Cari
                            </button>
                            @if (request()->filled('search') || request()->filled('placement_id') || request()->filled('status'))
                                <a href="{{ route('lecturer.logbooks.index') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs sm:text-sm font-semibold rounded-xl transition text-center">
                                    Reset
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            <!-- Daftar Paket Mingguan (Borderless, Thoughtful Review UX) -->
            <x-supervision.logbook-weekly
                :bundles="$weeklyBundles"
                :reviewRoute="route('lecturer.logbooks.bulk_approve')"
                :showBulkSelect="false"
                role="lecturer"
                emptyMessage="Tidak ada aktivitas logbook mingguan yang perlu ditinjau untuk filter ini."
            />

        </div>
    </div>
</x-app-layout>
