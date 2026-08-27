<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-xl text-slate-800 leading-tight">
                {{ __('Pengajuan Magang & Riwayat') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-8 bg-[#F5F8FC] min-h-screen text-slate-900 font-sans">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Success Message -->
            @if (session('success'))
                <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl flex items-center gap-3 text-sm font-semibold shadow-sm">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <!-- Error Validasi Input -->
            @if ($errors->any())
                <div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-2xl text-xs space-y-1 shadow-sm">
                    <p class="font-bold">Pengajuan Gagal Dikirim:</p>
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Alert Penolakan dari Admin (Jika Pengajuan Terakhir Ditolak) -->
            @if ($applicationHistory->first() && $applicationHistory->first()->status === 'rejected')
                <div class="p-5 bg-red-50 border border-red-200 rounded-2xl shadow-sm space-y-2">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <h3 class="text-sm font-bold text-red-900">Mohon Maaf, Pengajuan Magang Anda Ditolak</h3>
                    </div>
                    <div class="text-xs text-red-800">
                        <p class="font-semibold">Catatan / Alasan dari Admin:</p>
                        <p class="mt-1 italic bg-white p-3 rounded-xl border border-red-200 font-mono text-slate-800">
                            "{{ $applicationHistory->first()->rejection_note ?? 'Tidak ada catatan spesifik dari admin.' }}"
                        </p>
                    </div>
                    <p class="text-[11px] text-red-600">
                        *Silakan buat pengajuan baru di bawah dengan melengkapi/memperbaiki berkas sesuai catatan admin di atas.
                    </p>
                </div>
            @endif

            <!-- 1. FORM PENGAJUAN BARU -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm shadow-slate-200/50 p-6 sm:p-7 space-y-5">
                <div class="border-b border-slate-100 pb-3">
                    <h3 class="text-base font-bold text-slate-900">Form Buat Pengajuan Magang</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Pilih instansi dinas penempatan dan unggah berkas persyaratan yang diperlukan</p>
                </div>

                @if ($activeApplication)
                    <!-- Alert jika masih ada berkas PENDING -->
                    <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs leading-relaxed">
                        <span class="font-bold">Perhatian:</span> Anda masih memiliki pengajuan magang yang sedang diproses (Status: <strong>PENDING</strong>). Anda belum dapat membuat pengajuan baru hingga pengajuan tersebut selesai diverifikasi oleh Admin.
                    </div>
                @else
                    <!-- Form Input Magang Baru -->
                    <form action="{{ route('student.application.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                        @csrf

                        <!-- Pemilihan Unit (Grouped per Instansi) -->
                        <div>
                            <x-input-label for="unit_id" value="Pilih Instansi & Unit Kerja / Divisi Magang" class="text-xs font-semibold uppercase tracking-wider text-slate-600" />
                            @php
                                $totalAvailable = $units->filter(fn($unit) => $unit->remaining_quota > 0)->count();
                            @endphp

                            <select id="unit_id" name="unit_id"
                                class="mt-1 block w-full border-slate-200 bg-slate-50 focus:bg-white focus:border-blue-400 focus:ring-2 focus:ring-blue-400/40 rounded-xl shadow-sm text-xs sm:text-sm"
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
                                                    {{ $unit->remaining_quota <= 0 ? 'disabled class=text-slate-400' : '' }}>
                                                    {{ $unit->name }} &bull; Sisa Kuota: {{ $unit->remaining_quota }} {{ $unit->remaining_quota <= 0 ? '(PENUH)' : 'orang' }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                @endif
                            </select>
                            <p class="mt-1 text-[11px] text-slate-400">Unit kerja telah dikelompokkan secara rapi berdasarkan instansi induk.</p>
                            @error('unit_id')
                                <p class="mt-1 text-xs text-red-600 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Periode Magang (Datepicker Terkunci Otomatis) -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4" 
                             x-data="{ 
                                 startDate: '{{ old('start_date', '') }}',
                                 endDate: '{{ old('end_date', '') }}',
                                 today: '{{ date('Y-m-d') }}'
                             }">
                            
                            <!-- Tanggal Mulai -->
                            <div>
                                <x-input-label for="start_date" value="Tanggal Mulai Magang" class="text-xs font-semibold uppercase tracking-wider text-slate-600" />
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
                                <x-input-label for="end_date" value="Tanggal Selesai Magang" class="text-xs font-semibold uppercase tracking-wider text-slate-600" />
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

                        <!-- Dokumen Persyaratan -->
                        <div class="pt-4 border-t border-slate-100 space-y-3">
                            <div>
                                <h4 class="font-bold text-sm text-slate-800">Upload Dokumen Persyaratan Magang</h4>
                                <p class="text-xs text-slate-500 mt-0.5">Dokumen berformat PDF (maksimal 2MB per file)</p>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="surat_pengantar" value="1. Surat Pengantar / Proposal Kampus" class="text-xs font-semibold text-slate-700" />
                                    <input id="surat_pengantar" name="surat_pengantar" type="file" accept=".pdf"
                                        class="mt-1 block w-full text-xs border border-slate-200 rounded-xl p-2 bg-slate-50 focus:bg-white file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" required />
                                    <x-input-error :messages="$errors->get('surat_pengantar')" class="mt-1" />
                                </div>

                                <div>
                                    <x-input-label for="cv" value="2. Curriculum Vitae (CV)" class="text-xs font-semibold text-slate-700" />
                                    <input id="cv" name="cv" type="file" accept=".pdf"
                                        class="mt-1 block w-full text-xs border border-slate-200 rounded-xl p-2 bg-slate-50 focus:bg-white file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" required />
                                    <x-input-error :messages="$errors->get('cv')" class="mt-1" />
                                </div>

                                <div>
                                    <x-input-label for="transkrip" value="3. Transkrip Nilai Akademik Terakhir" class="text-xs font-semibold text-slate-700" />
                                    <input id="transkrip" name="transkrip" type="file" accept=".pdf"
                                        class="mt-1 block w-full text-xs border border-slate-200 rounded-xl p-2 bg-slate-50 focus:bg-white file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" required />
                                    <x-input-error :messages="$errors->get('transkrip')" class="mt-1" />
                                </div>

                                <div>
                                    <x-input-label for="id_card" value="4. Kartu Tanda Mahasiswa (KTM / ID Card)" class="text-xs font-semibold text-slate-700" />
                                    <input id="id_card" name="id_card" type="file" accept=".pdf,.jpg,.jpeg,.png"
                                        class="mt-1 block w-full text-xs border border-slate-200 rounded-xl p-2 bg-slate-50 focus:bg-white file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" required />
                                    <x-input-error :messages="$errors->get('id_card')" class="mt-1" />
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center space-x-3 pt-4 border-t border-slate-100">
                            <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-xs font-semibold uppercase tracking-wider rounded-xl shadow-sm shadow-blue-200 transition-all duration-200 hover:scale-[1.01]">
                                {{ __('Kirim Pengajuan Magang') }}
                            </button>

                            <a href="{{ route('dashboard') }}" class="px-4 py-2.5 bg-slate-50 hover:bg-slate-100 text-slate-600 border border-slate-200 text-xs font-medium rounded-xl transition">
                                {{ __('Kembali') }}
                            </a>
                        </div>
                    </form>
                @endif
            </div>

            <!-- 2. TABEL RIWAYAT PENGAJUAN MAGANG -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm shadow-slate-200/50 overflow-hidden">
                <div class="p-6 border-b border-slate-100">
                    <h3 class="text-base font-bold text-slate-900">Riwayat Pengajuan Magang Anda</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Daftar seluruh pengajuan penempatan yang pernah Anda kirimkan</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/60 border-b border-slate-100 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                <th class="py-3.5 px-5">Tanggal Pengajuan</th>
                                <th class="py-3.5 px-5">Unit Instansi</th>
                                <th class="py-3.5 px-5">Periode Magang</th>
                                <th class="py-3.5 px-5">Status</th>
                                <th class="py-3.5 px-5">Catatan / Alasan Admin</th>
                                <th class="py-3.5 px-5">Surat Penerimaan</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm divide-y divide-slate-100">
                            @forelse ($applicationHistory as $app)
                                <tr class="hover:bg-slate-50/70 transition-colors duration-150">
                                    <td class="py-4 px-5 text-slate-500 font-mono text-xs whitespace-nowrap">
                                        {{ $app->created_at->format('d M Y, H:i') }}
                                    </td>
                                    <td class="py-4 px-5">
                                        <div class="font-bold text-slate-900 leading-snug">{{ $app->unit->name ?? '-' }}</div>
                                        <div class="text-xs text-slate-400 mt-0.5">{{ $app->unit->agencyProfile->agency_name ?? '-' }}</div>
                                    </td>
                                    <td class="py-4 px-5 text-xs text-slate-700 whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($app->start_date)->translatedFormat('d M Y') }} s/d {{ \Carbon\Carbon::parse($app->end_date)->translatedFormat('d M Y') }}
                                    </td>
                                    <td class="py-4 px-5 whitespace-nowrap">
                                        @php $st = strtolower($app->status ?? ''); @endphp
                                        <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-bold rounded-full border
                                            {{ $st === 'accepted' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : '' }}
                                            {{ $st === 'pending' ? 'bg-amber-50 text-amber-700 border-amber-200' : '' }}
                                            {{ $st === 'verified' ? 'bg-blue-50 text-blue-700 border-blue-200' : '' }}
                                            {{ $st === 'rejected' ? 'bg-red-50 text-red-700 border-red-200' : '' }}">
                                            {{ strtoupper($app->status) }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-5 text-xs">
                                        @if ($app->status === 'rejected')
                                            <span class="text-red-700 font-medium bg-red-50 px-2.5 py-1 rounded-lg border border-red-200 inline-block">
                                                {{ $app->rejection_note ?? 'Tidak ada catatan' }}
                                            </span>
                                        @else
                                            <span class="text-slate-400">-</span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-5 whitespace-nowrap">
                                        @if ($app->status === 'accepted')
                                            <a href="{{ route('student.application.letter', $app->id) }}" target="_blank" 
                                               class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-100 rounded-xl text-xs font-semibold transition">
                                            
                                                <span>Unduh Surat PDF</span>
                                            </a>
                                        @else
                                            <span class="text-slate-400 text-xs italic">Belum tersedia</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-slate-400 text-xs">
                                        Belum ada riwayat pengajuan magang.
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