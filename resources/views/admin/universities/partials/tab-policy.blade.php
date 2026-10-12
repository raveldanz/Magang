{{-- Halaman detail kampus (admin): Tab kebijakan penilaian. Di-include dari admin/universities/show.blade.php. --}}
            <!-- ============================================================== -->
            <!-- TAB 4: KEBIJAKAN PENILAIAN & DOKUMEN                           -->
            <!-- ============================================================== -->
            <div x-show="activeTab === 'policy'" class="space-y-4" style="display: none;">
                <div class="cc-box bg-white rounded-3xl border border-slate-100 p-6 sm:p-7 shadow-xs space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-5">
                        <div>
                            <h3 class="font-black text-lg text-slate-900">Kebijakan Evaluasi & Skema Penilaian Magang MBKM</h3>
                            <p class="text-xs text-slate-500 mt-1">Konfigurasi formula bobot penilaian akhir dan keterlibatan Dosen Pembimbing Lapangan</p>
                        </div>

                        @if($isSuperAdmin)
                        <a href="{{ route('admin.universities.edit', $university->id) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition shadow-xs self-start">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            <span>Ubah Kebijakan Penilaian</span>
                        </a>
                        @endif
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Kartu Skema -->
                        <div class="p-5 rounded-2xl bg-slate-50 border border-slate-100 space-y-3">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Model Skema Penilaian</span>
                            <div class="text-base font-black text-slate-900">
                                @if($university->evaluation_scheme === 'mentor_only')
                                    100% Penilaian Mentor Dinas (Single Scheme)
                                @else
                                    Penilaian Kolaboratif Ganda (Dual Evaluation)
                                @endif
                            </div>
                            <p class="text-xs text-slate-600 leading-relaxed">
                                @if($university->evaluation_scheme === 'mentor_only')
                                    Kampus menyerahkan sepenuhnya nilai evaluasi magang mahasiswa kepada Mentor Kedinasan Pemerintah Kota Surabaya. Nilai akhir diambil 100% dari nilai mentor lapangan.
                                @else
                                    Nilai akhir merupakan gabungan berbobot antara penilaian kinerja praktis oleh Mentor Kedinasan Pemkot Surabaya dan evaluasi akademis oleh Dosen Pembimbing Lapangan.
                                @endif
                            </p>
                        </div>

                        <!-- Kartu Pembobotan -->
                        <div class="p-5 rounded-2xl bg-slate-50 border border-slate-100 space-y-3">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Komposisi Bobot Nilai</span>
                            
                            @if($university->evaluation_scheme === 'mentor_only')
                                <div class="flex items-center justify-between p-3 rounded-xl bg-white border border-slate-100">
                                    <span class="text-xs font-bold text-slate-700">Mentor Kedinasan Pemkot Surabaya</span>
                                    <span class="text-xs font-black text-purple-700 bg-purple-50 px-2.5 py-1 rounded-lg border border-purple-100">100%</span>
                                </div>
                            @else
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-white border border-slate-100">
                                        <span class="text-xs font-bold text-slate-700">Mentor Kedinasan Dinas Pemkot</span>
                                        <span class="text-xs font-black text-blue-700 bg-blue-50 px-2.5 py-1 rounded-lg border border-blue-100">{{ $university->weight_mentor ?? 40 }}%</span>
                                    </div>
                                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-white border border-slate-100">
                                        <span class="text-xs font-bold text-slate-700">Dosen Pembimbing Kampus</span>
                                        <span class="text-xs font-black text-indigo-700 bg-indigo-50 px-2.5 py-1 rounded-lg border border-indigo-100">{{ $university->weight_lecturer ?? 60 }}%</span>
                                    </div>
                                </div>
                            @endif

                            <div class="text-xs text-slate-500 pt-1">
                                Syarat Penugasan Dosen Pembimbing: <strong class="text-slate-800">{{ $university->require_dpl ? 'Wajib Ditetapkan' : 'Opsional / Fleksibel' }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
