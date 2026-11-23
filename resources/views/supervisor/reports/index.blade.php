<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-xl sm:text-2xl text-slate-900 leading-tight">
                    Laporan Peserta Magang
                </h2>
                <p class="text-sm text-slate-500 mt-0.5">
                    Pemantauan luaran naskah laporan dan proyek inovasi peserta magang
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- 1. Ringkasan Metrik Pemantauan (Style Dinas Bersih) -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/90 shadow-2xs flex items-center justify-between gap-3 sm:block sm:space-y-1">
                    <span class="text-sm sm:text-xs font-semibold text-slate-600 uppercase tracking-wider block">
                        Total Peserta Magang
                    </span>
                    <div class="text-2xl font-bold text-slate-900">
                        {{ $totalPlacements }}
                    </div>
                    <span class="hidden sm:block text-xs text-slate-600">
                        Peserta penempatan aktif dan selesai
                    </span>
                </div>

                <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/90 shadow-2xs flex items-center justify-between gap-3 sm:block sm:space-y-1">
                    <span class="text-sm sm:text-xs font-semibold text-slate-600 uppercase tracking-wider block">
                        Proyek Inovasi Tercipta
                    </span>
                    <div class="text-2xl font-bold text-slate-900">
                        {{ $totalProjects }}
                    </div>
                    <span class="hidden sm:block text-xs text-slate-600">
                        Karya luaran proyek untuk masyarakat
                    </span>
                </div>

                <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/90 shadow-2xs flex items-center justify-between gap-3 sm:block sm:space-y-1">
                    <span class="text-sm sm:text-xs font-semibold text-slate-600 uppercase tracking-wider block">
                        Naskah Laporan Disetujui
                    </span>
                    <div class="text-2xl font-bold text-slate-900">
                        {{ $totalApprovedReports }}
                    </div>
                    <span class="hidden sm:block text-xs text-slate-600">
                        Telah disetujui pembimbing
                    </span>
                </div>
            </div>

            <!-- 2. Filter Bar Rapi & Kondusif -->
            @php $filterAktif = (bool) ($searchQuery || $unitFilter || $statusFilter); @endphp
            <form method="GET" action="{{ route('supervisor.reports.index') }}" x-data="{ buka: @js($filterAktif) }" class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/90 shadow-2xs flex flex-col md:flex-row gap-3">
                <button type="button" @click="buka = !buka" :aria-expanded="buka.toString()" class="sm:hidden w-full min-h-[48px] inline-flex items-center justify-between gap-2 text-base font-bold text-slate-900"><span>Cari atau saring laporan</span><svg class="w-5 h-5 transition" :class="buka && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg></button>

                <div class="flex-1 {{ $filterAktif ? '' : 'hidden' }} sm:!block" :class="{ 'hidden': !buka }">
                    <input type="text" name="search" value="{{ $searchQuery }}" placeholder="Cari nama peserta magang, NIM/NIS, atau kampus/sekolah..." class="min-h-[52px] sm:min-h-0 w-full text-base sm:text-sm border-slate-300 rounded-xl focus:ring-blue-600 focus:border-blue-600">
                </div>

                <div class="w-full md:w-56 {{ $filterAktif ? '' : 'hidden' }} sm:!block" :class="{ 'hidden': !buka }">
                    <select name="unit_id" class="min-h-[52px] sm:min-h-0 w-full text-base sm:text-sm border-slate-300 rounded-xl focus:ring-blue-600 focus:border-blue-600">
                        <option value="">Semua Unit Bidang</option>
                        @foreach ($units as $u)
                            <option value="{{ $u->id }}" {{ (string)$unitFilter === (string)$u->id ? 'selected' : '' }}>
                                {{ $u->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="w-full md:w-52 {{ $filterAktif ? '' : 'hidden' }} sm:!block" :class="{ 'hidden': !buka }">
                    <select name="status" class="min-h-[52px] sm:min-h-0 w-full text-base sm:text-sm border-slate-300 rounded-xl focus:ring-blue-600 focus:border-blue-600">
                        <option value="">Semua Status Laporan</option>
                        <option value="approved" {{ $statusFilter === 'approved' ? 'selected' : '' }}>Disetujui</option>{{-- status-guard:ignore --}}
                        <option value="pending" {{ $statusFilter === 'pending' ? 'selected' : '' }}>Menunggu Tinjauan</option>{{-- status-guard:ignore --}}
                        <option value="revision" {{ $statusFilter === 'revision' ? 'selected' : '' }}>Perlu Revisi</option>{{-- status-guard:ignore --}}
                        <option value="not_uploaded" {{ $statusFilter === 'not_uploaded' ? 'selected' : '' }}>Belum Mengunggah</option>{{-- status-guard:ignore --}}
                    </select>
                </div>

                <div class="flex items-center gap-2 shrink-0 {{ $filterAktif ? '' : 'hidden' }} sm:!flex" :class="{ 'hidden': !buka }">
                    <button type="submit" class="min-h-[52px] sm:min-h-0 flex-1 sm:flex-none inline-flex items-center justify-center px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-base sm:text-xs font-bold rounded-xl transition shadow-2xs">
                        Terapkan
                    </button>
                    @if ($searchQuery || $unitFilter || $statusFilter)
                        <a href="{{ route('supervisor.reports.index') }}" class="min-h-[52px] sm:min-h-0 inline-flex items-center justify-center px-3.5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-base sm:text-xs font-bold rounded-xl transition">
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
                            <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 text-xs uppercase tracking-wider font-bold select-none">
                                <th class="py-3.5 px-5 min-w-[220px]">Peserta Magang</th>
                                <th class="py-3.5 px-5 min-w-[200px]">Unit Bidang</th>
                                <th class="py-3.5 px-5 min-w-[180px]">Proyek Inovasi</th>
                                <th class="py-3.5 px-5 whitespace-nowrap min-w-[150px]">Status Laporan</th>
                                <th class="py-3.5 pl-4 pr-6 text-right whitespace-nowrap w-24">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
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
                                                <div class="font-bold text-slate-900 text-sm leading-tight truncate" title="{{ $student?->name ?? '-' }}">
                                                    {{ $student?->name ?? '-' }}
                                                </div>
                                                <div class="text-xs text-slate-600 font-mono mt-0.5 truncate" title="NIM: {{ $profile?->nim ?? '-' }} &bull; {{ $profile?->universitas ?? '-' }}">
                                                    NIM/NIS: {{ $profile?->nim ?? '-' }} &bull; {{ $profile?->universitas ?? '-' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-5">
                                        <div class="font-semibold text-slate-800 leading-snug">{{ $unit?->name ?? '-' }}</div>
                                        <div class="text-xs text-slate-600 mt-0.5">
                                            Mentor: {{ $mentor?->name ?? 'Belum Ditugaskan' }}
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-5">
                                        @if ($projectsCount > 0)
                                            <div>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200/80">
                                                    {{ $projectsCount }} Proyek
                                                </span>
                                                <span class="block text-xs text-slate-600 font-medium truncate max-w-[190px] mt-0.5" title="{{ $item->internshipProjects->first()->title ?? '' }}">
                                                    {{ $item->internshipProjects->first()->title ?? '' }}
                                                </span>
                                            </div>
                                        @else
                                            <span class="text-slate-600 text-xs">Belum Ada Proyek</span>
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
                                        Tidak ada data peserta magang yang sesuai dengan filter pencarian.
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
                                    <h4 class="font-bold text-base sm:text-sm text-slate-900">{{ $student?->name ?? '-' }}</h4>
                                    <p class="text-sm sm:text-xs text-slate-500 mt-0.5">{{ $profile?->nim ?? '-' }} &bull; {{ $profile?->universitas ?? '-' }}</p>
                                </div>
                                <div>
                                    @if ($finalReport)
                                        <x-status-badge type="review" :status="$finalReport->status" />
                                    @else
                                        <span class="px-2.5 py-1 bg-slate-100 text-slate-700 rounded-full text-sm sm:text-xs font-medium">
                                            Belum Mengunggah
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="p-3 bg-slate-50 rounded-xl space-y-1 text-sm sm:text-xs">
                                <div class="text-slate-600">Bidang: <strong>{{ $unit?->name ?? '-' }}</strong></div>
                                <div class="text-slate-600">Proyek: <strong>{{ $projectsCount > 0 ? $projectsCount . ' Proyek' : 'Belum Ada' }}</strong></div>
                            </div>

                            <div class="pt-1">
                                <a href="{{ route('supervisor.reports.show', $item->id) }}" class="min-h-[52px] sm:min-h-0 w-full py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-base sm:text-xs font-bold rounded-xl text-center flex items-center justify-center transition">
                                    Lihat Detail Laporan & Proyek
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-slate-500 text-sm sm:text-xs">
                            Tidak ada data peserta magang ditemukan.
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
