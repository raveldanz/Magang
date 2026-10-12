# Direktori Skrip Otomasi & Audit (`scripts/`)

Direktori ini memuat seluruh skrip operasional resmi untuk pengujian antarmuka visual (E2E), verifikasi kesehatan sistem, runner browser tanpa modal dialog (YOLO Mode), serta orkestrasi multi-agen.

---

## 1. Skrip Pengujian Visual & Browser Runners (Playwright / Puppeteer)

Seluruh runner visual mengunci ke instalasi Google Chrome sistem lokal (`channel: 'chrome'`) untuk mematuhi aturan baku `AGENTS.md`.

| Nama Berkas | Perintah Eksekusi / NPM | Fungsi & Kegunaan |
| :--- | :--- | :--- |
| **`browser-runner.mjs`** | `node scripts/browser-runner.mjs --url /login --role admin --screenshot` | Runner browser mandiri (*YOLO mode*) untuk inspeksi visual dan pengambilan tangkapan layar cepat tanpa modal dialog. |
| **`visual-guard.mjs`** | Digunakan sebagai modul pendukung impor | Memastikan bundle Vite (`public/build/manifest.json`) dan CSS Tailwind ter-compile sebelum audit browser dimulai. |
| **`tier3_playwright_audit.mjs`** | `npm run test:visual` atau `npm run test:playwright` | Audit komprehensif Playwright seluruh rute utama, responsivitas tampilan, dan status HTTP 200. |
| **`tier1_puppeteer_audit.mjs`** | `npm run test:tier1` | Smoke test ringan berbasis Puppeteer Core untuk verifikasi cepat alur otentikasi. |
| **`hermes_all_roles_test.mjs`** | `npm run test:hermes` | Pengujian E2E terintegrasi mencakup seluruh 5 peran (Admin, Universitas, Dosen DPL, Mentor, Mahasiswa). |
| **`hermes_autopilot.mjs`** | `npm run autopilot` | Autopilot watcher untuk pengujian otomatis berkelanjutan saat ada perubahan kode. |
| **`hermes_live_browser.mjs`** | `npm run live` | Menjalankan browser interaktif live untuk memantau simulasi alur pengguna. |
| **`hermes_live_student_7days.mjs`** | `npm run student:7days` | Simulasi siklus 7 hari magang mahasiswa (pengisian logbook, upload laporan, penilaian). |

---

## 2. Skrip Presentasi & Tangkapan Layar E2E

| Nama Berkas | Bahasa | Fungsi & Kegunaan |
| :--- | :--- | :--- |
| **`generate_lecturer_test_ppt.py`** | Python (`python-pptx`) | Generator presentasi PowerPoint (.pptx) modern, widescreen 16:9, dengan navbar navigasi interaktif. |
| **`capture_focused_screenshots.mjs`** | Node.js | Pengambil tangkapan layar tingkat komponen (*component-level crop*) untuk bahan bukti laporan QA. |

---

## 3. Utilitas Sistem, Kredensial & Database

| Nama Berkas | Perintah Eksekusi | Fungsi & Kegunaan |
| :--- | :--- | :--- |
| **`health_check.php`** | `php scripts/health_check.php` | Memeriksa konektivitas PostgreSQL, symlink storage, dan integritas tabel. |
| **`dev_credentials.php`** | `php scripts/dev_credentials.php` | Menampilkan ringkasan akun pengujian untuk setiap peran (email & password). |
| **`migrate_and_seed.ps1`** | `.\scripts\migrate_and_seed.ps1` | Reset database, migrasi ulang, dan eksekusi seeder lengkap dalam satu perintah. |
| **`pre-commit-check.ps1`** | `.\scripts\pre-commit-check.ps1` | Pengecekan sintaksis (`php -l`), linting, dan test suite sebelum commit git. |

---

## 4. Orkestrasi Multi-Agen (Antigravity MCP)

| Nama Berkas | Platform | Fungsi & Kegunaan |
| :--- | :--- | :--- |
| **`mcp-agents-server.mjs`** | Node.js | Server stdio Model Context Protocol (MCP) untuk delegasi sub-agen. |
| **`start-agents.ps1`** | Windows PowerShell | Launcher orkestrasi swarm multi-agen di lingkungan Windows. |
| **`start-agents.sh`** | Linux / macOS Bash | Launcher orkestrasi swarm multi-agen di lingkungan Unix. |

---

## 5. Direktori Arsip (`scripts/archive/`)

Seluruh skrip *scratch*, skrip perbaikan skema sekali pakai (*one-off fixers*), serta skrip investigasi debugging dari riwayat insiden terdahulu disimpan di:
📁 **`scripts/archive/`**
