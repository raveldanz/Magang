                <!-- ==================================================== -->
                <!-- 4 & 5. MOBILE VIEW: STATUS CARDS & DETAIL PENEMPATAN  -->
                <!-- ==================================================== -->
                <div class="block md:hidden space-y-3.5">

                    <!-- Card 1: Status Magang (Mobile) -->
                    <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-100">
                        <div class="flex items-center justify-between">
                            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Status Magang</p>
                            @if ($application)
                                <x-status-badge :status="$application->status" />
                            @endif
                        </div>
                        <div class="mt-1">
                            @if ($application)
                                <p class="text-xs text-slate-600 font-medium">{{ $application->unit->name ?? $application->unit->agencyProfile->agency_name ?? 'Program Magang' }}</p>
                            @else
                                <p class="text-sm font-bold text-slate-800">Belum Mengajukan</p>
                            @endif
                        </div>
                        <div class="mt-3 pt-2 border-t border-slate-50 flex items-center justify-between">
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

                    <!-- Card 2: Dosen Pembimbing (Mobile) -->
                    <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-100">
                        <div class="flex items-center justify-between">
                            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Dosen Pembimbing</p>
                            @if ($academicAdvisor)
                                <span class="px-2 py-0.5 text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-md">
                                    Terpilih
                                </span>
                            @elseif ($application && in_array($rawAppSt, ['accepted', 'active', 'completed'], true))
                                <span class="px-2 py-0.5 text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200 rounded-md animate-pulse">
                                    Perlu Ditentukan
                                </span>
                            @endif
                        </div>
                        <div class="mt-1">
                            @if ($academicAdvisor)
                                <p class="text-sm font-bold text-slate-800">{{ $academicAdvisor->name }}</p>
                                <div class="text-xs text-slate-500 mt-0.5 space-y-0.5">
                                    <p class="truncate font-mono">{{ $academicAdvisor->email }}</p>
                                    <p class="truncate">{{ is_string($academicAdvisor->university) ? $academicAdvisor->university : ($academicAdvisor->universityRelation?->name ?? $academicAdvisor->university?->name ?? $univName ?? $profile->universitas) }}</p>
                                </div>
                            @elseif ($application && in_array($rawAppSt, ['accepted', 'active', 'completed'], true))
                                <p class="text-xs text-slate-600">Belum memilih dosen pembimbing</p>
                            @else
                                <p class="text-xs text-slate-400">Belum Tersedia</p>
                            @endif
                        </div>
                        @if ($application && in_array($rawAppSt, ['accepted', 'active', 'completed'], true))
                            <div class="mt-2.5 pt-2 border-t border-slate-50">
                                <button type="button"
                                    onclick="const el = document.getElementById('change-advisor-box-mobile'); el.classList.toggle('hidden'); if(!el.classList.contains('hidden')) el.scrollIntoView({behavior: 'smooth'});"
                                    class="text-xs font-bold text-blue-600 hover:text-blue-800 cursor-pointer">
                                    {{ $academicAdvisor ? 'Ganti Dosen Pembimbing' : 'Pilih Dosen Pembimbing' }} &rarr;
                                </button>
                            </div>
                        @endif
                    </div>

                    <!-- FORM PILIH DOSEN PEMBIMBING (MOBILE - TEPAT DI BAWAH KARTU DOSEN PEMBIMBING) -->
                    @if ($application && in_array($rawAppSt, ['accepted', 'active', 'completed'], true))
                        <div id="change-advisor-box-mobile" class="{{ $academicAdvisor ? 'hidden' : '' }} bg-white rounded-2xl p-4 border border-slate-200 shadow-sm space-y-3 scroll-mt-6">
                            <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                                <h4 class="font-bold text-slate-800 text-sm">Pilih Dosen Pembimbing</h4>
                                @if ($academicAdvisor)
                                    <button type="button"
                                        onclick="document.getElementById('change-advisor-box-mobile').classList.add('hidden')"
                                        class="text-xs text-slate-400 hover:text-slate-600 font-bold transition cursor-pointer">
                                        ✕ Tutup
                                    </button>
                                @endif
                            </div>
                            <form action="{{ route('student.select_advisor') }}" method="POST" class="space-y-3">
                                @csrf
                                <div>
                                    <label for="academic_advisor_id_mobile"
                                        class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                        Pilih Dosen Terdaftar: {{ $univName ?? $profile->universitas ?? 'Kampus Mahasiswa' }}
                                    </label>
                                    <select id="academic_advisor_id_mobile" name="academic_advisor_id" required
                                        class="w-full text-xs border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs p-2.5">
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

                                <div class="pt-1 flex flex-col gap-2">
                                    <button type="submit"
                                        class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs transition cursor-pointer">
                                        {{ __('Simpan Dosen Pembimbing') }}
                                    </button>
                                    @if ($academicAdvisor)
                                        <button type="button"
                                            onclick="document.getElementById('change-advisor-box-mobile').classList.add('hidden')"
                                            class="w-full py-1.5 text-xs font-bold text-slate-600 hover:text-slate-800 transition rounded-xl hover:bg-slate-100 cursor-pointer">
                                            Batal
                                        </button>
                                    @endif
                                    <button type="button" @click="openNewDosenModal = true"
                                        class="text-xs text-blue-600 hover:text-blue-800 font-semibold underline cursor-pointer text-center pt-1">
                                        Dosen tidak ditemukan? Input Dosen Baru
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endif

                    <!-- Card 3: Mentor Lapangan (Mobile) -->
                    <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-100">
                        <div>
                            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Mentor Lapangan</p>
                            <p class="text-sm font-bold mt-1 text-slate-800">
                                {{ $mentor ? $mentor->name : 'Belum Diplot Dinas' }}
                            </p>
                            <p class="text-[11px] text-slate-400 mt-0.5">Ditugaskan resmi oleh instansi penempatan magang Anda.</p>
                        </div>
                        @if ($mentor || $placement)
                            <div class="mt-2.5 pt-2 border-t border-slate-50 flex flex-wrap gap-2">
                                @if ($mentor)
                                    <x-chat-button :user="$mentor" label="Chat Mentor" />
                                @endif
                                @if ($placement)
                                    <x-chat-group-button :placement="$placement" />
                                @endif
                            </div>
                        @endif
                    </div>

                    <!-- DETAIL BANNER PENGAJUAN AKTIF (Mobile) -->
                    @if ($application)
                        <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm space-y-3">
                            <div class="flex justify-between items-center border-b border-slate-100 pb-2.5">
                                <h4 class="font-bold text-slate-800 text-sm">Detail Penempatan Magang</h4>
                                @if (in_array($rawAppSt, ['accepted', 'active', 'completed'], true))
                                    <a href="{{ route('student.application.letter', $application->id) }}" target="_blank"
                                        class="text-xs font-bold text-blue-600 hover:text-blue-800 inline-flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                        <span>Unduh Surat</span>
                                    </a>
                                @endif
                            </div>
                            <div class="space-y-2 text-xs">
                                <div class="flex items-start justify-between py-1 border-b border-slate-50">
                                    <span class="text-slate-500">Instansi:</span>
                                    <span class="font-bold text-slate-800 text-right ml-2">{{ $application->unit->agencyProfile->agency_name ?? '-' }}</span>
                                </div>
                                <div class="flex items-start justify-between py-1 border-b border-slate-50">
                                    <span class="text-slate-500">Unit Kerja:</span>
                                    <span class="font-bold text-slate-800 text-right ml-2">{{ $application->unit->name ?? '-' }}</span>
                                </div>
                                <div class="flex items-start justify-between py-1">
                                    <span class="text-slate-500">Periode:</span>
                                    <span class="font-bold text-slate-800 text-right ml-2">
                                        {{ \Carbon\Carbon::parse($application->start_date)->translatedFormat('d M Y') }} s/d {{ \Carbon\Carbon::parse($application->end_date)->translatedFormat('d M Y') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between gap-3">
                            <div>
                                <h4 class="font-bold text-slate-800 text-xs">Detail Penempatan Magang</h4>
                                <p class="text-[11px] text-slate-500 mt-0.5">Belum ada pengajuan aktif.</p>
                            </div>
                            <a href="{{ route('student.application.create') }}"
                                class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg text-xs transition shadow-sm shrink-0">
                                <span>Daftar Magang</span>
                            </a>
                        </div>
                    @endif

                </div>
