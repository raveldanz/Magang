<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight flex items-center gap-2">
                    <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    {{ __('Daftar Dosen Pembimbing Lapangan (DPL)') }}
                </h2>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">
                    👨‍🏫 Portal Pengelolaan Dosen Pembimbing Kampus &bull; <strong>{{ $univName ?? 'Universitas' }}</strong>
                </p>
            </div>

            <div class="flex items-center gap-3">
                <span class="bg-blue-50 border border-blue-200 text-blue-700 text-xs font-semibold px-3 py-2 rounded-xl hidden sm:inline-block">
                    {{ $univName ?? 'Kampus Mitra' }}
                </span>
                <button type="button" 
                        x-data
                        onclick="window.dispatchEvent(new CustomEvent('open-create-lecturer-modal'))"
                        @click="$dispatch('open-create-lecturer-modal')"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-md transition active:scale-95 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Tambah Dosen Baru</span>
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-8" 
         x-data="{ 
            showCreateModal: false, 
            showEditModal: false, 
            editLecturer: { id: '', name: '', email: '', nidn: '', status: 'active' } 
         }"
         @open-create-lecturer-modal.window="showCreateModal = true">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Success Message -->
            @if (session('success'))
                <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-xl shadow-xs flex items-center justify-between text-emerald-900 text-sm font-medium">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="p-4 bg-rose-50 border-l-4 border-rose-500 rounded-r-xl shadow-xs flex items-center justify-between text-rose-900 text-sm font-medium">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-rose-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        <span>{{ session('error') }}</span>
                    </div>
                </div>
            @endif

            <!-- Search & Info Card -->
            <div class="bg-white rounded-2xl border border-gray-100 p-4 shadow-xs">
                <form method="GET" action="{{ route('university.lecturers.index') }}" class="flex flex-col sm:flex-row items-center gap-3">
                    <div class="relative flex-1 w-full">
                        <div class="absolute inset-y-0 left-0 flex items-center pointer-events-none text-slate-400" style="padding-left: 1rem !important;">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, NIDN/NIP, atau email dosen..." 
                               class="w-full text-xs sm:text-sm border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs"
                               style="padding-left: 2.75rem !important; padding-right: 1rem !important; padding-top: 0.65rem !important; padding-bottom: 0.65rem !important;">
                    </div>

                    <button type="submit" class="w-full sm:w-auto px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-sm transition cursor-pointer">
                        Cari Dosen
                    </button>
                    @if(request('search'))
                        <a href="{{ route('university.lecturers.index') }}" class="w-full sm:w-auto px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-bold rounded-xl transition text-center cursor-pointer">
                            Reset
                        </a>
                    @endif
                </form>
            </div>

            <!-- Lecturers Table -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
                <div class="p-4 sm:p-6 border-b border-gray-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
                    <div>
                        <h3 class="font-bold text-gray-900 text-sm sm:text-base">Daftar Dosen Pembimbing Terdaftar</h3>
                        <p class="text-xs text-gray-400 mt-0.5">Dosen yang aktif dapat dipilih langsung oleh mahasiswa bimbingan atau ditugaskan oleh admin kampus</p>
                    </div>
                    <span class="text-xs font-bold bg-blue-50 text-blue-700 px-3 py-1 rounded-full border border-blue-100">
                        Total: {{ $lecturers->count() }} Dosen
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 text-left text-xs">
                        <thead class="bg-gray-50/75 text-gray-500 font-bold uppercase tracking-wider text-[11px]">
                            <tr>
                                <th class="py-3.5 px-4">Nama Dosen & Gelar</th>
                                <th class="py-3.5 px-4">NIDN / NIP</th>
                                <th class="py-3.5 px-4">Email Resmi (Login)</th>
                                <th class="py-3.5 px-4 text-center">Beban Bimbingan</th>
                                <th class="py-3.5 px-4 text-center">Status</th>
                                <th class="py-3.5 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($lecturers as $l)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-4 px-4 font-bold text-gray-900 text-xs sm:text-sm">
                                        <div class="flex items-center gap-2">
                                            <div class="w-8 h-8 rounded-full bg-blue-50 text-blue-700 flex items-center justify-center font-bold text-xs shrink-0">
                                                {{ strtoupper(substr($l->name, 0, 1)) }}
                                            </div>
                                            <span>{{ $l->name }}</span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-4 font-mono text-gray-600">
                                        {{ $l->studentProfile->nim ?? ($l->nidn ?? '-') }}
                                    </td>
                                    <td class="py-4 px-4 font-mono text-gray-600">
                                        {{ $l->email }}
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        <div class="inline-flex items-center gap-1.5 justify-center flex-wrap">
                                            <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200" title="Mahasiswa Bimbingan Sedang Magang">
                                                🟢 {{ $l->active_students_count }} Aktif
                                            </span>
                                            <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200" title="Mahasiswa Telah Lulus Magang">
                                                {{ $l->completed_students_count }} Lulus
                                            </span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-4 text-center whitespace-nowrap">
                                        @if($l->status === 'on_leave')
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">Cuti</span>
                                        @elseif($l->status === 'inactive')
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">Non-Aktif</span>
                                        @else
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Aktif</span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-4 text-right whitespace-nowrap">
                                        <div class="btn-action-group">
                                            
                                            <!-- Edit -->
                                            <button type="button" 
                                                    @click="editLecturer = { 
                                                        id: '{{ $l->id }}', 
                                                        name: '{{ addslashes($l->name) }}', 
                                                        email: '{{ addslashes($l->email) }}', 
                                                        nidn: '{{ addslashes($l->studentProfile->nim ?? ($l->nidn ?? '')) }}', 
                                                        status: '{{ $l->status ?? 'active' }}' 
                                                    }; showEditModal = true"
                                                    class="btn-action-edit">
                                                Edit
                                            </button>

                                            <!-- Reset Password -->
                                            <form action="{{ route('university.lecturers.reset_password', $l->id) }}" method="POST" onsubmit="return confirm('Reset password dosen {{ $l->name }} ke default (password)?');" class="btn-action-form">
                                                @csrf
                                                <button type="submit" class="btn-action-reset">
                                                    Reset
                                                </button>
                                            </form>

                                            <!-- Hapus -->
                                            <form action="{{ route('university.lecturers.destroy', $l->id) }}" method="POST" onsubmit="return confirm('Hapus dosen {{ $l->name }}? Pastikan dosen tidak sedang membimbing mahasiswa aktif.');" class="btn-action-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-action-delete">
                                                    Hapus
                                                </button>
                                            </form>

                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-12 text-center text-gray-400">
                                        <div class="text-4xl mb-2">👨‍🏫</div>
                                        <p class="font-bold text-gray-600 text-sm">Belum ada data dosen pembimbing terdaftar.</p>
                                        <p class="text-xs text-gray-400 mt-1">Klik tombol <strong>"Tambah Dosen Baru"</strong> untuk mendaftarkan dosen pembimbing kampus.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- ========================================== -->
        <!-- MODAL 1: TAMBAH DOSEN PEMBIMBING BARU -->
        <!-- ========================================== -->
        <div x-show="showCreateModal" 
             x-cloak 
             style="display: none;"
             class="fixed inset-0 z-[9999] overflow-y-auto p-4 sm:p-6 flex items-center justify-center bg-slate-900/70 backdrop-blur-sm transition-opacity"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            
            <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full p-6 sm:p-7 border border-slate-100 relative my-auto max-h-[90vh] overflow-y-auto"
                 @click.outside="showCreateModal = false"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95">
                
                <div class="flex justify-between items-center border-b border-gray-100 pb-3 mb-4">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-base">
                            👨‍🏫
                        </div>
                        <div>
                            <h3 class="font-bold text-base text-gray-900">Tambah Dosen Pembimbing Baru</h3>
                            <p class="text-xs text-gray-400">Daftarkan dosen pembimbing lapangan untuk {{ $univName }}</p>
                        </div>
                    </div>
                    <button type="button" @click="showCreateModal = false" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 hover:text-slate-800 flex items-center justify-center text-sm font-bold transition cursor-pointer">✕</button>
                </div>

                <form method="POST" action="{{ route('university.lecturers.store') }}" class="space-y-4">
                    @csrf
                    
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                            Nama Lengkap & Gelar Dosen <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="name" value="{{ old('name') }}" required placeholder="Contoh: Dr. Ir. Ahmad Sudrajat, M.Kom." class="w-full text-xs sm:text-sm border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                            NIDN / NIP (Opsional)
                        </label>
                        <input type="text" name="nidn" value="{{ old('nidn') }}" placeholder="Contoh: 0712058401" class="w-full text-xs sm:text-sm border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs font-mono">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                            Email Resmi Kampus / Akun Login <span class="text-rose-500">*</span>
                        </label>
                        <input type="email" name="email" value="{{ old('email') }}" required placeholder="Contoh: ahmad.sudrajat@kampus.ac.id" class="w-full text-xs sm:text-sm border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs font-mono">
                        <p class="text-[11px] text-gray-400 mt-1">Password bawaan akun: <code class="bg-gray-100 px-1.5 py-0.5 rounded text-blue-600 font-bold">password</code></p>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100">
                        <button type="button" @click="showCreateModal = false" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-xl transition cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-md transition active:scale-95 cursor-pointer">
                            Simpan & Daftarkan Dosen
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- MODAL 2: EDIT DATA & STATUS DOSEN PEMBIMBING -->
        <!-- ========================================== -->
        <div x-show="showEditModal" 
             x-cloak 
             style="display: none;"
             class="fixed inset-0 z-[9999] overflow-y-auto p-4 sm:p-6 flex items-center justify-center bg-slate-900/70 backdrop-blur-sm transition-opacity"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            
            <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full p-6 sm:p-7 border border-slate-100 relative my-auto max-h-[90vh] overflow-y-auto"
                 @click.outside="showEditModal = false"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95">
                
                <div class="flex justify-between items-center border-b border-gray-100 pb-3 mb-4">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-base">
                            ✏️
                        </div>
                        <div>
                            <h3 class="font-bold text-base text-gray-900">Edit Data & Status Dosen Pembimbing</h3>
                            <p class="text-xs text-gray-400">Perbarui identitas atau status keaktifan dosen pembimbing</p>
                        </div>
                    </div>
                    <button type="button" @click="showEditModal = false" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 hover:text-slate-800 flex items-center justify-center text-sm font-bold transition cursor-pointer">✕</button>
                </div>

                <form method="POST" :action="'/university/lecturers/' + editLecturer.id" class="space-y-4">
                    @csrf
                    @method('PUT')
                    
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                            Nama Lengkap & Gelar Dosen <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="name" x-model="editLecturer.name" required class="w-full text-xs sm:text-sm border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                            Email Resmi Kampus / Login <span class="text-rose-500">*</span>
                        </label>
                        <input type="email" name="email" x-model="editLecturer.email" required class="w-full text-xs sm:text-sm border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs font-mono">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                            Status Keaktifan Pembimbing <span class="text-rose-500">*</span>
                        </label>
                        <select name="status" x-model="editLecturer.status" required class="w-full text-xs sm:text-sm border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs font-medium">
                            <option value="active">🟢 Aktif (Tersedia untuk Membimbing)</option>
                            <option value="on_leave">🟡 Cuti (Sedang Cuti / Tidak Menerima Mahasiswa)</option>
                            <option value="inactive">🔴 Non-Aktif (Tidak Menjadi Pembimbing)</option>
                        </select>
                        <p class="text-[11px] text-gray-400 mt-1">Ubah ke "Cuti" atau "Non-Aktif" jika dosen sedang cuti atau tidak ditugaskan membimbing.</p>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100">
                        <button type="button" @click="showEditModal = false" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-xl transition">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-md transition active:scale-95 cursor-pointer">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
