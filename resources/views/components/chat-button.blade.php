@props(['user', 'label' => 'Chat', 'variant' => 'secondary'])

{{-- Tombol "Chat" kontekstual: hanya tampil bila pengguna boleh menghubungi $user (ChatContactDirectory) --}}
@php
    $chatViewer = auth()->user();
    $canChat = $chatViewer && $user && (int) $chatViewer->id !== (int) $user->id
        && app(\App\Services\Chat\ChatContactDirectory::class)->canContact($chatViewer, $user);
@endphp

@if ($canChat)
    <form method="POST" action="{{ route('chat.start') }}" class="inline-flex m-0">
        @csrf
        <input type="hidden" name="user_id" value="{{ $user->id }}">
        <button type="submit" title="Kirim pesan ke {{ $user->name }}"
                {{ $attributes->class([
                    'inline-flex items-center justify-center gap-1.5 px-3.5 py-2 text-xs font-bold rounded-xl transition shadow-xs whitespace-nowrap active:scale-95 cursor-pointer',
                    'bg-blue-600 hover:bg-blue-700 text-white' => $variant === 'primary',
                    'bg-white border border-blue-200 text-blue-700 hover:bg-blue-50 hover:border-blue-300' => $variant !== 'primary',
                ]) }}>
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
            <span>{{ $label }}</span>
        </button>
    </form>
@endif
