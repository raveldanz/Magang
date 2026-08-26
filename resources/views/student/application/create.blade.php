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

                        <!-- Pemilihan Unit (Grouped per Instansi) -->
                        <div>
                            <x-input-label for="unit_id" value="Pilih Instansi & Unit Kerja / Divisi Magang" />
                            @php
                                $totalAvailable = $units->filter(fn($unit) => $unit->remaining_quota > 0)->count();
                            @endphp

                            <select id="unit_id" name="unit_id"
                                class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-xl shadow-sm text-sm"
                                {{ $totalAvailable === 0 ? 'disabled' : '' }} required>
                                
                                @if ($totalAvailable === 0)
                                    <option value="" disabled selected>-- Maaf, saat ini seluruh kuota divisi magang sudah penuh --</option>
                                @else
                                    <option value="">-- Pilih Instansi & Bidang/Divisi Magang --</option>
                                    @foreach ($groupedUnits as $agencyName => $agencyUnits)
                                        <optgroup label="{{ strtoupper($agencyName) }}">
                                            @foreach ($agencyUnits as $unit)
                                                <option value="{{ $unit->id }}" 
                                                    {{ old('unit_id') == $unit->id ? 'selected' : '' }}
                                                    {{ $unit->remaining_quota <= 0 ? 'disabled class=text-gray-400' : '' }}>
                                                    {{ $unit->name }} &bull; Sisa Kuota: {{ $unit->remaining_quota }} {{ $unit->remaining_quota <= 0 ? '(PENUH)' : 'orang' }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                @endif
                            </select>
                            <p class="mt-1 text-xs text-gray-500">Unit kerja telah dikelompokkan secara rapi berdasarkan instansi induk.</p>
                            @error('unit_id')
                                <p class="mt-2 text-sm text-red-600 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Periode Magang (Datepicker) -->
                        <!-- Periode Magang (Datepicker Terkunci Otomatis) -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-4" 
     x-data="{ 
         startDate: '{{ old('start_date', '') }}',
         endDate: '{{ old('end_date', '') }}',
         today: '{{ date('Y-m-d') }}'
     }">
    
    <!-- Tanggal Mulai -->
    <div>
        <x-input-label for="start_date" value="Tanggal Mulai Magang" class="text-xs font-semibold uppercase tracking-wider text-slate-500" />
        <x-text-input id="start_date" 
                      name="start_date" 
                      type="date" 
                      min="{{ date('Y-m-d') }}" 
                      x-model="startDate"
                      class="mt-1 block w-full text-xs sm:text-sm rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-400/40 focus:border-blue-400"
                      required />
        <p class="text-[11px] text-slate-400 mt-1">Pilih tanggal awal mulai kegiatan magang.</p>
        <x-input-error :messages="$errors->get('start_date')" class="mt-1" />
    </div>

    <!-- Tanggal Selesai -->
    <div>
        <x-input-label for="end_date" value="Tanggal Selesai Magang" class="text-xs font-semibold uppercase tracking-wider text-slate-500" />
        <x-text-input id="end_date" 
                      name="end_date" 
                      type="date" 
                      x-bind:min="startDate || today" 
                      x-model="endDate"
                      class="mt-1 block w-full text-xs sm:text-sm rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-400/40 focus:border-blue-400"
                      required />
        <p class="text-[11px] text-slate-400 mt-1">Tanggal selesai otomatis terkunci setelah tanggal mulai.</p>
        <x-input-error :messages="$errors->get('end_date')" class="mt-1" />
    </div>
</div>

                        <hr class="my-6">
                        <div class="space-y-1">
                            <h4 class="font-bold text-sm text-gray-800 flex items-center gap-2">
                                <span>Upload Dokumen Persyaratan Magang</span>
                            </h4>
                            <p class="text-xs text-gray-500">Seluruh dokumen wajib berformat PDF dengan ukuran maksimum 2MB per file</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                            <div>
                                <x-input-label for="surat_pengantar" value="1. Surat Pengantar / Proposal Kampus " class="text-xs font-bold" />
                                <input id="surat_pengantar" name="surat_pengantar" type="file" accept=".pdf"
                                    class="mt-1 block w-full text-xs border border-gray-300 rounded-xl p-2.5 bg-gray-50/50 file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" required />
                                <x-input-error :messages="$errors->get('surat_pengantar')" class="mt-1" />
                            </div>

                            <div>
                                <x-input-label for="cv" value="2. Curriculum Vitae (CV) " class="text-xs font-bold" />
                                <input id="cv" name="cv" type="file" accept=".pdf"
                                    class="mt-1 block w-full text-xs border border-gray-300 rounded-xl p-2.5 bg-gray-50/50 file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" required />
                                <x-input-error :messages="$errors->get('cv')" class="mt-1" />
                            </div>

                            <div>
                                <x-input-label for="transkrip" value="3. Transkrip Nilai Akademik Terakhir " class="text-xs font-bold" />
                                <input id="transkrip" name="transkrip" type="file" accept=".pdf"
                                    class="mt-1 block w-full text-xs border border-gray-300 rounded-xl p-2.5 bg-gray-50/50 file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" required />
                                <x-input-error :messages="$errors->get('transkrip')" class="mt-1" />
                            </div>

                            <div>
                                <x-input-label for="id_card" value="4. Kartu Tanda Mahasiswa (KTM)" class="text-xs font-bold" />
                                <input id="id_card" name="id_card" type="file" accept=".pdf,.jpg,.jpeg,.png"
                                    class="mt-1 block w-full text-xs border border-gray-300 rounded-xl p-2.5 bg-gray-50/50 file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
                                <x-input-error :messages="$errors->get('id_card')" class="mt-1" />
                            </div>
                            
                        </div>

                        <div class="flex items-center space-x-3 pt-4 border-t border-gray-100">
                            <x-primary-button class="text-xs px-5 py-2.5">
                                {{ __('Kirim Pengajuan Magang') }}
                            </x-primary-button>

                            <a href="{{ route('dashboard') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-xl transition">
                                {{ __('Kembali') }}
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
                                <th class="p-3">Surat Penerimaan</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm divide-y">
                            @forelse ($applicationHistory as $app)
                                <tr>
                                    <td class="p-3 text-gray-500 font-mono text-xs">{{ $app->created_at->format('d M Y, H:i') }}</td>
                                    <td class="p-3">
                                        <div class="font-semibold text-gray-900">{{ $app->unit->name ?? '-' }}</div>
                                        <div class="text-xs text-gray-500">{{ $app->unit->agencyProfile->agency_name ?? '-' }}</div>
                                    </td>
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
                                    <td class="p-3">
                                        @if ($app->status === 'accepted')
                                            <a href="{{ route('student.application.letter', $app->id) }}" target="_blank" 
                                                class="inline-flex items-center space-x-1 px-3 py-1.5 bg-blue-600 text-white rounded-xl text-xs font-semibold hover:bg-blue-700 shadow-sm transition cursor-pointer">
                                                <span>Download Surat PDF</span>
                                            </a>
                                        @else
                                            <span class="text-gray-400 text-xs italic">Belum tersedia</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="p-4 text-center text-gray-500">Belum ada riwayat pengajuan magang.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>