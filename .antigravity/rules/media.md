# Panduan Standar Media & Pengelolaan Logo Instansi

Dokumen ini mendefinisikan aturan dan standar baku pengelolaan aset visual, khususnya logo instansi/perusahaan mitra magang di dalam aplikasi Laravel.

---

## 1. Standar Format & Spesifikasi Berkas Logo

Untuk menjaga ketajaman visual dan performa rendering aplikasi:

| Format | Prioritas | Penggunaan & Ketentuan |
| :--- | :---: | :--- |
| **SVG (`.svg`)** | **Utama (Direkomendasikan)** | Wajib digunakan jika aset vektor tersedia. Vektor tidak akan pecah di resolusi retina/4K, berukuran sangat kecil (< 50KB), dan mendukung rendering tajam. Pastikan tag `<svg>` sudah di-sanitize jika di-upload oleh user. |
| **PNG Transparan (`.png`)** | **Sekunder** | Digunakan apabila hanya format raster yang tersedia. Wajib memiliki *alpha transparency* (tanpa background putih kaku/kotak). Resolusi minimum 512x512 piksel (atau 300 DPI) agar tidak buram saat di-zoom. |
| **WebP (`.webp`)** | **Alternatif Modern** | Pilihan ideal untuk efisiensi penyimpanan dan loading cepat jika file raster dikompresi dari server sebelum disajikan ke web. |

### Konvensi Penamaan Berkas:
- Gunakan format **kebab-case** huruf kecil: `logo-[nama-instansi]-[varian].[ext]`
- Contoh: `logo-kemendikbud-primary.svg`, `logo-bumn-mono.png`.
- Hindari spasi, simbol aneh, atau karakter non-ASCII pada nama berkas.

---

## 2. Penggunaan Laravel Asset Helper

Aset logo disimpan pada disk publik Laravel (`storage/app/public/logos/`) yang diakses publik melalui tautan simbolik (`public/storage/logos/`).

### A. Memanggil Logo dari Storage (Dinamis / Database)
Gunakan helper `asset()` yang mengarah ke `storage/logos/...`:

```blade
{{-- Rekomendasi dengan fallback default jika logo kosong/null --}}
@php
    $logoUrl = !empty($institution->logo) && file_exists(public_path('storage/logos/' . $institution->logo))
        ? asset('storage/logos/' . $institution->logo)
        : asset('images/default-institution-logo.svg');
@endphp

<img src="{{ $logoUrl }}" 
     alt="Logo {{ $institution->name ?? 'Instansi' }}" 
     class="h-12 w-auto object-contain"
     loading="lazy">
```

### B. Memanggil Logo Statis (Aset Tema / Bawaan Sistem)
Untuk logo statis bawaan aplikasi yang berada di direktori `public/`:
```blade
<img src="{{ asset('assets/logos/logo-kemendikbud.svg') }}" 
     alt="Logo Kemendikbudristek" 
     class="h-10 w-auto object-contain">
```

### C. Catatan Symlink Storage Laravel
Pastikan symlink storage telah dibuat agar berkas di `storage/app/public/` dapat diakses oleh browser:
```bash
php artisan storage:link
```

---

## 3. Standar Rasio Aspek & Styling Tailwind CSS

Logo instansi memiliki variasi bentuk yang berbeda-beda (horizontal panjang, vertikal tinggi, persegi, atau bulat). Untuk mencegah **distorsi (gepeng/tertarik)** dan menjaga kerapian antarmuka Blade:

### A. Aturan Emas Styling:
1. **Gunakan `object-contain`**: Jangan pernah memakai `object-cover` untuk logo berbasis teks/simbol karena akan terpotong atau terdistorsi.
2. **Kunci Salah Satu Dimensi**: Pasang tinggi tetap (fixed height seperti `h-8`, `h-12`, `h-16`) dan biarkan lebar otomatis (`w-auto`), atau sebaliknya.
3. **Gunakan Container Box**: Selalu letakkan di dalam wrapper flexbox dengan perataan tengah:
   ```blade
   <div class="flex items-center justify-center p-2 bg-white rounded-lg shadow-sm border border-slate-100">
       <img ... />
   </div>
   ```

### B. Preset Tailwind CSS Berdasarkan Konteks Tampilan

#### 1. Header / Navbar / Topbar
Cocok untuk logo horizontal maupun persegi kecil:
```blade
<div class="flex items-center h-16">
    <img src="{{ asset('storage/logos/' . $institution->logo) }}" 
         alt="{{ $institution->name }}" 
         class="h-9 max-w-[160px] w-auto object-contain">
</div>
```

#### 2. Card / Grid Mitra Instansi
Memastikan semua logo sejajar tanpa merusak layout grid:
```blade
<div class="w-full h-24 bg-white rounded-xl border border-gray-100 p-3 flex items-center justify-center">
    <img src="{{ asset('storage/logos/' . $institution->logo) }}" 
         alt="{{ $institution->name }}" 
         class="max-h-16 max-w-full w-auto object-contain transition-transform duration-200 hover:scale-105">
</div>
```

#### 3. Lembar Dokumen / Laporan Akhir Mahasiswa (`final_report.blade.php`)
Standar kover laporan resmi magang / lembar pengesahan:
```blade
<div class="my-6 flex justify-center items-center">
    <img src="{{ !empty($institution->logo) ? asset('storage/logos/' . $institution->logo) : asset('images/default-logo.svg') }}" 
         alt="Logo Resmi {{ $institution->name }}" 
         class="h-24 sm:h-28 max-w-[240px] w-auto object-contain mx-auto filter drop-shadow-sm">
</div>
```

---

## 4. Checklist Kualitas Aset Visual
- [ ] Berkas berformat `.svg`, `.png` transparan, atau `.webp`.
- [ ] Tidak ada background putih pekat yang mengganggu pada mode gelap atau latar belakang berwarna.
- [ ] Atribut `alt` terisi nama instansi secara spesifik untuk aksesibilitas (a11y).
- [ ] Class Tailwind mengandung `object-contain` dan pembatas dimensi (`h-* w-auto` atau `max-h-* max-w-*`).
- [ ] Symlink `php artisan storage:link` aktif pada server.
