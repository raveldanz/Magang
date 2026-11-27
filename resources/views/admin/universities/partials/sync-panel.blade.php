{{-- Panel sinkron direktori sekolah/kampus (khusus Super Admin). Status ditanyakan hanya selama proses aktif. --}}
@foreach (['school' => 'Sekolah', 'college' => 'Kampus'] as $kind => $label)
<section class="bg-white rounded-3xl border border-slate-200/80 shadow-2xs p-4 sm:p-5 space-y-3"
    x-data="{
        data: null,
        failed: false,
        timer: null,
        async load() {
            clearTimeout(this.timer);
            try {
                const res = await fetch(@js(route('admin.universities.sync.status', ['kind' => $kind])), { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' });
                if (!res.ok) throw new Error('HTTP ' + res.status);
                this.data = await res.json();
                this.failed = false;
            } catch (e) {
                this.failed = true;
            }
            if (this.data?.active && !this.data.stalled) this.timer = setTimeout(() => this.load(), 3000);
        },
        get busy() { return !!this.data?.active && !this.data.stalled; },
        get progress() {
            const run = this.data?.run;
            if (!run || !run.step) return 'Menunggu giliran antrean...';
            if (run.step === 'KAMPUS') return 'Memeriksa daftar kampus...';
            return 'Memuat ' + run.step + ': halaman ' + run.page + (run.last_page ? ' dari ' + run.last_page : '');
        },
    }"
    x-init="load()">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="min-w-0">
            <h3 class="font-bold text-slate-900 text-sm">Direktori {{ $label }}</h3>
            <p class="text-xs text-slate-500 mt-0.5">
                Pencarian peserta hanya membaca database. Sinkron berjalan otomatis tiap pekan, atau muat sekarang lewat tombol ini.
            </p>
            <p class="text-[11px] text-slate-500 mt-1">
                Sinkron penuh terakhir:
                <span class="font-semibold text-slate-700" x-text="data?.last_full_sync || 'belum pernah'"></span>
            </p>
        </div>
        <form method="POST" action="{{ route('admin.universities.sync.start') }}" class="shrink-0" x-on:submit="$refs.submit.disabled = true">
            @csrf
            <input type="hidden" name="kind" value="{{ $kind }}">
            <button type="submit" x-ref="submit" :disabled="busy"
                class="w-full sm:w-auto inline-flex items-center justify-center min-h-[44px] sm:min-h-0 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-xs transition disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer">
                <span x-text="data?.stalled ? 'Mulai ulang' : 'Perbarui {{ $label }}'">Perbarui {{ $label }}</span>
            </button>
        </form>
    </div>

    <div x-show="busy" x-cloak role="status" class="rounded-2xl bg-blue-50 border border-blue-200 px-3.5 py-2.5 text-xs text-blue-900">
        <p class="font-semibold" x-text="progress"></p>
        <p class="mt-0.5" x-text="(data?.run?.created ?? 0) + ' data baru, ' + (data?.run?.updated ?? 0) + ' dilengkapi'"></p>
    </div>

    <p x-show="data?.queue_waiting" x-cloak role="alert" class="rounded-2xl bg-amber-50 border border-amber-200 px-3.5 py-2.5 text-xs text-amber-900">
        Antrean belum berjalan. Jalankan "php artisan queue:work".
    </p>
    <p x-show="data?.stalled" x-cloak role="alert" class="rounded-2xl bg-amber-50 border border-amber-200 px-3.5 py-2.5 text-xs text-amber-900">
        Proses sebelumnya terhenti. Tekan "Mulai ulang".
    </p>

    <div x-show="data?.run && !data.active" x-cloak class="rounded-2xl border px-3.5 py-2.5 text-xs"
        :class="data?.run?.status === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : 'bg-rose-50 border-rose-200 text-rose-900'">
        <p class="font-semibold"
            x-text="(data?.run?.status === 'success' ? 'Selesai' : 'Gagal') + (data?.run?.finished_at ? ' pada ' + data.run.finished_at : '')"></p>
        <p class="mt-0.5" x-text="(data?.run?.created ?? 0) + ' data baru, ' + (data?.run?.updated ?? 0) + ' dilengkapi, ' + (data?.run?.proposed ?? 0) + ' usul baru, ' + (data?.run?.skipped ?? 0) + ' dilewati'"></p>
        <p x-show="data?.run?.message" class="mt-0.5" x-text="data?.run?.message"></p>
    </div>

    <p x-show="failed" x-cloak role="alert" class="text-xs text-rose-600">Status sinkron belum bisa dimuat. Muat ulang halaman.</p>

    <a x-show="data?.pending_proposals > 0" x-cloak href="{{ route('admin.universities.sync.proposals') }}"
        class="inline-flex items-center min-h-[44px] sm:min-h-0 text-xs font-bold text-blue-700 hover:underline">
        <span x-text="(data?.pending_proposals ?? 0) + ' usul menunggu keputusan'"></span>
    </a>
</section>
@endforeach
