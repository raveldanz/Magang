<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ url()->previous() }}" class="p-2 rounded-xl bg-white border border-slate-200 text-slate-600 hover:text-slate-900 transition shadow-2xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <h2 class="font-black text-xl sm:text-2xl text-gray-900 tracking-tight">
                    💬 Kirim Laporan Kendala, Masukan, atau Pertanyaan
                </h2>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">
                    Laporkan kendala/bug sistem, ajukan saran perbaikan, atau sampaikan pertanyaan ke Super Admin / Instansi Dinas / Universitas
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if ($errors->any())
                <div class="p-4 bg-rose-50 border-l-4 border-rose-500 rounded-r-2xl shadow-xs text-rose-900 text-xs">
                    <p class="font-bold mb-1">Mohon perbaiki kesalahan berikut:</p>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('feedbacks.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-100 shadow-sm space-y-6">
                    
                    <div class="border-b border-slate-100 pb-4">
                        <h3 class="font-black text-base text-gray-900">Informasi Masukan / Laporan</h3>
                        <p class="text-xs text-gray-400">Pilih kategori dan tujuan penerima laporan Anda</p>
                    </div>

                    <!-- Kategori & Target Role (Alpine.js Dynamic Form) -->
                    <div x-data="{ targetRole: '{{ old('target_role', 'super_admin') }}' }" class="space-y-5">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                                    Kategori Laporan <span class="text-rose-500">*</span>
                                </label>
                                <select name="category" required class="w-full text-xs sm:text-sm border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs">
                                    <option value="error_bug" {{ old('category') == 'error_bug' ? 'selected' : '' }}>⚠️ Kendala / Error / Bug Sistem</option>
                                    <option value="saran_fitur" {{ old('category') == 'saran_fitur' ? 'selected' : '' }}>💡 Usulan / Saran Perbaikan Fitur</option>
                                    <option value="pertanyaan" {{ old('category') == 'pertanyaan' ? 'selected' : '' }}>❓ Pertanyaan Seputar Alur MBKM</option>
                                    <option value="koordinasi" {{ old('category') == 'koordinasi' ? 'selected' : '' }}>🤝 Koordinasi Kampus / Instansi</option>
                                    <option value="lainnya" {{ old('category') == 'lainnya' ? 'selected' : '' }}>📝 Lain-lain</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                                    Ditujukan Kepada <span class="text-rose-500">*</span>
                                </label>
                                <select name="target_role" x-model="targetRole" class="w-full text-xs sm:text-sm border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs">
                                    <option value="super_admin">👑 Super Administrator (Pengelola Pusat Pemkot)</option>
                                    <option value="admin_dinas">🏢 Admin Instansi Kedinasan</option>
                                    <option value="universitas">🎓 Pihak Perguruan Tinggi (Universitas)</option>
                                </select>
                            </div>
                        </div>

                        <!-- Dynamic Specific Target & Priority -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <!-- Case 1: Super Admin -->
                            <div x-show="targetRole === 'super_admin'" class="p-3.5 rounded-2xl bg-blue-50 border border-blue-100 flex items-center gap-3">
                                <span class="text-xl">👑</span>
                                <div>
                                    <div class="text-xs font-bold text-blue-950">Pengelola Pusat (Super Admin)</div>
                                    <div class="text-[11px] text-blue-700">Laporan ditangani langsung oleh Administrator Utama Pemkot Surabaya</div>
                                </div>
                            </div>

                            <!-- Case 2: Admin Dinas -->
                            <div x-show="targetRole === 'admin_dinas'" x-cloak>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                                    Pilih Instansi Kedinasan Terkait <span class="text-rose-500">*</span>
                                </label>
                                <select name="target_agency_id" class="w-full text-xs sm:text-sm border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs">
                                    <option value="">-- Pilih Dinas / Badan Terkait --</option>
                                    @foreach($agencies as $ag)
                                        <option value="{{ $ag->id }}" {{ old('target_agency_id') == $ag->id ? 'selected' : '' }}>{{ $ag->agency_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Case 3: Universitas -->
                            <div x-show="targetRole === 'universitas'" x-cloak>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                                    Pilih Perguruan Tinggi / Kampus Terkait <span class="text-rose-500">*</span>
                                </label>
                                <select name="target_university_id" class="w-full text-xs sm:text-sm border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs">
                                    <option value="">-- Pilih Perguruan Tinggi --</option>
                                    @foreach($universities as $u)
                                        <option value="{{ $u->id }}" {{ old('target_university_id') == $u->id ? 'selected' : '' }}>{{ $u->name }} ({{ $u->code }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Priority Selector -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                                    Tingkat Prioritas
                                </label>
                                <select name="priority" class="w-full text-xs sm:text-sm border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs">
                                    <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>🟢 Rendah (Informasi umum)</option>
                                    <option value="medium" {{ old('priority', 'medium') == 'medium' ? 'selected' : '' }}>🟡 Sedang (Pertanyaan standar)</option>
                                    <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>🟠 Tinggi (Menghambat aktivitas)</option>
                                    <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>🔴 Mendesak (Error sistem kritis)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Judul Subjek -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Judul / Subjek Laporan <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="subject" value="{{ old('subject') }}" required 
                               placeholder="Contoh: Terjadi kendala saat upload logbook / Usulan penambahan fitur unduh nilai" 
                               class="w-full text-xs sm:text-sm border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs">
                    </div>

                    <!-- Isi Pesan -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Detail Isi Masukan / Kronologi Kendala <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="message" rows="5" required 
                                  placeholder="Jelaskan secara rinci kendala yang dialami, halaman di mana error muncul, atau masukan yang ingin Anda sampaikan..." 
                                  class="w-full text-xs sm:text-sm border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs">{{ old('message') }}</textarea>
                    </div>

                    <!-- Lampiran File -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Lampiran Bukti / Screenshot (Opsional)
                        </label>
                        <input type="file" name="attachment" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.zip" 
                               class="w-full text-xs sm:text-sm border border-slate-200 rounded-xl file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 shadow-2xs cursor-pointer">
                        <p class="text-[11px] text-slate-400 mt-1">Format yang didukung: JPG, PNG, PDF, DOCX, ZIP (Maksimal 5MB)</p>
                    </div>

                    <!-- Info Pengirim -->
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-between text-xs">
                        <div class="flex items-center gap-2">
                            <span>👤</span>
                            <span class="text-slate-600">Pengirim: <strong>{{ $user->name }}</strong> ({{ $user->email }})</span>
                        </div>
                        <span class="px-2.5 py-0.5 bg-blue-100 text-blue-800 rounded-full font-bold text-[10px] uppercase">
                            Role: {{ $user->role }}
                        </span>
                    </div>

                </div>

                <!-- Submit Button -->
                <div class="flex items-center justify-between">
                    <a href="{{ route('feedbacks.my') }}" class="text-xs font-bold text-slate-500 hover:text-slate-800">
                        &larr; Lihat Riwayat Laporan Saya
                    </a>

                    <button type="submit" class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-bold rounded-2xl shadow-md transition cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                        <span>Kirim Laporan Sekarang</span>
                    </button>
                </div>
            </form>

        </div>
    </div>
</x-app-layout>
