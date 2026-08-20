<x-app-layout>
    <div class="py-8 bg-[#F5F8FC] min-h-screen text-slate-900 font-sans">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Page Header -->
            <div>
                <h2 class="text-xl font-bold tracking-tight text-slate-900">
                    Manajemen Sertifikat Kelulusan
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Daftar mahasiswa yang telah menyelesaikan seluruh laporan dan siap diterbitkan sertifikat
                </p>
            </div>

            <!-- Main Table Surface -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm shadow-slate-200/50 overflow-hidden">
                <div class="p-6 border-b border-slate-100">
                    <h3 class="text-base font-bold text-slate-900">Daftar Mahasiswa Siap Cetak</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Mahasiswa berikut telah memperoleh evaluasi mentor dan laporan disetujui</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50/60 text-xs font-semibold uppercase tracking-wider text-slate-500">
                                <th class="py-3.5 px-5">Nama Mahasiswa</th>
                                <th class="py-3.5 px-5">Universitas / NIM</th>
                                <th class="py-3.5 px-5">Unit Tempat Magang</th>
                                <th class="py-3.5 px-5 text-center">Rata-rata Nilai</th>
                                <th class="py-3.5 px-5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            @forelse ($applications as $app)
                                @php
                                    $eval = $app->placement?->evaluation;
                                    $hasScore = $eval && isset($eval->nilai_disiplin, $eval->nilai_kinerja, $eval->nilai_laporan);
                                    $rataRata = $hasScore 
                                        ? round(($eval->nilai_disiplin + $eval->nilai_kinerja + $eval->nilai_laporan) / 3, 2) 
                                        : null;
                                @endphp
                                <tr class="hover:bg-slate-50/70 transition-colors duration-150">
                                    <td class="py-4 px-5 font-bold text-slate-900 text-xs whitespace-nowrap">
                                        {{ $app->user->name ?? '-' }}
                                    </td>
                                    <td class="py-4 px-5 text-slate-700 text-xs">
                                        <div class="font-medium text-slate-900">{{ $app->user->studentProfile->universitas ?? '-' }}</div>
                                        <div class="text-slate-400 font-mono mt-0.5">{{ $app->user->studentProfile->nim ?? '-' }}</div>
                                    </td>
                                    <td class="py-4 px-5 text-slate-700 text-xs font-medium">
                                        {{ $app->unit->name ?? '-' }}
                                    </td>
                                    <td class="py-4 px-5 text-center whitespace-nowrap">
                                        @if ($rataRata !== null)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                {{ $rataRata }}
                                            </span>
                                        @else
                                            <span class="text-xs text-slate-400 italic">Belum dinilai</span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-5 text-right whitespace-nowrap">
                                        @if ($app->placement)
                                            <a href="{{ route('admin.certificates.generate', $app->placement->id) }}" 
                                               class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white rounded-xl text-xs font-semibold shadow-sm shadow-blue-200 transition-all duration-200 hover:scale-[1.01]">
                                                <span>🖨️</span>
                                                <span>Cetak PDF</span>
                                            </a>
                                        @else
                                            <span class="text-xs text-slate-400">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-12 text-center text-slate-400">
                                        <p class="font-medium text-slate-600">Belum ada mahasiswa yang memenuhi syarat kelulusan.</p>
                                        <p class="text-xs text-slate-400 mt-1">Pastikan mahasiswa telah mengumpulkan laporan dan dinilai oleh pembimbing.</p>
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