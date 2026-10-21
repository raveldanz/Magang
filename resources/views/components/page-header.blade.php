@props(['title', 'description' => null])

<div {{ $attributes->merge(['class' => 'flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4']) }}>
    <div>
        <h2 class="font-bold text-2xl text-gray-900 leading-tight">
            {{ $title }}
        </h2>
        @if($description)
            <p class="text-sm text-slate-600 mt-1">
                {{ $description }}
            </p>
        @endif
    </div>
    @if(isset($actions))
        <div class="flex items-center gap-3">
            {{ $actions }}
        </div>
    @endif
</div>
