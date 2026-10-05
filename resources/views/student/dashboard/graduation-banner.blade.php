{{-- Dasbor mahasiswa: Banner kelulusan & unduh E-Sertifikat. Di-include dari dashboard.blade.php (variabel induk tersedia). --}}
                <!-- 2. BANNER KELULUSAN RESMI & UNDUH E-SERTIFIKAT (TEMA EMERALD-SURABAYA BLUE SOLID) -->
                @if ($isPassed)
                    <div class="rounded-3xl p-6 sm:p-8 text-white shadow-xl space-y-6 relative overflow-hidden"
                        style="background: linear-gradient(135deg, #065f46 0%, #047857 50%, #1e3a8a 100%) !important; color: #ffffff !important;">
                        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                            <div class="flex items-start space-x-4">
                                <div class="space-y-1">
                                    <h2 class="text-xl sm:text-2xl font-black" style="color: #ffffff !important;">
                                        Selamat, {{ Auth::user()->name }}! Anda Telah Lulus Magang MBKM
                                    </h2>
                                    <p class="text-xs sm:text-sm max-w-2xl leading-relaxed"
                                        style="color: #d1fae5 !important;">
                                        Seluruh kewajiban logbook harian, laporan akhir ilmiah, dan evaluasi instansi dinas
                                        serta bimbingan akademik DPL kampus telah lengkap. E-Sertifikat resmi kelulusan
                                        telah
                                        diterbitkan.
                                    </p>
                                </div>
                            </div>

                            <div class="shrink-0 w-full md:w-auto">
                                <a href="{{ route('student.certificate.show', $application->id) }}" target="_blank"
                                    class="w-full md:w-auto inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-2xl shadow-xl transition transform hover:scale-105 active:scale-95 cursor-pointer font-black text-xs sm:text-sm"
                                    style="background-color: #ffffff !important; color: #065f46 !important; border: 2px solid #ffffff !important;">
                                    <span>Cetak Sertifikat Resmi (PDF)</span>
                                </a>
                            </div>
                        </div>

                        <!-- Rekapitulasi Nilai Transparan -->
                        @if($eval)
                            @php
                                $nilaiDinas = $eval->nilai_pembimbing ?? round((($eval->nilai_disiplin ?? 0) + ($eval->nilai_kinerja ?? 0) + ($eval->nilai_laporan ?? 0)) / 3, 1);
                                $nilaiDpl = $eval->nilai_dosen_calculated ?? ($eval->nilai_akademik ?? 0);
                                $nilaiAkhir = $eval->nilai_akhir;
                                $grade = $eval->grade_calculated ?? ($eval->grade ?? ($nilaiAkhir >= 85 ? 'A' : ($nilaiAkhir >= 70 ? 'B' : 'C')));
                            @endphp
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-4 border-t text-center"
                                style="border-top-color: rgba(255, 255, 255, 0.2) !important;">
                                <div class="p-3.5 rounded-2xl"
                                    style="background-color: rgba(255, 255, 255, 0.15) !important; border: 1px solid rgba(255, 255, 255, 0.25) !important;">
                                    <span class="text-[10px] font-bold uppercase tracking-wider block"
                                        style="color: #a7f3d0 !important;">Nilai Dinas</span>
                                    <p class="text-xl sm:text-2xl font-black mt-1" style="color: #ffffff !important;">
                                        {{ $nilaiDinas }}/100
                                    </p>
                                </div>
                                <div class="p-3.5 rounded-2xl"
                                    style="background-color: rgba(255, 255, 255, 0.15) !important; border: 1px solid rgba(255, 255, 255, 0.25) !important;">
                                    <span class="text-[10px] font-bold uppercase tracking-wider block"
                                        style="color: #a7f3d0 !important;">Nilai DPL</span>
                                    <p class="text-xl sm:text-2xl font-black mt-1" style="color: #ffffff !important;">
                                        {{ $nilaiDpl }}/100
                                    </p>
                                </div>
                                <div class="p-3.5 rounded-2xl"
                                    style="background-color: rgba(255, 255, 255, 0.2) !important; border: 1px solid rgba(255, 255, 255, 0.35) !important;">
                                    <span class="text-[10px] font-bold uppercase tracking-wider block"
                                        style="color: #fde047 !important;">Nilai Akhir Total</span>
                                    <p class="text-xl sm:text-2xl font-black mt-1" style="color: #fde047 !important;">
                                        {{ $nilaiAkhir }}
                                    </p>
                                </div>
                                <div class="p-3.5 rounded-2xl"
                                    style="background-color: rgba(255, 255, 255, 0.2) !important; border: 1px solid rgba(255, 255, 255, 0.35) !important;">
                                    <span class="text-[10px] font-bold uppercase tracking-wider block"
                                        style="color: #fde047 !important;">Grade Kelulusan</span>
                                    <p class="text-xl sm:text-2xl font-black mt-1" style="color: #ffffff !important;">
                                        {{ $grade }}
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

