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
        $startDate = $app?->start_date ? \Carbon\Carbon::parse($app->start_date)->startOfDay() : null;
        $endDate = $app?->end_date ? \Carbon\Carbon::parse($app->end_date)->startOfDay() : null;
        $today = now()->startOfDay();
        $isUpcoming = $startDate && $startDate->isAfter($today) && in_array($appStatus, ['pending', 'waiting_approval', 'accepted'], true);
        $isCompleted = $appStatus === 'completed';

        // Keterangan periode yang selalu sesuai tanggal hari ini
        $periodNote = match (true) {
            $isCompleted => 'Magang sudah dinyatakan selesai.',
            in_array($appStatus, ['rejected', 'resigned'], true) => '',
            $isUpcoming => 'Dimulai dalam '.(int) $today->diffInDays($startDate).' hari.',
            $endDate !== null && $endDate->isBefore($today) => 'Masa magang sudah berakhir '.(int) $endDate->diffInDays($today).' hari lalu. Lengkapi pemeriksaan dan nilai agar sertifikat dapat diproses.',
            $endDate !== null && $endDate->equalTo($today) => 'Hari ini adalah hari terakhir magang.',
            $endDate !== null => 'Masa magang sedang berjalan, berakhir dalam '.(int) $today->diffInDays($endDate).' hari.',
            default => 'Masa magang sedang berjalan.',
        };

        $stepStyles = [
            'done' => ['box' => 'bg-emerald-50 border-emerald-300', 'icon' => 'bg-emerald-600', 'label' => 'Selesai', 'labelClass' => 'text-emerald-800'],
            'todo' => ['box' => 'bg-amber-50 border-amber-400', 'icon' => 'bg-amber-500', 'label' => 'Perlu Anda kerjakan', 'labelClass' => 'text-amber-900'],
            'waiting' => ['box' => 'bg-slate-50 border-slate-200', 'icon' => 'bg-slate-400', 'label' => 'Belum ada yang perlu dikerjakan', 'labelClass' => 'text-slate-700'],
        ];
        $firstTask = $nextTasks[0] ?? null;

        // Nomor Telepon & Tautan WhatsApp
        $rawPhone = $profile?->phone ?? $student?->phone;
        $waLink = null;
        if ($rawPhone) {
            $cleanPhone = preg_replace('/[^0-9]/', '', (string) $rawPhone);
            if (str_starts_with($cleanPhone, '0')) {
                $cleanPhone = '62'.substr($cleanPhone, 1);
            }
            if (strlen($cleanPhone) >= 9) {
                $greetingPlace = $agency?->agency_name ? ' di '.$agency->agency_name : '';
                $msg = rawurlencode("Halo {$student?->name}, perihal bimbingan magang{$greetingPlace}.");
                $waLink = "https://wa.me/{$cleanPhone}?text={$msg}";
            }
        }
    @endphp

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('mentor.dashboard') }}"
                   class="min-h-[48px] min-w-[48px] inline-flex items-center justify-center rounded-xl bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition shadow-2xs"
                   title="Kembali ke Beranda Mentor" aria-label="Kembali ke Beranda Mentor">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
                <div>
                    <h2 class="font-bold text-2xl text-slate-900 leading-tight">
                        {{ $student?->name ?? '-' }}
                    </h2>
                    <p class="text-base text-slate-700 mt-1">
                        NIM/NIS: {{ $profile?->nim ?? '-' }} &bull; {{ $profile?->universitas ?? '-' }} ({{ $profile?->jurusan ?? '-' }})
                    </p>
                </div>
            </div>

            <!-- Tombol Komunikasi & Koordinasi Lapangan -->
            <div class="flex flex-wrap items-center gap-2">
                @if ($student)
                    <x-chat-button :user="$student" label="Chat Peserta Magang" />
                @endif

                @if ($waLink)
                    <a href="{{ $waLink }}" target="_blank" rel="noopener noreferrer"
                       class="min-h-[44px] inline-flex items-center justify-center gap-1.5 px-4 py-2 text-sm font-bold rounded-xl transition shadow-xs whitespace-nowrap cursor-pointer bg-white border border-slate-300 hover:border-[#25D366] hover:bg-emerald-50/30 text-slate-800"
                       title="Hubungi Peserta Magang via WhatsApp">
                        <svg class="w-5 h-5 shrink-0 fill-[#25D366]" viewBox="0 0 24 24" aria-hidden="true"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                        <span>WhatsApp</span>
                    </a>
                @endif

                <x-chat-group-button :placement="$placement" />
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @unless ($canEvaluate ?? true)
                <div class="p-4 bg-slate-50 border border-slate-300 rounded-xl text-base text-slate-800">
                    Anda membuka halaman ini sebagai <strong>Kepala Unit</strong> (validasi sementara karena mentor belum ditunjuk).
                    Anda dapat memeriksa dan menyetujui logbook. Penilaian dan persetujuan laporan akhir dilakukan oleh mentor yang nanti ditugaskan.
                </div>
            @endunless

            <!-- 1. Daftar Periksa: 3 langkah wajib mentor untuk mahasiswa ini -->
            <section class="bg-white rounded-2xl p-5 sm:p-7 border border-slate-200 shadow-xs space-y-5" aria-labelledby="judul-langkah">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h3 id="judul-langkah" class="text-xl font-bold text-slate-900">Tugas Anda untuk Peserta Magang Ini</h3>
                        <p class="text-base text-slate-700 mt-0.5">Tiga langkah wajib mentor. Langkah berwarna kuning perlu Anda kerjakan.</p>
                    </div>
                    <x-status-badge :status="$appStatus" />
                </div>

                <ol class="grid grid-cols-1 lg:grid-cols-3 gap-3">
                    @foreach ($checklist as $i => $step)
                        @php $style = $stepStyles[$step['state']] ?? $stepStyles['waiting']; @endphp
                        <li>
                            <a href="{{ $step['anchor'] }}" class="block h-full p-4 rounded-xl border-2 {{ $style['box'] }} hover:shadow-sm transition">
                                <div class="flex items-center gap-3">
                                    <span class="w-9 h-9 rounded-full {{ $style['icon'] }} text-white font-bold flex items-center justify-center shrink-0" aria-hidden="true">
                                        @if ($step['state'] === 'done')
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                        @else
                                            {{ $i + 1 }}
                                        @endif
                                    </span>
                                    <div>
                                        <p class="text-lg font-bold text-slate-900 leading-tight">{{ $step['title'] }}</p>
                                        <p class="text-sm font-bold {{ $style['labelClass'] }}">{{ $style['label'] }}</p>
                                    </div>
                                </div>
                                <p class="text-base text-slate-800 mt-2 leading-relaxed">{{ $step['text'] }}</p>
                            </a>
                        </li>
                    @endforeach
                </ol>

                @if ($firstTask)
                    <div class="p-4 rounded-xl bg-blue-50 border border-blue-200 flex flex-col md:flex-row md:items-center justify-between gap-3">
                        <div>
                            <p class="text-sm font-bold text-blue-900">Langkah berikutnya</p>
                            <p class="text-lg font-bold text-slate-900">{{ $firstTask['title'] }}</p>
                            <p class="text-base text-slate-800">{{ $firstTask['description'] }}</p>
                        </div>
                        <a href="{{ $firstTask['url'] }}"
                           class="min-h-[52px] inline-flex items-center justify-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white text-base font-bold rounded-xl shadow-xs transition shrink-0">
                            {{ $firstTask['button'] }}
                        </a>
                    </div>
                @elseif (! $isCompleted && ! in_array($appStatus, ['rejected', 'resigned'], true))
                    <p class="text-base text-emerald-800 font-semibold">Tidak ada tugas yang menunggu untuk peserta magang ini saat ini.</p>
                @endif
            </section>

            <!-- 2. Data Magang & Penugasan -->
            <section class="bg-white rounded-2xl p-5 sm:p-7 border border-slate-200 shadow-xs space-y-4" aria-labelledby="judul-data">
                <h3 id="judul-data" class="text-xl font-bold text-slate-900">Data Magang &amp; Penugasan</h3>
                <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 text-base">
                    <div>
                        <dt class="text-sm text-slate-700 font-semibold">Kampus/Sekolah</dt>
                        <dd class="font-semibold text-slate-900 mt-0.5">{{ $profile->universitas ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-slate-700 font-semibold">Program Studi</dt>
                        <dd class="font-semibold text-slate-900 mt-0.5">{{ $profile->jurusan ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-slate-700 font-semibold">Instansi &amp; Bidang</dt>
                        <dd class="font-semibold text-slate-900 mt-0.5">{{ $agency->agency_name ?? '-' }} &mdash; {{ $unit->name ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-slate-700 font-semibold">Mentor Lapangan (Dinas)</dt>
                        <dd class="font-semibold text-slate-900 mt-0.5">{{ $mentor->name ?? 'Belum ditunjuk' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-slate-700 font-semibold">Pembimbing Kampus/Sekolah</dt>
                        <dd class="font-semibold text-slate-900 mt-0.5">{{ $academicAdvisor->name ?? 'Belum Ditugaskan' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-slate-700 font-semibold">Periode Magang</dt>
                        <dd class="font-semibold text-slate-900 mt-0.5">
                            {{ $startDate ? $startDate->translatedFormat('d F Y') : '-' }} s/d {{ $endDate ? $endDate->translatedFormat('d F Y') : '-' }}
                        </dd>
                        @if ($periodNote !== '')
                            <dd class="text-sm text-slate-700 mt-0.5">{{ $periodNote }}</dd>
                        @endif
                    </div>
                    @if($app?->group_id && $app?->group)
                        <div class="sm:col-span-2 lg:col-span-3">
                            <dt class="text-sm text-indigo-800 font-semibold">Kelompok Magang</dt>
                            <dd class="font-bold text-indigo-900 mt-0.5">
                                {{ $app->group->name }} ({{ $app->role_in_group === 'leader' ? 'Ketua' : 'Anggota' }})
                            </dd>
                        </div>
                    @endif
                </dl>
            </section>

            <!-- 3. Logbook Aktivitas Harian -->
            <section id="bagian-logbook" class="scroll-mt-24 bg-white rounded-2xl p-5 sm:p-7 border border-slate-200 shadow-xs space-y-4" aria-labelledby="judul-logbook">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div>
                        <h3 id="judul-logbook" class="text-xl font-bold text-slate-900">Langkah 1 &mdash; Logbook Kegiatan Harian</h3>
                        <p class="text-base text-slate-700 mt-0.5">Dikelompokkan per minggu. Periksa minggu yang bertanda kuning.</p>
                    </div>
                    @if ($placement->logbooks->count() > 0)
                        <span class="px-3 py-1 bg-slate-100 text-slate-800 text-sm font-bold rounded-full">
                            {{ $placement->logbooks->count() }} hari terisi
                        </span>
                    @endif
                </div>

                @if ($isUpcoming)
                    <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl text-base text-slate-800">
                        Masa magang dimulai <strong>{{ $startDate->translatedFormat('d F Y') }}</strong>. Logbook akan muncul di sini setelah peserta magang mulai mengisinya.
                    </div>
                @endif

                <x-supervision.logbook-weekly
                    :bundles="$weeklyBundles"
                    :reviewRoute="route('mentor.logbooks.bulk_review')"
                    role="mentor"
                    :showBulkSelect="false"
                    emptyMessage="Belum ada logbook kegiatan yang diisi peserta magang."
                />
            </section>

            <!-- 4. Laporan Akhir & Proyek Inovasi -->
            <section id="bagian-laporan" class="scroll-mt-24 space-y-3" aria-label="Langkah 2 Laporan Akhir">
                <h3 class="text-xl font-bold text-slate-900 px-1">Langkah 2 &mdash; Laporan Akhir &amp; Proyek</h3>
                <x-supervision.report-review
                    :finalReport="$placement->finalreport"
                    :placement="$placement"
                    :submitRoute="route('mentor.final_report.updateStatus', optional($placement->finalreport)->id ?? 0)"
                    method="PUT"
                    role="mentor"
                    :canReview="$canEvaluate ?? true"
                />
            </section>

            <!-- 5. Penilaian Evaluasi Akhir -->
            <section id="bagian-nilai" class="scroll-mt-24 bg-white rounded-2xl p-5 sm:p-7 border border-slate-200 shadow-xs space-y-5" aria-labelledby="judul-nilai">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h3 id="judul-nilai" class="text-xl font-bold text-slate-900">Langkah 3 &mdash; Nilai Akhir</h3>
                        <p class="text-base text-slate-700 mt-0.5">Nilai dari Anda dan dari pembimbing kampus/sekolah.</p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        @if ($canEvaluate ?? true)
                            @if (! empty($evaluationLockReason))
                                @if ($isCompleted || $hasEval)
                                    <a href="{{ route('mentor.evaluations.create', $placement->id) }}"
                                       class="min-h-[48px] inline-flex items-center px-5 py-2.5 bg-white hover:bg-slate-50 text-slate-900 text-base font-bold rounded-xl border-2 border-slate-300 transition">
                                        Lihat Rincian Nilai
                                    </a>
                                @endif
                            @else
                                <a href="{{ route('mentor.evaluations.create', $placement->id) }}"
                                   class="min-h-[52px] inline-flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white text-base font-bold rounded-xl shadow-xs transition">
                                    {{ $hasEval ? 'Ubah Nilai' : 'Isi Penilaian Magang' }}
                                </a>
                            @endif
                            @if ($hasEval)
                                <a href="{{ route('mentor.evaluations.print', $placement->id) }}" target="_blank" rel="noopener"
                                   class="min-h-[48px] inline-flex items-center px-5 py-2.5 bg-white hover:bg-slate-50 text-slate-900 text-base font-bold rounded-xl border-2 border-slate-300 transition">
                                    Cetak Lembar Nilai
                                </a>
                            @endif
                        @endif
                    </div>
                </div>

                @if (! empty($evaluationLockReason) && ($canEvaluate ?? true))
                    <div class="p-4 {{ $isCompleted ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : 'bg-slate-50 border-slate-200 text-slate-800' }} border rounded-xl text-base">
                        @if ($isCompleted)
                            <p class="font-bold">Nilai sudah terkunci karena magang dinyatakan selesai.</p>
                            <p class="mt-0.5">Sertifikat diproses oleh Admin Dinas. Hubungi Admin Dinas bila perlu koreksi nilai.</p>
                        @else
                            <p class="font-bold">Formulir nilai belum dibuka.</p>
                            <p class="mt-0.5">{{ $evaluationLockReason }}@if ($startDate) Masa magang dimulai {{ $startDate->translatedFormat('d F Y') }}.@endif</p>
                        @endif
                    </div>
                @endif

                <!-- Perbandingan Nilai -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="p-4 sm:p-5 bg-slate-50 rounded-2xl border border-slate-200 space-y-3">
                        <span class="text-base font-bold text-slate-900 block">Nilai dari Anda (Mentor Lapangan)</span>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                            @forelse ($eval?->mentor_aspects_list ?? [] as $mAspect)
                                <div class="p-3 bg-white rounded-xl border border-slate-200">
                                    <span class="text-sm text-slate-700 block">{{ $mAspect['name'] }}</span>
                                    <span class="text-xl font-bold text-slate-900">{{ $mAspect['score'] ?? '-' }}</span>
                                </div>
                            @empty
                                <div class="sm:col-span-3 p-3 bg-white rounded-xl text-slate-700 text-base">
                                    Belum ada nilai dari Anda.
                                </div>
                            @endforelse
                        </div>
                        <div class="text-base text-slate-800">
                            Rata-rata: <strong class="text-slate-900">{{ $eval?->nilai_pembimbing > 0 ? $eval->nilai_pembimbing : '-' }}</strong>
                        </div>
                    </div>

                    <div class="p-4 sm:p-5 bg-slate-50 rounded-2xl border border-slate-200 space-y-3">
                        <span class="text-base font-bold text-slate-900 block">Nilai dari Pembimbing Kampus/Sekolah</span>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                            @forelse ($eval?->dosen_aspects_list ?? [] as $dAspect)
                                <div class="p-3 bg-white rounded-xl border border-slate-200">
                                    <span class="text-sm text-slate-700 block">{{ $dAspect['name'] }}</span>
                                    <span class="text-xl font-bold text-slate-900">{{ $dAspect['score'] ?? '-' }}</span>
                                </div>
                            @empty
                                <div class="sm:col-span-3 p-3 bg-white rounded-xl text-slate-700 text-base">
                                    Pembimbing kampus/sekolah belum mengisi nilai.
                                </div>
                            @endforelse
                        </div>
                        <div class="text-base text-slate-800">
                            Rata-rata: <strong class="text-slate-900">{{ $eval?->nilai_dosen_calculated > 0 ? $eval->nilai_dosen_calculated : '-' }}</strong>
                        </div>
                    </div>
                </div>

                <div class="p-4 sm:p-5 bg-white rounded-2xl border border-slate-200 flex flex-col sm:flex-row justify-between sm:items-center gap-4">
                    <div>
                        <span class="text-base text-slate-700 font-semibold block">Nilai Akhir Magang</span>
                        <div class="text-3xl font-black text-slate-900 mt-0.5">
                            {{ $eval?->final_score > 0 ? $eval->final_score : ($eval?->nilai_akhir > 0 ? $eval->nilai_akhir : '-') }}
                        </div>
                        @if (! ($eval?->final_score > 0))
                            <span class="text-sm text-slate-700">Dihitung otomatis setelah semua nilai yang dibutuhkan lengkap.</span>
                        @endif
                    </div>
                    <div class="sm:text-right">
                        <span class="text-base text-slate-700 font-semibold block">Predikat</span>
                        <div class="text-3xl font-black text-blue-800 mt-0.5">
                            {{ $eval?->grade_calculated ?? '-' }}
                        </div>
                    </div>
                </div>

                @if ($eval?->catatan)
                    <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 text-base text-slate-800">
                        <strong>Catatan evaluasi Anda:</strong> "{{ $eval->catatan }}"
                    </div>
                @endif
            </section>

        </div>
    </div>
</x-app-layout>
