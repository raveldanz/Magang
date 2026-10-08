<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('lecturer.students.show', $placement->id) }}" 
               class="p-2.5 rounded-xl bg-slate-100 text-slate-600 hover:bg-slate-200 hover:text-slate-900 transition" 
               title="Kembali ke Detail Mahasiswa">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <h2 class="font-bold text-xl sm:text-2xl text-slate-900 leading-tight">
                    Penilaian Evaluasi Akhir
                </h2>
                <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                    Mahasiswa: <strong class="text-slate-800">{{ $student->name ?? '-' }}</strong> (NIM: {{ $profile->nim ?? '-' }})
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Error -->
            @if ($errors->any())
                <div class="p-4 bg-rose-50 border-l-4 border-rose-500 rounded-r-lg text-rose-900 text-sm space-y-1">
                    <p class="font-bold">Terjadi kesalahan input nilai:</p>
                    <ul class="list-disc list-inside text-xs">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Komponen Bersama Formulir Penilaian -->
            <x-supervision.score-form
                :placement="$placement"
                :student="$student"
                :profile="$profile"
                :unit="$unit"
                :agencyProfile="$agencyProfile"
                :evaluation="$evaluation"
                :univ="$univ"
                :submitRoute="route('lecturer.evaluations.store', $placement->id)"
                :lockReason="$lockReason ?? null"
                :cancelRoute="route('lecturer.students.show', $placement->id)"
                role="lecturer"
            />

        </div>
    </div>
</x-app-layout>
