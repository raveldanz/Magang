<x-app-layout>
    @php
        $role = $viewer?->role;
        $isStudent = $role === 'mahasiswa';
        $backUrl = $isStudent
            ? route('student.final_report.index')
            : (in_array($role, ['admin', 'super_admin'], true) ? route('admin.applications.show', $application->id) : url()->previous());
    @endphp

    <div class="py-10">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl border border-amber-200 shadow-sm p-6 sm:p-8 space-y-5">
                <div class="flex items-start gap-3">
                    <div class="p-2.5 bg-amber-100 text-amber-700 rounded-xl shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </div>
                    <div>
                        <h1 class="text-lg font-bold text-slate-900">E-Sertifikat Belum Dapat Diterbitkan</h1>
                        <p class="text-sm text-slate-600 mt-1">
                            Sertifikat belum dapat diunduh karena nilai evaluasi dinas atau laporan akhir belum disetujui.
                            Sertifikat resmi hanya terbit setelah seluruh syarat kelulusan terpenuhi.
                        </p>
                    </div>
                </div>

                <div class="rounded-xl bg-amber-50 border border-amber-200 p-4">
                    <p class="text-xs font-bold text-amber-900 uppercase tracking-wider mb-2">Syarat yang belum terpenuhi</p>
                    <ul class="list-disc list-inside space-y-1 text-sm text-amber-900">
                        @foreach ($blockers as $blocker)
                            <li>{{ $blocker }}</li>
                        @endforeach
                    </ul>
                </div>

                <div class="text-xs text-slate-500 leading-relaxed">
                    @if ($isStudent)
                        Pastikan laporan akhir Anda sudah diunggah dan disetujui, serta Mentor Lapangan telah mengisi lembar penilaian. Status kelulusan diperbarui otomatis setelah semua syarat lengkap.
                    @else
                        Lengkapi penilaian mentor dan persetujuan laporan akhir, lalu tetapkan status kelulusan mahasiswa sebelum mencetak sertifikat.
                    @endif
                </div>

                <a href="{{ $backUrl }}" class="inline-flex items-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl transition">
                    Kembali
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
