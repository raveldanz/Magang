@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'min-h-[44px] sm:min-h-[42px] px-3.5 py-2 bg-white border-slate-300 text-slate-900 placeholder:text-slate-400 text-sm focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20 rounded-xl shadow-2xs transition disabled:bg-slate-100 disabled:text-slate-500']) }}>
