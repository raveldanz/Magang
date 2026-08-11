<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Pengajuan Magang & Riwayat') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            @if (session('success'))
                <div class="p-4 bg-green-100 text-green-800 rounded-lg text-sm font-semibold">
                    {{ session('success') }}
                </div>
            @endif

            <!-- 1. FORM PENGAJUAN BARU -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">Form Buat Pengajuan Magang</h3>

                @if ($activeApplication)
                    <!-- Alert jika masih ada berkas yang statusnya PENDING -->
                    <div class="p-4 text-sm text-yellow-800 rounded-lg bg-yellow-50 border border-yellow-200">
                        <span class="font-bold">Perhatian:</span> Anda masih memiliki pengajuan magang yang sedang diproses (Status: <strong>PENDING</strong>). 
                        Anda belum dapat membuat pengajuan baru hingga pengajuan tersebut selesai diverifikasi oleh Admin.
                    </div>
                @else
                    <!-- Form Input Magang Baru -->
                    <form action="{{ route('student.application.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                        @csrf

                        <!-- Pemilihan Unit -->
                        <div>
                            <x-input-label for="unit_id" value="Pilih Instansi / Unit Kerja" />
                            <select id="unit_id" name="unit_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                <option value="">-- Pilih Unit --</option>
                                @foreach ($units as $unit)
                                    <option value="{{ $unit->id }}">{{ $unit->name }} (Kuota: {{ $unit->quota }})</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Periode Magang -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="start_date" value="Tanggal Mulai Magang" />
                                <x-text-input id="start_date" name="start_date" type="date" class="mt-1 block w-full" required />
                            </div>
                            <div>
                                <x-input-label for="end_date" value="Tanggal Selesai Magang" />
                                <x-text-input id="end_date" name="end_date" type="date" class="mt-1 block w-full" required />
                            </div>
                        </div>

                        <hr class="my-6">
                        <h4 class="font-semibold text-md text-gray-700">Upload Dokumen Persyaratan (Format PDF, Maks 2MB)</h4>

                        <div>
                            <x-input-label for="surat_pengantar" value="Surat Pengantar Perguruan Tinggi" />
                            <x-text-input id="surat_pengantar" name="surat_pengantar" type="file" accept=".pdf" class="mt-1 block w-full border p-2 rounded-md" required />
                        </div>

                        <div>
                            <x-input-label for="cv" value="Curriculum Vitae (CV)" />
                            <x-text-input id="cv" name="cv" type="file" accept=".pdf" class="mt-1 block w-full border p-2 rounded-md" required />
                        </div>

                        <div>
                            <x-input-label for="transkrip" value="Transkrip Nilai" />
                            <x-text-input id="transkrip" name="transkrip" type="file" accept=".pdf" class="mt-1 block w-full border p-2 rounded-md" required />
                        </div>

                        <x-primary-button class="mt-4">
                            {{ __('Kirim Pengajuan Magang') }}
                        </x-primary-button>

                        <a href="{{ route('dashboard') }}">
                            <x-secondary-button>
                                {{ __('Kembali') }}
                            </x-secondary-button>
                        </a>
                    </form>
                @endif
            </div>

            <!-- 2. TABEL RIWAYAT PENGAJUAN MAGANG -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">Riwayat Pengajuan Magang Anda</h3>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b bg-gray-50 text-xs font-semibold text-gray-600 uppercase">
                                <th class="p-3">Tanggal Pengajuan</th>
                                <th class="p-3">Unit Instansi</th>
                                <th class="p-3">Periode Magang</th>
                                <th class="p-3">Status</th>
                                <th class="p-3">Catatan Admin</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm divide-y">
                            @forelse ($applicationHistory as $app)
                                <tr>
                                    <td class="p-3 text-gray-500">{{ $app->created_at->format('d M Y, H:i') }}</td>
                                    <td class="p-3 font-semibold">{{ $app->unit->name ?? '-' }}</td>
                                    <td class="p-3">{{ $app->start_date }} s/d {{ $app->end_date }}</td>
                                    <td class="p-3">
                                        <span class="px-2.5 py-1 text-xs font-bold rounded-full 
                                            {{ $app->status === 'accepted' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $app->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                            {{ $app->status === 'verified' ? 'bg-blue-100 text-blue-800' : '' }}
                                            {{ $app->status === 'rejected' ? 'bg-red-100 text-red-800' : '' }}">
                                            {{ strtoupper($app->status) }}
                                        </span>
                                    </td>
                                    <td class="p-3 text-gray-600">
                                        {{ $app->rejection_note ?? '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="p-4 text-center text-gray-500">Belum ada riwayat pengajuan magang.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>