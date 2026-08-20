<div class="flex items-center gap-2.5">
    <img src="{{ asset('images/logoPemkotSBY.png') }}" 
         alt="Logo Pemkot Surabaya" 
         {{ $attributes->merge(['class' => 'h-8 w-auto object-contain transition-transform duration-200 hover:scale-105']) }}>
    <div class="hidden sm:flex flex-col text-left">
        <span class="text-xs font-bold tracking-tight text-slate-900 leading-tight">Magang Pemkot</span>
        <span class="text-[10px] text-slate-400 uppercase tracking-wider font-semibold">Surabaya</span>
    </div>
</div>