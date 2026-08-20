<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard Mahasiswa') }}
        </h2>
    </x-slot>

    @php
        $profile = Auth::user()->studentProfile;
        $application = $profile
            ? \App\Models\Application::with(['unit', 'placement.pembimbing', 'placement.mentor', 'placement.evaluation', 'placement.finalreport'])
                ->where('user_id', Auth::id())->latest()->first()
            : null;

        $placement = optional($application)->placement;
        $logbooks  = $placement ? $placement->logbooks()->orderBy('date', 'desc')->get() : collect();
        $finalReport = optional($placement)->finalreport;
        $evaluation  = optional($placement)->evaluation;
        $certificate = $placement ? \App\Models\Certificate::where('placement_id', $placement->id)->first() : null;

        $logStats = [
            'total'    => $logbooks->count(),
            'approved' => $logbooks->where('status', 'approved')->count(),
            'pending'  => $logbooks->where('status', 'pending')->count(),
            'rejected' => $logbooks->where('status', 'rejected')->count(),
        ];
        $approvalRate = $logStats['total'] > 0 ? round(($logStats['approved'] / $logStats['total']) * 100) : 0;

        $isPassed = $application && $application->status === 'accepted'
            && $evaluation
            && optional($finalReport)->status === 'approved';
    @endphp

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Welcome Banner -->
            <div class="bg-gradient-to-r from-slate-700 to-slate-900 rounded-2xl p-6 text-white shadow-lg flex flex-col md:flex-row justify-between md:items-center gap-3">
                <div>
                    <h3 class="text-2xl font-bold">Selamat Datang, {{ Auth::user()->name }}!</h3>
                    <p class="text-slate-300 mt-1 text-sm">Sistem Informasi Penerimaan Magang Instansi Pemerintahan Kota Surabaya</p>
                </div>
                <span class="self-start md:self-auto bg-white/10 text-slate-100 text-xs font-semibold px-3 py-1.5 rounded-full uppercase tracking-wide w-max">
                    {{ ucfirst(Auth::user()->role ?? 'Mahasiswa') }}
                </span>
            </div>

            <!-- Banner Kelulusan & Unduh E-Sertifikat -->
            @if ($isPassed)
            <div class="bg-gradient-to-r from-emerald-500 to-emerald-700 rounded-2xl p-6 text-white shadow-xl flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-white/20 rounded-full shrink-0">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold">🎉 Selamat! Kamu telah menyelesaikan seluruh rangkaian magang.</h3>
                        <p class="text-emerald-100 mt-1 text-sm">Laporan akhir sudah disetujui dan penilaian sudah lengkap. E-Sertifikat resmi siap diunduh.</p>
                    </div>
                </div>
                <a href="{{ route('student.certificate.download', $placement->id) }}"
                   class="shrink-0 inline-flex items-center gap-2 px-5 py-2.5 bg-white text-emerald-800 font-bold text-sm rounded-xl shadow-md hover:bg-emerald-50 hover:shadow-lg transition">
                    <span>📜</span><span>Unduh E-Sertifikat</span>
                </a>
            </div>
            @endif

            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl p-4">{{ session('error') }}</div>
            @endif
            @if(session('success'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm rounded-xl p-4">{{ session('success') }}</div>
            @endif

            <!-- Bento Grid: Metrics & Quick Actions -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                <!-- Metrics Cluster -->
                <div class="lg:col-span-8 grid grid-cols-1 sm:grid-cols-2 gap-4">

                    <!-- Metric: Total Logbook -->
                    <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-[0_4px_12px_rgba(100,116,139,0.08)] hover:shadow-md transition-all flex flex-col justify-between min-h-[135px]">
                        <div class="flex justify-between items-start">
                            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Logbook</p>
                            <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                            </div>
                        </div>
                        <div class="mt-auto">
                            <h3 class="text-3xl font-bold text-slate-800">{{ $logStats['total'] }}</h3>
                            <p class="text-xs text-emerald-600 mt-1 font-medium">{{ $logStats['pending'] }} menunggu review</p>
                        </div>
                    </div>

                    <!-- Metric: Approved Logs -->
                    <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-[0_4px_12px_rgba(100,116,139,0.08)] hover:shadow-md transition-all flex flex-col justify-between min-h-[135px]">
                        <div class="flex justify-between items-start">
                            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Logbook Disetujui</p>
                            <div class="w-8 h-8 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                        </div>
                        <div class="mt-auto">
                            <h3 class="text-3xl font-bold text-slate-800">{{ $logStats['approved'] }}</h3>
                            <div class="w-full bg-slate-100 rounded-full h-1.5 mt-2 overflow-hidden">
                                <div class="bg-emerald-500 h-1.5 rounded-full" style="width: {{ $approvalRate }}%"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Metric: Final Report -->
                    <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-[0_4px_12px_rgba(100,116,139,0.08)] hover:shadow-md transition-all flex flex-col justify-between min-h-[135px]">
                        <div class="flex justify-between items-start">
                            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Laporan Akhir</p>
                            <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                        </div>
                        <div class="mt-auto">
                            @php
                                $frStatus = $finalReport?->status;
                                $frLabel = match($frStatus) {
                                    'approved' => 'Disetujui',
                                    'pending' => 'Menunggu Review',
                                    'revision' => 'Perlu Revisi',
                                    default => 'Belum Upload',
                                };
                                $frClass = match($frStatus) {
                                    'approved' => 'bg-emerald-100 text-emerald-700',
                                    'pending' => 'bg-amber-100 text-amber-700',
                                    'revision' => 'bg-red-100 text-red-700',
                                    default => 'bg-slate-100 text-slate-500',
                                };
                            @endphp
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $frClass }}">{{ $frLabel }}</span>
                        </div>
                    </div>

                    <!-- Metric: Certificate -->
                    <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-[0_4px_12px_rgba(100,116,139,0.08)] hover:shadow-md transition-all flex flex-col justify-between min-h-[135px]">
                        <div class="flex justify-between items-start">
                            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Sertifikat</p>
                            <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15a4 4 0 100-8 4 4 0 000 8zM6 21l1.5-4.5M18 21l-1.5-4.5"/></svg>
                            </div>
                        </div>
                        <div class="mt-auto">
                            @if($certificate)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">Terbit</span>
                            @elseif($isPassed)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">Siap Diunduh</span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-500 opacity-80">Belum Tersedia</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Quick Actions Panel -->
                <div class="lg:col-span-4 bg-white rounded-2xl p-5 border border-slate-100 shadow-[0_4px_12px_rgba(100,116,139,0.08)] flex flex-col gap-3">
                    <h3 class="text-base font-bold text-slate-800 mb-1">Aksi Cepat</h3>

                    @if($application && $application->status === 'accepted' && $placement)
                        <a href="{{ route('student.logbook.create') }}" class="w-full flex items-center justify-between bg-slate-800 text-white py-3 px-4 rounded-xl text-sm font-medium hover:bg-slate-900 transition-colors group">
                            <span>Isi Logbook Harian</span>
                            <span class="group-hover:translate-x-1 transition-transform">→</span>
                        </a>
                        <a href="{{ route('student.final_report.index') }}" class="w-full flex items-center justify-between bg-white border border-slate-200 text-slate-700 py-3 px-4 rounded-xl text-sm font-medium hover:bg-slate-50 transition-colors">
                            <span>Unggah Laporan Akhir</span>
                            <span>↑</span>
                        </a>
                    @else
                        <a href="{{ $profile ? route('student.application.create') : route('student.profile.edit') }}" class="w-full flex items-center justify-between bg-slate-800 text-white py-3 px-4 rounded-xl text-sm font-medium hover:bg-slate-900 transition-colors">
                            <span>{{ $profile ? 'Buat Pengajuan Magang' : 'Lengkapi Profil Dulu' }}</span>
                            <span>→</span>
                        </a>
                    @endif

                    <button type="button" disabled
                        class="w-full flex items-center justify-between bg-slate-50 text-slate-400 py-3 px-4 rounded-xl text-sm font-medium cursor-not-allowed {{ $certificate ? '!bg-white !text-slate-700 !cursor-pointer hover:!bg-slate-50 border border-slate-200' : '' }}">
                        @if($certificate)
                            <a href="{{ route('student.certificate.download', $placement->id) }}" class="flex w-full items-center justify-between">
                                <span>Unduh Sertifikat</span><span>↓</span>
                            </a>
                        @else
                            <span>Unduh Sertifikat</span><span>↓</span>
                        @endif
                    </button>
                </div>
            </div>

            <!-- Status Pengajuan & Pembimbing -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Status Profil -->
                <div class="bg-white p-5 rounded-2xl shadow-[0_4px_12px_rgba(100,116,139,0.08)] border-l-4 {{ $profile ? 'border-emerald-500' : 'border-amber-500' }}">
                    <p class="text-xs font-medium text-slate-400 uppercase tracking-wide">Status Profil</p>
                    <p class="text-lg font-bold mt-1 text-slate-800">{{ $profile ? 'Lengkap' : 'Belum Lengkap' }}</p>
                    <a href="{{ route('student.profile.edit') }}" class="inline-block mt-3 text-sm font-semibold text-slate-700 hover:text-slate-900">
                        {{ $profile ? 'Edit Profil →' : 'Lengkapi Sekarang →' }}
                    </a>
                </div>

                <!-- Status Pengajuan -->
                <div class="bg-white p-5 rounded-2xl shadow-[0_4px_12px_rgba(100,116,139,0.08)] border-l-4
                    {{ optional($application)->status === 'accepted' ? 'border-emerald-500' : '' }}
                    {{ optional($application)->status === 'pending' ? 'border-amber-500' : '' }}
                    {{ optional($application)->status === 'rejected' ? 'border-red-500' : '' }}
                    {{ !$application ? 'border-slate-300' : '' }}">
                    <p class="text-xs font-medium text-slate-400 uppercase tracking-wide">Status Pengajuan</p>
                    <p class="text-lg font-bold mt-1 text-slate-800">{{ $application ? strtoupper($application->status) : 'Belum Mengajukan' }}</p>
                    @if(!$application)
                        <a href="{{ route('student.application.create') }}" class="inline-block mt-3 text-sm font-semibold text-slate-700 hover:text-slate-900">Buat Pengajuan Baru →</a>
                    @else
                        <span class="inline-block mt-3 text-xs text-slate-500">Unit: {{ $application->unit->name ?? '-' }}</span>
                    @endif
                </div>

                <!-- Pembimbing Lapangan -->
                <div class="bg-white p-5 rounded-2xl shadow-[0_4px_12px_rgba(100,116,139,0.08)] border-l-4 border-blue-500">
                    <p class="text-xs font-medium text-slate-400 uppercase tracking-wide">Pembimbing Lapangan</p>
                    <p class="text-base font-bold mt-1 text-slate-800">
                        {{ optional($placement?->mentor ?? $placement?->pembimbing)->name ?? 'Belum Diplot' }}
                    </p>
                    <p class="mt-3 text-xs text-slate-500">Ditentukan oleh Admin saat magang diterima.</p>
                </div>

                @if($evaluation)
                @php
                    $rataRata = round(($evaluation->nilai_disiplin + $evaluation->nilai_kinerja + $evaluation->nilai_laporan) / 3, 2);
                @endphp
                <div class="bg-white p-5 rounded-2xl shadow-[0_4px_12px_rgba(100,116,139,0.08)] border-l-4 border-purple-500 md:col-span-3">
                    <p class="text-xs font-medium text-slate-400 uppercase tracking-wide">Penilaian Pembimbing</p>
                    <p class="text-2xl font-bold mt-1 text-slate-800">{{ $rataRata }}</p>
                    <p class="text-xs text-slate-500 mt-1">Disiplin: {{ $evaluation->nilai_disiplin }} · Kinerja: {{ $evaluation->nilai_kinerja }} · Laporan: {{ $evaluation->nilai_laporan }}</p>
                </div>
                @endif
            </div>

            <!-- Recent Logbook Activity Table -->
            @if($placement)
            <div class="bg-white rounded-2xl border border-slate-100 shadow-[0_4px_12px_rgba(100,116,139,0.08)] overflow-hidden">
                <div class="p-5 border-b border-slate-100 flex justify-between items-center">
                    <h3 class="text-base font-bold text-slate-800">Aktivitas Logbook Terbaru</h3>
                    <a href="{{ route('student.logbook.index') }}" class="text-sm font-semibold text-slate-700 hover:text-slate-900 hover:underline">Lihat Semua</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50">
                                <th class="py-3 px-5 text-xs font-semibold text-slate-400 uppercase">Tanggal</th>
                                <th class="py-3 px-5 text-xs font-semibold text-slate-400 uppercase">Ringkasan Aktivitas</th>
                                <th class="py-3 px-5 text-xs font-semibold text-slate-400 uppercase">Status</th>
                                <th class="py-3 px-5 text-xs font-semibold text-slate-400 uppercase text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm text-slate-700">
                            @forelse($logbooks->take(5) as $log)
                            <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors">
                                <td class="py-3 px-5 whitespace-nowrap text-slate-500">{{ \Carbon\Carbon::parse($log->date)->translatedFormat('d M Y') }}</td>
                                <td class="py-3 px-5 font-medium max-w-xs truncate">{{ \Illuminate\Support\Str::limit($log->activity, 60) }}</td>
                                <td class="py-3 px-5">
                                    @php
                                        $badge = match($log->status) {
                                            'approved' => 'bg-emerald-100 text-emerald-700',
                                            'rejected' => 'bg-red-100 text-red-700',
                                            default => 'bg-amber-100 text-amber-700',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $badge }}">{{ ucfirst($log->status) }}</span>
                                </td>
                                <td class="py-3 px-5 text-right">
                                    @if($log->status !== 'approved')
                                        <a href="{{ route('student.logbook.edit', $log->id) }}" class="text-slate-400 hover:text-slate-700">Edit</a>
                                    @else
                                        <span class="text-slate-300">—</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="py-8 px-5 text-center text-slate-400 text-sm">Belum ada logbook yang diisi.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            <!-- Detail Pengajuan -->
            @if ($application)
                <div class="bg-white p-6 rounded-2xl shadow-[0_4px_12px_rgba(100,116,139,0.08)] space-y-4">
                    <h4 class="font-bold text-slate-800 text-base border-b border-slate-100 pb-3">Detail Pengajuan Magang Aktif</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <p><span class="text-slate-400">Unit Instansi:</span> <strong class="text-slate-700">{{ $application->unit->name ?? '-' }}</strong></p>
                        <p><span class="text-slate-400">Tanggal Magang:</span> <strong class="text-slate-700">{{ $application->start_date }} s/d {{ $application->end_date }}</strong></p>
                        <p><span class="text-slate-400">Status Saat Ini:</span>
                            <span class="px-2 py-1 text-xs font-bold rounded
                                {{ $application->status === 'accepted' ? 'bg-emerald-100 text-emerald-700' : '' }}
                                {{ $application->status === 'pending' ? 'bg-amber-100 text-amber-700' : '' }}
                                {{ $application->status === 'rejected' ? 'bg-red-100 text-red-700' : '' }}">
                                {{ strtoupper($application->status) }}
                            </span>
                        </p>
                        @if ($application->status === 'rejected')
                            <p class="col-span-2 text-red-600"><strong>Catatan Penolakan:</strong> {{ $application->rejection_note }}</p>
                        @endif
                    </div>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
