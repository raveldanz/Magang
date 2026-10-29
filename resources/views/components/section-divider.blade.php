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

    <!-- 3. Ornamen Tengah ◆ ❖ ◆ -->
    <div class="mx-2 sm:mx-3.5 flex items-center gap-1.5 sm:gap-2.5 shrink-0">
        <!-- Satelit Kiri (Spring Floating Physics) -->
        <span class="w-1.5 h-1.5 sm:w-2 sm:h-2 rotate-45 rounded-[2px] bg-gradient-to-tr from-blue-600 to-sky-400 dark:from-blue-500 dark:to-sky-300 shadow-xs shadow-blue-500/40 dark:shadow-sky-400/30 transition-all duration-500 [transition-timing-function:cubic-bezier(0.34,1.56,0.64,1)] group-hover:-translate-x-1.5 group-hover:scale-125 group-hover:rotate-[55deg] group-hover:shadow-blue-400/70 dark:group-hover:shadow-sky-300/70"></span>
        
        <!-- Diamond Pusat -->
        <div class="relative flex items-center justify-center w-9 h-9 sm:w-11 sm:h-11">
            <!-- Aura Cahaya Pijar (Glowing Ambient Glow dengan Bloom Halus) -->
            <div class="absolute inset-0 rounded-2xl bg-blue-500/25 dark:bg-blue-400/30 blur-md group-hover:bg-blue-500/45 dark:group-hover:bg-blue-400/60 group-hover:blur-xl group-hover:scale-135 transition-all duration-700 ease-out"></div>
            
            <!-- Diamond Luar dengan Spring Hover Physics (+10 deg dynamic tilt halus & organik) -->
            <div class="relative w-6 h-6 sm:w-7 sm:h-7 rotate-45 rounded-lg bg-gradient-to-tr from-blue-600 via-indigo-500 to-sky-400 dark:from-blue-500 dark:via-indigo-400 dark:to-sky-300 p-[1.5px] shadow-md shadow-blue-500/25 dark:shadow-sky-400/20 transition-all duration-500 [transition-timing-function:cubic-bezier(0.34,1.56,0.64,1)] group-hover:rotate-[55deg] group-hover:scale-115 group-hover:shadow-xl group-hover:shadow-blue-500/40 dark:group-hover:shadow-sky-400/35">
                <!-- Wajah Diamond Dalam (Adaptif Light/Dark) -->
                <div class="w-full h-full bg-white dark:bg-slate-900 rounded-[5px] sm:rounded-[6px] flex items-center justify-center transition-colors duration-500">
                    <!-- Bintang 4 Titik (Lembut Membesar & Bercahaya) -->
                    <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5 text-blue-600 dark:text-sky-400 -rotate-45 group-hover:-rotate-[55deg] group-hover:scale-125 group-hover:text-blue-500 dark:group-hover:text-sky-300 transition-all duration-500 [transition-timing-function:cubic-bezier(0.34,1.56,0.64,1)]" 
                         fill="currentColor" 
                         viewBox="0 0 24 24">
                        <path d="M12 2L14.3 9.7L22 12L14.3 14.3L12 22L9.7 14.3L2 12L9.7 9.7L12 2Z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Satelit Kanan (Spring Floating Physics) -->
        <span class="w-1.5 h-1.5 sm:w-2 sm:h-2 rotate-45 rounded-[2px] bg-gradient-to-tl from-blue-600 to-sky-400 dark:from-blue-500 dark:to-sky-300 shadow-xs shadow-blue-500/40 dark:shadow-sky-400/30 transition-all duration-500 [transition-timing-function:cubic-bezier(0.34,1.56,0.64,1)] group-hover:translate-x-1.5 group-hover:scale-125 group-hover:rotate-[35deg] group-hover:shadow-blue-400/70 dark:group-hover:shadow-sky-300/70"></span>
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
