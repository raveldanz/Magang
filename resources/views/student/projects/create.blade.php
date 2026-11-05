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
                        <span class="px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-200 uppercase tracking-wider">
                            Formulir Mandiri
                        </span>
                        <span class="text-xs text-slate-400">&bull;</span>
                        <span class="text-xs text-slate-500 font-medium">
                            {{ $placement->application?->unit?->agencyProfile?->agency_name ?? 'Instansi Dinas' }}
                        </span>
                    </div>
                    <h2 class="font-bold text-xl sm:text-2xl text-slate-900 leading-tight mt-1">
                        Tambah Proyek Inovasi Magang
                    </h2>
                </div>
            </div>

            <a href="{{ route('student.final_report.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
                <span>Batal & Kembali</span>
            </a>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Banner Panduan Pengisian -->
            <div class="bg-blue-50/70 border border-blue-200/80 rounded-2xl p-4 sm:p-5 flex items-start gap-3.5 shadow-2xs">
                <div class="w-8 h-8 rounded-xl bg-blue-600 text-white flex items-center justify-center shrink-0 mt-0.5 shadow-2xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="space-y-1 text-xs text-blue-900 leading-relaxed">
                    <h4 class="font-bold text-blue-950 text-sm">Ketentuan Dokumentasi Luaran Inovasi</h4>
                    <p>
                        Unggah karya inovasi, dokumentasi kegiatan di masyarakat / instansi, tautan video, dan dokumen pendukung seperti buku panduan teknis (*manual book*). Anda dapat menambahkan lebih dari satu proyek jika mengerjakan beberapa output luaran.
                    </p>
                </div>
            </div>

            @if ($errors->any())
                <div class="p-4 bg-rose-50 border-l-4 border-rose-500 rounded-r-2xl shadow-2xs text-rose-900 text-xs space-y-1">
                    <p class="font-bold text-sm">Terdapat kesalahan pengisian formulir:</p>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('student.projects.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6" 
                  x-data="{ 
                      visibility: '{{ old('is_public', '1') == '1' ? 'public' : 'private' }}',
                      category: '{{ old('category', '') }}',
                      customCategory: '{{ old('custom_category', '') }}',
                      projectType: '{{ old('project_type', 'individual') }}',
                      roleRecommendations: @js($roleRecommendations),
                      categoryKeywords: @js($categoryKeywords),
                      categoryLabels: {
                          'teknologi': 'Sistem Digital',
                          'buku': 'Karya Literasi',
                          'media': 'Media Kreatif',
                          'preservasi': 'Preservasi Kearsipan',
                          'tata_kelola': 'Tata Kelola Layanan'
                      },
                      get detectedCategory() {
                          if (this.category !== 'lainnya' || !this.customCategory) return null;
                          const text = this.customCategory.toLowerCase().trim();
                          if (!text) return null;

                          let bestCat = null;
                          let bestScore = 0;

                          for (const [catKey, keywords] of Object.entries(this.categoryKeywords)) {
                              let score = 0;
                              for (const kw of keywords) {
                                  const lowerKw = kw.toLowerCase().trim();
                                  if (!lowerKw) continue;

                                  if (lowerKw.length < 4) {
                                      const regex = new RegExp('(?:^|[^a-z0-9])' + lowerKw.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '(?:[^a-z0-9]|$)', 'i');
                                      if (regex.test(text)) {
                                          score += lowerKw.length * 3;
                                      }
                                  } else {
                                      if (text.includes(lowerKw)) {
                                          score += lowerKw.length;
                                      }
                                  }
                              }
                              if (score > bestScore) {
                                  bestScore = score;
                                  bestCat = catKey;
                              }
                          }
                          return bestScore > 0 ? bestCat : null;
                      },
                      applyDetectedCategory(catKey) {
                          this.category = catKey;
                          this.customCategory = '';
                      },
                      get currentRoleChips() {
                          return this.roleRecommendations[this.category] || this.roleRecommendations['lainnya'] || [];
                      },
                      setOrAppendRole(elId, chipRole) {
                          const input = document.getElementById(elId);
                          if (!input) return;
                          const val = input.value.trim();
                          if (!val) {
                              input.value = chipRole;
                          } else if (!val.includes(chipRole)) {
                              input.value = val + ' & ' + chipRole;
                          }
                          input.dispatchEvent(new Event('input'));
                      }
                  }">
                @csrf

                <!-- BAGIAN 1: IDENTITAS & DESKRIPSI KARYA -->
                <div class="bg-white rounded-2xl sm:rounded-3xl p-5 sm:p-7 border border-slate-200/80 shadow-xs space-y-5">
                    <div class="pb-3 border-b border-slate-100">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Bagian 1</span>
                        <h3 class="font-bold text-base text-slate-900 mt-0.5">Identitas & Narasi Proyek</h3>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Judul Proyek Inovasi <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="title" value="{{ old('title') }}" required 
                                   placeholder="Contoh: Sistem Informasi Kearsipan Terpadu / Buku Cerita Rakyat / Pojok Baca Anak" 
                                   class="w-full text-xs sm:text-sm border-slate-300 rounded-xl focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Bidang / Kategori Inovasi <span class="text-rose-500">*</span>
                            </label>
                            <select name="category" x-model="category" required class="w-full text-xs sm:text-sm border-slate-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 bg-white">
                                <option value="" disabled {{ old('category') ? '' : 'selected' }}>Pilih Kategori Karya...</option>
                                <option value="teknologi">Sistem Digital</option>
                                <option value="buku">Karya Literasi</option>
                                <option value="media">Media Kreatif</option>
                                <option value="preservasi">Preservasi Kearsipan</option>
                                <option value="tata_kelola">Tata Kelola Layanan</option>
                                <option value="lainnya">Inovasi Lainnya</option>
                            </select>
                        </div>
                    </div>

                    {{-- Form Tambahan Kategori Lainnya dengan Rekomendasi Cerdas --}}
                    <div x-show="category === 'lainnya'" x-cloak class="p-3.5 bg-slate-50 border border-slate-200/80 rounded-xl space-y-2">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                            Nama Bidang Proyek <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="custom_category" x-model="customCategory" 
                               placeholder="Tuliskan nama bidang proyek Anda..." 
                               class="w-full text-xs sm:text-sm border-slate-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 bg-white">
                        
                        <template x-if="detectedCategory">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 p-2.5 bg-blue-50 border border-blue-200 rounded-lg text-xs text-blue-900 mt-2">
                                <div>
                                    <span class="font-bold text-blue-950">Rekomendasi:</span> Bidang ini cocok dengan kategori <span class="font-bold underline" x-text="categoryLabels[detectedCategory]"></span>.
                                </div>
                                <button type="button" @click="applyDetectedCategory(detectedCategory)" class="px-2.5 py-1 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-md text-[11px] shrink-0 transition cursor-pointer">
                                    Pilih Kategori Ini
                                </button>
                            </div>
                        </template>
                    </div>

                    <!-- Status Visibilitas & Privasi Proyek -->
                    <div class="pt-2 border-t border-slate-100">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Status Visibilitas Karya di Galeri Publik Dinas
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <label class="relative flex items-start gap-3 p-3.5 rounded-2xl border-2 cursor-pointer transition"
                                   :class="visibility === 'public' ? 'border-blue-600 bg-blue-50/40 ring-1 ring-blue-500/20' : 'border-slate-200 bg-slate-50/50 hover:bg-slate-50'">
                                <input type="radio" name="is_public" value="1" x-model="visibility" class="mt-0.5 text-blue-600 focus:ring-blue-500" {{ old('is_public', '1') == '1' ? 'checked' : '' }}>
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
                                <input type="radio" name="is_public" value="0" x-model="visibility" class="mt-0.5 text-amber-600 focus:ring-amber-500" {{ old('is_public') === '0' ? 'checked' : '' }}>
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
                                  placeholder="Jelaskan ruang lingkup inovasi, masalah yang diatasi, metodologi/teknologi/media yang digunakan, serta kontribusi mahasiswa selama masa magang..." 
                                  class="w-full text-xs sm:text-sm border-slate-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 leading-relaxed">{{ old('description') }}</textarea>
                    </div>
                </div>

                <!-- BAGIAN 2: PENGERJAAN PROYEK & ANGGOTA TIM -->
                <div class="bg-white rounded-2xl sm:rounded-3xl p-5 sm:p-7 border border-slate-200/80 shadow-xs space-y-6">
                    <div class="pb-3 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Bagian 2</span>
                            <h3 class="font-bold text-base text-slate-900 mt-0.5">Pengerjaan Proyek & Anggota Tim</h3>
                        </div>
                        <span class="text-[11px] text-slate-500">
                            Tentukan apakah karya dikerjakan individu atau kelompok
                        </span>
                    </div>

                    <!-- Pilihan Mode: Individu vs Kelompok -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <label class="relative flex items-start gap-3 p-3.5 rounded-2xl border-2 cursor-pointer transition"
                               :class="projectType === 'individual' ? 'border-blue-600 bg-blue-50/40 ring-1 ring-blue-500/20' : 'border-slate-200 bg-slate-50/50 hover:bg-slate-50'">
                            <input type="radio" name="project_type" value="individual" x-model="projectType" class="mt-0.5 text-blue-600 focus:ring-blue-500">
                            <div class="text-xs">
                                <div class="font-bold text-slate-900">Proyek Individu</div>
                                <p class="text-slate-500 mt-0.5">Karya dikerjakan mandiri oleh Anda sendiri.</p>
                            </div>
                        </label>

                        <label class="relative flex items-start gap-3 p-3.5 rounded-2xl border-2 cursor-pointer transition"
                               :class="projectType === 'group' ? 'border-blue-600 bg-blue-50/40 ring-1 ring-blue-500/20' : 'border-slate-200 bg-slate-50/50 hover:bg-slate-50'">
                            <input type="radio" name="project_type" value="group" x-model="projectType" class="mt-0.5 text-blue-600 focus:ring-blue-500">
                            <div class="text-xs">
                                <div class="font-bold text-slate-900">Proyek Kelompok (Tim)</div>
                                <p class="text-slate-500 mt-0.5">Karya kolaboratif bersama rekan magang di bidang Anda.</p>
                            </div>
                        </label>
                    </div>

                    <!-- Peran Pembuat / Pengunggah Utama -->
                    <div class="p-4 bg-slate-50 border border-slate-200/80 rounded-2xl space-y-2">
                        <div class="flex items-center justify-between">
                            <div class="text-xs font-bold text-slate-800">
                                Peran Anda: <span class="text-blue-600 font-semibold">{{ $user->name }}</span> (Pengunggah Utama)
                            </div>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-200">Ketua / Penanggung Jawab</span>
                        </div>
                        <div>
                            <input type="text" name="creator_role" id="creator_role_input"
                                   value="{{ old('creator_role', 'Koordinator Proyek') }}" 
                                   placeholder="Contoh: Koordinator Proyek & Desain Grafis" 
                                   class="w-full text-xs sm:text-sm border-slate-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 bg-white">
                        </div>
                        <div class="flex items-center gap-1.5 flex-wrap pt-1 text-[11px] text-slate-500">
                            <span class="font-medium text-slate-600 shrink-0">Rekomendasi:</span>
                            <template x-for="rChip in currentRoleChips" :key="rChip">
                                <button type="button" @click="setOrAppendRole('creator_role_input', rChip)"
                                        class="px-2 py-0.5 bg-white hover:bg-blue-50 hover:text-blue-700 hover:border-blue-300 border border-slate-200 rounded-md text-[11px] font-medium text-slate-700 transition cursor-pointer">
                                    <span x-text="rChip"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    <!-- Daftar Anggota Tim (Hanya Tampil Jika Mode Kelompok) -->
                    <div x-show="projectType === 'group'" x-cloak class="space-y-3 pt-2 border-t border-slate-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="font-bold text-xs sm:text-sm text-slate-900">Pilih Rekan Anggota Tim</h4>
                                <p class="text-[11px] text-slate-500">Centang rekan yang terlibat dan tentukan peran tugasnya.</p>
                            </div>
                            <span class="text-[11px] text-slate-400">{{ count($peers) }} rekan magang terdeteksi</span>
                        </div>

                        @if(count($peers) === 0)
                            <div class="p-4 bg-amber-50 border border-amber-200 rounded-2xl text-xs text-amber-900 leading-relaxed">
                                Tidak ada rekan magang lain yang terdaftar di unit kerja ini. Anda tetap dapat menyimpan proyek ini sebagai karya kelompok atau beralih ke mode individu.
                            </div>
                        @else
                            <div class="space-y-3">
                                @foreach($peers as $pIdx => $peer)
                                    @php
                                        $peerUser = $peer->application?->user;
                                        $peerProfile = $peerUser?->studentProfile;
                                    @endphp
                                    <div x-data="{ checked: {{ old('members.'.$pIdx.'.placement_id') == $peer->id ? 'true' : 'false' }} }"
                                         class="p-4 rounded-2xl border transition"
                                         :class="checked ? 'border-blue-300 bg-blue-50/20 shadow-2xs' : 'border-slate-200 bg-white hover:bg-slate-50/60'">
                                        <div class="flex items-start gap-3">
                                            <input type="checkbox" 
                                                   id="peer_check_{{ $peer->id }}" 
                                                   x-model="checked"
                                                   class="mt-1 w-4 h-4 text-blue-600 rounded border-slate-300 focus:ring-blue-500 cursor-pointer">
                                            
                                            <div class="flex-1">
                                                <label for="peer_check_{{ $peer->id }}" class="cursor-pointer block">
                                                    <div class="font-bold text-xs sm:text-sm text-slate-900">{{ $peerUser?->name ?? 'Mahasiswa' }}</div>
                                                    <div class="text-[11px] text-slate-500 mt-0.5">
                                                        NIM: {{ $peerProfile?->nim ?? '-' }} &bull; {{ $peerProfile?->universitas ?? $peerUser?->university ?? '-' }} ({{ $peerProfile?->jurusan ?? '-' }})
                                                    </div>
                                                </label>

                                                <div x-show="checked" x-cloak class="mt-3 pt-3 border-t border-slate-100 space-y-2">
                                                    <input type="hidden" name="members[{{ $pIdx }}][placement_id]" value="{{ $peer->id }}" :disabled="!checked">
                                                    
                                                    <div>
                                                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">
                                                            Peran:
                                                        </label>
                                                        <input type="text" 
                                                               id="peer_role_input_{{ $peer->id }}"
                                                               name="members[{{ $pIdx }}][role]" 
                                                               value="{{ old('members.'.$pIdx.'.role', 'Anggota Tim') }}" 
                                                               placeholder="Misal: Desain Grafis & Konten" 
                                                               :disabled="!checked"
                                                               class="w-full text-xs border-slate-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 bg-white">
                                                    </div>

                                                    <div class="flex items-center gap-1.5 flex-wrap text-[11px] text-slate-500">
                                                        <span class="font-medium text-slate-600 shrink-0">Rekomendasi:</span>
                                                        <template x-for="rChip in currentRoleChips" :key="rChip">
                                                            <button type="button" 
                                                                    @click="setOrAppendRole('peer_role_input_{{ $peer->id }}', rChip)"
                                                                    class="px-2 py-0.5 bg-white hover:bg-blue-50 hover:text-blue-700 hover:border-blue-300 border border-slate-200 rounded-md text-[11px] font-medium text-slate-700 transition cursor-pointer">
                                                                <span x-text="rChip"></span>
                                                            </button>
                                                        </template>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <!-- BAGIAN 3: PUSAT AKSES & TAUTAN PROYEK -->
                <div class="bg-white rounded-2xl sm:rounded-3xl p-5 sm:p-7 border border-slate-200/80 shadow-xs space-y-6">
                    <div class="pb-3 border-b border-slate-100">
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-600 text-white uppercase tracking-wider">
                                Bagian 3
                            </span>
                            <h3 class="font-bold text-base text-slate-900">Pusat Akses Proyek</h3>
                        </div>
                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                            Sediakan akses langsung ke karya inovasi Anda agar mentor dinas, dosen pembimbing, dan atasan dapat menguji atau meninjau karya Anda. Mahasiswa dapat mengisi salah satu atau seluruh opsi di bawah ini sesuai karakteristik proyek:
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
                                <input type="url" name="demo_url" value="{{ old('demo_url') }}" 
                                       placeholder="https://contoh-karya-magang.surabaya.go.id" 
                                       class="w-full pl-9 text-xs sm:text-sm border-slate-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 bg-white">
                            </div>
                            <p class="text-[11px] text-slate-500">Tautan website aktif, web portofolio, atau link pameran daring.</p>
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
                                <input type="url" name="repository_url" value="{{ old('repository_url') }}" 
                                       placeholder="https://github.com/... atau link Google Drive / Canva" 
                                       class="w-full pl-9 text-xs sm:text-sm border-slate-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 bg-white">
                            </div>
                            <p class="text-[11px] text-slate-500">Tautan GitHub/GitLab, folder Google Drive naskah, atau link materi.</p>
                        </div>
                    </div>

                    <!-- 3. Unggah Berkas Sumber Proyek / File ZIP (Konsisten ala Profil) -->
                    <div class="space-y-2 pt-2" x-data="{ zipName: '' }">
                        <div class="flex items-center justify-between">
                            <label for="source_code" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                Unggah Berkas Sumber Proyek / File ZIP (Opsional)
                            </label>
                            <span class="text-[10px] text-slate-400 font-mono">ZIP / RAR / TAR.GZ (Maks. 50 MB)</span>
                        </div>
                        
                        <p class="text-[11px] text-slate-500 leading-relaxed">
                            Bila proyek berupa web offline, source code, master file desain (PSD/AI), e-book PDF resolusi cetak, atau arsip lengkap dokumen, unggah file arsip .ZIP proyek Anda di sini.
                        </p>

                        <div class="flex items-center gap-2 w-full pt-1">
                            <input type="file" 
                                   id="source_code" 
                                   name="source_code" 
                                   accept=".zip,.rar,.7z,.tar,.gz" 
                                   class="hidden"
                                   @change="if($event.target.files.length) { 
                                       const f = $event.target.files[0];
                                       if(f.size > 50 * 1024 * 1024) {
                                           alert('Ukuran file arsip maksimal 50MB.');
                                           $event.target.value = '';
                                           zipName = '';
                                           return;
                                       }
                                       zipName = f.name; 
                                   }" />

                            <button type="button" 
                                    @click="document.getElementById('source_code').click()"
                                    class="px-3.5 py-2 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-bold rounded-xl transition shrink-0 cursor-pointer shadow-2xs">
                                Choose File
                            </button>

                            <div class="min-w-0 flex-1 truncate text-xs">
                                <template x-if="zipName">
                                    <span class="text-blue-700 font-bold truncate block" x-text="zipName"></span>
                                </template>
                                <template x-if="!zipName">
                                    <span class="text-slate-400">No file chosen</span>
                                </template>
                            </div>
                        </div>
                        <x-input-error :messages="$errors->get('source_code')" class="mt-1" />
                    </div>
                </div>

                <!-- BAGIAN 4: MEDIA VISUAL & SAMPUL PROYEK -->
                <div class="bg-white rounded-2xl sm:rounded-3xl p-5 sm:p-7 border border-slate-200/80 shadow-xs space-y-6">
                    <div class="pb-3 border-b border-slate-100">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Bagian 4</span>
                        <h3 class="font-bold text-base text-slate-900 mt-0.5">Dokumentasi Visual & Tangkapan Layar</h3>
                    </div>

                    <!-- Foto Sampul Utama (Choose File Rapi) -->
                    <div class="space-y-2" x-data="{ coverName: '' }">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                            Foto Sampul Utama Proyek <span class="text-rose-500">*</span>
                        </label>
                        <p class="text-[11px] text-slate-500 leading-relaxed">
                            Foto ini akan menjadi gambar utama yang tampil di kartu luar. Format JPG, PNG, atau WebP (maksimal 4 MB).
                        </p>

                        <div class="flex items-center gap-2 w-full pt-1">
                            <input type="file" 
                                   name="photo" 
                                   id="coverPhotoInput" 
                                   required 
                                   accept="image/jpeg,image/png,image/jpg,image/webp" 
                                   class="hidden"
                                   onchange="previewCoverImage(this)"
                                   @change="if($event.target.files.length) { coverName = $event.target.files[0].name; }" />

                            <button type="button" 
                                    @click="document.getElementById('coverPhotoInput').click()"
                                    class="px-3.5 py-2 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-bold rounded-xl transition shrink-0 cursor-pointer shadow-2xs">
                                Choose File
                            </button>

                            <div class="min-w-0 flex-1 truncate text-xs">
                                <template x-if="coverName">
                                    <span class="text-blue-700 font-bold truncate block" x-text="coverName"></span>
                                </template>
                                <template x-if="!coverName">
                                    <span class="text-slate-400">No file chosen</span>
                                </template>
                            </div>
                        </div>

                        <!-- Preview Container Proporsional -->
                        <div class="mt-3 w-full aspect-video max-h-72 rounded-2xl bg-slate-950/5 border border-slate-200 overflow-hidden flex items-center justify-center p-2 relative">
                            <div id="coverPlaceholder" class="text-center p-4 text-slate-400 text-xs">
                                Pratinjau foto sampul akan muncul di sini (tampilan otomatis menyesuaikan proporsi rapi)
                            </div>
                            <img id="coverPreview" src="" alt="Pratinjau Sampul" class="hidden max-h-64 w-auto max-w-full object-contain rounded-xl shadow-xs">
                        </div>
                        <x-input-error :messages="$errors->get('photo')" class="mt-1" />
                    </div>

                    <!-- Tangkapan Layar Aplikasi Tambahan (Multi-Upload Rapi) -->
                    <div class="space-y-2 pt-2" x-data="{ ssCountText: '' }">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                            Galeri Tangkapan Layar / Foto Produk / Mockup Fitur (Opsional)
                        </label>
                        <p class="text-[11px] text-slate-500 leading-relaxed">
                            Pilih beberapa foto produk fisik, mockup buku, atau tangkapan layar antarmuka sistem (Maksimal 5 berkas, masing-masing maks 4 MB).
                        </p>

                        <div class="flex items-center gap-2 w-full pt-1">
                            <input type="file" 
                                   id="screenshotsInput" 
                                   name="screenshots[]" 
                                   multiple 
                                   accept="image/jpeg,image/png,image/jpg,image/webp" 
                                   class="hidden"
                                   @change="if($event.target.files.length) { ssCountText = $event.target.files.length + ' file foto dipilih'; } else { ssCountText = ''; }" />

                            <button type="button" 
                                    @click="document.getElementById('screenshotsInput').click()"
                                    class="px-3.5 py-2 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-bold rounded-xl transition shrink-0 cursor-pointer shadow-2xs">
                                Choose File
                            </button>

                            <div class="min-w-0 flex-1 truncate text-xs">
                                <template x-if="ssCountText">
                                    <span class="text-blue-700 font-bold truncate block" x-text="ssCountText"></span>
                                </template>
                                <template x-if="!ssCountText">
                                    <span class="text-slate-400">No file chosen</span>
                                </template>
                            </div>
                        </div>
                        <x-input-error :messages="$errors->get('screenshots')" class="mt-1" />
                    </div>
                </div>

                <!-- BAGIAN 5: MANFAAT MASYARAKAT, DOKUMENTASI DESA & VIDEO -->
                <div class="bg-gradient-to-br from-emerald-50/50 via-white to-teal-50/30 rounded-2xl sm:rounded-3xl p-5 sm:p-7 border border-emerald-200/80 shadow-xs space-y-6">
                    <div class="pb-3 border-b border-emerald-100">
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-600 text-white uppercase tracking-wider">
                                Bagian 5
                            </span>
                            <h3 class="font-bold text-base text-emerald-950">Dampak Sosial & Dokumentasi Lapangan</h3>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-emerald-950 uppercase tracking-wider mb-1.5">
                            Uraian Manfaat untuk Masyarakat & Pelayanan Dinas <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="community_benefit" rows="3" required 
                                  placeholder="Jelaskan dampak konkret yang dirasakan warga, efisiensi pelayanan yang tercapai, atau kelompok masyarakat sasaran program..." 
                                  class="w-full text-xs sm:text-sm border-emerald-300 rounded-xl focus:ring-emerald-500 focus:border-emerald-500 leading-relaxed">{{ old('community_benefit') }}</textarea>
                    </div>

                    <!-- Dokumentasi Foto Kegiatan Lapangan / Desa (Rapi) -->
                    <div class="space-y-2" x-data="{ cpCountText: '' }">
                        <label class="block text-xs font-bold text-emerald-950 uppercase tracking-wider">
                            Foto Dokumentasi Kegiatan / Presentasi di Lapangan (Opsional)
                        </label>
                        <p class="text-[11px] text-emerald-800 leading-relaxed">
                            Unggah foto kegiatan saat sosialisasi ke warga, presentasi, atau penyerahan sistem (Maksimal 5 foto, masing-masing maks 4 MB).
                        </p>

                        <div class="flex items-center gap-2 w-full pt-1">
                            <input type="file" 
                                   id="communityPhotosInput" 
                                   name="community_photos[]" 
                                   multiple 
                                   accept="image/jpeg,image/png,image/jpg,image/webp" 
                                   class="hidden"
                                   @change="if($event.target.files.length) { cpCountText = $event.target.files.length + ' foto dokumentasi dipilih'; } else { cpCountText = ''; }" />

                            <button type="button" 
                                    @click="document.getElementById('communityPhotosInput').click()"
                                    class="px-3.5 py-2 bg-emerald-100 hover:bg-emerald-200 text-emerald-800 text-xs font-bold rounded-xl transition shrink-0 cursor-pointer shadow-2xs">
                                Choose File
                            </button>

                            <div class="min-w-0 flex-1 truncate text-xs">
                                <template x-if="cpCountText">
                                    <span class="text-emerald-800 font-bold truncate block" x-text="cpCountText"></span>
                                </template>
                                <template x-if="!cpCountText">
                                    <span class="text-slate-400">No file chosen</span>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Tautan Video Dokumentasi YouTube / Drive -->
                    <div>
                        <label class="block text-xs font-bold text-slate-800 uppercase tracking-wider mb-1.5">
                            Tautan Video Dokumentasi (YouTube / Google Drive)
                        </label>
                        <input type="url" name="video_url" value="{{ old('video_url') }}" 
                               placeholder="Contoh: https://youtu.be/jlKU3exv4Oc?si=... atau link rekaman video" 
                               class="w-full text-xs sm:text-sm border-slate-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 bg-white">
                        <p class="text-[11px] text-slate-500 mt-1">
                            Jika menggunakan link YouTube, pemutar video akan otomatis muncul secara interaktif di halaman detail proyek.
                        </p>
                    </div>
                </div>

                <!-- BAGIAN 6: DOKUMEN PENDUKUNG & BUKU PANDUAN TEKNIS -->
                <div class="bg-white rounded-2xl sm:rounded-3xl p-5 sm:p-7 border border-slate-200/80 shadow-xs space-y-4">
                    <div class="pb-3 border-b border-slate-100">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Bagian 6</span>
                        <h3 class="font-bold text-base text-slate-900 mt-0.5">Buku Panduan Teknis & Dokumen Lampiran</h3>
                    </div>

                    <div class="space-y-2" x-data="{ docCountText: '' }">
                        <label class="block text-xs font-bold text-blue-950 uppercase tracking-wider">
                            Unggah Dokumen PDF / Manual Book / Laporan Teknis (Opsional)
                        </label>
                        <p class="text-[11px] text-blue-800 leading-relaxed">
                            Lampirkan buku panduan teknis (*manual book*), SOP penggunaan sistem, atau laporan teknis inovasi (Format: PDF, DOC, DOCX. Maksimal 10 berkas, masing-masing maks 10 MB).
                        </p>

                        <div class="flex items-center gap-2 w-full pt-1">
                            <input type="file" 
                                   id="documentsInput" 
                                   name="documents[]" 
                                   multiple 
                                   accept=".pdf,.doc,.docx,application/pdf" 
                                   class="hidden"
                                   @change="if($event.target.files.length) { docCountText = $event.target.files.length + ' dokumen lampiran dipilih'; } else { docCountText = ''; }" />

                            <button type="button" 
                                    @click="document.getElementById('documentsInput').click()"
                                    class="px-3.5 py-2 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-bold rounded-xl transition shrink-0 cursor-pointer shadow-2xs">
                                Choose File
                            </button>

                            <div class="min-w-0 flex-1 truncate text-xs">
                                <template x-if="docCountText">
                                    <span class="text-blue-700 font-bold truncate block" x-text="docCountText"></span>
                                </template>
                                <template x-if="!docCountText">
                                    <span class="text-slate-400">No file chosen</span>
                                </template>
                            </div>
                        </div>
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
                        Simpan & Publikasikan Proyek
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
                    const placeholder = document.getElementById('coverPlaceholder');
                    if (preview) {
                        preview.src = e.target.result;
                        preview.classList.remove('hidden');
                    }
                    if (placeholder) {
                        placeholder.classList.add('hidden');
                    }
                };
                reader.readAsDataURL(file);
            }
        }
    </script>
    @endpush
</x-app-layout>