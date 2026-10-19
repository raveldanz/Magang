{{-- Halaman detail kampus (admin): Tab akun Admin Portal kampus. Di-include dari admin/universities/show.blade.php. --}}
            <!-- ============================================================== -->
            <!-- TAB 3: AKUN ADMIN PORTAL KAMPUS                                -->
            <!-- ============================================================== -->
            <div x-show="activeTab === 'admin'" class="space-y-4" style="display: none;">
                <div class="cc-box bg-white rounded-3xl border border-slate-100 p-6 sm:p-7 shadow-xs space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-5">
                        <div>
                            <h3 class="font-black text-lg text-slate-900">Akun Resmi Administrator Portal Universitas</h3>
                            <p class="text-xs text-slate-500 mt-1">Akun yang memegang otoritas penuh di Portal Resmi Kampus untuk menetapkan dosen pembimbing dan verifikasi dokumen pengantar</p>
                        </div>

                        @if($university->universityAdmin)
                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 inline-flex items-center gap-1.5 self-start">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                Akun Aktif Terdaftar
                            </span>
                        @else
                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200 self-start animate-pulse">
                                Belum Dibuatkan Akun
                            </span>
                        @endif
                    </div>

                    @if($university->universityAdmin)
                        @php $adminAccount = $university->universityAdmin; @endphp
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Nama Akun</span>
                                <div class="font-bold text-sm text-slate-900 mt-1">{{ $adminAccount->name }}</div>
                                <div class="text-xs text-slate-500">Role: Administrator Universitas</div>
                            </div>

                            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Email Login</span>
                                <div class="font-mono font-bold text-sm text-indigo-700 mt-1 select-all">{{ $adminAccount->email }}</div>
                                <div class="text-xs text-slate-500">Verifikasi: {{ $adminAccount->email_verified_at ? 'Terverifikasi' : 'Belum' }}</div>
                            </div>

                            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Waktu Pembuatan</span>
                                <div class="font-mono text-xs text-slate-700 mt-1">{{ $adminAccount->created_at ? $adminAccount->created_at->format('d M Y H:i') : '-' }}</div>
                                <div class="text-xs text-slate-500">Kredensial Default: password</div>
                            </div>
                        </div>

                        @if($isSuperAdmin)
                        <div class="flex flex-wrap items-center gap-3 pt-4 border-t border-slate-100">
                            @if($isSuperAdmin)
                                <form action="{{ route('admin.impersonate', $adminAccount->id) }}" method="POST" class="m-0">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                                        <span>Masuk Sebagai Admin Kampus (Login As)</span>
                                    </button>
                                </form>
                            @endif

                            @if($isSuperAdmin)
                            <button type="button"
                                    @click="$dispatch('open-reset-modal', {
                                        action: '{{ route('admin.users.reset_password', $adminAccount->id) }}',
                                        name: @js($adminAccount->name),
                                        email: @js($adminAccount->email),
                                        role: 'Admin Kampus'
                                    })"
                                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                                <span>Reset Password ke Default ('password')</span>
                            </button>
                            @endif
                        </div>
                        @endif
                    @else
                        <div class="p-6 rounded-2xl bg-amber-50 border border-amber-200 text-center space-y-3">
                            <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center mx-auto">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            </div>
                            <h4 class="font-bold text-amber-900 text-sm">Universitas Ini Belum Memiliki Akun Admin Portal</h4>
                            <p class="text-xs text-amber-700 max-w-md mx-auto">Buatkan akun admin kampus sekarang agar perwakilan rektorat/kemahasiswaan dapat login dan mengelola plotting dosen pembimbing secara mandiri.</p>
                            @if($isSuperAdmin)
                            <form action="{{ route('admin.universities.create_account', $university->id) }}" method="POST" class="inline-block pt-2">
                                @csrf
                                <button type="button"
                                        @click="$dispatch('open-confirm-modal', {
                                            form: $el.form,
                                            title: 'Buat Akun Admin Kampus',
                                            message: 'Sistem akan membuat akun login portal untuk perwakilan kampus agar dapat mengelola dosen pembimbing secara mandiri.',
                                            label: 'Perguruan tinggi:',
                                            name: @js($university->name),
                                            desc: 'Password awal: password',
                                            confirmText: 'Ya, Buat Akun'
                                        })"
                                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold transition shadow-sm cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    <span>Buatkan Akun Admin Portal Sekarang</span>
                                </button>
                            </form>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

