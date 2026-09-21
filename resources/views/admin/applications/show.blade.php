<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Verifikasi Pengajuan: ') }} {{ $application->user->name }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('success'))
                <div class="p-4 bg-green-100 text-green-800 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Data Profil & Pengajuan -->
            <div class="bg-white p-6 shadow sm:rounded-lg">
                <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Informasi Pemohon
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <p><strong class="text-slate-600">Nama Mahasiswa:</strong> <span class="font-bold text-slate-800">{{ $application->user->name }}</span></p>
                    <p><strong class="text-slate-600">NIM:</strong> {{ $application->user->studentProfile->nim ?? '-' }}</p>
                    <p><strong class="text-slate-600">Universitas:</strong> {{ $application->user->studentProfile->universitas ?? $application->user->university ?? '-' }}</p>
                    <p><strong class="text-slate-600">Fakultas / Program Studi:</strong> {{ $application->user->studentProfile->faculty ?? $application->user->studentProfile->fakultas ?? '-' }} / {{ $application->user->studentProfile->major ?? $application->user->studentProfile->jurusan ?? '-' }}</p>
                    <p><strong class="text-slate-600">No. Handphone:</strong> {{ $application->user->studentProfile->phone ?? '-' }}</p>
                    <p><strong class="text-slate-600">Instansi Tujuan:</strong> 
                        <span class="font-bold text-blue-700">
                            {{ $application->unit->agencyProfile->agency_name ?? $application->unit->agencyProfile->name ?? 'Pemerintah Kota Surabaya' }}
                        </span>
                    </p>
                    <p><strong class="text-slate-600">Unit Tujuan / Bidang:</strong> <span class="font-semibold text-slate-800">{{ $application->unit->name ?? '-' }}</span></p>
                    <p><strong class="text-slate-600">Periode Magang:</strong> {{ \Carbon\Carbon::parse($application->start_date)->translatedFormat('d M Y') }} s/d {{ \Carbon\Carbon::parse($application->end_date)->translatedFormat('d M Y') }}</p>
                </div>
            </div>

            <!-- Dokumen Syarat -->
            <div class="bg-white p-6 shadow sm:rounded-lg">
                <h3 class="text-lg font-bold mb-4">Dokumen Persyaratan</h3>
                <ul class="space-y-2">
                    @foreach ($application->documents as $doc)
                        <li class="flex justify-between items-center border-b pb-2">
                            <span>{{ $doc->document_type }}</span>
                            <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank"
                                class="px-3 py-1 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">
                                Lihat / Download PDF
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <!-- Laporan Akhir -->
            @if ($application->placement && $application->placement->finalreport)
                <div class="bg-white p-6 shadow sm:rounded-lg">
                    <h3 class="text-lg font-bold mb-4">Laporan Akhir Mahasiswa</h3>
                    <div class="flex justify-between items-center border-b pb-2">
                        <div>
                            <p><strong>Status:</strong> <span class="uppercase font-bold text-gray-700">{{ $application->placement->finalreport->status }}</span></p>
                        </div>
                        <a href="{{ route('final_reports.show', $application->placement->finalreport->id) }}" target="_blank"
                            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-emerald-600 text-white rounded-lg text-xs font-bold hover:bg-emerald-700 shadow-xs transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            <span>Buka Naskah Laporan</span>
                        </a>
                    </div>
                </div>
            @endif

            <!-- Evaluasi Akhir & Transkrip Nilai (Jika Sudah Ada Penilaian) -->
            @if ($application->placement && $application->placement->evaluation)
                @php $eval = $application->placement->evaluation; @endphp
                <div class="bg-white p-6 shadow sm:rounded-lg border-l-4 border-indigo-500 space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-100 pb-3">
                        <div>
                            <span class="text-[11px] font-bold uppercase tracking-wider text-indigo-600 block">Evaluasi & Transkrip Kelulusan Magang</span>
                            <h3 class="text-base font-black text-gray-900 mt-0.5">Rekapitulasi Penilaian Akhir Mahasiswa</h3>
                        </div>
                        <a href="{{ route('admin.certificates.show', $application->placement->id) }}" target="_blank"
                            class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition shadow-xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span>Pratinjau E-Sertifikat & Transkrip</span>
                        </a>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 text-center">
                            <div class="text-[11px] font-semibold text-slate-500 uppercase">Nilai Disiplin</div>
                            <div class="text-lg font-black text-slate-800 mt-1">{{ number_format($eval->nilai_disiplin ?? 0, 1) }}</div>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 text-center">
                            <div class="text-[11px] font-semibold text-slate-500 uppercase">Nilai Kinerja</div>
                            <div class="text-lg font-black text-slate-800 mt-1">{{ number_format($eval->nilai_kinerja ?? 0, 1) }}</div>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 text-center">
                            <div class="text-[11px] font-semibold text-slate-500 uppercase">Nilai Laporan</div>
                            <div class="text-lg font-black text-slate-800 mt-1">{{ number_format($eval->nilai_laporan ?? 0, 1) }}</div>
                        </div>
                        <div class="p-3 bg-indigo-50 rounded-xl border border-indigo-100 text-center">
                            <div class="text-[11px] font-bold text-indigo-700 uppercase">Nilai Akhir (Predikat)</div>
                            <div class="text-lg font-black text-indigo-900 mt-1">
                                {{ number_format($eval->final_score ?? $eval->nilai_akhir ?? $eval->nilai_pembimbing ?? 0, 1) }}
                                <span class="text-xs font-bold text-indigo-600 font-mono">({{ $eval->grade ?? 'A' }})</span>
                            </div>
                        </div>
                    </div>

                    @if($eval->catatan || $eval->feedback_dosen)
                        <div class="text-xs text-slate-600 bg-slate-50 p-3 rounded-xl border border-slate-100 space-y-1">
                            @if($eval->catatan)
                                <p><strong>Catatan Pembimbing Dinas:</strong> <span class="italic">"{{ $eval->catatan }}"</span></p>
                            @endif
                            @if($eval->feedback_dosen)
                                <p><strong>Catatan Dosen DPL:</strong> <span class="italic">"{{ $eval->feedback_dosen }}"</span></p>
                            @endif
                        </div>
                    @endif
                </div>
            @endif

            <!-- Form Verifikasi Admin -->
            <div class="bg-white p-6 shadow sm:rounded-lg">
                <h3 class="text-lg font-bold mb-4">Aksi Verifikasi & Seleksi</h3>
                <form action="{{ route('admin.applications.updateStatus', $application->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <!-- Dropdown Status -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Status Pengajuan</label>
                        <select id="status-select" name="status" class="w-full mt-1 border-gray-300 rounded-md focus:border-blue-500 focus:ring-blue-500">
                            <option value="pending" {{ $application->status == 'pending' ? 'selected' : '' }}>PENDING
                            </option>

                            <option value="accepted" {{ $application->status == 'accepted' ? 'selected' : '' }}>ACCEPTED (Diterima Magang)
                            </option>
                            <option value="completed" {{ $application->status == 'completed' ? 'selected' : '' }}>COMPLETED (Selesai Magang & Lulus)
                            </option>
                            <option value="rejected" {{ $application->status == 'rejected' ? 'selected' : '' }}>REJECTED
                            </option>
                        </select>
                    </div>

                    <!-- Container Khusus Jika Status = ACCEPTED / COMPLETED -->
                    <div id="acceptance-box" class="space-y-4 mb-4 border p-4 rounded-lg bg-green-50/50 border-green-200 {{ in_array($application->status, ['accepted', 'completed']) ? '' : 'hidden' }}">
                        <h4 class="font-semibold text-green-800 border-b pb-2 text-sm">Data Balasan Penerimaan & Penempatan Magang</h4>
                        
                        <!-- Dropdown Pembimbing Lapangan -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Plotting Pembimbing Lapangan (Mentor Dinas Terkait)</label>
                            <select name="mentor_id" class="w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                                <option value="">-- Pilih Pembimbing Lapangan ({{ $application->unit->agencyProfile->agency_name ?? 'Instansi' }}) --</option>
                                @foreach ($pembimbings as $pembimbing)
                                    <option value="{{ $pembimbing->id }}" {{ (optional($application->placement)->mentor_id == $pembimbing->id || optional($application->placement)->pembimbing_id == $pembimbing->id) ? 'selected' : '' }}>
                                        {{ $pembimbing->name }} ({{ $pembimbing->email }})
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Hanya menampilkan akun mentor resmi yang terdaftar di {{ $application->unit->agencyProfile->agency_name ?? 'instansi ini' }}.</p>
                        </div>

                        <!-- Dropdown Dosen Pembimbing Lapangan (DPL Kampus) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Plotting Dosen Pembimbing Lapangan (DPL Kampus)</label>
                            <select name="academic_advisor_id" class="w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                                <option value="">-- Pilih Dosen Pembimbing Lapangan (Opsional / Kampus Mitra) --</option>
                                @foreach ($dosens as $dosen)
                                    <option value="{{ $dosen->id }}" {{ optional($application->placement)->academic_advisor_id == $dosen->id ? 'selected' : '' }}>
                                        {{ $dosen->name }} ({{ $dosen->email }})
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Dosen DPL resmi dari perguruan tinggi mahasiswa yang bersangkutan.</p>
                        </div>

                        <!-- Grid Nomor Surat & Tanggal Surat -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nomor Surat Balasan Dinas</label>
                                <input type="text" name="letter_number" value="{{ old('letter_number', $application->letter_number) }}" 
                                    placeholder="Contoh: 500/123/APTIKA/2026"
                                    class="w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Tanggal Surat Balasan</label>
                                <input type="date" name="letter_date" value="{{ old('letter_date', $application->letter_date ? \Carbon\Carbon::parse($application->letter_date)->format('Y-m-d') : date('Y-m-d')) }}" 
                                    class="w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                            </div>
                        </div>
                    </div>

                    <!-- Field Alasan Penolakan (Tampil KHUSUS kalau REJECTED) -->
                    <div id="rejection-box" class="mb-4 {{ $application->status == 'rejected' ? '' : 'hidden' }}">
                        <label class="block text-sm font-medium text-red-700 font-bold">Alasan Penolakan</label>
                        <textarea name="rejection_note" rows="3" placeholder="Tuliskan alasan pengajuan ditolak..."
                            class="w-full mt-1 border-red-300 rounded-md focus:ring-red-500 focus:border-red-500">{{ $application->rejection_note }}</textarea>
                    </div>

                    <div class="mt-4 flex items-center space-x-3">
                        <x-primary-button type="submit">
                            {{ __('Simpan Perubahan Status') }}
                        </x-primary-button>

                        @if (in_array($application->status, ['accepted', 'completed']))
                            <a href="{{ route('admin.applications.letter', $application->id) }}" target="_blank" 
                                class="inline-flex items-center space-x-1 px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-800 transition shadow-sm">
                                <span>Pratinjau / Cetak Surat PDF</span>
                            </a>
                        @endif

                        @if ($application->placement && ($application->status === 'completed' || $application->placement->evaluation))
                            <a href="{{ route('admin.certificates.show', $application->placement->id) }}" target="_blank" 
                                class="inline-flex items-center space-x-1 px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-800 transition shadow-sm">
                                <span>E-Sertifikat & Transkrip</span>
                            </a>
                        @endif

                        <a href="{{ route('admin.applications.index') }}">
                            <x-secondary-button type="button">
                                {{ __('Kembali') }}
                            </x-secondary-button>
                        </a>
                    </div>
                </form>

                <!-- JavaScript Otomatis Tampil/Sembunyi Input -->
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        const statusSelect = document.getElementById('status-select');
                        const rejectionBox = document.getElementById('rejection-box');
                        const acceptanceBox = document.getElementById('acceptance-box');

                        function toggleFields() {
                            if (statusSelect.value === 'rejected') {
                                rejectionBox.classList.remove('hidden');
                                acceptanceBox.classList.add('hidden');
                            } else if (statusSelect.value === 'accepted' || statusSelect.value === 'completed') {
                                acceptanceBox.classList.remove('hidden');
                                rejectionBox.classList.add('hidden');
                            } else {
                                rejectionBox.classList.add('hidden');
                                acceptanceBox.classList.add('hidden');
                            }
                        }

                        statusSelect.addEventListener('change', toggleFields);
                        toggleFields(); // Jalankan saat awal load
                    });
                </script>
            </div>
        </div>
</x-app-layout>