<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-xl sm:text-2xl text-slate-900 leading-tight">
                    Laporan Mahasiswa
                </h2>
                <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                    Pemantauan luaran naskah laporan dan proyek inovasi mahasiswa magang
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- 1. Ringkasan Metrik Pemantauan (Style Dinas Bersih) -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-2xs space-y-1">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">
                        Total Mahasiswa Magang
                    </span>
                    <div class="text-2xl font-bold text-slate-900">
                        {{ $totalPlacements }}
                    </div>
                    <span class="text-[11px] text-slate-400 block">
                        Peserta penempatan aktif dan selesai
                    </span>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-2xs space-y-1">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">
                        Proyek Inovasi Tercipta
                    </span>
                    <div class="text-2xl font-bold text-slate-900">
                        {{ $totalProjects }}
                    </div>
                    <span class="text-[11px] text-slate-400 block">
                        Karya luaran proyek untuk masyarakat
                    </span>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-2xs space-y-1">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">
                        Naskah Laporan Disetujui
                    </span>
                    <div class="text-2xl font-bold text-slate-900">
                        {{ $totalApprovedReports }}
                    </div>
                    <span class="text-[11px] text-slate-400 block">
                        Telah disetujui pembimbing
                    </span>
                </div>
            </div>

            <!-- 2. Filter Bar Rapi & Kondusif -->
            <form method="GET" action="{{ route('supervisor.reports.index') }}" class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/90 shadow-2xs flex flex-col md:flex-row gap-3">
                <div class="flex-1">
                    <input type="text" name="search" value="{{ $searchQuery }}" placeholder="Cari nama mahasiswa, NIM, atau universitas..." class="w-full text-xs sm:text-sm border-slate-300 rounded-xl focus:ring-blue-600 focus:border-blue-600">
                </div>

                <div class="w-full md:w-56">
                    <select name="unit_id" class="w-full text-xs sm:text-sm border-slate-300 rounded-xl focus:ring-blue-600 focus:border-blue-600">
                        <option value="">Semua Bidang Devisi</option>
                        @foreach ($units as $u)
                            <option value="{{ $u->id }}" {{ (string)$unitFilter === (string)$u->id ? 'selected' : '' }}>
                                {{ $u->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="w-full md:w-52">
                    <select name="status" class="w-full text-xs sm:text-sm border-slate-300 rounded-xl focus:ring-blue-600 focus:border-blue-600">
                        <option value="">Semua Status Laporan</option>
                        <option value="approved" {{ $statusFilter === 'approved' ? 'selected' : '' }}>Laporan Disetujui</option>{{-- status-guard:ignore --}}
                        <option value="pending" {{ $statusFilter === 'pending' ? 'selected' : '' }}>Menunggu Review</option> {{-- status-guard:ignore --}}{{-- status-guard:ignore --}}
                        <option value="revision" {{ $statusFilter === 'revision' ? 'selected' : '' }}>Perlu Revisi</option>{{-- status-guard:ignore --}}
                        <option value="not_uploaded" {{ $statusFilter === 'not_uploaded' ? 'selected' : '' }}>Belum Mengunggah</option>{{-- status-guard:ignore --}}
                    </select>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl transition shadow-2xs">
                        Terapkan
                    </button>
                    @if ($searchQuery || $unitFilter || $statusFilter)
                        <a href="{{ route('supervisor.reports.index') }}" class="px-3.5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
                            Reset
                        </a>
                    @endif
                </div>
            </form>

            <!-- 3. Tabel Data Mahasiswa (Desktop Table) -->
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden">
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 text-[11px] uppercase tracking-wider font-bold select-none">
                                <th class="py-3.5 px-5 min-w-[220px]">Mahasiswa</th>
                                <th class="py-3.5 px-5 min-w-[200px]">Bidang Devisi</th>
                                <th class="py-3.5 px-5 min-w-[180px]">Proyek Inovasi</th>
                                <th class="py-3.5 px-5 whitespace-nowrap min-w-[150px]">Status Laporan</th>
                                <th class="py-3.5 pl-4 pr-6 text-right whitespace-nowrap w-24">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs sm:text-sm">
                            @forelse ($placements as $item)
                                @php
                                    $student = $item->application?->user;
                                    $profile = $student?->studentProfile;
                                    $unit = $item->application?->unit;
                                    $mentor = $item->mentor;
                                    $finalReport = $item->finalreport;
                                    $projectsCount = $item->internshipProjects->count();
                                @endphp
                                <tr class="hover:bg-slate-50/70 transition-colors duration-150">
                                    <td class="py-3.5 px-5">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-xl bg-blue-50 border border-blue-200/80 text-blue-700 font-extrabold text-xs flex items-center justify-center shrink-0 shadow-2xs">
                                                {{ strtoupper(substr($student?->name ?? 'M', 0, 1)) }}
                                            </div>
                                            <div class="min-w-0 max-w-[200px] lg:max-w-[240px]">
                                                <div class="font-bold text-slate-900 text-xs sm:text-sm leading-tight truncate" title="{{ $student?->name ?? '-' }}">
                                                    {{ $student?->name ?? '-' }}
                                                </div>
                                                <div class="text-[11px] text-slate-500 font-mono mt-0.5 truncate" title="NIM: {{ $profile?->nim ?? '-' }} &bull; {{ $profile?->universitas ?? '-' }}">
                                                    NIM: {{ $profile?->nim ?? '-' }} &bull; {{ $profile?->universitas ?? '-' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-5">
                                        <div class="font-semibold text-slate-800 leading-snug">{{ $unit?->name ?? '-' }}</div>
                                        <div class="text-[11px] text-slate-500 mt-0.5">
                                            Mentor: {{ $mentor?->name ?? 'Belum Ditugaskan' }}
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-5">
                                        @if ($projectsCount > 0)
                                            <div>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200/80">
                                                    {{ $projectsCount }} Proyek
                                                </span>
                                                <span class="block text-[11px] text-slate-600 font-medium truncate max-w-[190px] mt-0.5" title="{{ $item->internshipProjects->first()->title ?? '' }}">
                                                    {{ $item->internshipProjects->first()->title ?? '' }}
                                                </span>
                                            </div>
                                        @else
                                            <span class="text-slate-400 text-xs">Belum Ada Proyek</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-5 whitespace-nowrap">
                                        @if ($finalReport)
                                            <x-status-badge type="review" :status="$finalReport->status" />
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 bg-slate-100 text-slate-600 rounded-full text-xs font-medium whitespace-nowrap">
                                                Belum Mengunggah
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 pl-4 pr-6 text-right whitespace-nowrap">
                                        <a href="{{ route('supervisor.reports.show', $item->id) }}" class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 bg-slate-100 hover:bg-slate-800 hover:text-white text-slate-700 text-xs font-semibold rounded-lg transition whitespace-nowrap shadow-2xs">
                                            <span>Detail</span>
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-10 text-center text-slate-500 text-xs">
                                        Tidak ada data mahasiswa yang sesuai dengan filter pencarian.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Versi Mobile Kartu Bertumpuk -->
                <div class="md:hidden divide-y divide-slate-100">
                    @forelse ($placements as $item)
                        @php
                            $student = $item->application?->user;
                            $profile = $student?->studentProfile;
                            $unit = $item->application?->unit;
                            $finalReport = $item->finalreport;
                            $projectsCount = $item->internshipProjects->count();
                        @endphp
                        <div class="p-4 space-y-3">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h4 class="font-bold text-sm text-slate-900">{{ $student?->name ?? '-' }}</h4>
                                    <p class="text-xs text-slate-500 mt-0.5">{{ $profile?->nim ?? '-' }} &bull; {{ $profile?->universitas ?? '-' }}</p>
                                </div>
                                <div>
                                    @if ($finalReport)
                                        <x-status-badge type="review" :status="$finalReport->status" />
                                    @else
                                        <span class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded-full text-[11px] font-medium">
                                            Belum Upload
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="p-3 bg-slate-50 rounded-xl space-y-1 text-xs">
                                <div class="text-slate-600">Bidang: <strong>{{ $unit?->name ?? '-' }}</strong></div>
                                <div class="text-slate-600">Proyek: <strong>{{ $projectsCount > 0 ? $projectsCount . ' Proyek' : 'Belum Ada' }}</strong></div>
                            </div>

                            <div class="pt-1">
                                <a href="{{ route('supervisor.reports.show', $item->id) }}" class="w-full py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl text-center block transition">
                                    Lihat Detail Laporan & Proyek
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-slate-500 text-xs">
                            Tidak ada data mahasiswa ditemukan.
                        </div>
                    @endforelse
                </div>

                <!-- Pagination -->
                @if ($placements->hasPages())
                    <div class="p-4 border-t border-slate-100">
                        {{ $placements->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
