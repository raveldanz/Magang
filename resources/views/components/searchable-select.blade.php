@props([
    'name',
    'id' => null,
    'label' => null,
    'placeholder' => '-- Pilih atau Cari Data --',
    'items' => [],
    'selected' => null,
    'allowCustom' => false,
    'customName' => 'custom_' . $name,
    'customValue' => '',
    'customLabel' => '[+] Tidak terdaftar? Masukkan manual',
    'required' => false,
    'showAsterisk' => true,
    'disabled' => false,
    'class' => '',
    'eventName' => null,
    'parentListener' => null,
])

@php
    $elementId = $id ?? $name . '_' . \Illuminate\Support\Str::random(6);

    // Normalisasi struktur items ke array seragam: id, name, acronym, meta, raw
    $normalizedItems = collect($items)->map(function ($item) {
        if (is_array($item)) {
            $id = $item['id'] ?? null;
            $name = $item['name'] ?? $item['agency_name'] ?? $item['title'] ?? '';
            $acronym = $item['acronym'] ?? $item['code'] ?? null;
            $meta = $item['meta'] ?? $item['email'] ?? $item['description'] ?? $item['nip'] ?? $item['address'] ?? null;
            $keywords = $item['keywords'] ?? null;
            $raw = $item;
        } elseif (is_object($item)) {
            $id = $item->id ?? null;
            $name = $item->name ?? $item->agency_name ?? $item->title ?? '';
            $acronym = $item->acronym ?? $item->code ?? null;
            $meta = $item->meta ?? $item->email ?? $item->description ?? $item->nip ?? $item->address ?? null;
            $keywords = $item->keywords ?? $item->search_keywords ?? null;
            $raw = $item instanceof \Illuminate\Database\Eloquent\Model ? $item->attributesToArray() : (array) $item;
        } else {
            $id = $item;
            $name = (string) $item;
            $acronym = null;
            $meta = null;
            $keywords = null;
            $raw = [];
        }

        return [
            'id' => (string) $id,
            'name' => (string) $name,
            'acronym' => $acronym ? (string) $acronym : null,
            'meta' => $meta ? (string) $meta : null,
            'keywords' => is_array($keywords) ? implode(' ', $keywords) : (string) ($keywords ?? ''),
            'raw' => $raw,
        ];
    })->values()->all();

    $initialSelected = old($name, $selected);
    $initialCustom = old($customName, $customValue);
    $hasInitialCustom = ($initialSelected === 'other') || (!empty($initialCustom));
@endphp

