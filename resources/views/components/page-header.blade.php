@props(['title', 'description' => null])

<div {{ $attributes->merge(['class' => 'flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4']) }}>
    <div class="min-w-0">
        <h2 class="font-extrabold text-xl sm:text-2xl text-slate-900 tracking-tight leading-tight break-words">
            {{ $title }}
        </h2>
        @if($description)
            <p class="text-xs sm:text-sm text-slate-600 mt-1 leading-relaxed">
                {{ $description }}
            </p>
        @endif
    </div>
    @if(isset($actions))
        <div class="flex flex-wrap items-center gap-2.5 sm:gap-3 shrink-0">
            {{ $actions }}
        </div>
    @endif
</div>
