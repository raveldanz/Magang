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

            <!-- Form Verifikasi Admin -->
            <div class="bg-white p-6 shadow sm:rounded-lg">
                <h3 class="text-lg font-bold mb-4">Aksi Verifikasi & Seleksi</h3>
                <form action="{{ route('admin.applications.updateStatus', $application->id) }}" method="POST"
                    class="space-y-4">
                    @csrf
                    @method('PATCH')

                    <div>
                        <x-input-label for="status" value="Ubah Status Pengajuan" />
                        <select id="status" name="status"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="pending" {{ $application->status == 'pending' ? 'selected' : '' }}>PENDING
                            </option>
                            <option value="verified" {{ $application->status == 'verified' ? 'selected' : '' }}>VERIFIED
                                (Lolos Berkas)</option>
                            <option value="accepted" {{ $application->status == 'accepted' ? 'selected' : '' }}>ACCEPTED
                                (Diterima Magang)</option>
                            <option value="rejected" {{ $application->status == 'rejected' ? 'selected' : '' }}>REJECTED
                                (Ditolak)</option>
                        </select>
                    </div>

                    <div>
                        <x-input-label for="pembimbing_id" value="Plot Pembimbing Lapangan (Opsional saat Accepted)" />
                        <select id="pembimbing_id" name="pembimbing_id"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="">-- Pilih Pembimbing --</option>
                            @foreach ($pembimbings as $p)
                                <option value="{{ $p->id }}" {{ (optional($application->placement)->pembimbing_id == $p->id) ? 'selected' : '' }}>
                                    {{ $p->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <x-input-label for="rejection_note" value="Catatan Penolakan (Jika Ditolak)" />
                        <textarea id="rejection_note" name="rejection_note"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">{{ $application->rejection_note }}</textarea>
                    </div>

                    <x-primary-button>
                        {{ __('Simpan Perubahan Status') }}
                    </x-primary-button>

                    <a href="{{ url('admin/applications') }}">
                        <x-secondary-button>
                            {{ __('Kembali') }}
                        </x-secondary-button>
                    </a>

            </div>
        </div>
</x-app-layout>