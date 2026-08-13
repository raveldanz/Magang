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
            <!-- Menampilkan Error Validasi Jika Ada yang Gagal -->
            @if ($errors->any())
                <div class="p-4 mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 rounded-lg text-sm">
                    <p class="font-bold">Pengajuan Gagal Dikirim:</p>
                    <ul class="mt-1 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- ALERT PENOLAKAN DARI ADMIN (JIKA PENGAJUAN TERAKHIR REJECTED) -->
            @if ($applicationHistory->first() && $applicationHistory->first()->status === 'rejected')
                <div class="p-4 bg-red-50 border-l-4 border-red-500 rounded-r-lg shadow-sm">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 8 0 100-16 8 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-bold text-red-800">Mohon Maaf, Pengajuan Magang Anda Ditolak</h3>
                            <div class="mt-2 text-sm text-red-700">
                                <p class="font-semibold">Catatan / Alasan dari Admin:</p>
                                <p class="mt-1 italic bg-white p-3 rounded border border-red-200 font-mono text-gray-800">
                                    "{{ $applicationHistory->first()->rejection_note ?? 'Tidak ada catatan spesifik dari admin.' }}"
                                </p>
                            </div>
                            <p class="mt-3 text-xs text-red-600">
                                *Silakan buat pengajuan baru di bawah dengan melengkapi/memperbaiki berkas sesuai catatan
                                admin di atas.
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- 1. FORM PENGAJUAN BARU -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">Form Buat Pengajuan Magang</h3>

                @if ($activeApplication)
                    <!-- Alert jika masih ada berkas yang statusnya PENDING -->
                    <div class="p-4 text-sm text-yellow-800 rounded-lg bg-yellow-50 border border-yellow-200">
                        <span class="font-bold">Perhatian:</span> Anda masih memiliki pengajuan magang yang sedang diproses
                        (Status: <strong>PENDING</strong>).
                        Anda belum dapat membuat pengajuan baru hingga pengajuan tersebut selesai diverifikasi oleh Admin.
                    </div>
                @else
                    <!-- Form Input Magang Baru -->
                    <form action="{{ route('student.application.store') }}" method="POST" enctype="multipart/form-data"
                        class="space-y-4">
                        @csrf

                        <!-- Pemilihan Unit (Radio Button Cards) -->
                        <div>
                            <x-input-label value="Pilih Instansi / Unit Kerja" class="mb-3 text-base font-semibold" />
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach ($units as $unit)
                                    @php
                                        $isFull = $unit->remaining_quota <= 0;
                                        $filledQuota = max(0, $unit->quota - $unit->remaining_quota);
                                        $percentage = $unit->quota > 0 ? min(100, round(($filledQuota / $unit->quota) * 100)) : 100;
                                    @endphp
                                    <label class="relative flex flex-col p-4 border rounded-xl cursor-pointer transition-all duration-200 shadow-sm 
                                        {{ $isFull 
                                            ? 'bg-gray-100 border-gray-200 text-gray-400 opacity-75 cursor-not-allowed' 
                                            : 'bg-white border-gray-300 hover:border-indigo-500 has-[:checked]:border-indigo-600 has-[:checked]:ring-2 has-[:checked]:ring-indigo-500 has-[:checked]:bg-indigo-50/30 hover:shadow-md' }}">
                                        
                                        <div class="flex items-start justify-between mb-2">
                                            <div class="flex items-center space-x-3 pr-2">
                                                <input type="radio" name="unit_id" value="{{ $unit->id }}" 
                                                    class="w-4 h-4 text-indigo-600 border-gray-300 focus:ring-indigo-500 disabled:opacity-50"
                                                    {{ $isFull ? 'disabled' : '' }} {{ old('unit_id') == $unit->id ? 'checked' : '' }} required>
                                                <span class="font-bold text-sm {{ $isFull ? 'text-gray-500' : 'text-gray-800' }}">
                                                    {{ $unit->name }}
                                                </span>
                                            </div>

                                            @if ($isFull)
                                                <span class="px-2 py-0.5 text-xs font-extrabold rounded-full bg-red-100 text-red-700 border border-red-200 uppercase tracking-wide">
                                                    PENUH
                                                </span>
                                            @else
                                                <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-green-100 text-green-700 border border-green-200">
                                                    Tersedia
                                                </span>
                                            @endif
                                        </div>

                                        @if ($unit->description)
                                            <p class="text-xs text-gray-500 mb-3 line-clamp-2">{{ $unit->description }}</p>
                                        @endif

                                        <!-- Progress Bar & Kuota -->
                                        <div class="mt-auto pt-2">
                                            <div class="flex justify-between items-center text-xs mb-1">
                                                <span class="font-medium {{ $isFull ? 'text-gray-400' : 'text-gray-600' }}">Sisa Kuota:</span>
                                                <span class="font-bold {{ $isFull ? 'text-red-500' : 'text-indigo-600' }}">
                                                    {{ $unit->remaining_quota }} / {{ $unit->quota }} Kursi
                                                </span>
                                            </div>
                                            <!-- Visual Progress Bar -->
                                            <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                                                <div class="h-2 rounded-full transition-all duration-300 {{ $isFull ? 'bg-red-500' : ($percentage > 80 ? 'bg-amber-500' : 'bg-indigo-600') }}"
                                                    style="width: {{ $percentage }}%">
                                                </div>
                                            </div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                            @error('unit_id')
                                <p class="mt-2 text-sm text-red-600 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Periode Magang -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="start_date" value="Tanggal Mulai Magang" />
                                <x-text-input id="start_date" name="start_date" type="date" class="mt-1 block w-full"
                                    required />
                            </div>
                            <div>
                                <x-input-label for="end_date" value="Tanggal Selesai Magang" />
                                <x-text-input id="end_date" name="end_date" type="date" class="mt-1 block w-full"
                                    required />
                            </div>
                        </div>

                        <hr class="my-6">
                        <h4 class="font-semibold text-md text-gray-700">Upload Dokumen Persyaratan (Format PDF, Maks 2MB)
                        </h4>

                        <div>
                            <x-input-label for="surat_pengantar" value="Surat Pengantar Perguruan Tinggi" />
                            <x-text-input id="surat_pengantar" name="surat_pengantar" type="file" accept=".pdf"
                                class="mt-1 block w-full border p-2 rounded-md" required />
                        </div>

                        <div>
                            <x-input-label for="cv" value="Curriculum Vitae (CV)" />
                            <x-text-input id="cv" name="cv" type="file" accept=".pdf"
                                class="mt-1 block w-full border p-2 rounded-md" required />
                        </div>

                        <div>
                            <x-input-label for="transkrip" value="Transkrip Nilai" />
                            <x-text-input id="transkrip" name="transkrip" type="file" accept=".pdf"
                                class="mt-1 block w-full border p-2 rounded-md" required />
                        </div>

                        <div class="flex items-center space-x-3 pt-2">
                            <x-primary-button>
                                {{ __('Kirim Pengajuan Magang') }}
                            </x-primary-button>

                            <a href="{{ route('dashboard') }}">
                                <x-secondary-button type="button">
                                    {{ __('Kembali') }}
                                </x-secondary-button>
                            </a>
                        </div>
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
                                <th class="p-3">Catatan / Alasan Admin</th>
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
                                    <td class="p-3">
                                        @if ($app->status === 'rejected')
                                            <span
                                                class="text-red-600 font-medium bg-red-50 px-2 py-1 rounded border border-red-200 inline-block">
                                                {{ $app->rejection_note ?? 'Tidak ada catatan' }}
                                            </span>
                                        @else
                                            <span class="text-gray-500">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="p-4 text-center text-gray-500">Belum ada riwayat pengajuan
                                        magang.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>