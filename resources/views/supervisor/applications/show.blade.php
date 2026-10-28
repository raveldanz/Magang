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
                    <div class="bg-white rounded-3xl border-2 border-slate-200 p-5 sm:p-6 shadow-xs space-y-4">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-200 pb-4">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-12 h-12 rounded-2xl bg-blue-100 text-blue-700 flex items-center justify-center font-black text-lg shrink-0">
                                    {{ strtoupper(substr($application->user?->name ?? 'M', 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <h2 class="text-lg sm:text-xl font-black text-slate-900 truncate">{{ $application->user?->name }}</h2>
                                    <p class="text-xs text-slate-500">NIM: <strong class="text-slate-800">{{ $application->user?->studentProfile?->nim ?? '-' }}</strong></p>
                                </div>
                            </div>
                            @php
                                $st = $application->status instanceof \App\Enums\ApplicationStatus ? $application->status : \App\Enums\ApplicationStatus::tryFrom($application->status);
                            @endphp
                            @if($st)
                                <div class="self-start sm:self-auto shrink-0">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black border {{ $st->badgeColor() }}">
                                        <span class="w-2 h-2 rounded-full {{ $st->dotColor() }}"></span>
                                        {{ $st->description('supervisor') }}
                                    </span>
                                </div>
                            @endif
                        </div>

                        @if($application->group_id && $application->group)
                            <div class="p-4 bg-gradient-to-r from-indigo-50/90 via-blue-50/40 to-white border border-indigo-200 rounded-2xl space-y-2.5 mb-3">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                    <div class="flex items-center gap-2">
                                        <span class="px-2 py-0.5 rounded bg-indigo-600 text-white font-extrabold text-[10px] uppercase tracking-wider">Kelompok Magang</span>
                                        <span class="font-extrabold text-sm text-slate-900">{{ $application->group->name }}</span>
                                        <span class="text-xs text-indigo-700 font-bold font-mono">({{ $application->group->code }})</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-bold text-slate-700">
                                            Posisi: <strong class="text-indigo-800">{{ $application->role_in_group === 'leader' ? 'Ketua Kelompok' : 'Anggota Tim' }}</strong>
                                        </span>
                                        @if($application->group->proposal_letter_path)
                                            <a href="{{ route('documents.group.proposal', $application->group->id) }}" target="_blank" class="inline-flex items-center gap-1 px-2.5 py-1 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold shadow-2xs transition">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                                <span>Lihat Proposal</span>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                                <div class="pt-2 border-t border-indigo-100 text-xs">
                                    <span class="font-bold text-slate-700 block mb-1.5">Seluruh Anggota Kelompok Ini ({{ $application->group->applications->count() }} Orang):</span>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2">
                                        @foreach($application->group->applications as $grpMember)
                                            <a href="{{ route('supervisor.applications.show', $grpMember->id) }}" 
                                               class="p-2.5 rounded-xl border transition flex items-center justify-between {{ $grpMember->id === $application->id ? 'bg-indigo-100/70 border-indigo-300 font-bold text-indigo-950 ring-2 ring-indigo-400/30' : 'bg-white border-slate-200 hover:border-indigo-300 text-slate-800' }}">
                                                <div class="min-w-0">
                                                    <div class="text-xs truncate max-w-[140px]">{{ $grpMember->user?->name }}</div>
                                                    <div class="text-[10px] text-slate-400 font-mono">{{ $grpMember->user?->studentProfile?->nim ?? '-' }}</div>
                                                </div>
                                                <span class="text-[9px] font-extrabold px-1.5 py-0.5 rounded shrink-0 {{ $grpMember->role_in_group === 'leader' ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-600' }}">
                                                    {{ $grpMember->role_in_group === 'leader' ? 'Ketua' : 'Anggota' }}
                                                </span>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif

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
                                <span class="text-xs text-slate-500 font-bold uppercase block">Mentor Lapangan Bidang</span>
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
                                <span class="self-start sm:self-auto px-2.5 py-1 rounded-lg bg-slate-200 text-slate-800 font-bold">
                                    Durasi: {{ $application->start_date && $application->end_date ? (int) round(\Carbon\Carbon::parse($application->start_date)->floatDiffInMonths(\Carbon\Carbon::parse($application->end_date))) . ' Bulan' : '-' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- 3. Berkas Pendukung Mahasiswa --}}
                    <div class="bg-white rounded-3xl border-2 border-slate-200 p-5 sm:p-6 shadow-xs space-y-4">
                        <h3 class="text-base font-black text-slate-900 border-b border-slate-200 pb-3">
                            Berkas Dokumen Pendukung Pengajuan
                        </h3>

                        <div class="space-y-3">
                            @forelse($application->documents ?? [] as $doc)
                                <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div class="w-9 h-9 rounded-xl bg-slate-200 text-slate-600 flex items-center justify-center font-bold text-xs shrink-0">
                                             PDF
                                        </div>
                                        <div class="min-w-0">
                                            <div class="text-sm font-bold text-slate-900 truncate">{{ $doc->name ?? $doc->type ?? 'Dokumen Pengajuan' }}</div>
                                            <div class="text-xs text-slate-500">{{ $doc->created_at ? $doc->created_at->diffForHumans() : '' }}</div>
                                        </div>
                                    </div>
                                    @if($doc->file_path)
                                        <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" 
                                           class="w-full sm:w-auto text-center px-3.5 py-2 rounded-xl bg-white hover:bg-slate-100 text-blue-700 font-bold text-xs border border-slate-300 shadow-2xs transition min-h-[38px] flex items-center justify-center">
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

                    {{-- Panel Aksi ACC / Status Pengesahan --}}
                    @php
                        $isWaiting = ($application->status instanceof \App\Enums\ApplicationStatus && $application->status === \App\Enums\ApplicationStatus::WAITING_APPROVAL) || $application->status === 'waiting_approval' || $application->status === 'pending';
                        $isApproved = in_array(is_string($application->status) ? $application->status : $application->status?->value, ['accepted', 'active', 'completed'], true);
                        $isRejected = (is_string($application->status) ? $application->status : $application->status?->value) === 'rejected';
                    @endphp

                    @if($isWaiting)
                        <div class="bg-white rounded-3xl border-2 border-slate-200 p-6 shadow-xs space-y-5 sticky top-24"
                             x-data="{ openReject: false }">
                            <div class="border-b border-slate-200 pb-3">
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Tindakan Eksekutif Atasan</span>
                                <h3 class="text-lg font-black text-slate-900 mt-0.5">Persetujuan & Pengesahan</h3>
                                <p class="text-xs text-slate-500 mt-1">
                                    Klik tombol di bawah ini untuk menyetujui pengajuan magang ini dan langsung menerbitkan Surat Balasan resmi Dispusip.
                                </p>
                            </div>

                            {{-- Aksi 1: Setujui (ACC) - Langsung selesai tanpa dobel konfirmasi --}}
                            <form id="approve-form" method="POST" action="{{ route('supervisor.applications.approve', $application->id) }}">
                                @csrf
                                <button type="submit" 
                                        class="w-full py-4 px-6 rounded-2xl bg-emerald-600 hover:bg-emerald-700 active:scale-[0.98] text-white font-black text-base shadow-lg shadow-emerald-600/30 transition flex items-center justify-center gap-3 cursor-pointer">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span>Setujui & Terbitkan Surat</span>
                                </button>
                                @if($application->group_id && $application->group)
                                    <p class="text-[11px] text-center text-slate-500 font-medium mt-2.5">
                                        ⚡ Otomatis menyetujui seluruh anggota kelompok <strong>{{ $application->group->name }}</strong> ({{ $application->group->applications->count() }} orang).
                                    </p>
                                @endif
                            </form>

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
                    @elseif($isApproved)
                        <div class="bg-white rounded-3xl border-2 border-emerald-200 p-6 shadow-xs space-y-4 sticky top-24">
                            <div class="flex items-center gap-3 pb-3 border-b border-emerald-100">
                                <div class="w-10 h-10 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <div>
                                    <span class="text-xs font-bold uppercase tracking-wider text-emerald-600">Status Persetujuan</span>
                                    <h3 class="text-base font-black text-slate-900">Telah Disetujui (ACC)</h3>
                                </div>
                            </div>
                            <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-200 text-xs space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-slate-500">No. Surat Balasan:</span>
                                    <span class="font-bold text-slate-900 font-mono">{{ $application->letter_number ?? '-' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-500">Tanggal Terbit:</span>
                                    <span class="font-bold text-slate-900">{{ $application->letter_date ? \Carbon\Carbon::parse($application->letter_date)->isoFormat('D MMMM Y') : '-' }}</span>
                                </div>
                            </div>
                            @if($application->letter_token)
                                <a href="{{ route('verify.letter', $application->letter_token) }}" target="_blank"
                                   class="w-full py-3 px-4 rounded-xl bg-slate-900 hover:bg-black text-white font-bold text-xs shadow-xs transition flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    <span>Buka Surat Balasan Resmi</span>
                                </a>
                            @endif
                        </div>
                    @elseif($isRejected)
                        <div class="bg-white rounded-3xl border-2 border-rose-200 p-6 shadow-xs space-y-4 sticky top-24">
                            <div class="flex items-center gap-3 pb-3 border-b border-rose-100">
                                <div class="w-10 h-10 rounded-2xl bg-rose-100 text-rose-700 flex items-center justify-center font-bold">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </div>
                                <div>
                                    <span class="text-xs font-bold uppercase tracking-wider text-rose-600">Status Berkas</span>
                                    <h3 class="text-base font-black text-slate-900">Pengajuan Ditolak</h3>
                                </div>
                            </div>
                            <div class="p-3.5 bg-rose-50/50 rounded-2xl border border-rose-200 text-xs text-rose-900">
                                <span class="font-bold block mb-1">Catatan Penolakan:</span>
                                <p>{{ $application->rejection_note ?? $application->rejection_reason ?? 'Tidak ada catatan.' }}</p>
                            </div>
                        </div>
                    @endif

                </div>

            </div>

        </div>
    </div>
</x-app-layout>
