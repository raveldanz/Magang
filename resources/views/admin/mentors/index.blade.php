<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-black text-xl sm:text-2xl text-gray-900 tracking-tight flex items-center gap-2">
                    <span>👔</span>
                    <span>Kelola Mentor Lapangan {{ $currentAgency->agency_name ?? ($isSuperAdmin ? 'Seluruh Instansi' : 'Dinas') }}</span>
                </h2>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">
                    Kelola akun pembimbing teknis dinas yang bertugas membimbing dan mengevaluasi mahasiswa magang
                </p>
            </div>

            <button type="button" 
                    @click="showCreateModal = true"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-md transition active:scale-95 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
                <span>Tambah Mentor Baru</span>
            </button>
        </div>
    </x-slot>

    <div class="py-8" x-data="{ 
        showCreateModal: false, 
        showEditModal: false, 
        editMentor: { id: '', name: '', email: '', agency_profile_id: '{{ $agencyId ?? '' }}', status: 'active' } 
    }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Alert -->
            @if (session('success'))
                <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-xl shadow-xs flex items-center justify-between text-emerald-900 text-sm font-medium">
                    <div class="flex items-center gap-2">
                        <span>✅</span>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="p-4 bg-rose-50 border-l-4 border-rose-500 rounded-r-xl shadow-xs flex items-center justify-between text-rose-900 text-sm font-medium">
                    <div class="flex items-center gap-2">
                        <span>⚠️</span>
                        <span>{{ session('error') }}</span>
                    </div>
                </div>
            @endif

            <!-- Filter Instansi jika Super Admin -->
            @if($isSuperAdmin)
                <div class="bg-white rounded-2xl border border-gray-100 p-4 shadow-xs">
                    <form method="GET" action="{{ route('admin.mentors.index') }}" class="flex items-center gap-3">
                        <select name="agency_id" onchange="this.form.submit()" class="text-xs rounded-xl border-gray-200 shadow-2xs font-semibold focus:ring-blue-500 focus:border-blue-500">
                            <option value="">🏢 Semua Instansi Dinas</option>
                            @foreach($agencies as $ag)
                                <option value="{{ $ag->id }}" {{ request('agency_id') == $ag->id ? 'selected' : '' }}>
                                    {{ $ag->agency_name }}
                                </option>
                            @endforeach
                        </select>

                        <div class="relative flex-1">
                            <div class="absolute inset-y-0 left-0 flex items-center pointer-events-none text-slate-400" style="padding-left: 1rem !important;">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau email mentor..." 
                                   class="w-full text-xs sm:text-sm border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs"
                                   style="padding-left: 2.75rem !important; padding-right: 1rem !important; padding-top: 0.65rem !important; padding-bottom: 0.65rem !important;">
                        </div>

                        <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-sm transition cursor-pointer">
                            Cari
                        </button>
                    </form>
                </div>
            @endif

            <!-- Mentors Table -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 text-left text-xs">
                        <thead class="bg-gray-50/75 text-gray-500 font-bold uppercase tracking-wider text-[11px]">
                            <tr>
                                <th class="py-3.5 px-4">Nama Mentor</th>
                                <th class="py-3.5 px-4">Email Resmi / Login</th>
                                <th class="py-3.5 px-4">Instansi Dinas</th>
                                <th class="py-3.5 px-4 text-center">Beban Bimbingan</th>
                                <th class="py-3.5 px-4 text-center">Status</th>
                                <th class="py-3.5 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($mentors as $m)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-4 px-4 font-bold text-gray-900 text-xs sm:text-sm">
                                        👔 {{ $m->name }}
                                    </td>
                                    <td class="py-4 px-4 font-mono text-gray-600">
                                        {{ $m->email }}
                                    </td>
                                    <td class="py-4 px-4 text-gray-700">
                                        {{ $m->agencyProfile?->agency_name ?? '-' }}
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        <div class="inline-flex items-center gap-1.5 justify-center flex-wrap">
                                            <span class="px-2 py-0.5 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                🟢 {{ $m->active_students_count }} Aktif
                                            </span>
                                            <span class="px-2 py-0.5 rounded-lg text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                                🎓 {{ $m->completed_students_count }} Lulus
                                            </span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-4 text-center whitespace-nowrap">
                                        @if($m->status === 'on_leave')
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">Cuti</span>
                                        @elseif($m->status === 'inactive')
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">Non-Aktif</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Aktif</span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-4 text-right whitespace-nowrap">
                                        <div class="btn-action-group">
                                            
                                            <!-- Edit -->
                                            <button type="button" 
                                                    @click="editMentor = { 
                                                        id: '{{ $m->id }}', 
                                                        name: '{{ addslashes($m->name) }}', 
                                                        email: '{{ addslashes($m->email) }}', 
                                                        agency_profile_id: '{{ $m->agency_profile_id }}', 
                                                        status: '{{ $m->status ?? 'active' }}' 
                                                    }; showEditModal = true"
                                                    class="btn-action-edit">
                                                Edit
                                            </button>

                                            <!-- Reset Password -->
                                            <form action="{{ route('admin.mentors.reset_password', $m->id) }}" method="POST" onsubmit="return confirm('Reset password mentor {{ $m->name }} ke default (password)?');" class="btn-action-form">
                                                @csrf
                                                <button type="submit" class="btn-action-reset">
                                                    Reset
                                                </button>
                                            </form>

                                            <!-- Hapus -->
                                            <form action="{{ route('admin.mentors.destroy', $m->id) }}" method="POST" onsubmit="return confirm('Hapus mentor {{ $m->name }}?');" class="btn-action-form">
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
                                        Belum ada data mentor lapangan terdaftar.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- MODAL 1: TAMBAH MENTOR -->
        <div x-show="showCreateModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full p-6 border border-gray-100 relative" @click.away="showCreateModal = false">
                <div class="flex justify-between items-center border-b border-gray-100 pb-3 mb-4">
                    <div class="flex items-center gap-2">
                        <span class="text-xl">👔</span>
                        <h3 class="font-bold text-base text-gray-900">Tambah Mentor Lapangan Baru</h3>
                    </div>
                    <button type="button" @click="showCreateModal = false" class="text-gray-400 hover:text-gray-600 text-lg font-bold">✕</button>
                </div>

                <form method="POST" action="{{ route('admin.mentors.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                            Nama Lengkap & Gelar <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="name" required placeholder="Contoh: Ir. Siti Aminah, M.Kom" class="w-full text-xs sm:text-sm border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                            Email Kedinasan / Akun Login <span class="text-rose-500">*</span>
                        </label>
                        <input type="email" name="email" required placeholder="mentor.kominfo@surabaya.go.id" class="w-full text-xs sm:text-sm border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs font-mono">
                    </div>

                    @if($isSuperAdmin)
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                Instansi Dinas <span class="text-rose-500">*</span>
                            </label>
                            <select name="agency_profile_id" required class="w-full text-xs sm:text-sm border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs">
                                <option value="">-- Pilih Dinas --</option>
                                @foreach($agencies as $ag)
                                    <option value="{{ $ag->id }}">{{ $ag->agency_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100">
                        <button type="button" @click="showCreateModal = false" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-xl transition">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-md transition active:scale-95 cursor-pointer">
                            Simpan Mentor
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL 2: EDIT MENTOR -->
        <div x-show="showEditModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full p-6 border border-gray-100 relative" @click.away="showEditModal = false">
                <div class="flex justify-between items-center border-b border-gray-100 pb-3 mb-4">
                    <div class="flex items-center gap-2">
                        <span class="text-xl">✏️</span>
                        <h3 class="font-bold text-base text-gray-900">Edit Data Mentor Lapangan</h3>
                    </div>
                    <button type="button" @click="showEditModal = false" class="text-gray-400 hover:text-gray-600 text-lg font-bold">✕</button>
                </div>

                <form method="POST" :action="'/admin/mentors/' + editMentor.id" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                            Nama Lengkap & Gelar <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="name" x-model="editMentor.name" required class="w-full text-xs sm:text-sm border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                            Email Kedinasan / Login <span class="text-rose-500">*</span>
                        </label>
                        <input type="email" name="email" x-model="editMentor.email" required class="w-full text-xs sm:text-sm border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs font-mono">
                    </div>

                    @if($isSuperAdmin)
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                Instansi Dinas <span class="text-rose-500">*</span>
                            </label>
                            <select name="agency_profile_id" x-model="editMentor.agency_profile_id" required class="w-full text-xs sm:text-sm border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs">
                                @foreach($agencies as $ag)
                                    <option value="{{ $ag->id }}">{{ $ag->agency_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                            Status Keaktifan
                        </label>
                        <select name="status" x-model="editMentor.status" required class="w-full text-xs sm:text-sm border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs font-medium">
                            <option value="active">🟢 Aktif (Tersedia Membimbing)</option>
                            <option value="on_leave">🟡 Cuti (Tidak Menerima Bimbingan)</option>
                            <option value="inactive">🔴 Non-Aktif</option>
                        </select>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100">
                        <button type="button" @click="showEditModal = false" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-xl transition">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-md transition active:scale-95 cursor-pointer">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
