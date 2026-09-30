<x-app-layout>
    {{-- Data untuk Alpine hanya array primitif dari ChatPresenter (LRN-020) --}}
    <div x-data="chatApp(@js($chatConfig))" class="max-w-7xl mx-auto w-full px-0 sm:px-6 lg:px-8 sm:pt-5">
        <div x-ref="shell"
             class="relative flex bg-white sm:rounded-2xl border-y sm:border border-slate-200/80 shadow-xs overflow-hidden"
             style="height: calc(100vh - 7rem);">
            @include('chat.partials.sidebar')
            @include('chat.partials.conversation')
            @include('chat.partials.info-panel')
        </div>

        @include('chat.partials.message-menu')
        @include('chat.partials.modals')
        @include('chat.partials.lightbox')
    </div>
</x-app-layout>
