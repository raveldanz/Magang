{{-- Halaman detail kampus (admin): Tab Mahasiswa & status magang. Di-include dari admin/universities/show.blade.php. --}}
            <!-- ============================================================== -->
            <!-- TAB 2: MAHASISWA KAMPUS TERDAFTAR & STATUS MAGANG              -->
            <!-- ============================================================== -->
            <div x-show="activeTab === 'mahasiswa'" class="cc-card bg-white rounded-2xl border border-slate-100 shadow-2xs overflow-hidden" style="display: none;">
                <!-- Filter Status Mahasiswa & Bilah Pencarian -->
                <div class="cc-panel-head p-4 space-y-3 border-b border-slate-100">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <h3 class="text-sm font-black text-slate-900">Daftar Mahasiswa Asal {{ $university->name }}</h3>
                            <p class="text-xs text-slate-500">Pantau status pendaftaran, instansi penempatan, DPL pembimbing, dan perkembangan nilai</p>
                        </div>

                        <div class="cc-head-actions flex items-center gap-2">
                            <a href="{{ route('admin.applications.index', ['university_id' => $university->id]) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-bold border border-blue-200 transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                <span>Buka di Pengajuan Masuk</span>
                            </a>
                        </div>
                    </div>

                    <!-- Filter Form -->
                    <form method="GET" action="{{ route('admin.universities.show', $university->id) }}" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 pt-2 border-t border-slate-100">
                        <input type="hidden" name="tab" value="mahasiswa">
                        
                        <div class="relative flex-1">
                            <input type="text" name="student_search" value="{{ request('student_search') }}" placeholder="Cari nama mahasiswa, NIM, atau program studi..." 
                                   class="w-full text-xs border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500"
                                   style="padding-left: 2.25rem !important; padding-right: 0.75rem !important; padding-top: 0.5rem !important; padding-bottom: 0.5rem !important;">
                            <div class="absolute inset-y-0 left-0 flex items-center pointer-events-none text-slate-400 pl-3">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </div>
                        </div>

                        <select name="student_status" class="text-xs border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 py-2">
                            <option value="">Semua Status Mahasiswa</option>
                            <option value="action" {{ request('student_status') === 'action' ? 'selected' : '' }}>Perlu Tindakan</option>
                            <optgroup label="Status Pengajuan">
                                @foreach(\App\Enums\ApplicationStatus::cases() as $statusCase)
                                    <option value="{{ $statusCase->value }}" {{ request('student_status') === $statusCase->value ? 'selected' : '' }}>{{ $statusCase->label() }}</option>
                                @endforeach
                            </optgroup>
                            <option value="no_application" {{ request('student_status') === 'no_application' ? 'selected' : '' }}>Belum Mengajukan</option>
                        </select>

                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl transition shadow-2xs cursor-pointer">
                            Filter
                        </button>
                        @if(request('student_search') || request('student_status'))
                            <a href="{{ route('admin.universities.show', ['university' => $university->id, 'tab' => 'mahasiswa']) }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-xl transition text-center">
                                Reset
                            </a>
                        @endif
                    </form>
                </div>

                <!-- Table Mahasiswa (layar lebar >= 1200px) -->
                <div class="stt-wide">
                    <div class="overflow-x-auto">
                        <table class="stt w-full text-left border-collapse text-[13px]">
                            <colgroup>
                                <col style="width: 21%"><col style="width: 15%"><col style="width: 19%"><col style="width: 22%"><col style="width: 8%"><col style="width: 15%">
                            </colgroup>
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-100 text-slate-400 font-bold uppercase tracking-wider text-xs">
                                    <th class="p-3.5 pl-4">Mahasiswa</th>
                                    <th class="p-3.5">Status</th>
                                    <th class="p-3.5">Penempatan</th>
                                    <th class="p-3.5">Pembimbing</th>
                                    <th class="p-3.5 text-center">Nilai</th>
                                    <th class="p-3.5 pr-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                                @forelse($students as $student)
                                    @php
                                        $profile = $student->studentProfile;
                                        $latestApp = $student->applications->first();
                                        $placement = $latestApp?->placement;
                                        $dosen = $placement?->academicAdvisor;
                                        $mentor = $placement?->mentor ?? $placement?->pembimbing;
                                        $eval = $placement?->evaluation;
                                        $actionHint = $latestApp?->actionHint($requireAdvisor);
                                        $canAssignDpl = $isSuperAdmin && $latestApp && in_array($latestApp->statusValue(), ['accepted', 'active'], true);
                                        $metaLine = collect([
                                            $profile?->jurusan,
                                            $profile?->nim ? 'NIM ' . $profile->nim : null,
                                            $profile?->semester ? 'Smt ' . $profile->semester : null,
                                        ])->filter()->implode(' · ');
                                    @endphp
                                    <tr class="hover:bg-slate-50/80 transition">
                                        {{-- 1. Identitas mahasiswa (email tampil saat kursor diarahkan ke nama) --}}
                                        <td class="p-3.5 pl-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-9 h-9 rounded-xl bg-blue-50 border border-blue-100 text-blue-700 flex items-center justify-center font-bold text-xs shrink-0">
                                                    {{ strtoupper(substr($student->name, 0, 2)) }}
                                                </div>
                                                <div class="min-w-0">
                                                    <div class="stt-title" title="{{ $student->email }}">{{ $student->name }}</div>
                                                    <div class="stt-meta" title="{{ $metaLine }}">{{ $profile?->jurusan ?? '-' }}</div>
                                                    <div class="stt-meta stt-mono">NIM {{ $profile?->nim ?? '-' }} · Smt {{ $profile?->semester ?? '-' }}</div>
                                                </div>
                                            </div>
                                        </td>

                                        {{-- 2. Status + tindakan yang dibutuhkan --}}
                                        <td class="p-3.5">
                                            @if($latestApp)
                                                <x-status-badge :status="$latestApp->status" />
                                                @if($actionHint)
                                                    <div class="stt-hint">{{ $actionHint }}</div>
                                                @endif
                                            @else
                                                <span class="stt-empty">Belum mengajukan</span>
                                            @endif
                                        </td>

                                        {{-- 3. Instansi & divisi --}}
                                        <td class="p-3.5">
                                            @if($latestApp)
                                                @php $agencyName = $latestApp->unit?->agencyProfile?->agency_name ?? 'Pemerintah Kota Surabaya'; @endphp
                                                <div class="stt-place">
                                                    <span class="stt-clamp2 font-semibold text-slate-800" title="{{ $agencyName }}">{{ $agencyName }}</span>
                                                    <span class="stt-line stt-sub" title="{{ $latestApp->unit?->name }}">{{ $latestApp->unit?->name ?? '-' }}</span>
                                                </div>
                                            @else
                                                <span class="stt-empty">-</span>
                                            @endif
                                        </td>

                                        {{-- 4. Pembimbing: dosen DPL & mentor dinas dalam satu kolom --}}
                                        <td class="p-3.5">
                                            <div class="stt-people">
                                                <div class="stt-person">
                                                    <span class="stt-role">DPL</span>
                                                    @if($dosen)
                                                        <span class="stt-name text-indigo-700" title="{{ $dosen->name }}">{{ $dosen->name }}</span>
                                                        @if($isSuperAdmin && $latestApp)
                                                            <button type="button"
                                                                    @click="selectedApplicationId = {{ $latestApp->id }}; selectedStudentName = '{{ addslashes($student->name) }}'; showAssignAdvisorModal = true;"
                                                                    class="stt-link" title="Ganti Dosen Pembimbing" aria-label="Ganti Dosen Pembimbing">
                                                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13l6.232-6.232a2.5 2.5 0 113.536 3.536L12.536 16.536 9 17l.464-3.536z"/></svg>
                                                            </button>
                                                        @endif
                                                    @elseif($canAssignDpl)
                                                        <button type="button"
                                                                @click="selectedApplicationId = {{ $latestApp->id }}; selectedStudentName = '{{ addslashes($student->name) }}'; showAssignAdvisorModal = true;"
                                                                class="stt-assign">+ Tugaskan DPL</button>
                                                    @else
                                                        <span class="stt-empty">Belum ada</span>
                                                    @endif
                                                </div>
                                                <div class="stt-person">
                                                    <span class="stt-role">Mentor</span>
                                                    @if($mentor)
                                                        <span class="stt-name" title="{{ $mentor->name }}">{{ $mentor->name }}</span>
                                                    @else
                                                        <span class="stt-empty">Belum diplot</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>

                                        {{-- 5. Nilai akhir --}}
                                        <td class="p-3.5 text-center">
                                            @php $finalScore = $eval ? (float) $eval->nilai_akhir : 0; @endphp
                                            @if($finalScore > 0)
                                                <span class="stt-score">
                                                    <b>{{ number_format($finalScore, 1) }}</b>
                                                    <span>{{ $eval->grade_calculated }}</span>
                                                </span>
                                            @elseif($eval && ($eval->nilai_pembimbing > 0 || $eval->nilai_dosen_calculated > 0))
                                                <span class="stt-sub whitespace-nowrap">Menunggu nilai {{ $eval->nilai_pembimbing > 0 ? 'dosen' : 'mentor' }}</span>
                                            @else
                                                <span class="text-slate-300 font-bold">-</span>
                                            @endif
                                        </td>

                                        {{-- 6. Aksi --}}
                                        <td class="p-3.5 pr-4 text-right">
                                            <div class="stt-actions">
                                                @if($latestApp)
                                                    <a href="{{ route('admin.applications.show', $latestApp->id) }}" class="inline-flex items-center justify-center whitespace-nowrap px-2.5 py-1.5 text-xs font-medium rounded-lg border transition-all duration-150 bg-slate-50 border-slate-200 text-slate-700 hover:bg-slate-800 hover:text-white hover:border-slate-800 cursor-pointer" title="Lihat Detail Pengajuan">
                                                        Detail
                                                    </a>
                                                @endif
                                                @if($placement)
                                                    <a href="{{ route('admin.logbooks.show', $placement->id) }}" class="inline-flex items-center justify-center whitespace-nowrap px-2.5 py-1.5 text-xs font-medium rounded-lg border transition-all duration-150 bg-blue-50 border-blue-200 text-blue-600 hover:bg-blue-600 hover:text-white hover:border-blue-600 cursor-pointer" title="Lihat Aktivitas Logbook">
                                                        Logbook
                                                    </a>
                                                @endif
                                                @if(!$latestApp && !$placement)
                                                    <span class="text-slate-300 font-bold">-</span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="p-8 text-center text-slate-400">
                                            <p class="font-bold text-slate-600">Tidak ada data mahasiswa terdaftar</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Versi kartu: HP (1 kolom) & tablet/laptop kecil < 1200px (2 kolom) -->
                <div class="stt-cards mlist">
                    @forelse($students as $student)
                        @php
                            $profile = $student->studentProfile;
                            $latestApp = $student->applications->first();
                            $placement = $latestApp?->placement;
                            $dosen = $placement?->academicAdvisor;
                            $mentor = $placement?->mentor ?? $placement?->pembimbing;
                            $eval = $placement?->evaluation;
                            $mScore = $eval ? (float) $eval->nilai_akhir : 0;
                            $mSt = $latestApp?->statusValue();
                            $mHint = $latestApp?->actionHint($requireAdvisor);
                        @endphp
                        <div class="mcard">
                            <div class="mcard-head">
                                <div class="mavatar">{{ strtoupper(substr($student->name, 0, 2)) }}</div>
                                <div class="mcard-main">
                                    <div class="mcard-title">{{ $student->name }}</div>
                                    <div class="mcard-sub">{{ $profile?->jurusan ?? '-' }}<br><span class="mono">NIM {{ $profile?->nim ?? '-' }}</span> &middot; Smt {{ $profile?->semester ?? '-' }}</div>
                                </div>
                                @if($mScore > 0)
                                    <div class="mscore"><b>{{ number_format($mScore, 1) }}</b><span>Grade {{ $eval->grade_calculated }}</span></div>
                                @endif
                            </div>
                            <div class="mcard-body cc-grid2">
                                <div class="cc-span2">
                                    @if($latestApp)
                                        <x-status-badge :status="$latestApp->status" />
                                    @else
                                        <span class="mpill mpill-slate">Belum Mengajukan</span>
                                    @endif
                                    @if($mHint)
                                        <div class="text-xs font-semibold text-amber-700" style="margin-top:6px">{{ $mHint }}</div>
                                    @endif
                                    @if($latestApp)
                                        <div class="mfield-value" style="margin-top:6px">{{ $latestApp->unit?->agencyProfile?->agency_name ?? 'Pemerintah Kota Surabaya' }}</div>
                                        <div class="text-xs text-slate-400">Divisi: {{ $latestApp->unit?->name ?? '-' }}</div>
                                    @endif
                                </div>
                                <div>
                                    <div class="mfield-label">Dosen DPL</div>
                                    <div class="mfield-value">{{ $dosen->name ?? '-' }}</div>
                                    @if($isSuperAdmin && $latestApp && ($dosen || in_array($mSt, ['accepted', 'active'], true)))
                                        <button type="button"
                                                @click="selectedApplicationId = {{ $latestApp->id }}; selectedStudentName = '{{ addslashes($student->name) }}'; showAssignAdvisorModal = true;"
                                                class="cc-link" style="margin-top:2px">{{ $dosen ? 'Ganti DPL' : '+ Tugaskan DPL' }}</button>
                                    @endif
                                </div>
                                <div>
                                    <div class="mfield-label">Mentor Dinas</div>
                                    <div class="mfield-value">{{ $mentor->name ?? 'Belum Diplot' }}</div>
                                </div>
                            </div>
                            @if($latestApp || $placement)
                                <div class="mcard-foot">
                                    <div class="cc-actions">
                                        @if($latestApp)
                                            <a href="{{ route('admin.applications.show', $latestApp->id) }}" class="mbtn mbtn-soft">Detail Pengajuan</a>
                                        @endif
                                        @if($placement)
                                            <a href="{{ route('admin.logbooks.show', $placement->id) }}" class="mbtn mbtn-indigo">Logbook</a>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="mempty">Tidak ada data mahasiswa terdaftar.</div>
                    @endforelse
                </div>
            </div>

