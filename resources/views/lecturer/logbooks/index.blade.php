<x-app-layout>
    <x-control-center-style />
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

            <!-- Status Tabs & Filter Controls -->
            <div class="space-y-4">
                @php
                    $currStatus = request('status', '');
                @endphp
                <div class="border-b border-slate-200">
                    <nav class="flex items-center w-full sm:space-x-6 sm:w-auto" aria-label="Tabs">
                        <a href="{{ route('lecturer.logbooks.index', array_merge(request()->except('status'), ['status' => ''])) }}"
                           class="flex-1 sm:flex-initial py-3 px-1 border-b-2 text-xs sm:text-sm text-center flex items-center justify-center gap-1 sm:gap-2 transition cursor-pointer {{ $currStatus === '' ? 'border-blue-600 text-blue-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 font-semibold' }}">
                            <span class="sm:hidden">Semua</span>
                            <span class="hidden sm:inline">Semua Status</span>
                        </a>
                        <a href="{{ route('lecturer.logbooks.index', array_merge(request()->except('status'), ['status' => 'pending'])) }}"
                           class="flex-1 sm:flex-initial py-3 px-1 border-b-2 text-xs sm:text-sm text-center flex items-center justify-center gap-1 sm:gap-2 transition cursor-pointer {{ $currStatus === 'pending' ? 'border-blue-600 text-blue-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 font-semibold' }}">
                            <span class="sm:hidden">Menunggu</span>
                            <span class="hidden sm:inline">{{ \App\Enums\ReviewStatus::PENDING->description() }}</span>
                            <span class="px-1.5 sm:px-2 py-0.5 rounded-full text-[10px] sm:text-[11px] font-semibold {{ $currStatus === 'pending' ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-600' }}">{{ $pendingCount }}</span>
                        </a>
                        <a href="{{ route('lecturer.logbooks.index', array_merge(request()->except('status'), ['status' => 'approved'])) }}"
                           class="flex-1 sm:flex-initial py-3 px-1 border-b-2 text-xs sm:text-sm text-center flex items-center justify-center gap-1 sm:gap-2 transition cursor-pointer {{ $currStatus === 'approved' ? 'border-blue-600 text-blue-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 font-semibold' }}">
                            <span>{{ \App\Enums\ReviewStatus::APPROVED->description() }}</span>
                            <span class="px-1.5 sm:px-2 py-0.5 rounded-full text-[10px] sm:text-[11px] font-semibold {{ $currStatus === 'approved' ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-600' }}">{{ $approvedCount }}</span>
                        </a>
                        <a href="{{ route('lecturer.logbooks.index', array_merge(request()->except('status'), ['status' => 'rejected'])) }}"
                           class="flex-1 sm:flex-initial py-3 px-1 border-b-2 text-xs sm:text-sm text-center flex items-center justify-center gap-1 sm:gap-2 transition cursor-pointer {{ $currStatus === 'rejected' ? 'border-blue-600 text-blue-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 font-semibold' }}">
                            <span class="sm:hidden">Revisi</span> {{-- status-guard:ignore --}}
                            <span class="hidden sm:inline">{{ \App\Enums\ReviewStatus::REVISION->description() }}</span>
                            <span class="px-1.5 sm:px-2 py-0.5 rounded-full text-[10px] sm:text-[11px] font-semibold {{ $currStatus === 'rejected' ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-600' }}">{{ $rejectedCount }}</span>
                        </a>
                    </nav>
                </div>

                <!-- Filter Controls: Mahasiswa & Search -->
                <div class="bg-white rounded-2xl p-4 sm:p-5 shadow-xs border border-slate-200/80">
                    <form method="GET" action="{{ route('lecturer.logbooks.index') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 sm:gap-3">
                        @if(request('status'))
                            <input type="hidden" name="status" value="{{ request('status') }}">
                        @endif

                        <div class="w-full sm:w-64 sm:max-w-xs shrink-0">
                            <select name="placement_id" onchange="this.form.submit()" 
                                    class="w-full text-xs sm:text-sm bg-white border border-slate-200 focus:border-blue-600 focus:ring-1 focus:ring-blue-600 rounded-xl py-2.5 px-3.5 font-medium transition shadow-2xs truncate">
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
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari isi kegiatan atau topik logbook..."
                                   class="w-full text-xs sm:text-sm bg-white border border-slate-200 focus:border-blue-600 focus:ring-1 focus:ring-blue-600 rounded-xl pl-10 pr-3.5 py-2.5 transition shadow-2xs">
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            <button type="submit" class="flex-1 sm:flex-initial px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-bold rounded-xl transition cursor-pointer shadow-xs shrink-0">
                                Cari
                            </button>
                            @if (request()->filled('search') || request()->filled('placement_id') || request()->filled('status'))
                                <a href="{{ route('lecturer.logbooks.index') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs sm:text-sm font-semibold rounded-xl transition text-center shrink-0">
                                    Reset
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            <!-- One-Click Quick Approval Banner (Jika ada logbook yang telah di-ACC Mentor Lapangan) -->
            @if (($mentorApprovedPendingCount ?? 0) > 0)
                <div class="bg-gradient-to-r from-emerald-50/90 via-teal-50/70 to-emerald-50/90 border border-emerald-200/90 rounded-2xl p-4 sm:p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3.5 shadow-2xs">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0 shadow-2xs">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h4 class="text-xs sm:text-sm font-bold text-emerald-950">Validasi Sekali Klik (One-Click Approval)</h4>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-200/80 text-emerald-900">
                                    {{ $mentorApprovedPendingCount }} Logbook Telah Di-ACC Mentor
                                </span>
                            </div>
                            <p class="text-xs text-emerald-800/90 mt-1">
                                Seluruh entri ini telah diverifikasi oleh Mentor Instansi terkait dan menunggu validasi DPL kampus. Anda dapat menyetujui seluruhnya secara instan.
                            </p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('lecturer.logbooks.approve_all_mentor_reviewed') }}"
                          onsubmit="return confirm('Apakah Anda yakin ingin menyetujui seluruh {{ $mentorApprovedPendingCount }} logbook yang telah di-ACC mentor ini sekaligus?');">
                        @csrf
                        @if (request()->filled('placement_id'))
                            <input type="hidden" name="placement_id" value="{{ request('placement_id') }}">
                        @endif
                        <button type="submit"
                                class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition shadow-xs shrink-0 cursor-pointer active:scale-95">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            <span>Setujui Semua yang Di-ACC Mentor ({{ $mentorApprovedPendingCount }})</span>
                        </button>
                    </form>
                </div>
            @endif

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
