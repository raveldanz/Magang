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
                            Pengajuan
                        </a>
                    </div>
                </div>

                {{-- Card 2: Sertifikat --}}
                <div class="bg-white rounded-2xl border border-slate-200 p-5 sm:p-6 shadow-xs flex flex-col justify-between">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Sertifikat Menunggu ACC</span>
                        <div class="text-3xl sm:text-4xl font-black text-slate-900 mt-2">{{ $pendingCertificatesCount }}</div>
                        <p class="text-xs text-slate-500 mt-1">Sertifikat selesai magang perlu disahkan</p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-100">
                        <a href="{{ route('supervisor.certificates.index') }}" 
                           class="w-full inline-flex items-center justify-center py-2 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-xs transition">
                            Sertifikat
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
                <div class="md:hidden p-4 space-y-3 bg-slate-50/50">
                    @foreach($units as $unit)
                        @php
                            $unitActiveCount = $unit->applications->whereIn('status', ['accepted', 'active'])->count();
                            $unitWaitingCount = $unit->applications->where('status', \App\Enums\ApplicationStatus::WAITING_APPROVAL->value)->count();
                            $unitCompletedCount = $unit->applications->where('status', \App\Enums\ApplicationStatus::COMPLETED->value)->count();
                            $remainingQuota = max(0, $unit->quota - $unitActiveCount);
                            $uPercent = $unit->quota > 0 ? min(100, round(($unitActiveCount / $unit->quota) * 100)) : 0;
                        @endphp
                        <div class="p-4 rounded-2xl bg-white border border-slate-200/90 shadow-2xs space-y-3">
                            <div class="flex items-start justify-between gap-2.5">
                                <h3 class="font-bold text-slate-900 text-sm leading-snug flex-1 pr-2">{{ $unit->name }}</h3>
                                <span class="px-2.5 py-1 rounded-xl text-xs font-bold shrink-0 {{ $remainingQuota > 0 ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                                    Sisa: {{ $remainingQuota }}
                                </span>
                            </div>

                            {{-- Progress Bar Mini Kuota Terisi --}}
                            <div class="space-y-1">
                                <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                    <div class="h-1.5 rounded-full {{ $uPercent >= 100 ? 'bg-rose-500' : ($uPercent >= 75 ? 'bg-amber-500' : 'bg-emerald-500') }}"
                                         style="width: {{ $uPercent }}%"></div>
                                </div>
                            </div>

                            {{-- 4 Kotak Metrik Horizontal --}}
                            <div class="grid grid-cols-4 gap-2 pt-1 border-t border-slate-100" style="display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 0.5rem;">
                                <div class="bg-slate-50/80 rounded-xl p-2 text-center border border-slate-200/60">
                                    <div class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Kuota</div>
                                    <div class="text-sm font-extrabold text-slate-800 mt-0.5">{{ $unit->quota }}</div>
                                </div>
                                <div class="bg-emerald-50/50 rounded-xl p-2 text-center border border-emerald-100">
                                    <div class="text-[10px] text-emerald-600 uppercase font-bold tracking-wider">Mhs Aktif</div>
                                    <div class="text-sm font-extrabold text-emerald-700 mt-0.5">{{ $unitActiveCount }}</div>
                                </div>
                                <div class="{{ $unitWaitingCount > 0 ? 'bg-purple-50/60 border-purple-200' : 'bg-slate-50/60 border-slate-100' }} rounded-xl p-2 text-center border">
                                    <div class="text-[10px] {{ $unitWaitingCount > 0 ? 'text-purple-700' : 'text-slate-400' }} uppercase font-bold tracking-wider">ACC</div>
                                    <div class="text-sm font-extrabold {{ $unitWaitingCount > 0 ? 'text-purple-700' : 'text-slate-400' }} mt-0.5">{{ $unitWaitingCount }}</div>
                                </div>
                                <div class="bg-blue-50/50 rounded-xl p-2 text-center border border-blue-100">
                                    <div class="text-[10px] text-blue-600 uppercase font-bold tracking-wider">Mhs Lulus</div>
                                    <div class="text-sm font-extrabold text-blue-700 mt-0.5">{{ $unitCompletedCount }}</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
