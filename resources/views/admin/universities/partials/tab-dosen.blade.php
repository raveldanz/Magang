{{-- Halaman detail kampus (admin): Tab Dosen Pembimbing. Di-include dari admin/universities/show.blade.php. --}}
            <!-- ============================================================== -->
            <!-- TAB 1: DOSEN PEMBIMBING LAPANGAN KAMPUS                  -->
            <!-- ============================================================== -->
            <div x-show="activeTab === 'dosen'" class="cc-card bg-white rounded-2xl border border-slate-100 shadow-2xs overflow-hidden" style="display: none;">
                <div class="cc-panel-head flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 border-b border-slate-100">
    <div>
        <h3 class="text-sm font-black text-slate-900">Dosen Pembimbing Terdaftar</h3>
        <p class="text-xs text-slate-500">Kelola akun dosen pembimbing akademik dari {{ $university->name }} yang memantau & menilai logbook mahasiswa</p>
    </div>

    <!-- Tombol Tambah DPL (Mengarah ke form create & otomatis kembali ke sini) -->
    @if($isSuperAdmin)
    <a href="{{ route('admin.users.create', ['university_id' => $university->id, 'role' => 'dosen', 'return_to' => url()->current()]) }}" 
       class="cc-head-actions inline-flex items-center gap-2 px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs transition active:scale-95 cursor-pointer">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
        <span>Tambah DPL Baru</span>
    </a>
    @endif
