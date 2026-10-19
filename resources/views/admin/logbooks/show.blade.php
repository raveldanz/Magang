<x-app-layout>
    @php
        $userRole = Auth::user()?->role;
        $backUrl = route('admin.logbooks.index');
        if (in_array($userRole, ['mentor', 'pembimbing'])) {
            $backUrl = route('mentor.logbooks.index');
        } elseif (in_array($userRole, ['dosen', 'academic_advisor'])) {
            $backUrl = route('lecturer.logbooks.index');
        } elseif ($userRole === 'universitas') {
            $backUrl = route('university.dashboard');
        }

        $placement = $logbook->placement;
        $student = $placement?->application?->user;
        $profile = $student?->studentProfile;
        $unit = $placement?->application?->unit;
        $agency = $unit?->agencyProfile ?? $placement?->agencyProfile;
        $mentor = $placement?->mentor ?? $placement?->pembimbing;
        $dosen = $placement?->academicAdvisor ?? $placement?->dosen;
        $isMentor = in_array($userRole, ['mentor', 'pembimbing']);
        $isLecturer = in_array($userRole, ['dosen', 'academic_advisor']);
        // Fallback: mentor teknis belum ditunjuk → Admin Dinas instansi terkait boleh memvalidasi sementara
        $isAdminFallback = ! $isMentor && ! $isLecturer && (bool) $placement?->allowsAgencyAdminLogbookFallback(Auth::user());
        $isUnitHeadFallback = $isMentor && $placement && ! $placement->isAssignedFieldMentor(Auth::user());

        $attExt = $logbook->attachment ? strtolower(pathinfo($logbook->attachment, PATHINFO_EXTENSION)) : null;
        $attIsImage = in_array($attExt, ['jpg', 'jpeg', 'png', 'webp'], true);
        $dateLabel = \Carbon\Carbon::parse($logbook->date)->translatedFormat('l, d F Y');
    @endphp

    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ $backUrl }}" class="p-2 bg-white hover:bg-slate-50 border border-slate-200 text-slate-600 rounded-xl transition" title="Kembali">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <h2 class="font-bold text-xl sm:text-2xl text-slate-900 leading-tight">Detail Logbook</h2>
                <p class="text-sm text-slate-500 mt-0.5">{{ $student->name ?? '-' }} &bull; {{ $dateLabel }}</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if (session('success'))
                <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-xl text-emerald-900 text-sm font-medium">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="p-4 bg-rose-50 border-l-4 border-rose-500 rounded-r-xl text-rose-900 text-sm font-medium">
                    {{ session('error') }}
                </div>
            @endif

            <!-- 1. Data Magang -->
            <div class="bg-white rounded-2xl p-6 border border-slate-200 space-y-4">
                <h3 class="font-bold text-base text-slate-900 pb-3 border-b border-slate-100">Data Magang</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider block">Mahasiswa</span>
                        <span class="font-bold text-slate-900 block mt-0.5">{{ $student->name ?? '-' }}</span>
                        <span class="text-xs text-slate-500">NIM: {{ $profile->nim ?? '-' }} &bull; {{ $profile->universitas ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider block">Instansi & Unit Kerja</span>
                        <span class="font-medium text-slate-800 block mt-0.5">{{ $agency->agency_name ?? '-' }}</span>
                        <span class="text-xs text-slate-500">{{ $unit->name ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider block">Mentor Lapangan</span>
                        <span class="font-medium text-slate-800 block mt-0.5">{{ $mentor->name ?? 'Belum Ditugaskan' }}</span>
                    </div>
                    <div>
                        <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider block">Dosen Pembimbing</span>
                        <span class="font-medium text-slate-800 block mt-0.5">{{ $dosen->name ?? 'Belum Ditugaskan' }}</span>
                    </div>
                </div>
            </div>

            <!-- 2. Kegiatan & Bukti -->
            <div class="bg-white rounded-2xl p-6 border border-slate-200 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-slate-100">
                    <h3 class="font-bold text-base text-slate-900">Kegiatan</h3>
                    <span class="text-sm text-slate-600">{{ $dateLabel }}</span>
                </div>

                <p class="text-sm text-slate-800 leading-relaxed whitespace-pre-line">{{ trim($logbook->activity) }}</p>

                <div class="pt-1">
                    <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider block mb-2">Bukti Kegiatan</span>
                    @if ($logbook->attachment)
                        @if ($attIsImage)
                            <a href="{{ $logbook->attachment_url }}" target="_blank" title="Klik untuk memperbesar" class="inline-block">
                                <img src="{{ $logbook->attachment_url }}" alt="Bukti kegiatan {{ $dateLabel }}" loading="lazy"
                                     class="rounded-lg border border-slate-200 object-cover" style="max-width: 100%; width: 360px; max-height: 260px;">
                            </a>
                            <span class="block text-xs text-slate-400 mt-1">Klik foto untuk memperbesar</span>
                        @else
                            <a href="{{ $logbook->attachment_url }}" target="_blank"
                               class="inline-flex items-center px-4 py-2 bg-white hover:bg-slate-50 text-slate-700 text-sm font-bold rounded-xl border border-slate-300 transition">
                                Buka Berkas Bukti ({{ strtoupper($attExt ?: 'file') }})
                            </a>
                        @endif
                    @else
                        <p class="text-sm text-slate-400">Mahasiswa tidak melampirkan bukti untuk kegiatan ini.</p>
                    @endif
                </div>
            </div>

            <!-- 3. Status Verifikasi Mentor & Dosen -->
            <div class="bg-white rounded-2xl p-6 border border-slate-200 space-y-4">
                <h3 class="font-bold text-base text-slate-900 pb-3 border-b border-slate-100">Status Verifikasi</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="p-4 rounded-xl border border-slate-200 bg-slate-50 space-y-2">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-sm font-bold text-slate-800">Mentor Lapangan</span>
                            <x-status-badge type="review" :status="$logbook->status ?? 'pending'" />
                        </div>
                        <p class="text-sm {{ $logbook->feedback ? 'text-slate-700' : 'text-slate-400' }}">
                            {{ $logbook->feedback ?: 'Belum ada catatan dari mentor.' }}
                        </p>
                    </div>
                    <div class="p-4 rounded-xl border border-slate-200 bg-slate-50 space-y-2">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-sm font-bold text-slate-800">Dosen Pembimbing</span>
                            <x-status-badge type="review" :status="$logbook->lecturer_status ?? 'pending'" />
                        </div>
                        <p class="text-sm {{ $logbook->lecturer_feedback ? 'text-slate-700' : 'text-slate-400' }}">
                            {{ $logbook->lecturer_feedback ?: 'Belum ada catatan dari dosen.' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- 4. Form Verifikasi sesuai peran (urutan & tombol sama dengan halaman logbook mingguan) -->
            @php
                $reviewForm = null;
                if ($isMentor) {
                    $reviewForm = ['route' => route('mentor.logbooks.updateStatus', $logbook->id), 'feedback' => $logbook->feedback, 'title' => 'Verifikasi Mentor Lapangan'];
                } elseif ($isLecturer) {
                    $reviewForm = ['route' => route('lecturer.logbooks.updateStatus', $logbook->id), 'feedback' => $logbook->lecturer_feedback, 'title' => 'Verifikasi Dosen Pembimbing'];
                } elseif ($isAdminFallback) {
                    $reviewForm = ['route' => route('admin.logbooks.review', $logbook->id), 'feedback' => $logbook->feedback, 'title' => 'Validasi Sementara oleh Admin Dinas'];
                }
            @endphp

            @if ($reviewForm)
                <div class="bg-white rounded-2xl p-6 border border-slate-200 space-y-4">
                    <div class="pb-3 border-b border-slate-100">
                        <h3 class="font-bold text-base text-slate-900">{{ $reviewForm['title'] }}</h3>
                        @if ($isAdminFallback)
                            <p class="text-sm text-slate-500 mt-1">Mahasiswa ini belum memiliki Mentor Dinas. Anda dapat memvalidasi logbook sementara sampai mentor ditugaskan. Riwayat validasi tetap tersimpan.</p>
                        @elseif ($isUnitHeadFallback)
                            <p class="text-sm text-slate-500 mt-1">Anda memvalidasi sebagai Kepala Unit (sementara, karena mentor belum ditunjuk).</p>
                        @endif
                    </div>

                    <form action="{{ $reviewForm['route'] }}" method="POST" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <div>
                            <label for="feedback" class="block text-sm font-bold text-slate-700 mb-1.5">Catatan untuk mahasiswa</label>
                            <textarea id="feedback" name="feedback" rows="3" maxlength="1000"
                                      class="w-full text-sm border-slate-300 rounded-xl focus:ring-blue-500 focus:border-blue-500"
                                      placeholder="Tuliskan catatan, arahan, atau alasan revisi...">{{ old('feedback', $reviewForm['feedback']) }}</textarea>
                        </div>

                        <div class="flex flex-wrap items-center justify-between gap-3 pt-3 border-t border-slate-100">
                            <button type="submit" name="status" value="rejected"
                                    class="inline-flex items-center justify-center px-4 py-2.5 bg-white hover:bg-rose-50 text-rose-700 text-sm font-bold rounded-xl border border-rose-300 transition cursor-pointer">
                                Minta Revisi
                            </button>
                            <div class="flex flex-wrap items-center gap-3">
                                @if ($isAdminFallback && $placement?->application_id)
                                    <a href="{{ route('admin.applications.show', $placement->application_id) }}#penugasan-pembimbing"
                                       class="inline-flex items-center px-4 py-2.5 bg-white hover:bg-slate-50 text-slate-700 text-sm font-bold rounded-xl border border-slate-300 transition">
                                        Tugaskan Mentor
                                    </a>
                                @endif
                                <button type="submit" name="status" value="approved"
                                        class="inline-flex items-center justify-center px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl transition cursor-pointer">
                                    Setujui
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
