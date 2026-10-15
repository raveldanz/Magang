<x-app-layout>
    <x-slot name="header">
        <x-page-header 
            title="Monitoring Bidang & Kepegawaian Magang" 
            description="Pantau kuota penempatan, sebaran mahasiswa magang aktif di 4 Bidang Resmi Dispusip, serta progres persetujuan." 
        />
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- 1. KARTU STATISTIK UTAMA (SIMPEL MINIMALIS MODERN - STYLE DINAS) --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                <!-- Card 1: Pengajuan Menunggu Persetujuan -->
                <div class="bg-white rounded-2xl border border-slate-200 p-5 sm:p-6 shadow-xs flex flex-col justify-between">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Perlu Persetujuan</span>
                        <div class="text-3xl sm:text-4xl font-black text-slate-900 mt-2">{{ $pendingApplicationsCount }}</div>
                        <p class="text-xs text-slate-500 mt-1">Pengajuan magang menunggu persetujuan atasan</p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-100">
                        <a href="{{ route('supervisor.applications.index') }}" 
                           class="w-full inline-flex items-center justify-center py-2 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-xs transition">
                            Persetujuan Pengajuan
                        </a>
                    </div>
                </div>

                <!-- Card 2: Pengesahan Sertifikat -->
                <div class="bg-white rounded-2xl border border-slate-200 p-5 sm:p-6 shadow-xs flex flex-col justify-between">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Sertifikat Menunggu ACC</span>
                        <div class="text-3xl sm:text-4xl font-black text-slate-900 mt-2">{{ $pendingCertificatesCount }}</div>
                        <p class="text-xs text-slate-500 mt-1">Sertifikat selesai magang perlu disahkan</p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-100">
                        <a href="{{ route('supervisor.certificates.index') }}" 
                           class="w-full inline-flex items-center justify-center py-2 px-4 rounded-xl bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs border border-slate-200 shadow-xs transition">
                            Pengesahan Sertifikat
                        </a>
                    </div>
                </div>

                <!-- Card 3: Mahasiswa Aktif -->
                <div class="bg-white rounded-2xl border border-slate-200 p-5 sm:p-6 shadow-xs flex flex-col justify-between">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Mahasiswa Aktif</span>
                        <div class="text-3xl sm:text-4xl font-black text-slate-900 mt-2">{{ $totalActiveInterns }}</div>
                        <p class="text-xs text-slate-500 mt-1">Sedang bertugas di unit kerja</p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-100">
                        <span class="text-xs text-slate-500 font-medium">{{ $totalMentors }} Mentor Pembimbing</span>
                    </div>
                </div>

                <!-- Card 4: Mahasiswa Lulus -->
                <div class="bg-white rounded-2xl border border-slate-200 p-5 sm:p-6 shadow-xs flex flex-col justify-between">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Alumni Selesai Magang</span>
                        <div class="text-3xl sm:text-4xl font-black text-slate-900 mt-2">{{ $totalCompletedInterns }}</div>
                        <p class="text-xs text-slate-500 mt-1">Total mahasiswa telah tuntas magang</p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-100">
                        <span class="text-xs text-slate-500 font-medium">Telah menyelesaikan program magang</span>
                    </div>
                </div>
            </div>

            {{-- 2. TABEL BIDANG PENEMPATAN (SIMPEL & MINIMALIS) --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="p-5 sm:p-6 bg-slate-50 border-b border-slate-200">
                    <h2 class="text-base sm:text-lg font-bold text-slate-900">
                        Bidang Penempatan Magang
                    </h2>
                </div>

                {{-- Tabel Matriks Bidang (Desktop & Tablet) --}}
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-200 bg-slate-100/70 text-slate-700 text-xs font-bold uppercase tracking-wider">
                                <th class="py-3.5 px-6">Bidang</th>
                                <th class="py-3.5 px-4 text-center">Kuota Total</th>
                                <th class="py-3.5 px-4 text-center">Mhs Aktif</th>
                                <th class="py-3.5 px-4 text-center">Sisa Kuota</th>
                                <th class="py-3.5 px-4 text-center">Menunggu ACC</th>
                                <th class="py-3.5 px-4 text-center">Mhs Lulus</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 text-sm">
                            @forelse($units as $unit)
                                @php
                                    $unitActiveCount = $unit->applications->whereIn('status', ['accepted', 'active'])->count();
                                    $unitWaitingCount = $unit->applications->where('status', \App\Enums\ApplicationStatus::WAITING_APPROVAL->value)->count();
                                    $unitCompletedCount = $unit->applications->where('status', \App\Enums\ApplicationStatus::COMPLETED->value)->count();
                                    $remainingQuota = max(0, $unit->quota - $unitActiveCount);
                                @endphp
                                <tr class="hover:bg-slate-50/80 transition">
                                    {{-- Nama Bidang --}}
                                    <td class="py-4 px-6">
                                        <div class="font-bold text-slate-900">{{ $unit->name }}</div>
                                    </td>

                                    {{-- Kuota Total --}}
                                    <td class="py-4 px-4 text-center font-semibold text-slate-700">
                                        {{ $unit->quota }}
                                    </td>

                                    {{-- Mhs Aktif --}}
                                    <td class="py-4 px-4 text-center font-bold text-slate-900">
                                        {{ $unitActiveCount }}
                                    </td>

                                    {{-- Sisa Kuota --}}
                                    <td class="py-4 px-4 text-center font-bold {{ $remainingQuota > 0 ? 'text-blue-600' : 'text-slate-400' }}">
                                        {{ $remainingQuota }}
                                    </td>

                                    {{-- Menunggu ACC --}}
                                    <td class="py-4 px-4 text-center">
                                        @if($unitWaitingCount > 0)
                                            <a href="{{ route('supervisor.applications.index', ['unit_id' => $unit->id, 'status' => 'waiting_approval']) }}"
                                               class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 transition">
                                                <span>{{ $unitWaitingCount }}</span>
                                            </a>
                                        @else
                                            <span class="text-xs font-semibold text-slate-400">0</span>
                                        @endif
                                    </td>

                                    {{-- Mahasiswa Lulus --}}
                                    <td class="py-4 px-4 text-center font-medium text-slate-600">
                                        {{ $unitCompletedCount }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-slate-400 font-medium">
                                        Belum ada data bidang penempatan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Footer Info Bar untuk menambah kenyamanan visual di bagian bawah tabel --}}
                <div class="hidden md:flex px-6 py-4 bg-slate-50/60 border-t border-slate-100 items-center justify-between text-xs text-slate-500">
                    <span class="font-medium">Total: {{ $units->count() }} Bidang Penempatan</span>
                    <span class="font-semibold text-slate-600">Dinas Perpustakaan dan Kearsipan Kota Surabaya</span>
                </div>

                {{-- Tampilan Mobile / Tablet --}}
                <div class="md:hidden p-4 space-y-3">
                    @foreach($units as $unit)
                        @php
                            $unitActiveCount = $unit->applications->whereIn('status', ['accepted', 'active'])->count();
                            $unitWaitingCount = $unit->applications->where('status', \App\Enums\ApplicationStatus::WAITING_APPROVAL->value)->count();
                            $unitCompletedCount = $unit->applications->where('status', \App\Enums\ApplicationStatus::COMPLETED->value)->count();
                            $remainingQuota = max(0, $unit->quota - $unitActiveCount);
                        @endphp
                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-2.5">
                            <div class="flex items-start justify-between gap-3">
                                <h3 class="font-bold text-slate-900 text-sm">{{ $unit->name }}</h3>
                                <span class="px-2 py-0.5 rounded-md text-xs font-bold {{ $remainingQuota > 0 ? 'text-blue-700 bg-blue-50 border border-blue-200' : 'text-slate-500 bg-slate-200' }}">
                                    Sisa: {{ $remainingQuota }}
                                </span>
                            </div>

                            <div class="grid grid-cols-4 gap-2 text-center text-xs py-2 border-t border-slate-200">
                                <div>
                                    <div class="text-[10px] text-slate-500 uppercase font-bold">Kuota</div>
                                    <div class="font-bold text-slate-800">{{ $unit->quota }}</div>
                                </div>
                                <div>
                                    <div class="text-[10px] text-slate-500 uppercase font-bold">Mhs Aktif</div>
                                    <div class="font-bold text-slate-900">{{ $unitActiveCount }}</div>
                                </div>
                                <div>
                                    <div class="text-[10px] text-slate-500 uppercase font-bold">ACC</div>
                                    <div class="font-bold text-blue-700">{{ $unitWaitingCount }}</div>
                                </div>
                                <div>
                                    <div class="text-[10px] text-slate-500 uppercase font-bold">Mhs Lulus</div>
                                    <div class="font-bold text-slate-700">{{ $unitCompletedCount }}</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
