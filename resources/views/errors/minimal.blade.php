{{--
    Kerangka halaman error (404, 419, 429, 500, 503, dan kode lain bawaan Laravel lewat errors::minimal).
    Sengaja berdiri sendiri: tanpa @vite, tanpa font/CDN eksternal, tanpa query database,
    supaya tetap tampil saat build aset hilang, sesi rusak, atau demo berjalan tanpa internet.
--}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('code') - @yield('title') | SIM Magang Surabaya</title>
    <link rel="shortcut icon" href="/favicon.ico?v=5">
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: flex; flex-direction: column; background: #f8fafc; color: #1e293b;
               font-family: Figtree, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; line-height: 1.6; }
        main { flex: 1; display: flex; align-items: center; justify-content: center; padding: 24px; }
        .kartu { width: 100%; max-width: 480px; background: #fff; border: 1px solid #e2e8f0; border-radius: 20px;
                 padding: 32px 28px; text-align: center; box-shadow: 0 10px 30px rgba(15, 23, 42, .08); }
        .kode { display: inline-block; padding: 4px 14px; border-radius: 999px; background: #e0f2fe; color: #075985;
                font-size: 14px; font-weight: 700; letter-spacing: .04em; }
        h1 { margin: 16px 0 8px; font-size: 24px; line-height: 1.3; color: #0f172a; }
        p { margin: 0 0 8px; font-size: 16px; color: #475569; }
        .aksi { margin-top: 24px; display: flex; flex-direction: column; gap: 10px; }
        .tombol { display: block; width: 100%; padding: 14px 16px; border-radius: 12px; border: 0; cursor: pointer;
                  font: inherit; font-size: 16px; font-weight: 700; text-decoration: none; }
        .utama { background: #0f172a; color: #fff; }
        .kedua { background: #e2e8f0; color: #1e293b; }
        .tombol:focus-visible { outline: 3px solid #38bdf8; outline-offset: 2px; }
        footer { padding: 16px; text-align: center; font-size: 13px; color: #64748b; }
    </style>
</head>
<body>
    <main>
        <div class="kartu">
            <span class="kode">Kode @yield('code')</span>
            <h1>@yield('title')</h1>
            <p>@yield('message')</p>
            <div class="aksi">
                @hasSection('actions')
                    @yield('actions')
                @else
                    <button type="button" class="tombol utama" onclick="history.back()">Kembali ke Halaman Sebelumnya</button>
                @endif
                <a class="tombol kedua" href="/">Halaman Depan</a>
            </div>
        </div>
    </main>
    <footer>Dinas Perpustakaan dan Kearsipan Kota Surabaya - Sistem Informasi Magang</footer>
</body>
</html>
