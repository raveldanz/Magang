<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl sm:text-2xl text-slate-800 leading-tight">
            {{ __('Pengajuan Magang') }}
        </h2>
    </x-slot>

    <div class="py-8 bg-[#F5F8FC] min-h-screen text-slate-900 font-sans">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Success Message -->
            @if (session('success'))
                <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl flex items-center gap-3 text-sm font-semibold shadow-sm">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <!-- Error Validasi Input -->
            @if ($errors->any())
                <div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-2xl text-xs space-y-1 shadow-sm">
                    <p class="font-bold">Pengajuan Gagal Dikirim:</p>
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Alert Penolakan dari Admin -->
            @if ($applicationHistory->first()?->statusValue() === 'rejected')
                <div class="p-5 bg-red-50 border border-red-200 rounded-2xl shadow-sm space-y-2">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <h3 class="text-sm font-bold text-red-900">Mohon Maaf, Pengajuan Magang Anda Ditolak</h3>
                    </div>
                    <div class="text-xs text-red-800">
                        <p class="font-semibold">Catatan / Alasan dari Admin:</p>
                        <p class="mt-1 italic bg-white p-3 rounded-xl border border-red-200 font-mono text-slate-800">
                            "{{ $applicationHistory->first()->rejection_note ?? 'Tidak ada catatan spesifik dari admin.' }}"
                        </p>
                    </div>
                    <p class="text-[11px] text-red-600">
                        Silakan buat pengajuan baru di bawah dengan melengkapi/memperbaiki berkas sesuai catatan admin di atas.
                    </p>
                </div>
            @endif

            <!-- Alert Pengunduran Diri -->
            @if ($applicationHistory->first()?->statusValue() === 'resigned')
                <div class="p-5 bg-slate-50 border border-slate-200 rounded-2xl shadow-sm space-y-2">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-slate-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h3 class="text-sm font-bold text-slate-800">
                            Anda Telah Mengundurkan Diri dari Program Magang Sebelumnya
                        </h3>
                    </div>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Anda dapat mendaftar kembali ke program magang dengan memilih instansi & divisi kerja yang tersedia di bawah serta mengirimkan berkas pengajuan baru.
                    </p>
                </div>
            @endif

            <!-- 1. FORM PENGAJUAN BARU / STATUS BANNER -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm shadow-slate-200/50 p-6 sm:p-8 space-y-6">

                @if ($activeApplication)
                    @php $actSt = $activeApplication->statusValue(); @endphp
                    <div class="p-4 sm:p-5 rounded-2xl border {{ in_array($actSt, ['accepted', 'active'], true) ? 'bg-emerald-50 border-emerald-200 text-emerald-950' : ($actSt === 'completed' ? 'bg-indigo-50 border-indigo-200 text-indigo-950' : 'bg-amber-50 border-amber-200 text-amber-950') }} shadow-xs space-y-2">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
                            <div class="flex items-center gap-2 font-bold text-sm">
                                <svg class="w-5 h-5 shrink-0 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>
                                    @if(in_array($actSt, ['pending', 'waiting_approval']))
                                        Pengajuan Magang Sedang Diproses
                                    @elseif($actSt === 'accepted')
                                        Pengajuan Magang Anda Telah Diterima
                                    @elseif($actSt === 'active')
                                        Anda Sedang Menjalani Magang
                                    @elseif($actSt === 'completed')
                                        Program Magang MBKM Telah Selesai
                                    @endif
                                </span>
                            </div>
                            <div>
                                <x-status-badge :status="$activeApplication->status" />
                            </div>
                        </div>
                        <p class="text-xs leading-relaxed">
                            @if(in_array($actSt, ['pending', 'waiting_approval']))
                                Berkas pengajuan magang Anda di <strong>{{ $activeApplication->unit->agencyProfile->agency_name ?? 'Pemerintah Kota Surabaya' }}</strong> (Divisi: {{ $activeApplication->unit->name ?? '-' }}) saat ini sedang dalam tahap seleksi & verifikasi oleh Tim Admin Dinas. Anda belum dapat mengajukan magang baru sampai proses ini selesai.
                            @elseif(in_array($actSt, ['accepted', 'active']))
                                Selamat! Anda telah resmi diterima magang di <strong>{{ $activeApplication->unit->agencyProfile->agency_name ?? 'Pemerintah Kota Surabaya' }}</strong> (Divisi: {{ $activeApplication->unit->name ?? '-' }}). Silakan fokus pada pelaksanaan kegiatan magang harian Anda.
                            @elseif($actSt === 'completed')
                                Anda telah menyelesaikan seluruh rangkaian kegiatan magang MBKM serta menerima penilaian akhir resmi. Anda dapat mengunduh E-Sertifikat dan arsip laporan melalui menu <strong>Laporan Akhir</strong>.
                            @endif
                        </p>

                        @if($activeApplication->group_id && $activeApplication->group)
                            <div class="mt-3 p-3 bg-white/80 border border-indigo-200/80 rounded-xl flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 rounded-md bg-indigo-600 text-white font-bold text-[10px] uppercase tracking-wider">Kelompok Magang</span>
                                    <span class="font-bold text-slate-800">{{ $activeApplication->group->name }}</span>
                                    <span class="text-slate-500 font-medium">({{ $activeApplication->isLeader() ? 'Ketua Kelompok' : 'Anggota Tim' }})</span>
                                </div>
                                <span class="text-indigo-700 font-bold font-mono text-[11px] bg-indigo-50 px-2 py-0.5 rounded border border-indigo-200 self-start sm:self-auto">
                                    {{ $activeApplication->group->applications->count() }} Anggota Terdaftar
                                </span>
                            </div>
                        @endif
                    </div>
                @elseif ($draftGroup)
                    @php
                        $isLeader = $draftGroup->leader_user_id === Auth::id();
                        $memberCount = $draftGroup->applications->count();
                        $remQuota = $draftGroup->unit?->remaining_quota ?? 0;
                    @endphp
                    <div class="space-y-6">
                        <!-- Group Header Hero -->
                        <div class="p-5 sm:p-6 rounded-2xl bg-gradient-to-r from-indigo-50/90 via-blue-50/50 to-white border border-indigo-200/80 shadow-xs space-y-4">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-amber-100 text-amber-800 border border-amber-200">
                                            DRAFT KELOMPOK
                                        </span>
                                        <span class="text-xs text-slate-500 font-medium">&bull; Menunggu Anggota & Berkas</span>
                                    </div>
                                    <h3 class="text-base sm:text-xl font-extrabold text-slate-900 mt-1">
                                        {{ $draftGroup->name ?: 'Kelompok Magang' }}
                                    </h3>
                                    <p class="text-xs text-slate-500 mt-0.5">
                                        {{ $draftGroup->unit->agencyProfile->agency_name ?? 'Dinas Perpustakaan dan Kearsipan Kota Surabaya' }} &bull; Bidang: <strong class="text-slate-800">{{ $draftGroup->unit->name ?? '-' }}</strong>
                                    </p>
                                </div>

                                <!-- Box Kode Gabung -->
                                <div class="p-3 bg-white rounded-xl border border-indigo-200 shadow-2xs flex items-center justify-between sm:justify-start gap-3">
                                    <div>
                                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Kode Gabung Tim</div>
                                        <div class="text-sm sm:text-base font-black font-mono text-indigo-700 tracking-wider">
                                            {{ $draftGroup->code }}
                                        </div>
                                    </div>
                                    <button type="button" 
                                            onclick="navigator.clipboard.writeText('{{ $draftGroup->code }}'); alert('Kode kelompok berhasil disalin: {{ $draftGroup->code }}');"
                                            class="px-2.5 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-lg text-xs font-bold transition flex items-center gap-1 cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" /></svg>
                                        <span>Salin</span>
                                    </button>
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center gap-4 pt-3 border-t border-indigo-100 text-xs text-slate-600">
                                <div>
                                    <span class="text-slate-400">Periode:</span>
                                    <strong class="text-slate-700 ml-1">{{ \Carbon\Carbon::parse($draftGroup->start_date)->translatedFormat('d M Y') }} – {{ \Carbon\Carbon::parse($draftGroup->end_date)->translatedFormat('d M Y') }}</strong>
                                </div>
                                <div>
                                    <span class="text-slate-400">Ketua:</span>
                                    <strong class="text-slate-700 ml-1">{{ $draftGroup->leader->name }}</strong>
                                </div>
                                <div>
                                    <span class="text-slate-400">Kapasitas Sisa Kuota Bidang:</span>
                                    <strong class="text-slate-700 ml-1">{{ $remQuota }} orang</strong>
                                </div>
                            </div>
                        </div>

                        <!-- Daftar Anggota Tim -->
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <h4 class="text-xs sm:text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                                    <span>Anggota Kelompok ({{ $memberCount }} / 5 Mahasiswa)</span>
                                </h4>
                                @if($memberCount < 2)
                                    <span class="text-[11px] font-semibold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-md border border-amber-200">
                                        Perlu Minimal 2 Anggota
                                    </span>
                                @endif
                            </div>

                            <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
                                <table class="w-full text-left text-xs">
                                    <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-200">
                                        <tr>
                                            <th class="py-3 px-4 w-12 text-center">No</th>
                                            <th class="py-3 px-4">Nama Mahasiswa</th>
                                            <th class="py-3 px-4">NIM</th>
                                            <th class="py-3 px-4">Program Studi</th>
                                            <th class="py-3 px-4 text-center">Peran</th>
                                            <th class="py-3 px-4 text-right">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @foreach($draftGroup->applications as $idx => $grpApp)
                                            <tr class="hover:bg-slate-50/60 transition">
                                                <td class="py-3.5 px-4 text-center font-bold text-slate-400">{{ $idx + 1 }}</td>
                                                <td class="py-3.5 px-4 font-bold text-slate-900">
                                                    {{ $grpApp->user?->name }}
                                                    @if($grpApp->user_id === Auth::id())
                                                        <span class="ml-1 text-[10px] text-blue-600 font-semibold">(Anda)</span>
                                                    @endif
                                                </td>
                                                <td class="py-3.5 px-4 font-mono text-slate-600">{{ $grpApp->user?->studentProfile?->nim ?? '-' }}</td>
                                                <td class="py-3.5 px-4 text-slate-600">{{ $grpApp->user?->studentProfile?->jurusan ?? '-' }}</td>
                                                <td class="py-3.5 px-4 text-center">
                                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $grpApp->role_in_group === 'leader' ? 'bg-blue-100 text-blue-800 border border-blue-200' : 'bg-slate-100 text-slate-700' }}">
                                                        {{ $grpApp->role_in_group === 'leader' ? 'Ketua' : 'Anggota' }}
                                                    </span>
                                                </td>
                                                <td class="py-3.5 px-4 text-right">
                                                    @if($isLeader && $grpApp->role_in_group === 'member')
                                                        <form action="{{ route('student.group.remove_member') }}" method="POST" onsubmit="return confirm('Keluarkan anggota ini dari kelompok?');" class="inline">
                                                            @csrf
                                                            <input type="hidden" name="application_id" value="{{ $grpApp->id }}">
                                                            <button type="submit" class="px-2.5 py-1 text-red-600 hover:text-red-800 hover:bg-red-50 rounded-lg font-bold text-[11px] transition">
                                                                Keluarkan
                                                            </button>
                                                        </form>
                                                    @elseif(! $isLeader && $grpApp->user_id === Auth::id())
                                                        <form action="{{ route('student.group.leave') }}" method="POST" onsubmit="return confirm('Anda yakin ingin keluar dari kelompok ini?');" class="inline">
                                                            @csrf
                                                            <button type="submit" class="px-2.5 py-1 text-red-600 hover:text-red-800 hover:bg-red-50 rounded-lg font-bold text-[11px] transition">
                                                                Keluar Kelompok
                                                            </button>
                                                        </form>
                                                    @else
                                                        <span class="text-slate-300">-</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Action Area: Submit or Info -->
                        @if($isLeader)
                            <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200 space-y-4">
                                <h4 class="text-xs sm:text-sm font-bold text-slate-900 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    <span>Kirim Berkas Pengajuan Kelompok ke Admin Dinas</span>
                                </h4>

                                @if($memberCount >= 2)
                                    <form action="{{ route('student.group.submit') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                                        @csrf
                                        <div class="space-y-1.5">
                                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                                Surat Pengantar / Proposal Kelompok dari Universitas <span class="text-red-500">*</span>
                                            </label>
                                            <input type="file" name="surat_pengantar" accept=".pdf" required 
                                                   class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3.5 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer transition bg-white border border-slate-200 rounded-xl p-1.5" />
                                            <p class="text-[11px] text-slate-400">Format: Dokumen PDF resmi bertandatangan Dekan/Kaprodi memuat seluruh nama anggota (Maks. 2MB).</p>
                                        </div>

                                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 pt-2">
                                            <button type="submit" 
                                                    class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-xs transition flex items-center justify-center gap-2 cursor-pointer">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" /></svg>
                                                <span>Kirim Pengajuan Kelompok Sekarang</span>
                                            </button>
                                            
                                            <button type="button" 
                                                    onclick="if(confirm('Batalkan dan hapus kelompok ini beserta anggotanya?')) document.getElementById('cancel-group-form').submit();"
                                                    class="px-4 py-2 text-red-600 hover:text-red-800 hover:bg-red-50 text-xs font-semibold rounded-xl transition text-center cursor-pointer">
                                                Batalkan Kelompok
                                            </button>
                                        </div>
                                    </form>

                                    <form id="cancel-group-form" action="{{ route('student.group.cancel') }}" method="POST" class="hidden">
                                        @csrf
                                    </form>
                                @else
                                    <div class="p-3.5 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-900 space-y-1">
                                        <p class="font-bold">Menunggu Anggota Tambahan:</p>
                                        <p>Kelompok magang minimal memiliki 2 anggota (Ketua + 1 Anggota). Berikan Kode Gabung <strong class="font-mono text-indigo-700 bg-white px-2 py-0.5 rounded border border-amber-200">{{ $draftGroup->code }}</strong> kepada rekan Anda agar mereka dapat bergabung.</p>
                                    </div>
                                    <div class="pt-2">
                                        <form action="{{ route('student.group.cancel') }}" method="POST" onsubmit="return confirm('Batalkan dan hapus kelompok ini?');">
                                            @csrf
                                            <button type="submit" class="px-4 py-2 text-red-600 hover:text-red-800 text-xs font-semibold rounded-xl transition cursor-pointer">
                                                Batalkan Kelompok
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="p-4 rounded-xl bg-blue-50 border border-blue-200 text-xs text-blue-900 flex items-center gap-3">
                                <svg class="w-5 h-5 text-blue-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <div>
                                    <p class="font-bold">Anda Terdaftar Sebagai Anggota</p>
                                    <p class="text-blue-800 mt-0.5">Mohon tunggu Ketua Kelompok (<strong>{{ $draftGroup->leader->name }}</strong>) mengunggah Surat Pengantar Kampus dan mengirimkan berkas pengajuan ini ke Admin Dinas.</p>
                                </div>
                            </div>
                        @endif
                    </div>
                @else
                    <div x-data="{ 
                        regMode: '{{ old('group_code') || old('name') || request('mode') === 'group' ? 'group' : (old('unit_id') || request('mode') === 'individual' ? 'individual' : '') }}' 
                    }" class="space-y-6">

                        <!-- Tampilan Awal: Pilihan 2 Card Besar Moda Pendaftaran -->
                        <div x-show="!regMode" x-cloak class="space-y-6">
                            <div class="text-center max-w-2xl mx-auto space-y-2 pt-2 pb-2">
                                <span class="inline-flex items-center px-3.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-blue-50 text-blue-700 border border-blue-200">
                                    Tahap Awal Pendaftaran
                                </span>
                                <h3 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                                    Pilih Jalur Pendaftaran Magang
                                </h3>
                                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                                    Tentukan metode pendaftaran magang yang akan diajukan. Silakan pelajari ketentuan masing-masing jalur di bawah ini sebelum melanjutkan ke pengisian formulir.
                                </p>
                            </div>

                            <!-- Grid 2 Card Besar -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
                                <!-- Card 1: Pendaftaran Individu -->
                                <div @click="regMode = 'individual'" 
                                     class="group relative rounded-3xl p-6 sm:p-8 bg-white hover:bg-blue-50/20 border-2 border-slate-200 hover:border-blue-500 shadow-sm hover:shadow-xl transition-all duration-200 cursor-pointer flex flex-col justify-between space-y-6">
                                    <div class="space-y-5">
                                        <div class="flex items-start justify-between gap-4">
                                            <div class="w-14 h-14 rounded-2xl bg-blue-100 text-blue-700 group-hover:bg-blue-600 group-hover:text-white flex items-center justify-center transition-all duration-200 shadow-xs shrink-0">
                                                <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                            </div>
                                            <span class="px-3.5 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider bg-blue-50 text-blue-700 border border-blue-200">
                                                Jalur Mandiri
                                            </span>
                                        </div>

                                        <div>
                                            <h4 class="text-lg sm:text-xl font-black text-slate-900 group-hover:text-blue-700 transition-colors">
                                                Pendaftaran Individu
                                            </h4>
                                            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                                                Pengajuan magang perorangan bagi mahasiswa yang mendaftar secara mandiri.
                                            </p>
                                        </div>

                                        <!-- Rincian Ketentuan Jalur Individu -->
                                        <div class="space-y-3 pt-4 border-t border-slate-100">
                                            <div class="flex items-start gap-2.5 text-xs text-slate-700">
                                                <div class="w-4 h-4 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center shrink-0 mt-0.5">
                                                    <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                                                </div>
                                                <span>Pendaftaran diajukan untuk satu orang mahasiswa tanpa ikatan tim.</span>
                                            </div>
                                            <div class="flex items-start gap-2.5 text-xs text-slate-700">
                                                <div class="w-4 h-4 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center shrink-0 mt-0.5">
                                                    <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                                                </div>
                                                <span>Surat pengantar perguruan tinggi ditujukan khusus atas nama diri sendiri.</span>
                                            </div>
                                            <div class="flex items-start gap-2.5 text-xs text-slate-700">
                                                <div class="w-4 h-4 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center shrink-0 mt-0.5">
                                                    <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                                                </div>
                                                <span>Surat penerimaan resmi diterbitkan perorangan langsung untuk Anda.</span>
                                            </div>
                                            <div class="flex items-start gap-2.5 text-xs text-slate-700">
                                                <div class="w-4 h-4 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center shrink-0 mt-0.5">
                                                    <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                                                </div>
                                                <span>Aktivitas harian logbook, evaluasi pembimbing, serta sertifikat dikelola secara mandiri.</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="pt-4 border-t border-slate-100">
                                        <button type="button" 
                                                class="w-full py-3.5 px-4 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white rounded-2xl text-xs font-bold uppercase tracking-wider transition-all duration-150 flex items-center justify-center gap-2 shadow-sm hover:shadow-md cursor-pointer">
                                            <span>Pilih Pendaftaran Individu</span>
                                            <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <!-- Card 2: Pendaftaran Kelompok -->
                                <div @click="regMode = 'group'" 
                                     class="group relative rounded-3xl p-6 sm:p-8 bg-white hover:bg-indigo-50/20 border-2 border-slate-200 hover:border-indigo-500 shadow-sm hover:shadow-xl transition-all duration-200 cursor-pointer flex flex-col justify-between space-y-6">
                                    <div class="space-y-5">
                                        <div class="flex items-start justify-between gap-4">
                                            <div class="w-14 h-14 rounded-2xl bg-indigo-100 text-indigo-700 group-hover:bg-indigo-600 group-hover:text-white flex items-center justify-center transition-all duration-200 shadow-xs shrink-0">
                                                <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                                </svg>
                                            </div>
                                            <span class="px-3.5 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider bg-indigo-50 text-indigo-700 border border-indigo-200">
                                                Jalur Tim Kampus
                                            </span>
                                        </div>

                                        <div>
                                            <h4 class="text-lg sm:text-xl font-black text-slate-900 group-hover:text-indigo-700 transition-colors">
                                                Pendaftaran Kelompok
                                            </h4>
                                            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                                                Pengajuan magang bersama rekan satu perguruan tinggi pada satu unit kerja dinas.
                                            </p>
                                        </div>

                                        <!-- Rincian Ketentuan Jalur Kelompok -->
                                        <div class="space-y-3 pt-4 border-t border-slate-100">
                                            <div class="flex items-start gap-2.5 text-xs text-slate-700">
                                                <div class="w-4 h-4 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center shrink-0 mt-0.5">
                                                    <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                                                </div>
                                                <span>Pendaftaran bersama mahasiswa lain yang berasal dari satu perguruan tinggi yang sama.</span>
                                            </div>
                                            <div class="flex items-start gap-2.5 text-xs text-slate-700">
                                                <div class="w-4 h-4 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center shrink-0 mt-0.5">
                                                    <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                                                </div>
                                                <span>Ketua kelompok membuat tim dan mengunggah surat pengantar resmi yang mencantumkan nama anggota.</span>
                                            </div>
                                            <div class="flex items-start gap-2.5 text-xs text-slate-700">
                                                <div class="w-4 h-4 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center shrink-0 mt-0.5">
                                                    <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                                                </div>
                                                <span>Anggota tim tidak perlu membuat kelompok baru, cukup meminta kode undangan dari ketua lalu bergabung.</span>
                                            </div>
                                            <div class="flex items-start gap-2.5 text-xs text-slate-700">
                                                <div class="w-4 h-4 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center shrink-0 mt-0.5">
                                                    <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                                                </div>
                                                <span>Diterbitkan satu surat penerimaan resmi bersama yang memuat tabel seluruh anggota tim.</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="pt-4 border-t border-slate-100">
                                        <button type="button" 
                                                class="w-full py-3.5 px-4 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white rounded-2xl text-xs font-bold uppercase tracking-wider transition-all duration-150 flex items-center justify-center gap-2 shadow-sm hover:shadow-md cursor-pointer">
                                            <span>Pilih Pendaftaran Kelompok</span>
                                            <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Moda 1: Pendaftaran Individu -->
                        <div x-show="regMode === 'individual'" x-cloak class="space-y-6">
                            <!-- Navigasi Kembali ke Pilihan Jalur -->
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <button type="button" 
                                                @click="regMode = ''" 
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition cursor-pointer">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                                            <span>Ganti Jalur Pendaftaran</span>
                                        </button>
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-blue-50 text-blue-700 border border-blue-200">
                                            Pendaftaran Individu
                                        </span>
                                    </div>
                                    <h3 class="text-base sm:text-lg font-bold text-slate-900 mt-2">Formulir Pengajuan Magang Mandiri</h3>
                                    <p class="text-xs text-slate-500">Lengkapi pilihan unit kerja, tanggal periode magang, dan dokumen pengantar dari kampus Anda.</p>
                                </div>
                            </div>

                            <form action="{{ route('student.application.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                                @csrf

                        <!-- Bagian A: Pilihan Instansi & Unit Kerja / Divisi Magang Dispusip -->
                        @php
                            $selectedUnitId = old('unit_id', '');
                            $selectedUnit = $units->firstWhere('id', (int) $selectedUnitId);
                            $defaultAgency = $agencies->first();
                            $selectedAgencyId = old('agency_profile_id', $selectedUnit?->agency_profile_id ?? ($defaultAgency?->id ?? ''));

                            $agencyItems = $agencies->map(function ($a) {
                                return [
                                    'id' => $a->id,
                                    'name' => $a->agency_name,
                                    'acronym' => $a->acronym,
                                    'meta' => $a->address ?? 'Dinas Perpustakaan dan Kearsipan Kota Surabaya',
                                ];
                            })->values()->all();

                            $unitItems = $units->map(function ($u) {
                                $agencyName = $u->agencyProfile->agency_name ?? 'Dinas Perpustakaan dan Kearsipan';
                                $rem = $u->remaining_quota;
                                return [
                                    'id' => $u->id,
                                    'agency_profile_id' => $u->agency_profile_id,
                                    'name' => $u->name,
                                    'acronym' => $u->agencyProfile?->acronym,
                                    'meta' => ($rem > 0 ? "Sisa Kuota: {$rem} orang" : "Kuota Penuh") . " • {$agencyName}",
                                    'remaining_quota' => $rem,
                                    'disabled' => $rem <= 0,
                                ];
                            })->values()->all();
                        @endphp

                        <!-- 1. Instansi Penempatan Tunggal Resmi: Dinas Perpustakaan dan Kearsipan Kota Surabaya -->
                        <input type="hidden" name="agency_profile_id" value="{{ $selectedAgencyId }}">
                        
                        <div class="p-4 sm:p-5 rounded-2xl bg-gradient-to-r from-blue-50/80 via-indigo-50/50 to-white border border-blue-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="flex items-center gap-3.5 min-w-0">
                                <div class="w-12 h-12 rounded-xl bg-white border border-blue-100 p-1.5 flex items-center justify-center shrink-0 shadow-2xs">
                                    <img src="{{ asset('images/logos/dispusip.png') }}" alt="Dispusip Surabaya" class="max-h-full max-w-full object-contain">
                                </div>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-blue-600 text-white uppercase tracking-wider">
                                            Instansi Tunggal Resmi
                                        </span>
                                        <span class="text-xs text-slate-500 font-medium hidden sm:inline">&bull; Pemkot Surabaya</span>
                                    </div>
                                    <h4 class="text-sm sm:text-base font-extrabold text-slate-900 tracking-tight mt-0.5 truncate">
                                        Dinas Perpustakaan dan Kearsipan Kota Surabaya
                                    </h4>
                                    <p class="text-[11px] text-slate-500 truncate mt-0.5">
                                        Jl. Rungkut Asri Tengah No. 5-7, Rungkut Kidul, Surabaya 60293
                                    </p>
                                </div>
                            </div>
                            <div class="shrink-0 flex items-center gap-1.5 self-start sm:self-auto px-3 py-1.5 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs font-bold">
                                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span>Mitra Resmi Terpilih</span>
                            </div>
                        </div>

                        <!-- 2. Pemilihan Bidang Resmi Dispusip -->
                        <div>
                            <x-searchable-select
                                name="unit_id"
                                :items="$unitItems"
                                :selected="$selectedUnitId"
                                label="Pilihan Bidang / Divisi Resmi Dispusip Surabaya"
                                placeholder="-- Cari atau Pilih Bidang Resmi Dispusip Surabaya --"
                                :required="true"
                                :show-asterisk="true"
                            />
                            @error('unit_id')
                                <p class="text-xs text-red-600 font-semibold mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-[11px] text-slate-500 mt-1.5 flex items-center gap-1 font-medium">
                                <svg class="w-3.5 h-3.5 text-blue-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Tersedia 4 Bidang resmi: Sekretariat, Pengelolaan Perpustakaan, Layanan Kearsipan, dan Perlindungan/Preservasi Arsip.</span>
                            </p>
                        </div>

                        <!-- Bagian B: Periode Magang -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6" 
                             x-data="{ 
                                 startDate: '{{ old('start_date', '') }}',
                                 endDate: '{{ old('end_date', '') }}',
                                 today: '{{ date('Y-m-d') }}'
                             }">
                            
                            <!-- Tanggal Mulai -->
                            <div class="space-y-1.5">
                                <x-input-label for="start_date" value="Tanggal Mulai Magang" class="text-xs font-bold uppercase tracking-wider text-slate-700" />
                                <x-text-input id="start_date" 
                                              name="start_date" 
                                              type="date" 
                                              x-model="startDate"
                                              class="block w-full text-xs sm:text-sm rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-400/40 focus:border-blue-500 p-2.5"
                                              :value="old('start_date')"
                                              required />
                                <x-input-error :messages="$errors->get('start_date')" class="mt-1" />
                            </div>

                            <!-- Tanggal Selesai -->
                            <div class="space-y-1.5">
                                <x-input-label for="end_date" value="Tanggal Selesai Magang" class="text-xs font-bold uppercase tracking-wider text-slate-700" />
                                <x-text-input id="end_date" 
                                              name="end_date" 
                                              type="date" 
                                              x-bind:min="startDate"
                                              x-model="endDate"
                                              class="block w-full text-xs sm:text-sm rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-400/40 focus:border-blue-500 p-2.5"
                                              :value="old('end_date')"
                                              required />
                                <x-input-error :messages="$errors->get('end_date')" class="mt-1" />
                            </div>
                        </div>

                        <hr class="border-slate-100">

                        <!-- Upload Dokumen Persyaratan Magang -->
                        <div class="space-y-5">
                            <div>
                                <h3 class="text-sm font-bold text-slate-900">Dokumen Persyaratan Magang</h3>
                                <p class="text-xs text-slate-500 mt-0.5">Unggah Surat Pengantar resmi dari perguruan tinggi Anda. Dokumen personal CV, Transkrip, dan KTM Anda otomatis terhubung dari profil.</p>
                            </div>

                            <!-- 1. Surat Pengantar / Proposal Kampus -->
                            <div x-data="{ 
                                    fileName: '', 
                                    fileUrl: null, 
                                    handleFile(e) {
                                        const file = e.target.files[0];
                                        if (file) {
                                            this.fileName = file.name;
                                            this.fileUrl = URL.createObjectURL(file);
                                        } else {
                                            this.fileName = '';
                                            this.fileUrl = null;
                                        }
                                    } 
                                 }" 
                                 class="p-5 rounded-2xl border-2 border-blue-200/80 bg-blue-50/20 space-y-3">
                                <div class="flex items-center justify-between">
                                    <label for="surat_pengantar" class="block text-xs font-bold text-slate-800 uppercase tracking-wider">
                                        Surat Pengantar dari Perguruan Tinggi <span class="text-red-500">*</span>
                                    </label>
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-blue-100 text-blue-800">
                                        Wajib Diunggah
                                    </span>
                                </div>
                                
                                <div class="flex items-center gap-2">
                                    <input type="file" 
                                           id="surat_pengantar" 
                                           name="surat_pengantar" 
                                           accept=".pdf" 
                                           required 
                                           @change="handleFile($event)" 
                                           class="block w-full text-xs text-slate-500 file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-600 file:text-white hover:file:bg-blue-700 cursor-pointer transition shadow-xs" />
                                </div>

                                <div x-show="fileUrl" x-cloak class="flex items-center justify-between pt-2 border-t border-blue-100 text-xs">
                                    <a :href="fileUrl" target="_blank" class="inline-flex items-center gap-1 font-bold text-blue-700 hover:text-blue-900 hover:underline">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        Lihat Berkas Terpilih
                                    </a>
                                </div>
                                <p x-show="!fileUrl" class="text-[11px] text-slate-500">Format: Dokumen PDF, Ukuran Maksimal 2MB</p>
                                <x-input-error :messages="$errors->get('surat_pengantar')" class="mt-1" />
                            </div>

                            <!-- Panel Dokumen Personal Otomatis dari Profil -->
                            @php
                                $studentProfile = Auth::user()->studentProfile;
                                $hasCv = !empty($studentProfile?->cv_path);
                                $hasTranscript = !empty($studentProfile?->transcript_path);
                                $hasIdCard = !empty($studentProfile?->id_card_path);
                                $allComplete = $hasCv && $hasTranscript && $hasIdCard;
                            @endphp

                            <div class="p-5 rounded-2xl border border-slate-200 bg-slate-50/70 space-y-3">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full {{ $allComplete ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }} flex items-center justify-center font-bold text-xs">
                                            @if($allComplete)
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                            @else
                                                !
                                            @endif
                                        </div>
                                        <h4 class="text-xs sm:text-sm font-bold text-slate-900">
                                            Dokumen Personal Terhubung Otomatis dari Profil
                                        </h4>
                                    </div>

                                    <a href="{{ route('student.profile.edit') }}" class="text-xs font-bold text-blue-600 hover:text-blue-800 hover:underline inline-flex items-center gap-1 self-start sm:self-auto">
                                        <span>Perbarui di Profil Saya</span>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    </a>
                                </div>

                                <p class="text-[11px] text-slate-500">
                                    Anda tidak perlu mengunggah ulang berkas identitas pribadi. Dokumen di bawah ini ditarik langsung dari profil mahasiswa Anda:
                                </p>

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-1">
                                    <!-- CV Status -->
                                    <div class="p-3 rounded-xl bg-white border border-slate-200/80 flex items-center justify-between">
                                        <div class="min-w-0">
                                            <span class="block text-[11px] font-bold text-slate-800 truncate">Curriculum Vitae</span>
                                            <span class="text-[10px] {{ $hasCv ? 'text-emerald-600 font-semibold' : 'text-rose-600 font-semibold' }}">
                                                {{ $hasCv ? 'Tersedia di Profil' : 'Belum Diunggah' }}
                                            </span>
                                        </div>
                                        @if($hasCv)
                                            <a href="{{ route('student.document', ['userId' => Auth::id(), 'type' => 'cv']) }}" target="_blank" class="p-1.5 rounded-lg text-blue-600 hover:bg-blue-50" title="Lihat Berkas">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            </a>
                                        @endif
                                    </div>

                                    <!-- Transkrip Status -->
                                    <div class="p-3 rounded-xl bg-white border border-slate-200/80 flex items-center justify-between">
                                        <div class="min-w-0">
                                            <span class="block text-[11px] font-bold text-slate-800 truncate">Transkrip Nilai</span>
                                            <span class="text-[10px] {{ $hasTranscript ? 'text-emerald-600 font-semibold' : 'text-rose-600 font-semibold' }}">
                                                {{ $hasTranscript ? 'Tersedia di Profil' : 'Belum Diunggah' }}
                                            </span>
                                        </div>
                                        @if($hasTranscript)
                                            <a href="{{ route('student.document', ['userId' => Auth::id(), 'type' => 'transkrip']) }}" target="_blank" class="p-1.5 rounded-lg text-blue-600 hover:bg-blue-50" title="Lihat Berkas">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            </a>
                                        @endif
                                    </div>

                                    <!-- KTM Status -->
                                    <div class="p-3 rounded-xl bg-white border border-slate-200/80 flex items-center justify-between">
                                        <div class="min-w-0">
                                            <span class="block text-[11px] font-bold text-slate-800 truncate">KTM atau Identitas</span>
                                            <span class="text-[10px] {{ $hasIdCard ? 'text-emerald-600 font-semibold' : 'text-rose-600 font-semibold' }}">
                                                {{ $hasIdCard ? 'Tersedia di Profil' : 'Belum Diunggah' }}
                                            </span>
                                        </div>
                                        @if($hasIdCard)
                                            <a href="{{ route('student.document', ['userId' => Auth::id(), 'type' => 'id_card']) }}" target="_blank" class="p-1.5 rounded-lg text-blue-600 hover:bg-blue-50" title="Lihat Berkas">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            </a>
                                        @endif
                                    </div>
                                </div>

                                @if(!$allComplete)
                                    <div class="p-3 rounded-xl bg-amber-100/70 border border-amber-200 text-amber-900 text-xs flex items-center justify-between gap-2 mt-2">
                                        <span>Berkas personal Anda belum lengkap. Silakan lengkapi pada menu Profil Saya sebelum mengirim pengajuan.</span>
                                        <a href="{{ route('student.profile.edit') }}" class="px-3 py-1 bg-amber-600 hover:bg-amber-700 text-white rounded-lg font-bold text-[11px] shrink-0 transition">
                                            Lengkapi Profil
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center sm:justify-end gap-2.5 sm:gap-3 pt-5 border-t border-slate-100">
                            <a href="{{ route('dashboard') }}" class="w-full sm:w-auto px-5 py-3 sm:py-2.5 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 text-xs font-semibold uppercase tracking-wider rounded-xl transition-all duration-150 text-center flex items-center justify-center">
                                {{ __('Kembali ke dashboard') }}
                            </a>
                            <button type="submit" class="w-full sm:w-auto px-6 py-3 sm:py-2.5 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-xs font-bold uppercase tracking-wider rounded-xl shadow-xs transition-all duration-150 cursor-pointer flex items-center justify-center">
                                {{ __('Kirim Pengajuan Magang') }}
                            </button>
                            </div>
                        </form>
                    </div>

                    <!-- Moda 2: Pendaftaran Kelompok -->
                    <div x-show="regMode === 'group'" x-cloak class="space-y-6">
                        <!-- Navigasi Kembali ke Pilihan Jalur -->
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <button type="button" 
                                            @click="regMode = ''" 
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                                        <span>Ganti Jalur Pendaftaran</span>
                                    </button>
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-indigo-50 text-indigo-700 border border-indigo-200">
                                        Pendaftaran Kelompok
                                    </span>
                                </div>
                                <h3 class="text-base sm:text-lg font-bold text-slate-900 mt-2">Pendaftaran Magang Bersama Tim</h3>
                                <p class="text-xs text-slate-500">Buat kelompok baru sebagai ketua tim atau bergabung ke kelompok rekan kampus menggunakan kode undangan.</p>
                            </div>
                        </div>

                        <!-- Petunjuk Peran Ketua dan Anggota -->
                        <div class="p-4 sm:p-5 rounded-2xl bg-amber-50/90 border border-amber-200 text-amber-950 space-y-3">
                            <div class="flex items-center gap-2">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-amber-200 text-amber-900 border border-amber-300">
                                    Petunjuk Penting
                                </span>
                                <h4 class="text-xs sm:text-sm font-black text-amber-950">
                                    Panduan Peran Pendaftaran Kelompok
                                </h4>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs leading-relaxed">
                                <div class="p-3.5 rounded-xl bg-white/80 border border-amber-200/70 space-y-1">
                                    <div class="font-extrabold text-blue-900 flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                                        <span>Untuk Ketua Kelompok:</span>
                                    </div>
                                    <p class="text-slate-700">
                                        Pilih bagian <strong>Buat Kelompok Baru</strong>. Anda yang menentukan unit dinas, memilih periode magang, mengunggah surat pengantar resmi bersama, dan membagikan kode undangan kepada rekan tim.
                                    </p>
                                </div>
                                <div class="p-3.5 rounded-xl bg-white/80 border border-amber-200/70 space-y-1">
                                    <div class="font-extrabold text-indigo-900 flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full bg-indigo-600"></span>
                                        <span>Untuk Anggota Tim:</span>
                                    </div>
                                    <p class="text-slate-700">
                                        <strong>Jangan membuat kelompok baru.</strong> Mintalah ketua kelompok Anda untuk membuat kelompok terlebih dahulu di bagian sebelah kiri. Setelah ketua selesai membuat kelompok, minta <strong>Kode Undangan Kelompok</strong> dari ketua lalu masukkan ke bagian <strong>Gabung ke Kelompok Rekan</strong>.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <!-- Card A: Buat Kelompok Baru -->
                            <div class="p-6 rounded-2xl bg-gradient-to-b from-blue-50/60 to-white border border-blue-200/80 shadow-xs flex flex-col justify-between space-y-5">
                                <div class="space-y-3">
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center font-bold shadow-xs">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                        </div>
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-blue-100 text-blue-800 border border-blue-200">
                                            Khusus Ketua Tim
                                        </span>
                                    </div>
                                    <h4 class="text-base font-extrabold text-slate-900">Buat Kelompok Baru</h4>
                                    <div class="p-2.5 rounded-xl bg-blue-100/60 border border-blue-200 text-[11px] text-blue-900 font-medium">
                                        Formulir ini khusus untuk Ketua Kelompok yang menginisiasi pendaftaran. Jika Anda adalah anggota, jangan isi formulir ini.
                                    </div>
                                    <p class="text-xs text-slate-600 leading-relaxed">
                                        Sebagai Ketua Tim, Anda akan memilih Bidang Dispusip, menentukan rentang tanggal magang, dan mendapatkan <strong>Kode Undangan</strong> untuk dibagikan ke rekan satu kampus Anda.
                                    </p>

                                    <form action="{{ route('student.group.create') }}" method="POST" class="space-y-4 pt-2">
                                        @csrf
                                        <div>
                                            <x-input-label for="group_name" value="Nama Kelompok - Opsional" class="text-xs font-bold uppercase tracking-wider text-slate-700" />
                                            <x-text-input id="group_name" name="name" type="text" placeholder="Contoh: Tim Riset Kearsipan MBKM" class="block w-full text-xs sm:text-sm rounded-xl border-slate-200 bg-white mt-1 p-2.5" />
                                        </div>

                                        <div>
                                            <label for="group_unit_select" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                                                Pilihan Bidang Dispusip <span class="text-red-500">*</span>
                                            </label>
                                            <select id="group_unit_select" name="unit_id" required class="block w-full text-xs sm:text-sm rounded-xl border-slate-200 bg-white p-2.5 focus:ring-2 focus:ring-blue-400">
                                                <option value="">-- Pilih Bidang Dispusip Surabaya --</option>
                                                @foreach($units as $u)
                                                    @php $rem = $u->remaining_quota; @endphp
                                                    <option value="{{ $u->id }}" {{ $rem <= 0 ? 'disabled' : '' }}>
                                                        {{ $u->name }} - Sisa Kuota: {{ $rem }} orang
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                            <div>
                                                <x-input-label for="group_start_date" value="Tanggal Mulai" class="text-xs font-bold uppercase tracking-wider text-slate-700" />
                                                <x-text-input id="group_start_date" name="start_date" type="date" required class="block w-full text-xs rounded-xl border-slate-200 bg-white mt-1 p-2" />
                                            </div>
                                            <div>
                                                <x-input-label for="group_end_date" value="Tanggal Selesai" class="text-xs font-bold uppercase tracking-wider text-slate-700" />
                                                <x-text-input id="group_end_date" name="end_date" type="date" required class="block w-full text-xs rounded-xl border-slate-200 bg-white mt-1 p-2" />
                                            </div>
                                        </div>

                                        <button type="submit" class="w-full py-2.5 px-4 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold uppercase tracking-wider shadow-xs transition flex items-center justify-center gap-2 cursor-pointer">
                                            <span>Buat Kelompok & Dapatkan Kode</span>
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <!-- Card B: Gabung Kelompok Rekan -->
                            <div class="p-6 rounded-2xl bg-gradient-to-b from-indigo-50/60 to-white border border-indigo-200/80 shadow-xs flex flex-col justify-between space-y-5">
                                <div class="space-y-3">
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-bold shadow-xs">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" /></svg>
                                        </div>
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-indigo-100 text-indigo-800 border border-indigo-200">
                                            Khusus Anggota Tim
                                        </span>
                                    </div>
                                    <h4 class="text-base font-extrabold text-slate-900">Gabung ke Kelompok Rekan</h4>
                                    <div class="p-2.5 rounded-xl bg-indigo-100/60 border border-indigo-200 text-[11px] text-indigo-900 font-medium">
                                        Formulir ini khusus untuk Anggota Tim. Pastikan Ketua Kelompok Anda sudah membuat tim terlebih dahulu agar Anda dapat meminta kode undangannya.
                                    </div>
                                    <p class="text-xs text-slate-600 leading-relaxed">
                                        Sudah ada rekan satu kampus Anda yang membuat kelompok magang? Minta <strong>Kode Undangan Kelompok</strong> dari ketua tim Anda dan masukkan di bawah ini.
                                    </p>

                                    <form action="{{ route('student.group.join') }}" method="POST" class="space-y-4 pt-2">
                                        @csrf
                                        <div>
                                            <x-input-label for="group_code" value="Kode Undangan Kelompok" class="text-xs font-bold uppercase tracking-wider text-slate-700" />
                                            <x-text-input id="group_code" name="group_code" type="text" placeholder="Contoh: GRP-8K2A1P" required 
                                                          class="block w-full text-sm font-mono uppercase tracking-widest text-center font-bold rounded-xl border-slate-200 bg-white mt-1 p-2.5" />
                                            <p class="text-[11px] text-slate-400 mt-1">Kode terdiri dari 6-10 karakter yang diberikan oleh Ketua Kelompok Anda.</p>
                                        </div>

                                        <div class="p-3 bg-amber-50/70 border border-amber-200/70 rounded-xl text-[11px] text-amber-800 space-y-1">
                                            <p class="font-bold">Ketentuan Bergabung:</p>
                                            <p>&bull; Anda harus berasal dari perguruan tinggi yang sama dengan Ketua.</p>
                                            <p>&bull; Anda belum memiliki pengajuan aktif lain yang sedang berjalan.</p>
                                        </div>

                                        <button type="submit" class="w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold uppercase tracking-wider shadow-xs transition flex items-center justify-center gap-2 cursor-pointer">
                                            <span>Gabung ke Kelompok Tim</span>
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013 3v1" /></svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            </div>

            <!-- 2. TABEL RIWAYAT PENGAJUAN MAGANG -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm shadow-slate-200/50 overflow-hidden">
                <div class="p-5 sm:p-6 border-b border-slate-100">
                    <h3 class="text-base font-bold text-slate-900">Riwayat Pengajuan Magang Anda</h3>
                </div>

                <!-- Desktop View (Tabel Pas Presisi Tanpa Scrollbar Horizontal) -->
                <div class="hidden md:block w-full">
                    <table class="w-full table-fixed text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/60 border-b border-slate-100 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">
                                <th class="w-[16%] py-3.5 px-4">Tanggal Pengajuan</th>
                                <th class="w-[28%] py-3.5 px-4">Unit Instansi</th>
                                <th class="w-[22%] py-3.5 px-4">Periode Magang</th>
                                <th class="w-[12%] py-3.5 px-4 text-center">Status</th>
                                <th class="w-[10%] py-3.5 px-4 text-center">Catatan</th>
                                <th class="w-[12%] py-3.5 px-4 text-right">Surat Penerimaan</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm divide-y divide-slate-100">
                            @forelse ($applicationHistory as $app)
                                @php $st = $app->statusValue(); @endphp
                                <tr class="hover:bg-slate-50/70 transition-colors duration-150">
                                    <td class="py-4 px-4 text-slate-500 font-mono text-xs">
                                        {{ $app->created_at->format('d M Y, H:i') }}
                                    </td>
                                    <td class="py-4 px-4 pr-3">
                                        <div class="font-bold text-slate-900 text-xs leading-snug break-words">
                                            {{ $app->unit->name ?? '-' }}
                                        </div>
                                        @if($app->group_id && $app->group)
                                            <div class="mt-0.5">
                                                <span class="inline-block px-1.5 py-0.2 rounded text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                                    Kelompok: {{ $app->group->name }} ({{ $app->role_in_group === 'leader' ? 'Ketua' : 'Anggota' }})
                                                </span>
                                            </div>
                                        @endif
                                        <div class="text-[11px] text-slate-400 mt-0.5 break-words">
                                            {{ $app->unit->agencyProfile->agency_name ?? '-' }}
                                        </div>
                                    </td>
                                    <td class="py-4 px-4 text-xs text-slate-700 leading-relaxed">
                                        {{ \Carbon\Carbon::parse($app->start_date)->translatedFormat('d M Y') }} s/d {{ \Carbon\Carbon::parse($app->end_date)->translatedFormat('d M Y') }}
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        <x-status-badge :status="$app->status" />
                                    </td>
                                    <td class="py-4 px-4 text-center text-xs">
                                        @if ($st === 'rejected')
                                            <span class="text-red-700 font-medium bg-red-50 px-2 py-0.5 rounded-md border border-red-200 inline-block text-[11px] truncate max-w-full" title="{{ $app->rejection_note }}">
                                                {{ $app->rejection_note ?? 'Ditolak' }}
                                            </span>
                                        @else
                                            <span class="text-slate-400">-</span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-4 text-right">
                                        @if (in_array($st, ['accepted', 'active', 'completed']))
                                            <a href="{{ route('student.application.letter', $app->id) }}" target="_blank" 
                                               class="inline-flex items-center gap-1.5 px-2.5 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-100 rounded-xl text-xs font-semibold transition shrink-0">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                                <span>Unduh PDF</span>
                                            </a>
                                        @else
                                            <span class="text-slate-400 text-xs italic">Belum tersedia</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-slate-400 text-xs">
                                        Belum ada riwayat pengajuan magang.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Mobile View (Cards) -->
                <div class="block md:hidden border-t border-slate-100 divide-y divide-slate-100">
                    @forelse ($applicationHistory as $app)
                        @php $st = $app->statusValue(); @endphp
                        <div class="p-5 space-y-3 hover:bg-slate-50/70 transition-colors duration-150">
                            <!-- Header: Tanggal & Status -->
                            <div class="flex items-center justify-between gap-2">
                                <div class="text-xs text-slate-400 font-mono">
                                    {{ $app->created_at->format('d M Y, H:i') }}
                                </div>
                                <x-status-badge :status="$app->status" />
                            </div>

                            <!-- Body: Unit -->
                            <div>
                                <div class="font-bold text-slate-900 text-sm leading-snug">{{ $app->unit->name ?? '-' }}</div>
                                @if($app->group_id && $app->group)
                                    <div class="mt-1">
                                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                            Kelompok: {{ $app->group->name }} ({{ $app->role_in_group === 'leader' ? 'Ketua' : 'Anggota' }})
                                        </span>
                                    </div>
                                @endif
                                <div class="text-xs text-slate-500 mt-0.5">{{ $app->unit->agencyProfile->agency_name ?? '-' }}</div>
                            </div>
                            
                            <!-- Body: Periode Magang -->
                            <div class="text-xs text-slate-600 bg-slate-50/80 px-3 py-2 rounded-xl border border-slate-100 flex items-center justify-between">
                                <span class="text-slate-400 text-[11px] font-semibold uppercase tracking-wider">Periode</span>
                                <span class="font-medium text-slate-700">{{ \Carbon\Carbon::parse($app->start_date)->translatedFormat('d M Y') }} – {{ \Carbon\Carbon::parse($app->end_date)->translatedFormat('d M Y') }}</span>
                            </div>

                            <!-- Footer: Catatan & Surat -->
                            @if ($st === 'rejected')
                                <div class="text-xs">
                                    <span class="font-semibold block text-slate-700 mb-1">Catatan Admin:</span>
                                    <span class="text-red-700 font-medium bg-red-50 px-3 py-2 rounded-xl border border-red-200 block">
                                        {{ $app->rejection_note ?? 'Tidak ada catatan' }}
                                    </span>
                                </div>
                            @endif

                            @if (in_array($st, ['accepted', 'active', 'completed']))
                                <div class="pt-1">
                                    <a href="{{ route('student.application.letter', $app->id) }}" target="_blank" 
                                       class="inline-flex w-full justify-center items-center gap-1.5 px-3 py-2 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-100 rounded-xl text-xs font-semibold transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                        <span>Unduh Surat PDF</span>
                                    </a>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="py-8 text-center text-slate-400 text-xs">
                            Belum ada riwayat pengajuan magang.
                        </div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
</x-app-layout>