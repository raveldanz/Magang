<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('mentor.dashboard') }}" class="min-h-[48px] min-w-[48px] inline-flex items-center justify-center bg-white hover:bg-slate-50 border border-slate-300 text-slate-800 rounded-xl transition" title="Kembali ke Beranda Mentor" aria-label="Kembali ke Beranda Mentor">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <h2 class="font-bold text-2xl text-slate-900 leading-tight">
                    Periksa Logbook Mahasiswa
                </h2>
                <p class="text-base text-slate-700 mt-0.5">
                    Logbook dikelompokkan per minggu. Buka minggu yang bertanda kuning, putuskan setiap hari, lalu simpan.
                </p>
            </div>
        </div>
    </x-slot>

    @php
        $filterQuery = request()->except(['status', 'page']);
        $statusTabs = [
            'pending' => ['Perlu Diperiksa', $pendingCount, 'hari'],
            'all' => ['Semua', $pendingCount + $approvedCount + $rejectedCount, 'hari'],
            'approved' => [\App\Enums\ReviewStatus::APPROVED->localizedLabel('mentor'), $approvedCount, 'hari'],
            'rejected' => ['Sedang Diperbaiki Mahasiswa', $rejectedCount, 'hari'],
        ];
    @endphp

    <div class="py-6 sm:py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-5">

            <!-- Ringkasan: berapa yang harus diperiksa -->
            <div class="p-5 rounded-2xl border {{ $pendingCount > 0 ? 'bg-amber-50 border-amber-300' : 'bg-emerald-50 border-emerald-300' }}">
                @if ($pendingCount > 0)
                    <p class="text-lg font-bold text-amber-900">
                        Ada {{ $pendingCount }} hari kegiatan ({{ $pendingWeeks }} minggu) yang menunggu pemeriksaan Anda.
                    </p>
                    <p class="text-base text-amber-900 mt-0.5">Mulai dari minggu paling atas. Minggu pertama sudah dibuka otomatis.</p>
                @else
                    <p class="text-lg font-bold text-emerald-900">Semua logbook sudah Anda periksa. Tidak ada yang menunggu.</p>
                @endif
            </div>

            <!-- Pilihan tampilan berdasarkan keadaan -->
            <div class="border-b border-slate-200 overflow-x-auto">
                <nav class="flex gap-6 min-w-max" aria-label="Pilih logbook berdasarkan keadaan">
                    @foreach ($statusTabs as $key => [$label, $count, $unit])
                        <a href="{{ route('mentor.logbooks.index', array_merge($filterQuery, ['status' => $key])) }}"
                           @if ($statusFilter === $key) aria-current="page" @endif
                           class="py-3 px-1 border-b-4 text-base inline-flex items-center gap-2 transition min-h-[48px] {{ $statusFilter === $key ? 'border-blue-600 text-blue-800 font-bold' : 'border-transparent text-slate-700 hover:text-slate-900 font-semibold' }}">
                            <span>{{ $label }}</span>
                            <span class="px-2.5 py-0.5 rounded-full text-sm font-bold {{ $statusFilter === $key ? 'bg-blue-100 text-blue-900' : 'bg-slate-100 text-slate-800' }}">{{ $count }} {{ $unit }}</span>
                        </a>
                    @endforeach
                </nav>
            </div>

            <!-- Cari mahasiswa -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200">
                <form method="GET" action="{{ route('mentor.logbooks.index') }}" class="grid grid-cols-1 lg:grid-cols-3 gap-3 items-end">
                    <input type="hidden" name="status" value="{{ $statusFilter }}">
                    <div>
                        <label for="pilih-mahasiswa" class="block text-base font-semibold text-slate-900 mb-1">Mahasiswa</label>
                        <select id="pilih-mahasiswa" name="placement_id" onchange="this.form.submit()" class="w-full min-h-[48px] text-base border-slate-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 py-2">
                            <option value="">Semua Mahasiswa</option>
                            @foreach ($supervisedPlacements as $pl)
                                @php
                                    $st = $pl->application?->user;
                                    $nim = $st?->studentProfile?->nim ?? '-';
                                @endphp
                                <option value="{{ $pl->id }}" {{ request('placement_id') == $pl->id ? 'selected' : '' }}>
                                    {{ $st?->name ?? 'Mahasiswa' }} ({{ $nim }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="lg:col-span-2">
                        <label for="cari-logbook" class="block text-base font-semibold text-slate-900 mb-1">Cari nama, NIM, atau isi kegiatan</label>
                        <div class="flex items-center gap-2">
                            <input id="cari-logbook" type="text" name="search" value="{{ request('search') }}" placeholder="Contoh: Budi atau katalog"
                                   class="w-full min-h-[48px] text-base border-slate-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 py-2">
                            <button type="submit" class="min-h-[48px] inline-flex items-center justify-center px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white text-base font-bold rounded-xl transition shrink-0 cursor-pointer">
                                Cari
                            </button>
                            @if (request()->filled('search') || request()->filled('placement_id'))
                                <a href="{{ route('mentor.logbooks.index') }}"
                                   class="min-h-[48px] inline-flex items-center justify-center px-5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-800 text-base font-bold rounded-xl transition shrink-0">
                                    Reset
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>

            <!-- Daftar Paket Mingguan (Komponen Bersama) -->
            <x-supervision.logbook-weekly
                :bundles="$weeklyBundles"
                :reviewRoute="route('mentor.logbooks.bulk_review')"
                role="mentor"
                emptyMessage="Tidak ada logbook mingguan yang sesuai dengan pilihan ini."
            />

        </div>
    </div>
</x-app-layout>
