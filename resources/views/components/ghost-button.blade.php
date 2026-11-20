<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center gap-2 min-h-[44px] sm:min-h-[40px] px-3.5 py-2 bg-transparent hover:bg-slate-100 active:bg-slate-200/70 text-slate-700 hover:text-blue-600 text-xs sm:text-sm font-bold rounded-xl transition duration-150 ease-in-out cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500/40 disabled:opacity-50 disabled:cursor-not-allowed']) }}>
    {{ $slot }}
</button>
