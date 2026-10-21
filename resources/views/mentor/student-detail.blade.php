<x-app-layout>
    @php
        $student = $placement->application?->user;
        $profile = $student?->studentProfile;
        $unit = $placement->application?->unit;
        $agency = $unit?->agencyProfile ?? $placement->agencyProfile;
        $mentor = $placement->mentor ?? $placement->pembimbing;
        $academicAdvisor = $placement->academicAdvisor;
        $eval = $placement->evaluation;
        $hasEval = $eval && ($eval->nilai_disiplin > 0 || $eval->nilai_kinerja > 0 || $eval->nilai_laporan > 0);

        $app = $placement->application;
        $appStatus = $app?->status instanceof \BackedEnum ? $app->status->value : (string) ($app?->status ?? 'pending');
        $startDate = $app?->start_date ? \Carbon\Carbon::parse($app->start_date) : null;
        $endDate = $app?->end_date ? \Carbon\Carbon::parse($app->end_date) : null;

        $pendingLogbooksCount = $placement->logbooks->where('status', 'pending')->count();

        // Status Kedinasan Resmi & Tugas Mentor Berdasarkan Status (0% emoji, gaya administrasi formal)
        $isUpcoming = false;
        $isActive = false;
        $isCompleted = false;
        $statusLabel = 'Draf Pengajuan';
        $statusDesc = '';
        $statusBadgeClass = 'bg-slate-100 text-slate-700 border border-slate-300';
        $statusDotClass = 'bg-slate-400';

        $mentorTaskTitle = 'Belum Ada Aksi yang Diperlukan';
        $mentorTaskDesc = 'Mahasiswa belum memulai masa magang. Mentor belum perlu melakukan tindakan apapun.';
        $mentorTaskBadge = 'Tidak Ada Aksi';
        $mentorTaskBg = 'bg-slate-50';
        $mentorTaskBorder = 'border-slate-200';
        $mentorTaskBadgeClass = 'bg-slate-100 text-slate-700 border-slate-300';

        if ($appStatus === 'pending') {
            $statusLabel = 'Menunggu Verifikasi Berkas';
            $statusDesc = 'Pengajuan berkas sedang dalam verifikasi administrasi dinas';
            $statusBadgeClass = 'bg-amber-50 text-amber-700 border border-amber-200';
            $statusDotClass = 'bg-amber-500';
            $mentorTaskTitle = 'Belum Perlu Tindakan Apapun';
            $mentorTaskDesc = 'Pengajuan magang mahasiswa masih dalam tahap verifikasi berkas administrasi oleh staf dinas. Mentor belum perlu melakukan aksi bimbingan apapun saat ini.';
            $mentorTaskBadge = 'Proses Pendaftaran';
            $mentorTaskBg = 'bg-amber-50/50';
            $mentorTaskBorder = 'border-amber-200';
            $mentorTaskBadgeClass = 'bg-amber-100 text-amber-800 border-amber-200';
        } elseif ($appStatus === 'waiting_approval') {
            $statusLabel = 'Menunggu Persetujuan Atasan';
            $statusDesc = 'Menunggu persetujuan akhir dari Atasan Kepegawaian Dinas';
            $statusBadgeClass = 'bg-purple-50 text-purple-700 border border-purple-200';
            $statusDotClass = 'bg-purple-600';
            $mentorTaskTitle = 'Belum Perlu Tindakan Apapun';
            $mentorTaskDesc = 'Penugasan dan pengajuan magang sedang menunggu persetujuan akhir (ACC) dari Atasan Kepegawaian Dinas. Mentor belum perlu melakukan aksi bimbingan sampai proses persetujuan resmi selesai.';
            $mentorTaskBadge = 'Menunggu ACC Atasan';
            $mentorTaskBg = 'bg-purple-50/40';
            $mentorTaskBorder = 'border-purple-200';
            $mentorTaskBadgeClass = 'bg-purple-100 text-purple-800 border-purple-200';
        } elseif ($appStatus === 'accepted') {
            if ($startDate && $startDate->startOfDay()->isFuture()) {
                $isUpcoming = true;
                $daysUntil = (int) ceil(now()->floatDiffInDays($startDate->startOfDay(), false));
                $statusLabel = 'Menunggu Jadwal Mulai';
                $statusDesc = 'Masa magang dimulai dalam ' . $daysUntil . ' hari (' . $startDate->translatedFormat('d M Y') . ')';
                $statusBadgeClass = 'bg-indigo-50 text-indigo-700 border border-indigo-200';
                $statusDotClass = 'bg-indigo-500';
                $mentorTaskTitle = 'Belum Perlu Tindakan Apapun';
                $mentorTaskDesc = 'Mahasiswa telah resmi diterima. Belum ada tindakan bimbingan yang diperlukan hingga mahasiswa hadir pada hari pertama magang tanggal ' . $startDate->translatedFormat('d F Y') . '. Pengisian dan verifikasi logbook akan aktif otomatis saat masa magang dimulai.';
                $mentorTaskBadge = 'Menunggu Mulai';
                $mentorTaskBg = 'bg-indigo-50/40';
                $mentorTaskBorder = 'border-indigo-200';
                $mentorTaskBadgeClass = 'bg-indigo-100 text-indigo-800 border-indigo-200';
            } else {
                $isActive = true;
                $statusLabel = 'Aktif Magang';
                $statusDesc = 'Masa magang sedang berjalan';
                $statusBadgeClass = 'bg-emerald-50 text-emerald-700 border border-emerald-200';
                $statusDotClass = 'bg-emerald-500';
                if ($pendingLogbooksCount > 0) {
                    $mentorTaskTitle = 'Verifikasi Logbook Harian Mahasiswa';
                    $mentorTaskDesc = 'Terdapat ' . $pendingLogbooksCount . ' entri logbook kegiatan mahasiswa yang menunggu pemeriksaan dan persetujuan Anda di bagian bawah halaman ini.';
                    $mentorTaskBadge = 'Perlu Review (' . $pendingLogbooksCount . ')';
                    $mentorTaskBg = 'bg-amber-50/60';
                    $mentorTaskBorder = 'border-amber-300';
                    $mentorTaskBadgeClass = 'bg-amber-100 text-amber-800 border-amber-300';
                } else {
                    $mentorTaskTitle = 'Supervisi Aktivitas & Bimbingan Lapangan';
                    $mentorTaskDesc = 'Pantau kehadiran mahasiswa di unit kerja dinas dan berikan arahan teknis kedinasan. Formulir penilaian evaluasi akhir akan diisi menjelang masa magang berakhir.';
                    $mentorTaskBadge = 'Supervisi Aktif';
                    $mentorTaskBg = 'bg-emerald-50/40';
                    $mentorTaskBorder = 'border-emerald-200';
                    $mentorTaskBadgeClass = 'bg-emerald-100 text-emerald-800 border-emerald-200';
                }
            }
        } elseif ($appStatus === 'active') {
            $isActive = true;
            $statusLabel = 'Aktif Magang';
            $statusDesc = 'Masa magang sedang berjalan';
            $statusBadgeClass = 'bg-emerald-50 text-emerald-700 border border-emerald-200';
            $statusDotClass = 'bg-emerald-500';
            if ($pendingLogbooksCount > 0) {
                $mentorTaskTitle = 'Verifikasi Logbook Harian Mahasiswa';
                $mentorTaskDesc = 'Terdapat ' . $pendingLogbooksCount . ' entri logbook kegiatan mahasiswa yang menunggu pemeriksaan dan persetujuan Anda di bagian bawah halaman ini.';
                $mentorTaskBadge = 'Perlu Review (' . $pendingLogbooksCount . ')';
                $mentorTaskBg = 'bg-amber-50/60';
                $mentorTaskBorder = 'border-amber-300';
                $mentorTaskBadgeClass = 'bg-amber-100 text-amber-800 border-amber-300';
            } else {
                $mentorTaskTitle = 'Supervisi Aktivitas & Bimbingan Lapangan';
                $mentorTaskDesc = 'Pantau kehadiran mahasiswa di unit kerja dinas dan berikan arahan teknis kedinasan. Formulir penilaian evaluasi akhir akan diisi menjelang masa magang berakhir.';
                $mentorTaskBadge = 'Supervisi Aktif';
                $mentorTaskBg = 'bg-emerald-50/40';
                $mentorTaskBorder = 'border-emerald-200';
                $mentorTaskBadgeClass = 'bg-emerald-100 text-emerald-800 border-emerald-200';
            }
        } elseif ($appStatus === 'completed') {
            $isCompleted = true;
            $statusLabel = 'Selesai Magang';
            $statusDesc = 'Masa magang telah selesai';
            $statusBadgeClass = 'bg-blue-50 text-blue-700 border border-blue-200';
            $statusDotClass = 'bg-blue-500';
            $mentorTaskTitle = 'Seluruh Bimbingan Telah Selesai';
            $mentorTaskDesc = 'Mahasiswa telah menyelesaikan seluruh masa magang, nilai evaluasi akhir telah terkunci, dan sertifikat kelulusan telah diterbitkan. Tidak ada tindakan lanjutan yang diperlukan.';
            $mentorTaskBadge = 'Selesai';
            $mentorTaskBg = 'bg-blue-50/40';
            $mentorTaskBorder = 'border-blue-200';
            $mentorTaskBadgeClass = 'bg-blue-100 text-blue-800 border-blue-200';
        } elseif ($appStatus === 'resigned') {
            $statusLabel = 'Mengundurkan Diri';
            $statusBadgeClass = 'bg-slate-100 text-slate-700 border border-slate-300';
            $statusDotClass = 'bg-slate-400';
            $mentorTaskTitle = 'Tidak Ada Tindakan (Mengundurkan Diri)';
            $mentorTaskDesc = 'Mahasiswa telah membatalkan / mengundurkan diri dari kegiatan magang. Seluruh proses bimbingan dinonaktifkan.';
            $mentorTaskBadge = 'Batal';
            $mentorTaskBg = 'bg-slate-50';
            $mentorTaskBorder = 'border-slate-200';
            $mentorTaskBadgeClass = 'bg-slate-100 text-slate-700 border-slate-300';
        } elseif ($appStatus === 'rejected') {
            $statusLabel = 'Pengajuan Ditolak';
            $statusBadgeClass = 'bg-rose-50 text-rose-700 border border-rose-200';
            $statusDotClass = 'bg-rose-500';
            $mentorTaskTitle = 'Tidak Ada Tindakan (Pengajuan Ditolak)';
            $mentorTaskDesc = 'Pengajuan magang mahasiswa ditolak oleh instansi dinas. Tidak ada tindakan yang perlu dilakukan oleh mentor.';
            $mentorTaskBadge = 'Ditolak';
            $mentorTaskBg = 'bg-rose-50/40';
            $mentorTaskBorder = 'border-rose-200';
            $mentorTaskBadgeClass = 'bg-rose-100 text-rose-800 border-rose-200';
        } else {
            $statusLabel = strtoupper(str_replace('_', ' ', $appStatus));
        }

        // Nomor Telepon & Tautan WhatsApp
        $rawPhone = $profile?->phone ?? $student?->phone;
        $waLink = null;
        if ($rawPhone) {
            $cleanPhone = preg_replace('/[^0-9]/', '', (string)$rawPhone);
            if (str_starts_with($cleanPhone, '0')) {
                $cleanPhone = '62' . substr($cleanPhone, 1);
            }
            if (strlen($cleanPhone) >= 9) {
                $agencyName = $agency->agency_name ?? 'Dinas Perpustakaan dan Kearsipan';
                $msg = rawurlencode("Halo {$student?->name}, perihal bimbingan magang di {$agencyName}.");
                $waLink = "https://wa.me/{$cleanPhone}?text={$msg}";
            }
        }
    @endphp

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('mentor.dashboard') }}" 
                   class="p-2.5 rounded-xl bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition shadow-2xs" 
                   title="Kembali ke Daftar Mahasiswa Bimbingan">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
                <div>
                    <h2 class="font-bold text-xl sm:text-2xl text-slate-900 leading-tight">
                        {{ $placement->application?->user?->name ?? '-' }}
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1">
                        NIM: {{ $placement->application?->user?->studentProfile?->nim ?? '-' }} &bull; {{ $placement->application?->user?->studentProfile?->universitas ?? '-' }} ({{ $placement->application?->user?->studentProfile?->jurusan ?? '-' }})
                    </p>
                </div>
            </div>

            <!-- Tombol Komunikasi & Koordinasi Lapangan -->
            <div class="flex flex-wrap items-center gap-2">
                @if ($placement->application?->user)
                    <x-chat-button :user="$placement->application->user" label="Chat Mahasiswa" />
                @endif

                @if ($waLink)
                    <a href="{{ $waLink }}" target="_blank" rel="noopener noreferrer"
                       class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 text-xs font-bold rounded-xl transition shadow-xs whitespace-nowrap active:scale-95 cursor-pointer bg-white border border-slate-200 hover:border-[#25D366] hover:bg-emerald-50/30 text-slate-700 hover:text-slate-900"
                       title="Hubungi Mahasiswa via WhatsApp">
                        <svg class="w-4 h-4 shrink-0 fill-[#25D366]" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                        <span>WhatsApp</span>
                    </a>
                @endif

                <x-chat-group-button :placement="$placement" />
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Message -->
            @if (session('success'))
                <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-xl flex items-center justify-between text-emerald-900 text-sm font-medium shadow-2xs">
                    <span>{{ session('success') }}</span>
                </div>
            @endif
            @if (session('error'))
                <div class="p-4 bg-rose-50 border-l-4 border-rose-500 rounded-r-xl text-rose-900 text-sm font-medium">
                    {{ session('error') }}
                </div>
            @endif

            @unless ($canEvaluate ?? true)
                <div class="p-4 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-700">
                    Anda membuka halaman ini sebagai <strong>Kepala Unit</strong> (validasi sementara karena mentor belum ditunjuk).
                    Anda dapat memeriksa dan menyetujui logbook. Penilaian dan persetujuan laporan akhir dilakukan oleh mentor yang nanti ditugaskan.
                </div>
            @endunless

            <!-- 1. Card: Data Magang & Penugasan Dinas -->
            <div class="bg-white rounded-2xl p-6 sm:p-7 border border-slate-200 shadow-xs space-y-5">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-slate-100">
                    <div>
                        <h3 class="font-bold text-base text-slate-900">
                            Data Magang & Penugasan
                        </h3>
                    </div>
                    <div>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold {{ $statusBadgeClass }}">
                            <span class="w-2 h-2 rounded-full {{ $statusDotClass }}"></span>
                            {{ $statusLabel }}
                        </span>
                    </div>
                </div>

                <!-- 6 Data Pokok Magang (Grid 3 Kolom Rapi) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 text-sm">
                    <div>
                        <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider block">Perguruan Tinggi</span>
                        <span class="font-medium text-slate-800 text-sm block mt-0.5">{{ $profile->universitas ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider block">Program Studi / Jurusan</span>
                        <span class="font-medium text-slate-800 text-sm block mt-0.5">{{ $profile->jurusan ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider block">Instansi & Unit Kerja</span>
                        <span class="font-medium text-slate-800 text-sm block mt-0.5">{{ $agency->agency_name ?? '-' }} &mdash; {{ $unit->name ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider block">Mentor Lapangan (Dinas)</span>
                        <span class="font-medium text-slate-800 text-sm block mt-0.5">{{ $mentor->name ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider block">Dosen Pembimbing (Kampus)</span>
                        <span class="font-medium text-slate-800 text-sm block mt-0.5">{{ $academicAdvisor->name ?? 'Belum Ditugaskan' }}</span>
                    </div>
                    <div>
                        <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider block">Periode Pelaksanaan</span>
                        <span class="font-medium text-slate-800 text-sm block mt-0.5">
                            {{ $startDate ? $startDate->translatedFormat('d M Y') : '-' }} s/d {{ $endDate ? $endDate->translatedFormat('d M Y') : '-' }}
                        </span>
                        @if ($statusDesc)
                            <span class="text-xs text-slate-500 font-normal block mt-0.5">{{ $statusDesc }}</span>
                        @endif
                    </div>
                    @if($placement->application?->group_id && $placement->application?->group)
                        <div class="col-span-1 sm:col-span-2 lg:col-span-3">
                            <span class="text-[11px] text-indigo-500 font-semibold uppercase tracking-wider block">Kelompok Magang</span>
                            <span class="inline-flex items-center gap-1.5 font-bold text-indigo-700 text-xs mt-0.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                                {{ $placement->application->group->name }} ({{ $placement->application->role_in_group === 'leader' ? 'Ketua' : 'Anggota' }})
                            </span>
                        </div>
                    @endif
                </div>

                <!-- Tugas Mentor Saat Ini (Full-Width di Bawah Tanpa Terpotong) -->
                <div class="pt-3 border-t border-slate-100 space-y-1.5">
                    <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider block">Tugas Mentor Saat Ini</span>
                    <div class="p-4 rounded-xl border {{ $mentorTaskBorder }} {{ $mentorTaskBg }} space-y-1">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <span class="font-bold text-sm text-slate-900">{{ $mentorTaskTitle }}</span>
                            <span class="px-2.5 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider border {{ $mentorTaskBadgeClass }}">
                                {{ $mentorTaskBadge }}
                            </span>
                        </div>
                        <p class="text-xs sm:text-sm text-slate-600 leading-relaxed font-normal">
                            {{ $mentorTaskDesc }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- 2. Card: Logbook Aktivitas Harian Mahasiswa -->
            <div class="bg-white rounded-2xl p-6 sm:p-7 border border-slate-200 shadow-xs space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-slate-100">
                    <div>
                        <h3 class="font-bold text-base text-slate-900">
                            Logbook Aktivitas Harian
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Daftar riwayat aktivitas harian per paket mingguan mahasiswa untuk diverifikasi
                        </p>
                    </div>
                    @if ($placement->logbooks->count() > 0)
                        <span class="px-3 py-1 bg-slate-100 text-slate-700 text-xs font-bold rounded-full">
                            {{ $placement->logbooks->count() }} Entri Kegiatan
                        </span>
                    @endif
                </div>

                @if ($isUpcoming)
                    <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl flex items-start gap-3 text-sm">
                        <svg class="w-5 h-5 text-slate-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <div>
                            <p class="font-bold text-slate-800">Masa Magang Belum Dimulai</p>
                            <p class="text-xs text-slate-600 mt-0.5 leading-relaxed">
                                Mahasiswa dijadwalkan mulai melaksanakan kegiatan magang pada tanggal <strong>{{ $startDate ? $startDate->translatedFormat('d F Y') : '-' }}</strong>. Pengisian serta verifikasi logbook harian oleh mahasiswa akan aktif secara otomatis saat masa magang dimulai.
                            </p>
                        </div>
                    </div>
                @endif

                <x-supervision.logbook-weekly
                    :bundles="$weeklyBundles"
                    :reviewRoute="route('mentor.logbooks.bulk_review')"
                    role="mentor"
                    emptyMessage="Belum ada entri logbook kegiatan yang diinputkan mahasiswa."
                />
            </div>

            <!-- 3. Card: Laporan Akhir & Proyek Inovasi (Komponen Bersama) -->
            <x-supervision.report-review
                :finalReport="$placement->finalreport"
                :placement="$placement"
                :submitRoute="route('mentor.final_report.updateStatus', optional($placement->finalreport)->id ?? 0)"
                method="PUT"
                role="mentor"
                :canReview="$canEvaluate ?? true"
            />

            <!-- 4. Card: Penilaian Evaluasi Akhir Magang -->
            <div class="bg-white rounded-2xl p-6 sm:p-7 border border-slate-200 shadow-xs space-y-5">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100">
                    <div>
                        <h3 class="font-bold text-base text-slate-900">
                            Penilaian Evaluasi Akhir
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Ringkasan nilai evaluasi mutu kerja dan bimbingan mahasiswa
                        </p>
                    </div>

                    <div class="text-left sm:text-right">
                        @if ($canEvaluate ?? true)
                            @if (! empty($evaluationLockReason))
                                @if ($isCompleted || $hasEval)
                                    <div class="inline-flex items-center gap-2">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 text-slate-700 text-xs font-bold rounded-xl border border-slate-200">
                                            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                            Formulir Terkunci
                                        </span>
                                        <a href="{{ route('mentor.evaluations.create', $placement->id) }}"
                                           class="inline-flex items-center px-3.5 py-1.5 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold rounded-xl border border-slate-300 shadow-2xs transition">
                                            Rincian Formulir
                                        </a>
                                    </div>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-slate-100 text-slate-600 text-xs font-bold rounded-xl border border-slate-200">
                                        <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                        Formulir Belum Dibuka
                                    </span>
                                @endif
                            @else
                                <a href="{{ route('mentor.evaluations.create', $placement->id) }}"
                                   class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl shadow-xs transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    {{ $hasEval ? 'Perbarui Nilai Magang' : 'Isi Penilaian Magang' }}
                                </a>
                            @endif
                        @endif
                    </div>
                </div>

                <!-- Pemberitahuan Status Formulir Evaluasi (Style Kedinasan) -->
                @if (! empty($evaluationLockReason))
                    @if ($isCompleted)
                        <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl flex items-start gap-3 text-sm">
                            <svg class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <div class="space-y-0.5">
                                <p class="font-bold text-emerald-900">Penilaian Selesai & Terkunci</p>
                                <p class="text-xs text-emerald-800 leading-relaxed">
                                    Mahasiswa telah menyelesaikan program magang dan sertifikat telah diterbitkan. Data evaluasi telah terkunci secara permanen. Hubungi Admin Kepegawaian Dinas bila diperlukan penyesuaian khusus.
                                </p>
                            </div>
                        </div>
                    @else
                        <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl flex items-start gap-3 text-sm">
                            <svg class="w-5 h-5 text-slate-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <div class="space-y-0.5">
                                <p class="font-bold text-slate-800">Tahap Penilaian Evaluasi Belum Dibuka</p>
                                <p class="text-xs text-slate-600 leading-relaxed">
                                    Formulir penilaian evaluasi akhir magang baru dapat diisi setelah mahasiswa resmi melaksanakan kegiatan magang (mulai tanggal <strong>{{ $startDate ? $startDate->translatedFormat('d F Y') : '-' }}</strong>). Saat ini sistem mengunci form evaluasi untuk menjaga ketertiban data.
                                </p>
                            </div>
                        </div>
                    @endif
                @endif

                <!-- Grid Komparasi Nilai -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Nilai Mentor Sendiri -->
                    <div class="p-4 sm:p-5 bg-slate-50/80 rounded-2xl border border-slate-200 space-y-3 shadow-2xs">
                        <span class="text-xs font-bold text-slate-600 uppercase tracking-wider block">Nilai Mentor Lapangan (Dinas)</span>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 text-center">
                            @forelse ($eval?->mentor_aspects_list ?? [] as $mAspect)
                                <div class="p-2.5 bg-white rounded-xl border border-slate-200 shadow-2xs">
                                    <span class="text-xs text-slate-500 font-semibold block truncate" title="{{ $mAspect['name'] }}">{{ $mAspect['name'] }}</span>
                                    <span class="text-base font-bold text-slate-900">{{ $mAspect['score'] ?? '-' }}</span>
                                </div>
                            @empty
                                <div class="col-span-2 sm:col-span-3 p-3 bg-white rounded-xl text-slate-400 text-xs">
                                    Belum ada nilai mentor.
                                </div>
                            @endforelse
                        </div>
                        <div class="text-xs text-slate-600 pt-1">
                            Rata-rata Mentor: <strong class="text-slate-900">{{ $eval?->nilai_pembimbing > 0 ? $eval->nilai_pembimbing : '-' }}</strong>
                        </div>
                    </div>

                    <!-- Nilai Dosen Pembimbing (Read-Only) -->
                    <div class="p-4 sm:p-5 bg-slate-50/80 rounded-2xl border border-slate-200 space-y-3 shadow-2xs">
                        <span class="text-xs font-bold text-slate-600 uppercase tracking-wider block">Nilai Dosen Pembimbing (Kampus)</span>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 text-center">
                            @forelse ($eval?->dosen_aspects_list ?? [] as $dAspect)
                                <div class="p-2.5 bg-white rounded-xl border border-slate-200 shadow-2xs">
                                    <span class="text-xs text-slate-500 font-semibold block truncate" title="{{ $dAspect['name'] }}">{{ $dAspect['name'] }}</span>
                                    <span class="text-base font-bold text-slate-900">{{ $dAspect['score'] ?? '-' }}</span>
                                </div>
                            @empty
                                <div class="col-span-2 sm:col-span-3 p-3 bg-white rounded-xl text-slate-400 text-xs">
                                    Belum ada nilai bimbingan akademik dosen.
                                </div>
                            @endforelse
                        </div>
                        <div class="text-xs text-slate-600 pt-1">
                            Rata-rata Dosen: <strong class="text-slate-900">{{ $eval?->nilai_dosen_calculated > 0 ? $eval->nilai_dosen_calculated : '-' }}</strong>
                        </div>
                    </div>
                </div>

                <!-- Nilai Akhir & Predikat -->
                <div class="p-4 sm:p-5 bg-white rounded-2xl border border-slate-200 flex flex-col sm:flex-row justify-between items-center gap-4 shadow-2xs">
                    <div>
                        <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider block">Nilai Akhir Magang</span>
                        <div class="text-2xl font-black text-slate-900 mt-0.5">
                            {{ $eval?->final_score > 0 ? $eval->final_score : ($eval?->nilai_akhir > 0 ? $eval->nilai_akhir : '-') }}
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider block">Predikat Mutu</span>
                        <div class="text-2xl font-black text-blue-700 mt-0.5">
                            {{ $eval?->grade_calculated ?? '-' }}
                        </div>
                    </div>
                </div>

                @if ($eval?->catatan)
                    <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200 text-xs text-slate-700">
                        <strong>Catatan Evaluasi Mentor:</strong> "{{ $eval->catatan }}"
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
