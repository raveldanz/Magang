<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center gap-2 min-h-[44px] sm:min-h-[40px] px-4 py-2.5 bg-rose-600 hover:bg-rose-700 active:bg-rose-800 border border-transparent text-white text-xs sm:text-sm font-bold rounded-xl shadow-xs transition duration-150 ease-in-out active:scale-95 cursor-pointer focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed']) }}>
    {{ $slot }}
</button>
