<x-app-layout>
    <x-slot name="header">
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

            </div>
        </div>
    </div>
</x-app-layout>
