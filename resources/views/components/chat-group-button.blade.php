@props(['placement', 'label' => 'Grup Bimbingan'])

{{-- Tombol ke Grup Bimbingan penempatan: hanya untuk mahasiswa, mentor, dan DPL penempatan tersebut --}}
@php
    $chatViewer = auth()->user();
    $groupMembers = $placement
        ? array_map('intval', array_filter([$placement->application?->user_id, $placement->mentor_id, $placement->pembimbing_id, $placement->academic_advisor_id]))
        : [];
    $showGroup = $chatViewer && count(array_unique($groupMembers)) >= 2 && in_array((int) $chatViewer->id, $groupMembers, true);
@endphp

@if ($showGroup)
    <a href="{{ route('chat.placement', $placement->id) }}" title="Grup Bimbingan Magang"
       {{ $attributes->class('inline-flex items-center justify-center gap-1.5 px-3.5 py-2 text-xs font-bold rounded-xl transition shadow-xs whitespace-nowrap active:scale-95 bg-white border border-emerald-200 text-emerald-700 hover:bg-emerald-50 hover:border-emerald-300') }}>
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        <span>{{ $label }}</span>
    </a>
@endif
