<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="font-bold text-2xl text-slate-900 leading-tight">
                    Beranda Mentor Lapangan
                </h2>
                <p class="text-base text-slate-700 mt-1">
                    Selamat datang, {{ Auth::user()->name }}. Hari ini {{ now()->translatedFormat('l, d F Y') }}.
                </p>
            </div>

            @if (Auth::user()->agencyProfile || Auth::user()->unit)
                <span class="inline-flex items-center px-3.5 py-2 bg-blue-50 border border-blue-200 text-blue-900 rounded-xl text-sm font-semibold">
                    {{ Auth::user()->agencyProfile->agency_name ?? '' }}@if(Auth::user()->agencyProfile && Auth::user()->unit) &bull; @endif{{ Auth::user()->unit->name ?? '' }}
                </span>
            @endif
        </div>
    </x-slot>

    @php
        $taskTypeStyles = [
            'evaluation' => 'bg-blue-100 text-blue-800',
            'report' => 'bg-amber-100 text-amber-900',
            'logbook' => 'bg-emerald-100 text-emerald-900',
            'reminder' => 'bg-slate-200 text-slate-800',
        ];
        $taskTypeLabels = [
            'evaluation' => 'Nilai Akhir',
            'report' => 'Laporan Akhir',
            'logbook' => 'Logbook Harian',
            'reminder' => 'Pengingat',
        ];
        $visibleTaskCount = 8;
    @endphp

    <div class="py-6 sm:py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- 1. DAFTAR TUGAS: satu daftar urut dari yang paling mendesak --}}
            <section class="bg-white rounded-2xl border border-slate-200 shadow-xs" aria-labelledby="judul-tugas" x-data="{ showAll: false }">
                <div class="p-5 sm:p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div>
                        <h3 id="judul-tugas" class="text-xl font-bold text-slate-900">Daftar Tugas Anda</h3>
                        <p class="text-base text-slate-700 mt-0.5">
                            @if ($tasks->isNotEmpty())
                                Perlu Tindakan Segera: kerjakan dari atas ke bawah. Klik tombol biru di setiap baris.
                            @else
                                Tugas akan muncul di sini saat peserta magang mengirim logbook, laporan, atau saat waktunya memberi nilai.
                            @endif
                        </p>
                    </div>
                    <span class="self-start sm:self-center inline-flex items-center px-4 py-1.5 rounded-full text-base font-bold {{ $tasks->isNotEmpty() ? 'bg-amber-100 text-amber-900' : 'bg-emerald-100 text-emerald-900' }}">
                        {{ $tasks->count() }} tugas
                    </span>
                </div>

                @if ($tasks->isEmpty())
                    <div class="p-6 flex items-start gap-3 text-emerald-900">
                        <svg class="w-7 h-7 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <p class="text-lg font-semibold">Tidak ada tugas yang menunggu. Semua pekerjaan bimbingan sudah Anda selesaikan.</p>
                    </div>
                @else
                    <ol class="divide-y divide-slate-100">
                        @foreach ($tasks as $index => $task)
                            <li class="p-4 sm:p-5 flex flex-col md:flex-row md:items-center gap-4 {{ $task['urgent'] ? 'bg-rose-50/60' : '' }}"
                                @if ($index >= $visibleTaskCount) x-show="showAll" x-cloak @endif>
                                <div class="flex items-start gap-4 flex-1 min-w-0">
                                    <span class="w-10 h-10 rounded-full {{ $task['urgent'] ? 'bg-rose-600' : 'bg-slate-800' }} text-white text-lg font-bold flex items-center justify-center shrink-0" aria-hidden="true">
                                        {{ $index + 1 }}
                                    </span>
                                    <div class="min-w-0 space-y-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="px-2.5 py-0.5 rounded-md text-sm font-bold {{ $taskTypeStyles[$task['type']] ?? 'bg-slate-100 text-slate-800' }}">
                                                {{ $taskTypeLabels[$task['type']] ?? 'Tugas' }}
                                            </span>
                                            @if ($task['badge'])
                                                <span class="px-2.5 py-0.5 rounded-md text-sm font-bold bg-rose-600 text-white">{{ $task['badge'] }}</span>
                                            @endif
                                        </div>
                                        <p class="text-lg font-bold text-slate-900 leading-snug">{{ $task['title'] }}</p>
                                        <p class="text-base text-slate-700 leading-relaxed">{{ $task['description'] }}</p>
                                    </div>
                                </div>
                                <a href="{{ $task['url'] }}"
                                   class="min-h-[52px] w-full md:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white text-base font-bold rounded-xl transition shadow-xs shrink-0">
                                    {{ $task['button'] }}
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </li>
                        @endforeach
                    </ol>
                    @if ($tasks->count() > $visibleTaskCount)
                        <div class="p-4 border-t border-slate-100 text-center">
                            <button type="button" @click="showAll = !showAll"
                                    class="min-h-[48px] px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-900 text-base font-bold rounded-xl transition cursor-pointer">
                                <span x-text="showAll ? 'Tampilkan lebih sedikit' : 'Tampilkan semua {{ $tasks->count() }} tugas'"></span>
                            </button>
                        </div>
                    @endif
                @endif
            </section>

            {{-- 2. PANDUAN SINGKAT: apa saja tugas mentor dan urutannya --}}
            <details class="bg-white rounded-2xl border border-slate-200 shadow-xs group">
                <summary class="p-5 sm:p-6 cursor-pointer list-none flex items-center justify-between gap-3 min-h-[56px]">
                    <span class="text-lg font-bold text-slate-900">Panduan singkat: apa saja tugas mentor?</span>
                    <span class="text-base font-semibold text-blue-700 group-open:hidden">Buka panduan</span>
                    <span class="text-base font-semibold text-blue-700 hidden group-open:inline">Tutup</span>
                </summary>
                <div class="px-5 sm:px-6 pb-6 grid grid-cols-1 md:grid-cols-3 gap-4 text-base text-slate-800">
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-1">
                        <p class="font-bold text-slate-900">1. Periksa logbook harian</p>
                        <p>Selama magang berjalan, peserta magang mengisi kegiatan setiap hari. Baca per minggu, pilih <strong>Sesuai</strong> atau <strong>Perlu Diperbaiki</strong> untuk tiap hari, lalu simpan.</p>
                    </div>
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-1">
                        <p class="font-bold text-slate-900">2. Periksa laporan akhir</p>
                        <p>Menjelang akhir magang, peserta magang mengunggah naskah laporan. Buka naskahnya, lalu <strong>Setujui</strong> atau <strong>Kembalikan untuk Diperbaiki</strong> dengan catatan.</p>
                    </div>
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-1">
                        <p class="font-bold text-slate-900">3. Beri nilai akhir</p>
                        <p>Isi nilai setiap aspek (0–100) paling lambat pada tanggal akhir magang. Sertifikat peserta magang baru diproses setelah laporan disetujui dan nilai lengkap.</p>
                    </div>
                    <p class="md:col-span-3 text-slate-700">
                        Persetujuan penerimaan peserta magang tetap dilakukan Admin Dinas dan Atasan. Anda cukup mengerjakan tugas yang muncul di <strong>Daftar Tugas Anda</strong>; bila daftar kosong, berarti tidak ada yang tertinggal.
                    </p>
                </div>
            </details>

            {{-- 3. RINGKASAN ANGKA (dihitung dari sumber yang sama dengan Daftar Tugas) --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
                    <span class="text-base font-semibold text-slate-700 block">Peserta Bimbingan</span>
                    <div class="text-3xl font-black text-slate-900 mt-1">{{ $stats['total_students'] }}</div>
                    <span class="text-sm text-slate-700 mt-2 block">
                        {{ $stats['active_students'] }} aktif &middot; {{ $stats['upcoming_students'] }} belum mulai &middot; {{ $stats['completed_students'] }} selesai
                    </span>
                </div>

                <a href="{{ route('mentor.logbooks.index') }}" class="bg-white p-5 rounded-2xl border border-slate-200 hover:border-blue-400 shadow-xs transition group block">
                    <span class="text-base font-semibold text-slate-700 block group-hover:text-blue-800">Logbook Belum Diperiksa</span>
                    <div class="text-3xl font-black {{ $stats['pending_logbooks'] > 0 ? 'text-amber-700' : 'text-slate-900' }} mt-1">
                        {{ $stats['pending_logbooks'] }} <span class="text-lg font-bold">hari</span>
                    </div>
                    <span class="text-sm text-blue-800 font-semibold mt-2 block">
                        @if ($stats['pending_logbooks'] > 0)
                            dari {{ $stats['pending_logbook_students'] }} peserta magang &middot; Buka halaman logbook &rarr;
                        @else
                            Semua logbook sudah diperiksa
                        @endif
                    </span>
                </a>

                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
                    <span class="text-base font-semibold text-slate-700 block">Laporan Menunggu Anda</span>
                    <div class="text-3xl font-black {{ $stats['total_reports_pending'] > 0 ? 'text-amber-700' : 'text-slate-900' }} mt-1">{{ $stats['total_reports_pending'] }}</div>
                    <span class="text-sm text-slate-700 mt-2 block">{{ $stats['total_reports_approved'] }} laporan sudah disetujui</span>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
                    <span class="text-base font-semibold text-slate-700 block">Belum Diberi Nilai</span>
                    <div class="text-3xl font-black {{ $stats['evaluation_due'] > 0 ? 'text-amber-700' : 'text-slate-900' }} mt-1">{{ $stats['total_pending_eval'] }}</div>
                    <span class="text-sm text-slate-700 mt-2 block">
                        @if ($stats['evaluation_overdue'] > 0)
                            {{ $stats['evaluation_due'] }} perlu segera diisi ({{ $stats['evaluation_overdue'] }} sudah lewat tanggal akhir)
                        @elseif ($stats['evaluation_due'] > 0)
                            {{ $stats['evaluation_due'] }} perlu segera diisi
                        @elseif ($stats['total_pending_eval'] > 0)
                            Nilai diisi menjelang akhir magang ({{ $stats['total_evaluated'] }} sudah dinilai)
                        @else
                            {{ $stats['total_evaluated'] }} peserta magang sudah dinilai
                        @endif
                    </span>
                </div>
            </div>

            {{-- 4. TAB STATUS & PENCARIAN --}}
            <div class="space-y-3">
                @php
                    $activeTab = $tab ?: 'all';
                    $tabBaseQuery = request()->except(['tab', 'page']);
                    $tabs = [
                        'all' => ['Semua', $stats['total_students']],
                        'active' => [\App\Enums\ApplicationStatus::ACTIVE->localizedLabel('mentor'), $stats['active_students']],
                        'upcoming' => ['Belum Mulai Magang', $stats['upcoming_students']],
                        'completed' => [\App\Enums\ApplicationStatus::COMPLETED->localizedLabel('mentor'), $stats['completed_students']],
                    ];
                @endphp
                <h3 class="text-xl font-bold text-slate-900">Daftar Peserta Bimbingan Magang</h3>
                <div class="border-b border-slate-200 overflow-x-auto">
                    <nav class="flex gap-6 min-w-max" aria-label="Filter Status Peserta Magang">
                        @foreach ($tabs as $tabKey => [$tabLabel, $tabCount])
                            <a href="{{ route('mentor.dashboard', array_merge($tabBaseQuery, ['tab' => $tabKey])) }}"
                               @if ($activeTab === $tabKey) aria-current="page" @endif
                               class="py-3 px-1 border-b-4 text-base inline-flex items-center gap-2 transition min-h-[48px] {{ $activeTab === $tabKey ? 'border-blue-600 text-blue-800 font-bold' : 'border-transparent text-slate-700 hover:text-slate-900 font-semibold' }}">
                                <span>{{ $tabLabel }}</span>
                                <span class="px-2.5 py-0.5 rounded-full text-sm font-bold {{ $activeTab === $tabKey ? 'bg-blue-100 text-blue-900' : 'bg-slate-100 text-slate-800' }}">{{ $tabCount }}</span>
                            </a>
                        @endforeach
                    </nav>
                </div>

                <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-xs">
                    <form method="GET" action="{{ route('mentor.dashboard') }}" class="flex flex-col lg:flex-row items-stretch lg:items-center gap-3">
                        @if(request('tab'))
                            <input type="hidden" name="tab" value="{{ request('tab') }}">
                        @endif
                        <div class="relative flex-1">
                            <label for="cari-mahasiswa" class="sr-only">Cari peserta magang</label>
                            <div class="absolute inset-y-0 left-0 flex items-center pointer-events-none pl-3.5 text-slate-500">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                            <input id="cari-mahasiswa" type="text" name="search" value="{{ request('search', request('q')) }}"
                                   placeholder="Cari nama peserta magang atau NIM/NIS..."
                                   class="w-full min-h-[48px] text-base border-slate-300 rounded-xl pl-11 pr-4 py-2.5 focus:ring-blue-600 focus:border-blue-600 shadow-2xs">
                        </div>

                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 shrink-0">
                            <select name="report_status" onchange="this.form.submit()"
                                    aria-label="Filter status laporan akhir"
                                    class="min-h-[48px] py-2.5 pl-3.5 pr-9 text-base border-slate-300 rounded-xl focus:ring-blue-600 focus:border-blue-600 shadow-2xs bg-white">
                                <option value="">Semua Status Laporan</option>
                                @foreach (\App\Enums\ReviewStatus::cases() as $reviewCase)
                                    @continue($reviewCase === \App\Enums\ReviewStatus::REJECTED)
                                    <option value="{{ $reviewCase->value }}" {{ request('report_status') == $reviewCase->value ? 'selected' : '' }}>{{ $reviewCase->localizedLabel('mentor') }}</option>
                                @endforeach
                                <option value="none" {{ request('report_status') == 'none' ? 'selected' : '' }}>Belum Unggah Laporan</option>
                            </select>

                            @if($universities->count() > 1)
                                <select name="university_id" onchange="this.form.submit()"
                                        aria-label="Filter perguruan tinggi"
                                        class="min-h-[48px] py-2.5 pl-3.5 pr-9 text-base border-slate-300 rounded-xl focus:ring-blue-600 focus:border-blue-600 shadow-2xs bg-white max-w-full sm:max-w-xs truncate">
                                    <option value="">Semua Perguruan Tinggi</option>
                                    @foreach($universities as $u)
                                        <option value="{{ $u->id }}" {{ request('university_id') == $u->id ? 'selected' : '' }}>
                                            {{ $u->name }}
                                        </option>
                                    @endforeach
                                </select>
                            @endif

                            <div class="flex items-center gap-2">
                                <button type="submit" class="min-h-[48px] flex-1 sm:flex-initial px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-base font-bold rounded-xl shadow-xs transition cursor-pointer">
                                    Cari
                                </button>

                                @if(request()->hasAny(['search', 'q', 'university_id', 'report_status']))
                                    <a href="{{ route('mentor.dashboard', array_filter(['tab' => request('tab')])) }}"
                                       class="min-h-[48px] inline-flex items-center justify-center px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-800 text-base font-bold rounded-xl transition">
                                        Reset
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- 5. DAFTAR MAHASISWA BIMBINGAN MENTOR --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="p-5 border-b border-slate-100 flex items-center justify-between gap-2">
                    <p class="text-base text-slate-700">Klik <strong>Buka</strong> untuk melihat logbook, laporan, dan nilai seorang peserta magang.</p>
                    <span class="text-sm text-slate-800 font-bold bg-slate-100 px-3 py-1 rounded-full shrink-0">
                        {{ $placements->total() }} Peserta Magang
                    </span>
                </div>

                @php
                    $rows = $placements->map(function ($p) use ($tasksByPlacement) {
                        $student = $p->application?->user;
                        $profile = $student?->studentProfile;
                        $eval = $p->evaluation;
                        $nilaiMentor = $eval ? $eval->nilai_pembimbing : 0;

                        return [
                            'p' => $p,
                            'student' => $student,
                            'profile' => $profile,
                            'unit' => $p->application?->unit,
                            'status' => $p->application?->status,
                            'nilai' => $nilaiMentor,
                            'hasEval' => $eval && $nilaiMentor > 0,
                            'grade' => \App\Support\Grade::fromScore($nilaiMentor),
                            'report' => $p->finalreport,
                            'logTotal' => (int) ($p->logbooks_count ?? 0),
                            'logPending' => (int) ($p->pending_logbooks_count ?? 0),
                            'univ' => $student?->universityRelation?->name ?? $student?->university ?? $profile?->universitas ?? '-',
                            'taskCount' => $tasksByPlacement->get($p->id)?->count() ?? 0,
                        ];
                    });
                @endphp

                {{-- Tampilan kartu untuk layar di bawah 1280px (ponsel, tablet, laptop dengan zoom besar) --}}
                <div class="block xl:hidden divide-y divide-slate-100">
                    @forelse($rows as $row)
                        <div class="p-4 sm:p-5 space-y-3">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="font-bold text-lg text-slate-900 leading-snug">{{ $row['student']->name ?? '-' }}</p>
                                    <p class="text-sm text-slate-700 mt-0.5">NIM/NIS: {{ $row['profile']?->nim ?? '-' }}</p>
                                </div>
                                <x-status-badge :status="$row['status']" />
                            </div>

                            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-1.5 text-base bg-slate-50 p-4 rounded-xl border border-slate-200">
                                <div><dt class="inline font-semibold text-slate-900">Kampus:</dt> <dd class="inline text-slate-800">{{ $row['univ'] }}</dd></div>
                                <div><dt class="inline font-semibold text-slate-900">Unit:</dt> <dd class="inline text-slate-800">{{ $row['unit']->name ?? '-' }}</dd></div>
                                <div>
                                    <dt class="inline font-semibold text-slate-900">Logbook:</dt>
                                    <dd class="inline {{ $row['logPending'] > 0 ? 'text-amber-800 font-bold' : 'text-slate-800' }}">
                                        {{ $row['logPending'] > 0 ? $row['logPending'].' hari belum diperiksa' : $row['logTotal'].' hari terisi' }}
                                    </dd>
                                </div>
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <dt class="font-semibold text-slate-900">Laporan:</dt>
                                    <dd>
                                        @if(! $row['report'])
                                            <span class="text-slate-800">Belum diunggah</span>
                                        @else
                                            <x-status-badge type="review" :status="$row['report']->status" />
                                        @endif
                                    </dd>
                                </div>
                                <div>
                                    <dt class="inline font-semibold text-slate-900">Nilai Mentor:</dt>
                                    <dd class="inline {{ $row['hasEval'] ? 'text-emerald-800 font-bold' : 'text-slate-800' }}">
                                        {{ $row['hasEval'] ? $row['nilai'].' ('.$row['grade'].')' : 'Belum diisi' }}
                                    </dd>
                                </div>
                            </dl>

                            <a href="{{ route('mentor.students.show', $row['p']->id) }}"
                               class="w-full min-h-[52px] inline-flex items-center justify-center gap-2 py-3 px-4 {{ $row['taskCount'] > 0 ? 'bg-blue-600 hover:bg-blue-700 text-white' : 'bg-white border-2 border-slate-300 hover:bg-slate-50 text-slate-900' }} text-base font-bold rounded-xl transition">
                                {{ $row['taskCount'] > 0 ? 'Buka ('.$row['taskCount'].' tugas)' : 'Buka' }}
                            </a>
                        </div>
                    @empty
                        <div class="p-8 text-center text-slate-700 text-base">
                            Belum ada peserta bimbingan magang yang sesuai dengan filter ini.
                        </div>
                    @endforelse
                </div>

                {{-- Tampilan tabel untuk layar lebar (>= 1280px) --}}
                <div class="hidden xl:block">
                    <table class="w-full divide-y divide-slate-200 text-left text-base">
                        <thead class="bg-slate-50 text-slate-800 font-bold text-sm">
                            <tr>
                                <th scope="col" class="py-3.5 px-5">Peserta Magang</th>
                                <th scope="col" class="py-3.5 px-4">Perguruan Tinggi &amp; Unit</th>
                                <th scope="col" class="py-3.5 px-4">Status</th>
                                <th scope="col" class="py-3.5 px-4">Logbook</th>
                                <th scope="col" class="py-3.5 px-4">Laporan Akhir</th>
                                <th scope="col" class="py-3.5 px-4">Nilai Mentor</th>
                                <th scope="col" class="py-3.5 px-5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($rows as $row)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-4 px-5 align-middle">
                                        <div class="font-bold text-slate-900 leading-snug">{{ $row['student']->name ?? '-' }}</div>
                                        <div class="text-sm text-slate-700 mt-0.5">NIM/NIS: {{ $row['profile']?->nim ?? '-' }}</div>
                                    </td>
                                    <td class="py-4 px-4 align-middle">
                                        <div class="font-semibold text-slate-900">{{ $row['univ'] }}</div>
                                        <div class="text-sm text-slate-700 mt-0.5">{{ $row['unit']->name ?? '-' }}</div>
                                    </td>
                                    <td class="py-4 px-4 align-middle">
                                        <x-status-badge :status="$row['status']" />
                                    </td>
                                    <td class="py-4 px-4 align-middle">
                                        @if($row['logPending'] > 0)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-sm font-bold bg-amber-100 text-amber-900 border border-amber-200">
                                                {{ $row['logPending'] }} hari belum diperiksa
                                            </span>
                                        @else
                                            <span class="text-slate-800">{{ $row['logTotal'] }} hari terisi</span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-4 align-middle">
                                        @if(! $row['report'])
                                            <span class="text-slate-800">Belum diunggah</span>
                                        @else
                                            <x-status-badge type="review" :status="$row['report']->status" />
                                        @endif
                                    </td>
                                    <td class="py-4 px-4 align-middle">
                                        @if($row['hasEval'])
                                            <span class="font-bold text-emerald-800">{{ $row['nilai'] }} ({{ $row['grade'] }})</span>
                                        @else
                                            <span class="text-slate-800">Belum diisi</span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-5 text-right align-middle whitespace-nowrap">
                                        <a href="{{ route('mentor.students.show', $row['p']->id) }}"
                                           class="min-h-[48px] inline-flex items-center px-5 py-2.5 {{ $row['taskCount'] > 0 ? 'bg-blue-600 hover:bg-blue-700 text-white' : 'bg-white border-2 border-slate-300 hover:bg-slate-50 text-slate-900' }} rounded-xl text-base font-bold transition">
                                            {{ $row['taskCount'] > 0 ? 'Buka ('.$row['taskCount'].' tugas)' : 'Buka' }}
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-12 text-center text-slate-700">
                                        Belum ada peserta bimbingan magang yang sesuai dengan filter ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($placements->hasPages())
                    <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                        {{ $placements->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
