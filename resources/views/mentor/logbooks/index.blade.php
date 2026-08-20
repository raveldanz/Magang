<x-app-layout>
    <div class="py-8 bg-[#F5F8FC] min-h-screen text-slate-900 font-sans">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Page Header -->
            <div class="flex items-center gap-3">
                <a href="{{ route('mentor.dashboard') }}" class="p-2.5 bg-white hover:bg-slate-50 border border-slate-200 text-slate-600 rounded-xl transition shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                </a>
                <div>
                    <h2 class="text-xl font-bold tracking-tight text-slate-900">
                        Semua Logbook Mahasiswa Bimbingan
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Tinjau seluruh aktivitas harian mahasiswa di unit kerja Anda
                    </p>
                </div>
            </div>

            <!-- Flash Alert -->
            @if (session('success'))
                <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center gap-3 text-emerald-800 text-sm font-semibold shadow-sm">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <!-- Filter & Search Bar -->
            <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-100 shadow-sm shadow-slate-200/50 flex flex-col md:flex-row justify-between items-center gap-4">
                
                <!-- Status Filter Pills -->
                <div class="flex flex-wrap items-center gap-2 w-full md:w-auto">
                    <a href="{{ route('mentor.logbooks.index') }}" 
                       class="px-3.5 py-1.5 rounded-full text-xs font-semibold uppercase tracking-wider transition-all duration-200 {{ !request('status') ? 'bg-blue-600 text-white shadow-sm shadow-blue-200' : 'bg-slate-50 text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
                        Semua Status
                    </a>
                    <a href="{{ route('mentor.logbooks.index', ['status' => 'pending']) }}" 
                       class="px-3.5 py-1.5 rounded-full text-xs font-semibold uppercase tracking-wider transition-all duration-200 {{ request('status') === 'pending' ? 'bg-amber-500 text-white shadow-sm' : 'bg-amber-50 text-amber-700 border border-amber-200 hover:bg-amber-100' }}">
                        Pending Review
                    </a>
                    <a href="{{ route('mentor.logbooks.index', ['status' => 'approved']) }}" 
                       class="px-3.5 py-1.5 rounded-full text-xs font-semibold uppercase tracking-wider transition-all duration-200 {{ request('status') === 'approved' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100' }}">
                        Approved
                    </a>
                    <a href="{{ route('mentor.logbooks.index', ['status' => 'rejected']) }}" 
                       class="px-3.5 py-1.5 rounded-full text-xs font-semibold uppercase tracking-wider transition-all duration-200 {{ request('status') === 'rejected' ? 'bg-red-600 text-white shadow-sm' : 'bg-red-50 text-red-700 border border-red-200 hover:bg-red-100' }}">
                        Rejected
                    </a>
                </div>

                <!-- Search Form -->
                <form method="GET" action="{{ route('mentor.logbooks.index') }}" class="w-full md:w-72 flex items-center gap-2">
                    @if (request('status'))
                        <input type="hidden" name="status" value="{{ request('status') }}">
                    @endif
                    <div class="relative w-full">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama mahasiswa..." class="w-full text-xs border border-slate-200 bg-slate-50 text-slate-900 rounded-xl pl-9 pr-3 py-2 placeholder-slate-400 focus:bg-white focus:ring-2 focus:ring-blue-400/40 focus:border-blue-400 transition-all duration-200">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </div>
                    <button type="submit" class="px-3.5 py-2 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-xs font-semibold uppercase tracking-wider rounded-xl shadow-sm shadow-blue-200 transition">
                        Cari
                    </button>
                </form>

            </div>

            <!-- List Logbook Cards -->
            <div class="space-y-4">
                @forelse ($logbooks as $log)
                    @php
                        $student = $log->placement->application->user ?? null;
                        $unit = $log->placement->application->unit ?? null;
                        $st = strtolower($log->status ?? '');
                    @endphp
                    <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-100 shadow-sm shadow-slate-200/50 space-y-4 transition-all duration-200 hover:scale-[1.005]">
                        
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 border-b border-slate-100 pb-3">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-blue-50 text-blue-600 font-bold text-xs flex items-center justify-center">
                                    {{ strtoupper(substr($student->name ?? 'M', 0, 2)) }}
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900 text-sm leading-snug">{{ $student->name ?? '-' }}</div>
                                    <div class="text-xs text-slate-400 mt-0.5">{{ $unit->name ?? '-' }} &bull; Tanggal: {{ \Carbon\Carbon::parse($log->date)->translatedFormat('l, d F Y') }}</div>
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                <span class="px-3 py-1 text-xs font-bold rounded-full 
                                    {{ $log->status === 'approved' ? 'bg-emerald-100 text-emerald-800' : '' }}
                                    {{ $log->status === 'pending' ? 'bg-amber-100 text-amber-800' : '' }}
                                    {{ $log->status === 'rejected' ? 'bg-rose-100 text-rose-800' : '' }}">
                                    {{ strtoupper($log->status) }}
                                </span>
                                <a href="{{ route('mentor.logbooks.show', $log->id) }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">
                                    Detail & Review &rarr;
                                </a>
                            </div>
                        </div>

                        <!-- Activity Text -->
                        <div class="text-xs sm:text-sm text-slate-700 leading-relaxed bg-slate-50/70 p-4 rounded-xl border border-slate-100">
                            {{ $log->activity }}
                        </div>

                        @if ($log->attachment)
                            <div>
                                <a href="{{ asset('storage/' . $log->attachment) }}" target="_blank" class="inline-flex items-center gap-1.5 text-xs text-blue-600 hover:text-blue-700 font-semibold bg-blue-50 border border-blue-100 px-3 py-1.5 rounded-xl">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" /></svg>
                                    Buka Lampiran Bukti
                                </a>
                            </div>
                        @endif

                        <!-- Form Approve / Reject Inline -->
                        <form action="{{ route('mentor.logbooks.updateStatus', $log->id) }}" method="POST" class="pt-3 border-t border-slate-100 grid grid-cols-1 sm:grid-cols-4 gap-2.5 items-center">
                            @csrf
                            @method('PUT')
                            
                            <div class="sm:col-span-3">
                                <input type="text" name="feedback" value="{{ $log->feedback }}" placeholder="Catatan/feedback perbaikan mentor..." class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 text-slate-900 px-3.5 py-2 placeholder-slate-400 focus:bg-white focus:ring-2 focus:ring-blue-400/40 focus:border-blue-400 transition-all duration-200">
                            </div>

                            <div class="flex gap-2">
                                <button type="submit" name="status" value="approved" class="w-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold uppercase tracking-wider rounded-xl transition shadow-sm">
                                    Approve
                                </button>
                                <button type="submit" name="status" value="rejected" class="w-full py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold uppercase tracking-wider rounded-xl transition shadow-sm">
                                    Reject
                                </button>
                            </div>
                        </form>

                    </div>
                @empty
                    <div class="bg-white rounded-2xl p-12 text-center text-slate-400 border border-slate-100">
                        <p class="font-medium text-slate-600">Tidak Ada Logbook Ditemukan</p>
                        <p class="text-xs text-slate-400 mt-1">Belum ada aktivitas mahasiswa sesuai filter pencarian yang dipilih.</p>
                    </div>
                @endforelse

                @if ($logbooks->hasPages())
                    <div class="p-4 bg-white rounded-2xl border border-slate-100 shadow-sm">
                        {{ $logbooks->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>