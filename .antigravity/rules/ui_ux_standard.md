# Standar Desain & Handoff UI/UX Global — SIP-MAGANG (Dispusip Surabaya)

Dokumen rujukan tunggal (*single source of truth*) yang dikunci pada **Tahap 0** untuk seluruh pengerjaan penyelarasan antarmuka **Tahap 1–9**.

---

## 1. Protokol Wajib Tiap Tahap (Disiplin 11 Langkah)

Setiap sesi tahap (**Tahap 0, 1, 2, 3A, 3B, 4, 5, 6, 7, 8, 9**) WAJIB menjalankan urutan ketat berikut agar ruang lingkup fokus, desain seragam, dan riwayat Git aman:
1. **Baca Aturan & Kunci Ruang Lingkup**: Baca berkas ini, `AGENTS.md`, `CLAUDE.md`, dan `.antigravity/rules.md`. Edit **hanya** halaman/komponen milik tahap aktif (dilarang menyentuh halaman role lain).
2. **Audit Sinkronisasi & Visual BEFORE**: Cek `git status`/`git diff`; ambil screenshot BEFORE Desktop (`1440x900`) dan Mobile (`375x812`) via Playwright Chrome (`channel: 'chrome'`). Catat elemen Desktop yang sudah baik (wajib dipertahankan).
3. **Implementasi Anti-Rusak Desktop**: Gunakan token/komponen pada dokumen ini (jangan ciptakan gaya tandingan). Isolasi seluruh penyesuaian layar kecil dengan breakpoint (`sm:`, `md:`, `lg:`, `xl:` atau `@media`). Jangan ubah logika backend, route, atribut `name` form, `data-status`, `aria-label`, atau hook test.
4. **Verifikasi AFTER & Anti-Regresi**: Jalankan `npm run build` (bila ada kelas Tailwind baru) + `php artisan optimize:clear && php artisan view:clear`. Ambil screenshot AFTER Desktop (`1440x900`) & Mobile (`375x812`), inspeksi PNG via multimodal vision, pastikan `0` horizontal overflow Mobile (`scrollWidth <= innerWidth`), `0` console error, dan seluruh PHPUnit/Playwright terkait **PASS 100%** (Exit Code 0).
5. **Update Checklist & Titik Aman Git Commit**: Tandai tahap selesai (`[x] Selesai`) di Bagian 5 berkas ini, lalu buat satu `git commit` bersih khusus file tahap tersebut sebagai titik aman per role (tanpa atribusi AI; di PowerShell tulis pesan commit ke file sementara lalu `git commit -F <file>`).

---

## 2. Design System Tokens (Terkunci)

### A. Palet Warna Instansi (Bersih, Kontras Tinggi)
| Peran | Token / Kelas Tailwind | Pemakaian |
| :--- | :--- | :--- |
| **Canvas & Surface** | `bg-[#F5F8FC]`, kartu `bg-white border border-slate-200/80` | Latar aplikasi & permukaan kartu/panel |
| **Primary Brand** | `bg-blue-600 hover:bg-blue-700 text-white`, aksen `text-blue-600` | CTA utama, tab/link aktif, focus ring `ring-blue-500` |
| **Tipografi / Ink** | `text-slate-900` (judul), `text-slate-700` (isi), `text-slate-500` (caption) | Hierarki bacaan ramah pengguna senior |
| **Semantik** | `emerald` (sukses/aktif), `amber` (menunggu), `rose` (ditolak/hapus), `indigo`/`purple`/`orange` (tahap alur) | Alert, banner, dan badge status |

