{{-- Dasbor mahasiswa: Modal rincian kelengkapan & langkah berikutnya. Di-include dari dashboard.blade.php (variabel induk tersedia). --}}
                <!-- MODAL: RINCIAN KELENGKAPAN & KEKURANGAN BERKAS MAGANG -->
                <div x-show="showDetailModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto"
                    aria-labelledby="modal-title" role="dialog" aria-modal="true">
                    @php
                        $isAccepted = !empty($application) && in_array($rawSt, ['accepted', 'active', 'completed'], true);
                        $canProceed = $isAccepted && !empty($academicAdvisor);
                    @endphp
                    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                        <!-- Backdrop -->
                        <div x-show="showDetailModal" x-transition:enter="ease-out duration-200"
                            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                            x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100"
                            x-transition:leave-end="opacity-0" @click="showDetailModal = false"
                            class="fixed inset-0 bg-slate-900/60 transition-opacity"></div>

                        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"
                            aria-hidden="true">&#8203;</span>

                        <div x-show="showDetailModal" x-transition:enter="ease-out duration-200"
                            x-transition:enter-start="opacity-0 translate-y-4 sm:scale-95"
                            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                            x-transition:leave="ease-in duration-150"
                            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                            x-transition:leave-end="opacity-0 translate-y-4 sm:scale-95"
                            class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-slate-200">

                            <!-- Header Modal -->
                            <div
                                class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                                <div>
                                    <h3 class="text-sm font-bold text-slate-800" id="modal-title">Rincian Kelengkapan &
                                        Kekurangan Berkas Magang</h3>
                                    <p class="text-xs text-slate-500 mt-0.5">{{ $stepCount }} dari 7 syarat telah
                                        terpenuhi ({{ $progressPercent }}%)</p>
                                </div>
                                <button type="button" @click="showDetailModal = false"
                                    class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg border border-slate-200 hover:bg-white transition cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <!-- List Status & Kekurangan dengan Navigasi Arahan -->
                            <div class="p-6 divide-y divide-slate-100 text-xs max-h-[70vh] overflow-y-auto space-y-1">

                                <!-- Box Arahan Langkah Selanjutnya (Next Actionable Step) -->
                                <div class="pb-3">
                                    <div
                                        class="p-3.5 bg-blue-50 border border-blue-200 rounded-xl flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                        <div class="space-y-0.5">
                                            <span
                                                class="text-[11px] font-bold uppercase tracking-wider text-blue-800 block">
                                                Arahan Tahapan Anda Saat Ini:
                                            </span>
                                            <p class="text-slate-700 leading-relaxed font-medium">
                                                @if(empty($profile?->nim))
                                                    Lengkapi biodata dan Nomor Induk Mahasiswa (NIM) terlebih dahulu.
                                                @elseif(!$application)
                                                    Anda belum mengajukan magang. Silakan buat permohonan magang untuk memulai program magang.
                                                @elseif(in_array($rawSt, ['resigned', 'rejected'], true))
                                                    {{ $rawSt === 'rejected' ? 'Pengajuan magang Anda sebelumnya ditolak.' : 'Anda telah mengundurkan diri dari magang sebelumnya.' }}
                                                    Silakan buat permohonan magang baru untuk melanjutkan program magang.
                                                @elseif($rawSt === 'verified')
                                                    Berkas Anda sudah lolos verifikasi. Menunggu keputusan penerimaan dari instansi dinas terkait.
                                                @elseif(!in_array($rawSt, ['accepted', 'active', 'completed'], true))
                                                    Menunggu verifikasi dan persetujuan dari instansi dinas terkait.
                                                @elseif($isPassed)
                                                    Selamat! Seluruh kewajiban telah terpenuhi. E-Sertifikat resmi siap
                                                    diunduh.
                                                @elseif(!$academicAdvisor)
                                                    Pengajuan diterima! Silakan <strong>pilih Dosen Pembimbing Lapangan</strong> dari perguruan tinggi Anda.
                                                @elseif($logbooksCount < 30)
                                                    Terus catat aktivitas kerja harian Anda (sudah terisi
                                                    <strong>{{ $logbooksCount }} hari</strong>, tersisa
                                                    <strong>{{ max(0, 30 - $logbooksCount) }} hari kerja</strong>).
                                                @elseif(!$finalReport || $rawFinalSt !== 'approved')
                                                    Program magang Anda sedang berlangsung. Anda dapat mencatat aktivitas
                                                    harian (terisi <strong>{{ $logbooksCount }} hari</strong>) serta
                                                    mengunggah/mencicil draf Laporan Akhir kapan saja.
                                                @else
                                                    Laporan akhir Anda sudah disetujui. E-Sertifikat akan terbit setelah syarat berikut terpenuhi:
                                                    {{ implode(' ', $certificateBlockers) }}
                                                @endif
                                            </p>
                                        </div>

                                        {{-- Tombol Tindakan Cepat Dinamis Sesuai Tahapan --}}
                                        <div class="shrink-0">
                                            @if(empty($profile?->nim))
                                                <a href="{{ route('student.profile.edit') }}"
                                                    class="px-3.5 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition inline-block">
                                                    Lengkapi Profil
                                                </a>
                                            @elseif(!$application || in_array($rawSt, ['resigned', 'rejected']))
                                                <a href="{{ route('student.application.create') }}"
                                                    class="px-3.5 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition inline-block">
                                                    Daftar Magang
                                                </a>
                                            @elseif(!in_array($rawSt, ['accepted', 'active', 'completed']))
                                                <span
                                                    class="px-3 py-1 rounded-lg bg-amber-100 text-amber-800 text-xs font-bold border border-amber-200 inline-block">
                                                    Dalam Peninjauan
                                                </span>
                                            @elseif($isPassed)
                                                <a href="{{ route('student.certificate.show', $application->id) }}"
                                                    target="_blank"
                                                    class="px-3.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition inline-block">
                                                    Unduh Sertifikat
                                                </a>
                                            @elseif(!$academicAdvisor)
                                                <a href="#change-advisor-box" @click="showDetailModal = false"
                                                    class="px-3.5 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition inline-block">
                                                    Pilih Dosen
                                                </a>
                                            @elseif($logbooksCount < 30)
                                                <a href="{{ route('student.logbook.create') }}"
                                                    class="px-3.5 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition inline-block">
                                                    + Isi Logbook
                                                </a>
                                            @elseif(!$finalReport || $rawFinalSt !== 'approved')
                                                <a href="{{ route('student.final_report.index') }}"
                                                    class="px-3.5 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition inline-block">
                                                    Unggah Laporan
                                                </a>
                                            @else
                                                <span title="{{ implode(' ', $certificateBlockers) }}"
                                                    class="px-3 py-1 rounded-lg bg-amber-100 text-amber-800 text-xs font-bold border border-amber-200 inline-block cursor-help">
                                                    Menunggu Nilai &amp; Kelulusan
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <!-- 1. Profil -->
                                <div class="py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                    <div>
                                        <div class="font-bold text-slate-800">1. Biodata & NIM Mahasiswa</div>

                                    </div>
                                    <div class="shrink-0">
                                        @if(!empty($profile?->nim))
                                            <span
                                                class="px-2.5 py-1 rounded-md font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 inline-block">
                                                Lengkap
                                                ({{ $profile->user->name ?? auth()->user()->name ?? 'Nama Mahasiswa' }}
                                                &bull; {{ $profile->nim }})
                                            </span>
                                        @else
                                            <a href="{{ route('student.profile.edit') }}"
                                                class="px-3 py-1.5 rounded-md font-bold bg-blue-600 hover:bg-blue-700 text-white shadow-xs transition inline-block">
                                                Lengkapi Profil
                                            </a>
                                        @endif
                                    </div>
                                </div>
