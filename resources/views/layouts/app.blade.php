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
    </body>
</html>