### B. Skala Tipografi, Spacing, Radius & Bayangan
- **Font**: `Figtree` (`font-sans antialiased`); kelas `app-readable` di `<body>` memaksa teks layar minimal `11px` (kecuali `.keep-size` & lembar cetak A4).
- **Heading & Teks** (`resources/css/app.css`): `.ui-h1` (`text-xl sm:text-2xl lg:text-3xl font-extrabold text-slate-900 tracking-tight`), `.ui-h2` (`text-lg sm:text-xl font-bold`), `.ui-h3` (`text-base sm:text-lg font-bold`), `.ui-body` (`text-sm sm:text-base text-slate-700 leading-relaxed`), `.ui-caption` (`text-xs text-slate-500`).
- **Container & Kartu**: `.ui-container` (`max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full`), `.ui-card` (`bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 sm:p-6`), `.ui-card-lg` (`rounded-3xl shadow-sm p-6 sm:p-8`). Kontrol/tombol/input memakai `rounded-xl`.

### C. Standar Tombol CTA (`min-h-[44px]` Mobile, `sm:min-h-[40px]` Desktop)
| Varian | Komponen Blade / Kelas CSS | Ringkasan Kelas Kunci |
| :--- | :--- | :--- |
| **Primary** | `<x-primary-button>` / `.btn-primary` | `bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-xs` |
| **Secondary** | `<x-secondary-button>` / `.btn-secondary` | `bg-white hover:bg-slate-50 border border-slate-300 text-slate-700 font-bold rounded-xl shadow-2xs` |
| **Danger** | `<x-danger-button>` / `.btn-danger` | `bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl shadow-xs` |
| **Ghost** | `<x-ghost-button>` / `.btn-ghost` | `bg-transparent hover:bg-slate-100 text-slate-700 hover:text-blue-600 font-bold rounded-xl` |
| **Aksi Tabel** | `.btn-action-{create,edit,reset,delete,login}` | Tinggi `30px` di desktop, otomatis `min-height: 38px` + wrap di `<= 639px` |

### D. Standar Badge Status (Wajib via `<x-status-badge>` & Enum)
Semua badge status wajib lewat `<x-status-badge :status="..." type="application|review|account|feedback" />` (kontras tinggi WCAG AA `bg-*-50 text-*-800 border-*-300`):
- **`ApplicationStatus`**: `DRAFT`/`RESIGNED` (`slate-100/slate-800`), `PENDING` (`amber-50/amber-800`), `WAITING_APPROVAL` (`purple-50/purple-800`), `ACCEPTED` (`indigo-50/indigo-800`), `ACTIVE` (`emerald-50/emerald-800`), `COMPLETED` (`blue-50/blue-800`), `REJECTED` (`rose-50/rose-800`).
- **`ReviewStatus`**: `PENDING` (`amber`), `APPROVED` (`emerald`), `REJECTED` (`rose`), `REVISION` (`orange-50/orange-800 border-orange-300`).
- **`AccountStatus`**: `ACTIVE` (`emerald`), `PENDING` (`amber`), `INACTIVE` (`slate`).
- **`FeedbackStatus`**: `PENDING` (`amber`), `IN_PROGRESS` (`blue`), `RESOLVED` (`emerald`), `CLOSED` (`slate`).

---

## 3. Breakpoint Resmi & Pola Komponen Global

- **Breakpoint**: Mobile `375px` (`< 640px`), `sm: 640px`, `md: 768px`, `lg: 1024px` (batas Desktop Navbar), `xl: 1280px`, Audit Desktop `1440x900`.
- **Navbar & Off-Canvas Drawer (`layouts/navigation.blade.php`)**:
  - Desktop (`>= 1024px`): Horizontal navbar `hidden lg:flex`, tinggi `h-20`, logo Pemkot + Dispusip, link aktif `border-b-2 border-blue-600`, dropdown `max-w-[calc(100vw-2rem)]`.
  - Mobile/Tablet (`< 1024px`, `lg:hidden`): Top bar `h-16 sm:h-20`, tombol Chat, Bell, dan Hamburger (`aria-label="Buka Menu"`) minimal `44x44px`. Drawer slide-over kanan (`data-nav-drawer`, `w-[min(86vw,340px)]`), backdrop `bg-slate-900/60 backdrop-blur-xs`, header dengan tombol tutup `44x44px` (`data-nav-drawer-close`), tutup via `Escape`, tautan `min-h-[44px]`, footer logout sticky.