</div>

                <div class="d-only">
                    <div class="overflow-x-auto">
                        <table class="rtable w-full text-left border-collapse text-[13px]">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-100 text-slate-400 font-bold uppercase tracking-wider text-xs whitespace-nowrap">
                                    <th class="p-3.5 pl-4">Dosen Pembimbing</th>
                                    <th class="p-3.5">Email & Kontak</th>
                                    <th class="p-3.5 text-center">Bimbingan Aktif</th>
                                    <th class="p-3.5 text-center">Bimbingan Selesai</th>
                                    <th class="p-3.5 text-center">Total Mahasiswa</th>
                                    @if($isSuperAdmin)
                                    <th class="p-3.5 pr-4 text-right">Aksi</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                                @forelse($dosens as $dosen)
                                    <tr class="hover:bg-slate-50/80 transition">
                                        <td class="rt-title p-3.5 pl-4" data-label="Dosen Pembimbing">
                                            <div class="flex items-center gap-3">
                                                <div class="w-9 h-9 rounded-xl bg-indigo-50 border border-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-xs shrink-0">
                                                    {{ strtoupper(substr($dosen->name, 0, 2)) }}
                                                </div>
                                                <div>
                                                    <div class="font-bold text-slate-900">{{ $dosen->name }}</div>
                                                    <div class="text-xs text-slate-500">Perguruan Tinggi: {{ $university->name }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="p-3.5" data-label="Email &amp; Kontak">
                                            <div class="font-mono text-slate-800">{{ $dosen->email }}</div>
                                            <div class="text-xs text-slate-400">{{ $dosen->phone ?? 'Nomor telp belum diisi' }}</div>
                                        </td>
                                        <td class="p-3.5 text-center" data-label="Bimbingan Aktif">
                                            <span class="whitespace-nowrap px-2.5 py-1 rounded-full text-xs font-semibold {{ $dosen->active_students_count > 0 ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-slate-50 text-slate-500' }}">
                                                {{ $dosen->active_students_count }} Mhs
                                            </span>
                                        </td>
                                        <td class="p-3.5 text-center" data-label="Bimbingan Selesai">
                                            <span class="whitespace-nowrap px-2.5 py-1 rounded-full text-xs font-semibold {{ $dosen->completed_students_count > 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-50 text-slate-500' }}">
                                                {{ $dosen->completed_students_count }} Mhs
                                            </span>
                                        </td>
                                        <td class="p-3.5 text-center font-bold text-slate-900" data-label="Total Mahasiswa">
                                            {{ $dosen->total_students_count }}
                                        </td>
                                        @if($isSuperAdmin)
                                        <td class="rt-actions p-3.5 pr-4 text-right" data-label="Aksi">
                                            <div class="inline-flex items-center justify-end gap-1.5 align-middle">
                                                @if($isSuperAdmin)
                                                    <form action="{{ route('admin.impersonate', $dosen->id) }}" method="POST" class="inline-flex items-center m-0 p-0 align-middle">
                                                        @csrf
                                                        <button type="submit" class="inline-flex items-center justify-center whitespace-nowrap px-3 py-1.5 text-xs font-medium rounded-lg border transition-all duration-150 bg-slate-50 border-slate-200 text-slate-700 hover:bg-slate-800 hover:text-white hover:border-slate-800 cursor-pointer" title="Login As sebagai Dosen ini">
                                                            Login As
                                                        </button>
                                                    </form>

                                                    <a href="{{ route('admin.users.edit', ['user' => $dosen->id, 'return_to' => url()->current() . '?tab=dosen']) }}" class="inline-flex items-center justify-center whitespace-nowrap px-3 py-1.5 text-xs font-medium rounded-lg border transition-all duration-150 bg-blue-50 border-blue-200 text-blue-600 hover:bg-blue-600 hover:text-white hover:border-blue-600 cursor-pointer" title="Edit Akun Dosen">
                                                        Edit
                                                    </a>

                                                    <button type="button"
                                                            @click="$dispatch('open-reset-modal', {
                                                                action: '{{ route('admin.universities.dosens.reset_password', [$university->id, $dosen->id]) }}',
                                                                name: @js($dosen->name),
                                                                email: @js($dosen->email),
                                                                role: 'Dosen Pembimbing'
                                                            })"
                                                            class="inline-flex items-center justify-center whitespace-nowrap px-3 py-1.5 text-xs font-medium rounded-lg border transition-all duration-150 bg-amber-50 border-amber-200 text-amber-700 hover:bg-amber-500 hover:text-white hover:border-amber-500 cursor-pointer" title="Reset Password ke default ('password')">
                                                        Reset
                                                    </button>

                                                    <button type="button" 
                                                            @click="$dispatch('open-delete-modal', {
                                                                action: '{{ route('admin.universities.dosens.destroy', [$university->id, $dosen->id]) }}',
                                                                title: 'Hapus Dosen Pembimbing',
                                                                name: '{{ addslashes($dosen->name) }}',
                                                                desc: 'Email: {{ $dosen->email }} &bull; {{ $dosen->total_students_count }} Mahasiswa Bimbingan'
                                                            })" 
                                                            class="inline-flex items-center justify-center whitespace-nowrap px-3 py-1.5 text-xs font-medium rounded-lg border transition-all duration-150 bg-rose-50 border-rose-200 text-rose-600 hover:bg-rose-600 hover:text-white hover:border-rose-600 cursor-pointer"
                                                            title="Hapus Dosen Pembimbing">
                                                        Hapus
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $isSuperAdmin ? 6 : 5 }}" class="p-8 text-center text-slate-400">
                                            <div class="flex flex-col items-center justify-center gap-2">
                                                <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                                <p class="font-bold text-slate-600">Belum ada Dosen Pembimbing terdaftar untuk kampus ini</p>
                                                <p class="text-xs text-slate-400">Tambahkan DPL baru menggunakan tombol di atas agar dapat ditugaskan membimbing mahasiswa.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Versi HP: kartu dosen DPL -->
                <div class="m-only mlist">
                    @forelse($dosens as $dosen)
                        <div class="mcard">
                            <div class="mcard-head">
                                <div class="mavatar mavatar-indigo">{{ strtoupper(substr($dosen->name, 0, 2)) }}</div>
                                <div class="mcard-main">
                                    <div class="mcard-title">{{ $dosen->name }}</div>
                                    <div class="mcard-sub"><span class="mono" style="overflow-wrap:anywhere">{{ $dosen->email }}</span><br>{{ $dosen->phone ?? 'Nomor telp belum diisi' }}</div>
                                </div>
                            </div>
                            <div class="mcard-body">
                                <div class="cc-stat3">
                                    <div><b style="color:#2563eb">{{ $dosen->active_students_count }}</b><span>{{ \App\Enums\ApplicationStatus::ACTIVE->label() }}</span></div>
                                    <div><b style="color:#059669">{{ $dosen->completed_students_count }}</b><span>{{ \App\Enums\ApplicationStatus::COMPLETED->label() }}</span></div>
                                    <div><b>{{ $dosen->total_students_count }}</b><span>Total Mhs</span></div>
                                </div>
                            </div>
                            @if($isSuperAdmin)
                                <div class="mcard-foot">
                                    <div class="cc-actions">
                                        <form action="{{ route('admin.impersonate', $dosen->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="mbtn mbtn-login">Login As</button>
                                        </form>
                                        <a href="{{ route('admin.users.edit', ['user' => $dosen->id, 'return_to' => url()->current() . '?tab=dosen']) }}" class="mbtn mbtn-soft">Edit</a>
                                        <button type="button" class="mbtn mbtn-amber"
                                                @click="$dispatch('open-reset-modal', {
                                                    action: '{{ route('admin.universities.dosens.reset_password', [$university->id, $dosen->id]) }}',
                                                    name: @js($dosen->name),
                                                    email: @js($dosen->email),
                                                    role: 'Dosen Pembimbing'
                                                })">Reset</button>
                                        <button type="button"
                                                @click="$dispatch('open-delete-modal', {
                                                    action: '{{ route('admin.universities.dosens.destroy', [$university->id, $dosen->id]) }}',
                                                    title: 'Hapus Dosen Pembimbing',
                                                    name: '{{ addslashes($dosen->name) }}',
                                                    desc: 'Email: {{ $dosen->email }} &bull; {{ $dosen->total_students_count }} Mahasiswa Bimbingan'
                                                })"
                                                class="mbtn mbtn-danger">Hapus</button>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="mempty">Belum ada Dosen Pembimbing terdaftar untuk kampus ini.</div>
                    @endforelse
                </div>
            </div>