<!-- 2. Unit Penempatan -->
<div class="py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
    <div>
        <div class="font-bold text-slate-800">2. Penempatan Instansi & Unit Kerja</div>
        <div class="text-slate-500 mt-0.5">Pemilihan instansi dinas dan divisi penempatan magang</div>
    </div>
    <div class="shrink-0">
        @if($application && in_array($rawSt, ['accepted', 'active', 'completed'], true))
            <span class="px-2.5 py-1 rounded-md font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 inline-block">
                Diterima ({{ $application->unit->name ?? 'Instansi Dinas' }})
            </span>
        @elseif($application && in_array($rawSt, ['resigned', 'rejected'], true))
            <a href="{{ route('student.application.create') }}"
                class="px-3 py-1.5 rounded-md font-bold bg-blue-600 hover:bg-blue-700 text-white shadow-xs transition inline-block">
                Buat Pengajuan Baru
            </a>
        @elseif($application)
            <span class="px-2.5 py-1 rounded-md font-bold bg-amber-50 text-amber-700 border border-amber-200 inline-block">
                {{ $rawSt === 'verified' ? 'Menunggu Keputusan Dinas' : 'Menunggu Verifikasi Dinas' }}
            </span>
        @else
            {{-- Karena NIM sudah ada, saat belum ada pengajuan tombol ini HARUS BISA DIKLIK --}}
            <a href="{{ route('student.application.create') }}"
                class="px-3 py-1.5 rounded-md font-bold bg-blue-600 hover:bg-blue-700 text-white shadow-xs transition inline-block">
                Pilih Unit
            </a>
        @endif
    </div>