- **Tabel Responsif & Card Stack di `375px`**:
  - Pilihan 1 (tabel 4–6 kolom): `<x-responsive-table-style />` + `<table class="rtable">` + `<td data-label="...">` (otomatis jadi kartu `< 768px`).
  - Pilihan 2 (tabel kompleks >6 kolom): `<x-mobile-list-style />` dengan `.d-only` (desktop) dan `.m-only .mlist > .mcard` (mobile, tombol `.mbtn` `min-h: 44px`).
  - Pilihan 3 (matriks padat): bungkus `.ui-table-wrap` (`overflow-x-auto max-w-full`).
- **Modal, Dropdown, Form & Pagination**:
  - Modal (`x-modal`, `confirm-*-modal`, `bulk-action-modal`): `max-w-[calc(100vw-1.5rem)] max-h-[90vh] overflow-y-auto rounded-2xl sm:rounded-3xl`, tombol tutup & CTA `min-h-[44px]`.
  - Form mobile 1 kolom (`grid-cols-1 md:grid-cols-2 gap-4`), input `<x-text-input>` (`min-h-[44px]`), label `<x-input-label>`, tombol submit `w-full sm:w-auto`.
  - Dropdown & Toast: `max-w-[calc(100vw-1.5rem)]`; Pagination (`vendor/pagination/tailwind.blade.php`) `flex-wrap justify-center` + target sentuh `40px` di mobile.

---

## 4. Catatan Teknis CSS / JS / Alpine / Vite (Wajib Dipatuhi)

- **Tailwind v3.4.19 + PostCSS**: `tailwind.config.js` telah mendaftarkan ekstensi `shadow-2xs`, `shadow-xs`, `backdrop-blur-xs`, `blur-xs`, `scale-98`. Setelah menambah kelas Tailwind baru, wajib `npm run build` dan pastikan `public/hot` tidak tertinggal.
- **Alpine.js v3**: Pakai `[x-cloak]`, gunakan format objek `:style="{ ... }"` jika berpasangan dengan `x-show` (LRN-040), dan **jangan** memasang `touch-none` di `document.body` saat drawer terbuka agar isi drawer bisa digulir di layar sentuh.
- **Blade & Data Safety**: Normalisasi enum `$s instanceof \BackedEnum ? $s->value : $s` (LRN-022); jangan lempar model Eloquent utuh ke `@json()` (LRN-020); halaman A4/sertifikat wajib memakai `partials/sheet-fit` (LRN-161); berkas privat disajikan lewat route terotorisasi, bukan `asset('storage/...')`.
- **Anti-Redundansi (LRN-136)**: Micro-copy ringkas, tanpa banner pengulangan, tanpa nama entitas panjang dalam label tombol; pastikan `UiMinimalismAndAntiRedundancyTest` tetap PASS.
- **Runner Audit Visual**: `node scripts/ui_foundation_audit.mjs --tag <nama> --strict` (pakai `waitUntil: 'load'`, bukan `networkidle`, karena polling chat aktif).

---

## 5. Checklist Progres 11 Tahap

- [x] **Tahap 0 — Fondasi Layout Global & Design System** (`layouts/*`, `components/*`, `app.css`, `tailwind.config.js`, `Enums`, pagination) — **Selesai**
- [x] **Tahap 1 — Halaman Publik, Auth & Verifikasi QR** (`welcome`, `auth/*`, `projects/*`, `verify-*`) — **Selesai**
  - Di bawah 640 px, daftar instansi mengikuti alur formulir dan dapat digulir. Mulai 640 px, daftar terbuka tepat di bawah input sebagai dropdown.
  - Jalankan `node scripts/ui_stage1_audit.mjs --tag after --strict` dengan token fixture lokal `STAGE1_LETTER_TOKEN` dan `STAGE1_CERTIFICATE_TOKEN`.
  - Audit mencakup 17 skenario pada 1440 x 900 dan 375 x 812, ditambah 16 tangkapan interaksi.
