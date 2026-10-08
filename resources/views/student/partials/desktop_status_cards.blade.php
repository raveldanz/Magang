                <div class="hidden md:block space-y-6">

                    <!-- STATUS CARD GRID (3 KARTU INFORMASI UTAMA: STATUS, DOSEN, MENTOR - DESKTOP) -->
                    <div class="grid grid-cols-3 gap-6">

                        <!-- Card 1: Status Magang (Desktop) -->
                        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 flex flex-col justify-between">
                            <div>
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Status Magang</p>
                                <div class="mt-2">
                                    @if ($application)
                                        <div class="flex items-center gap-2">
                                            <x-status-badge :status="$application->status" />
                                        </div>
                                        <p class="text-xs text-slate-500 mt-2 font-medium line-clamp-1">
                                            {{ $application->unit->name ?? $application->unit->agencyProfile->agency_name ?? 'Program Magang' }}
                                        </p>
                                    @else
                                        <p class="text-base font-bold text-slate-800">Belum Mengajukan</p>
                                        <p class="text-xs text-slate-400 mt-1">Belum ada pengajuan magang aktif.</p>
                                    @endif
                                </div>
                            </div>
                            <div class="mt-4 pt-2 border-t border-slate-50 flex items-center justify-between">
                                @if ($application && in_array($rawAppSt, ['accepted', 'active', 'completed'], true))
                                    <a href="{{ route('student.application.letter', $application->id) }}" target="_blank"
                                        class="text-xs font-bold text-blue-600 hover:text-blue-800 inline-flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                        <span>Surat Balasan</span>
                                    </a>
                                @elseif (!$application)
                                    <a href="{{ route('student.application.create') }}" class="text-xs font-bold text-blue-600 hover:text-blue-800 inline-flex items-center gap-1">
                                        <span>Buat Pengajuan</span> &rarr;
                                    </a>
                                @elseif ($rawAppSt === 'rejected')
                                    <a href="{{ route('student.application.create') }}" class="text-xs font-bold text-rose-600 hover:text-rose-800 inline-flex items-center gap-1">
                                        <span>Ajukan Magang Baru</span> &rarr;
                                    </a>
                                @elseif ($rawAppSt === 'resigned')
                                    <a href="{{ route('student.application.create') }}" class="text-xs font-bold text-blue-600 hover:text-blue-800 inline-flex items-center gap-1">
                                        <span>Daftar Magang Baru</span> &rarr;
                                    </a>
                                @else
                                    <span class="text-[11px] text-amber-600 font-semibold">Dalam proses verifikasi</span>
                                @endif
                            </div>
                        </div>

                        <!-- Card 2: Dosen Pembimbing (Desktop) -->
                        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between">
                                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Dosen Pembimbing</p>
                                    @if ($academicAdvisor)
                                        <span class="px-2.5 py-0.5 text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full">
                                            Terpilih
                                        </span>
                                    @elseif ($application && in_array($rawAppSt, ['accepted', 'active', 'completed'], true))
                                        <span class="px-2.5 py-0.5 text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200 rounded-full animate-pulse">
                                            Perlu Ditentukan
                                        </span>
                                    @endif
                                </div>
                                <div class="mt-2">
                                    @if ($academicAdvisor)
                                        <p class="text-base font-bold text-slate-800 line-clamp-1" title="{{ $academicAdvisor->name }}">
                                            {{ $academicAdvisor->name }}
                                        </p>
                                        <div class="text-xs text-slate-500 mt-1 space-y-0.5">
                                            <p class="truncate"><span class="text-slate-400">Email:</span> <span class="font-mono text-slate-700">{{ $academicAdvisor->email }}</span></p>
                                            <p class="truncate"><span class="text-slate-400">Kampus:</span> <strong>{{ is_string($academicAdvisor->university) ? $academicAdvisor->university : ($academicAdvisor->universityRelation?->name ?? $academicAdvisor->university?->name ?? $univName ?? $profile->universitas) }}</strong></p>
                                        </div>
                                    @elseif ($application && in_array($rawAppSt, ['accepted', 'active', 'completed'], true))
                                        <p class="text-sm font-semibold text-slate-700">Belum memilih dosen pembimbing</p>
                                        <p class="text-xs text-slate-400 mt-1">Dosen akademik dari perguruan tinggi Anda.</p>
                                    @else
                                        <p class="text-sm font-semibold text-slate-400">Belum Tersedia</p>
                                        <p class="text-xs text-slate-400 mt-1">Tersedia setelah diterima magang.</p>
                                    @endif
                                </div>
                            </div>
                            <div class="mt-4 pt-2 border-t border-slate-50 flex items-center justify-between">
                                @if ($application && in_array($rawAppSt, ['accepted', 'active', 'completed'], true))
                                    <button type="button"
                                        onclick="const el = document.getElementById('change-advisor-box'); el.classList.toggle('hidden'); if(!el.classList.contains('hidden')) el.scrollIntoView({behavior: 'smooth'});"
                                        class="text-xs font-bold text-blue-600 hover:text-blue-800 cursor-pointer">
                                        {{ $academicAdvisor ? 'Ganti Dosen Pembimbing' : 'Pilih Dosen Pembimbing' }} &rarr;
                                    </button>
                                @else
                                    <span class="text-[11px] text-slate-400">-</span>
                                @endif
                            </div>
                        </div>

                        <!-- Card 3: Mentor Lapangan (Desktop) -->
                        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 flex flex-col justify-between">
                            <div>
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Mentor Lapangan</p>
                                <div class="mt-2">
                                    <p class="text-base font-bold text-slate-800 line-clamp-1" title="{{ $mentor ? $mentor->name : 'Belum Diplot Dinas' }}">
                                        {{ $mentor ? $mentor->name : 'Belum Diplot Dinas' }}
                                    </p>
                                    <p class="text-xs text-slate-400 mt-1 leading-relaxed">
                                        Ditugaskan resmi oleh instansi penempatan magang Anda.
                                    </p>
                                </div>
                            </div>
                            <div class="mt-4 pt-2 border-t border-slate-50">
                                @if ($mentor || $placement)
                                    <div class="flex flex-wrap gap-2">
                                        @if ($mentor)
                                            <x-chat-button :user="$mentor" label="Chat Mentor" />
                                        @endif
                                        @if ($placement)
                                            <x-chat-group-button :placement="$placement" />
                                        @endif
                                    </div>
                                @else
                                    <span class="text-[11px] text-slate-400">-</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- FORM PILIH DOSEN PEMBIMBING (DESKTOP - MUNCUL TEPAT DI BAWAH 3 KARTU, DETAIL PENEMPATAN TURUN KE BAWAH) -->
                    @if ($application && in_array($rawAppSt, ['accepted', 'active', 'completed'], true))
                        <div id="change-advisor-box" class="{{ $academicAdvisor ? 'hidden' : '' }} bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-4 scroll-mt-6">
                            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                                <h4 class="font-bold text-slate-800 text-base">Pilih Dosen Pembimbing</h4>
                                @if ($academicAdvisor)
                                    <button type="button"
                                        onclick="document.getElementById('change-advisor-box').classList.add('hidden')"
                                        class="text-xs text-slate-400 hover:text-slate-600 font-bold transition cursor-pointer">
                                        ✕ Tutup
                                    </button>
                                @endif
                            </div>
                            <form action="{{ route('student.select_advisor') }}" method="POST" class="space-y-4">
                                @csrf
                                <div>
                                    <label for="academic_advisor_id"
                                        class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                        Pilih Dosen Terdaftar: {{ $univName ?? $profile->universitas ?? 'Kampus Mahasiswa' }}
                                    </label>
                                    <select id="academic_advisor_id" name="academic_advisor_id" required
                                        class="w-full text-xs sm:text-sm border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs p-2.5">
                                        <option value="">-- Pilih Dosen Pembimbing --</option>
                                        @if(isset($availableDosens))
                                            @foreach ($availableDosens as $dosen)
                                                <option value="{{ $dosen->id }}" {{ optional($placement)->academic_advisor_id == $dosen->id ? 'selected' : '' }}>
                                                    {{ $dosen->name }} —
                                                    {{ is_string($dosen->university) ? $dosen->university : ($dosen->universityRelation?->name ?? $dosen->university?->name ?? 'Dosen') }}
                                                    ({{ $dosen->email }})
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>

                                <!-- Baris Aksi Terpadu -->
                                <div class="pt-1 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                    <div class="flex items-center gap-2">
                                        <button type="submit"
                                            class="w-full sm:w-auto px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs transition cursor-pointer">
                                            {{ __('Simpan Dosen Pembimbing') }}
                                        </button>
                                        @if ($academicAdvisor)
                                            <button type="button"
                                                onclick="document.getElementById('change-advisor-box').classList.add('hidden')"
                                                class="px-3 py-2.5 text-xs font-bold text-slate-600 hover:text-slate-800 transition rounded-xl hover:bg-slate-100 cursor-pointer">
                                                Batal
                                            </button>
                                        @endif
                                    </div>
                                    <button type="button" @click="openNewDosenModal = true"
                                        class="text-xs text-blue-600 hover:text-blue-800 font-semibold underline cursor-pointer text-left sm:text-right">
                                        Dosen tidak ditemukan? Input Dosen Baru
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endif

                    <!-- DETAIL BANNER PENGAJUAN AKTIF (DESKTOP) -->
                    @if ($application)
                        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm space-y-4">
                            <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                                <h4 class="font-bold text-slate-800 text-base">Detail Penempatan Magang</h4>
                                @if (in_array($rawAppSt, ['accepted', 'active', 'completed'], true))
                                    <a href="{{ route('student.application.letter', $application->id) }}" target="_blank"
                                        class="text-xs font-bold text-blue-600 hover:text-blue-800 inline-flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                        <span>Unduh Surat Balasan Dinas</span>
                                    </a>
                                @endif
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                <p><span class="text-slate-500">Instansi Penempatan:</span>
                                    <strong>{{ $application->unit->agencyProfile->agency_name ?? '-' }}</strong>
                                </p>
                                <p><span class="text-slate-500">Unit Kerja:</span>
                                    <strong>{{ $application->unit->name ?? '-' }}</strong>
                                </p>
                                <p><span class="text-slate-500">Periode Magang:</span>
                                    <strong>{{ \Carbon\Carbon::parse($application->start_date)->translatedFormat('d M Y') }} s/d
                                        {{ \Carbon\Carbon::parse($application->end_date)->translatedFormat('d M Y') }}</strong>
                                </p>
                            </div>
                        </div>
                    @else
                        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                            <div>
                                <h4 class="font-bold text-slate-800 text-base">Detail Penempatan Magang</h4>
                                <p class="text-xs text-slate-500 mt-1">Anda belum memiliki pengajuan magang yang aktif.</p>
                            </div>
                            <a href="{{ route('student.application.create') }}"
                                class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl text-xs transition shadow-sm">
                                <span>Buat Pengajuan Baru</span>
                            </a>
                        </div>
                    @endif

                </div>
