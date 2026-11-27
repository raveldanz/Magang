<x-app-layout>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900">Pengaturan Sistem</h1></x-slot>
    <main class="mx-auto max-w-5xl px-4 py-8 sm:px-6">
        @if(session('success'))<p role="status" class="mb-4 rounded-xl bg-emerald-50 p-4 text-emerald-900">{{ session('success') }}</p>@endif
        <div class="grid items-start gap-6 lg:grid-cols-2">
            <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                @csrf @method('PUT')
                <h2 class="text-lg font-bold">Pendaftaran dan surat</h2>
                <div>
                    <label for="group_limit" class="block font-semibold">Maksimum anggota per kelompok</label>
                    <p id="group-help" class="mb-2 text-sm text-slate-600">Termasuk ketua. Jumlah peserta juga mengikuti sisa kuota unit.</p>
                    <input id="group_limit" aria-describedby="group-help" name="group_limit" type="number" min="1" required value="{{ old('group_limit', $settings->group_limit) }}" class="w-full rounded-lg border-slate-300">
                    @error('group_limit')<p role="alert" class="text-red-700">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="letter_contact" class="block font-semibold">Narahubung surat balasan</label>
                    <p id="contact-help" class="mb-2 text-sm text-slate-600">Tercantum pada surat penerimaan saat dicetak oleh admin atau peserta.</p>
                    <input id="letter_contact" aria-describedby="contact-help" name="letter_contact" maxlength="120" required value="{{ old('letter_contact', $settings->letter_contact) }}" class="w-full rounded-lg border-slate-300">
                    @error('letter_contact')<p role="alert" class="text-red-700">{{ $message }}</p>@enderror
                </div>
                <button class="rounded-lg bg-blue-700 px-5 py-3 font-semibold text-white hover:bg-blue-800">Simpan perubahan</button>
            </form>
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="mb-3 text-lg font-bold">Pengaturan fitur</h2>
                <div class="divide-y divide-slate-200">
                    @foreach ([
                        ['admin.certificates.index','Sertifikat','Tema tahunan dan penandatangan'],
                        ['admin.agency_profile.edit','Profil instansi','Kop surat dan identitas pejabat'],
                        ['admin.universities.index','Sekolah dan kampus','Sinkronisasi direktori serta kebijakan per institusi'],
                        ['admin.agencies.index','Instansi dan unit','Kapasitas penempatan peserta'],
                        ['admin.audit_logs.index','Riwayat perubahan','Aktivitas administrator'],
                    ] as [$route,$title,$summary])
                        <a href="{{ route($route) }}" class="flex items-center justify-between gap-3 py-3 text-blue-800 hover:underline">
                            <span><strong>{{ $title }}</strong><span class="block text-sm text-slate-600">{{ $summary }}</span></span>
                            <span aria-hidden="true">→</span>
                        </a>
                    @endforeach
                </div>
            </section>
        </div>
    </main>
</x-app-layout>
