<x-app-layout>
    <x-slot name="header">
        <x-page-header 
            title="Pengesahan Sertifikat Kelulusan Magang" 
            description="Validasi nilai evaluasi akhir peserta magang dan berikan pengesahan resmi agar sertifikat dapat diunduh oleh mahasiswa.">
            <x-slot name="actions">
                <a href="{{ route('supervisor.dashboard') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white hover:bg-slate-50 text-slate-700 font-bold text-sm border border-slate-200 transition">
                    &larr; Monitoring Bidang
                </a>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="p-4 rounded-2xl bg-emerald-50 border-2 border-emerald-300 text-emerald-900 flex items-center justify-between gap-3 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-bold shrink-0">
                            ✓
                        </div>
                        <div class="text-sm font-bold">{{ session('success') }}</div>
                    </div>
                </div>
            @endif

            {{-- TABS FILTER --}}
            <div class="flex flex-wrap gap-2 border-b-2 border-slate-200 pb-3">
                <a href="{{ route('supervisor.certificates.index', ['status' => 'waiting_approval']) }}" 
                   class="px-5 py-2.5 rounded-2xl text-sm font-black transition flex items-center gap-2.5 {{ $statusFilter === 'waiting_approval' ? 'bg-blue-600 text-white shadow-md' : 'bg-white hover:bg-slate-100 text-slate-700 border-2 border-slate-200' }}">
                    <span>Menunggu Pengesahan</span>
                    <span class="px-2 py-0.5 rounded-full text-xs {{ $statusFilter === 'waiting_approval' ? 'bg-white text-blue-700' : 'bg-blue-100 text-blue-800' }}">
                        {{ $waitingCount }}
                    </span>
                </a>

                <a href="{{ route('supervisor.certificates.index', ['status' => 'approved']) }}" 
                   class="px-5 py-2.5 rounded-2xl text-sm font-black transition flex items-center gap-2.5 {{ $statusFilter === 'approved' ? 'bg-emerald-600 text-white shadow-md' : 'bg-white hover:bg-slate-100 text-slate-700 border-2 border-slate-200' }}">
                    <span>Telah Disahkan</span>
                    <span class="px-2 py-0.5 rounded-full text-xs {{ $statusFilter === 'approved' ? 'bg-white text-emerald-700' : 'bg-emerald-100 text-emerald-800' }}">
                        {{ $approvedCount }}
                    </span>
                </a>

                <a href="{{ route('supervisor.certificates.index', ['status' => 'revision']) }}" 
                   class="px-5 py-2.5 rounded-2xl text-sm font-black transition flex items-center gap-2 {{ $statusFilter === 'revision' ? 'bg-amber-600 text-white shadow-md' : 'bg-white hover:bg-slate-100 text-slate-700 border-2 border-slate-200' }}">
                    <span>Perlu Ditinjau Ulang (Revisi)</span>
                </a>
            </div>

            {{-- FORM PENCARIAN --}}
            <form method="GET" action="{{ route('supervisor.certificates.index') }}" class="bg-white p-4 sm:p-5 rounded-3xl border-2 border-slate-200 shadow-xs flex flex-col sm:flex-row gap-3">
                <input type="hidden" name="status" value="{{ $statusFilter }}">

                <div class="flex-1">
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Cari nama mahasiswa, NIM, atau universitas..." 
                           class="w-full h-12 px-4 rounded-xl border-2 border-slate-200 focus:border-blue-600 focus:ring-0 text-sm font-medium text-slate-800">
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="px-6 h-12 rounded-xl bg-slate-900 hover:bg-black text-white font-bold text-sm shadow-sm transition">
                        Cari
                    </button>
                    @if(request()->filled('search'))
                        <a href="{{ route('supervisor.certificates.index', ['status' => $statusFilter]) }}" class="px-4 h-12 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-sm flex items-center justify-center transition">
                            Reset
                        </a>
                    @endif
                </div>
            </form>

            {{-- DAFTAR TABEL SERTIFIKAT --}}
            <div class="bg-white rounded-3xl border-2 border-slate-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b-2 border-slate-200 bg-slate-100/70 text-slate-700 text-xs font-bold uppercase tracking-wider">
                                <th class="py-4 px-6">Mahasiswa & Kampus</th>
                                <th class="py-4 px-6">Bidang & No. Sertifikat</th>
                                <th class="py-4 px-4 text-center">Nilai Akhir</th>
                                <th class="py-4 px-4 text-center">Status ACC</th>
                                <th class="py-4 px-6 text-center">Tindakan Atasan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 text-sm">
                            @forelse($certificates as $cert)
                                @php
                                    $student = $cert->placement?->application?->user;
                                    $profile = $student?->studentProfile;
                                    $unit = $cert->placement?->application?->unit;
                                    $eval = $cert->placement?->evaluation;
                                    $finalScore = $eval ? $eval->final_score : ($cert->placement?->final_score ?? '-');
                                @endphp
                                <tr class="hover:bg-slate-50 transition">
                                    {{-- Mahasiswa & Kampus --}}
                                    <td class="py-4 px-6">
                                        <div class="font-black text-slate-900 text-base">{{ $student?->name }}</div>
                                        <div class="text-xs text-slate-600 mt-0.5">
                                            NIM: <span class="font-bold text-slate-700">{{ $profile?->nim ?? '-' }}</span>
                                        </div>
                                        <div class="text-xs font-semibold text-blue-700 mt-0.5">
                                            {{ $profile?->universitas ?? $student?->university ?? '-' }}
                                        </div>
                                    </td>

                                    {{-- Bidang & Nomor Sertifikat --}}
                                    <td class="py-4 px-6">
                                        <div class="font-bold text-slate-900">{{ $unit?->name ?? 'Dispusip Surabaya' }}</div>
                                        <div class="text-xs text-slate-500 font-mono mt-1">
                                            {{ $cert->certificate_number ?? $cert->placement?->certificate_number ?? 'Nomor otomatis' }}
                                        </div>
                                    </td>

                                    {{-- Nilai Akhir --}}
                                    <td class="py-4 px-4 text-center">
                                        @if(is_numeric($finalScore))
                                            <span class="inline-block px-3 py-1 rounded-xl font-black text-base {{ $finalScore >= 80 ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-blue-50 text-blue-800 border border-blue-200' }}">
                                                {{ number_format($finalScore, 1) }}
                                            </span>
                                        @else
                                            <span class="text-xs text-slate-400 font-semibold">{{ $finalScore }}</span>
                                        @endif
                                    </td>

                                    {{-- Status ACC --}}
                                    <td class="py-4 px-4 text-center">
                                        @if($cert->approval_status === 'approved')
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-emerald-50 text-emerald-800 border border-emerald-300">
                                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                                Telah Disahkan
                                            </span>
                                            @if($cert->approved_at)
                                                <div class="text-[10px] text-slate-400 mt-0.5">{{ \Carbon\Carbon::parse($cert->approved_at)->isoFormat('D MMM Y') }}</div>
                                            @endif
                                        @elseif($cert->approval_status === 'revision')
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-amber-50 text-amber-800 border border-amber-300">
                                                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                                Perlu Revisi
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-blue-50 text-blue-800 border border-blue-300">
                                                <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span>
                                                Menunggu ACC
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Tindakan Atasan --}}
                                    <td class="py-4 px-6 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            {{-- Tombol Pratinjau --}}
                                            <a href="{{ route('supervisor.certificates.show', $cert->placement_id) }}" target="_blank"
                                               class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-white hover:bg-slate-100 text-slate-800 font-bold text-xs border border-slate-300 shadow-2xs transition">
                                                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                <span>Pratinjau</span>
                                            </a>

                                            @if($cert->approval_status === 'waiting_approval')
                                                {{-- Tombol Sahkan (ACC) dengan Modal Konfirmasi --}}
                                                <div x-data="{ showApproveCertModal: false }">
                                                    <form id="approve-cert-form-{{ $cert->id }}" method="POST" action="{{ route('supervisor.certificates.approve', $cert->id) }}">
                                                        @csrf
                                                        <button type="button" 
                                                                @click="showApproveCertModal = true"
                                                                class="inline-flex items-center gap-1 px-3 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs shadow-sm transition cursor-pointer">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                            <span>Sahkan</span>
                                                        </button>
                                                    </form>

                                                    {{-- Modal Konfirmasi Sahkan Sertifikat --}}
                                                    <div x-show="showApproveCertModal" 
                                                         x-cloak 
                                                         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs text-left"
                                                         @keydown.escape.window="showApproveCertModal = false">
                                                        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200 space-y-4"
                                                             @click.outside="showApproveCertModal = false">
                                                            <div class="flex items-start gap-3 pb-3 border-b border-slate-100">
                                                                <div class="w-10 h-10 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                                                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                                </div>
                                                                <div>
                                                                    <h3 class="text-sm font-extrabold text-slate-900">Pengesahan E-Sertifikat Kelulusan</h3>
                                                                    <p class="text-xs text-slate-500 mt-0.5">Sahkan sertifikat dan transkrip nilai resmi</p>
                                                                </div>
                                                            </div>
                                                            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-xs space-y-1">
                                                                <div class="text-slate-500">Nama Mahasiswa:</div>
                                                                <div class="font-bold text-slate-900 text-sm">{{ $student?->name }}</div>
                                                                <div class="text-[11px] text-slate-500">{{ $student?->studentProfile?->universitas ?? '-' }}</div>
                                                            </div>
                                                            <p class="text-xs text-slate-600 leading-relaxed">
                                                                Setelah disahkan, mahasiswa yang bersangkutan akan dapat mengunduh E-Sertifikat resmi Dispusip beserta transkrip nilai.
                                                            </p>
                                                            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                                                                <button type="button" @click="showApproveCertModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition cursor-pointer">
                                                                    Batal
                                                                </button>
                                                                <button type="button" @click="document.getElementById('approve-cert-form-{{ $cert->id }}').submit()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition flex items-center gap-1.5 cursor-pointer shadow-md shadow-emerald-600/20">
                                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                                    <span>Ya, Sahkan Sertifikat</span>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                {{-- Modal / Dropdown Revisi --}}
                                                <div x-data="{ openRev: false }" class="relative">
                                                    <button @click="openRev = !openRev" type="button" 
                                                            class="inline-flex items-center gap-1 px-2.5 py-2 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-800 font-bold text-xs border border-amber-300 transition">
                                                        <span>Kembalikan</span>
                                                    </button>

                                                    <div x-show="openRev" @click.outside="openRev = false" x-cloak 
                                                         class="absolute right-0 bottom-full mb-2 w-72 bg-white rounded-2xl p-4 shadow-xl border border-slate-200 z-50 text-left">
                                                        <form method="POST" action="{{ route('supervisor.certificates.revise', $cert->id) }}">
                                                            @csrf
                                                            <div class="text-xs font-bold text-slate-800 mb-1">Catatan Revisi Nilai/Data:</div>
                                                            <textarea name="notes" rows="2" required minlength="5" placeholder="Misal: nilai kriteria DPL belum masuk..."
                                                                      class="w-full text-xs p-2 rounded-lg border border-slate-300"></textarea>
                                                            <button type="submit" class="mt-2 w-full py-1.5 px-3 rounded-lg bg-amber-600 text-white font-bold text-xs">
                                                                Kirim Revisi
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-12 text-center text-slate-400 font-semibold">
                                        Tidak ada berkas sertifikat yang sesuai dengan filter ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($certificates->hasPages())
                    <div class="p-4 border-t border-slate-200 bg-slate-50">
                        {{ $certificates->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
