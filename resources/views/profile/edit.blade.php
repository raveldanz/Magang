<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-1.5">
            <h2 class="font-bold text-lg sm:text-2xl text-slate-800 leading-tight">
                {{ __('Pengaturan Akun') }}
            </h2>
        </div>
    </x-slot>

    <div class="w-full pt-4 pb-12 sm:pt-6 sm:pb-16 bg-[#F5F8FC] overflow-x-hidden">
        <div class="max-w-4xl mx-auto px-3 sm:px-6 lg:px-8 space-y-6">

            @if (session('success'))
                <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-sm font-semibold">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="p-4 rounded-2xl bg-amber-50 border border-amber-300 text-amber-950 text-sm font-semibold leading-relaxed">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Panduan jelas saat akun baru wajib mengganti kata sandi sementara --}}
            @if (auth()->user()->must_change_password)
                <div class="p-5 sm:p-6 bg-blue-50 border border-blue-200 rounded-2xl sm:rounded-3xl space-y-2">
                    <h3 class="text-base font-bold text-blue-950">Langkah Pengamanan Awal Akun</h3>
                    <ol class="list-decimal list-inside text-sm text-blue-900 space-y-1 leading-relaxed">
                        <li>Masukkan kata sandi sementara yang Anda gunakan saat masuk tadi pada kolom <strong>Kata Sandi Sekarang</strong>.</li>
                        <li>Ketik kata sandi baru pilihan Anda (minimal 8 karakter) pada kolom <strong>Kata Sandi Baru</strong> dan ulangi pada kolom konfirmasi.</li>
                        <li>Klik ikon mata di kanan kolom bila ingin melihat ketikan, lalu tekan tombol <strong>Simpan Kata Sandi</strong>.</li>
                    </ol>
                </div>
            @endif

            {{-- Ringkasan Identitas Akun (Read-Only untuk kejelasan pengguna) --}}
            @php
                $currentUser = auth()->user();
                $unitName = $currentUser->unit?->name;
                $agencyName = $currentUser->agencyProfile?->agency_name;
                $univName = $currentUser->universityRelation?->name;
            @endphp
            <div class="p-5 sm:p-7 bg-white border border-slate-200/80 shadow-xs rounded-2xl sm:rounded-3xl">
                <div class="border-b border-slate-100 pb-3 mb-4">
                    <h3 class="font-bold text-base text-slate-900">Informasi Akun Anda</h3>
                    <p class="text-sm text-slate-600 mt-0.5">Data akun terdaftar pada Sistem Manajemen Magang Pemkot Surabaya</p>
                </div>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/70">
                        <dt class="text-xs font-semibold uppercase tracking-wider text-slate-600">Nama Lengkap</dt>
                        <dd class="font-bold text-slate-900 mt-1 text-base">{{ $currentUser->name }}</dd>
                    </div>
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/70">
                        <dt class="text-xs font-semibold uppercase tracking-wider text-slate-600">Alamat Email Masuk</dt>
                        <dd class="font-semibold text-slate-900 mt-1 break-all">{{ $currentUser->email }}</dd>
                    </div>
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/70">
                        <dt class="text-xs font-semibold uppercase tracking-wider text-slate-600">Peran Tugas</dt>
                        <dd class="font-bold text-slate-900 mt-1">{{ $currentUser->roleLabel() }}</dd>
                    </div>
                    @if ($agencyName || $unitName || $univName)
                        <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/70">
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-600">Unit / Instansi</dt>
                            <dd class="font-semibold text-slate-900 mt-1">
                                {{ collect([$unitName, $agencyName, $univName])->filter()->implode(' — ') }}
                            </dd>
                        </div>
                    @endif
                </dl>
            </div>

            <!-- Card 1: Ganti Password (Bisa diakses SEMUA ROLE: Mahasiswa, Dosen, Mentor, Univ, Admin) -->
            <div class="p-5 sm:p-7 bg-white border border-slate-200/80 shadow-xs rounded-2xl sm:rounded-3xl">
                <div class="max-w-xl">
                    <div class="border-b border-slate-100 pb-3 mb-5">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-base text-slate-900">Keamanan Kata Sandi</h3>
                                <p class="text-sm text-slate-600">Perbarui kata sandi akun Anda secara berkala</p>
                            </div>
                        </div>
                    </div>

                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <!-- Card: Nomor kontak untuk fitur Chat (staf; mahasiswa memakai nomor di Profil Saya) -->
            @if(auth()->user()->role !== 'mahasiswa')
                <div class="p-5 sm:p-7 bg-white border border-slate-200/80 shadow-xs rounded-2xl sm:rounded-3xl">
                    <div class="max-w-xl">
                        <div class="border-b border-slate-100 pb-3 mb-5">
                            <div class="flex items-center gap-2.5">
                                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                </div>
                                <div>
                                    <h3 class="font-bold text-base text-slate-900">Nomor Kontak untuk Chat</h3>
                                    <p class="text-sm text-slate-600">Tampil di Info Kontak chat hanya bagi pihak yang memiliki hubungan magang langsung dengan Anda</p>
                                </div>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('profile.phone.update') }}" class="space-y-4">
                            @csrf
                            @method('PATCH')
                            <div>
                                <label for="chat-phone" class="block text-sm font-semibold text-slate-800 mb-1.5">Nomor Telepon / WhatsApp (opsional)</label>
                                <input id="chat-phone" name="phone" type="tel" value="{{ old('phone', auth()->user()->phone) }}" autocomplete="tel" placeholder="Contoh: 0812 3456 7890"
                                       class="w-full min-h-[44px] px-3.5 rounded-xl border-slate-300 text-sm sm:text-base focus:border-blue-600 focus:ring-blue-600">
                                @error('phone')
                                    <p class="mt-1.5 text-sm text-rose-600 font-medium">{{ $message }}</p>
                                @enderror
                                <p class="mt-1.5 text-sm text-slate-600">Kosongkan bila tidak ingin nomor Anda ditampilkan. Email akun tetap terlihat oleh pihak yang berhubungan.</p>
                            </div>
                            <x-primary-button class="min-h-[44px]">Simpan Nomor</x-primary-button>
                        </form>
                    </div>
                </div>
            @endif

            <!-- Card 2: Hapus Akun (Disederhanakan & dilipat agar tidak membingungkan pengguna baru) -->
            @if(!in_array(auth()->user()->role, ['admin', 'super_admin']))
                <div class="p-5 sm:p-7 bg-white border border-slate-200 shadow-xs rounded-2xl sm:rounded-3xl"
                     x-data="{ openDanger: {{ $errors->userDeletion->isNotEmpty() ? 'true' : 'false' }} }">
                    <div class="max-w-xl">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <h3 class="font-bold text-base text-slate-900">Pengaturan Lanjutan Akun</h3>
                                <p class="text-sm text-slate-600 mt-0.5">Opsi penonaktifan atau penghapusan akun permanen</p>
                            </div>
                            <button type="button" @click="openDanger = !openDanger"
                                    class="px-4 py-2 rounded-xl border border-slate-300 bg-slate-50 hover:bg-slate-100 text-slate-800 text-sm font-semibold transition cursor-pointer shrink-0">
                                <span x-text="openDanger ? 'Tutup' : 'Kelola'"></span>
                            </button>
                        </div>

                        <div x-show="openDanger" x-cloak class="mt-5 pt-5 border-t border-slate-200">
                            @if($hasInternshipHistory ?? false)
                                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-sm text-slate-700 leading-relaxed">
                                    Akun ini menyimpan riwayat magang (logbook, nilai, dan sertifikat) yang wajib diarsipkan,
                                    sehingga tidak dapat dihapus. Hubungi admin dinas atau kampus bila akun perlu dinonaktifkan.
                                </div>
                            @else
                                @include('profile.partials.delete-user-form')
                            @endif
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>