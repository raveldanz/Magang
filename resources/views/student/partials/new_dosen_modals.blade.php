<!-- Modal Input Dosen Baru -->
<div x-show="openNewDosenModal" x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0" class="fixed inset-0 z-[9999] overflow-y-auto"
    style="display: none;">

    <div class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm transition-opacity"
        @click="openNewDosenModal = false"></div>

    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
        <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-100 p-6 sm:p-7 space-y-4 my-auto max-h-[90vh] overflow-y-auto">

            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-lg font-bold text-slate-900 leading-6">
                    Input Dosen Pembimbing Baru
                </h3>
                <button type="button" @click="openNewDosenModal = false"
                    class="text-slate-400 hover:text-slate-600 text-sm cursor-pointer">
                    ✕
                </button>
            </div>

            <p class="text-xs text-slate-500 leading-relaxed">
                Jika dosen pembimbing Anda belum terdaftar di sistem, silakan isi data di bawah ini. Akun portal dosen akan otomatis dibuatkan dan terhubung ke universitas Anda.
            </p>

            <form action="{{ route('student.create_advisor') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="university_id" value="{{ Auth::user()->university_id }}">

                <div>
                    <label for="modal_name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Nama Lengkap Beserta Gelar <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="modal_name" name="name" required
                        placeholder="Contoh: Dr. Ir. Ahmad Sudrajat, M.Kom"
                        class="w-full text-xs sm:text-sm border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs">
                </div>

                <div>
                    <label for="modal_email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Email Resmi / Kampus Dosen <span class="text-rose-500">*</span>
                    </label>
                    <input type="email" id="modal_email" name="email" required
                        placeholder="Contoh: ahmad.sudrajat@kampus.ac.id"
                        class="w-full text-xs sm:text-sm border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs">
                </div>

                <div>
                    <label for="modal_nidn" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        NIDN / NIP Dosen (Opsional)
                    </label>
                    <input type="text" id="modal_nidn" name="nidn" placeholder="Contoh: 0012345678"
                        class="w-full text-xs sm:text-sm border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Asal Perguruan Tinggi:
                    </label>
                    <input type="text"
                        value="{{ $univName ?? $profile->universitas ?? 'Universitas Mahasiswa' }}"
                        disabled
                        class="w-full text-xs bg-slate-100 text-slate-600 border-slate-200 rounded-xl shadow-2xs cursor-not-allowed">
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="openNewDosenModal = false"
                        class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition cursor-pointer">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-sm transition cursor-pointer">
                        Daftarkan & Pilih Sebagai Dosen Pembimbing
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<!-- Modal Popup Kredensial Akun Dosen Baru -->
@if (session('new_advisor_credential'))
        @php
            $cred = session('new_advisor_credential');
            $waText = "Halo Bapak/Ibu {$cred['name']},\n\nBerikut adalah akun akses Portal Dosen Pembimbing Magang Anda:\n- Portal Login: {$cred['login_url']}\n- Email: {$cred['email']}\n- Password: {$cred['password']}\n\nSilakan login untuk memonitor logbook mingguan dan memberikan nilai akhir magang mahasiswa. Terima kasih.";
        @endphp
        <div x-show="showCredentialModal" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" class="fixed inset-0 z-[9999] overflow-y-auto"
            style="display: none;">

            <!-- Backdrop -->
            <div class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm transition-opacity"
                @click="showCredentialModal = false"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div
                    class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-emerald-100 p-6 sm:p-8 space-y-5 my-auto max-h-[90vh] overflow-y-auto">

                    <!-- Header Modal -->
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3">

                            <div>
                                <h3 class="text-lg font-black text-slate-900 leading-snug">Akun Dosen Pembimbing
                                    Berhasil Dibuat</h3>
                                <p class="text-xs text-emerald-600 font-semibold">Tersambung ke
                                    {{ $cred['univ_name'] }}
                                </p>
                            </div>
                        </div>
                        <button type="button" @click="showCredentialModal = false"
                            class="text-slate-400 hover:text-slate-600 text-lg p-1 cursor-pointer">
                            ✕
                        </button>
                    </div>

                    <!-- Box Kredensial -->
                    <div
                        class="rounded-2xl bg-slate-900 text-slate-100 p-5 space-y-3 font-mono text-xs border border-slate-800 shadow-inner">
                        <div class="flex justify-between items-center pb-2 border-b border-slate-800">
                            <span
                                class="text-slate-400 font-sans text-[11px] font-bold uppercase tracking-wider">Kredensial
                                Akses Dosen</span>
                            <span
                                class="px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-400 text-[10px] font-sans font-bold">{{ \App\Enums\AccountStatus::ACTIVE->label() }}</span>
                        </div>

                        <div class="grid grid-cols-3 gap-1">
                            <span class="text-slate-400 font-sans">Nama Dosen:</span>
                            <span class="col-span-2 font-bold text-white">{{ $cred['name'] }}</span>
                        </div>

                        <div class="grid grid-cols-3 gap-1">
                            <span class="text-slate-400 font-sans">Email Login:</span>
                            <span
                                class="col-span-2 font-bold text-amber-300 select-all">{{ $cred['email'] }}</span>
                        </div>

                        <div class="grid grid-cols-3 gap-1">
                            <span class="text-slate-400 font-sans">Password Default:</span>
                            <span
                                class="col-span-2 font-bold text-emerald-300 select-all">{{ $cred['password'] }}</span>
                        </div>

                        <div class="grid grid-cols-3 gap-1">
                            <span class="text-slate-400 font-sans">Portal Login:</span>
                            <span
                                class="col-span-2 font-bold text-sky-300 break-all select-all">{{ $cred['login_url'] }}</span>
                        </div>
                    </div>

                    <!-- Instruksi Mahasiswa -->
                    <div
                        class="p-4 bg-amber-50/80 rounded-2xl border border-amber-200/80 text-xs text-amber-900 flex items-start gap-3 leading-relaxed">
                        <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <strong class="font-bold">Instruksi Mahasiswa:</strong>
                            <p class="mt-1 text-amber-800">Harap simpan dan teruskan kredensial di atas kepada
                                <strong>Dosen Pembimbing</strong> Anda agar beliau dapat login ke
                                Portal Dosen untuk memonitor logbook mingguan dan memberikan penilaian akhir
                                magang.
                            </p>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-col sm:flex-row items-center gap-3 pt-2">
                        <button type="button"
                            @click="navigator.clipboard.writeText(`{{ addslashes($waText) }}`); copied = true; setTimeout(() => copied = false, 3000)"
                            class="w-full inline-flex justify-center items-center gap-2 px-5 py-3 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-md transition transform active:scale-98 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                            </svg>
                            <span
                                x-text="copied ? 'Berhasil Disalin ke Clipboard!' : 'Salin Informasi Login (WhatsApp)'">Salin
                                Informasi Login</span>
                        </button>

                        <button type="button" @click="showCredentialModal = false"
                            class="w-full sm:w-auto px-5 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition cursor-pointer">
                            Tutup
                        </button>
                    </div>

                </div>
            </div>
        </div>
@endif
