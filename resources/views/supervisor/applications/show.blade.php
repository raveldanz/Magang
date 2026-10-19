<x-app-layout>
    <x-slot name="header">
        <x-page-header 
            title="Detail Berkas & Usulan Penempatan Magang" 
            description="Tinjau kesesuaian berkas mahasiswa, kuota bidang, dan mentor pembimbing untuk pengajuan #{{ $application->id }}.">
            <x-slot name="actions">
                <a href="{{ route('supervisor.applications.index') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white hover:bg-slate-50 text-slate-700 font-bold text-sm border border-slate-200 transition">
                    &larr; Kembali ke Daftar Pengajuan
                </a>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Error Validation Alerts --}}
            @if ($errors->any())
                <div class="p-4 rounded-2xl bg-rose-50 border-2 border-rose-300 text-rose-900 shadow-sm">
                    <div class="font-bold text-sm mb-1">Terdapat kesalahan dalam pengisian:</div>
                    <ul class="list-disc list-inside text-xs space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- KOLOM KIRI (2 KOLOM): DETAIL MAHASISWA, PENEMPATAN, & BERKAS --}}
                <div class="lg:col-span-2 space-y-6">

                    {{-- 1. Kartu Data Mahasiswa --}}
                    <div class="bg-white rounded-3xl border-2 border-slate-200 p-6 shadow-xs space-y-4">
                        <div class="flex items-center justify-between border-b border-slate-200 pb-4">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-2xl bg-blue-100 text-blue-700 flex items-center justify-center font-black text-lg">
                                    {{ strtoupper(substr($application->user?->name ?? 'M', 0, 1)) }}
                                </div>
                                <div>
                                    <h2 class="text-xl font-black text-slate-900">{{ $application->user?->name }}</h2>
                                    <p class="text-xs text-slate-500">NIM: <strong class="text-slate-800">{{ $application->user?->studentProfile?->nim ?? '-' }}</strong></p>
                                </div>
                            </div>
                            @php
                                $st = $application->status instanceof \App\Enums\ApplicationStatus ? $application->status : \App\Enums\ApplicationStatus::tryFrom($application->status);
                            @endphp
                            @if($st)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black border {{ $st->badgeColor() }}">
                                    <span class="w-2 h-2 rounded-full {{ $st->dotColor() }}"></span>
                                    {{ $st->description('supervisor') }}
                                </span>
                            @endif
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                            <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200">
                                <span class="text-xs text-slate-500 font-bold uppercase block">Asal Perguruan Tinggi</span>
                                <span class="font-black text-slate-900 text-sm mt-0.5 block">
                                    {{ $application->user?->studentProfile?->universitas ?? $application->user?->university ?? '-' }}
                                </span>
                            </div>

                            <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200">
                                <span class="text-xs text-slate-500 font-bold uppercase block">Program Studi / Jurusan</span>
                                <span class="font-black text-slate-900 text-sm mt-0.5 block">
                                    {{ $application->user?->studentProfile?->jurusan ?? '-' }}
                                </span>
                            </div>

                            <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200">
                                <span class="text-xs text-slate-500 font-bold uppercase block">Kontak Telepon / WhatsApp</span>
                                <span class="font-bold text-slate-800 text-sm mt-0.5 block">
                                    {{ $application->user?->studentProfile?->phone ?? $application->user?->phone ?? '-' }}
                                </span>
                            </div>

                            <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200">
                                <span class="text-xs text-slate-500 font-bold uppercase block">Alamat Email Mahasiswa</span>
                                <span class="font-bold text-slate-800 text-sm mt-0.5 block truncate">
                                    {{ $application->user?->email }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- 2. Usulan Penempatan & Mentor --}}
                    <div class="bg-white rounded-3xl border-2 border-slate-200 p-6 shadow-xs space-y-4">
                        <div class="border-b border-slate-200 pb-3">
                            <h3 class="text-lg font-black text-slate-900">
                                Usulan Penempatan Bidang & Pembimbing
                            </h3>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                            <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200">
                                <span class="text-xs text-slate-500 font-bold uppercase block">Bidang Penempatan</span>
                                <span class="font-black text-slate-900 text-sm mt-0.5 block">
                                    {{ $application->unit?->name ?? 'Belum Ditentukan' }}
                                </span>
                                <div class="mt-2 text-xs text-slate-500">
                                    Kepala Bidang: <strong class="text-slate-800">{{ $application->unit?->headUser?->name ?? '-' }}</strong>
                                </div>
                            </div>

                            <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200">
                                <span class="text-xs text-slate-500 font-bold uppercase block">Mentor Lapangan (Tim Kerja)</span>
                                <span class="font-black text-slate-900 text-sm mt-0.5 block">
                                    {{ $application->placement?->mentor?->name ?? $application->placement?->pembimbing?->name ?? 'Belum Ditugaskan' }}
                                </span>
                                <div class="mt-2 text-xs text-slate-500">
                                    Email: <strong class="text-slate-800 font-medium">{{ $application->placement?->mentor?->email ?? '-' }}</strong>
                                </div>
                            </div>
                        </div>

                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-2">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between text-xs gap-2">
                                <div>
                                    <span class="text-slate-500 font-bold uppercase">Rencana Periode Magang:</span>
                                    <strong class="text-slate-800 text-sm ml-1">
                                        {{ $application->start_date ? \Carbon\Carbon::parse($application->start_date)->isoFormat('D MMMM Y') : '-' }}
                                        s/d 
                                        {{ $application->end_date ? \Carbon\Carbon::parse($application->end_date)->isoFormat('D MMMM Y') : '-' }}
                                    </strong>
                                </div>
                                <span class="px-2.5 py-1 rounded-lg bg-slate-200 text-slate-800 font-bold">
                                    Durasi: {{ $application->start_date && $application->end_date ? (int) round(\Carbon\Carbon::parse($application->start_date)->floatDiffInMonths(\Carbon\Carbon::parse($application->end_date))) . ' Bulan' : '-' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- 3. Berkas Pendukung Mahasiswa --}}
                    <div class="bg-white rounded-3xl border-2 border-slate-200 p-6 shadow-xs space-y-4">
                        <h3 class="text-base font-black text-slate-900 border-b border-slate-200 pb-3">
                            Berkas Dokumen Pendukung Pengajuan
                        </h3>

                        <div class="space-y-3">
                            @forelse($application->documents ?? [] as $doc)
                                <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-slate-200 text-slate-600 flex items-center justify-center font-bold text-xs shrink-0">
                                             PDF
                                        </div>
                                        <div>
                                            <div class="text-sm font-bold text-slate-900">{{ $doc->name ?? $doc->type ?? 'Dokumen Pengajuan' }}</div>
                                            <div class="text-xs text-slate-500">{{ $doc->created_at ? $doc->created_at->diffForHumans() : '' }}</div>
                                        </div>
                                    </div>
                                    @if($doc->file_path)
                                        <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" 
                                           class="px-3 py-1.5 rounded-xl bg-white hover:bg-slate-100 text-blue-700 font-bold text-xs border border-slate-300 shadow-2xs transition">
                                            Buka Dokumen &rarr;
                                        </a>
                                    @endif
                                </div>
                            @empty
                                <div class="py-4 text-center text-xs text-slate-400">
                                    Tidak ada lampiran dokumen digital tambahan.
                                </div>
                            @endforelse
                        </div>
                    </div>

                </div>

                {{-- KOLOM KANAN (1 KOLOM): PANEL TINDAKAN ATASAN (DOUBLE ACC) --}}
                <div class="space-y-6">

                    {{-- Panel Aksi ACC --}}
                    <div class="bg-white rounded-3xl border-2 border-slate-200 p-6 shadow-xs space-y-5 sticky top-24"
                         x-data="{ 
                             showApproveModal: false, 
                             confirmedCheck: false, 
                             approveKeyword: '', 
                             openReject: false,
                             get canSubmitApprove() {
                                 return this.confirmedCheck && this.approveKeyword.trim().toUpperCase() === 'SETUJUI';
                             }
                         }">
                        <div class="border-b border-slate-200 pb-3">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Tindakan Eksekutif Atasan</span>
                            <h3 class="text-lg font-black text-slate-900 mt-0.5">Persetujuan & Pengesahan</h3>
                            <p class="text-xs text-slate-500 mt-1">
                                Klik tombol ACC untuk menyetujui pengajuan ini dan otomatis menerbitkan Surat Balasan resmi dengan nomor dinas.
                            </p>
                        </div>

                        {{-- Aksi 1: Setujui (ACC) - Trigger Modal Konfirmasi --}}
                        <form id="approve-form" method="POST" action="{{ route('supervisor.applications.approve', $application->id) }}">
                            @csrf
                            <button type="button" 
                                    @click="showApproveModal = true; confirmedCheck = false; approveKeyword = ''"
                                    class="w-full py-4 px-6 rounded-2xl bg-emerald-600 hover:bg-emerald-700 active:scale-[0.98] text-white font-black text-base shadow-lg shadow-emerald-600/30 transition flex items-center justify-center gap-3 cursor-pointer">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>Setujui & Terbitkan Surat</span>
                            </button>
                        </form>

                        {{-- MODAL KONFIRMASI 2 KALI (PENGGANTI confirm() BROWSER) --}}
                        <div x-show="showApproveModal" 
                             x-cloak 
                             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
                             @keydown.escape.window="showApproveModal = false">
                            <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-7 shadow-2xl border border-slate-200 space-y-5 text-left"
                                 @click.outside="showApproveModal = false">
                                
                                {{-- Header Modal --}}
                                <div class="flex items-start gap-3.5 pb-4 border-b border-slate-100">
                                    <div class="w-10 h-10 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </div>
                                    <div>
                                        <h3 class="text-base font-extrabold text-slate-900">Konfirmasi Pengesahan Akhir (ACC)</h3>
                                        <p class="text-xs text-slate-500 mt-0.5">Pengesahan resmi penerimaan magang dan penerbitan Surat Balasan Resmi Dispusip.</p>
                                    </div>
                                </div>

                                {{-- Rincian Berkas --}}
                                <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 text-xs space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-slate-500">Nama Mahasiswa:</span>
                                        <span class="font-bold text-slate-900">{{ $application->user?->name }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-slate-500">Perguruan Tinggi:</span>
                                        <span class="font-bold text-slate-900">{{ $application->user?->studentProfile?->universitas ?? '-' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-slate-500">Bidang Penempatan:</span>
                                        <span class="font-bold text-blue-700">{{ $application->unit?->name ?? '-' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-slate-500">Mentor Lapangan:</span>
                                        <span class="font-bold text-slate-900">{{ $application->placement?->mentor?->name ?? $application->placement?->pembimbing?->name ?? 'Belum Ditugaskan' }}</span>
                                    </div>
                                </div>

                                {{-- Konfirmasi Lapis 1: Checkbox Pernyataan --}}
                                <div class="space-y-1.5">
                                    <div class="text-[11px] font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                                        <span class="w-4 h-4 rounded-full bg-emerald-100 text-emerald-800 flex items-center justify-center text-[10px] font-black">1</span>
                                        <span>Pernyataan Persetujuan</span>
                                    </div>
                                    <label class="flex items-start gap-2.5 p-3 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 cursor-pointer transition select-none">
                                        <input type="checkbox" x-model="confirmedCheck" class="mt-0.5 rounded text-emerald-600 focus:ring-emerald-500 cursor-pointer">
                                        <span class="text-xs font-semibold text-slate-800 leading-snug">
                                            Saya selaku Atasan (Tim Kerja Umum &amp; Kepegawaian) telah memeriksa berkas dan menyetujui penerimaan magang mahasiswa ini.
                                        </span>
                                    </label>
                                </div>

                                {{-- Konfirmasi Lapis 2: Ketik Kata Kunci Tepat --}}
                                <div class="space-y-1.5">
                                    <div class="text-[11px] font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                                        <span class="w-4 h-4 rounded-full bg-emerald-100 text-emerald-800 flex items-center justify-center text-[10px] font-black">2</span>
                                        <span>Ketik Konfirmasi</span>
                                    </div>
                                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 space-y-1.5">
                                        <p class="text-xs text-slate-600">Ketik kata kunci <strong class="text-emerald-700 font-mono">SETUJUI</strong> di bawah ini:</p>
                                        <input type="text" 
                                               x-model="approveKeyword" 
                                               @input="approveKeyword = $event.target.value.toUpperCase()"
                                               placeholder="Ketik 'SETUJUI' di sini..." 
                                               class="w-full text-xs font-mono font-bold uppercase tracking-wider border-slate-300 rounded-xl focus:border-emerald-500 focus:ring-emerald-500 py-2">
                                    </div>
                                </div>

                                {{-- Tombol Modal --}}
                                <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100">
                                    <button type="button" 
                                            @click="showApproveModal = false"
                                            class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition cursor-pointer">
                                        Batal
                                    </button>
                                    <button type="button"
                                            :disabled="!canSubmitApprove"
                                            @click="document.getElementById('approve-form').submit()"
                                            :class="canSubmitApprove ? 'bg-emerald-600 hover:bg-emerald-700 text-white cursor-pointer shadow-md shadow-emerald-600/30' : 'bg-slate-200 text-slate-400 cursor-not-allowed'"
                                            class="px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                        <span>Sahkan & Terbitkan Surat</span>
                                    </button>
                                </div>

                            </div>
                        </div>

                        <div class="relative flex py-1 items-center">
                            <div class="flex-grow border-t border-slate-200"></div>
                            <span class="flex-shrink mx-3 text-xs font-bold text-slate-400 uppercase">atau</span>
                            <div class="flex-grow border-t border-slate-200"></div>
                        </div>

                        {{-- Aksi 2: Tolak --}}
                        <div>
                            <button @click="openReject = !openReject" 
                                    type="button" 
                                    class="w-full py-4 px-6 rounded-2xl bg-rose-600 hover:bg-rose-700 active:scale-[0.98] text-white font-black text-base shadow-lg shadow-rose-600/30 transition flex items-center justify-center gap-3 cursor-pointer">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>Tolak</span>
                                <svg class="w-4 h-4 ml-1 transition-transform duration-200" :class="{ 'rotate-180': openReject }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                            </button>

                            <div x-show="openReject" x-cloak class="mt-4 p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-3">
                                <form method="POST" action="{{ route('supervisor.applications.revise', $application->id) }}">
                                    @csrf
                                    <label class="block text-xs font-bold text-slate-800">
                                        Catatan Penolakan:
                                    </label>
                                    <textarea name="notes" rows="3" required minlength="5"
                                              placeholder="Tuliskan catatan penolakan untuk Admin Dinas..."
                                              class="w-full mt-1 p-3 rounded-xl border border-slate-300 focus:border-rose-500 focus:ring-0 text-xs font-medium text-slate-800 bg-white shadow-2xs"></textarea>
                                    
                                    <button type="submit" 
                                            class="w-full mt-2 py-3 px-4 rounded-xl bg-rose-700 hover:bg-rose-800 active:scale-[0.98] text-white font-black text-xs shadow-md shadow-rose-700/20 transition cursor-pointer flex items-center justify-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                                        <span>Kirim Penolakan</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

        </div>
    </div>
</x-app-layout>
