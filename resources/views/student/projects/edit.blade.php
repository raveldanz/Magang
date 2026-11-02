<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('student.final_report.index') }}" 
                   class="p-2.5 rounded-xl bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition shadow-2xs" 
                   title="Kembali ke Laporan Akhir">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200 uppercase tracking-wider">
                            Perbarui Proyek
                        </span>
                        <span class="text-xs text-slate-400">&bull;</span>
                        <span class="text-xs text-slate-500 font-medium">
                            {{ $placement->application?->unit?->agencyProfile?->agency_name ?? 'Instansi Dinas' }}
                        </span>
                    </div>
                    <h2 class="font-bold text-xl sm:text-2xl text-slate-900 leading-tight mt-1">
                        Ubah Proyek Inovasi: {{ $project->title }}
                    </h2>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('projects.show', $project->id) }}" target="_blank" 
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold rounded-xl shadow-2xs transition">
                    <span>Lihat Halaman Proyek</span>
                </a>
                <a href="{{ route('student.final_report.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
                    <span>Batal</span>
                </a>
            </div>
        </div>
    </x-slot>

    @php
        $currentScreenshots = is_array($project->screenshots) ? $project->screenshots : [];
        $currentCommunityPhotos = is_array($project->community_photos) ? $project->community_photos : [];
        $currentDocuments = is_array($project->documents) ? $project->documents : [];
    @endphp

    <div class="py-6 sm:py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if ($errors->any())
                <div class="p-4 bg-rose-50 border-l-4 border-rose-500 rounded-r-2xl shadow-2xs text-rose-900 text-xs space-y-1">
                    <p class="font-bold text-sm">Terdapat kesalahan pembaruan formulir:</p>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('student.projects.update', $project->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6" x-data="{ visibility: '{{ old('is_public', $project->is_public ? '1' : '0') == '1' ? 'public' : 'private' }}' }">
                @csrf
                @method('PUT')

                <!-- BAGIAN 1: IDENTITAS & DESKRIPSI KARYA -->
                <div class="bg-white rounded-2xl sm:rounded-3xl p-5 sm:p-7 border border-slate-200/80 shadow-xs space-y-6">
                    <div class="pb-3 border-b border-slate-100">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Bagian 1</span>
                        <h3 class="font-bold text-base text-slate-900 mt-0.5">Identitas & Narasi Proyek</h3>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Judul Proyek Inovasi <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="title" value="{{ old('title', $project->title) }}" required 
                                   class="w-full text-xs sm:text-sm border-slate-300 rounded-xl focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Bidang / Kategori Inovasi <span class="text-rose-500">*</span>
                            </label>
                            @php
                                $curCat = old('category', $project->category ?? 'teknologi');
                            @endphp
                            <select name="category" required class="w-full text-xs sm:text-sm border-slate-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 bg-white">
                                <option value="teknologi" {{ $curCat === 'teknologi' ? 'selected' : '' }}>Teknologi & Perangkat Lunak (Aplikasi Web/Mobile/Sistem)</option>
                                <option value="buku" {{ $curCat === 'buku' ? 'selected' : '' }}>Buku & Karya Tulis Literasi (Buku Cerita/Antologi/Kamus)</option>
                                <option value="desain" {{ $curCat === 'desain' ? 'selected' : '' }}>Desain Grafis & Penataan Ruang (Mural/Pojok Baca/Interior)</option>
                                <option value="preservasi" {{ $curCat === 'preservasi' ? 'selected' : '' }}>Preservasi & Konservasi (Restorasi Naskah Kuno/Arsip Fisik)</option>
                                <option value="media" {{ $curCat === 'media' ? 'selected' : '' }}>Media Kreatif & Audio-Visual (Video/Podcast/Audiobook)</option>
                                <option value="tata_kelola" {{ $curCat === 'tata_kelola' ? 'selected' : '' }}>Tata Kelola & SOP Layanan (Pedoman/Kebijakan Administrasi)</option>
                                <option value="lainnya" {{ $curCat === 'lainnya' ? 'selected' : '' }}>Karya Inovasi Lainnya</option>
                            </select>
                        </div>
                    </div>

                    <!-- Status Visibilitas & Privasi Proyek -->
                    <div class="pt-2 border-t border-slate-100">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Status Visibilitas Karya di Galeri Publik Dinas
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <label class="relative flex items-start gap-3 p-3.5 rounded-2xl border-2 cursor-pointer transition"
                                   :class="visibility === 'public' ? 'border-blue-600 bg-blue-50/40 ring-1 ring-blue-500/20' : 'border-slate-200 bg-slate-50/50 hover:bg-slate-50'">
                                <input type="radio" name="is_public" value="1" x-model="visibility" class="mt-0.5 text-blue-600 focus:ring-blue-500" {{ old('is_public', $project->is_public ? '1' : '0') == '1' ? 'checked' : '' }}>
                                <div class="text-xs">
                                    <div class="font-bold text-slate-900 flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        <span>Publik (Galeri Karya Proyek Dinas)</span>
                                    </div>
                                    <p class="text-slate-500 mt-1 leading-relaxed">
                                        Karya dapat dinikmati publik umum, menerima ulasan diskusi, serta apresiasi suka (like).
                                    </p>
                                </div>
                            </label>

                            <label class="relative flex items-start gap-3 p-3.5 rounded-2xl border-2 cursor-pointer transition"
                                   :class="visibility === 'private' ? 'border-amber-500 bg-amber-50/40 ring-1 ring-amber-500/20' : 'border-slate-200 bg-slate-50/50 hover:bg-slate-50'">
                                <input type="radio" name="is_public" value="0" x-model="visibility" class="mt-0.5 text-amber-600 focus:ring-amber-500" {{ old('is_public', $project->is_public ? '1' : '0') == '0' ? 'checked' : '' }}>
                                <div class="text-xs">
                                    <div class="font-bold text-slate-900 flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                        <span>Privat / Data Sensitif Kedinasan</span>
                                    </div>
                                    <p class="text-slate-500 mt-1 leading-relaxed">
                                        Disembunyikan dari galeri publik jika memuat dokumen rahasia instansi atau arsip terbatas dinas.
                                    </p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Deskripsi Lengkap & Latar Belakang Proyek <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="description" rows="4" required 
                                  class="w-full text-xs sm:text-sm border-slate-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 leading-relaxed">{{ old('description', $project->description) }}</textarea>
                    </div>
                </div>

                <!-- BAGIAN 2: PUSAT AKSES & TAUTAN PROYEK -->
                <div class="bg-white rounded-2xl sm:rounded-3xl p-5 sm:p-7 border border-slate-200/80 shadow-xs space-y-6">
                    <div class="pb-3 border-b border-slate-100">
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-600 text-white uppercase tracking-wider">
                                Bagian 2
                            </span>
                            <h3 class="font-bold text-base text-slate-900">Pusat Akses Proyek</h3>
                        </div>
                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                            Sediakan akses langsung ke karya inovasi Anda agar mentor dinas, dosen pembimbing, dan atasan dapat menguji atau meninjau karya Anda. Mahasiswa dapat mengisi salah satu atau seluruh opsi di bawah ini sesuai karakteristik proyek (aplikasi, buku, desain fisik, dll):
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- 1. Tautan Website / Live Demo / Portofolio -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                Tautan Website / Portofolio / Live Demo
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                                </div>
                                <input type="url" name="demo_url" value="{{ old('demo_url', $project->demo_url) }}" 
                                       placeholder="https://contoh-aplikasi-magang.surabaya.go.id" 
                                       class="w-full pl-9 text-xs sm:text-sm border-slate-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 bg-white">
                            </div>
                            <p class="text-[11px] text-slate-500">Tautan website aktif, link portofolio daring, atau link hosting pameran.</p>
                        </div>

                        <!-- 2. Tautan Repositori / Penyimpanan Awan -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                Tautan Repositori / Cloud Storage (Opsional)
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.53 1.032 1.53 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"/></svg>
                                </div>
                                <input type="url" name="repository_url" value="{{ old('repository_url', $project->repository_url) }}" 
                                       placeholder="https://github.com/... atau link Google Drive / Canva" 
                                       class="w-full pl-9 text-xs sm:text-sm border-slate-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 bg-white">
                            </div>
                            <p class="text-[11px] text-slate-500">Tautan GitHub/GitLab, Google Drive naskah buku, atau materi digital.</p>
                        </div>
                    </div>

                    <!-- 3. Unggah Berkas Sumber Proyek / File ZIP Website -->
                    <div class="p-4 sm:p-5 bg-slate-50 border border-slate-200/80 rounded-2xl space-y-2.5">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-bold text-slate-800 uppercase tracking-wider">
                                Unggah Berkas Sumber Proyek / File ZIP (Opsional)
                            </label>
                            <span class="text-[10px] font-bold text-slate-500 bg-white border border-slate-200 px-2 py-0.5 rounded">ZIP / RAR / TAR.GZ (Maks. 50 MB)</span>
                        </div>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Bila proyek berupa web offline, source code, master file desain (PSD/AI), e-book PDF resolusi cetak, atau arsip lengkap dokumen, unggah file arsip .ZIP proyek Anda di sini.
                        </p>

                        @if($project->source_code_path)
                            <!-- Kartu File ZIP yang sudah terunggah -->
                            <div class="p-3 bg-amber-50/70 border border-amber-200 rounded-xl flex items-center justify-between gap-3 text-xs mb-1">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="p-2 bg-amber-100 text-amber-800 rounded-lg shrink-0">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                                    </div>
                                    <div class="min-w-0">
                                        <span class="font-bold text-slate-900 block truncate">{{ $project->source_code_name ?? 'Berkas_Sumber_Proyek.zip' }}</span>
                                        <span class="text-[11px] text-slate-500">{{ $project->source_code_size ?? 'Tersedia' }} &bull; Sudah terunggah</span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <a href="{{ asset('storage/' . $project->source_code_path) }}" download class="px-3 py-1.5 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 rounded-lg font-bold shadow-2xs">Unduh</a>
                                    <label class="flex items-center gap-1 text-[11px] text-rose-600 font-semibold cursor-pointer">
                                        <input type="checkbox" name="delete_source_code" value="1" class="rounded border-rose-300 text-rose-600 focus:ring-rose-500">
                                        <span>Hapus Berkas</span>
                                    </label>
                                </div>
                            </div>
                        @endif

                        <input type="file" name="source_code" accept=".zip,.rar,.7z,.tar,.gz"
                               class="w-full text-xs text-slate-600 file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-dashed border-slate-300 rounded-xl p-2 bg-white">
                    </div>
                </div>

                <!-- BAGIAN 3: MEDIA VISUAL & SAMPUL PROYEK -->
                <div class="bg-white rounded-2xl sm:rounded-3xl p-5 sm:p-7 border border-slate-200/80 shadow-xs space-y-6">
                    <div class="pb-3 border-b border-slate-100">
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-800 text-white uppercase tracking-wider">
                                Bagian 3
                            </span>
                            <h3 class="font-bold text-base text-slate-900">Dokumentasi Visual & Tangkapan Layar</h3>
                        </div>
                    </div>

                    <!-- Foto Sampul Utama -->
                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                            Foto Sampul Utama Proyek
                        </label>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Biarkan kosong jika tidak ingin mengubah foto sampul saat ini. Format JPG, PNG, atau WebP (maksimal 4 MB).
                        </p>
                        <input type="file" name="photo" id="coverPhotoInput" accept="image/jpeg,image/png,image/jpg,image/webp" 
                               onchange="previewCoverImage(this)"
                               class="w-full text-xs text-slate-600 file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">

                        <!-- Preview Container Proporsional & Terkendali -->
                        <div class="mt-3 w-full aspect-video max-h-72 rounded-2xl bg-slate-950/5 border border-slate-200 overflow-hidden flex items-center justify-center p-2 relative">
                            <img id="coverPreview" src="{{ $project->photo_url }}" alt="{{ $project->title }}" class="max-h-64 w-auto max-w-full object-contain rounded-xl shadow-xs">
                        </div>
                    </div>

                    <!-- Tangkapan Layar Aplikasi Tambahan -->
                    <div class="p-4 sm:p-5 bg-slate-50 border border-slate-200/80 rounded-2xl space-y-3">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-bold text-slate-800 uppercase tracking-wider">
                                Galeri Tangkapan Layar Aplikasi / Mockup Fitur
                            </label>
                            @if(count($currentScreenshots) > 0)
                                <span class="px-2 py-0.5 bg-slate-200 text-slate-700 font-bold text-[10px] rounded-md">
                                    {{ count($currentScreenshots) }} Berkas Tersimpan
                                </span>
                            @endif
                        </div>

                        @if(count($currentScreenshots) > 0)
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 pt-1">
                                @foreach($currentScreenshots as $cIdx => $sImg)
                                    <div class="aspect-video rounded-xl overflow-hidden bg-white border border-slate-200 relative group">
                                        <img src="{{ asset('storage/' . $sImg) }}" alt="Screenshot {{ $cIdx + 1 }}" class="w-full h-full object-cover">
                                        <span class="absolute bottom-1 left-1.5 px-1.5 py-0.5 bg-black/60 text-white rounded text-[9px] font-medium">Layar {{ $cIdx + 1 }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <p class="text-xs text-slate-500 leading-relaxed">
                            Unggah berkas tangkapan layar baru jika ingin memperbarui / menambah koleksi (Maksimal 5 gambar, format JPG, PNG, WebP).
                        </p>
                        <input type="file" name="screenshots[]" multiple accept="image/jpeg,image/png,image/jpg,image/webp" 
                               class="w-full text-xs text-slate-600 file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-200 file:text-slate-800 hover:file:bg-slate-300">
                    </div>
                </div>

                <!-- BAGIAN 4: MANFAAT MASYARAKAT, DOKUMENTASI DESA & VIDEO -->
                <div class="bg-gradient-to-br from-emerald-50/50 via-white to-teal-50/30 rounded-2xl sm:rounded-3xl p-5 sm:p-7 border border-emerald-200/80 shadow-xs space-y-6">
                    <div class="pb-3 border-b border-emerald-100">
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-600 text-white uppercase tracking-wider">
                                Bagian 4
                            </span>
                            <h3 class="font-bold text-base text-emerald-950">Dampak Sosial & Dokumentasi Lapangan</h3>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-emerald-950 uppercase tracking-wider mb-1.5">
                            Uraian Manfaat untuk Masyarakat & Pelayanan Dinas <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="community_benefit" rows="3" required 
                                  class="w-full text-xs sm:text-sm border-emerald-300 rounded-xl focus:ring-emerald-500 focus:border-emerald-500 leading-relaxed">{{ old('community_benefit', $project->community_benefit) }}</textarea>
                    </div>

                    <!-- Dokumentasi Foto Kegiatan Lapangan / Desa -->
                    <div class="p-4 sm:p-5 bg-white border border-emerald-200 rounded-2xl space-y-3 shadow-2xs">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-bold text-emerald-950 uppercase tracking-wider">
                                Foto Dokumentasi Kegiatan / Presentasi di Lapangan
                            </label>
                            @if(count($currentCommunityPhotos) > 0)
                                <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 font-bold text-[10px] rounded-md">
                                    {{ count($currentCommunityPhotos) }} Foto Tersimpan
                                </span>
                            @endif
                        </div>

                        @if(count($currentCommunityPhotos) > 0)
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 pt-1">
                                @foreach($currentCommunityPhotos as $cIdx => $cImg)
                                    <div class="aspect-video rounded-xl overflow-hidden bg-slate-900 border border-slate-200 relative group">
                                        <img src="{{ asset('storage/' . $cImg) }}" alt="Dokumentasi {{ $cIdx + 1 }}" class="w-full h-full object-cover">
                                        <span class="absolute bottom-1 left-1.5 px-1.5 py-0.5 bg-black/60 text-white rounded text-[9px] font-medium">Foto {{ $cIdx + 1 }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <p class="text-xs text-emerald-800 leading-relaxed">
                            Unggah foto kegiatan baru untuk memperbarui dokumentasi di lapangan (Maksimal 5 foto, masing-masing maks 4 MB).
                        </p>
                        <input type="file" name="community_photos[]" multiple accept="image/jpeg,image/png,image/jpg,image/webp" 
                               class="w-full text-xs text-slate-600 file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-emerald-100 file:text-emerald-800 hover:file:bg-emerald-200">
                    </div>

                    <!-- Tautan Video Dokumentasi YouTube / Drive -->
                    <div>
                        <label class="block text-xs font-bold text-slate-800 uppercase tracking-wider mb-1.5">
                            Tautan Video Dokumentasi (YouTube / Google Drive)
                        </label>
                        <input type="url" name="video_url" value="{{ old('video_url', $project->video_url) }}" 
                               placeholder="Contoh: https://youtu.be/jlKU3exv4Oc?si=... atau link rekaman video" 
                               class="w-full text-xs sm:text-sm border-slate-300 rounded-xl focus:ring-blue-500 focus:border-blue-500">
                        <p class="text-[11px] text-slate-500 mt-1">
                            Jika menggunakan link YouTube, pemutar video akan otomatis muncul secara interaktif di halaman detail proyek.
                        </p>
                    </div>
                </div>

                <!-- BAGIAN 5: DOKUMEN PENDUKUNG & BUKU PANDUAN TEKNIS -->
                <div class="bg-white rounded-2xl sm:rounded-3xl p-5 sm:p-7 border border-slate-200/80 shadow-xs space-y-4">
                    <div class="pb-3 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-800 text-white uppercase tracking-wider inline-block mb-1">
                                Bagian 5
                            </span>
                            <h3 class="font-bold text-base text-slate-900">Buku Panduan Teknis & Dokumen Lampiran</h3>
                        </div>
                        @if(count($currentDocuments) > 0)
                            <span class="px-2.5 py-0.5 bg-blue-100 text-blue-800 font-bold text-[10px] rounded-md">
                                {{ count($currentDocuments) }} Berkas Tersimpan
                            </span>
                        @endif
                    </div>

                    @if(count($currentDocuments) > 0)
                        <div class="space-y-2">
                            @foreach($currentDocuments as $doc)
                                @php
                                    $dName = is_array($doc) ? ($doc['name'] ?? 'Dokumen.pdf') : basename($doc);
                                    $dSize = is_array($doc) ? ($doc['size'] ?? '') : '';
                                    $dPath = is_array($doc) ? ($doc['path'] ?? $doc) : $doc;
                                @endphp
                                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between text-xs">
                                    <div class="flex items-center gap-2 truncate">
                                        <svg class="w-4 h-4 text-red-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/></svg>
                                        <span class="font-medium text-slate-800 truncate">{{ $dName }}</span>
                                        @if($dSize) <span class="text-slate-400 font-mono text-[11px]">({{ $dSize }})</span> @endif
                                    </div>
                                    <a href="{{ asset('storage/' . $dPath) }}" target="_blank" class="text-blue-600 hover:text-blue-800 font-bold shrink-0">Buka</a>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="p-4 sm:p-5 bg-blue-50/50 border border-blue-200 rounded-2xl space-y-2.5">
                        <label class="block text-xs font-bold text-blue-950 uppercase tracking-wider">
                            Unggah Dokumen PDF Tambahan / Pengganti (Opsional)
                        </label>
                        <p class="text-xs text-blue-800 leading-relaxed">
                            Lampirkan buku panduan teknis (*manual book*), SOP penggunaan sistem, atau laporan teknis inovasi (Format: PDF, DOC, DOCX. Maksimal 10 berkas, masing-masing maks 10 MB).
                        </p>
                        <input type="file" name="documents[]" multiple accept=".pdf,.doc,.docx,application/pdf" 
                               class="w-full text-xs text-slate-600 file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-100 file:text-blue-800 hover:file:bg-blue-200">
                    </div>
                </div>

                <!-- Tombol Aksi Simpan & Batal -->
                <div class="flex flex-col-reverse sm:flex-row items-center justify-between gap-3 pt-2">
                    <a href="{{ route('student.final_report.index') }}" 
                       class="w-full sm:w-auto px-6 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition text-center">
                        Batal
                    </a>
                    <button type="submit" 
                            class="w-full sm:w-auto px-8 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs transition cursor-pointer">
                        Simpan Perubahan Proyek
                    </button>
                </div>
            </form>

        </div>
    </div>

    @push('scripts')
    <script>
        function previewCoverImage(input) {
            if (input.files && input.files[0]) {
                const file = input.files[0];
                if (!['image/jpeg', 'image/png', 'image/jpg', 'image/webp'].includes(file.type)) {
                    alert('Format foto sampul harus berupa JPG, PNG, atau WebP.');
                    input.value = '';
                    return;
                }
                if (file.size > 4 * 1024 * 1024) {
                    alert('Ukuran foto maksimal 4 MB.');
                    input.value = '';
                    return;
                }
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('coverPreview');
                    if (preview) {
                        preview.src = e.target.result;
                    }
                };
                reader.readAsDataURL(file);
            }
        }
    </script>
    @endpush
</x-app-layout>