<div 
    x-data="{
        open: false,
        search: '',
        items: {{ \Illuminate\Support\Js::from($normalizedItems) }},
        selectedId: '{{ $initialSelected }}',
        selectedItem: null,
        isCustom: {{ $hasInitialCustom ? 'true' : 'false' }},
        customValue: '{{ addslashes((string) $initialCustom) }}',
        name: '{{ $name }}',
        placeholder: '{{ addslashes($placeholder) }}',
        allowCustom: {{ $allowCustom ? 'true' : 'false' }},
        activeIndex: -1,

        init() {
            this.syncSelectedItem();
            if (this.selectedId === 'other' && this.allowCustom) {
                this.isCustom = true;
            }

            // Dengarkan pembaruan items dinamis dari komponen induk jika ada
            @if($parentListener)
            window.addEventListener('{{ $parentListener }}', (e) => {
                if (e.detail && Array.isArray(e.detail.items)) {
                    this.items = e.detail.items;
                    this.clear();
                }
            });
            @endif
        },

        syncSelectedItem() {
            if (!this.selectedId || this.selectedId === 'other') {
                this.selectedItem = null;
                return;
            }
            this.selectedItem = this.items.find(i => String(i.id) === String(this.selectedId)) || null;
        },

        norm(text) {
            return String(text || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '')
                .replace(/[^a-z0-9\s]+/g, ' ').replace(/\s+/g, ' ').trim();
        },

        haystack(item) {
            return this.norm((item.name || '') + ' ' + (item.acronym || '') + ' ' + (item.meta || '') + ' ' + (item.keywords || ''));
        },

        // Kemiripan Dice berbasis bigram (tahan salah ketik: 'airlanga' ≈ 'airlangga')
        similarity(a, b) {
            a = this.norm(a).replace(/\s/g, ''); b = this.norm(b).replace(/\s/g, '');
            if (!a || !b) return 0;
            if (a === b) return 1;
            if (a.length < 2 || b.length < 2) return 0;
            const grams = s => { const m = {}; for (let i = 0; i < s.length - 1; i++) { const g = s.substr(i, 2); m[g] = (m[g] || 0) + 1; } return m; };
            const x = grams(a), y = grams(b);
            let inter = 0, total = 0;
            for (const g in x) { inter += Math.min(x[g], y[g] || 0); total += x[g]; }
            for (const g in y) { total += y[g]; }
            return (2 * inter) / total;
        },

        similarItems(text, min = 0.45, limit = 5) {
            const q = this.norm(text);
            if (q.length < 3) return [];
            return this.items
                .map(item => {
                    const fields = [item.name, item.acronym, ...String(item.keywords || '').split('|')].filter(Boolean);
                    const score = Math.max(0, ...fields.map(f => this.similarity(q, f)), this.haystack(item).includes(q) ? 0.9 : 0);
                    return { item, score };
                })
                .filter(r => r.score >= min)
                .sort((a, b) => b.score - a.score)
                .slice(0, limit)
                .map(r => r.item);
        },

        get filteredItems() {
            const q = this.norm(this.search);
            if (!q) return this.items;

            const tokens = q.split(/\s+/);
            return this.items.filter(item => {
                const target = this.haystack(item);
                return tokens.every(token => target.includes(token));
            });
        },

        // Saran 'Mungkin maksud Anda' saat pencarian tidak menemukan hasil persis
        get fuzzySuggestions() {
            if (this.filteredItems.length > 0) return [];
            return this.similarItems(this.search, 0.4, 5);
        },

        // Data terdaftar yang mirip dengan input manual (cegah duplikat entri baru)
        get customSimilar() {
            if (!this.isCustom) return [];
            return this.similarItems(this.customValue, 0.5, 3);
        },

        openDropdown() {
            if ({{ $disabled ? 'true' : 'false' }}) return;
            this.open = true;
            this.search = '';
            this.activeIndex = -1;
            this.$nextTick(() => {
                if (this.$refs.searchInput) {
                    this.$refs.searchInput.focus();
                }
            });
        },

        select(item) {
            this.isCustom = false;
            this.selectedId = item ? String(item.id) : '';
            this.selectedItem = item;
            this.customValue = '';
            this.open = false;
            this.search = '';
            this.activeIndex = -1;
            this.dispatchChange();
        },

        selectCustom() {
            this.isCustom = true;
            this.selectedId = 'other';
            this.selectedItem = null;
            if (!this.customValue && this.search) {
                this.customValue = this.search; // bawa teks pencarian ke input manual
            }
            this.open = false;
            this.search = '';
            this.activeIndex = -1;
            this.$nextTick(() => {
                if (this.$refs.customInput) {
                    this.$refs.customInput.focus();
                }
            });
            this.dispatchChange();
        },

        clear() {
            this.isCustom = false;
            this.selectedId = '';
            this.selectedItem = null;
            this.customValue = '';
            this.search = '';
            this.activeIndex = -1;
            this.dispatchChange();
        },

        dispatchChange() {
            this.$nextTick(() => {
                const hiddenInput = this.$refs.hiddenInput;
                if (hiddenInput) {
                    hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
                    hiddenInput.dispatchEvent(new Event('input', { bubbles: true }));
                }

                // Dispatch custom event untuk reaktivitas relasi antar-dropdown
                const payload = {
                    name: this.name,
                    value: this.selectedId,
                    item: this.selectedItem,
                    isCustom: this.isCustom,
                    customValue: this.customValue,
                    raw: this.selectedItem ? this.selectedItem.raw : null
                };

                window.dispatchEvent(new CustomEvent('searchable-select-changed', { detail: payload }));
                @if($eventName)
                window.dispatchEvent(new CustomEvent('{{ $eventName }}', { detail: payload }));
                @endif
            });
        },

        handleKeyDown(e) {
            if (!this.open) {
                if (e.key === 'ArrowDown' || e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    this.openDropdown();
                }
                return;
            }

            const total = this.filteredItems.length + (this.allowCustom ? 1 : 0);
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                this.activeIndex = (this.activeIndex + 1) % total;
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                this.activeIndex = (this.activeIndex - 1 + total) % total;
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (this.activeIndex >= 0 && this.activeIndex < this.filteredItems.length) {
                    this.select(this.filteredItems[this.activeIndex]);
                } else if (this.allowCustom && this.activeIndex === this.filteredItems.length) {
                    this.selectCustom();
                }
            } else if (e.key === 'Escape') {
                this.open = false;
            }
        }
    }"
    class="relative w-full {{ $class }}"
    @click.outside="open = false"
    @keydown="handleKeyDown($event)"
