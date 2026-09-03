<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Portal Magang — Pemerintah Kota Surabaya') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            [x-cloak] { display: none !important; }
        </style>
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            <header class="sticky top-0 z-50 bg-white">
                @if(session()->has('impersonator_id'))
                    <aside aria-label="Impersonation Alert" class="bg-gradient-to-r from-amber-600 via-rose-600 to-red-600 text-white shadow-md border-b border-rose-700/60">
                        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2 flex flex-col sm:flex-row items-center justify-between gap-3 min-h-[44px]">
                            <div class="flex items-center gap-2.5 text-xs sm:text-sm font-medium text-white/95 tracking-normal">
                                <span class="inline-flex relative flex h-2.5 w-2.5 shrink-0">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-white"></span>
                                </span>
                                <span>
                                    Mode Penyamaran: Anda sedang mengelola akun <strong class="font-bold text-white">{{ auth()->user()->name }}</strong> 
                                    <span class="inline-flex items-center font-bold tracking-wider uppercase text-[11px] bg-white/20 px-2 py-0.5 rounded-md ml-1 border border-white/25">
                                        {{ auth()->user()->role }}
                                    </span>
                                </span>
                            </div>
                            <form action="{{ route('admin.impersonate.leave') }}" method="POST" class="shrink-0 m-0">
                                @csrf
                                <button type="submit" class="inline-flex items-center gap-2 px-3.5 py-1.5 bg-white hover:bg-rose-50 text-rose-700 text-xs font-bold rounded-lg shadow-sm transition active:scale-95 cursor-pointer border border-white/40">
                                    <svg class="w-3.5 h-3.5 text-rose-700 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                    </svg>
                                    <span>Kembali ke Super Admin ({{ session('impersonator_name') }})</span>
                                </button>
                            </form>
                        </div>
                    </aside>
                @endif

                @include('layouts.navigation')
            </header>

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>

        <!-- Global Toast Notification System (Alpine.js) -->
        <div x-data="{
            toasts: [],
            init() {
                @if(session('success'))
                    this.add('success', '{{ addslashes(session('success')) }}');
                @endif
                @if(session('error'))
                    this.add('error', '{{ addslashes(session('error')) }}');
                @endif
                @if(session('warning'))
                    this.add('warning', '{{ addslashes(session('warning')) }}');
                @endif
                @if(session('status'))
                    this.add('info', '{{ addslashes(session('status')) }}');
                @endif
            },
            add(type, message) {
                const id = Date.now() + Math.random();
                this.toasts.push({ id, type, message });
                setTimeout(() => this.remove(id), 4500);
            },
            remove(id) {
                this.toasts = this.toasts.filter(t => t.id !== id);
            }
        }"
        @toast.window="add($event.detail.type || 'info', $event.detail.message)"
        class="fixed bottom-5 right-5 z-50 flex flex-col space-y-3 pointer-events-none max-w-sm w-full"
        style="z-index: 99999;">
            <template x-for="t in toasts" :key="t.id">
                <div x-show="true"
                     x-transition:enter="transition ease-out duration-300 transform"
                     x-transition:enter-start="opacity-0 translate-y-3 scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                     x-transition:leave="transition ease-in duration-200 transform"
                     x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                     x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                     class="pointer-events-auto p-4 rounded-2xl shadow-xl border flex items-start gap-3 backdrop-blur-md transition-all"
                     :class="{
                         'bg-white/95 border-emerald-200 text-emerald-900 shadow-emerald-500/10': t.type === 'success',
                         'bg-white/95 border-rose-200 text-rose-900 shadow-rose-500/10': t.type === 'error',
                         'bg-white/95 border-amber-200 text-amber-900 shadow-amber-500/10': t.type === 'warning',
                         'bg-white/95 border-blue-200 text-blue-900 shadow-blue-500/10': t.type === 'info'
                     }">
                    
                    <!-- Icon -->
                    <div class="shrink-0 mt-0.5">
                        <template x-if="t.type === 'success'">
                            <div class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            </div>
                        </template>
                        <template x-if="t.type === 'error'">
                            <div class="w-6 h-6 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                            </div>
                        </template>
                        <template x-if="t.type === 'warning'">
                            <div class="w-6 h-6 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            </div>
                        </template>
                        <template x-if="t.type === 'info'">
                            <div class="w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                        </template>
                    </div>

                    <!-- Message Body -->
                    <div class="flex-1 text-xs sm:text-sm font-medium leading-snug" x-text="t.message"></div>

                    <!-- Close Button -->
                    <button @click="remove(t.id)" class="shrink-0 text-gray-400 hover:text-gray-600 transition p-1 rounded-lg">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </template>
        </div>
    </body>
</html>
