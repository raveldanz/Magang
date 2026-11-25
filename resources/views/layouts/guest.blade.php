<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth overflow-x-hidden w-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@hasSection('title')@yield('title') - @endif{{ config('app.name', 'Portal Magang Dispusip — Dinas Perpustakaan dan Kearsipan Kota Surabaya') }}</title>

        <!-- Favicon Portal Magang (Official Dispusip) -->
        <link rel="icon" type="image/png" sizes="32x32" href="data:image/png;base64,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">
        <link rel="shortcut icon" href="data:image/png;base64,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">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}?v=6">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}?v=6">
        <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v=6">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}?v=6">
        <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('android-chrome-192x192.png') }}?v=6">
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