<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('mentor.dashboard') }}" class="p-2 bg-white hover:bg-gray-100 border border-gray-200 text-gray-700 rounded-xl transition shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <div>
                    <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                        Semua Logbook Mahasiswa Bimbingan
                    </h2>
                    <p class="text-xs sm:text-sm text-gray-500 mt-0.5">
                        Tinjau seluruh aktivitas harian mahasiswa di unit kerja Anda
                    </p>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Success Message -->
            @if (session('success'))
                <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-lg shadow-sm flex items-center justify-between text-emerald-900 text-sm font-medium">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            <!-- Filter & Search Bar -->
            <div class="bg-white rounded-2xl p-4 border border-gray-200 shadow-sm flex flex-col md:flex-row justify-between items-center gap-4">
                
                <!-- Status Filter Tabs -->
                <div class="flex flex-wrap items-center gap-2 w-full md:w-auto">
                    <a href="{{ route('mentor.logbooks.index') }}" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition {{ !request('status') ? 'bg-indigo-600 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        Semua Status
                    </a>
                    <a href="{{ route('mentor.logbooks.index', ['status' => 'pending']) }}" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition {{ request('status') === 'pending' ? 'bg-amber-500 text-white shadow-sm' : 'bg-amber-50 text-amber-800 hover:bg-amber-100' }}">
                        Pending Review
                    </a>
                    <a href="{{ route('mentor.logbooks.index', ['status' => 'approved']) }}" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition {{ request('status') === 'approved' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-emerald-50 text-emerald-800 hover:bg-emerald-100' }}">
                        Approved
                    </a>
                    <a href="{{ route('mentor.logbooks.index', ['status' => 'rejected']) }}" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition {{ request('status') === 'rejected' ? 'bg-rose-600 text-white shadow-sm' : 'bg-rose-50 text-rose-800 hover:bg-rose-100' }}">
                        Rejected
                    </a>
                </div>

                <!-- Search Form -->
                <form method="GET" action="{{ route('mentor.logbooks.index') }}" class="w-full md:w-72 flex items-center gap-2">
                    @if (request('status'))
                        <input type="hidden" name="status" value="{{ request('status') }}">
                    @endif
                    <div class="relative w-full">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama mahasiswa..." class="w-full text-xs border-gray-300 rounded-xl pl-9 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
                        <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <button type="submit" class="px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-sm transition">
                        Cari
                    </button>
                </form>

            </div>

            <!-- List Logbook Entries -->
            <div class="space-y-4">
                @forelse ($logbooks as $log)
                    @php
                        $student = $log->placement->application->user ?? null;
                        $unit = $log->placement->application->unit ?? null;
                    @endphp
                    <div class="bg-white rounded-2xl p-5 border border-gray-200 shadow-sm hover:border-indigo-200 transition space-y-3">
                        
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 border-b pb-3">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 font-black text-xs flex items-center justify-center">
                                    {{ strtoupper(substr($student->name ?? 'M', 0, 2)) }}
                                </div>
                                <div>
                                    <div class="font-bold text-gray-900 text-sm">{{ $student->name ?? '-' }}</div>
                                    <div class="text-xs text-gray-500">{{ $unit->name ?? '-' }} &bull; Tanggal: {{ \Carbon\Carbon::parse($log->date)->translatedFormat('l, d F Y') }}</div>
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
                        <div class="text-sm text-gray-700 leading-relaxed bg-slate-50 p-3.5 rounded-xl border border-gray-100">
                            {{ $log->activity }}
                        </div>

                        @if ($log->attachment)
                            <div class="pt-1">
                                <a href="{{ asset('storage/' . $log->attachment) }}" target="_blank" class="inline-flex items-center gap-1.5 text-xs text-indigo-600 hover:text-indigo-800 font-bold bg-indigo-50 px-3 py-1.5 rounded-lg">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                    </svg>
                                    Buka Lampiran Dokumen Bukti
                                </a>
                            </div>
                        @endif

                        <!-- Form Approve / Reject -->
                        <form action="{{ route('mentor.logbooks.updateStatus', $log->id) }}" method="POST" class="pt-3 border-t border-gray-100 grid grid-cols-1 sm:grid-cols-4 gap-3 items-center">
                            @csrf
                            @method('PUT')
                            
                            <div class="sm:col-span-3">
                                <input type="text" name="feedback" value="{{ $log->feedback }}" placeholder="Catatan/feedback mentor..." class="w-full text-xs border-gray-300 rounded-xl focus:ring-indigo-500 focus:border-indigo-500">
                            </div>

                            <div class="flex gap-2">
                                <button type="submit" name="status" value="approved" class="w-full px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition shadow-sm">
                                    Approve
                                </button>
                                <button type="submit" name="status" value="rejected" class="w-full px-3 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl transition shadow-sm">
                                    Reject
                                </button>
                            </div>
                        </form>

                    </div>
                @empty
                    <div class="bg-white rounded-2xl p-12 text-center text-gray-400 border border-gray-200">
                        <p class="font-medium text-gray-600">Tidak Ada Logbook Ditemukan</p>
                        <p class="text-xs text-gray-400 mt-1">Belum ada aktivitas mahasiswa sesuai filter pencarian yang dipilih.</p>
                    </div>
                @endforelse

                <!-- Pagination -->
                @if ($logbooks->hasPages())
                    <div class="p-4 bg-white rounded-2xl border border-gray-200">
                        {{ $logbooks->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
