<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-black text-xl sm:text-2xl text-gray-900 tracking-tight flex items-center gap-2">
                    <span>💬</span>
                    <span>Riwayat Masukan & Laporan Kendala Saya</span>
                </h2>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">
                    Pantau status tiket masukan dan tanggapan resmi dari tim pengelola
                </p>
            </div>

            <a href="{{ route('feedbacks.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-md transition active:scale-95 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                <span>Kirim Laporan Kendala</span>
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Alert -->
            @if (session('success'))
                <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-2xl shadow-xs flex items-center justify-between text-emerald-900 text-sm font-medium">
                    <div class="flex items-center gap-2">
                        <span>✅</span>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            <!-- Feedback Cards List -->
            <div class="space-y-4">
                @forelse($feedbacks as $fb)
                    @php
                        $catLabel = match($fb->category) {
                            'error_bug' => '⚠️ Kendala Sistem / Bug',
                            'saran_fitur' => '💡 Saran Fitur',
                            'pertanyaan' => '❓ Pertanyaan MBKM',
                            default => '📝 Masukan',
                        };

                        $statusBadge = match($fb->status) {
                            'pending' => 'bg-amber-100 text-amber-900 border-amber-300',
                            'in_progress' => 'bg-blue-100 text-blue-900 border-blue-300',
                            'resolved' => 'bg-emerald-100 text-emerald-900 border-emerald-300',
                            default => 'bg-slate-100 text-slate-700 border-slate-200',
                        };
                    @endphp

                    <div class="bg-white rounded-3xl border border-slate-100 p-6 shadow-xs space-y-4">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-50 pb-3">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-blue-50 text-blue-700 border border-blue-200">
                                    {{ $catLabel }}
                                </span>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $statusBadge }}">
                                    Status: {{ strtoupper($fb->status) }}
                                </span>
                            </div>
                            <span class="text-xs text-slate-400">
                                {{ $fb->created_at->diffForHumans() }}
                            </span>
                        </div>

                        <div>
                            <h3 class="font-extrabold text-base text-gray-900 mb-1">
                                {{ $fb->subject }}
                            </h3>
                            <p class="text-xs text-slate-600 leading-relaxed whitespace-pre-line">
                                {{ $fb->message }}
                            </p>
                        </div>

                        <!-- Response if available -->
                        @if($fb->admin_response)
                            <div class="p-4 rounded-2xl bg-emerald-50/70 border border-emerald-200 space-y-1.5">
                                <div class="flex items-center gap-2 text-xs font-bold text-emerald-900">
                                    <span>💬</span>
                                    <span>Tanggapan Resmi Pengelola ({{ $fb->responded_at ? $fb->responded_at->translatedFormat('d M Y, H:i') : '' }}):</span>
                                </div>
                                <p class="text-xs text-emerald-800 leading-relaxed whitespace-pre-line">
                                    {{ $fb->admin_response }}
                                </p>
                            </div>
                        @else
                            <div class="p-3 rounded-xl bg-amber-50/60 border border-amber-200 text-amber-800 text-[11px] font-medium flex items-center gap-2">
                                <span>⏳</span>
                                <span>Laporan Anda telah diterima dan sedang menunggu peninjauan tim pengelola.</span>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="py-16 text-center bg-white rounded-3xl border border-slate-100 p-8 space-y-3">
                        <div class="w-16 h-16 rounded-full bg-slate-50 text-slate-400 flex items-center justify-center text-3xl mx-auto">
                            💬
                        </div>
                        <h4 class="font-bold text-base text-gray-800">Belum Ada Masukan yang Dikirimkan</h4>
                        <p class="text-xs text-gray-400">Jika Anda menemukan kendala atau memiliki saran, silakan klik tombol kirim masukan di atas.</p>
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