- [x] **Tahap 2 — Portal Mahasiswa** (`student/dashboard`, `profile`, `application`, `logbook`, `final_report`, `projects`) — **Selesai**
  - Audit langsung memakai Edge pada 375 × 812 dan 1440 × 900. Playwright Chrome melengkapi matriks Beranda, Profil, pengajuan individu dan kelompok, riwayat dan editor Logbook, buat dan edit proyek, Laporan Akhir, serta pratinjau sertifikat. PHPUnit terkait lulus 136/136.
  - Pola seluler: status dan langkah berikutnya di Beranda, kartu riwayat dengan aksi 44 piksel, pintasan ke naskah laporan, dan nama berkas yang bisa membungkus. Skenario transisi dan penyimpanan diuji melalui PHPUnit dengan data uji.
- [x] **Tahap 3A — Portal Admin Dinas & Super Admin (Dashboard, Pengajuan, Instansi/Unit)** — **Selesai**
  - Audit pakai `node scripts/ui_stage_audit.mjs --stage 3a --tag after` (skenario di `scripts/stage_scenarios/3a.json`): mengukur overflow, elemen bertumpuk, jarak antar kontrol, target sentuh, teks terpotong, error console, serta membuka otomatis dropdown/modal.
  - Pola baru: kontrol ketuk di HP `min-h-[44px] sm:min-h-0`; baris kartu dokumen `flex-col lg:flex-row` agar nama berkas tidak terjepit; daftar panjang (akun instansi) digulir di panel pada desktop dan diringkas 8 teratas + "Tampilkan semua" di HP; nama panjang di HP dibungkus (`sm:truncate`), bukan dipotong.
- [x] **Tahap 3B — Portal Admin Dinas & Super Admin (Kampus/Sekolah, Pengguna, Mentor, Logbook, Sertifikat, Feedback, Audit)** — **Selesai**
  - Bug nyata: `admin/universities/partials/tab-policy.blade.php` punya `</div>` berlebih sehingga modal Tambah DPL dan Penugasan DPL jatuh di luar scope Alpine (error "showAddDosenModal is not defined"). Aturan: setiap partial tab harus seimbang tag `<div>`-nya.
  - Daftar kampus (`Admin\UniversityController@index`) kini `paginate(24)` (sebelumnya `get()` merender 535 kartu = 250.000 px di HP).
  - `components/searchable-select`: lencana singkatan dibatasi `max-w-[40%]`, tombol hapus pilihan 44 px di HP.
  - Halaman detail logbook admin tidak ditautkan dari daftar; diaudit lewat URL langsung. Halaman detail feedback belum teruji (tidak ada tiket di data demo) dan dicakup Tahap 8.
- [x] **Tahap 4 — Portal Supervisor / Atasan** (`supervisor/dashboard`, `applications`, `reports`, `certificates`) — **Selesai**
  - Halaman persetujuan di HP: tombol pintasan "Ke Panel Keputusan (Setujui / Tolak)" di atas karena panel keputusan berada di bagian bawah halaman; panel keputusan hanya `sticky` di `lg:`.
- [x] **Tahap 5 — Portal Mentor Lapangan** (`mentor/dashboard`, `logbooks`, `students`, `evaluations`)
- [x] **Tahap 6 — Portal Dosen / Pembimbing Kampus & Sekolah** (`lecturer/dashboard`, `monitoring`, `logbooks`, `evaluations`)
- [x] **Tahap 7 — Portal Admin Kampus / Sekolah** (`university/dashboard`, `lecturers`, `students`, `profile`)
- [x] **Tahap 8 — Modul Lintas Peran (Chat, Notifikasi, Profil Akun & Feedback)**
- [x] **Tahap 9 — Audit Holistik Akhir, Dokumen Cetak A4/Sertifikat & Regresi 9 Domain**
