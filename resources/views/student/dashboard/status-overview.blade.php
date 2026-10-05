{{-- Dasbor mahasiswa: Kartu status utama & detail pengajuan aktif. Di-include dari dashboard.blade.php (variabel induk tersedia). --}}
                <!-- 4. STATUS CARD GRID (3 KARTU INFORMASI UTAMA) -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                    <!-- Card 1: Profil Mahasiswa -->
                    <div
                        class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 border-l-4 {{ $profile ? 'border-l-emerald-500' : 'border-l-amber-500' }} flex flex-col justify-between">
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Status Profil</p>
                            <p class="text-lg font-black mt-1 text-slate-800">
                                {{ $profile ? 'Lengkap' : 'Belum Lengkap' }}
                            </p>
                        </div>
                        <div class="mt-4 pt-2">
                            <a href="{{ route('student.profile.edit') }}"
                                class="inline-block text-xs font-bold text-blue-600 hover:text-blue-800">
                                {{ $profile ? 'Edit Data Profil ' : 'Lengkapi Profil Sekarang ' }}
                            </a>
                        </div>
                    </div>

                    <!-- Card 2: Status Pengajuan & Lifecycle -->
                    @php
                        $rawAppSt = $rawSt;
                    @endphp
                    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 border-l-4 
                    {{ $rawAppSt === 'active' ? 'border-l-emerald-500' : '' }}
                    {{ $rawAppSt === 'completed' ? 'border-l-blue-600' : '' }}
                    {{ $rawAppSt === 'accepted' ? 'border-l-indigo-500' : '' }}
                    {{ $rawAppSt === 'verified' ? 'border-l-sky-500' : '' }}
                    {{ $rawAppSt === 'pending' ? 'border-l-amber-500' : '' }}
                    {{ $rawAppSt === 'rejected' ? 'border-l-rose-500' : '' }}
                    {{ $rawAppSt === 'resigned' ? 'border-l-slate-400' : '' }}
                    {{ !$application ? 'border-l-slate-300' : '' }} flex flex-col justify-between">
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-wider text-slate-400">Status Magang
                            </div>
                            <div class="mt-2">
                                @if(!$application)
                                    <span
                                        class="inline-flex items-center gap-1.5 rounded-full bg-slate-50 px-2.5 py-1 text-xs font-bold text-slate-600 border border-slate-200">
                                        Belum Mengajukan
                                    </span>
                                @else
                                    <x-status-badge :status="$application->status" stacked />
                                @endif
                            </div>
                        </div>
                        <div class="mt-4 pt-2">
                            @if(!$application || in_array($rawAppSt, ['resigned', 'rejected']))
                                <a href="{{ route('student.application.create') }}"
                                    class="inline-flex items-center gap-1 text-xs font-bold text-blue-600 hover:text-blue-800">
                                    <span>Buat Pengajuan Baru</span>
                                </a>
                            @else
                                <div class="text-xs text-slate-500">Unit:
                                    <strong>{{ $application->unit->name ?? '-' }}</strong>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Card 3: Pembimbing Lapangan Dinas -->
                    <div
                        class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 border-l-4 border-l-blue-600 flex flex-col justify-between">
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Pembimbing Lapangan
                                (Dinas)
                            </p>
                            <p class="text-base font-bold mt-1 text-slate-800">
                                {{ $mentor ? $mentor->name : 'Belum Diplot Dinas' }}
                            </p>
                        </div>
                        <div class="mt-4 pt-2 space-y-3">
                            <p class="text-[11px] text-slate-400">Ditugaskan resmi oleh instansi penempatan magang Anda.
                            </p>
                            @if ($mentor || $academicAdvisor)
                                <div class="flex flex-wrap gap-2">
                                    @if ($mentor)
                                        <x-chat-button :user="$mentor" label="Chat Mentor" />
                                    @endif
                                    <x-chat-group-button :placement="$placement" />
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Card 4: Penilaian Nilai Pembimbing -->
                    @if($eval)
                        @php
                            $rataRata = round((($eval->nilai_disiplin ?? 0) + ($eval->nilai_kinerja ?? 0) + ($eval->nilai_laporan ?? 0)) / 3, 2);
                        @endphp
                        <div
                            class="bg-white p-5 rounded-2xl shadow-[0_4px_12px_rgba(100,116,139,0.08)] border-l-4 border-purple-500 md:col-span-3">
                            <p class="text-xs font-medium text-slate-400 uppercase tracking-wide">Penilaian Pembimbing
                                Lapangan
                            </p>
                            <p class="text-2xl font-bold mt-1 text-slate-800">{{ $rataRata }}</p>
                            <p class="text-xs text-slate-500 mt-1">Disiplin: {{ $eval->nilai_disiplin ?? '-' }} · Kinerja:
                                {{ $eval->nilai_kinerja ?? '-' }} · Laporan: {{ $eval->nilai_laporan ?? '-' }}
                            </p>
                        </div>
                    @endif
                </div>

                <!-- 5. DETAIL BANNER PENGAJUAN AKTIF -->
                @if ($application)
                    <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm space-y-4">
                        <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                            <h4 class="font-bold text-slate-800 text-base">Detail Penempatan Magang</h4>
                            @if (in_array($appStatusVal, ['accepted', 'active', 'completed']))
                                <a href="{{ route('student.application.letter', $application->id) }}" target="_blank"
                                    class="text-xs font-bold text-blue-600 hover:text-blue-800 inline-flex items-center gap-1">
                                    <span>Unduh Surat Balasan Dinas</span>
                                </a>
                            @endif
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs sm:text-sm">
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
                            <p><span class="text-slate-500">Status Saat Ini:</span>
                                <x-status-badge :status="$application->status" />
                            </p>
                            @if ($rawAppSt === 'rejected')
                                <div
                                    class="col-span-1 md:col-span-2 p-3 bg-rose-50 border border-rose-200 rounded-2xl text-rose-800 text-xs">
                                    <strong>Catatan Penolakan:</strong>
                                    {{ $application->rejection_reason ?? $application->rejection_note }}
                                </div>
                            @elseif ($rawAppSt === 'resigned')
                                <div
                                    class="col-span-1 md:col-span-2 p-3 bg-slate-50 border border-slate-200 rounded-2xl text-slate-700 text-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
                                    <div>
                                        <strong>Status:</strong> Anda telah mengundurkan diri dari kegiatan magang ini.
                                    </div>
                                    <a href="{{ route('student.application.create') }}"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg text-xs transition shadow-sm">
                                        Buat Pengajuan Baru
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
