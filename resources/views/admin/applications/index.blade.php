<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-gray-900 leading-tight">
            {{ __('Daftar Pengajuan Magang') }}
        </h2>
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
                
                <!-- Form Filter & Search (Responsive Single-Row Flex Layout) -->
                <form method="GET" action="{{ route('admin.applications.index') }}" class="mb-6 bg-gray-50 p-4 rounded-xl border border-gray-200">
                    <div class="flex flex-col lg:flex-row items-center gap-3">
                        <!-- Input Search (flex-[1.5]) -->
                        <div class="flex-[1.5] min-w-[200px] w-full">
                            <x-text-input type="text" name="search" value="{{ request('search') }}" 
                                placeholder="Cari nama mahasiswa, NIM, atau universitas..." class="w-full text-sm h-10" />
                        </div>

                        <!-- Filter Universitas (w-52) -->
                        @if(isset($universities))
                            <div class="w-full lg:w-52">
                                <select name="university_id" class="w-full h-10 text-xs border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm">
                                    <option value="">-- Semua Kampus --</option>
                                    @foreach ($universities as $unv)
                                        <option value="{{ $unv->id }}" {{ request('university_id') == $unv->id ? 'selected' : '' }}>
                                            {{ $unv->name }} ({{ $unv->code }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <!-- Filter Instansi Dinas (Khusus Super Admin) -->
                        @if ($isSuperAdmin && isset($agencies) && count($agencies) > 0)
                            <div class="w-full lg:w-56">
                                <select name="agency_id" class="w-full h-10 text-xs border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm">
                                    <option value="">-- Semua Instansi Dinas --</option>
                                    @foreach ($agencies as $ag)
                                        <option value="{{ $ag->id }}" {{ request('agency_id') == $ag->id ? 'selected' : '' }}>
                                            {{ $ag->agency_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <!-- Filter Unit / Divisi (w-64) -->
                        <div class="w-full lg:w-64">
                            <select name="unit_id" class="w-full h-10 text-xs border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm">
                                <option value="">-- Semua Unit/Divisi --</option>
                                @if (isset($groupedUnits) && $groupedUnits !== null)
                                    @foreach ($groupedUnits as $agencyName => $agencyUnits)
                                        <optgroup label=" {{ $agencyName }}">
                                            @foreach ($agencyUnits as $u)
                                                <option value="{{ $u->id }}" {{ request('unit_id') == $u->id ? 'selected' : '' }}>
                                                    {{ $u->name }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                @elseif(isset($units))
                                    @foreach ($units as $u)
                                        <option value="{{ $u->id }}" {{ request('unit_id') == $u->id ? 'selected' : '' }}>
                                            {{ $u->name }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>

                        <!-- Filter Status (w-44) -->
                        <div class="w-full lg:w-44">
                            <select name="status" class="w-full h-10 text-xs border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm">
                                <option value="">-- Semua Status --</option>
                                <option value="action" {{ request('status') == 'action' ? 'selected' : '' }}>Perlu Tindakan</option>
                                <optgroup label="Status Pengajuan">
                                    @foreach(\App\Enums\ApplicationStatus::cases() as $statusCase)
                                        <option value="{{ $statusCase->value }}" {{ request('status') == $statusCase->value ? 'selected' : '' }}>{{ $statusCase->label() }}</option>
                                    @endforeach
                                </optgroup>
                            </select>
                        </div>

                        <!-- Action Buttons (Height 10 uniform) -->
                        <div class="flex items-center gap-2 w-full lg:w-auto shrink-0">
                            <button type="submit" class="inline-flex items-center justify-center gap-1.5 px-4 h-10 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-lg shadow-sm transition w-full lg:w-auto cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                <span>Filter</span>
                            </button>

                            @if(request('search') || request('status') || request('unit_id') || request('university_id') || request('agency_id'))
                                <a href="{{ route('admin.applications.index') }}" class="inline-flex items-center justify-center px-4 h-10 bg-gray-200 hover:bg-gray-300 text-gray-700 text-xs font-bold rounded-lg shadow-sm transition">
                                    Reset
                                </a>
                            @endif
                        </div>
                    </div>
                </form>

                <!-- Form Bulk Action (Alpine.js) -->
                <form id="bulk-action-form" method="POST" action="{{ route('admin.applications.bulk_update') }}"
                      x-data="{ 
                          selected: [], 
                          selectAll: false, 
                          showModal: false,
                          action: '',
                          rejectionNote: '',
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
                                  this.showModal = true;
                              } else {
                                  if (confirm('Apakah Anda yakin ingin menerima ' + this.selected.length + ' pengajuan terpilih?')) {
                                      this.$el.submit();
                                  }
                              }
                          }
                      }">
                    @csrf
                    <input type="hidden" name="bulk_action" :value="action">
                    
                    <!-- Modal Penolakan Masal -->
                    <div x-show="showModal" class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/50 backdrop-blur-sm" x-cloak style="display: none;">
                        <div @click.away="showModal = false" class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 space-y-4">
                            <h3 class="text-lg font-bold text-slate-900">Tolak Pengajuan Terpilih</h3>
                            <p class="text-xs text-slate-600">Berikan alasan mengapa <span x-text="selected.length" class="font-bold"></span> pengajuan ini ditolak.</p>
                            <textarea name="bulk_rejection_note" x-model="rejectionNote" rows="3" class="w-full rounded-xl border-slate-300 text-sm focus:border-red-500 focus:ring-red-500" placeholder="Contoh: Kuota instansi penuh / Berkas tidak lengkap..."></textarea>
                            <div class="flex justify-end gap-2 pt-2">
                                <button type="button" @click="showModal = false" class="px-4 py-2 text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl text-xs font-bold">Batal</button>
                                <button type="button" @click="const input = $el.closest('form').querySelector('input[name=\'bulk_action\']'); if (input) input.value = 'rejected'; $el.closest('form').submit()" class="px-4 py-2 text-white bg-red-600 hover:bg-red-700 rounded-xl text-xs font-bold" :disabled="!rejectionNote.trim()">Tolak Pengajuan</button>
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
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b bg-gray-50 text-xs font-semibold text-gray-600 uppercase">
                                <th class="p-3 w-10 text-center">
                                    <input type="checkbox" x-model="selectAll" @change="toggleAll" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                                </th>
                                <th class="p-3">Tanggal Pengajuan</th>
                                <th class="p-3">Nama Mahasiswa</th>
                                <th class="p-3">Universitas / Jurusan</th>
                                <th class="p-3">Instansi & Unit Tujuan</th>
                                <th class="p-3">Status</th>
                                <th class="p-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm divide-y">
                            @forelse ($applications as $app)
                                <tr class="border-b hover:bg-gray-50/50">
                                    <td class="p-3 text-center">
                                        @if(in_array($app->statusValue(), ['pending', 'verified']))
                                            <input type="checkbox" name="application_ids[]" value="{{ $app->id }}" x-model="selected" class="bulk-cb rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                                        @endif
                                    </td>
                                    <td class="p-3 text-xs text-gray-500 font-mono">
                                        {{ $app->created_at->format('d M Y, H:i') }}
                                    </td>
                                    <td class="p-3 font-semibold text-gray-900">
                                        <div class="leading-snug">{{ $app->user?->name ?? 'Mahasiswa' }}</div>
                                    </td>
                                    <td class="p-3 text-gray-600">{{ $app->user?->studentProfile?->universitas ?? '-' }} <br><span class="text-xs text-gray-400">({{ $app->user?->studentProfile?->jurusan ?? '-' }})</span></td>
                                    <td class="p-3">
                                        <div class="space-y-1">
                                            <div class="font-bold text-gray-900 leading-snug">
                                                {{ $app->unit->name ?? '-' }}
                                            </div>
                                            @if($app->unit?->agencyProfile)
                                                <div class="inline-flex items-center gap-1.5 px-2 py-0.5 bg-blue-50/90 border border-blue-200/80 rounded-md text-[11px] text-blue-800 font-semibold">
                                                    <svg class="w-3 h-3 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                                    </svg>
                                                    <span class="truncate max-w-[220px]" title="{{ $app->unit->agencyProfile->agency_name }}">
                                                        {{ $app->unit->agencyProfile->agency_name }}
                                                    </span>
                                                </div>
                                            @else
                                                <span class="text-[11px] text-gray-400">Pemerintah Kota Surabaya</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="p-3 whitespace-nowrap">
                                        @php
                                            $actionHint = $app->actionHint();
                                            $waitingEvaluation = !$actionHint && in_array($app->statusValue(), ['accepted', 'active'], true)
                                                && $app->has_approved_report && !$app->has_complete_evaluation;
                                            $waitingLogbook = !$actionHint && in_array($app->statusValue(), ['accepted', 'active'], true)
                                                && $app->has_approved_report && $app->has_complete_evaluation && !$app->has_filled_logbook;
                                        @endphp
                                        <x-status-badge :status="$app->status"
                                            :tooltip="$app->statusValue() === 'rejected' && $app->rejection_note ? 'Alasan: ' . $app->rejection_note : null" />
                                        @if($app->isPastEndDate())
                                            <div class="mt-1.5 inline-flex px-2 py-0.5 rounded-md bg-rose-50 text-rose-700 border border-rose-200 text-[10px] font-bold" title="Tanggal selesai {{ \Carbon\Carbon::parse($app->end_date)->translatedFormat('d M Y') }}">Lewat masa magang {{ $app->daysPastEndDate() }} hari</div>
                                        @endif
                                        @if($actionHint)
                                            <div class="mt-1.5 text-xs font-semibold text-amber-700">{{ $actionHint }}</div>
                                        @elseif($waitingLogbook)
                                            <div class="mt-1.5 text-xs font-semibold text-rose-600">Logbook belum diisi</div>
                                        @elseif($waitingEvaluation)
                                            <div class="mt-1.5 text-xs text-slate-500">Menunggu nilai evaluasi</div>
                                        @endif
                                    </td>
                                    <td class="p-3 text-center whitespace-nowrap">
                                        <a href="{{ route('admin.applications.show', $app->id) }}" 
                                           class="inline-flex items-center justify-center gap-1 px-3 py-1.5 bg-blue-50 hover:bg-blue-600 text-blue-700 hover:text-white text-xs font-bold rounded-xl border border-blue-200/80 transition duration-150 shadow-2xs cursor-pointer group">
                                            <span>Detail</span>
                                            <svg class="w-3.5 h-3.5 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                            </svg>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="p-6 text-center text-gray-500">Tidak ada pengajuan magang yang sesuai kriteria pencarian.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Kartu Data Pengajuan Khusus Mobile (< 768px) -->
                <div class="md:hidden space-y-3">
                    @forelse ($applications as $app)
                        @php
                            $rawStatus = $app->statusValue();
                            $actionHint = $app->actionHint();
                            $waitingEvaluation = !$actionHint && in_array($rawStatus, ['accepted', 'active'], true)
                                && $app->has_approved_report && !$app->has_complete_evaluation;
                            $waitingLogbook = !$actionHint && in_array($rawStatus, ['accepted', 'active'], true)
                                && $app->has_approved_report && $app->has_complete_evaluation && !$app->has_filled_logbook;
                        @endphp
                        <div class="p-4 bg-white rounded-2xl border border-slate-200/80 shadow-2xs space-y-3 relative">
                            @if(in_array($rawStatus, ['pending', 'verified']))
                                <div class="absolute top-4 right-4 z-10">
                                    <input type="checkbox" name="application_ids[]" value="{{ $app->id }}" x-model="selected" class="bulk-cb w-5 h-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                                </div>
                            @endif
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0 pr-2">
                                    <h4 class="font-bold text-sm text-slate-900 leading-snug truncate">{{ $app->user?->name ?? 'Mahasiswa' }}</h4>
                                    <p class="text-xs text-slate-500 mt-0.5">{{ $app->user?->studentProfile?->universitas ?? '-' }} <span class="text-slate-400">({{ $app->user?->studentProfile?->jurusan ?? '-' }})</span></p>
                                </div>
                                <div class="text-right shrink-0 {{ in_array($rawStatus, ['pending', 'verified']) ? 'pr-8' : '' }}">
                                    <x-status-badge :status="$app->status" />
                                    @if($app->isPastEndDate())
                                        <div class="mt-1 text-[10px] font-bold text-rose-700">Lewat {{ $app->daysPastEndDate() }} hari</div>
                                    @endif
                                </div>
                            </div>

                            @if($actionHint)
                                <div class="text-xs font-semibold text-amber-700">{{ $actionHint }}</div>
                            @elseif($waitingLogbook)
                                <div class="text-xs font-semibold text-rose-600">Logbook belum diisi</div>
                            @elseif($waitingEvaluation)
                                <div class="text-xs text-slate-500">Menunggu nilai evaluasi</div>
                            @endif

                            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100 text-xs space-y-1.5">
                                <div class="flex items-start justify-between text-slate-600 gap-2">
                                    <span class="text-slate-400 shrink-0">Instansi & Unit:</span>
                                    <div class="text-right">
                                        <div class="font-bold text-slate-900">{{ $app->unit->name ?? '-' }}</div>
                                        @if($app->unit?->agencyProfile)
                                            <div class="text-[11px] text-blue-700 font-medium mt-0.5">{{ $app->unit->agencyProfile->agency_name }}</div>
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