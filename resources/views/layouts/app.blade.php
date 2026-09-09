<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth overflow-x-hidden w-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Portal Magang — Pemerintah Kota Surabaya') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            [x-cloak] { display: none !important; }
            html, body {
                max-width: 100vw;
                overflow-x: hidden;
            }
        </style>
    </head>
    <body class="font-sans antialiased bg-[#F5F8FC] text-slate-900 overflow-x-hidden w-full relative">
        <div class="min-h-screen bg-[#F5F8FC] w-full overflow-x-hidden flex flex-col">
            
            <header class="sticky top-0 z-50 bg-white w-full border-b border-slate-200/80">
                @if(session()->has('impersonator_id'))
                    <aside aria-label="Impersonation Alert" class="bg-gradient-to-r from-amber-600 via-rose-600 to-red-600 text-white shadow-md border-b border-rose-700/60 w-full overflow-hidden">
                        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-2 flex flex-col sm:flex-row items-center justify-between gap-2 min-h-[44px]">
                            <div class="flex items-center gap-2 text-xs sm:text-sm font-medium text-white/95 tracking-normal truncate max-w-full">
                                <span class="inline-flex relative flex h-2.5 w-2.5 shrink-0">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-white"></span>
                                </span>
                                <span class="truncate">
                                    Mode Penyamaran: <strong class="font-bold text-white">{{ auth()->user()->name }}</strong> 
                                    <span class="inline-flex items-center font-bold tracking-wider uppercase text-[10px] bg-white/20 px-1.5 py-0.5 rounded ml-1 border border-white/25">
                                        {{ auth()->user()->role }}
                                    </span>
                                </span>
                            </div>
                            <form action="{{ route('admin.impersonate.leave') }}" method="POST" class="shrink-0 m-0 w-full sm:w-auto">
                                @csrf
                                <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-3 py-1 bg-white hover:bg-rose-50 text-rose-700 text-xs font-bold rounded-lg shadow-sm transition active:scale-95 cursor-pointer border border-white/40">
                                    <svg class="w-3.5 h-3.5 text-rose-700 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                    </svg>
                                    <span>Kembali</span>
                                </button>
                            </form>
                        </div>
                    </aside>
                @endif

                @include('layouts.navigation')
            </header>

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow-xs w-full overflow-hidden border-b border-slate-100">
                    <div class="max-w-7xl mx-auto py-4 px-3 sm:px-6 lg:px-8 w-full overflow-hidden">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content (flex-1 agar pas memenuhi sisa layar tanpa memaksa ruang kosong) -->
            <main class="w-full flex-1 overflow-x-hidden">
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
        class="fixed bottom-4 right-4 z-50 flex flex-col space-y-2 pointer-events-none max-w-[calc(100vw-2rem)] sm:max-w-sm w-full"
        style="z-index: 99999;">
            <template x-for="t in toasts" :key="t.id">
                <div x-show="true"
                     x-transition:enter="transition ease-out duration-300 transform"
                     x-transition:enter-start="opacity-0 translate-y-3 scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                     x-transition:leave="transition ease-in duration-200 transform"
                     x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                     x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                     class="pointer-events-auto p-3.5 rounded-2xl shadow-xl border flex items-start gap-2.5 backdrop-blur-md transition-all text-xs"
                     :class="{
                         'bg-white/95 border-emerald-200 text-emerald-900 shadow-emerald-500/10': t.type === 'success',
                         'bg-white/95 border-rose-200 text-rose-900 shadow-rose-500/10': t.type === 'error',
                         'bg-white/95 border-amber-200 text-amber-900 shadow-amber-500/10': t.type === 'warning',
                         'bg-white/95 border-blue-200 text-blue-900 shadow-blue-500/10': t.type === 'info'
                     }">
                    <div class="flex-1 font-medium leading-snug" x-text="t.message"></div>
                    <button @click="remove(t.id)" class="shrink-0 text-gray-400 hover:text-gray-600 transition p-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </template>
        </div>
    </body>
</html>