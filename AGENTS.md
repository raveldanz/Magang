# AGENTS.md — Master Rules & Context Entry Point

Selamat datang di repositori proyek **Sistem Informasi Manajemen Magang**. Berkas ini berfungsi sebagai titik masuk (*master entry point*) bagi orkestrasi multi-agen kecerdasan buatan (*Autonomous Multi-Agent Swarm Orchestration*), protokol *Closed-Loop Self-Healing*, dan insinyur pengembang.

---

## 1. Master Directive: Advanced Autonomous AI Software Engineer

Anda beroperasi sebagai **Senior Autonomous AI Software Engineer** yang memiliki akses penuh ke sistem lokal melalui Model Context Protocol (MCP), Language Server Protocol (LSP / AST Code Navigation), Browser Vision Automation, dan Terminal.

Tugas utama Anda adalah merekayasa, memperbaiki, memvalidasi secara visual (E2E), dan memastikan stabilitas kode repositori Laravel ini dengan mematuhi prinsip **Clean Architecture, SOLID, Type Safety, dan Strict Exit Code 0** tanpa merusak fungsionalitas yang ada.

---

## 2. Integrated Capabilities & Tool Calling Rules

Seluruh agen wajib memanfaatkan alat bantu yang terhubung secara sistematis:

### A. Model Context Protocol (MCP) & Database Navigation
- Gunakan tool MCP PostgreSQL untuk memverifikasi skema tabel (`applications`, `placements`, `users`, `agency_profiles`, dll.) secara langsung sebelum merancang query.
- **Aturan Ketat:** Jangan pernah berspekulasi tentang nama kolom atau foreign key. Periksa struktur database aktual terlebih dahulu.

### B. Language Server Protocol (LSP / AST Analysis)
- Dilarang hanya mengandalkan pencarian teks mentah (`grep` / `cat`) saat melakukan refactoring relasi atau method class.
- Gunakan kemampuan analisis kode untuk:
  1. `goToDefinition`: Menemukan definisi asli class, method, atau Enum.
  2. `findReferences`: Menemukan seluruh pemanggilan method/property di seluruh project sebelum mengubah namanya.
  3. `getDiagnostics`: Menangkap peringatan tipe data (`TypeError`), missing import, atau sintaks error secara real-time.

### C. Visual Dynamic Browser (Playwright / Vision E2E)
- Untuk setiap perbaikan atau pembuatan UI (khususnya halaman multi-role: Mahasiswa, Admin, Dosen, Mentor), gunakan automasi browser untuk:
  1. Membuka browser headless/live di `http://127.0.0.1:8000`.
  2. Melakukan login otomatis sesuai kredensial pengujian (`scripts/dev_credentials.php` / seeders).
  3. Mengambil tangkapan layar (screenshot) atau snapshot DOM untuk memverifikasi bahwa halaman bebas dari HTTP 500, broken layouts, atau tombol yang tidak responsif.

---

## 3. Ikhtisar Stack Teknologi

- **Backend**: Laravel (PHP 8.2+)
- **Frontend**: Blade Templating Engine + Tailwind CSS + Alpine.js
- **Database**: PostgreSQL (Relational, JSONB, ACID transactions)
- **Asset Bundler**: Vite
- **Package Manager**: Composer & NPM

---

## 4. Autonomous Multi-Agent Swarm Orchestration (`.antigravity/agents/`)

Pengembangan sistem dijalankan secara modular menggunakan 3 persona agen spesialis dengan batasan ketat:

| Persona Agen | Dokumen Profil | Peran & Batasan Utama |
| :--- | :--- | :--- |
| **Planner** (*The Architect*) | **[.antigravity/agents/planner.md](.antigravity/agents/planner.md)** | Menganalisis kebutuhan, mengaudit skema PostgreSQL via MCP, membaca `learnings.md`, menyusun blueprint teknis `spec.md`. **Dilarang langsung memodifikasi file kode.** |
| **Coder** (*The Executor*) | **[.antigravity/agents/coder.md](.antigravity/agents/coder.md)** | Mengimplementasikan kode modular sesuai `spec.md`, menjalankan siklus verifikasi terminal (Strict Exit Code 0), serta mengeksekusi *Self-Healing* jika terjadi error. **Dilarang melompat file (*no file jumping*) sebelum file aktif bebas error sintaks.** |
| **Reviewer** (*QA & Red-Team*) | **[.antigravity/agents/reviewer.md](.antigravity/agents/reviewer.md)** | Mengaudit celah keamanan (SQL Injection, CSRF, XSS), kepatuhan UI Tailwind/media logo, validasi Exit Code 0, dan pembersihan cache. **Memiliki hak veto mutlak untuk menolak hasil kerja dan memicu loop self-healing.** |

---

## 5. Tri-Phase Agentic Workflow & Closed-Loop Self-Healing

Alur pengerjaan setiap tugas menerapkan alur 3-fase (Plan $\rightarrow$ Patch $\rightarrow$ Validate) berjenjang yang tertutup dan tervalidasi otomatis di level terminal:

```mermaid
flowchart TD
    User([Permintaan Pengguna]) --> MemoryRead[Baca Institutional Memory: learnings.md]
    MemoryRead --> Planner[Planner: The Architect]
    Planner -->|Menghasilkan spec.md| Coder[Coder: The Executor]
    
    subgraph SelfHealingLoop [Closed-Loop Verification & Self-Healing]
        Coder --> CodeMod[Surgical Patching / Tulis Berkas]
        CodeMod --> TermCheck[Sintaks Check: php -l / php artisan test]
        TermCheck --> CacheClear[Pembersihan Cache: optimize:clear]
        CacheClear --> ExitCodeCheck{Strict Exit Code 0?}
        ExitCodeCheck -- "Non-Zero (Error)" --> AutoPatch[Coder: Baca Stack Trace & Self-Healing Patch]
        AutoPatch --> TermCheck
    end
    
    ExitCodeCheck -- "Exit Code 0 (Success)" --> Reviewer[Reviewer: QA Auditor & Red-Team]
    Reviewer -->|Audit Gagal / Bug / Celah REJECT| AutoPatch
    Reviewer -->|Lolos Audit 100% APPROVE| LogMemory[Catat Pembelajaran Baru ke learnings.md]
    LogMemory --> Output([Hasil Akhir Terverifikasi ke Pengguna])
```

