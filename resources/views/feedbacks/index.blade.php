<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-black text-xl sm:text-2xl text-gray-900 tracking-tight flex items-center gap-2">
                    
                    <span>Pusat Feedback & Tiket Kendala Sistem</span>
                </h2>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">
                    Kelola dan tanggapi laporan bug, saran fitur, dan koordinasi dari seluruh pengguna sistem
                </p>
            </div>

            <a href="{{ route('feedbacks.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-md transition active:scale-95 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                <span>Buat Laporan Baru</span>
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Alert -->
            @if (session('success'))
                <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-2xl shadow-xs flex items-center justify-between text-emerald-900 text-sm font-medium">
                    <div class="flex items-center gap-2">
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            <!-- 4 Metric Cards -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="p-5 bg-white rounded-3xl border border-slate-100 shadow-2xs">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-slate-400 text-xs uppercase font-bold tracking-wider">Total Masukan</span>
                    </div>
                    <div class="text-2xl font-black text-slate-900">{{ $stats['total'] }}</div>
                    <div class="text-[11px] text-slate-400 mt-1">Seluruh tiket masuk</div>
                </div>

                <div class="p-5 bg-white rounded-3xl border border-amber-200 shadow-2xs">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-amber-700 text-xs uppercase font-bold tracking-wider">Menunggu Respon</span>
                    </div>
                    <div class="text-2xl font-black text-amber-600">{{ $stats['pending'] }}</div>
                    <div class="text-[11px] text-amber-700 mt-1">Perlu ditanggapi admin</div>
                </div>

                <div class="p-5 bg-white rounded-3xl border border-blue-200 shadow-2xs">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-blue-700 text-xs uppercase font-bold tracking-wider">Sedang Diproses</span>
                    </div>
                    <div class="text-2xl font-black text-blue-600">{{ $stats['in_progress'] }}</div>
                    <div class="text-[11px] text-blue-700 mt-1">Investigasi / Pengerjaan</div>
                </div>

                <div class="p-5 bg-white rounded-3xl border border-emerald-200 shadow-2xs">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-emerald-700 text-xs uppercase font-bold tracking-wider">Selesai / Terjawab</span>
                    </div>
                    <div class="text-2xl font-black text-emerald-600">{{ $stats['resolved'] }}</div>
                    <div class="text-[11px] text-emerald-700 mt-1">Telah diselesaikan</div>
                </div>
            </div>

            <!-- Search & Filters Card -->
            <div class="bg-white rounded-3xl border border-slate-100 p-4 shadow-2xs">
                <form method="GET" action="{{ route('admin.feedbacks.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                    <div class="sm:col-span-2">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari subjek, isi pesan, nama pengirim..." 
                               class="w-full text-xs sm:text-sm border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs">
                    </div>

                    <div>
                        <select name="status" class="w-full text-xs sm:text-sm border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs">
                            <option value="">-- Semua Status --</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Menunggu (Pending)</option>
                            <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>Sedang Diproses</option>
                            <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Selesai (Resolved)</option>
                            <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Ditutup (Closed)</option>
                        </select>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="submit" class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs transition cursor-pointer">
                            Filter
                        </button>
                        @if(request()->hasAny(['search', 'status', 'category']))
                            <a href="{{ route('admin.feedbacks.index') }}" class="px-3 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-xl transition">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Feedback Items Grid -->
            <div class="space-y-4">
                @forelse($feedbacks as $fb)
                    @php
                        $catBadge = match($fb->category) {
                            'error_bug' => 'bg-rose-50 text-rose-700 border-rose-200',
                            'saran_fitur' => 'bg-blue-50 text-blue-700 border-blue-200',
                            'pertanyaan' => 'bg-amber-50 text-amber-700 border-amber-200',
                            default => 'bg-slate-50 text-slate-700 border-slate-200',
                        };

                        $catLabel = match($fb->category) {
                            'error_bug' => 'Bug / Error',
                            'saran_fitur' => 'Saran Fitur',
                            'pertanyaan' => 'Pertanyaan',
                            'koordinasi' => 'Koordinasi',
                            default => 'Masukan',
                        };

                        $statusBadge = match($fb->status) {
                            'pending' => 'bg-amber-100 text-amber-900 border-amber-300',
                            'in_progress' => 'bg-blue-100 text-blue-900 border-blue-300',
                            'resolved' => 'bg-emerald-100 text-emerald-900 border-emerald-300',
                            default => 'bg-slate-100 text-slate-700 border-slate-200',
                        };
                    @endphp

                    <div class="bg-white rounded-3xl border border-slate-100 p-6 shadow-xs hover:shadow-md transition flex flex-col sm:flex-row sm:items-center justify-between gap-5">
                        <div class="space-y-2 max-w-3xl">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black border {{ $catBadge }}">
                                    {{ $catLabel }}
                                </span>

                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $statusBadge }}">
                                    Status: {{ strtoupper($fb->status) }}
                                </span>

                                @if($fb->priority === 'urgent' || $fb->priority === 'high')
                                    <span class="px-2 py-0.5 bg-rose-100 text-rose-800 rounded-full text-[10px] font-black animate-pulse">
                                        Prioritas: {{ strtoupper($fb->priority) }}
                                    </span>
                                @endif

                                <span class="text-xs text-slate-400">
                                    • {{ $fb->created_at->diffForHumans() }}
                                </span>
                            </div>

                            <h3 class="font-extrabold text-base text-gray-900 leading-snug">
                                {{ $fb->subject }}
                            </h3>

                            <p class="text-xs text-slate-600 line-clamp-2 leading-relaxed">
                                {{ $fb->message }}
                            </p>

                            <div class="flex items-center gap-3 text-xs text-slate-500 pt-1">
                                <span>👤 <strong>{{ $fb->sender_name }}</strong> ({{ strtoupper($fb->sender_role) }})</span>
                                @if($fb->targetAgency)
                                    <span>Dinas: {{ $fb->targetAgency->agency_name }}</span>
                                @endif
                                @if($fb->attachment)
                                    <span class="text-blue-600 font-semibold">📎 Ada Lampiran</span>
                                @endif
                            </div>
                        </div>

                        <!-- Action -->
                        <div class="shrink-0">
                            <a href="{{ route('admin.feedbacks.show', $fb->id) }}" 
                               class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-2xl shadow-xs transition cursor-pointer">
                                <span>Buka & Tanggapi</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="py-16 text-center bg-white rounded-3xl border border-slate-100 p-8 space-y-3">
                        
                        <h4 class="font-bold text-base text-gray-800">Belum Ada Tiket Masukan</h4>
                        <p class="text-xs text-gray-400">Semua masukan dan kendala pengguna yang masuk akan terdata di sini.</p>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $feedbacks->links() }}
            </div>

        </div>
    </div>
</x-app-layout>
