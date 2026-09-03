<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
            <div>
                <h2 class="font-bold text-lg sm:text-2xl text-slate-800 leading-tight">
                    {{ __('Daftar Dosen Pembimbing Lapangan (DPL)') }}
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Portal Pengelolaan Dosen Pembimbing Kampus &bull; <strong class="text-slate-700">{{ $univName ?? 'Universitas' }}</strong>
                </p>
            </div>

            <button type="button" 
                    @click="$dispatch('open-create-lecturer-modal')"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-xs font-bold rounded-xl shadow-xs transition cursor-pointer">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                </svg>
                <span>Tambah Dosen Baru</span>
            </button>
        </div>
    </x-slot>

    <div class="w-full pt-4 pb-6 sm:pt-6 sm:pb-8" 
     x-data="{ 
        showCreateModal: false, 
        showEditModal: false, 
        editLecturer: { id: '', name: '', email: '', nidn: '', status: 'active' } 
     }"
     @open-create-lecturer-modal.window="showCreateModal = true">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 space-y-4 sm:space-y-6">

            <!-- Flash Alerts -->
            @if (session('success'))
                <div class="p-3.5 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-xl shadow-xs flex items-center gap-2 text-emerald-900 text-xs sm:text-sm font-medium">
                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="p-3.5 bg-rose-50 border-l-4 border-rose-500 rounded-r-xl shadow-xs flex items-center gap-2 text-rose-900 text-xs sm:text-sm font-medium">
                    <svg class="w-4 h-4 text-rose-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <!-- Search Panel -->
            <div class="bg-white rounded-2xl border border-slate-100 p-3 sm:p-4 shadow-xs">
                <form method="GET" action="{{ route('university.lecturers.index') }}" class="flex flex-col sm:flex-row items-center gap-2">
                    <div class="relative flex-1 w-full">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 pointer-events-none">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </span>
                        <input type="text" name="search" value="{{ request('search') }}" 
                               placeholder="Cari nama, NIDN, atau email..." 
                               class="w-full pl-9 pr-3 py-2 text-xs sm:text-sm border-slate-200 bg-slate-50 focus:bg-white rounded-xl focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <button type="submit" class="flex-1 sm:flex-initial px-4 py-2 bg-blue-600 hover:bg-blue-700 active:scale-95 text-white text-xs font-bold rounded-xl transition shadow-xs cursor-pointer">
                            Cari
                        </button>
                        @if(request('search'))
                            <a href="{{ route('university.lecturers.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-xl transition text-center cursor-pointer">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Kontainer Daftar Dosen -->
            <div class="bg-white rounded-2xl sm:rounded-3xl border border-slate-100 shadow-xs overflow-hidden">
                <div class="p-3.5 sm:p-5 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-slate-900 text-xs sm:text-base">Daftar Dosen Pembimbing Terdaftar</h3>
                        <p class="text-[10px] sm:text-xs text-slate-400">Total: {{ $lecturers->count() }} dosen pembimbing aktif/cuti</p>
                    </div>
                    <span class="text-[10px] sm:text-xs font-bold bg-blue-50 text-blue-700 px-2.5 py-0.5 rounded-full border border-blue-100 shrink-0">
                        {{ $lecturers->count() }} Dosen
                    </span>
                </div>

                <!-- 1. TAMPILAN MOBILE: Ringkas, Bertingkat 2 Baris, Bebas Overflow -->
                <div class="block sm:hidden divide-y divide-slate-100">
                    @forelse($lecturers as $l)
                        <div class="p-3.5 space-y-2.5">
                            <!-- Baris 1: Nama, NIDN, Email, Status -->
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0 flex-1">
                                    <p class="font-bold text-xs text-slate-900 truncate leading-snug">
                                        {{ $l->name }}
                                    </p>
                                    <p class="text-[10px] text-slate-400 font-mono mt-0.5">
                                        NIDN: {{ $l->studentProfile?->nim ?? ($l->nidn ?? '-') }}
                                    </p>
                                    <p class="text-[10px] text-slate-400 font-mono truncate">
                                        {{ $l->email }}
                                    </p>
                                </div>
                                <span class="px-2 py-0.5 rounded-full text-[9px] font-bold shrink-0
                                    {{ $l->status === 'on_leave' ? 'bg-amber-50 text-amber-700 border border-amber-200' : '' }}
                                    {{ $l->status === 'inactive' ? 'bg-slate-100 text-slate-600 border border-slate-200' : '' }}
                                    {{ $l->status === 'active' || !$l->status ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : '' }}">
                                    {{ $l->status === 'on_leave' ? 'Cuti' : ($l->status === 'inactive' ? 'Nonaktif' : 'Aktif') }}
                                </span>
                            </div>

                            <!-- Baris 2: Beban Bimbingan (Kiri) & Tombol Aksi Sejajar (Kanan) -->
                            <div class="flex items-center justify-between gap-2 pt-1 border-t border-slate-50">
                                <div class="flex items-center gap-1 text-[9px] shrink-0">
                                    <span class="px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-200 font-bold">
                                        {{ $l->active_students_count ?? 0 }} Aktif
                                    </span>
                                    <span class="px-1.5 py-0.5 rounded bg-blue-50 text-blue-700 border border-blue-200 font-bold">
                                        {{ $l->completed_students_count ?? 0 }} Lulus
                                    </span>
                                </div>

                                <!-- Tombol Aksi Ramping Sejajar -->
                                <div class="flex items-center gap-1 shrink-0">
                                    <button type="button" 
                                            @click="editLecturer = { 
                                                id: '{{ $l->id }}', 
                                                name: '{{ addslashes($l->name) }}', 
                                                email: '{{ addslashes($l->email) }}', 
                                                nidn: '{{ addslashes($l->studentProfile?->nim ?? ($l->nidn ?? '')) }}', 
                                                status: '{{ $l->status ?? 'active' }}' 
                                            }; showEditModal = true"
                                            class="inline-flex items-center justify-center h-6 px-2 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 font-bold text-[10px] rounded-lg transition">
                                        Edit
                                    </button>

                                    <form action="{{ route('university.lecturers.reset_password', $l->id) }}" method="POST" onsubmit="return confirm('Reset password dosen {{ $l->name }} ke default?');" class="inline-flex m-0 p-0">
                                        @csrf
                                        <button type="submit" 
                                                class="inline-flex items-center justify-center h-6 px-2 bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 font-bold text-[10px] rounded-lg transition">
                                            Reset
                                        </button>
                                    </form>

                                    <form action="{{ route('university.lecturers.destroy', $l->id) }}" method="POST" onsubmit="return confirm('Hapus dosen {{ $l->name }}?');" class="inline-flex m-0 p-0">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="inline-flex items-center justify-center h-6 px-2 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-bold text-[10px] rounded-lg transition">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center text-slate-400 text-xs">
                            Belum ada data dosen pembimbing.
                        </div>
                    @endforelse
                </div>

                <!-- 2. TAMPILAN DESKTOP: Dibungkus w-full & overflow-hidden agar tidak membocorkan lebar ke mobile -->
                <div class="hidden sm:block w-full overflow-x-auto">
                    <table class="w-full divide-y divide-slate-100 text-left text-xs">
                        <thead class="bg-slate-50/80 text-slate-500 font-bold uppercase tracking-wider text-[11px]">
                            <tr>
                                <th class="py-3.5 px-4">Nama Dosen & Gelar</th>
                                <th class="py-3.5 px-4">NIDN / NIP</th>
                                <th class="py-3.5 px-4">Email Resmi (Login)</th>
                                <th class="py-3.5 px-4 text-center">Beban Bimbingan</th>
                                <th class="py-3.5 px-4 text-center">Status</th>
                                <th class="py-3.5 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($lecturers as $l)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-4 px-4 font-bold text-slate-900 text-xs sm:text-sm">
                                        <div class="flex items-center gap-2">
                                            <div class="w-8 h-8 rounded-full bg-blue-50 text-blue-700 flex items-center justify-center font-bold text-xs shrink-0">
                                                {{ strtoupper(substr($l->name, 0, 1)) }}
                                            </div>
                                            <span>{{ $l->name }}</span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-4 font-mono text-slate-500">
                                        {{ $l->studentProfile?->nim ?? ($l->nidn ?? '-') }}
                                    </td>
                                    <td class="py-4 px-4 font-mono text-slate-500">
                                        {{ $l->email }}
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        <div class="inline-flex items-center gap-1.5 justify-center">
                                            <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                {{ $l->active_students_count ?? 0 }} Aktif
                                            </span>
                                            <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                                {{ $l->completed_students_count ?? 0 }} Lulus
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
                                        <div class="inline-flex items-center gap-1.5 justify-end">
                                            <button type="button" 
                                                    @click="editLecturer = { 
                                                        id: '{{ $l->id }}', 
                                                        name: '{{ addslashes($l->name) }}', 
                                                        email: '{{ addslashes($l->email) }}', 
                                                        nidn: '{{ addslashes($l->studentProfile?->nim ?? ($l->nidn ?? '')) }}', 
                                                        status: '{{ $l->status ?? 'active' }}' 
                                                    }; showEditModal = true"
                                                    class="inline-flex items-center justify-center h-7 px-2.5 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 font-bold rounded-lg text-xs transition cursor-pointer">
                                                Edit
                                            </button>

                                            <form action="{{ route('university.lecturers.reset_password', $l->id) }}" method="POST" onsubmit="return confirm('Reset password dosen {{ $l->name }} ke default?');" class="inline-flex m-0 p-0 items-center">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center justify-center h-7 px-2.5 bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 font-bold rounded-lg text-xs transition cursor-pointer">
                                                    Reset
                                                </button>
                                            </form>

                                            <form action="{{ route('university.lecturers.destroy', $l->id) }}" method="POST" onsubmit="return confirm('Hapus dosen {{ $l->name }}?');" class="inline-flex m-0 p-0 items-center">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex items-center justify-center h-7 px-2.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-bold rounded-lg text-xs transition cursor-pointer">
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-slate-400">
                                        Belum ada data dosen pembimbing terdaftar.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- MODAL 1: TAMBAH DOSEN PEMBIMBING BARU -->
        <div x-show="showCreateModal" 
             x-cloak 
             style="display: none;"
             class="fixed inset-0 z-[9999] overflow-y-auto p-4 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs transition-opacity"
             x-transition:enter="ease-out duration-150"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-100"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            
            <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-5 sm:p-6 border border-slate-100 relative my-auto"
                 @click.outside="showCreateModal = false">
                
                <div class="flex justify-between items-center border-b border-slate-100 pb-3 mb-4">
                    <div>
                        <h3 class="font-bold text-sm sm:text-base text-slate-900">Tambah Dosen Pembimbing Baru</h3>
                        <p class="text-[11px] text-slate-400">Daftarkan dosen pembimbing lapangan kampus</p>
                    </div>
                    <button type="button" @click="showCreateModal = false" class="text-slate-400 hover:text-slate-600 text-sm font-bold">✕</button>
                </div>

                <form method="POST" action="{{ route('university.lecturers.store') }}" class="space-y-3.5">
                    @csrf
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 uppercase tracking-wider mb-1">
                            Nama Lengkap & Gelar Dosen <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="name" value="{{ old('name') }}" required placeholder="Contoh: Dr. Ir. Ahmad Sudrajat, M.Kom." class="w-full text-xs border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 uppercase tracking-wider mb-1">
                            NIDN / NIP (Opsional)
                        </label>
                        <input type="text" name="nidn" value="{{ old('nidn') }}" placeholder="Contoh: 0712058401" class="w-full text-xs border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 font-mono">
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 uppercase tracking-wider mb-1">
                            Email Resmi Kampus / Akun Login <span class="text-rose-500">*</span>
                        </label>
                        <input type="email" name="email" value="{{ old('email') }}" required placeholder="Contoh: ahmad.sudrajat@kampus.ac.id" class="w-full text-xs border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 font-mono">
                        <p class="text-[10px] text-slate-400 mt-1">Password bawaan akun: <code class="bg-slate-100 px-1 py-0.5 rounded text-blue-600 font-bold font-mono">password</code></p>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                        <button type="button" @click="showCreateModal = false" class="px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-xl transition shadow-xs">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL 2: EDIT DATA & STATUS DOSEN PEMBIMBING -->
        <div x-show="showEditModal" 
             x-cloak 
             style="display: none;"
             class="fixed inset-0 z-[9999] overflow-y-auto p-4 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs transition-opacity"
             x-transition:enter="ease-out duration-150"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-100"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            
            <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-5 sm:p-6 border border-slate-100 relative my-auto"
                 @click.outside="showEditModal = false">
                
                <div class="flex justify-between items-center border-b border-slate-100 pb-3 mb-4">
                    <div>
                        <h3 class="font-bold text-sm sm:text-base text-slate-900">Edit Data & Status Dosen Pembimbing</h3>
                        <p class="text-[11px] text-slate-400">Perbarui identitas atau status keaktifan dosen</p>
                    </div>
                    <button type="button" @click="showEditModal = false" class="text-slate-400 hover:text-slate-600 text-sm font-bold">✕</button>
                </div>

                <form method="POST" :action="'/university/lecturers/' + editLecturer.id" class="space-y-3.5">
                    @csrf
                    @method('PUT')
                    
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 uppercase tracking-wider mb-1">
                            Nama Lengkap & Gelar Dosen <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="name" x-model="editLecturer.name" required class="w-full text-xs border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 uppercase tracking-wider mb-1">
                            Email Resmi Kampus / Login <span class="text-rose-500">*</span>
                        </label>
                        <input type="email" name="email" x-model="editLecturer.email" required class="w-full text-xs border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 font-mono">
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 uppercase tracking-wider mb-1">
                            Status Keaktifan Pembimbing <span class="text-rose-500">*</span>
                        </label>
                        <select name="status" x-model="editLecturer.status" required class="w-full text-xs border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500">
                            <option value="active">Aktif (Tersedia untuk Membimbing)</option>
                            <option value="on_leave">Cuti (Sedang Cuti)</option>
                            <option value="inactive">Non-Aktif (Tidak Membimbing)</option>
                        </select>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                        <button type="button" @click="showEditModal = false" class="px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold rounded-xl transition">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-xl transition shadow-xs">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>