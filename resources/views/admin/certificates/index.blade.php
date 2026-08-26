<x-app-layout>
    <x-slot name="header">
<<<<<<< HEAD
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manajemen Sertifikat Kelulusan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                
                <h3 class="text-lg font-bold border-b pb-2 mb-4 text-gray-700">Daftar Mahasiswa Siap Cetak Sertifikat</h3>

                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b bg-indigo-50">
                            <th class="p-3">Nama Mahasiswa</th>
                            <th class="p-3">Universitas / NIM</th>
                            <th class="p-3">Unit Tempat Magang</th>
                            <th class="p-3">Rata-rata Nilai</th>
                            <th class="p-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($applications as $app)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="p-3 font-semibold">{{ $app->user->name }}</td>
                                <td class="p-3 text-sm">{{ $app->user->studentProfile->universitas ?? '-' }} <br><span class="text-gray-500">{{ $app->user->studentProfile->nim ?? '-' }}</span></td>
                                <td class="p-3 text-sm">{{ $app->unit->name ?? '-' }}</td>
                                <td class="p-3">
                                    @php
                                        $eval = $app->placement->evaluation;
                                        $rataRata = round(($eval->nilai_disiplin + $eval->nilai_kinerja + $eval->nilai_laporan) / 3, 2);
                                    @endphp
                                    <span class="font-bold text-green-700">{{ $rataRata }}</span>
                                </td>
                                <td class="p-3">
                                    <a href="{{ route('admin.certificates.generate', $app->placement->id) }}" class="px-3 py-1.5 bg-indigo-600 text-white rounded text-xs font-bold hover:bg-indigo-700 inline-block shadow-sm">
                                        🖨️ Cetak PDF
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-8 text-center text-gray-500">
                                    <p class="font-semibold text-lg">Belum ada mahasiswa yang memenuhi syarat kelulusan.</p>
                                    <p class="text-sm">Pastikan mahasiswa telah mengumpulkan laporan dan dinilai oleh pembimbing.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
=======
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-black text-xl sm:text-2xl text-gray-900 tracking-tight flex items-center gap-2">
                    <span>🏆</span>
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
                                        🎓 {{ $app->user->name }}
                                    </td>
                                    <td class="py-4 px-4">
                                        <div class="text-gray-800 font-semibold">{{ $app->user->studentProfile->universitas ?? '-' }}</div>
                                        <div class="text-[11px] text-blue-600 font-mono">NIM: {{ $app->user->studentProfile->nim ?? '-' }}</div>
                                    </td>
                                    <td class="py-4 px-4 text-gray-700 font-medium">
                                        🏢 {{ $app->unit->name ?? '-' }}
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        @php
                                            $eval = $app->placement?->evaluation;
                                            $rataRata = $eval ? round((($eval->nilai_disiplin ?? 0) + ($eval->nilai_kinerja ?? 0) + ($eval->nilai_laporan ?? 0)) / 3, 2) : 0;
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-black bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            ⭐ {{ $rataRata > 0 ? $rataRata : '-' }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-4 text-right whitespace-nowrap">
                                        <a href="{{ route('admin.certificates.generate', $app->placement->id) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-xs transition active:scale-95 cursor-pointer">
                                            🖨️ Cetak E-Sertifikat
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
>>>>>>> main

            </div>
        </div>
    </div>
</x-app-layout>
