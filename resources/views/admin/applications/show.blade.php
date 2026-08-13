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
                <h3 class="text-lg font-bold mb-4">Informasi Pemohon</h3>
                <div class="grid grid-cols-2 gap-4">
                    <p><strong>NIM:</strong> {{ $application->user->studentProfile->nim ?? '-' }}</p>
                    <p><strong>Universitas:</strong> {{ $application->user->studentProfile->universitas ?? '-' }}</p>
                    <p><strong>Jurusan:</strong> {{ $application->user->studentProfile->jurusan ?? '-' }}</p>
                    <p><strong>No. HP:</strong> {{ $application->user->studentProfile->phone ?? '-' }}</p>
                    <p><strong>Unit Tujuan:</strong> {{ $application->unit->name ?? '-' }}</p>
                    <p><strong>Periode Magang:</strong> {{ $application->start_date }} s/d {{ $application->end_date }}
                    </p>
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
                        <a href="{{ asset('storage/' . $application->placement->finalreport->file_path) }}" target="_blank"
                            class="px-3 py-1 bg-green-600 text-white rounded text-sm hover:bg-green-700">
                            📄 Buka Laporan Akhir
                        </a>
                    </div>
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
                        <select id="status-select" name="status" class="w-full mt-1 border-gray-300 rounded-md">
                            <option value="pending" {{ $application->status == 'pending' ? 'selected' : '' }}>PENDING
                            </option>
                            <option value="verified" {{ $application->status == 'verified' ? 'selected' : '' }}>VERIFIED
                            </option>
                            <option value="accepted" {{ $application->status == 'accepted' ? 'selected' : '' }}>ACCEPTED
                            </option>
                            <option value="rejected" {{ $application->status == 'rejected' ? 'selected' : '' }}>REJECTED
                            </option>
                        </select>
                    </div>

                    <!-- Dropdown Pembimbing (Tampil kalau Accepted/Verified) -->
                    <div id="pembimbing-box" class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Plotting Pembimbing Lapangan</label>
                        <select name="pembimbing_id" class="w-full mt-1 border-gray-300 rounded-md">
                            <option value="">-- Pilih Pembimbing --</option>
                            @foreach ($pembimbings as $pembimbing)
                                <option value="{{ $pembimbing->id }}" {{ optional($application->placement)->pembimbing_id == $pembimbing->id ? 'selected' : '' }}>
                                    {{ $pembimbing->name }}
                                </option>
                            @endforeach
                        </select>
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

                        function toggleFields() {
                            if (statusSelect.value === 'rejected') {
                                rejectionBox.classList.remove('hidden');
                            } else {
                                rejectionBox.classList.add('hidden');
                            }
                        }

                        statusSelect.addEventListener('change', toggleFields);
                        toggleFields(); // Jalankan saat awal load
                    });
                </script>
            </div>
        </div>
</x-app-layout>