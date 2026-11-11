# GEMINI.md — Konteks Proyek SIP-MAGANG (Antigravity)

Berkas ini melengkapi `AGENTS.md` dengan konteks domain, perintah kerja, gotcha teknis, dan konvensi Git spesifik repositori **SIP-MAGANG** agar otomatis termuat oleh Antigravity saat folder `Magang/` dibuka sebagai workspace.

---

## 1. Profil Sistem & Tech Stack

- **Sistem**: **SIP-MAGANG** — Sistem Informasi Manajemen Magang Pemerintah Kota Surabaya (Dinas/OPD ↔ Perguruan Tinggi ↔ Mahasiswa). Seluruh teks antarmuka, label, validasi, dan dokumen resmi menggunakan **Bahasa Indonesia**.
- **Branch Kerja Utama**: `AlurPengajuan-Admin-Yasin` (remote `raveldanz/Magang`).
- **Stack**:
  - **Backend**: Laravel 13 (`laravel/framework ^13.8`), PHP 8.3+, Breeze, Sanctum
  - **Database**: PostgreSQL (`DB_CONNECTION=pgsql`) di dev/prod; **SQLite `:memory:`** saat `php artisan test` (`phpunit.xml`)
  - **Frontend**: Blade + Tailwind CSS 3 + Alpine.js + Vite
  - **Dokumen & QR**: `barryvdh/laravel-dompdf` (surat/sertifikat PDF) & `simplesoftwareio/simple-qrcode` (QR lokal tanpa layanan eksternal)
  - **Testing & Gaya Kode**: PHPUnit 12 (tanpa Pest) & Laravel Pint

---

## 2. Perintah Harian & Akun Demo

```powershell
# Menyalakan PostgreSQL + Vite + Laravel server (http://127.0.0.1:8000)
.\start_dev.bat

# Alternatif runner serentak (serve + queue + schedule + pail + vite)
composer dev

# Kesehatan sistem, format kode, & pembersihan cache
php artisan app:health
vendor/bin/pint --test
php -l app/Http/Controllers/ContohController.php
php artisan optimize:clear; php artisan view:clear

# Pengujian otomatis (wajib Strict Exit Code 0)
php artisan test
php artisan test --filter=NamaTestAtauMethod
```

**Akun Demo** (password: `password`):
`admin@surabaya.go.id` (Super Admin), `admin.dispusip@surabaya.go.id` (Admin Dinas), `supervisor@dispusip.surabaya.go.id` (Supervisor), `mentor.dispusip@surabaya.go.id` (Mentor), `admin@unesa.ac.id` (Universitas), `dosen.unesa@unesa.ac.id` (Dosen), `mahasiswa.aktif@unesa.ac.id` / `mahasiswa.lulus@unesa.ac.id` (Mahasiswa).

---

## 3. Arsitektur Domain & Alur Bisnis

1. **Peran & Otorisasi (`App\Http\Middleware\CheckRole` / alias `role`)**:
   - Nilai `users.role`: `super_admin`, `admin` (Admin Dinas, di-scope `agency_profile_id`), `mentor` / `pembimbing`, `dosen` / `academic_advisor`, `universitas` (di-scope `university_id`), `mahasiswa`.
   - User ber-role `super_admin` atau `admin` dengan `agency_profile_id = null` lolos gate `role:admin` maupun `role:super_admin`.
   - Fitur *Login As* (`Admin\ImpersonationController`) menyimpan `impersonator_id` di session; endpoint tulis tertentu diblokir oleh `EnsureNotImpersonating`.
2. **Siklus Magang & Penilaian**:
   - `Application` (pengajuan ke `Unit` milik `AgencyProfile`) → saat diterima memiliki satu `Placement` yang menghubungkan pembimbing lapangan (`mentor`/`pembimbing`) dan DPL (`academicAdvisor`/`dosen`), beserta `logbooks`, `finalreport`, `evaluation`, dan sertifikat (`certificate_hash`).
   - `Application.status` di-cast ke `App\Enums\ApplicationStatus` (`pending → verified → accepted → active → completed`, serta `rejected`, `resigned`). Review logbook/laporan menggunakan `App\Enums\ReviewStatus` (`pending`, `approved`, `rejected`, `revision`).
   - Transisi otomatis: `accepted → active` via `app:sync-internship-status` (harian); `active → completed` via `Placement::syncCompletionStatus()`.
   - Nilai akhir di `Evaluation`: 40% pembimbing lapangan + 60% DPL (kebijakan komponen evaluasi kampus di tabel `universities`).
   - Verifikasi publik tanpa login: `/verify-letter/{token}` (`applications.letter_token`) dan `/verify-certificate/{token}` (`placements.certificate_hash`).
