{{-- Halaman detail kampus (admin): Modal tambah DPL & penugasan DPL. Di-include dari admin/universities/show.blade.php. --}}
        <!-- ============================================================== -->
        <!-- MODAL 1: TAMBAH DOSEN DPL BARU                                -->
        <!-- ============================================================== -->
        <div x-show="showAddDosenModal" 
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
             style="display: none;"
             x-transition>
            <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 space-y-5" @click.away="showAddDosenModal = false">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        </div>
                        <div>
                            <h3 class="font-black text-base text-slate-900">Tambah Dosen DPL Baru</h3>
                            <p class="text-xs text-slate-500">{{ $university->name }}</p>
                        </div>
                    </div>
                    <button type="button" @click="showAddDosenModal = false" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
                </div>

                <form method="POST" action="{{ route('admin.universities.dosens.store', $university->id) }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Lengkap & Gelar Dosen <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" required placeholder="Contoh: Dr. Budi Santoso, M.Kom." class="w-full text-xs border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 py-2.5">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Email Resmi Dosen <span class="text-rose-500">*</span></label>
                        <input type="email" name="email" required placeholder="budi@kampus.ac.id" class="w-full text-xs border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 py-2.5">
                        <p class="text-xs text-slate-400 mt-1">Digunakan untuk login ke Portal Dosen dengan password default: 'password'</p>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">NIDN (Opsional)</label>
                            <input type="text" name="nidn" placeholder="0012345678" class="w-full text-xs border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 py-2.5">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nomor Telepon/WA</label>
                            <input type="text" name="phone" placeholder="08123456789" class="w-full text-xs border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 py-2.5">
                        </div>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5">
                        <button type="button" @click="showAddDosenModal = false" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition shadow-xs">
                            Simpan DPL
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- MODAL 2: PENUGASAN / PLOTTING DPL UNTUK MAHASISWA              -->
        <!-- ============================================================== -->
        <div x-show="showAssignAdvisorModal" 
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
             style="display: none;"
             x-transition>
            <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 space-y-5" @click.away="showAssignAdvisorModal = false">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </div>
                        <div>
                            <h3 class="font-black text-base text-slate-900">Penugasan Dosen Pembimbing</h3>
                            <p class="text-xs text-slate-500" x-text="'Mahasiswa: ' + selectedStudentName"></p>
                        </div>
                    </div>
                    <button type="button" @click="showAssignAdvisorModal = false" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
                </div>

                <form method="POST" action="{{ route('admin.universities.assign_advisor', $university->id) }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="application_id" :value="selectedApplicationId">

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Dosen Pembimbing <span class="text-rose-500">*</span></label>
                        <select name="academic_advisor_id" required class="w-full text-xs border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 py-2.5">
                            <option value="">-- Pilih Dosen DPL dari {{ $university->name }} --</option>
                            @foreach($dosens as $dsn)
                                <option value="{{ $dsn->id }}">{{ $dsn->name }} ({{ $dsn->email }})</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-slate-400 mt-1">Dosen yang dipilih akan menerima hak akses untuk memantau logbook dan menilai laporan mahasiswa ini.</p>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5">
                        <button type="button" @click="showAssignAdvisorModal = false" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition shadow-xs">
                            Tugaskan DPL
                        </button>
                    </div>
                </form>
            </div>
        </div>
