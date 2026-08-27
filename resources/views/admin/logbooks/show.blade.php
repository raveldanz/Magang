<x-app-layout>
    <x-slot name="header">
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
        @endphp
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ $backUrl }}" class="p-2 bg-white hover:bg-gray-100 border border-gray-200 text-gray-700 rounded-xl transition shadow-xs">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <div>
                    <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                        @if (in_array($userRole, ['mentor', 'pembimbing']))
                            {{ __('Review & Verifikasi Logbook - Mentor Lapangan') }}
                        @elseif (in_array($userRole, ['dosen', 'academic_advisor']))
                            {{ __('Review & Verifikasi Logbook - Dosen Pembimbing') }}
                        @else
                            {{ __('Detail & Monitoring Logbook') }}
                        @endif
                    </h2>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Tanggal Kegiatan: <strong>{{ \Carbon\Carbon::parse($logbook->date)->translatedFormat('l, d F Y') }}</strong> &bull; Mahasiswa: {{ $logbook->placement->application->user->name ?? '-' }}
                    </p>
                </div>
            </div>

            <div>
                <a href="{{ $backUrl }}" class="px-3.5 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-xl transition shadow-xs">
                    &larr; Kembali
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Message -->
            @if (session('success'))
                <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-xl shadow-xs flex items-center justify-between text-emerald-900 text-sm font-medium">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @php
                $student = $logbook->placement->application->user ?? null;
                $profile = $student?->studentProfile;
                $unit = $logbook->placement->application->unit ?? null;
                $agency = $unit?->agencyProfile ?? $logbook->placement->agencyProfile;
                $mentor = $logbook->placement->mentor ?? $logbook->placement->pembimbing;
                $dosen = $logbook->placement->academicAdvisor ?? $logbook->placement->dosen;
                $isMentor = in_array(Auth::user()?->role, ['mentor', 'pembimbing']);
                $isLecturer = in_array(Auth::user()?->role, ['dosen', 'academic_advisor']);
            @endphp

            <!-- 1. Card Informasi Mahasiswa & Penempatan -->
            <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-blue-100 text-blue-700 font-bold rounded-xl flex items-center justify-center text-sm">
                            {{ strtoupper(substr($student->name ?? 'M', 0, 2)) }}
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 text-base leading-snug">{{ $student->name ?? '-' }}</h3>
                            <p class="text-xs text-gray-500 font-mono">NIM: {{ $profile->nim ?? '-' }}</p>
                        </div>
                    </div>
                    <span class="text-xs font-semibold px-2.5 py-1 bg-blue-50 text-blue-700 rounded-lg">
                        {{ $profile->universitas ?? '-' }}
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                    <div class="p-3 bg-gray-50 rounded-xl space-y-1">
                        <span class="text-gray-500 font-medium">Instansi & Unit Kerja:</span>
                        <p class="font-bold text-gray-800">{{ $agency->agency_name ?? '-' }}</p>
                        <p class="text-blue-600 font-semibold">{{ $unit->name ?? '-' }}</p>
                    </div>

                    <div class="p-3 bg-gray-50 rounded-xl space-y-1">
                        <span class="text-gray-500 font-medium">Pembimbing:</span>
                        <p class="font-bold text-gray-800">
                            Mentor Lapangan: <span class="font-semibold text-gray-700">{{ $mentor->name ?? 'Belum Diplot' }}</span>
                        </p>
                        <p class="text-gray-600">
                            DPL Kampus: <span class="font-medium text-gray-700">{{ $dosen->name ?? '-' }}</span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- 2. Card Detail Logbook Kegiatan -->
            <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-sm space-y-5">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 border-b border-gray-100 pb-3">
                    <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                        Uraian Aktivitas Logbook
                    </h3>
                    <div class="text-xs font-mono font-bold text-gray-700 bg-gray-100 px-3 py-1 rounded-lg">
                        {{ \Carbon\Carbon::parse($logbook->date)->translatedFormat('l, d F Y') }}
                    </div>
                </div>

                <!-- Deskripsi Kegiatan -->
                <div>
                    <span class="text-xs font-bold text-gray-500 uppercase tracking-wider block mb-1.5">Deskripsi Kegiatan Harian:</span>
                    <div class="p-4 bg-slate-50 rounded-xl text-gray-800 text-sm leading-relaxed border border-gray-100 whitespace-pre-wrap font-sans">
                        {{ $logbook->activity }}
                    </div>
                </div>

                <!-- Berkas Lampiran Bukti Kegiatan -->
                <div>
                    <span class="text-xs font-bold text-gray-500 uppercase tracking-wider block mb-1.5">Lampiran Dokumen / Foto Bukti:</span>
                    @if ($logbook->attachment)
                        <div class="flex items-center justify-between p-3.5 bg-blue-50/75 rounded-xl border border-blue-100">
                            <div class="flex items-center gap-2 text-xs font-semibold text-blue-900">
                                <svg class="w-5 h-5 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                </svg>
                                <span>File Bukti Aktivitas Mahasiswa</span>
                            </div>
                            <a href="{{ asset('storage/' . $logbook->attachment) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold shadow-xs transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg>
                                Buka / Unduh Lampiran
                            </a>
                        </div>
                    @else
                        <div class="p-3 bg-gray-50 rounded-xl text-gray-400 text-xs italic border border-gray-100">
                            Mahasiswa tidak melampirkan berkas bukti untuk aktivitas ini.
                        </div>
                    @endif
                </div>
            </div>

            <!-- 3. Status Verifikasi 2-Arah (Mentor Dinas & Dosen Kampus) -->
            <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-sm space-y-4">
                <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider flex items-center gap-2 border-b border-gray-100 pb-3">
                    <span>Status Verifikasi 2-Arah</span>
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    
                    <!-- Panel Status Mentor Dinas -->
                    <div class="p-4 rounded-xl border {{ $logbook->status === 'approved' ? 'bg-emerald-50/50 border-emerald-200' : ($logbook->status === 'rejected' ? 'bg-rose-50/50 border-rose-200' : 'bg-amber-50/50 border-amber-200') }} space-y-2.5">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-gray-700">Mentor Lapangan Dinas</span>
                            @if ($logbook->status === 'approved')
                                <span class="px-2.5 py-0.5 text-[11px] font-black rounded-full bg-emerald-100 text-emerald-800 border border-emerald-300">
                                    ✅ Disetujui
                                </span>
                            @elseif ($logbook->status === 'rejected')
                                <span class="px-2.5 py-0.5 text-[11px] font-black rounded-full bg-rose-100 text-rose-800 border border-rose-300">
                                    ❌ Ditolak
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 text-[11px] font-black rounded-full bg-amber-100 text-amber-800 border border-amber-300">
                                    ⏳ Menunggu Persetujuan
                                </span>
                            @endif
                        </div>

                        <div>
                            <span class="text-[11px] font-semibold text-gray-500 block mb-0.5">Feedback Mentor:</span>
                            @if ($logbook->feedback)
                                <p class="text-xs text-gray-800 italic bg-white p-2.5 rounded-lg border border-gray-200/80">
                                    "{{ $logbook->feedback }}"
                                </p>
                            @else
                                <p class="text-[11px] text-gray-400 italic">Belum ada catatan feedback dari mentor.</p>
                            @endif
                        </div>
                    </div>

                    <!-- Panel Status Dosen Kampus -->
                    <div class="p-4 rounded-xl border {{ $logbook->lecturer_status === 'approved' ? 'bg-emerald-50/50 border-emerald-200' : ($logbook->lecturer_status === 'rejected' ? 'bg-rose-50/50 border-rose-200' : 'bg-amber-50/50 border-amber-200') }} space-y-2.5">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-gray-700">Dosen Pembimbing (DPL)</span>
                            @if ($logbook->lecturer_status === 'approved')
                                <span class="px-2.5 py-0.5 text-[11px] font-black rounded-full bg-emerald-100 text-emerald-800 border border-emerald-300">
                                    ✅ Disetujui
                                </span>
                            @elseif ($logbook->lecturer_status === 'rejected')
                                <span class="px-2.5 py-0.5 text-[11px] font-black rounded-full bg-rose-100 text-rose-800 border border-rose-300">
                                    ❌ Perlu Revisi
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 text-[11px] font-black rounded-full bg-amber-100 text-amber-800 border border-amber-300">
                                    ⏳ Menunggu Persetujuan
                                </span>
                            @endif
                        </div>

                        <div>
                            <span class="text-[11px] font-semibold text-gray-500 block mb-0.5">Feedback Dosen:</span>
                            @if ($logbook->lecturer_feedback)
                                <p class="text-xs text-gray-800 italic bg-white p-2.5 rounded-lg border border-gray-200/80">
                                    "{{ $logbook->lecturer_feedback }}"
                                </p>
                            @else
                                <p class="text-[11px] text-gray-400 italic">Belum ada catatan feedback dari dosen.</p>
                            @endif
                        </div>
                    </div>

                </div>
            </div>

            <!-- 4. Blok Formulir Aksi Sesuai Peran -->
            @if ($isMentor)
                <!-- FORM KHUSUS MENTOR LAPANGAN DINAS -->
                <div class="bg-white rounded-2xl p-6 border-2 border-blue-300 shadow-sm space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                        <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Formulir Verifikasi Pembimbing Lapangan
                        </h3>
                    </div>

                    <form action="{{ route('mentor.logbooks.updateStatus', $logbook->id) }}" method="POST" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <div>
                            <label for="feedback" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                Catatan / Feedback Pembimbing Lapangan:
                            </label>
                            <textarea id="feedback" name="feedback" rows="3"
                                class="w-full text-xs sm:text-sm border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-xs"
                                placeholder="Tuliskan catatan komentar atau instruksi bimbingan dinas...">{{ old('feedback', $logbook->feedback) }}</textarea>
                        </div>

                        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-3 border-t border-gray-100">
                            <div class="flex items-center gap-2 w-full sm:w-auto">
                                <button type="submit" name="status" value="approved" class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-sm transition cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Setujui (Approve)
                                </button>
                                <button type="submit" name="status" value="rejected" class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl shadow-sm transition cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    Tolak / Minta Revisi (Reject)
                                </button>
                            </div>

                            <a href="{{ $backUrl }}" class="w-full sm:w-auto text-center px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-xl transition">Kembali
                            </a>
                        </div>
                    </form>
                </div>

            @elseif ($isLecturer)
                <!-- FORM KHUSUS DOSEN PEMBIMBING KAMPUS (DPL) -->
                <div class="bg-white rounded-2xl p-6 border-2 border-blue-400 shadow-sm space-y-4 bg-blue-50/10">
                    <div class="flex items-center justify-between border-b border-blue-100 pb-3">
                        <h3 class="text-base font-bold text-blue-950 flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Aksi Verifikasi & Feedback Dosen Kampus
                        </h3>
                        <span class="text-xs font-bold px-3 py-1 rounded-full
                            {{ $logbook->lecturer_status === 'approved' ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : '' }}
                            {{ $logbook->lecturer_status === 'pending' ? 'bg-amber-100 text-amber-800 border border-amber-300' : '' }}
                            {{ $logbook->lecturer_status === 'rejected' ? 'bg-rose-100 text-rose-800 border border-rose-300' : '' }}">
                            Status Dosen: {{ strtoupper($logbook->lecturer_status ?? 'PENDING') }}
                        </span>
                    </div>

                    <form action="{{ route('lecturer.logbooks.updateStatus', $logbook->id) }}" method="POST" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <div>
                            <label for="lecturer_feedback" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                Catatan / Feedback Dosen Kampus:
                            </label>
                            <textarea id="lecturer_feedback" name="feedback" rows="3"
                                class="w-full text-xs sm:text-sm border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-xs"
                                placeholder="Tuliskan catatan evaluasi akademik, konfirmasi kegiatan, atau instruksi perbaikan untuk mahasiswa...">{{ old('feedback', $logbook->lecturer_feedback) }}</textarea>
                            <p class="text-[11px] text-gray-400 mt-1">Catatan ini akan tampil pada feed logbook mahasiswa dan portal instansi.</p>
                        </div>

                        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-3 border-t border-gray-100">
                            <div class="flex items-center gap-2 w-full sm:w-auto">
                                <button type="submit" name="status" value="approved" class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-sm transition cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Setujui Logbook
                                </button>
                                <button type="submit" name="status" value="rejected" class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl shadow-sm transition cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    Minta Revisi
                                </button>
                            </div>

                            <a href="{{ $backUrl }}" class="w-full sm:w-auto text-center px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-xl transition">
                                Kembali ke Daftar Logbook
                            </a>
                        </div>
                    </form>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>