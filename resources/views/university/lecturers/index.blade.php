<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight flex items-center gap-2">
                    <svg class="w-7 h-7 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    {{ __('Daftar Dosen Pembimbing Lapangan (DPL)') }}
                </h2>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">
                    👨‍🏫 Portal Pengelolaan Dosen Pembimbing Kampus &bull; <strong>{{ $univName ?? 'Universitas' }}</strong>
                </p>
            </div>

            <div class="flex items-center gap-2">
                <span class="bg-indigo-50 border border-indigo-200 text-indigo-700 text-xs font-semibold px-3 py-1.5 rounded-xl">
                    🎓 {{ $univName ?? 'Kampus Mitra' }}
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
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

            <!-- Summary KPI Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-xs">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Dosen Terdaftar</p>
                    <h3 class="text-2xl font-black text-indigo-600 mt-1">{{ $stats['total_lecturers'] }}</h3>
                    <p class="text-xs text-gray-500 mt-1">Dosen pembimbing di kampus Anda</p>
                </div>

                <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-xs">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Mahasiswa Sedang Dibimbing</p>
                    <h3 class="text-2xl font-black text-emerald-600 mt-1">{{ $stats['total_active_students'] }}</h3>
                    <p class="text-xs text-gray-500 mt-1">Mahasiswa aktif magang di dinas</p>
                </div>

                <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-xs">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Mahasiswa Telah Lulus</p>
                    <h3 class="text-2xl font-black text-purple-600 mt-1">{{ $stats['total_completed_students'] }}</h3>
                    <p class="text-xs text-gray-500 mt-1">Bimbingan selesai & bernilai akhir</p>
                </div>
            </div>

            <!-- Search Bar -->
            <div class="bg-white rounded-2xl p-4 border border-gray-200 shadow-xs flex flex-col md:flex-row justify-between items-center gap-4">
                <form method="GET" action="{{ route('university.lecturers.index') }}" class="w-full md:w-80 flex items-center gap-2">
                    <div class="relative w-full">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama / email dosen..." class="w-full text-xs border-gray-300 rounded-xl pl-9 focus:ring-indigo-500 focus:border-indigo-500 shadow-xs">
                        <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <button type="submit" class="px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-xs transition shrink-0">
                        Cari
                    </button>
                </form>

                <p class="text-xs text-gray-500">
                    Menampilkan <strong>{{ $lecturers->count() }}</strong> dosen terdaftar di <strong>{{ $univName }}</strong>
                </p>
            </div>

            <!-- Table Daftar Dosen -->
            <div class="bg-white overflow-hidden shadow-xs rounded-2xl border border-gray-200">
                <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Daftar Dosen Pembimbing Lapangan</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Dosen yang ditugaskan memonitor logbook mingguan dan memberikan penilaian akhir akademik</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/75 border-b border-gray-200 text-[11px] font-bold text-gray-500 uppercase tracking-wider">
                                <th class="py-3.5 px-4 text-center w-12">No</th>
                                <th class="py-3.5 px-4">Nama Lengkap & Gelar</th>
                                <th class="py-3.5 px-4">Email Resmi / Login</th>
                                <th class="py-3.5 px-4 text-center">Mahasiswa Bimbingan</th>
                                <th class="py-3.5 px-4 text-center">Status Akun</th>
                                <th class="py-3.5 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            @forelse ($lecturers as $index => $lecturer)
                                <tr class="hover:bg-slate-50/75 transition-colors">
                                    
                                    <!-- No -->
                                    <td class="py-4 px-4 text-center text-xs font-semibold text-gray-500">
                                        {{ $index + 1 }}
                                    </td>

                                    <!-- Nama Lengkap & Gelar -->
                                    <td class="py-4 px-4">
                                        <div class="font-bold text-gray-900 leading-snug flex items-center gap-2">
                                            <span class="w-8 h-8 rounded-full bg-indigo-50 text-indigo-700 flex items-center justify-center font-bold text-xs shrink-0 border border-indigo-100">
                                                👨‍🏫
                                            </span>
                                            <div>
                                                <div>{{ $lecturer->name }}</div>
                                                <div class="text-xs text-gray-400 font-normal">Peran: {{ strtoupper($lecturer->role) }}</div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Email Resmi -->
                                    <td class="py-4 px-4 font-mono text-xs text-gray-700">
                                        <span class="bg-gray-100 px-2 py-1 rounded-md">{{ $lecturer->email }}</span>
                                    </td>

                                    <!-- Jumlah Mahasiswa Bimbingan (Aktif & Selesai) -->
                                    <td class="py-4 px-4 text-center">
                                        <div class="inline-flex items-center gap-1.5 flex-wrap justify-center">
                                            <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-bold rounded-lg" title="Mahasiswa Aktif Bimbingan">
                                                🟢 {{ $lecturer->active_students_count }} Aktif
                                            </span>
                                            <span class="px-2 py-0.5 bg-purple-50 text-purple-700 border border-purple-200 text-xs font-bold rounded-lg" title="Mahasiswa Telah Lulus">
                                                🎓 {{ $lecturer->completed_students_count }} Lulus
                                            </span>
                                        </div>
                                    </td>

                                    <!-- Status Akun -->
                                    <td class="py-4 px-4 text-center">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                            Aktif
                                        </span>
                                    </td>

                                    <!-- Aksi: Reset Password ke Default -->
                                    <td class="py-4 px-4 text-right">
                                        <form action="{{ route('university.lecturers.reset_password', $lecturer->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mereset password untuk {{ $lecturer->name }} ke default (password)?');">
                                            @csrf
                                            <button type="submit" 
                                                    title="Reset password dosen ke default: 'password'"
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 rounded-lg text-xs font-bold transition shadow-2xs active:scale-95">
                                                <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                                </svg>
                                                Reset Password
                                            </button>
                                        </form>
                                    </td>

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-12 text-center text-gray-400">
                                        <p class="font-medium text-gray-600">Belum Ada Dosen Pembimbing Terdaftar</p>
                                        <p class="text-xs text-gray-400 mt-1">Dosen akan terdaftar otomatis saat mahasiswa memilih atau mendaftarkan DPL mereka.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
