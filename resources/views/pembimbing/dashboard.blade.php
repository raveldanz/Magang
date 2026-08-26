<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard Pembimbing Lapangan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

<<<<<<< HEAD
            <div class="bg-indigo-600 rounded-lg p-6 text-white shadow">
                <h3 class="text-2xl font-bold">Selamat Datang, {{ Auth::user()->name }}!</h3>
                <p class="text-indigo-100 mt-1">Panel Pembimbingan & Monitoring Logbook Mahasiswa Magang</p>
=======
            <div class="bg-blue-600 rounded-2xl p-6 text-white shadow-md">
                <h3 class="text-2xl font-bold">Selamat Datang, {{ Auth::user()->name }}!</h3>
                <p class="text-blue-100 mt-1">Panel Pembimbingan & Monitoring Logbook Mahasiswa Magang</p>
>>>>>>> main
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">Daftar Mahasiswa Bimbingan Anda</h3>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b bg-gray-50 text-xs font-semibold text-gray-600 uppercase">
                                <th class="p-3">Nama Mahasiswa</th>
                                <th class="p-3">NPM / NIM</th>
                                <th class="p-3">Unit Instansi</th>
                                <th class="p-3">Total Logbook</th>
                                <th class="p-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm divide-y">
                            @forelse ($placements as $place)
                                <tr>
                                    <td class="p-3 font-semibold">{{ $place->application->user->name ?? '-' }}</td>
                                    <td class="p-3 text-gray-600">{{ $place->application->user->studentProfile->npm ?? '-' }}</td>
                                    <td class="p-3">{{ $place->application->unit->name ?? '-' }}</td>
                                    <td class="p-3">
                                        <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-bold">
                                            {{ $place->logbooks->count() }} Kegiatan
                                        </span>
                                    </td>
                                    <td class="p-3">
<<<<<<< HEAD
                                        <a href="{{ route('pembimbing.student.detail', $place->id) }}" class="px-3 py-1.5 bg-indigo-600 text-white rounded hover:bg-indigo-700 text-xs font-semibold">
=======
                                        <a href="{{ route('pembimbing.student.detail', $place->id) }}" class="px-3 py-1.5 bg-blue-600 text-white rounded-xl hover:bg-blue-700 text-xs font-semibold shadow-xs transition cursor-pointer">
<<<<<<< HEAD
                                            Periksa Logbook 
=======
>>>>>>> main
                                            Periksa Logbook →
>>>>>>> 50a572d5d784ad7edaf539544c540e0815d13017
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="p-4 text-center text-gray-500">Belum ada mahasiswa yang diplotkan ke Anda.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>