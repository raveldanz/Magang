<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="font-black text-xl sm:text-2xl text-gray-900 tracking-tight">Usul Perubahan Data Direktori</h2>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">
                    Data lokal baru berubah setelah usul diterima. Usul yang ditolak tidak diajukan lagi.
                </p>
            </div>
            <a href="{{ route('admin.universities.index') }}"
                class="inline-flex items-center justify-center px-4 py-2 min-h-[44px] sm:min-h-0 rounded-xl border border-slate-300 bg-white text-xs font-bold text-slate-700 hover:bg-slate-50">
                Kembali ke daftar
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            @if (session('success'))
                <div role="status" class="p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-xl text-emerald-900 text-sm font-medium">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div role="alert" class="p-4 bg-rose-50 border-l-4 border-rose-500 rounded-r-xl text-rose-900 text-sm font-medium">{{ $errors->first() }}</div>
            @endif

            <p class="rounded-2xl bg-amber-50 border border-amber-200 px-3.5 py-2.5 text-xs text-amber-900">
                Periksa usul nama satu per satu. Nama yang jauh berbeda bisa berarti NPSN pada data lama keliru, sehingga usul itu sebaiknya ditolak.
            </p>

            <nav class="flex flex-wrap gap-2" aria-label="Jenis usul">
                @foreach (['' => 'Semua'] + $kinds as $key => $label)
                    <a href="{{ route('admin.universities.sync.proposals', $key === '' ? [] : ['kind' => $key]) }}"
                        @class([
                            'inline-flex items-center px-3 py-2 min-h-[44px] sm:min-h-0 rounded-xl text-xs font-bold border',
                            'bg-blue-600 border-blue-600 text-white' => (string) $kind === $key,
                            'bg-white border-slate-200 text-slate-700 hover:bg-slate-50' => (string) $kind !== $key,
                        ])>{{ $label }}</a>
                @endforeach
            </nav>

            @if ($proposals->isEmpty())
                <p class="bg-white rounded-3xl border border-slate-200 p-8 text-center text-sm text-slate-500">Tidak ada usul yang menunggu keputusan.</p>
            @else
                <form method="POST" action="{{ route('admin.universities.sync.decide') }}" class="space-y-3"
                    x-data="{
                        selected: 0,
                        count() { this.selected = this.$root.querySelectorAll('input[name=\'ids[]\']:checked').length; },
                        toggleAll(on) { this.$root.querySelectorAll('input[name=\'ids[]\']:not([data-manual])').forEach((box) => box.checked = on); this.count(); },
                    }"
                    x-on:change="count()">
                    @csrf
                    <div class="sticky top-2 z-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 bg-white rounded-2xl border border-slate-200 shadow-sm p-3">
                        <label class="inline-flex items-center gap-2 min-h-[44px] sm:min-h-0 text-xs font-bold text-slate-700">
                            <input type="checkbox" class="rounded border-slate-300 text-blue-600" x-on:change="toggleAll($event.target.checked)">
                            <span>Centang semua di halaman ini, kecuali perubahan nama (<span x-text="selected">0</span> dipilih)</span>
                        </label>
                        <div class="grid grid-cols-2 sm:flex gap-2">
                            <button type="submit" name="decision" value="accept" :disabled="selected === 0"
                                class="px-4 py-2 min-h-[44px] sm:min-h-0 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold disabled:opacity-50">Terima</button>
                            <button type="submit" name="decision" value="reject" :disabled="selected === 0"
                                class="px-4 py-2 min-h-[44px] sm:min-h-0 rounded-xl border border-rose-300 bg-white text-rose-700 hover:bg-rose-50 text-xs font-bold disabled:opacity-50">Tolak</button>
                        </div>
                    </div>

                    <ul class="space-y-2">
                        @foreach ($proposals as $proposal)
                            <li class="bg-white rounded-2xl border border-slate-200 p-3.5">
                                <label class="flex items-start gap-3 cursor-pointer">
                                    <input type="checkbox" name="ids[]" value="{{ $proposal->id }}" @if ($proposal->kind === \App\Models\InstitutionChangeProposal::KIND_NAME) data-manual="1" @endif class="mt-1 rounded border-slate-300 text-blue-600">
                                    <span class="min-w-0 flex-1 space-y-2 break-words">
                                        <span class="flex flex-wrap items-center gap-2">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-blue-50 text-blue-700 border border-blue-200">{{ $kinds[$proposal->kind] ?? $proposal->kind }}</span>
                                            @if ($proposal->university)
                                                <span class="text-xs font-bold text-slate-900">{{ $proposal->university->name }}</span>
                                                @if ($proposal->university->code)
                                                    <span class="text-[11px] font-mono text-slate-500">{{ $proposal->university->code }}</span>
                                                @endif
                                            @endif
                                        </span>
                                        <span class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                                            <span class="rounded-xl bg-slate-50 border border-slate-200 p-2.5">
                                                <span class="block text-[10px] font-bold uppercase text-slate-500">Sekarang</span>
                                                <span class="text-slate-700">{{ ($proposal->university ? ($proposal->kind === \App\Models\InstitutionChangeProposal::KIND_NAME ? $proposal->university->name : $proposal->university->address) : null) ?: 'Belum ada di database' }}</span>
                                            </span>
                                            <span class="rounded-xl bg-blue-50 border border-blue-200 p-2.5">
                                                <span class="block text-[10px] font-bold uppercase text-blue-700">Dari API</span>
                                                <span class="text-slate-900 font-semibold">{{ $proposal->proposed_value }}</span>
                                            </span>
                                        </span>
                                    </span>
                                </label>
                            </li>
                        @endforeach
                    </ul>
                </form>

                {{ $proposals->links() }}
            @endif
        </div>
    </div>
</x-app-layout>
