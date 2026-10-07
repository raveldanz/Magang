<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.feedbacks.index') }}" class="p-2 rounded-xl bg-white border border-slate-200 text-slate-600 hover:text-slate-900 transition shadow-2xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <h2 class="font-black text-xl sm:text-2xl text-gray-900 tracking-tight">
                    Tiket Masukan #{{ $feedback->id }}
                </h2>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">
                    Detail laporan kendala, masukan pengguna, dan tanggapan resmi dari tim pengelola
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Alert -->
            @if (session('success'))
                <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-2xl shadow-xs flex items-center justify-between text-emerald-900 text-sm font-medium">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            <!-- Ticket Card Details -->
            <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-100 shadow-sm space-y-6">
                
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-5">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="px-3 py-1 rounded-full text-xs font-black bg-blue-50 text-blue-700 border border-blue-200">
                                {{ $feedback->category === \App\Models\SystemFeedback::CATEGORY_CHAT_REPORT ? 'LAPORAN PESAN CHAT' : strtoupper(str_replace('_', ' ', $feedback->category)) }}
                            </span>
                            <x-status-badge type="feedback" :status="$feedback->status" />
                            <span class="text-xs text-slate-400">
                                Dibuat {{ $feedback->created_at->translatedFormat('d F Y, H:i') }} ({{ $feedback->created_at->diffForHumans() }})
                            </span>
                        </div>
                        <h3 class="font-extrabold text-lg sm:text-xl text-gray-900 pt-1">
                            {{ $feedback->subject }}
                        </h3>
                    </div>

                    <div class="text-right shrink-0">
                        <span class="text-xs text-slate-400 block font-medium">Prioritas:</span>
                        <span class="font-bold text-xs uppercase px-2.5 py-1 rounded-lg {{ $feedback->priority === 'urgent' ? 'bg-rose-100 text-rose-800 font-black' : 'bg-slate-100 text-slate-700' }}">
                            {{ $feedback->priority }}
                        </span>
                    </div>
                </div>

                <!-- Sender Info Card -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 p-4 rounded-2xl bg-slate-50 border border-slate-100 text-xs">
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase font-bold mb-0.5">Nama Pengirim</span>
                        <span class="font-bold text-slate-800">{{ $feedback->sender_name }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase font-bold mb-0.5">Email Pengirim</span>
                        <span class="font-bold text-slate-800">{{ $feedback->sender_email }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase font-bold mb-0.5">Peran / Role</span>
                        <span class="font-bold text-blue-600 uppercase">{{ $feedback->sender_role }}</span>
                    </div>
                </div>

                <!-- Message Body -->
                <div class="space-y-2">
                    <h4 class="font-bold text-xs text-slate-400 uppercase tracking-wider">Isi Laporan / Pesan:</h4>
                    <div class="p-5 rounded-2xl bg-slate-50/70 border border-slate-100 text-slate-800 text-sm leading-relaxed whitespace-pre-line">
                        {{ $feedback->message }}
                    </div>
                </div>

                <!-- Attachment (if any) -->
                @if($feedback->attachment)
                    <div class="p-4 rounded-2xl bg-blue-50/70 border border-blue-200 flex items-center justify-between flex-wrap gap-3">
                        <div class="flex items-center gap-2 text-xs text-blue-900 font-semibold">
                            <svg class="w-5 h-5 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                            <span>Lampiran Berkas / Bukti Screenshot</span>
                        </div>
                        <a href="{{ route('feedbacks.attachment', $feedback->id) }}" target="_blank"
                           class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition shadow-xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            <span>Unduh / Buka Lampiran</span>
                        </a>
                    </div>
                @endif

                {{-- Moderasi pesan chat yang dilaporkan (hanya Super Admin) --}}
                @if($feedback->chat_message_id && Auth::user()->isSuperAdmin())
                    @php
                        $reportedMessage = \App\Models\ChatMessage::with(['sender', 'attachments'])->find($feedback->chat_message_id);
                    @endphp
                    <div class="p-5 rounded-2xl border {{ $reportedMessage && !$reportedMessage->isDeleted() ? 'bg-rose-50/60 border-rose-200' : 'bg-slate-50 border-slate-200' }} space-y-3">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div>
                                <h4 class="font-extrabold text-sm text-slate-900">Moderasi Pesan Chat</h4>
                                <p class="text-xs text-slate-600 mt-0.5">
                                    @if(!$reportedMessage)
                                        Pesan sudah tidak ada di sistem.
                                    @elseif($reportedMessage->isDeleted())
                                        Pesan ini sudah dihapus {{ $reportedMessage->deleted_at?->diffForHumans() }}.
                                    @else
                                        Pesan masih terlihat oleh peserta percakapan. Hapus bila melanggar aturan; tindakan ini tercatat di Log Audit.
                                    @endif
                                </p>
                            </div>
                            @if($reportedMessage && !$reportedMessage->isDeleted())
                                <button type="button"
                                        @click="$dispatch('open-delete-modal', { action: '{{ route('chat.moderation.destroy', $reportedMessage->id) }}', title: 'Hapus Pesan Chat', name: @js('Pesan dari ' . ($reportedMessage->sender?->name ?? 'pengguna')), desc: 'Isi dan lampiran pesan akan dihapus untuk semua peserta percakapan dan diganti keterangan &quot;Pesan ini telah dihapus&quot;.' })"
                                        class="inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold transition shadow-xs whitespace-nowrap shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    Hapus Pesan untuk Semua
                                </button>
                            @endif
                        </div>
                        @if($reportedMessage && !$reportedMessage->isDeleted() && $reportedMessage->attachments->isNotEmpty())
                            <div class="flex flex-wrap gap-2">
                                @foreach($reportedMessage->attachments as $reportedAttachment)
                                    <a href="{{ route('chat.attachment', $reportedAttachment->id) }}" target="_blank" rel="noopener"
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white border border-slate-200 text-xs font-semibold text-slate-700 hover:border-slate-300">
                                        📎 {{ $reportedAttachment->name }}
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif

            </div>

            <!-- Existing Official Response -->
            @if($feedback->admin_response)
                <div class="bg-emerald-50/80 rounded-3xl p-6 sm:p-8 border border-emerald-200 shadow-sm space-y-4">
                    <div class="flex items-center justify-between border-b border-emerald-200 pb-3">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <h4 class="font-extrabold text-sm text-emerald-950">Tanggapan Resmi Pengelola</h4>
                        </div>
                        <span class="text-xs text-emerald-700">
                            {{ $feedback->responded_at ? $feedback->responded_at->translatedFormat('d F Y, H:i') : '' }}
                            @if($feedback->responder)
                                (Oleh: {{ $feedback->responder->name }})
                            @endif
                        </span>
                    </div>

                    <div class="text-xs sm:text-sm text-emerald-900 leading-relaxed whitespace-pre-line">
                        {{ $feedback->admin_response }}
                    </div>
                </div>
            @endif

            <!-- Response Form (For Admin / Super Admin / Target Authority) -->
            @if($isSuperAdmin || $isAdminDinas || $isUniversitas)
                <form action="{{ route('admin.feedbacks.respond', $feedback->id) }}" method="POST" class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-100 shadow-sm space-y-5">
                    @csrf

                    <div class="border-b border-slate-100 pb-3">
                        <h4 class="font-extrabold text-base text-gray-900">Form Tindak Lanjut & Tanggapan Admin</h4>
                        <p class="text-xs text-gray-400">Tuliskan solusi, tindak lanjut, atau jawaban resmi untuk pengirim</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Perbarui Status Tiket <span class="text-rose-500">*</span>
                        </label>
                        <select name="status" required class="w-full text-xs sm:text-sm border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs">
                            @foreach(\App\Enums\FeedbackStatus::cases() as $feedbackCase)
                                <option value="{{ $feedbackCase->value }}" {{ $feedback->status == $feedbackCase->value ? 'selected' : '' }}>{{ $feedbackCase->label() }}</option>
                            @endforeach
                        </select>
                        <x-status-legend type="feedback" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Isi Tanggapan Resmi <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="admin_response" rows="4" required 
                                  placeholder="Tuliskan jawaban atau solusi resmi untuk pengirim. Pengirim akan mendapatkan notifikasi instan..." 
                                  class="w-full text-xs sm:text-sm border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs">{{ old('admin_response', $feedback->admin_response) }}</textarea>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-2xl shadow-md transition cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Simpan & Kirim Tanggapan</span>
                        </button>
                    </div>
                </form>
            @endif

        </div>
    </div>
</x-app-layout>