3. **Modul Chat (`/chat`, `App\Services\Chat\*`)**:
   - Tipe percakapan: `direct`, `group` (dibuat staf), dan `placement` (Grup Bimbingan otomatis dari `PlacementChatObserver`, sinkronisasi lewat `php artisan chat:sync-placement-groups`).
   - Izin mulai chat/tambah anggota diatur terpusat di `ChatContactDirectory`; visibilitas detail pribadi mengikuti `ChatContactDirectory::personalDetailsVisibleTo()`. Lampiran chat (maks. 5 berkas) disimpan di disk privat `local` dan disajikan lewat route `chat.attachment`.

---

## 4. Gotchas & Pencegahan Regresi Wajib

- **LRN-022 (BackedEnum vs String)**: Status dapat berupa objek Enum atau string mentah. Selalu normalisasi dengan `$status instanceof \BackedEnum ? $status->value : $status`; jangan panggil `strtolower()` langsung pada objek Enum.
- **Migration Dual-Driver (PostgreSQL + SQLite)**: Seluruh migration wajib berjalan di PostgreSQL **dan** SQLite `:memory:`. Bungkus DDL khusus PostgreSQL (mis. `ALTER TABLE ... DROP CONSTRAINT`) dengan pengecekan `DB::getDriverName() === 'pgsql'` atau `try/catch`.
- **LRN-020 (`@json` di Blade)**: Jangan melempar model Eloquent utuh ke `@json(...)` karena memicu kehabisan memori; petakan ke array spesifik terlebih dahulu.
- **LRN-040 (Alpine `:style` + `x-show`)**: Gunakan bentuk objek `:style="{ ... }"` saat elemen Alpine juga memakai `x-show`.
- **Keamanan Berkas Pribadi vs Logo Publik**: Dokumen pengajuan, lampiran logbook/chat/feedback, dan foto mahasiswa berada di disk privat `local` (sajikan hanya lewat route terotorisasi seperti `documents.application*`, `logbooks.attachment`, `student.photo`, `feedbacks.attachment` — **jangan** gunakan `asset('storage/...')`). Logo instansi publik memakai PNG transparan murni/SVG/WebP dengan `object-contain`.
- **Resolusi Kampus**: Gunakan `App\Services\UniversityResolver`, jangan query `LIKE '%nama%'`.
- **LRN-130 (UI Kedinasan & Dokumen Resmi)**: Gunakan ikon vektor SVG profesional (Heroicons) alih-alih emoji mentah pada antarmuka kedinasan, dan pastikan `Carbon::setLocale('id')` aktif pada rendering surat/sertifikat resmi.
- **Partial Blade**: Variabel lokal yang didefinisikan di dalam satu `@include` partial tidak terlihat oleh partial berikutnya.

---

## 5. Konvensi Git & Tanpa Atribusi AI

- **Tanpa Atribusi AI di GitHub**: Jangan pernah menulis `Co-Authored-By: Claude ...`, `Generated with Claude Code`, atau atribusi AI apa pun pada pesan commit, deskripsi PR, maupun komentar issue/PR agar riwayat kontributor murni atas nama pemilik repositori.
- **Commit Multi-Baris di PowerShell**: Tulis pesan commit ke berkas sementara terlebih dahulu lalu jalankan `git commit -F <file>` (dan gunakan `--body-file` untuk deskripsi/komentar PR).
- **Definisi Selesai**: Baca `.antigravity/memory/learnings.md` sebelum perubahan non-trivial, pastikan `php -l` pada berkas yang diubah, `php artisan optimize:clear`, dan `php artisan test` seluruhnya menghasilkan **Exit Code 0**, serta catat perbaikan bug penting baru dengan format `[LRN-XXX]`.

