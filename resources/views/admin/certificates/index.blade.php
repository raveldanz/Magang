<x-app-layout>
    <x-mobile-list-style />
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-black text-xl sm:text-2xl text-gray-900 tracking-tight flex items-center gap-2">
                    <span></span>
                    <span>{{ __('Manajemen Sertifikat Kelulusan') }}</span>
                </h2>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">
                    Cetak dan terbitkan sertifikat magang resmi untuk mahasiswa yang telah menyelesaikan program
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
                <div class="p-6 border-b border-gray-100">
                    <h3 class="text-base font-bold text-gray-900">Daftar Mahasiswa Siap Cetak Sertifikat</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Mahasiswa berikut telah dinilai dan memenuhi seluruh kriteria kelulusan</p>
                </div>

                {{-- Tampilan HP: kartu --}}
                <div class="m-only mlist">
                    @forelse ($applications as $app)
                        @php
                            $mEval = $app->placement?->evaluation;
                            $mScore = $mEval ? (float) $mEval->nilai_akhir : 0;
                            $mGrade = $mEval ? $mEval->grade_calculated : '-';
                            $mInitials = collect(explode(' ', trim(preg_replace('/\s*\([^)]*\)/', '', $app->user->name))))->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');
                        @endphp
                        <div class="mcard">
                            <div class="mcard-head">
                                <div class="mavatar">{{ $mInitials ?: 'M' }}</div>
                                <div class="mcard-main">
                                    <div class="mcard-title">{{ $app->user->name }}</div>
                                    <div class="mcard-sub">
                                        <span class="mono">{{ $app->user->studentProfile->nim ?? '-' }}</span> · {{ $app->user->studentProfile->universitas ?? '-' }}
                                    </div>
                                </div>
                                <div class="mscore">
                                    <b>{{ $mScore > 0 ? rtrim(rtrim(number_format($mScore, 2, '.', ''), '0'), '.') : '-' }}</b>
                                    <span>{{ $mGrade !== '-' ? 'Grade ' . $mGrade : 'Nilai' }}</span>
                                </div>
                            </div>
                            <div class="mcard-body">
                                <div>
                                    <div class="mfield-label">Unit Tempat Magang</div>
                                    <div class="mfield-value">{{ $app->unit->name ?? '-' }}</div>
                                </div>
                            </div>
                            <div class="mcard-foot">
                                <a href="{{ route('admin.certificates.generate', $app->placement->id) }}" target="_blank" class="mbtn mbtn-primary mbtn-block">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                    Cetak E-Sertifikat
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="mempty">
                            <p style="font-weight:700;color:#475569">Belum ada mahasiswa yang memenuhi syarat kelulusan.</p>
                            <p style="margin-top:4px">Pastikan mahasiswa telah mengumpulkan laporan dan dinilai oleh pembimbing.</p>
                        </div>
                    @endforelse
                </div>

                {{-- Tampilan desktop: tabel --}}
                <div class="d-only overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-gray-100 bg-gray-50/75 text-gray-500 font-bold uppercase tracking-wider text-[11px]">
                                <th class="py-3.5 px-4">Nama Mahasiswa</th>
                                <th class="py-3.5 px-4">Universitas / NIM</th>
                                <th class="py-3.5 px-4">Unit Tempat Magang</th>
                                <th class="py-3.5 px-4 text-center">Nilai Akhir</th>
                                <th class="py-3.5 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($applications as $app)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-4 px-4 font-bold text-gray-900 text-xs sm:text-sm">
                                        {{ $app->user->name }}
                                    </td>
                                    <td class="py-4 px-4">
                                        <div class="text-gray-800 font-semibold">{{ $app->user->studentProfile->universitas ?? '-' }}</div>
                                        <div class="text-[11px] text-blue-600 font-mono">NIM: {{ $app->user->studentProfile->nim ?? '-' }}</div>
                                    </td>
                                    <td class="py-4 px-4 text-gray-700 font-medium">
                                        {{ $app->unit->name ?? '-' }}
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        @php
                                            // Nilai akhir gabungan (dinas + DPL sesuai bobot kampus), sama dengan yang tercetak di sertifikat
                                            $eval = $app->placement?->evaluation;
                                            $rataRata = $eval ? (float) $eval->nilai_akhir : 0;
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-black bg-emerald-50 text-emerald-700 border border-emerald-200">
                                             {{ $rataRata > 0 ? $rataRata : '-' }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-4 text-right whitespace-nowrap">
                                        <a href="{{ route('admin.certificates.generate', $app->placement->id) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-xs transition active:scale-95 cursor-pointer">
                                             Cetak E-Sertifikat
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-12 text-center text-gray-400">
                                        <p class="font-bold text-gray-600 text-sm">Belum ada mahasiswa yang memenuhi syarat kelulusan.</p>
                                        <p class="text-xs text-gray-400 mt-1">Pastikan mahasiswa telah mengumpulkan laporan dan dinilai oleh pembimbing.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
