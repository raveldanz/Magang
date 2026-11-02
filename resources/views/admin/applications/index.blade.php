<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h2 class="font-bold text-2xl text-gray-900 leading-tight">
                    {{ __('Daftar Pengajuan Magang') }}
                </h2>
                @if(auth()->user()->isBidangAdmin())
                    <p class="text-xs text-blue-600 font-semibold mt-0.5">
                        Bidang: {{ auth()->user()->unit->name ?? 'Bidang Kerja' }}
                    </p>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-5 sm:py-8 lg:py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            <!-- Active University Filter Indicator Banner -->
            @if(isset($selectedUniversity) && $selectedUniversity)
                <div class="p-4 bg-indigo-50/80 border border-indigo-200 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-indigo-900 shadow-2xs">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-indigo-600 text-white flex items-center justify-center shrink-0 shadow-xs">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
                        </div>
                        <div>
                            <span class="font-medium text-indigo-700">Filter Aktif Perguruan Tinggi:</span>
                            <h4 class="font-black text-sm text-indigo-950">{{ $selectedUniversity->name }} {{ $selectedUniversity->code ? "({$selectedUniversity->code})" : '' }}</h4>
                        </div>
                    </div>
                    <div class="flex items-center flex-wrap gap-2 shrink-0">
                        <a href="{{ route('admin.universities.show', $selectedUniversity->id) }}" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-xs transition">
                            Pusat Kendali Kampus
                        </a>
                        <a href="{{ route('admin.applications.index') }}" class="px-3 py-1.5 bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 font-bold rounded-xl transition">
                            Reset Filter
                        </a>
                    </div>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm rounded-2xl sm:rounded-3xl p-4 sm:p-6 border border-slate-100">
                
                <!-- Form Filter & Search (Responsive 2-Row Grid Layout - No Squishing) -->
                <form method="GET" action="{{ route('admin.applications.index') }}" class="mb-6 bg-slate-50/90 p-4 sm:p-5 rounded-2xl border border-slate-200/90 shadow-2xs">
                    <!-- Baris 1: Search Mahasiswa & Action Buttons -->
                    <div class="flex flex-col sm:flex-row items-center gap-3 mb-3">
                        <!-- Input Search Utama -->
                        <div class="relative flex-1 w-full">
                            <div class="absolute inset-y-0 left-0 flex items-center pointer-events-none text-slate-400" style="padding-left: 1rem !important;">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input type="text" name="search" value="{{ request('search') }}" 
                                placeholder="Cari nama mahasiswa, NIM, atau universitas..." 
                                class="w-full text-xs sm:text-sm bg-white border border-gray-300 rounded-xl focus:border-blue-500 focus:ring-1 focus:ring-blue-500 shadow-2xs font-medium placeholder:text-gray-400 h-10"
                                style="padding-left: 2.75rem !important; padding-right: 1rem !important;" />
                        </div>

                        <!-- Tombol Terapkan Filter & Reset -->
                        <div class="flex items-center gap-2 w-full sm:w-auto shrink-0">
                            <button type="submit" class="inline-flex items-center justify-center gap-2 px-5 h-10 bg-blue-600 hover:bg-blue-700 active:scale-98 text-white text-xs font-bold rounded-xl shadow-xs transition duration-150 w-full sm:w-auto cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                                </svg>
                                <span>Terapkan Filter</span>
                            </button>

                            @if(request()->hasAny(['search', 'status', 'unit_id', 'university_id', 'agency_id', 'registration_type']))
                                <a href="{{ route('admin.applications.index') }}" class="inline-flex items-center justify-center px-4 h-10 bg-white hover:bg-gray-100 text-gray-700 border border-gray-200 text-xs font-semibold rounded-xl shadow-2xs transition duration-150">
                                    Reset
                                </a>
                            @endif
                        </div>
                    </div>

                    <!-- Baris 2: 4 Dropdown Filter Proporsional (Grid 4 Kolom Lega) -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                        <!-- Filter Universitas -->
                        @if(isset($universities))
                            <div>
                                <select name="university_id" class="w-full h-10 text-xs border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-xl shadow-2xs bg-white font-medium">
                                    <option value="">-- Semua Kampus --</option>
                                    @foreach ($universities as $unv)
                                        <option value="{{ $unv->id }}" {{ request('university_id') == $unv->id ? 'selected' : '' }}>
                                            {{ $unv->name }} ({{ $unv->code }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <!-- Filter Unit / Divisi -->
                        <div>
                            @if (auth()->user()->unit_id && ! auth()->user()->isSuperAdmin())
                                <div class="w-full flex items-center px-3.5 h-10 bg-blue-50 border border-blue-200 text-blue-700 text-xs font-semibold rounded-xl shadow-2xs">
                                    <span class="truncate">Bidang: {{ auth()->user()->unit->name ?? 'Bidang Kerja' }}</span>
                                    <input type="hidden" name="unit_id" value="{{ auth()->user()->unit_id }}">
                                </div>
                            @else
                                <select name="unit_id" class="w-full h-10 text-xs border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-xl shadow-2xs bg-white font-medium">
                                    <option value="">-- Semua Unit/Divisi --</option>
                                    @if (isset($groupedUnits) && $groupedUnits !== null)
                                        @foreach ($groupedUnits as $agencyName => $agencyUnits)
                                            @if(count($groupedUnits) > 1)
                                                <optgroup label=" {{ $agencyName }}">
                                            @endif
                                                @foreach ($agencyUnits as $u)
                                                    <option value="{{ $u->id }}" {{ request('unit_id') == $u->id ? 'selected' : '' }}>
                                                        {{ $u->name }}
                                                    </option>
                                                @endforeach
                                            @if(count($groupedUnits) > 1)
                                                </optgroup>
                                            @endif
                                        @endforeach
                                    @elseif(isset($units))
                                        @foreach ($units as $u)
                                            <option value="{{ $u->id }}" {{ request('unit_id') == $u->id ? 'selected' : '' }}>
                                                {{ $u->name }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            @endif
                        </div>

                        <!-- Filter Status -->
                        <div>
                            <select name="status" class="w-full h-10 text-xs border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-xl shadow-2xs bg-white font-medium">
                                <option value="">-- Semua Status --</option>
                                <option value="action" {{ request('status') == 'action' ? 'selected' : '' }}>Perlu Tindakan</option>
                                <optgroup label="Status Pengajuan">
                                    @foreach(\App\Enums\ApplicationStatus::cases() as $statusCase)
                                        <option value="{{ $statusCase->value }}" {{ request('status') == $statusCase->value ? 'selected' : '' }}>{{ $statusCase->label() }}</option>
                                    @endforeach
                                </optgroup>
                            </select>
                        </div>

                        <!-- Filter Tipe Pendaftaran (Individu / Kelompok) -->
                        <div>
                            <select name="registration_type" class="w-full h-10 text-xs border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-xl shadow-2xs bg-white font-medium">
                                <option value="">-- Semua Moda --</option>
                                <option value="individual" {{ request('registration_type') == 'individual' ? 'selected' : '' }}>Individu Saja</option>
                                <option value="group" {{ request('registration_type') == 'group' ? 'selected' : '' }}>Kelompok Saja</option>
                            </select>
                        </div>
                    </div>
                </form>

                <!-- Form Bulk Action (Alpine.js) -->
                <form id="bulk-action-form" method="POST" action="{{ route('admin.applications.bulk_update') }}"
                      x-data="{ 
                          selected: [], 
                          selectAll: false, 
                          showRejectModal: false,
                          showApproveModal: false,
                          approveKeyword: '',
                          action: '',
                          rejectionNote: '',
                          get approveKeywordValid() {
                              return this.approveKeyword.trim().toUpperCase() === 'SETUJUI';
                          },
                          toggleAll() {
                              if (this.selectAll) {
                                  const values = Array.from(document.querySelectorAll('.bulk-cb')).map(cb => cb.value);
                                  this.selected = [...new Set(values)];
                              } else {
                                  this.selected = [];
                              }
                          },
                          submitBulk(type) {
                              this.action = type;
                              const input = this.$el.querySelector('input[name=\'bulk_action\']');
                              if (input) input.value = type;
                              if (type === 'rejected') {
                                  this.showRejectModal = true;
                              } else {
                                  this.approveKeyword = '';
                                  this.showApproveModal = true;
                              }
                          },
                          executeBulkSubmit(actionType) {
                              const form = document.getElementById('bulk-action-form');
                              form.querySelectorAll('.injected-bulk-id').forEach(el => el.remove());
                              [...new Set(this.selected)].forEach(id => {
                                  const input = document.createElement('input');
                                  input.type = 'hidden';
                                  input.name = 'application_ids[]';
                                  input.value = id;
                                  input.className = 'injected-bulk-id';
                                  form.appendChild(input);
                              });
                              const actionInput = form.querySelector('input[name=\'bulk_action\']');
                              if (actionInput) actionInput.value = actionType;
                              form.submit();
                          },
                          submitApproval() {
                              if (!this.approveKeywordValid) return;
                              this.executeBulkSubmit('accepted');
                          },
                          closeApproveModal() {
                              this.showApproveModal = false;
                              this.approveKeyword = '';
                          }
                      }">
                    @csrf
                    <input type="hidden" name="bulk_action" :value="action">

                    <!-- ═══════════════════════════════════════════════════════════ -->
                    <!-- Modal Konfirmasi Persetujuan Massal (Two-Step Confirmation) -->
                    <!-- ═══════════════════════════════════════════════════════════ -->
                    <div x-show="showApproveModal" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0"
                         @keydown.escape.window="if(showApproveModal) closeApproveModal()"
                         class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/60 backdrop-blur-sm p-4" 
                         x-cloak style="display: none;">
                        <div x-show="showApproveModal"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                             x-transition:leave-end="opacity-0 translate-y-4 scale-95"
                             @click.outside="closeApproveModal()" 
                             class="bg-white rounded-3xl shadow-2xl border border-slate-100 w-full max-w-lg overflow-hidden p-6 sm:p-7 space-y-5 text-left relative max-h-[92vh] flex flex-col">

                            <!-- Header -->
                            <div class="flex items-start gap-3.5 shrink-0">
                                <div class="w-12 h-12 rounded-2xl bg-emerald-50 border border-emerald-100 text-emerald-600 flex items-center justify-center shrink-0 shadow-xs">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-0.5">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-800">
                                            Konfirmasi Persetujuan Massal
                                        </span>
                                    </div>
                                    <h3 class="text-base sm:text-lg font-black text-slate-900 leading-tight">Setujui Pengajuan Magang</h3>
                                    <p class="text-xs text-slate-500 mt-0.5">
                                        Tindakan ini memerlukan <strong>2 lapis verifikasi</strong> untuk memastikan keputusan yang tepat.
                                    </p>
                                </div>
                                <button type="button" @click="closeApproveModal()" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg transition cursor-pointer" title="Tutup">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>

                            <!-- Scrollable Body -->
                            <div class="flex-1 overflow-y-auto space-y-4 pr-1">
                                <!-- LAPIS 1: Ringkasan Dampak -->
                                <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/90 space-y-2">
                                    <div class="flex items-center justify-between text-xs font-bold text-slate-700">
                                        <span class="flex items-center gap-1.5 text-slate-800 font-extrabold">
                                            <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-[10px] font-black">1</span>
                                            Ringkasan Dampak Aksi
                                        </span>
                                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-black bg-emerald-100 text-emerald-700">
                                            <span x-text="selected.length"></span> Pengajuan Dipilih
                                        </span>
                                    </div>

                                    <div class="p-3 rounded-xl bg-white border border-slate-200/80 space-y-2">
                                        <div class="flex items-start gap-2.5">
                                            <div class="w-7 h-7 rounded-lg bg-purple-50 border border-purple-100 text-purple-600 flex items-center justify-center shrink-0 mt-0.5">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </div>
                                            <div class="text-xs">
                                                <p class="font-bold text-slate-800 leading-snug">Status akan berubah ke: <span class="text-purple-700">{{ \App\Enums\ApplicationStatus::WAITING_APPROVAL->label() }}</span> {{-- status-guard:ignore --}}</p>
                                                <p class="text-slate-500 mt-0.5 leading-relaxed">Pengajuan akan diteruskan ke <strong>Atasan (Tim Kerja Umum &amp; Kepegawaian)</strong> untuk persetujuan akhir sesuai alur hierarki sistem.</p>
                                            </div>
                                        </div>

                                        <div class="flex items-start gap-2.5 pt-2 border-t border-slate-100">
                                            <div class="w-7 h-7 rounded-lg bg-amber-50 border border-amber-100 text-amber-600 flex items-center justify-center shrink-0 mt-0.5">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                                </svg>
                                            </div>
                                            <div class="text-xs">
                                                <p class="font-bold text-amber-800 leading-snug">Perhatian</p>
                                                <p class="text-slate-500 mt-0.5 leading-relaxed">Pengajuan yang kuota unit-nya sudah penuh atau statusnya tidak valid akan otomatis dilewati oleh sistem.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- LAPIS 2: Ketik Konfirmasi -->
                                <div class="space-y-2">
                                    <div class="flex items-center gap-1.5 text-xs font-extrabold text-slate-800">
                                        <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-[10px] font-black">2</span>
                                        Ketik Konfirmasi Persetujuan
                                    </div>
                                    <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 space-y-2">
                                        <p class="text-xs text-slate-600 leading-snug">
                                            Ketik kata kunci tepat berikut untuk membuka kunci tombol eksekusi:
                                        </p>
                                        <div class="flex items-center gap-2">
                                            <span class="select-all font-mono font-black text-sm tracking-wider px-3 py-1 rounded-xl border shadow-2xs bg-emerald-100/70 border-emerald-300 text-emerald-800">SETUJUI</span>
                                            <span class="text-[11px] text-slate-400 italic">(huruf kapital)</span>
                                        </div>
                                        <div class="relative">
                                            <input type="text"
                                                   x-model="approveKeyword"
                                                   @input="approveKeyword = $event.target.value.toUpperCase()"
                                                   placeholder="Ketik 'SETUJUI' di sini..."
                                                   class="w-full px-3.5 py-2 text-xs font-mono font-bold rounded-xl border transition uppercase tracking-wider pr-10"
                                                   :class="approveKeywordValid 
                                                       ? 'border-emerald-400 ring-2 ring-emerald-500/20 bg-emerald-50/30 text-emerald-900' 
                                                       : 'border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20'">
                                            <div x-show="approveKeywordValid" 
                                                 class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-emerald-600">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Footer -->
                            <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center sm:justify-end gap-2.5 pt-3 border-t border-slate-100 shrink-0">
                                <button type="button" 
                                        @click="closeApproveModal()" 
                                        class="w-full sm:w-auto px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition text-center cursor-pointer">
                                    Batal
                                </button>
                                <button type="button" 
                                        :disabled="!approveKeywordValid" 
                                        @click="submitApproval()" 
                                        class="w-full sm:w-auto px-6 py-2.5 text-xs font-bold rounded-xl transition flex items-center justify-center gap-2 shadow-sm"
                                        :class="approveKeywordValid 
                                            ? 'bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white cursor-pointer shadow-emerald-600/20' 
                                            : 'bg-slate-200 text-slate-400 cursor-not-allowed border border-slate-300/60'">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>Setujui & Teruskan ke Atasan</span>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- ═══════════════════════════════════════════════════ -->
                    <!-- Modal Penolakan Massal                            -->
                    <!-- ═══════════════════════════════════════════════════ -->
                    <div x-show="showRejectModal" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0"
                         @keydown.escape.window="if(showRejectModal) showRejectModal = false"
                         class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/60 backdrop-blur-sm p-4" 
                         x-cloak style="display: none;">
                        <div x-show="showRejectModal"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                             x-transition:leave-end="opacity-0 translate-y-4 scale-95"
                             @click.outside="showRejectModal = false" 
                             class="bg-white rounded-3xl shadow-2xl border border-slate-100 w-full max-w-md p-6 sm:p-7 space-y-4">
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 rounded-xl bg-rose-50 border border-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-base font-black text-slate-900">Tolak Pengajuan Terpilih</h3>
                                    <p class="text-xs text-slate-500 mt-0.5">Berikan alasan mengapa <span x-text="selected.length" class="font-bold text-rose-600"></span> pengajuan ini ditolak.</p>
                                </div>
                                <button type="button" @click="showRejectModal = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg transition cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                            <textarea name="bulk_rejection_note" x-model="rejectionNote" rows="3" class="w-full rounded-xl border-slate-300 text-sm focus:border-red-500 focus:ring-red-500" placeholder="Contoh: Kuota instansi penuh / Berkas tidak lengkap..."></textarea>
                            <div class="flex justify-end gap-2 pt-2">
                                <button type="button" @click="showRejectModal = false" class="px-4 py-2 text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl text-xs font-bold cursor-pointer">Batal</button>
                                <button type="button" @click="executeBulkSubmit('rejected')" class="px-4 py-2 text-white bg-red-600 hover:bg-red-700 rounded-xl text-xs font-bold cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed" :disabled="!rejectionNote.trim()">Tolak Pengajuan</button>
                            </div>
                        </div>
                    </div>


                    <!-- Floating Action Bar -->
                    <div x-show="selected.length > 0"
                         x-transition:enter="transition ease-out duration-300 transform"
                         x-transition:enter-start="opacity-0 translate-y-8 scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                         x-transition:leave="transition ease-in duration-200 transform"
                         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                         x-transition:leave-end="opacity-0 translate-y-8 scale-95"
                         class="fixed bottom-6 left-1/2 -translate-x-1/2 z-[90] bg-slate-900/95 backdrop-blur-md text-white border border-slate-700/60 shadow-2xl rounded-2xl px-5 py-3 flex items-center justify-between w-max max-w-full gap-4 sm:gap-6" 
                         x-cloak style="display: none;">
                        
                        <!-- Left: Info & Cancel -->
                        <div class="flex items-center gap-3">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center justify-center bg-blue-600 text-white text-xs font-semibold px-2.5 py-1 rounded-full" x-text="selected.length"></span>
                                <span class="text-sm font-medium text-slate-200 hidden sm:inline">pengajuan dipilih</span>
                            </div>
                            <button type="button" @click="selected = []; selectAll = false;" class="text-xs text-slate-400 hover:text-white underline transition">
                                Batal
                            </button>
                        </div>
                        
                        <!-- Divider -->
                        <div class="h-6 w-px bg-slate-700 hidden sm:block"></div>

                        <!-- Right: Actions -->
                        <div class="flex items-center gap-2">
                            <button type="button" @click="submitBulk('rejected')" class="bg-rose-600 hover:bg-rose-500 text-white text-sm font-medium px-4 py-2 rounded-xl transition shadow-sm inline-flex items-center gap-2 cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <span>Tolak</span>
                            </button>
                            <button type="button" @click="submitBulk('accepted')" class="bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-medium px-4 py-2 rounded-xl transition shadow-sm inline-flex items-center gap-2 cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <span>Terima</span>
                            </button>
                        </div>
                    </div>

                <!-- Tabel Data Pengajuan (Desktop & Tablet) -->
                <div class="hidden md:block overflow-x-auto rounded-2xl border border-gray-200/90 shadow-2xs overflow-hidden">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead class="bg-slate-50/90 text-slate-500 font-bold uppercase tracking-wider text-[11px] whitespace-nowrap border-b border-slate-200/90">
                            <tr>
                                <th class="py-3.5 pl-8 sm:pl-10 pr-3 w-12 text-center whitespace-nowrap">
                                    <input type="checkbox" x-model="selectAll" @change="toggleAll" class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                                </th>
                                <th class="py-3.5 pl-3 pr-3 text-left">Pemohon / Tim</th>
                                <th class="py-3.5 px-3 text-left">Bidang &amp; Mentor</th>
                                <th class="py-3.5 px-3 text-left whitespace-nowrap">Tanggal Pengajuan</th>
                                <th class="py-3.5 px-3 text-center whitespace-nowrap">Status</th>
                                <th class="py-3.5 px-6 text-center whitespace-nowrap">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm divide-y divide-slate-100">
                        @forelse ($applications as $app)
                            @php
                                $isGroup = $app->isGroup() && $app->group;
                                $rawStatus = $app->statusValue();
                                $isActive = $rawStatus === \App\Enums\ApplicationStatus::ACTIVE->value;
                                $isReadyToComplete = $app->canBeCompleted();
                                $actionHint = $app->actionHint();
                                $waitingEvaluation = !$actionHint && $isActive
                                    && $app->has_approved_report && !$app->has_complete_evaluation;
                                $waitingLogbook = !$actionHint && $isActive
                                    && $app->has_approved_report && $app->has_complete_evaluation && !$app->has_filled_logbook;
                            @endphp

                            @if($isGroup)
                                @php
                                    $groupMembers = $app->group->applications;
                                    $groupMemberCount = $groupMembers->count();
                                    $groupStatuses = $groupMembers->map(fn($a) => $a->statusValue())->unique();
                                    $isUniformGroupStatus = $groupStatuses->count() === 1;
                                    $groupPendingOrWaiting = in_array($app->group->status, ['pending', 'waiting_approval']) || in_array($rawStatus, ['pending', 'waiting_approval']);
                                @endphp
                                <tbody class="text-sm divide-y divide-slate-100 border-b border-slate-200/80" 
                                       x-data="{ expanded: false }">
                                    <tr class="hover:bg-indigo-50/30 transition-colors duration-150" :class="selected.includes('{{ $app->id }}') ? 'bg-indigo-50/50' : 'bg-slate-50/20'">
                                        <td class="py-3.5 pl-8 sm:pl-10 pr-3 w-12 text-center whitespace-nowrap align-middle">
                                            @if($groupPendingOrWaiting)
                                                <input type="checkbox" name="application_ids[]" value="{{ $app->id }}" x-model="selected" class="bulk-cb w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                                            @endif
                                        </td>
                                        <td class="py-3.5 pl-3 pr-3 align-middle">
                                            <div class="flex items-center gap-2.5">
                                                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 text-white flex items-center justify-center shrink-0 shadow-2xs cursor-help" title="Jalur Kelompok #{{ $app->group->code }}">
                                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                                    </svg>
                                                </div>
                                                <div class="min-w-0 max-w-[240px] sm:max-w-[280px]">
                                                    <!-- Baris 1: Nama Tim + Pill Button [👥 X Anggota ⌄] -->
                                                    <div class="flex items-center gap-1.5 min-w-0">
                                                        <a href="{{ route('admin.applications.show', $app->id) }}" 
                                                           class="font-bold text-slate-900 text-xs sm:text-sm hover:text-indigo-600 transition leading-tight truncate" 
                                                           title="{{ $app->group->name }}">
                                                            {{ $app->group->name }}
                                                        </a>
                                                        <button type="button" 
                                                                @click="expanded = !expanded" 
                                                                class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md text-[10px] font-bold bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200/90 transition cursor-pointer shrink-0"
                                                                :class="expanded ? 'bg-indigo-100 ring-1 ring-indigo-300' : ''"
                                                                :title="expanded ? 'Tutup daftar anggota' : 'Buka daftar anggota'">
                                                            <svg class="w-3 h-3 text-indigo-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                                            </svg>
                                                            <span>{{ $groupMemberCount }} Anggota</span>
                                                            <svg class="w-3 h-3 transition-transform duration-200 shrink-0" :class="expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                            </svg>
                                                        </button>
                                                    </div>

                                                    <!-- Baris 2: Ketua: [Nama Ketua] • [Nama Universitas] -->
                                                    <div class="text-[11px] text-slate-500 mt-0.5 leading-tight truncate font-normal" 
                                                         title="Ketua: {{ $app->group->leader?->name ?? $app->user?->name ?? 'Mahasiswa' }} • {{ $app->user?->studentProfile?->universitas ?? '-' }}">
                                                        <span class="text-slate-600">Ketua: <span class="font-medium text-slate-700">{{ $app->group->leader?->name ?? $app->user?->name ?? 'Mahasiswa' }}</span></span>
                                                        <span class="text-slate-300 mx-1">•</span>
                                                        <span class="text-slate-500">{{ $app->user?->studentProfile?->universitas ?? '-' }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3.5 px-3 align-middle">
                                            <div class="space-y-0.5 max-w-[200px] sm:max-w-[240px]">
                                                <div class="font-bold text-slate-900 text-xs sm:text-sm leading-tight truncate" title="{{ $app->unit->name ?? '-' }}">
                                                    {{ $app->unit->name ?? '-' }}
                                                </div>
                                                @if($app->placement?->mentor)
                                                    <div class="text-[11px] text-slate-600 leading-tight truncate flex items-center gap-1" title="Mentor: {{ $app->placement->mentor->name }}">
                                                        <span class="text-slate-400 font-medium">Mentor:</span>
                                                        <span class="text-slate-800 font-semibold truncate">{{ $app->placement->mentor->name }}</span>
                                                    </div>
                                                @else
                                                    <div class="text-[11px] text-amber-600 font-semibold italic leading-tight truncate">
                                                        Mentor belum ditetapkan
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="py-3.5 px-3 whitespace-nowrap align-middle">
                                            <div class="text-xs font-semibold text-slate-800">
                                                {{ $app->group->created_at ? $app->group->created_at->translatedFormat('d M Y') : $app->created_at->translatedFormat('d M Y') }}
                                            </div>
                                            <div class="text-[11px] text-slate-400 font-mono mt-0.5">
                                                {{ $app->group->created_at ? $app->group->created_at->format('H:i') : $app->created_at->format('H:i') }} WIB
                                            </div>
                                        </td>
                                        <td class="py-3.5 px-3 whitespace-nowrap text-center align-middle">
                                            @php
                                                $groupTooltip = null;
                                                if ($app->isPastEndDate()) {
                                                    $groupTooltip = 'Lewat masa magang ' . $app->daysPastEndDate() . ' hari';
                                                } elseif (! $isUniformGroupStatus) {
                                                    $groupTooltip = 'Status Anggota Beragam';
                                                } elseif ($rawStatus === 'rejected' && $app->rejection_note) {
                                                    $groupTooltip = 'Alasan: ' . $app->rejection_note;
                                                }
                                            @endphp
                                            <x-status-badge :status="$app->group->status ?? $app->status"
                                                :tooltip="$groupTooltip" />
                                        </td>
                                        <td class="py-3.5 px-6 text-center whitespace-nowrap align-middle">
                                            <div class="flex items-center justify-center">
                                                <a href="{{ route('admin.applications.show', $app->id) }}" 
                                                    class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 bg-blue-50 hover:bg-blue-600 text-blue-700 hover:text-white text-xs font-bold rounded-xl border border-blue-200/80 transition duration-150 shadow-2xs cursor-pointer group">
                                                    <span>Detail</span>
                                                    <svg class="w-3.5 h-3.5 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                    </svg>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Dropdown Sub-Table Daftar Mahasiswa Anggota -->
                                    <tr x-show="expanded" x-cloak class="bg-indigo-50/20 border-t border-indigo-100">
                                        <td colspan="6" class="p-3 sm:p-5">
                                            <div class="rounded-2xl bg-white border border-indigo-100 p-3 sm:p-4 shadow-xs space-y-3">
                                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-2.5">
                                                    <div class="flex items-center gap-2">
                                                        <span class="w-2.5 h-2.5 rounded-full bg-indigo-600 shrink-0"></span>
                                                        <h5 class="font-extrabold text-xs text-slate-800 uppercase tracking-wider">
                                                            Daftar Mahasiswa Anggota: {{ $app->group->name }}
                                                        </h5>
                                                        <span class="text-xs text-slate-400 font-medium">Total {{ $groupMemberCount }} Mahasiswa</span>
                                                    </div>
                                                    <div class="text-[11px] text-slate-500">
                                                        Buka Detail untuk melihat berkas dan mengelola keputusan per mahasiswa
                                                    </div>
                                                </div>

                                                <div class="overflow-x-auto">
                                                    <table class="w-full text-left text-xs border-collapse">
                                                        <thead>
                                                            <tr class="text-[10px] uppercase font-bold text-slate-400 border-b border-slate-100 bg-slate-50/60">
                                                                <th class="py-2 px-3">Peran</th>
                                                                <th class="py-2 px-3">Nama Mahasiswa &amp; NIM</th>
                                                                <th class="py-2 px-3">Jurusan &amp; Kampus</th>
                                                                <th class="py-2 px-3 text-center">Status Mahasiswa</th>
                                                                <th class="py-2 px-3 text-center">Aksi</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="divide-y divide-slate-100">
                                                            @foreach($groupMembers as $memberApp)
                                                                <tr class="hover:bg-slate-50/80 transition">
                                                                    <td class="py-2.5 px-3 whitespace-nowrap">
                                                                        @if($memberApp->role_in_group === 'leader')
                                                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-black bg-purple-100 text-purple-800 border border-purple-200">
                                                                                Ketua
                                                                            </span>
                                                                        @else
                                                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                                                                Anggota
                                                                            </span>
                                                                        @endif
                                                                    </td>
                                                                    <td class="py-2.5 px-3">
                                                                        <div class="font-bold text-slate-900 text-xs">
                                                                            {{ $memberApp->user?->name ?? 'Mahasiswa' }}
                                                                        </div>
                                                                        <div class="text-[11px] text-slate-400 font-mono">
                                                                            NIM: {{ $memberApp->user?->studentProfile?->nim ?? '-' }}
                                                                        </div>
                                                                    </td>
                                                                    <td class="py-2.5 px-3">
                                                                        <div class="text-slate-700 font-medium text-xs">
                                                                            {{ $memberApp->user?->studentProfile?->jurusan ?? '-' }}
                                                                        </div>
                                                                        <div class="text-[11px] text-slate-400">
                                                                            {{ $memberApp->user?->studentProfile?->universitas ?? '-' }}
                                                                        </div>
                                                                    </td>
                                                                    <td class="py-2.5 px-3 text-center whitespace-nowrap">
                                                                        <x-status-badge :status="$memberApp->status" />
                                                                    </td>
                                                                    <td class="py-2.5 px-3 text-center whitespace-nowrap">
                                                                        <a href="{{ route('admin.applications.show', $memberApp->id) }}" 
                                                                           class="inline-flex items-center gap-1 px-3 py-1 text-[11px] font-bold rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-600 hover:text-white border border-blue-200 transition"
                                                                           title="Lihat Detail &amp; Kelola Keputusan">
                                                                            Detail
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            @else
                                <!-- Jalur Individu Standar -->
                                <tbody class="text-sm divide-y divide-slate-100 border-b border-slate-100">
                                    <tr class="hover:bg-slate-50/80 transition-colors duration-150" :class="selected.includes('{{ $app->id }}') ? 'bg-blue-50/40' : ''">
                                        <td class="py-3.5 pl-8 sm:pl-10 pr-3 w-12 text-center whitespace-nowrap align-middle">
                                            @if(in_array($rawStatus, ['pending', 'waiting_approval']))
                                                <input type="checkbox" name="application_ids[]" value="{{ $app->id }}" x-model="selected" class="bulk-cb w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                                            @endif
                                        </td>
                                        <td class="py-3.5 pl-3 pr-3 align-middle">
                                            <div class="flex items-center gap-2.5">
                                                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-200/70 text-blue-700 font-extrabold text-xs flex items-center justify-center shrink-0 shadow-2xs">
                                                    {{ strtoupper(substr($app->user?->name ?? 'M', 0, 1)) }}
                                                </div>
                                                <div class="min-w-0 max-w-[240px] sm:max-w-[280px]">
                                                    <a href="{{ route('admin.applications.show', $app->id) }}" class="font-bold text-slate-900 text-xs sm:text-sm hover:text-blue-600 transition leading-tight truncate block" title="{{ $app->user?->name ?? 'Mahasiswa' }}">
                                                        {{ $app->user?->name ?? 'Mahasiswa' }}
                                                    </a>
                                                    <div class="text-[11px] text-slate-500 mt-0.5 leading-tight truncate" title="{{ $app->user?->studentProfile?->universitas ?? '-' }}{{ $app->user?->studentProfile?->jurusan ? ' • ' . $app->user?->studentProfile?->jurusan : '' }}">
                                                        <span class="font-medium text-slate-700">{{ $app->user?->studentProfile?->universitas ?? '-' }}</span>
                                                        @if($app->user?->studentProfile?->jurusan)
                                                            <span class="text-slate-300 mx-1">•</span>
                                                            <span class="text-slate-500">{{ $app->user?->studentProfile?->jurusan }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3.5 px-3 align-middle">
                                            <div class="space-y-0.5 max-w-[200px] sm:max-w-[240px]">
                                                <div class="font-bold text-slate-900 text-xs sm:text-sm leading-tight truncate" title="{{ $app->unit->name ?? '-' }}">
                                                    {{ $app->unit->name ?? '-' }}
                                                </div>
                                                @if($app->placement?->mentor)
                                                    <div class="text-[11px] text-slate-600 leading-tight truncate flex items-center gap-1" title="Mentor: {{ $app->placement->mentor->name }}">
                                                        <span class="text-slate-400 font-medium">Mentor:</span>
                                                        <span class="text-slate-800 font-semibold truncate">{{ $app->placement->mentor->name }}</span>
                                                    </div>
                                                @else
                                                    <div class="text-[11px] text-amber-600 font-semibold italic leading-tight truncate">
                                                        Mentor belum ditetapkan
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="py-3.5 px-3 whitespace-nowrap align-middle">
                                            <div class="text-xs font-semibold text-slate-800">
                                                {{ $app->created_at->translatedFormat('d M Y') }}
                                            </div>
                                            <div class="text-[11px] text-slate-400 font-mono mt-0.5">
                                                {{ $app->created_at->format('H:i') }} WIB
                                            </div>
                                        </td>
                                        <td class="py-3.5 px-3 whitespace-nowrap text-center align-middle">
                                            @php
                                                $appTooltip = null;
                                                if ($app->isPastEndDate()) {
                                                    $appTooltip = 'Lewat masa magang ' . $app->daysPastEndDate() . ' hari';
                                                } elseif ($rawStatus === 'rejected' && $app->rejection_note) {
                                                    $appTooltip = 'Alasan: ' . $app->rejection_note;
                                                }
                                            @endphp
                                            <x-status-badge :status="$app->status"
                                                :tooltip="$appTooltip" />
                                        </td>
                                        <td class="py-3.5 px-6 text-center whitespace-nowrap align-middle">
                                            <div class="flex items-center justify-center">
                                                <a href="{{ route('admin.applications.show', $app->id) }}" 
                                                    class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 bg-blue-50 hover:bg-blue-600 text-blue-700 hover:text-white text-xs font-bold rounded-xl border border-blue-200/80 transition duration-150 shadow-2xs cursor-pointer group">
                                                    <span>Detail</span>
                                                    <svg class="w-3.5 h-3.5 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                    </svg>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            @endif
                        @empty
                            <tbody class="text-sm">
                                <tr>
                                    <td colspan="6" class="py-12 text-center text-gray-400">Tidak ada pengajuan magang yang sesuai kriteria pencarian.</td>
                                </tr>
                            </tbody>
                        @endforelse
                    </table>
                </div>

                <!-- Kartu Data Pengajuan Khusus Mobile (< 768px) -->
                <div class="md:hidden space-y-3">
                    @forelse ($applications as $app)
                        @php
                            $isGroup = $app->isGroup() && $app->group;
                            $rawStatus = $app->statusValue();
                            $isActive = $rawStatus === \App\Enums\ApplicationStatus::ACTIVE->value;
                            $isReadyToComplete = $app->canBeCompleted();
                            $actionHint = $app->actionHint();
                            $waitingEvaluation = !$actionHint && $isActive
                                && $app->has_approved_report && !$app->has_complete_evaluation;
                            $waitingLogbook = !$actionHint && $isActive
                                && $app->has_approved_report && $app->has_complete_evaluation && !$app->has_filled_logbook;
                        @endphp

                        @if($isGroup)
                            @php
                                $groupMembers = $app->group->applications;
                                $groupMemberCount = $groupMembers->count();
                                $groupStatuses = $groupMembers->map(fn($a) => $a->statusValue())->unique();
                                $isUniformGroupStatus = $groupStatuses->count() === 1;
                                $groupPendingOrWaiting = in_array($app->group->status, ['pending', 'waiting_approval']) || in_array($rawStatus, ['pending', 'waiting_approval']);
                            @endphp
                            <div class="p-4 bg-white rounded-2xl border border-indigo-200/90 shadow-2xs space-y-3 relative" 
                                 x-data="{ expanded: false }">
                                @if($groupPendingOrWaiting)
                                    <div class="absolute top-4 right-4 z-10">
                                        <input type="checkbox" name="application_ids[]" value="{{ $app->id }}" x-model="selected" class="bulk-cb w-5 h-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                                    </div>
                                @endif
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0 pr-2">
                                        <div class="flex items-center gap-1.5 mb-1">
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-black bg-indigo-100 text-indigo-800 border border-indigo-200 uppercase">
                                                Jalur Kelompok
                                            </span>
                                            <span class="font-mono text-[10px] font-bold text-slate-500">#{{ $app->group->code }}</span>
                                        </div>
                                        <h4 class="font-bold text-sm text-slate-900 leading-snug truncate">{{ $app->group->name }}</h4>
                                        <p class="text-xs text-slate-600 mt-0.5 font-normal">Ketua: {{ $app->group->leader?->name ?? $app->user?->name ?? 'Mahasiswa' }}</p>
                                        <p class="text-xs text-slate-500 mt-0.5">{{ $app->user?->studentProfile?->universitas ?? '-' }}</p>
                                    </div>
                                    <div class="text-right shrink-0 {{ $groupPendingOrWaiting ? 'pr-8' : '' }}">
                                        <x-status-badge :status="$app->group->status ?? $app->status" />
                                        @if(! $isUniformGroupStatus)
                                            <div class="mt-1 text-[10px] font-semibold text-amber-700 bg-amber-50 border border-amber-200 rounded px-1.5 py-0.5 inline-block">
                                                Status Beragam
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100 text-xs space-y-1.5">
                                    <div class="flex items-start justify-between text-slate-600 gap-2">
                                        <span class="text-slate-400 shrink-0">Bidang:</span>
                                        <div class="text-right">
                                            <div class="font-bold text-slate-900">{{ $app->unit->name ?? '-' }}</div>
                                            <div class="text-[11px] text-indigo-700 font-semibold mt-0.5">{{ $groupMemberCount }} Anggota Terdaftar</div>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between text-slate-600">
                                        <span class="text-slate-400">Diajukan:</span>
                                        <span class="font-mono text-slate-500">{{ $app->group->created_at ? $app->group->created_at->format('d M Y, H:i') : $app->created_at->format('d M Y, H:i') }}</span>
                                    </div>
                                </div>

                                <!-- Accordion Button Mobile -->
                                <button type="button" 
                                        @click="expanded = !expanded" 
                                        class="w-full inline-flex items-center justify-between px-3 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-800 text-xs font-bold rounded-xl border border-indigo-200 transition cursor-pointer">
                                    <span x-text="expanded ? 'Tutup Daftar Anggota' : 'Buka Daftar ' + {{ $groupMemberCount }} + ' Anggota'"></span>
                                    <svg class="w-4 h-4 transition-transform duration-200" :class="expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                <!-- Sub List Anggota Mobile -->
                                <div x-show="expanded" x-cloak class="space-y-2 pt-1">
                                    @foreach($groupMembers as $memberApp)
                                        <div class="p-2.5 rounded-xl bg-slate-50/90 border border-slate-200/80 space-y-2">
                                            <div class="flex items-start justify-between gap-2">
                                                <div>
                                                    <div class="flex items-center gap-1.5">
                                                        <span class="px-1.5 py-0.5 rounded text-[9px] font-black {{ $memberApp->role_in_group === 'leader' ? 'bg-purple-100 text-purple-800 border border-purple-200' : 'bg-slate-200 text-slate-700' }}">
                                                            {{ $memberApp->role_in_group === 'leader' ? 'Ketua' : 'Anggota' }}
                                                        </span>
                                                        <span class="font-bold text-xs text-slate-900">{{ $memberApp->user?->name ?? 'Mahasiswa' }}</span>
                                                    </div>
                                                    <p class="text-[11px] text-slate-500 mt-0.5">NIM: {{ $memberApp->user?->studentProfile?->nim ?? '-' }} · {{ $memberApp->user?->studentProfile?->jurusan ?? '-' }}</p>
                                                </div>
                                                <x-status-badge :status="$memberApp->status" />
                                            </div>
                                            <div class="flex items-center justify-end gap-1.5 pt-1 border-t border-slate-200/60">
                                                <a href="{{ route('admin.applications.show', $memberApp->id) }}" class="px-2.5 py-1 text-[10px] font-bold rounded-lg bg-blue-50 text-blue-700 border border-blue-200">
                                                    Detail
                                                </a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <!-- Action Buttons Mobile -->
                                <div class="pt-1">
                                    <a href="{{ route('admin.applications.show', $app->id) }}" class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2.5 bg-blue-50 hover:bg-blue-600 text-blue-700 hover:text-white text-xs font-bold rounded-xl border border-blue-200/80 transition duration-150 shadow-2xs cursor-pointer group">
                                        <span>Lihat Detail</span>
                                        <svg class="w-3.5 h-3.5 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </a>
                                </div>
                            </div>
                        @else
                            <!-- Jalur Individu Mobile -->
                            <div class="p-4 bg-white rounded-2xl border border-slate-200/80 shadow-2xs space-y-3 relative">
                                @if(in_array($rawStatus, ['pending', 'waiting_approval']))
                                    <div class="absolute top-4 right-4 z-10">
                                        <input type="checkbox" name="application_ids[]" value="{{ $app->id }}" x-model="selected" class="bulk-cb w-5 h-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                                    </div>
                                @endif
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0 pr-2">
                                        <h4 class="font-bold text-sm text-slate-900 leading-snug truncate">{{ $app->user?->name ?? 'Mahasiswa' }}</h4>
                                        <p class="text-xs text-slate-500 mt-0.5">{{ $app->user?->studentProfile?->universitas ?? '-' }} <span class="text-slate-400">· {{ $app->user?->studentProfile?->jurusan ?? '-' }}</span></p>
                                    </div>
                                    <div class="text-right shrink-0 {{ in_array($rawStatus, ['pending', 'waiting_approval']) ? 'pr-8' : '' }}">
                                        <x-status-badge :status="$app->status" />
                                        @if($app->isPastEndDate())
                                            <div class="mt-1 text-[10px] font-bold text-rose-700">Lewat {{ $app->daysPastEndDate() }} hari</div>
                                        @endif
                                    </div>
                                </div>

                                @if($isActive && $isReadyToComplete)
                                    <div class="text-xs text-emerald-600 font-medium">Siap diluluskan</div>
                                @elseif($isActive && $app->isPastEndDate() && !$isReadyToComplete)
                                    <div class="text-xs font-semibold text-rose-600">Menunggu Evaluasi Mentor/Kelulusan</div>
                                @elseif($actionHint && $actionHint !== 'Siap diluluskan')
                                    <div class="text-xs font-semibold text-amber-700">{{ $actionHint }}</div>
                                @elseif($waitingLogbook)
                                    <div class="text-xs font-semibold text-rose-600">Logbook belum diisi</div>
                                @elseif($waitingEvaluation)
                                    <div class="text-xs text-slate-500">Menunggu nilai evaluasi</div>
                                @elseif($rawStatus === \App\Enums\ApplicationStatus::ACCEPTED->value)
                                    <div class="text-xs text-indigo-600 font-medium">Pra-Magang</div>
                                @endif

                                <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100 text-xs space-y-1.5">
                                    <div class="flex items-start justify-between text-slate-600 gap-2">
                                        <span class="text-slate-400 shrink-0">Bidang &amp; Mentor:</span>
                                        <div class="text-right">
                                            <div class="font-bold text-slate-900">{{ $app->unit->name ?? '-' }}</div>
                                            @if($app->placement?->mentor)
                                                <div class="text-[11px] text-slate-600 font-medium mt-0.5">Mentor: {{ $app->placement->mentor->name }}</div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between text-slate-600">
                                        <span class="text-slate-400">Diajukan:</span>
                                        <span class="font-mono text-slate-500">{{ $app->created_at->format('d M Y, H:i') }}</span>
                                    </div>
                                </div>

                                @if($rawStatus === 'rejected' && $app->rejection_note)
                                    <div class="p-2.5 rounded-xl bg-rose-50/70 border border-rose-100 text-xs text-rose-800">
                                        <span class="font-bold text-[11px] uppercase tracking-wider block text-rose-900 mb-0.5">Alasan Penolakan:</span>
                                        <p class="italic text-[11px] text-rose-700">"{{ $app->rejection_note }}"</p>
                                    </div>
                                @endif

                                <a href="{{ route('admin.applications.show', $app->id) }}" class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-2.5 bg-blue-50 hover:bg-blue-600 text-blue-700 hover:text-white text-xs font-bold rounded-xl border border-blue-200/80 transition duration-150 shadow-2xs active:scale-95 cursor-pointer group">
                                    <span>Lihat Detail Pengajuan</span>
                                    <svg class="w-3.5 h-3.5 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </div>
                        @endif
                    @empty
                        <div class="p-6 bg-white rounded-2xl border border-slate-200 text-center text-xs text-slate-500">
                            Tidak ada pengajuan magang yang sesuai kriteria pencarian.
                        </div>
                    @endforelse
                </div>

                </form>

                <!-- Paginasi -->
                <div class="mt-6">
                    {{ $applications->links() }}
                </div>

            </div>
        </div>
    </div>
</x-app-layout>