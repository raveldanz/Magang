<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-1.5">
            <div>
                <h2 class="font-bold text-lg sm:text-2xl text-slate-800 leading-tight">
                    {{ __('Pengaturan Akun') }}
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Kelola keamanan kata sandi dan pengaturan akun Anda
                </p>
            </div>
        </div>
    </x-slot>

    <div class="w-full pt-4 pb-12 sm:pt-6 sm:pb-16 bg-[#F5F8FC] overflow-x-hidden">
        <div class="max-w-4xl mx-auto px-3 sm:px-6 lg:px-8 space-y-6">

            <!-- Card 1: Ganti Password (Bisa diakses SEMUA ROLE: Mahasiswa, Dosen, Mentor, Univ, Admin) -->
            <div class="p-5 sm:p-7 bg-white border border-slate-200/80 shadow-xs rounded-2xl sm:rounded-3xl">
                <div class="max-w-xl">
                    <div class="border-b border-slate-100 pb-3 mb-5">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-sm sm:text-base text-slate-800">Keamanan Kata Sandi</h3>
                                <p class="text-xs text-slate-500">Perbarui kata sandi akun Anda secara berkala</p>
                            </div>
                        </div>
                    </div>

                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <!-- Card 2: Hapus Akun (HANYA DITAMPILKAN JIKA BUKAN ADMIN / SUPER ADMIN) -->
            @if(!in_array(auth()->user()->role, ['admin', 'super_admin']))
                <div class="p-5 sm:p-7 bg-white border border-rose-200 shadow-xs rounded-2xl sm:rounded-3xl">
                    <div class="max-w-xl">
                        <div class="border-b border-rose-100 pb-3 mb-5">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-bold text-sm sm:text-base text-rose-900">Hapus Akun Pengguna</h3>
                                    <p class="text-xs text-rose-500">Tindakan permanen untuk menghapus akun Anda</p>
                                </div>
                            </div>
                        </div>

                        @include('profile.partials.delete-user-form')
                    </div>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>