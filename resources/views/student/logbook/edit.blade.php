<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-xl sm:text-2xl text-slate-900 tracking-tight flex items-center gap-2.5">
                <span>Edit Catatan Logbook Magang</span>
            </h2>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8 lg:py-10">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- 1. Catatan Revisi & Feedback Pembimbing --}}
            @php
                $mentorFeedback = $logbook->mentor_feedback ?? $logbook->feedback;
                $lecturerFeedback = $logbook->lecturer_feedback;
            @endphp

            @if($mentorFeedback || $lecturerFeedback)
                <div class="rounded-3xl border border-rose-200/90 bg-rose-50/70 p-5 sm:p-6 space-y-3.5 shadow-2xs">
                    <div class="flex items-center gap-2 text-rose-900 font-bold text-sm">
                        <div class="w-7 h-7 rounded-xl bg-rose-500 text-white flex items-center justify-center shrink-0 shadow-xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        </div>
                        <span>Catatan Perbaikan / Masukan Revisi</span>
                    </div>
                    
                    @if($mentorFeedback)
                        <div class="rounded-2xl bg-white p-4 border border-rose-100 shadow-2xs space-y-1">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-rose-700 block">Masukan Mentor Instansi Dinas:</span>
                            <p class="text-xs sm:text-sm text-slate-700 leading-relaxed italic whitespace-pre-line">"{{ $mentorFeedback }}"</p>
                        </div>
                    @endif

                    @if($lecturerFeedback)
                        <div class="rounded-2xl bg-white p-4 border border-rose-100 shadow-2xs space-y-1">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-rose-700 block">Masukan Dosen Pembimbing Lapangan:</span>
                            <p class="text-xs sm:text-sm text-slate-700 leading-relaxed italic whitespace-pre-line">"{{ $lecturerFeedback }}"</p>
                        </div>
                    @endif
                </div>
            @endif

            {{-- 2. Alert Error Validasi --}}
            @if ($errors->any())
                <div class="p-4 bg-rose-50 border-l-4 border-rose-500 rounded-r-2xl shadow-2xs text-rose-900 text-xs space-y-1">
                    <p class="font-bold text-sm">Gagal Menyimpan Perubahan:</p>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- 3. Form Card Utama --}}
            <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs space-y-6">
                
                <div class="border-b border-slate-100 pb-4">
                    <h3 class="text-base sm:text-lg font-bold text-slate-900">Perbarui Catatan Kegiatan Harian</h3>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                        Perubahan pada logbook dengan status ditolak akan mengirimkan ulang data untuk ditinjau kembali.
                    </p>
                </div>

                <form action="{{ route('student.logbook.update', $logbook->id) }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                    @csrf
                    @method('PUT')

                    {{-- Tanggal Kegiatan --}}
                    <div>
                        <label for="date" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Tanggal Kegiatan <span class="text-rose-500">*</span>
                        </label>
                        <input type="date" id="date" name="date" value="{{ old('date', $logbook->date) }}" 
                               class="w-full text-xs sm:text-sm border-slate-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs" required>
                        <x-input-error :messages="$errors->get('date')" class="mt-1" />
                    </div>

                    {{-- Deskripsi Kegiatan --}}
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="activity" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                Uraian Aktivitas Magang <span class="text-rose-500">*</span>
                            </label>
                            <span class="text-[11px] text-slate-400">Minimal 10 karakter</span>
                        </div>
                        <textarea id="activity" name="activity" rows="5" 
                                  placeholder="Tuliskan secara terperinci apa saja aktivitas, capaian tugas, dan pembelajaran Anda..." 
                                  class="w-full text-xs sm:text-sm border-slate-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs leading-relaxed" required>{{ old('activity', $logbook->activity) }}</textarea>
                        <x-input-error :messages="$errors->get('activity')" class="mt-1" />
                    </div>

                    {{-- Lampiran Bukti Kegiatan (Konsisten dengan Profil & Pengajuan) --}}
                    @php
                        $hasAttachment = !empty($logbook?->attachment_path ?? $logbook?->attachment);
                        $attachmentPath = $logbook?->attachment_path ?? $logbook?->attachment;
                        $savedName = $hasAttachment ? basename($attachmentPath) : '';
                        $savedUrl = $hasAttachment ? ($logbook->attachment_url ?? asset('storage/' . $attachmentPath)) : null;
                    @endphp

                    <div class="space-y-2" 
                         x-data="{ 
                            selectedName: '', 
                            previewUrl: null 
                         }">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                            Lampiran Bukti Kegiatan (Opsional)
                        </label>

                        <div class="flex items-center gap-2 w-full">
                            <input type="file" 
                                   id="attachment" 
                                   name="attachment" 
                                   accept=".pdf,.jpg,.jpeg,.png" 
                                   class="hidden"
                                   @change="if($event.target.files.length) { 
                                       const file = $event.target.files[0];
                                       if(file.size > 2 * 1024 * 1024) {
                                           alert('Ukuran berkas lampiran maksimal 2MB.');
                                           $event.target.value = '';
                                           selectedName = '';
                                           previewUrl = null;
                                           return;
                                       }
                                       selectedName = file.name; 
                                       previewUrl = URL.createObjectURL(file); 
                                   }" />

                            <!-- Tombol Choose File Mandiri -->
                            <button type="button" 
                                    @click="document.getElementById('attachment').click()"
                                    class="px-3.5 py-2 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-bold rounded-xl transition shrink-0 cursor-pointer shadow-2xs">
                                Choose File
                            </button>

                            <!-- Tempat Nama File: File Baru atau File yang Sudah Ada (Dapat Diklik Langsung untuk View) -->
                            <div class="min-w-0 flex-1 truncate text-xs">
                                {{-- Jika ada file baru yang dipilih --}}
                                <template x-if="selectedName">
                                    <a :href="previewUrl" 
                                       target="_blank" 
                                       class="text-blue-600 hover:text-blue-800 hover:underline font-semibold truncate block cursor-pointer" 
                                       :title="selectedName" 
                                       x-text="selectedName"></a>
                                </template>

                                {{-- Jika belum pilih file baru tetapi ada file lama tersimpan --}}
                                @if($hasAttachment)
                                    <template x-if="!selectedName">
                                        <a href="{{ $savedUrl }}" 
                                           target="_blank" 
                                           class="text-blue-600 hover:text-blue-800 hover:underline font-semibold truncate block cursor-pointer" 
                                           title="Buka berkas lampiran saat ini">
                                            {{ $savedName }}
                                        </a>
                                    </template>
                                @else
                                    <template x-if="!selectedName">
                                        <span class="text-slate-400">No file chosen</span>
                                    </template>
                                @endif
                            </div>
                        </div>

                        <p class="text-[10px] text-slate-400">Format: PDF, JPG, PNG (Maks. 2MB)</p>
                        <x-input-error :messages="$errors->get('attachment')" class="mt-1" />
                    </div>

                    {{-- Tombol Aksi Bawah --}}
                    <div class="pt-4 border-t border-slate-100 flex flex-col-reverse sm:flex-row items-stretch sm:items-center sm:justify-end gap-2.5 sm:gap-3">
                        <a href="{{ route('student.logbook.index') }}" 
                           class="w-full sm:w-auto px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition text-center justify-center flex items-center">
                            Kembali
                        </a>
                        <button type="submit" 
                                class="w-full sm:w-auto px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-md transition active:scale-95 cursor-pointer text-center justify-center flex items-center">
                            Simpan Perubahan
                        </button>
                    </div>

                </form>
            </div>

        </div>
    </div>
</x-app-layout>