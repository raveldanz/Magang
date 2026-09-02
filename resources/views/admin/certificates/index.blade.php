<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-black text-xl sm:text-2xl text-gray-900 tracking-tight flex items-center gap-2">
                    <svg class="w-6 h-6 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
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

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-gray-100 bg-gray-50/75 text-gray-500 font-bold uppercase tracking-wider text-[11px]">
                                <th class="py-3.5 px-4">Nama Mahasiswa</th>
                                <th class="py-3.5 px-4">Universitas / NIM</th>
                                <th class="py-3.5 px-4">Unit Tempat Magang</th>
                                <th class="py-3.5 px-4 text-center">Rata-rata Nilai</th>
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
                                            $eval = $app->placement?->evaluation;
                                            $rataRata = $eval ? round((($eval->nilai_disiplin ?? 0) + ($eval->nilai_kinerja ?? 0) + ($eval->nilai_laporan ?? 0)) / 3, 2) : 0;
                                        @endphp
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-black bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <svg class="w-3.5 h-3.5 text-amber-500 fill-amber-400" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                            <span>{{ $rataRata > 0 ? $rataRata : '-' }}</span>
                                        </span>
                                    </td>
                                    <td class="py-4 px-4 text-right whitespace-nowrap">
                                        <a href="{{ route('admin.certificates.generate', $app->placement->id) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-xs transition active:scale-95 cursor-pointer">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                            <span>Cetak E-Sertifikat</span>
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