>
    @if($label)
        <label for="{{ $elementId }}_btn" class="block font-bold text-xs uppercase tracking-wider text-slate-700 mb-1.5">
            {{ $label }}
            @if($required && $showAsterisk) <span class="text-rose-500">*</span> @endif
        </label>
    @endif

    <!-- Hidden Input for Form Submission -->
    <input 
        type="hidden" 
        id="{{ $elementId }}"
        name="{{ $name }}" 
        x-ref="hiddenInput"
        :value="selectedId" 
        @if($required) required @endif
    />

    <!-- Select Trigger Button -->
    <button
        type="button"
        id="{{ $elementId }}_btn"
        @click="open ? open = false : openDropdown()"
        :disabled="{{ $disabled ? 'true' : 'false' }}"
        class="w-full flex items-center justify-between text-left px-3.5 py-2.5 rounded-xl border transition duration-150 ease-in-out shadow-2xs text-xs sm:text-sm bg-white"
        :class="{
            'border-blue-500 ring-2 ring-blue-500/20': open,
            'border-slate-200 hover:border-slate-300': !open,
            'bg-slate-50 cursor-not-allowed opacity-75': {{ $disabled ? 'true' : 'false' }},
            'border-amber-300 bg-amber-50/30': isCustom
        }"
        aria-haspopup="listbox"
        :aria-expanded="open"
    >
        <!-- Displayed Content -->
        <div class="flex items-center gap-2 min-w-0 pr-2">
            <!-- Mode Custom / Manual Terpilih -->
            <template x-if="isCustom">
                <div class="flex items-center gap-2 truncate">
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 uppercase tracking-wider shrink-0">
                        Input Bebas
                    </span>
                    <span class="font-bold text-slate-800 truncate" x-text="customValue ? customValue : 'Ketik nama manual...'"></span>
                </div>
            </template>

            <!-- Data Terpilih dari Daftar -->
            <template x-if="!isCustom && selectedItem">
                <div class="flex items-center gap-2 truncate">
                    <template x-if="selectedItem.acronym">
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200/60 uppercase tracking-wider shrink-0" x-text="selectedItem.acronym"></span>
                    </template>
                    <span class="font-semibold text-slate-900 truncate" x-text="selectedItem.name"></span>
                </div>
            </template>

            <!-- Belum Memilih Data -->
            <template x-if="!isCustom && !selectedItem">
                <span class="text-slate-400 select-none truncate" x-text="placeholder"></span>
            </template>
        </div>

        <!-- Action Icons (Clear / Caret) -->
        <div class="flex items-center gap-1.5 shrink-0 ml-1 text-slate-400">
            <!-- Tombol Reset / Clear -->
            <template x-if="selectedId || isCustom">
                <span
                    role="button"
                    tabindex="0"
                    @click.stop="clear()"
                    class="p-1 hover:text-rose-600 hover:bg-rose-50 rounded-md transition cursor-pointer"
                    title="Hapus Pilihan"
                >
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </span>
            </template>

            <!-- Caret Down Icon -->
            <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180 text-blue-600': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </div>
    </button>

    <!-- Custom Text Input (Jika opsi manual dipilih) -->
    <div x-show="isCustom" x-cloak class="mt-2">
        <div class="relative">
            <input 
                type="text" 
                name="{{ $customName }}" 
                x-ref="customInput"
                x-model="customValue" 
                @input="dispatchChange()"
                placeholder="Ketik nama lengkap data yang ingin didaftarkan..." 
                class="w-full text-xs sm:text-sm px-3.5 py-2.5 bg-amber-50/50 border border-amber-300 rounded-xl focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 font-medium text-slate-800 placeholder-slate-400 transition"
                :required="isCustom"
            />
            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-amber-600">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
            </div>
        </div>
        <p class="text-[11px] text-amber-700 mt-1 flex items-center gap-1 font-medium">
            <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
            </svg>
            <span>Mode input manual aktif. Klik tombol silang (x) di atas jika ingin kembali memilih dari daftar.</span>
        </p>

        <!-- Peringatan data serupa sudah terdaftar -->
        <template x-if="customSimilar.length > 0">
            <div class="mt-2 p-3 rounded-xl bg-blue-50 border border-blue-200">
                <p class="text-[11px] font-bold text-blue-900">Mungkin sudah terdaftar — pilih salah satu jika sesuai:</p>
                <div class="mt-1.5 flex flex-wrap gap-1.5">
                    <template x-for="item in customSimilar" :key="'sim-' + item.id">
                        <button type="button" @click="select(item)"
                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-white border border-blue-200 text-[11px] font-semibold text-blue-800 hover:bg-blue-100 transition">
                            <span x-text="item.name"></span>
                            <template x-if="item.acronym"><span class="text-blue-500" x-text="'(' + item.acronym + ')'"></span></template>
                        </button>
                    </template>
                </div>
            </div>
        </template>
    </div>

    <!-- Dropdown Panel -->
    <div 
        x-show="open" 
        x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 translate-y-1 scale-98"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-1 scale-98"
        class="absolute left-0 right-0 z-50 mt-1.5 bg-white border border-slate-200 rounded-2xl shadow-xl p-2 min-w-[280px]"
    >
        <!-- Search Input Box -->
        <div class="relative mb-2">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <input
                type="text"
                x-ref="searchInput"
                x-model="search"
                placeholder="Ketik untuk mencari (nama, akronim, info)..."
                class="w-full text-xs sm:text-sm pl-8 pr-7 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 text-slate-800 placeholder-slate-400 transition"
            />
            <template x-if="search">
                <button
                    type="button"
                    @click="search = ''; $refs.searchInput.focus()"
                    class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600"
                >
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </template>
        </div>

        <!-- Scrollable Options List -->
        <div class="max-h-60 overflow-y-auto space-y-0.5 divide-y divide-slate-50 pr-0.5">
            <!-- Filtered Items -->
            <template x-for="(item, idx) in filteredItems" :key="item.id">
                <div
                    @click="select(item)"
                    @mouseenter="activeIndex = idx"
                    class="px-3 py-2 rounded-xl cursor-pointer transition text-left flex items-start justify-between gap-2"
                    :class="{
                        'bg-blue-50/80 text-blue-900': activeIndex === idx || String(selectedId) === String(item.id),
                        'hover:bg-slate-50 text-slate-700': activeIndex !== idx && String(selectedId) !== String(item.id)
                    }"
                >
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span class="font-semibold text-xs sm:text-sm" x-text="item.name"></span>
                            <template x-if="item.acronym">
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 uppercase tracking-wide border border-slate-200/60" x-text="item.acronym"></span>
                            </template>
                        </div>
                        <template x-if="item.meta">
                            <p class="text-[11px] text-slate-400 truncate mt-0.5" x-text="item.meta"></p>
                        </template>
                    </div>

                    <!-- Selected Checkmark -->
                    <template x-if="String(selectedId) === String(item.id)">
                        <svg class="w-4 h-4 text-blue-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                        </svg>
                    </template>
                </div>
            </template>

            <!-- Empty Search State -->
            <template x-if="filteredItems.length === 0">
                <div class="py-3 px-3">
                    <p class="text-xs text-slate-500 text-center">
                        Tidak ditemukan data yang cocok dengan "<span class="font-semibold text-slate-700" x-text="search"></span>".
                    </p>
                    <template x-if="fuzzySuggestions.length > 0">
                        <div class="mt-2">
                            <p class="text-[11px] font-bold text-slate-600 mb-1">Mungkin maksud Anda:</p>
                            <template x-for="item in fuzzySuggestions" :key="'fz-' + item.id">
                                <div @click="select(item)" class="px-3 py-2 rounded-xl cursor-pointer hover:bg-blue-50 text-slate-700 text-xs sm:text-sm flex items-center gap-1.5">
                                    <span class="font-semibold" x-text="item.name"></span>
                                    <template x-if="item.acronym">
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 uppercase border border-slate-200/60" x-text="item.acronym"></span>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </template>

            <!-- Opsi Input Bebas (Manual / Tidak Terdaftar) -->
            <template x-if="allowCustom">
                <div class="pt-1 mt-1 border-t border-slate-100">
                    <div
                        @click="selectCustom()"
                        @mouseenter="activeIndex = filteredItems.length"
                        class="px-3 py-2.5 rounded-xl cursor-pointer transition text-left flex items-center gap-2 group"
                        :class="{
                            'bg-amber-50 text-amber-900': isCustom || activeIndex === filteredItems.length,
                            'hover:bg-amber-50/50 text-amber-700': !isCustom && activeIndex !== filteredItems.length
                        }"
                    >
                        <div class="w-6 h-6 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-bold group-hover:underline">
                                {{ $customLabel }}
                            </p>
                            <p class="text-[10px] text-amber-600/80 truncate">
                                Klik di sini untuk mengetik nama data baru
                            </p>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>
