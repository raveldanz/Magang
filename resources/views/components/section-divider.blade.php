@props([
    'id' => null,
])

@php
    $uniqueId = $id ?? 'wave_' . \Illuminate\Support\Str::random(8);
    $gradLeftId = $uniqueId . '_L';
    $gradRightId = $uniqueId . '_R';
@endphp

<!-- Pembatas Gelombang Elegan Kustom ◆ ❖ ◆ -->
<div {{ $attributes->merge(['class' => 'group relative my-2 sm:my-3 flex items-center justify-center w-full max-w-7xl mx-auto px-2 sm:px-4 select-none cursor-default transition-all duration-300 [--wave-c1:#3B82F6] [--wave-c2:#60A5FA] [--wave-c3:#2563EB] dark:[--wave-c1:#60A5FA] dark:[--wave-c2:#38BDF8] dark:[--wave-c3:#93C5FD] group-hover:[--wave-c2:#93C5FD] dark:group-hover:[--wave-c2:#BAE6FD] group-hover:[--wave-c3:#3B82F6] dark:group-hover:[--wave-c3:#E0F2FE]']) }}
     style="--wave-c1: #3B82F6; --wave-c2: #60A5FA; --wave-c3: #2563EB;"
     title="Transisi Arsip Perjalanan Karya Proyek Dinas">

    <!-- 1. Garis Aksen Ekstensi Kiri (Menyatu Mulus dengan Gelombang, Tersembunyi Rapi di HP) -->
    <div class="hidden sm:block flex-1 h-px bg-gradient-to-r from-transparent via-slate-200/80 to-blue-400/40 dark:via-slate-800 dark:to-blue-400/30 transition-opacity duration-500 group-hover:opacity-100 opacity-60"></div>

    <!-- 2. Gelombang Kiri -->
    <svg class="w-[130px] min-[400px]:w-[155px] sm:w-56 md:w-72 lg:w-[380px] xl:w-[420px] h-6 sm:h-9 md:h-11 shrink-0 overflow-visible transition-all duration-500 ease-out origin-right group-hover:scale-y-115 group-hover:-translate-x-1 group-hover:drop-shadow-[0_0_10px_rgba(59,130,246,0.4)] dark:group-hover:drop-shadow-[0_0_12px_rgba(96,165,250,0.6)]" 
         viewBox="0 0 440 56" 
         fill="none" 
         xmlns="http://www.w3.org/2000/svg">
        <defs>
            <linearGradient id="{{ $gradLeftId }}" x1="0" y1="28" x2="440" y2="28" gradientUnits="userSpaceOnUse">
                <stop offset="0%" stop-color="var(--wave-c1, #3B82F6)" stop-opacity="0.35"></stop>
                <stop offset="35%" stop-color="var(--wave-c2, #60A5FA)" stop-opacity="0.65"></stop>
                <stop offset="100%" stop-color="var(--wave-c3, #2563EB)" stop-opacity="1"></stop>
            </linearGradient>
        </defs>
        <!-- Jalur Gelombang Halus 1 (Aksen Anggun) -->
        <path d="M 0 28 Q 44.0 30.4, 88.0 28 Q 132.0 21.9, 176.0 28 Q 220.0 37.4, 264.0 28 Q 308.0 15.5, 352.0 28 Q 396.0 43.5, 440.0 28" 
              stroke="url(#{{ $gradLeftId }})" 
              stroke-width="1.2" 
              stroke-opacity="0.55" 
              stroke-linecap="round"
              class="transition-all duration-500 ease-out group-hover:stroke-opacity-90"></path>
        <!-- Jalur Gelombang Dinamis 2 (Nada Utama) -->
        <path d="M 0 28 Q 44.0 24.3, 88.0 28 Q 132.0 37.3, 176.0 28 Q 220.0 13.6, 264.0 28 Q 308.0 47.2, 352.0 28 Q 396.0 4.2, 440.0 28" 
              stroke="url(#{{ $gradLeftId }})" 
              stroke-width="2.2" 
              stroke-linecap="round"
              class="transition-all duration-500 ease-out group-hover:stroke-width-[2.6] group-hover:stroke-opacity-100"></path>
    </svg>

    <!-- 3. Ornamen Tengah: Tiga Titik Bulat Berjenjang & Logo Dispusip Bersih -->
    <div class="mx-2.5 sm:mx-4 flex items-center gap-2.5 sm:gap-3.5 shrink-0">
        <!-- 3 Titik Bulat Kiri (Kecil -> Sedang -> Besar) -->
        <div class="flex items-center gap-1.5 sm:gap-2 transition-transform duration-300 ease-out group-hover:-translate-x-1.5">
            <span class="w-1.5 h-1.5 rounded-full bg-blue-300 dark:bg-blue-400/60"></span>
            <span class="w-2 h-2 rounded-full bg-blue-400 dark:bg-blue-400"></span>
            <span class="w-2.5 h-2.5 rounded-full bg-blue-600 dark:bg-blue-300 shadow-xs shadow-blue-500/50"></span>
        </div>

        <!-- Logo Dispusip Bersih Tanpa Teks & Tanpa Badge Kaku -->
        <div class="relative flex items-center justify-center w-8 h-8 sm:w-10 sm:h-10 md:w-11 md:h-11 transition-all duration-300 ease-out group-hover:scale-115 group-hover:-translate-y-0.5">
            <!-- Pendaran Cahaya Halus (Atmospheric Soft Glow) -->
            <div class="absolute inset-0 rounded-full bg-blue-400/20 blur-md opacity-40 group-hover:opacity-90 group-hover:blur-lg transition-all duration-500"></div>
            <img src="{{ asset('images/logos/dispusip-clean.png') }}" 
                 class="relative w-full h-full object-contain filter drop-shadow-[0_2px_6px_rgba(0,0,0,0.06)] group-hover:drop-shadow-[0_4px_12px_rgba(59,130,246,0.35)] transition-all duration-300 select-none" 
                 alt="Logo Dispusip Surabaya" />
        </div>

        <!-- 3 Titik Bulat Kanan (Besar -> Sedang -> Kecil) -->
        <div class="flex items-center gap-1.5 sm:gap-2 transition-transform duration-300 ease-out group-hover:translate-x-1.5">
            <span class="w-2.5 h-2.5 rounded-full bg-blue-600 dark:bg-blue-300 shadow-xs shadow-blue-500/50"></span>
            <span class="w-2 h-2 rounded-full bg-blue-400 dark:bg-blue-400"></span>
            <span class="w-1.5 h-1.5 rounded-full bg-blue-300 dark:bg-blue-400/60"></span>
        </div>
    </div>

    <!-- 4. Gelombang Kanan -->
    <svg class="w-[130px] min-[400px]:w-[155px] sm:w-56 md:w-72 lg:w-[380px] xl:w-[420px] h-6 sm:h-9 md:h-11 shrink-0 overflow-visible transition-all duration-500 ease-out origin-left group-hover:scale-y-115 group-hover:translate-x-1 group-hover:drop-shadow-[0_0_10px_rgba(59,130,246,0.4)] dark:group-hover:drop-shadow-[0_0_12px_rgba(96,165,250,0.6)]" 
         viewBox="0 0 440 56" 
         fill="none" 
         xmlns="http://www.w3.org/2000/svg">
        <defs>
            <linearGradient id="{{ $gradRightId }}" x1="440" y1="28" x2="0" y2="28" gradientUnits="userSpaceOnUse">
                <stop offset="0%" stop-color="var(--wave-c1, #3B82F6)" stop-opacity="0.35"></stop>
                <stop offset="35%" stop-color="var(--wave-c2, #60A5FA)" stop-opacity="0.65"></stop>
                <stop offset="100%" stop-color="var(--wave-c3, #2563EB)" stop-opacity="1"></stop>
            </linearGradient>
        </defs>
        <!-- Jalur Gelombang Halus 1 (Simetris Sempurna Berlawanan) -->
        <path d="M 440 28 Q 396.0 30.4, 352.0 28 Q 308.0 21.9, 264.0 28 Q 220.0 37.4, 176.0 28 Q 132.0 15.5, 88.0 28 Q 44.0 43.5, 0.0 28" 
              stroke="url(#{{ $gradRightId }})" 
              stroke-width="1.2" 
              stroke-opacity="0.55" 
              stroke-linecap="round"
              class="transition-all duration-500 ease-out group-hover:stroke-opacity-90"></path>
        <!-- Jalur Gelombang Dinamis 2 (Simetris Sempurna Berlawanan) -->
        <path d="M 440 28 Q 396.0 24.3, 352.0 28 Q 308.0 37.3, 264.0 28 Q 220.0 13.6, 176.0 28 Q 132.0 47.2, 88.0 28 Q 44.0 4.2, 0.0 28" 
              stroke="url(#{{ $gradRightId }})" 
              stroke-width="2.2" 
              stroke-linecap="round"
              class="transition-all duration-500 ease-out group-hover:stroke-width-[2.6] group-hover:stroke-opacity-100"></path>
    </svg>

    <!-- 5. Garis Aksen Ekstensi Kanan (Menyatu Mulus dengan Gelombang, Tersembunyi Rapi di HP) -->
    <div class="hidden sm:block flex-1 h-px bg-gradient-to-l from-transparent via-slate-200/80 to-blue-400/40 dark:via-slate-800 dark:to-blue-400/30 transition-opacity duration-500 group-hover:opacity-100 opacity-60"></div>
</div>