</div>

                                <!-- 3. DPL Kampus -->
                                <div class="py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                    <div>
                                        <div class="font-bold text-slate-800">3. Dosen Pembimbing Lapangan</div>
                                        <div class="text-slate-500 mt-0.5">Dosen pembimbing akademik dari universitas
                                            asal</div>
                                    </div>
                                    <div class="shrink-0">
                                        @if($academicAdvisor)
                                            <span
                                                class="px-2.5 py-1 rounded-md font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 inline-block">
                                                Terdaftar ({{ $academicAdvisor->name }})
                                            </span>
                                        @elseif($isAccepted)
                                            <a href="#change-advisor-box" @click="showDetailModal = false"
                                                class="px-3 py-1.5 rounded-md font-bold bg-blue-600 hover:bg-blue-700 text-white shadow-xs transition inline-block">
                                                Pilih Dosen
                                            </a>
                                        @else
                                            <button type="button" disabled
                                                class="px-3 py-1.5 rounded-md font-bold bg-slate-100 text-slate-400 border border-slate-200 cursor-not-allowed select-none inline-flex items-center gap-1 shadow-none"
                                                title="Sertifikat belum dapat diunduh: {{ implode(' ', $certificateBlockers) }}">
                                                <span>Pilih Dosen</span>
                                            </button>
                                        @endif
                                    </div>
                                </div>

                                <!-- 4. Logbook Kegiatan Harian -->
                                <div class="py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                    <div>
                                        <div class="font-bold text-slate-800">4. Logbook Kegiatan Harian</div>
                                        <div class="text-slate-500 mt-0.5">
                                            Status: <strong>{{ $logbooksCount }} hari terisi</strong>
                                        </div>
                                    </div>
                                    <div class="shrink-0 flex items-center gap-2">
                                        @if ($canProceed)
                                            <a href="{{ route('student.logbook.create') }}"
                                                class="px-3 py-1.5 rounded-md font-bold bg-blue-600 hover:bg-blue-700 text-white shadow-xs transition inline-flex items-center gap-1">
                                                <span>+ Isi Logbook</span>
                                            </a>
                                            <a href="{{ route('student.logbook.index') }}"
                                                class="px-3 py-1.5 rounded-md font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-300 transition inline-block">
                                                Lihat Riwayat
                                            </a>
                                        @else
                                          <button type="button" disabled
                                                class="px-3 py-1.5 rounded-md font-bold bg-slate-100 text-slate-400 border border-slate-200 cursor-not-allowed select-none inline-flex items-center gap-1 shadow-none"
                                                title="Lengkapi verifikasi pengajuan dan pilih DPL terlebih dahulu">
                                                <span>+ Isi Logbook</span>
                                                
                                            </button>
                                            <button type="button" disabled
                                                class="px-3 py-1.5 rounded-md font-bold bg-slate-100 text-slate-400 border border-slate-200 cursor-not-allowed select-none inline-block shadow-none"
                                                title="Lengkapi verifikasi pengajuan dan pilih DPL terlebih dahulu">
                                                Lihat Riwayat
                                            </button>
                                        @endif
                                    </div>
                                </div>

                                <!-- 5. Laporan Akhir Ilmiah Magang -->
                                <div class="py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                    <div>
                                        <div class="font-bold text-slate-800">5. Laporan Akhir Ilmiah Magang</div>
                                        <div class="text-slate-500 mt-0.5">Dokumen pertanggungjawaban kegiatan magang
                                            yang disahkan DPL & Mentor</div>
                                    </div>
                                    <div class="shrink-0">
                                        @if (!$canProceed)
                                            <button type="button" disabled
                                                class="px-3 py-1.5 rounded-md font-bold bg-slate-100 text-slate-400 border border-slate-200 cursor-not-allowed select-none inline-flex items-center gap-1 shadow-none"
                                                title="Lengkapi verifikasi pengajuan dan pilih DPL terlebih dahulu">
                                                <span>Unggah Laporan</span>
                                                
                                            </button>
                                        @elseif ($finalReport && $rawFinalSt === 'approved')
                                            <span
                                                class="px-2.5 py-1 rounded-md font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 inline-block">
                                                Disetujui & Disahkan
                                            </span>
                                        @elseif ($finalReport)
                                            <a href="{{ route('student.final_report.index') }}"
                                                class="px-3 py-1.5 rounded-md font-bold bg-amber-100 hover:bg-amber-200 text-amber-900 border border-amber-300 transition inline-block">
                                                Cek Status Review
                                            </a>
                                        @else
                                            <a href="{{ route('student.final_report.index') }}"
                                                class="px-3 py-1.5 rounded-md font-bold bg-blue-600 hover:bg-blue-700 text-white shadow-xs transition inline-block">
                                                Unggah Laporan
                                            </a>
                                        @endif
                                    </div>
                                </div>

                                <!-- 6. Nilai & Sertifikat -->
                                <div class="py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                    <div>
                                        <div class="font-bold text-slate-800">6. Nilai Kelulusan & E-Sertifikat Resmi
                                        </div>

                                        <div class="shrink-0">
                                            @if($isPassed)
                                                <a href="{{ route('student.certificate.show', $application->id) }}"
                                                    target="_blank"
                                                    class="px-3.5 py-1.5 rounded-md font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs transition inline-block">
                                                    Unduh E-Sertifikat (PDF)
                                                </a>
                                            @else
                                                <button type="button" disabled
                                                class="px-3 py-1.5 rounded-md font-bold bg-slate-100 text-slate-400 border border-slate-200 cursor-not-allowed select-none inline-flex items-center gap-1 shadow-none"
                                                title="Lengkapi verifikasi pengajuan dan pilih DPL terlebih dahulu">
                                                <span>Unduh E-Sertifikat</span>
                                                
                                            </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Footer Modal -->
                                <div class="px-6 py-3 bg-slate-50 border-t border-slate-200 flex justify-end">
                                    <button type="button" @click="showDetailModal = false"
                                        class="px-4 py-2 bg-white border border-slate-300 text-slate-700 text-xs font-bold rounded-lg hover:bg-slate-100 transition cursor-pointer">
                                        Tutup Rincian
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

