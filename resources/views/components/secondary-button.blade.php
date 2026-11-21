<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center gap-2 min-h-[44px] sm:min-h-[40px] px-4 py-2.5 bg-white hover:bg-slate-50 active:bg-slate-100 border border-slate-300 text-slate-700 text-xs sm:text-sm font-bold rounded-xl shadow-2xs transition duration-150 ease-in-out active:scale-95 cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed']) }}>
    {{ $slot }}
</button>
