<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
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
    </head>
    <body class="font-sans antialiased">
        @if(session()->has('impersonator_id'))
            <div class="bg-gradient-to-r from-amber-600 via-rose-600 to-red-600 text-white px-4 py-2.5 shadow-lg flex items-center justify-between sticky top-0 z-[99999] text-xs sm:text-sm font-medium border-b border-rose-700">
                <div class="flex items-center gap-2">
                    <span class="animate-pulse text-base">⚠️</span>
                    <span>Mode Penyamaran: Anda sedang mengelola akun <strong>{{ auth()->user()->name }}</strong> (Role: <span class="uppercase tracking-wider font-bold bg-white/20 px-1.5 py-0.5 rounded">{{ auth()->user()->role }}</span>)</span>
                </div>
                <form action="{{ route('admin.impersonate.leave') }}" method="POST">
                    @csrf
                    <button type="submit" class="px-3 py-1 bg-white hover:bg-rose-50 text-rose-700 rounded-lg font-bold shadow-md transition text-xs flex items-center gap-1.5 cursor-pointer">
                        <span>⬅️</span>
                        <span>Kembali ke Super Admin ({{ session('impersonator_name') }})</span>
                    </button>
                </form>
            </div>
        @endif

        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

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
