<x-app-layout>
    <x-control-center-style />
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

            <a href="{{ route('feedbacks.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs transition active:scale-95 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                <span>Buat Laporan Baru</span>
            </a>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Alert -->
            @if (session('success'))
                <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-2xl shadow-xs flex items-center justify-between text-emerald-900 text-sm font-medium">
                    <div class="flex items-center gap-2">
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            <!-- 1. Ringkasan Metrik Tiket (Sleek Connected Segmented Bar) -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-px bg-slate-200/80 rounded-2xl border border-slate-200/80 overflow-hidden shadow-xs">
                <a href="{{ route('admin.feedbacks.index', array_merge(request()->except(['status', 'page']), ['status' => ''])) }}" 
                   class="bg-white p-4 sm:p-5 hover:bg-slate-50/80 transition block group">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total Masukan</span>
                        <span class="text-xs font-semibold text-slate-400 group-hover:text-blue-600 transition">Semua</span>
                    </div>
                    <div class="mt-2 flex items-baseline gap-2">
                        <span class="text-2xl font-black text-slate-900">{{ $stats['total'] }}</span>
                        <span class="text-xs text-slate-400 font-medium">tiket</span>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">Seluruh tiket masuk</p>
                </a>

                <a href="{{ route('admin.feedbacks.index', array_merge(request()->except(['status', 'page']), ['status' => 'pending'])) }}" 
                   class="bg-white p-4 sm:p-5 hover:bg-slate-50/80 transition block group">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-amber-700 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-amber-500 shrink-0"></span>
                            <span>{{ \App\Enums\FeedbackStatus::PENDING->label() }}</span>
                        </span>
                        <span class="text-xs font-semibold text-slate-400 group-hover:text-amber-600 transition">Filter</span>
                    </div>
                    <div class="mt-2 flex items-baseline gap-2">
                        <span class="text-2xl font-black text-amber-600">{{ $stats['pending'] }}</span>
                        <span class="text-xs text-slate-400 font-medium">antrean</span>
                    </div>
                    <p class="text-[11px] text-slate-500 mt-1">{{ \App\Enums\FeedbackStatus::PENDING->description() }}</p>
                </a>

                <a href="{{ route('admin.feedbacks.index', array_merge(request()->except(['status', 'page']), ['status' => 'in_progress'])) }}" 
                   class="bg-white p-4 sm:p-5 hover:bg-slate-50/80 transition block group">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-blue-700 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-blue-500 shrink-0"></span>
                            <span>{{ \App\Enums\FeedbackStatus::IN_PROGRESS->label() }}</span>
                        </span>
                        <span class="text-xs font-semibold text-slate-400 group-hover:text-blue-600 transition">Filter</span>
                    </div>
                    <div class="mt-2 flex items-baseline gap-2">
                        <span class="text-2xl font-black text-blue-600">{{ $stats['in_progress'] }}</span>
                        <span class="text-xs text-slate-400 font-medium">proses</span>
                    </div>
                    <p class="text-[11px] text-slate-500 mt-1">{{ \App\Enums\FeedbackStatus::IN_PROGRESS->description() }}</p>
                </a>

                <a href="{{ route('admin.feedbacks.index', array_merge(request()->except(['status', 'page']), ['status' => 'resolved'])) }}" 
                   class="bg-white p-4 sm:p-5 hover:bg-slate-50/80 transition block group">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-700 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
                            <span>{{ \App\Enums\FeedbackStatus::RESOLVED->label() }}</span>
                        </span>
                        <span class="text-xs font-semibold text-slate-400 group-hover:text-emerald-600 transition">Filter</span>
                    </div>
                    <div class="mt-2 flex items-baseline gap-2">
                        <span class="text-2xl font-black text-emerald-600">{{ $stats['resolved'] }}</span>
                        <span class="text-xs text-slate-400 font-medium">selesai</span>
                    </div>
                    <p class="text-[11px] text-slate-500 mt-1">{{ \App\Enums\FeedbackStatus::RESOLVED->description() }}</p>
                </a>
            </div>

            <!-- 2. Navigasi Tab Status (Consistent with Admin Standard) -->
            @php
                $currStatus = request('status', '');
            @endphp
            <div class="cc-tabs-bare border-b border-slate-200">
                <nav class="cc-tabs flex flex-nowrap space-x-2 sm:space-x-4 overflow-x-auto pb-px scrollbar-none" aria-label="Tabs Status">
                    <a href="{{ route('admin.feedbacks.index', array_merge(request()->except(['status', 'page']), ['status' => ''])) }}" 
                       class="cc-tab whitespace-nowrap py-3 px-3 sm:px-4 border-b-2 text-xs sm:text-sm flex items-center gap-2 transition cursor-pointer {{ $currStatus === '' ? 'cc-active border-blue-600 text-blue-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 font-semibold' }}">
                        <span>Semua Masukan</span>
                        <span class="cc-count">{{ $stats['total'] }}</span>
                    </a>
                    <a href="{{ route('admin.feedbacks.index', array_merge(request()->except(['status', 'page']), ['status' => 'pending'])) }}" 
                       class="cc-tab whitespace-nowrap py-3 px-3 sm:px-4 border-b-2 text-xs sm:text-sm flex items-center gap-2 transition cursor-pointer {{ $currStatus === 'pending' ? 'cc-active border-blue-600 text-blue-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 font-semibold' }}">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 shrink-0"></span>
                        <span>{{ \App\Enums\FeedbackStatus::PENDING->description() }}</span>
                        <span class="cc-count">{{ $stats['pending'] }}</span>
                    </a>
                    <a href="{{ route('admin.feedbacks.index', array_merge(request()->except(['status', 'page']), ['status' => 'in_progress'])) }}" 
                       class="cc-tab whitespace-nowrap py-3 px-3 sm:px-4 border-b-2 text-xs sm:text-sm flex items-center gap-2 transition cursor-pointer {{ $currStatus === 'in_progress' ? 'cc-active border-blue-600 text-blue-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 font-semibold' }}">
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500 shrink-0"></span>
                        <span>{{ \App\Enums\FeedbackStatus::IN_PROGRESS->description() }}</span>
                        <span class="cc-count">{{ $stats['in_progress'] }}</span>
                    </a>
                    <a href="{{ route('admin.feedbacks.index', array_merge(request()->except(['status', 'page']), ['status' => 'resolved'])) }}" 
                       class="cc-tab whitespace-nowrap py-3 px-3 sm:px-4 border-b-2 text-xs sm:text-sm flex items-center gap-2 transition cursor-pointer {{ $currStatus === 'resolved' ? 'cc-active border-blue-600 text-blue-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 font-semibold' }}">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                        <span>{{ \App\Enums\FeedbackStatus::RESOLVED->description() }}</span>
                        <span class="cc-count">{{ $stats['resolved'] }}</span>
                    </a>
                </nav>
            </div>

            <!-- 3. Form Pencarian & Kategori Tiket (Sleek Minimalist Form) -->
            <div class="bg-white rounded-2xl p-4 sm:p-5 shadow-xs border border-slate-200/80">
                <form method="GET" action="{{ route('admin.feedbacks.index') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 sm:gap-3">
                    @if(request('status'))
                        <input type="hidden" name="status" value="{{ request('status') }}">
                    @endif

                    <div class="relative flex-1 min-w-0">
                        <div class="absolute inset-y-0 left-0 flex items-center pointer-events-none pl-3.5 text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}" 
                               placeholder="Cari subjek, isi pesan, nama pengirim, atau email..."
                               class="w-full text-xs sm:text-sm bg-slate-50 focus:bg-white border-0 ring-1 ring-slate-200 focus:ring-2 focus:ring-blue-600 rounded-xl pl-9 pr-3.5 py-2.5 transition">
                    </div>

                    <select name="category" onchange="this.form.submit()"
                            class="text-xs sm:text-sm bg-slate-50 focus:bg-white border-0 ring-1 ring-slate-200 focus:ring-2 focus:ring-blue-600 rounded-xl py-2.5 px-3.5 font-medium w-full sm:w-56 transition">
                        <option value="">Semua Kategori</option>
                        <option value="error_bug" {{ request('category') === 'error_bug' ? 'selected' : '' }}>Bug / Kendala Teknis</option>
                        <option value="saran_fitur" {{ request('category') === 'saran_fitur' ? 'selected' : '' }}>Saran Fitur</option>
                        <option value="pertanyaan" {{ request('category') === 'pertanyaan' ? 'selected' : '' }}>Pertanyaan</option>
                        <option value="koordinasi" {{ request('category') === 'koordinasi' ? 'selected' : '' }}>Koordinasi Magang</option>
                        <option value="laporan_chat" {{ request('category') === 'laporan_chat' ? 'selected' : '' }}>Laporan Pesan Chat</option>
                    </select>

                    <div class="flex items-center gap-2">
                        <button type="submit" class="flex-1 sm:flex-initial px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-bold rounded-xl shadow-xs transition cursor-pointer">
                            Filter
                        </button>
                        @if (request()->hasAny(['search', 'category', 'status']))
                            <a href="{{ route('admin.feedbacks.index') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs sm:text-sm font-semibold rounded-xl transition text-center">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- 4. Daftar Tiket & Umpan Balik (Clean Unified List Container) -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs divide-y divide-slate-100 overflow-hidden">
                @forelse($feedbacks as $fb)
                    @php
                        $catBadge = match($fb->category) {
                            'error_bug', 'laporan_chat' => 'bg-rose-50 text-rose-700 border-rose-200/60',
                            'saran_fitur' => 'bg-blue-50 text-blue-700 border-blue-200/60',
                            'pertanyaan' => 'bg-amber-50 text-amber-800 border-amber-200/60',
                            'koordinasi' => 'bg-purple-50 text-purple-700 border-purple-200/60',
                            default => 'bg-slate-100 text-slate-700 border-slate-200/60',
                        };

                        $catLabel = match($fb->category) {
                            'error_bug' => 'Bug / Error',
                            'saran_fitur' => 'Saran Fitur',
                            'pertanyaan' => 'Pertanyaan',
                            'koordinasi' => 'Koordinasi',
                            'laporan_chat' => 'Laporan Chat',
                            default => 'Masukan',
                        };
                    @endphp

                    <div class="group p-4 sm:p-5 hover:bg-slate-50/70 transition flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                        <div class="space-y-2 min-w-0 flex-1">
                            <!-- Badges row -->
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="px-2.5 py-0.5 rounded-md text-[11px] font-bold border {{ $catBadge }}">
                                    {{ $catLabel }}
                                </span>

                                <x-status-badge type="feedback" :status="$fb->status" />

                                @if($fb->priority === 'urgent' || $fb->priority === 'high')
                                    <span class="px-2 py-0.5 bg-rose-50 text-rose-700 border border-rose-200/70 rounded-md text-[10px] font-bold uppercase tracking-wider">
                                        Prioritas: {{ $fb->priority }}
                                    </span>
                                @endif

                                <span class="text-xs text-slate-400 font-medium">
                                    • {{ $fb->created_at->diffForHumans() }}
                                </span>
                            </div>

                            <!-- Subject Title -->
                            <h3 class="font-bold text-sm sm:text-base text-slate-900 group-hover:text-blue-600 transition leading-snug">
                                <a href="{{ route('admin.feedbacks.show', $fb->id) }}" class="hover:underline">
                                    {{ $fb->subject }}
                                </a>
                            </h3>

                            <!-- Message excerpt -->
                            <p class="text-xs text-slate-600 line-clamp-2 leading-relaxed">
                                {{ $fb->message }}
                            </p>

                            <!-- Metadata row -->
                            <div class="flex flex-wrap items-center gap-y-1 gap-x-3 text-xs text-slate-500 pt-0.5">
                                <span class="inline-flex items-center gap-1.5">
                                    <span>Pengirim:</span>
                                    <strong class="text-slate-800">{{ $fb->sender_name }}</strong>
                                    <span class="px-1.5 py-0.2 bg-slate-100 text-slate-600 rounded text-[10px] font-semibold">{{ strtoupper($fb->sender_role) }}</span>
                                </span>
                                @if($fb->targetAgency)
                                    <span>• Dinas: {{ $fb->targetAgency->agency_name }}</span>
                                @endif
                                @if($fb->targetUniversity)
                                    <span>• Kampus: {{ $fb->targetUniversity->name }}</span>
                                @endif
                                @if($fb->attachment)
                                    <span class="text-blue-600 font-semibold inline-flex items-center gap-1">
                                        • Ada Lampiran Berkas
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Action Button -->
                        <div class="shrink-0 self-start sm:self-center">
                            <a href="{{ route('admin.feedbacks.show', $fb->id) }}" 
                               class="inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-white hover:bg-slate-50 text-slate-700 hover:text-blue-600 border border-slate-200/80 rounded-xl text-xs font-bold shadow-2xs group-hover:border-blue-300 transition cursor-pointer">
                                <span>Buka Tiket</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="py-16 px-4 text-center space-y-2">
                        <div class="w-12 h-12 mx-auto rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                            </svg>
                        </div>
                        <h4 class="font-bold text-sm text-slate-800">Tidak Ada Tiket Masukan</h4>
                        <p class="text-xs text-slate-400 max-w-sm mx-auto">
                            @if(request()->hasAny(['search', 'category', 'status']))
                                Tidak ada masukan yang sesuai dengan kriteria filter saat ini. Coba ubah kata kunci atau reset filter.
                            @else
                                Seluruh masukan dan laporan kendala teknis dari pengguna akan tercatat di sini.
                            @endif
                        </p>
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
