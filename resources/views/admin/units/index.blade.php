<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight flex items-center gap-2">
                    <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    {{ __('Manajemen Divisi & Kuota Magang') }}
                </h2>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">
                    {{ Auth::user()->agencyProfile->agency_name ?? 'Super Administrator (Semua Instansi Pemkot Surabaya)' }}
                </p>
            </div>

            <a href="{{ route('admin.units.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-md transition cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Divisi / Lowongan Baru
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Message -->
            @if (session('success'))
                <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-lg shadow-sm flex items-center justify-between text-emerald-900 text-sm font-medium">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="p-4 bg-rose-50 border-l-4 border-rose-500 rounded-r-lg shadow-sm flex items-center justify-between text-rose-900 text-sm font-medium">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-rose-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                        <span>{{ session('error') }}</span>
                    </div>
                </div>
            @endif

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Divisi / Bidang</p>
                    <h3 class="text-2xl font-black text-gray-900 mt-1">{{ $stats['total_units'] }}</h3>
                    <p class="text-xs text-gray-500 mt-1">Unit kerja aktif</p>
                </div>

                <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Kuota Magang</p>
                    <h3 class="text-2xl font-black text-blue-600 mt-1" id="stat-total-quota">{{ $stats['total_quota'] }}</h3>
                    <p class="text-xs text-gray-500 mt-1">Kapasitas maksimal</p>
                </div>

                <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Kuota Terisi (Diterima)</p>
                    <h3 class="text-2xl font-black text-emerald-600 mt-1">{{ $stats['total_filled'] }}</h3>
                    <p class="text-xs text-gray-500 mt-1">Mahasiswa aktif magang</p>
                </div>

                <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Sisa Kuota Tersedia</p>
                    <h3 class="text-2xl font-black text-amber-600 mt-1" id="stat-total-remaining">{{ $stats['total_remaining'] }}</h3>
                    <p class="text-xs text-gray-500 mt-1">Slot mahasiswa baru</p>
                </div>
            </div>

            <!-- Filter & Search Bar -->
            <div class="bg-white rounded-2xl p-4 border border-gray-200 shadow-sm flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="w-full md:w-auto flex flex-wrap items-center gap-3">
                    @if (Auth::user()->agency_profile_id === null && count($agencies) > 1)
                        <form method="GET" action="{{ route('admin.units.index') }}" class="flex items-center gap-2">
                            <select name="agency_id" onchange="this.form.submit()" class="text-xs border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-xs">
                                <option value="">-- Semua Instansi --</option>
                                @foreach ($agencies as $agency)
                                    <option value="{{ $agency->id }}" {{ request('agency_id') == $agency->id ? 'selected' : '' }}>
                                         {{ $agency->agency_name }}
                                    </option>
                                @endforeach
                            </select>
                        </form>
                    @endif
                </div>

                <form method="GET" action="{{ route('admin.units.index') }}" class="w-full md:w-72 flex items-center gap-2">
                    @if (request('agency_id'))
                        <input type="hidden" name="agency_id" value="{{ request('agency_id') }}">
                    @endif
                    <div class="relative w-full">
                        <div class="absolute inset-y-0 left-0 flex items-center pointer-events-none text-slate-400" style="padding-left: 0.85rem !important;">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama divisi..." 
                               class="w-full text-xs border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 shadow-2xs"
                               style="padding-left: 2.5rem !important; padding-right: 0.75rem !important; padding-top: 0.55rem !important; padding-bottom: 0.55rem !important;">
                    </div>
                    <button type="submit" class="px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs transition shrink-0 cursor-pointer">
                        Cari
                    </button>
                </form>
            </div>

            <!-- Table Divisi / Unit Kerja -->
            <div class="bg-white overflow-hidden shadow-sm rounded-2xl border border-gray-200">
                <div class="p-6 border-b border-gray-100">
                    <h3 class="text-lg font-bold text-gray-900">Daftar Divisi & Penyesuaian Kuota</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Ketik angka kuota langsung atau klik tombol +/- untuk penyesuaian instan</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/75 border-b border-gray-200 text-[11px] font-bold text-gray-500 uppercase tracking-wider">
                                <th class="py-3.5 px-4">Nama Divisi / Bidang</th>
                                <th class="py-3.5 px-4">Instansi Induk</th>
                                <th class="py-3.5 px-4 text-center">Status Kuota</th>
                                <th class="py-3.5 px-4 text-center">Sisa Kuota</th>
                                <th class="py-3.5 px-4 text-center">Aksi Cepat Kuota</th>
                                <th class="py-3.5 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            @forelse ($units as $unit)
                                @php
                                    $acceptedCount = $unit->applications->where('status', 'accepted')->count();
                                    $remaining = max(0, $unit->quota - $acceptedCount);
                                    $percent = $unit->quota > 0 ? min(100, round(($acceptedCount / $unit->quota) * 100)) : 100;
                                @endphp
                                <tr class="hover:bg-slate-50/75 transition-colors" id="unit-row-{{ $unit->id }}">
                                    
                                    <!-- Nama Divisi & Deskripsi -->
                                    <td class="py-4 px-4 max-w-xs">
                                        <div class="font-bold text-gray-900 leading-snug">{{ $unit->name }}</div>
                                        <div class="text-xs text-gray-500 mt-0.5 line-clamp-2">{{ $unit->description ?? 'Tidak ada deskripsi' }}</div>
                                    </td>

                                    <!-- Instansi Induk -->
                                    <td class="py-4 px-4">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-blue-50 text-blue-700 text-xs font-semibold rounded-lg">
                                             {{ $unit->agencyProfile->agency_name ?? '-' }}
                                        </span>
                                    </td>

                                    <!-- Progress Kuota -->
                                    <td class="py-4 px-4 text-center min-w-[140px]">
                                        <div class="space-y-1">
                                            <div class="flex justify-between text-xs font-bold text-gray-700">
                                                <span>{{ $acceptedCount }} Terisi</span>
                                                <span id="unit-total-quota-{{ $unit->id }}">{{ $unit->quota }} Total</span>
                                            </div>
                                            <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                                                <div id="unit-progress-bar-{{ $unit->id }}" class="h-2 rounded-full {{ $percent >= 100 ? 'bg-rose-500' : ($percent >= 75 ? 'bg-amber-500' : 'bg-emerald-500') }}" style="width: {{ $percent }}%"></div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Sisa Kuota & Status -->
                                    <td class="py-4 px-4 text-center" id="unit-remaining-badge-{{ $unit->id }}">
                                        @if ($remaining > 0)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-100 text-emerald-800 text-xs font-black rounded-full">
                                                {{ $remaining }} Slot Tersedia
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-rose-100 text-rose-800 text-xs font-black rounded-full">
                                                PENUH
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Aksi Cepat Kuota (Inline Editable Number Input + Sync Buttons) -->
                                    <td class="py-4 px-4 text-center">
                                        <div class="inline-flex items-center gap-1 bg-slate-50 p-1 rounded-xl border border-slate-200 shadow-xs relative">
                                            <!-- Kurang (-1) -->
                                            <button type="button" 
                                                    onclick="adjustQuota({{ $unit->id }}, -1)"
                                                    title="Kurangi Kuota (-1)" 
                                                    class="btn-decrement w-7 h-7 flex items-center justify-center bg-white hover:bg-slate-100 text-slate-700 font-bold rounded-lg border border-slate-200 transition text-sm shadow-xs active:scale-95">
                                                -
                                            </button>

                                            <!-- Input Number Editable Langsung -->
                                            <input type="number" 
                                                   id="quota-input-{{ $unit->id }}"
                                                   name="quota" 
                                                   value="{{ $unit->quota }}" 
                                                   min="{{ $acceptedCount }}" 
                                                   max="500"
                                                   class="quota-input w-16 text-center border-slate-300 rounded-md text-sm font-bold text-slate-800 focus:ring-blue-500 focus:border-blue-500 py-1 px-1 bg-white shadow-xs"
                                                   data-unit-id="{{ $unit->id }}"
                                                   data-current-val="{{ $unit->quota }}"
                                                   data-filled="{{ $acceptedCount }}"
                                                   onchange="updateQuotaValue({{ $unit->id }}, this.value)"
                                                   onkeydown="if(event.key === 'Enter'){ this.blur(); }">

                                            <!-- Tambah (+1) -->
                                            <button type="button" 
                                                    onclick="adjustQuota({{ $unit->id }}, 1)"
                                                    title="Tambah Kuota (+1)" 
                                                    class="btn-increment w-7 h-7 flex items-center justify-center bg-white hover:bg-slate-100 text-slate-700 font-bold rounded-lg border border-slate-200 transition text-sm shadow-xs active:scale-95">
                                                +
                                            </button>

                                            <!-- Save Indicator Icon -->
                                            <span id="save-indicator-{{ $unit->id }}" class="hidden absolute -top-2 -right-2 bg-emerald-500 text-white rounded-full p-0.5 shadow-sm">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                                </svg>
                                            </span>
                                        </div>
                                    </td>

                                    <!-- Aksi Edit & Hapus -->
                                    <td class="py-4 px-4 text-right whitespace-nowrap">
                                        <div class="btn-action-group">
                                            <a href="{{ route('admin.units.edit', $unit->id) }}" class="btn-action-edit" title="Edit Divisi">
                                                Edit
                                            </a>

                                            <form action="{{ route('admin.units.destroy', $unit->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus divisi {{ $unit->name }}?');" class="btn-action-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-action-delete" title="Hapus Divisi">
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </td>

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-12 text-center text-gray-400">
                                        <p class="font-medium text-gray-600">Belum Ada Divisi / Lowongan Magang</p>
                                        <p class="text-xs text-gray-400 mt-1">Silakan klik tombol "Tambah Divisi Baru" untuk membuka lowongan.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <!-- Quick Quota AJAX Handler Script -->
    <script>
        async function updateQuotaValue(unitId, newValue) {
            const input = document.getElementById(`quota-input-${unitId}`);
            const indicator = document.getElementById(`save-indicator-${unitId}`);
            const filled = parseInt(input.dataset.filled || 0);
            let val = parseInt(newValue);

            if (isNaN(val) || val < 0) {
                val = 0;
                input.value = 0;
            }

            if (val < filled) {
                alert(`Kuota tidak boleh kurang dari jumlah mahasiswa yang sudah diterima (${filled} orang).`);
                input.value = input.dataset.currentVal;
                return;
            }

            try {
                input.classList.add('opacity-50');
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                const response = await fetch(`/admin/units/${unitId}/quota`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        custom_quota: val,
                        quota: val
                    })
                });

                const data = await response.json();
                input.classList.remove('opacity-50');

                if (response.ok && data.success) {
                    input.dataset.currentVal = val;
                    input.value = val;
                    
                    // Show visual feedback checkmark
                    if (indicator) {
                        indicator.classList.remove('hidden');
                        setTimeout(() => {
                            indicator.classList.add('hidden');
                        }, 2000);
                    }

                    // Update UI Progress & Remaining
                    const totalLabel = document.getElementById(`unit-total-quota-${unitId}`);
                    if (totalLabel) totalLabel.innerText = `${val} Total`;

                    const remainingBadge = document.getElementById(`unit-remaining-badge-${unitId}`);
                    const remaining = Math.max(0, val - filled);
                    if (remainingBadge) {
                        if (remaining > 0) {
                            remainingBadge.innerHTML = `<span class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-100 text-emerald-800 text-xs font-black rounded-full">${remaining} Slot Tersedia</span>`;
                        } else {
                            remainingBadge.innerHTML = `<span class="inline-flex items-center gap-1 px-2.5 py-1 bg-rose-100 text-rose-800 text-xs font-black rounded-full">PENUH</span>`;
                        }
                    }

                    const progressBar = document.getElementById(`unit-progress-bar-${unitId}`);
                    if (progressBar) {
                        const percent = val > 0 ? Math.min(100, Math.round((filled / val) * 100)) : 100;
                        progressBar.style.width = `${percent}%`;
                        progressBar.className = `h-2 rounded-full ${percent >= 100 ? 'bg-rose-500' : (percent >= 75 ? 'bg-amber-500' : 'bg-emerald-500')}`;
                    }

                    // Show toast notification
                    showToast(data.message || 'Kuota berhasil diperbarui!');
                } else {
                    alert(data.message || 'Gagal memperbarui kuota.');
                    input.value = input.dataset.currentVal;
                }
            } catch (error) {
                input.classList.remove('opacity-50');
                console.error(error);
                alert('Terjadi kesalahan saat memperbarui kuota.');
                input.value = input.dataset.currentVal;
            }
        }

        function adjustQuota(unitId, change) {
            const input = document.getElementById(`quota-input-${unitId}`);
            let current = parseInt(input.value || 0);
            let nextVal = current + change;
            if (nextVal < 0) nextVal = 0;
            input.value = nextVal;
            updateQuotaValue(unitId, nextVal);
        }

        function showToast(message) {
            let toast = document.getElementById('quick-quota-toast');
            if (!toast) {
                toast = document.createElement('div');
                toast.id = 'quick-quota-toast';
                toast.className = 'fixed bottom-5 right-5 bg-slate-900 text-white px-4 py-2.5 rounded-xl shadow-xl text-xs font-semibold flex items-center gap-2 z-50 transition-all duration-300 transform translate-y-10 opacity-0';
                document.body.appendChild(toast);
            }
            toast.innerHTML = `<span></span> <span>${message}</span>`;
            toast.classList.remove('translate-y-10', 'opacity-0');
            setTimeout(() => {
                toast.classList.add('translate-y-10', 'opacity-0');
            }, 2500);
        }
    </script>
</x-app-layout>
