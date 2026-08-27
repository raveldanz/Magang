<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.universities.index') }}" class="p-2 rounded-xl bg-white border border-gray-200 text-gray-600 hover:text-gray-900 transition">
                
            </a>
            <div>
                <h2 class="font-black text-xl text-gray-900 tracking-tight">
                    Edit Data Universitas: {{ $university->name }}
                </h2>
                <p class="text-xs text-gray-500">Perbarui identitas kampus, kode, dan pejabat penandatangan</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-xs p-6 sm:p-8">

                <form method="POST" action="{{ route('admin.universities.update', $university->id) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                Nama Resmi Perguruan Tinggi <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="name" value="{{ old('name', $university->name) }}" required class="w-full text-xs sm:text-sm border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                Kode / Singkatan <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="code" value="{{ old('code', $university->code) }}" required class="w-full text-xs sm:text-sm border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs font-mono uppercase">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                Email Resmi Kampus
                            </label>
                            <input type="email" name="email" value="{{ old('email', $university->email) }}" class="w-full text-xs sm:text-sm border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs font-mono">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                Nomor Telepon / Fax
                            </label>
                            <input type="text" name="phone" value="{{ old('phone', $university->phone) }}" class="w-full text-xs sm:text-sm border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                            Alamat Kampus Utama
                        </label>
                        <textarea name="address" rows="2" class="w-full text-xs sm:text-sm border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs">{{ old('address', $university->address) }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                Nama Rektor / Pimpinan (PIC)
                            </label>
                            <input type="text" name="pic_name" value="{{ old('pic_name', $university->pic_name) }}" class="w-full text-xs sm:text-sm border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                NIP / NIDN Rektor
                            </label>
                            <input type="text" name="pic_nip" value="{{ old('pic_nip', $university->pic_nip) }}" class="w-full text-xs sm:text-sm border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs font-mono">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                Jabatan Penandatangan
                            </label>
                            <input type="text" name="pic_position" value="{{ old('pic_position', $university->pic_position) }}" class="w-full text-xs sm:text-sm border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs">
                        </div>
                    </div>

                    <!-- Skema Kebijakan & Pembobotan Nilai Kampus Adaptif -->
                    <div x-data="{ 
                            scheme: '{{ old('evaluation_scheme', $university->evaluation_scheme ?? 'dual_evaluation') }}',
                            weightMentor: {{ old('weight_mentor', $university->weight_mentor ?? 40) }},
                            weightLecturer: {{ old('weight_lecturer', $university->weight_lecturer ?? 60) }},
                            updateLecturer() {
                                this.weightLecturer = Math.max(0, Math.min(100, 100 - this.weightMentor));
                            },
                            updateMentor() {
                                this.weightMentor = Math.max(0, Math.min(100, 100 - this.weightLecturer));
                            }
                         }" 
                         class="p-5 rounded-2xl bg-slate-50 border border-slate-200 space-y-4">
                        
                        <div class="border-b border-slate-200 pb-2.5 flex items-center justify-between">
                            <h4 class="font-bold text-xs sm:text-sm text-gray-900 flex items-center gap-1.5">
                                <span>⚙️</span>
                                <span>Skema Kebijakan Evaluasi & Pembobotan Kampus</span>
                            </h4>
                            <span class="text-[11px] font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-lg border border-blue-200">
                                Fleksibel Multi-Kampus
                            </span>
                        </div>

                        <!-- Skema Radio -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                            <label class="p-3 rounded-xl border cursor-pointer flex items-start gap-2.5 transition"
                                   :class="scheme === 'dual_evaluation' ? 'border-blue-600 bg-white shadow-xs' : 'border-slate-200 hover:border-slate-300'">
                                <input type="radio" name="evaluation_scheme" value="dual_evaluation" x-model="scheme" class="mt-0.5 text-blue-600">
                                <div>
                                    <strong class="text-gray-900 block">⚖️ Kemitraan Dua Pihak (Standar)</strong>
                                    <span class="text-gray-500 text-[11px] block mt-0.5">Dinilai Mentor Dinas & DPL. Logbook diverifikasi 2 arah.</span>
                                </div>
                            </label>

                            <label class="p-3 rounded-xl border cursor-pointer flex items-start gap-2.5 transition"
                                   :class="scheme === 'mentor_only' ? 'border-blue-600 bg-white shadow-xs' : 'border-slate-200 hover:border-slate-300'">
                                <input type="radio" name="evaluation_scheme" value="mentor_only" x-model="scheme" class="mt-0.5 text-blue-600">
                                <div>
                                    <strong class="text-gray-900 block">🏢 Penilaian Penuh Dinas (100%)</strong>
                                    <span class="text-gray-500 text-[11px] block mt-0.5">100% dinilai Dinas. Logbook cukup di-ACC Mentor.</span>
                                </div>
                            </label>
                        </div>

                        <!-- Range Slider jika Dual Evaluation -->
                        <div x-show="scheme === 'dual_evaluation'" class="space-y-3 pt-2">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="space-y-1">
                                    <div class="flex justify-between text-xs font-semibold text-gray-700">
                                        <span>Bobot Dinas:</span>
                                        <span class="font-mono text-blue-600 font-bold" x-text="weightMentor + '%'"></span>
                                    </div>
                                    <input type="range" min="0" max="100" step="5" x-model="weightMentor" @input="updateLecturer()" class="w-full h-2 bg-gray-200 rounded-lg accent-blue-600">
                                    <input type="number" name="weight_mentor" min="0" max="100" x-model="weightMentor" @input="updateLecturer()" class="w-full text-xs border-gray-300 rounded-xl font-mono text-center font-bold">
                                </div>

                                <div class="space-y-1">
                                    <div class="flex justify-between text-xs font-semibold text-gray-700">
                                        <span>Bobot DPL Kampus:</span>
                                        <span class="font-mono text-purple-600 font-bold" x-text="weightLecturer + '%'"></span>
                                    </div>
                                    <input type="range" min="0" max="100" step="5" x-model="weightLecturer" @input="updateMentor()" class="w-full h-2 bg-gray-200 rounded-lg accent-purple-600">
                                    <input type="number" name="weight_lecturer" min="0" max="100" x-model="weightLecturer" @input="updateMentor()" class="w-full text-xs border-gray-300 rounded-xl font-mono text-center font-bold">
                                </div>
                            </div>

                            <div class="pt-1 flex items-center gap-2">
                                <input type="checkbox" name="require_dpl" value="1" id="admin_require_dpl" {{ old('require_dpl', $university->require_dpl ?? true) ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600">
                                <label for="admin_require_dpl" class="text-xs text-gray-700 font-medium">
                                    Kunci pengisian logbook mahasiswa hingga memilih DPL.
                                </label>
                            </div>
                        </div>

                    </div>

                    <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-100">
                        <a href="{{ route('admin.universities.index') }}" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-xl transition">
                            Batal
                        </a>
                        <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-md transition active:scale-95 cursor-pointer">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