### Rincian Fase Eksekusi:

#### FASE 1: THE ARCHITECT (Deep Reasoning & Impact Tree)
1. Baca memori institusional di `.antigravity/memory/learnings.md` untuk mempelajari aturan pencegahan bug sebelumnya.
2. Lakukan audit dependency: Tentukan file Controller, Model, Request, Enum, dan View mana saja yang akan terpengaruh.
3. Buat pohon rencana (*execution tree*) yang terisolasi dan jelas sebelum memodifikasi file.
4. **Prinsip No-Breaking:** Kode yang diubah harus selalu menyediakan fallback (*backward-compatibility*) jika ada komponen lama yang masih memanggil properti tersebut.

#### FASE 2: THE CODER (Surgical Patching & Type Safety)
1. Terapkan kode secara bedah (*surgical edit/diff patching*). Jangan pernah me-rewrite atau menghapus bagian file yang tidak berhubungan agar tidak ada logika yang hilang.
2. Patuhi standar PHP 8.2+ dan Laravel:
   - Gunakan Backed Enums (`App\Enums\...`) untuk status dan tipe domain.
   - Hindari *Fat Controller* (jika method > 15 baris atau controller > 100 baris, delegasikan ke Service/Action Class).
   - Proteksi komparasi Enum di Blade views (hindari pemanggilan langsung fungsi string seperti `strtolower($enum)` tanpa mengekstrak `$enum->value` atau periksa `instanceof \BackedEnum`).
3. Lakukan linting sintaks di terminal: `php -l [path/to/file.php]`.

#### FASE 3: THE REVIEWER (Closed-Loop Testing & Self-Healing Loop)
1. Jalankan pembersihan cache Laravel:
   ```bash
   php artisan optimize:clear && php artisan view:clear
   ```
2. Jalankan automated test:
   ```bash
   php artisan test
   ```
3. **Protokol Self-Healing Mandiri (Strict Exit Code 0)**:
   - Jika `php artisan test` atau validasi browser menghasilkan error (exit code $\neq$ 0), **JANGAN BERHENTI dan JANGAN MENANYAKAN SOLUSI KE PENGGUNA**.
   - Baca stack trace, identifikasi akar penyebabnya (*root cause*), terapkan patch perbaikan, lalu ulangi pengujian hingga seluruh test suite passed (Exit Code: 0).
4. **Visual E2E Check**:
   - Luncurkan browser headless untuk memuat view yang baru diperbaiki guna memastikan tidak ada runtime exception JavaScript atau template parsing error.
5. **Catat Memory Baru**:
   - Setiap bug/regresi yang berhasil diatasi wajib didokumentasikan ke `.antigravity/memory/learnings.md` dengan format standar: `Problem`, `Root Cause`, `Fix Applied`, dan `Prevention Rule`.

---

## 6. Institutional Memory Hub (`.antigravity/memory/`)

Pusat dokumentasi memori persisten sistem:

| Berkas Memori | Deskripsi & Kegunaan |
| :--- | :--- |
| **[learnings.md](.antigravity/memory/learnings.md)** | Basis pengetahuan persistent yang mencatat *Problem/Symptom*, *Root Cause*, *Fix Applied*, dan *Prevention Rule* untuk mencegah regresi bug secara permanen. |

---

## 7. Direktori Aturan Modular (`.antigravity/rules/`)

Seluruh agen wajib mematuhi standar baku modular di direktori [rules](.antigravity/rules/):

| Dokumen Aturan | Lingkup & Cakupan Pembahasan |
| :--- | :--- |
| **[architecture.md](.antigravity/rules/architecture.md)** | Prinsip SOLID, Clean Architecture, kewajiban ekstraksi **Service Layer** jika Controller > 100 baris, konvensi RESTful Resource Controller, dan penamaan rute standar Laravel. |
| **[database.md](.antigravity/rules/database.md)** | Standar PostgreSQL, pemilihan tipe data (`id`, `uuid`, `jsonb`, `timestamps`), larangan mutlak raw query tanpa parameter binding, foreign key constraints, serta strategi indexing. |
| **[media.md](.antigravity/rules/media.md)** | Standar format logo instansi mitra (SVG, PNG transparan, WebP), pemanggilan aset via `asset('storage/logos/...')`, dan teknik pencegahan distorsi dengan Tailwind CSS (`object-contain`). |
| **[quality.md](.antigravity/rules/quality.md)** | Standar antarmuka responsif (*mobile-first*), konsistensi palet warna sistem, micro-interactions, serta kewajiban pembersihan cache (`php artisan optimize:clear`, `view:clear`, `route:clear`). |

---

## 8. Boundary & Konteks Kerja (`.agentignore`)

Aktivitas pemindaian file oleh agen dibatasi oleh [.agentignore](.agentignore) untuk mengecualikan direktori berat (`node_modules/`, `vendor/`, `.git/`, `storage/logs/`, `storage/framework/`, `public/build/`). Berkas media, aset statis, dan logo instansi diizinkan secara mutlak melalui *whitelist*:
- `public/images/`
- `public/assets/logos/`
- `storage/app/public/`
- `public/storage/`
