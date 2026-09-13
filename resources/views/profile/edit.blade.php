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
                           
                            

                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <!-- Card 2: Hapus Akun (HANYA DITAMPILKAN JIKA BUKAN ADMIN / SUPER ADMIN) -->
            @if(!in_array(auth()->user()->role, ['admin', 'super_admin']))
                <div class="p-5 sm:p-7 bg-white border border-rose-600/70 shadow-xs rounded-2x1 sm:rounded-3xl">
                    <div class="max-w-xl">
                        <div class="border-b border-rose-110 pb-3 mb-5">
                            <div class="flex items-center gap-2">
                                

                        @include('profile.partials.delete-user-form')
                    </div>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>