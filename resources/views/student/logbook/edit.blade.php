<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Logbook Magang') }}
        </h2>
    </x-slot>

<<<<<<< HEAD
    <div class="py-10">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl border border-slate-100 shadow-[0_4px_12px_rgba(100,116,139,0.08)] p-8">

                <div class="mb-6 pb-4 border-b border-slate-100">
                    <h3 class="text-lg font-bold text-slate-800">Edit Catatan Kegiatan</h3>
                    <p class="text-sm text-slate-500 mt-1">Perbarui logbook kegiatan magang kamu. Hanya logbook dengan status PENDING/REJECTED yang bisa diedit.</p>
                </div>

                {{-- Alert Error Validasi --}}
                @if ($errors->any())
                    <div class="p-4 mb-6 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-xl text-sm">
=======
    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow-sm p-8 border border-gray-100">

                <div class="mb-6 pb-4 border-b border-gray-100">
                    <h3 class="text-lg font-bold text-gray-800">✏️ Edit Catatan Kegiatan</h3>
                    <p class="text-sm text-gray-500 mt-1">Perbarui logbook kegiatan magang Anda. Hanya logbook dengan status PENDING/REJECTED yang bisa diedit.</p>
                </div>

                {{-- Catatan Feedback Pembimbing --}}
                @php
                    $mentorFeedback = $logbook->mentor_feedback ?? $logbook->feedback;
                    $lecturerFeedback = $logbook->lecturer_feedback;
                @endphp

                @if($mentorFeedback || $lecturerFeedback)
                    <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50/70 p-4">
                        <h4 class="flex items-center gap-2 text-sm font-bold text-rose-800">
                            <span>⚠️ Catatan Revisi & Feedback Pembimbing</span>
                        </h4>
                        
                        @if($mentorFeedback)
                            <div class="mt-3 rounded-lg bg-white p-3 border border-rose-100 shadow-sm">
                                <span class="text-xs font-semibold text-rose-600 block">Feedback Mentor Dinas:</span>
                                <p class="text-sm text-slate-700 mt-1 whitespace-pre-line">{{ $mentorFeedback }}</p>
                            </div>
                        @endif

                        @if($lecturerFeedback)
                            <div class="mt-2 rounded-lg bg-white p-3 border border-rose-100 shadow-sm">
                                <span class="text-xs font-semibold text-rose-600 block">Feedback Dosen Pembimbing (DPL):</span>
                                <p class="text-sm text-slate-700 mt-1 whitespace-pre-line">{{ $lecturerFeedback }}</p>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Alert Error Validasi --}}
                @if ($errors->any())
                    <div class="p-4 mb-6 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-lg text-sm">
>>>>>>> main
                        <p class="font-bold mb-1">Gagal Menyimpan Perubahan:</p>
                        <ul class="list-disc list-inside space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- FORM EDIT (Mengirim PUT ke route update) --}}
                <form action="{{ route('student.logbook.update', $logbook->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    @method('PUT')

                    {{-- Tanggal --}}
                    <div>
<<<<<<< HEAD
                        <label for="date" class="block font-semibold text-sm text-slate-700 mb-1">Tanggal Kegiatan</label>
                        <input type="date" id="date" name="date" value="{{ old('date', $logbook->date) }}" class="w-full rounded-xl border-slate-200 focus:border-slate-500 focus:ring-slate-500 shadow-sm text-sm" required>
=======
                        <label for="date" class="block font-semibold text-sm text-gray-700 mb-1">Tanggal Kegiatan</label>
                        <input type="date" id="date" name="date" value="{{ old('date', $logbook->date) }}" class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm text-sm" required>
>>>>>>> main
                    </div>

                    {{-- Deskripsi Kegiatan --}}
                    <div>
<<<<<<< HEAD
                        <label for="activity" class="block font-semibold text-sm text-slate-700 mb-1">Deskripsi Kegiatan</label>
                        <textarea id="activity" name="activity" rows="5" class="w-full rounded-xl border-slate-200 focus:border-slate-500 focus:ring-slate-500 shadow-sm text-sm" required placeholder="Tuliskan secara detail kegiatan magang kamu hari ini...">{{ old('activity', $logbook->activity) }}</textarea>
                        <p class="text-xs text-slate-400 mt-1">Minimal 10 karakter</p>
=======
                        <label for="activity" class="block font-semibold text-sm text-gray-700 mb-1">Deskripsi Kegiatan</label>
                        <textarea id="activity" name="activity" rows="5" class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm text-sm" required placeholder="Tuliskan secara detail kegiatan magang Anda hari ini...">{{ old('activity', $logbook->activity) }}</textarea>
                        <p class="text-xs text-gray-400 mt-1">Minimal 10 karakter</p>
>>>>>>> main
                    </div>

                    {{-- Lampiran --}}
                    <div>
<<<<<<< HEAD
                        <label for="attachment" class="block font-semibold text-sm text-slate-700 mb-1">Lampiran (Opsional)</label>
                        @if ($logbook->attachment)
                            <p class="text-xs text-slate-600 mb-2 font-medium">
                                File saat ini: <a href="{{ asset('storage/' . $logbook->attachment) }}" target="_blank" class="underline text-slate-700 hover:text-slate-900">Lihat File</a>
                            </p>
                        @endif
                        <input id="attachment" name="attachment" type="file" accept=".pdf,.jpg,.jpeg,.png" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 cursor-pointer" />
                        <p class="text-xs text-slate-400 mt-1">Format: PDF, JPG, PNG. Maksimal 2MB.</p>
                    </div>

                    {{-- Tombol Submit --}}
                    <div class="flex items-center space-x-3 pt-4 border-t border-slate-100">
                        <button type="submit" class="px-6 py-2.5 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold uppercase tracking-wider transition-colors">
                            Simpan Perubahan
                        </button>

                        <a href="{{ route('student.logbook.index') }}" class="px-5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-700 uppercase tracking-wider hover:bg-slate-50 transition-colors">
                            Batal
=======
                        <label for="attachment" class="block font-semibold text-sm text-gray-700 mb-1">Lampiran (Opsional)</label>
                        @if ($logbook->attachment)
                            <p class="text-xs text-blue-600 mb-2 font-medium">
                                📄 File saat ini: <a href="{{ asset('storage/' . $logbook->attachment) }}" target="_blank" class="underline hover:text-blue-800">Lihat File</a>
                            </p>
                        @endif
                        <input id="attachment" name="attachment" type="file" accept=".pdf,.jpg,.jpeg,.png" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer" />
                        <p class="text-xs text-gray-400 mt-1">Format: PDF, JPG, PNG. Maksimal 2MB.</p>
                    </div>

                    {{-- Tombol Submit --}}
                    <div class="flex items-center space-x-3 pt-4 border-t border-gray-100">
                        <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold uppercase tracking-wider transition cursor-pointer shadow-md">
                            SIMPAN PERUBAHAN
                        </button>

                        <a href="{{ route('student.logbook.index') }}" class="px-5 py-2.5 bg-gray-100 border border-gray-300 rounded-xl text-xs font-bold text-gray-700 uppercase tracking-wider hover:bg-gray-200 transition">
                            BATAL
>>>>>>> main
                        </a>
                    </div>
                </form>

            </div>
        </div>
    </div>
<<<<<<< HEAD
</x-app-layout>
=======
</x-app-layout>
>>>>>>> main
