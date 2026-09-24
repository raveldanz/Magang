<x-guest-layout>
    @php
        $agency = $placement->application?->unit?->agencyProfile 
            ?? $placement->agencyProfile 
            ?? \App\Models\AgencyProfile::first();
        $govName = $agency->government_name ?? 'Pemerintah Kota Surabaya';
        $agencyName = $agency->agency_name ?? 'Dinas Komunikasi Dan Informatika';
        $student = $placement->application?->user;
        $profile = $student?->studentProfile;
        $app = $placement->application;
        $appStatus = $app?->status;
        $rawStatus = $appStatus instanceof \App\Enums\ApplicationStatus ? $appStatus->value : strtolower((string)$appStatus);
        $isCompleted = ($rawStatus === 'completed');
        $isResigned = in_array($rawStatus, ['resigned', 'canceled', 'rejected']);

        $eval = $placement->evaluation;
        $rataRata = $eval ? ($eval->final_score ?? $eval->nilai_akhir ?? 0) : 0;
        $grade = $eval?->grade ?? $eval?->grade_calculated ?? '-';
        if ($grade === '-' && $rataRata > 0) {
            if ($rataRata >= 85) $grade = 'A (Sangat Memuaskan)';
            elseif ($rataRata >= 70) $grade = 'B (Memuaskan)';
            else $grade = 'C (Cukup)';
        }
    @endphp

    <div class="py-10 max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl rounded-2xl p-6 border-t-8 {{ $isCompleted ? 'border-emerald-600' : ($isResigned ? 'border-rose-600' : 'border-amber-500') }}">
            
            <!-- Header Logo / Instansi -->
            <div class="text-center pb-4 border-b">
                @if(!empty($agency->logo))
                    <img src="{{ asset('storage/' . $agency->logo) }}" alt="Logo {{ $agencyName }}" class="w-16 h-16 mx-auto mb-2 object-contain">
                @endif
                <h2 class="text-lg font-bold text-gray-800 uppercase tracking-wide">{{ $govName }}</h2>
                <h1 class="text-xl font-extrabold text-blue-900 uppercase">{{ $agencyName }}</h1>
                <p class="text-xs text-gray-500 mt-1">Sistem Informasi Pendaftaran & Sertifikasi Magang Resmi</p>
            </div>

            <!-- Badge Status Verifikasi Sah / Peringatan -->
            @if($isCompleted)
                <div class="mt-6 flex items-center space-x-4 bg-emerald-50 p-4 rounded-xl border border-emerald-200 shadow-xs">
                    <div class="flex-shrink-0 bg-emerald-600 text-white p-3 rounded-full shadow-md">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <span class="inline-block px-2.5 py-0.5 text-xs font-black bg-emerald-200 text-emerald-900 rounded-full mb-1">
                            STATUS: E-SERTIFIKAT SAH & TERVERIFIKASI
                        </span>
                        <h3 class="font-extrabold text-emerald-950 text-base leading-tight">SERTIFIKAT KELULUSAN MAGANG RESMI</h3>
                        <p class="text-xs text-emerald-700 mt-0.5">Sertifikat ini diterbitkan secara resmi dan tercatat sah dalam pangkalan data Pemerintah Kota Surabaya.</p>
                    </div>
                </div>
            @elseif($isResigned)
                <div class="mt-6 flex items-center space-x-4 bg-rose-50 p-4 rounded-xl border border-rose-200 shadow-xs">
                    <div class="flex-shrink-0 bg-rose-600 text-white p-3 rounded-full shadow-md">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </div>
                    <div>
                        <span class="inline-block px-2.5 py-0.5 text-xs font-black bg-rose-200 text-rose-900 rounded-full mb-1">
                            STATUS: DOKUMEN TIDAK BERLAKU
                        </span>
                        <h3 class="font-extrabold text-rose-950 text-base leading-tight">PESERTA MENGUNDURKAN DIRI / DROP OUT</h3>
                        <p class="text-xs text-rose-700 mt-0.5">Mahasiswa telah mengundurkan diri atau dibatalkan dari penempatan magang ini, sehingga tidak ada sertifikat kelulusan yang sah.</p>
                    </div>
                </div>
            @else
                <div class="mt-6 flex items-center space-x-4 bg-amber-50 p-4 rounded-xl border border-amber-200 shadow-xs">
                    <div class="flex-shrink-0 bg-amber-600 text-white p-3 rounded-full shadow-md">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <div>
                        <span class="inline-block px-2.5 py-0.5 text-xs font-black bg-amber-200 text-amber-900 rounded-full mb-1">
                            STATUS: MAGANG SEDANG BERJALAN / BELUM LULUS
                        </span>
                        <h3 class="font-extrabold text-amber-950 text-base leading-tight">SERTIFIKAT BELUM DITERBITKAN</h3>
                        <p class="text-xs text-amber-800 mt-0.5">Peserta saat ini masih aktif menempuh program magang dan belum menyelesaikan seluruh evaluasi serta laporan akhir.</p>
                    </div>
                </div>
            @endif

            <!-- Detail Informasi Sertifikat -->
            <div class="mt-6">
                <h4 class="font-bold text-gray-800 text-sm uppercase tracking-wider border-b pb-2 mb-3">Detail Informasi Mahasiswa & Penilaian</h4>
                
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-500 font-medium">Nomor Registrasi Sertifikat:</span>
                        <span class="font-bold text-blue-700 font-mono">{{ $placement->certificate_number ?? ('SRT/' . date('Y') . '/' . str_pad($placement->id, 4, '0', STR_PAD_LEFT)) }}</span>
                    </div>
                    @if($placement->certificate_hash)
                        <div class="flex justify-between py-2 border-b border-gray-100">
                            <span class="text-gray-500 font-medium">Kode Keamanan Digital:</span>
                            <span class="font-mono text-xs text-slate-700 bg-slate-100 px-2 py-0.5 rounded font-semibold">{{ substr($placement->certificate_hash, 0, 16) }}...</span>
                        </div>
                    @endif
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-500 font-medium">Nama Mahasiswa:</span>
                        <span class="font-bold text-gray-900 uppercase">{{ $student->name }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-500 font-medium">NIM / NPM:</span>
                        <span class="font-semibold text-gray-800">{{ $profile->nim ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-500 font-medium">Perguruan Tinggi:</span>
                        <span class="font-semibold text-gray-800 text-right">{{ $profile->universitas ?? '-' }} <br><span class="text-xs text-gray-500">({{ $profile->jurusan ?? '-' }})</span></span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-500 font-medium">Unit Penempatan:</span>
                        <span class="font-bold text-blue-700">{{ $placement->application->unit->name ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-500 font-medium">Periode Magang:</span>
                        <span class="font-semibold text-gray-800">
                            {{ \Carbon\Carbon::parse($placement->application->start_date)->translatedFormat('d F Y') }} s/d {{ \Carbon\Carbon::parse($placement->application->end_date)->translatedFormat('d F Y') }}
                        </span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-500 font-medium">Nilai Akhir Rata-Rata:</span>
                        <span class="font-bold text-emerald-600 text-base">{{ $rataRata }} (Predikat: {{ $grade }})</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-500 font-medium">Pembimbing Lapangan:</span>
                        <span class="font-semibold text-gray-800">{{ $placement->mentor->name ?? $placement->pembimbing->name ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between py-2">
                        <span class="text-gray-500 font-medium">Pejabat Pengesah:</span>
                        <span class="font-semibold text-gray-800 text-right">
                            {{ $agency->signee_name }}<br>
                            <span class="text-xs text-gray-500">{{ $agency->signee_position }}</span>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Footer Keamanan -->
            <div class="mt-8 pt-4 border-t text-center text-xs text-gray-400">
                <p>Dokumen ini telah divalidasi secara elektronik menggunakan Tanda Tangan Elektronik Balai Sertifikasi Elektronik (BSrE).</p>
                <p class="mt-1 font-semibold text-gray-500">© {{ date('Y') }} {{ $agencyName }} {{ $govName }}</p>
            </div>

        </div>
    </div>
</x-guest-layout>
