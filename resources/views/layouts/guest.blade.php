<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth overflow-x-hidden w-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@hasSection('title')@yield('title') - @endif{{ config('app.name', 'Portal Magang Dispusip — Dinas Perpustakaan dan Kearsipan Kota Surabaya') }}</title>

        <!-- Favicon Portal Magang (Official Dispusip) -->
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}?v=5">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}?v=5">
        <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v=5">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}?v=5">
        <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('android-chrome-192x192.png') }}?v=5">
        <link rel="manifest" href="{{ asset('site.webmanifest') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" media="print" onload="this.media='all'" />

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
    {{-- data-autosave-purge: draf isian form milik akun sebelumnya dihapus dari tab ini saat halaman masuk/daftar --}}
    <body class="app-readable font-sans text-slate-900 antialiased bg-[#F5F8FC] overflow-x-hidden w-full" data-autosave-purge>
        <div class="min-h-screen w-full flex flex-col justify-center items-center px-4 py-6 sm:px-6 lg:px-8">
            
            <div class="w-full sm:max-w-md max-w-full">
                {{ $slot }}
            </div>

        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const formFields = document.querySelectorAll('input[required], select[required], textarea[required]');
                formFields.forEach(field => {
                    field.addEventListener('invalid', function () {
                        if (this.validity.customError) return;

                        if (this.validity.valueMissing) {
                            this.setCustomValidity('Harap lengkapi kolom ini terlebih dahulu.');
                        } else if (this.validity.typeMismatch && this.type === 'email') {
                            this.setCustomValidity('Masukkan format alamat email yang valid.');
                        } else if (this.validity.tooShort) {
                            this.setCustomValidity(`Minimal panjang isian adalah ${this.minLength} karakter.`);
                        }
                    });
                    field.addEventListener('input', function () {
                        this.setCustomValidity('');
                    });
                });
            });
        </script>
    </body>
</html>