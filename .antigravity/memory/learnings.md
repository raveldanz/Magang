# Institutional Memory & Continuous Evolution Hub (`learnings.md`)

Berkas ini berfungsi sebagai **pusat memori kelembagaan (*institutional memory hub*)** persisten bagi seluruh persona agen otonom (Planner, Coder, Reviewer). Setiap kendala sintaksis, runtime exception, kegagalan query PostgreSQL, kegagalan migrasi, atau distorsi tampilan Blade yang berhasil dipecahkan melalui protokol *Self-Healing* **wajib dicatat di sini** guna mencegah pengulangan pola kesalahan di masa depan.

---

## 1. Aturan Akses Agen
- **Planner**: Wajib membaca berkas ini pada tahap awal perencanaan sebelum menyusun `spec.md` untuk menghindari arsitektur yang rentan error serupa.
- **Coder**: Wajib membaca berkas ini sebelum mengimplementasikan kode dan wajib mencatat entri baru setelah menyelesaikan insiden *self-healing*.
- **Reviewer**: Wajib memverifikasi bahwa solusi yang diterapkan oleh Coder tidak mengabaikan aturan pencegahan (*prevention rule*) yang telah didokumentasikan di sini.

---

## 2. Tabel Indeks Masalah & Rekayasa Perbaikan

| ID | Tanggal | Modul / Komponen | Gejala Masalah Singkat | Status |
| :---: | :---: | :--- | :--- | :---: |
| **LRN-001** | 2026-09-03 | Media / Storage Link | Aset logo instansi 404 saat dipanggil via `asset('storage/...')` | RESOLVED |
| **LRN-002** | 2026-09-03 | Database / PostgreSQL | Raw query unparameterized rentan error parsing tipe data & SQLi | RESOLVED |
| **LRN-003** | 2026-09-09 | Master Instansi & Logo Fallback | Logo instansi salah tebak (Kominfo) saat logo kosong & ketiadaan alur akun admin dinas | RESOLVED |
| **LRN-004** | 2026-09-09 | Storage & Laporan Akhir | Error 403 saat buka laporan (`default.pdf`) & nama file acak saat diunduh | RESOLVED |
| **LRN-005** | 2026-09-09 | Seleksi, Kuota Unit & Keamanan Verifikasi | Double-decrement kuota unit kerja & IDOR pada QR verifikasi surat/sertifikat | RESOLVED |
| **LRN-006** | 2026-09-10 | Auth Testing & Navigasi Blade View | Kegagalan assertion redirect registrasi & tombol kembali (back button) kosong | RESOLVED |
| **LRN-007** | 2026-09-10 | Verifikasi Publik & Hermes E2E | Verifikasi QR surat penerimaan berstatus lulus/selesai (completed) | RESOLVED |
| **LRN-008** | 2026-09-10 | Live Upload Preview & Layout Scripts | Logo box tidak berubah saat upload file & ketiadaan @stack('scripts') di app.blade.php | RESOLVED |
| **LRN-009** | 2026-09-10 | Mobile-First UI & Android Ergonomics | Tampilan mobile berantakan, horizontal table scroll menyulitkan, dan tombol aksi terpotong | RESOLVED |
| **LRN-010** | 2026-09-10 | Double-Confirmation Deletion & Cascade Clean | Tombol hapus universitas/instansi tertolak akun admin otomatis & ketiadaan modal konfirmasi ganda | RESOLVED |
| **LRN-011** | 2026-09-13 | Super Admin Agency Hub & Workflow | Tombol terbatas 'Lihat Unit ->', ketiadaan 'Lihat Akun', dan ketiadaan Pusat Kendali Alur Dinas | RESOLVED |
| **LRN-012** | 2026-09-13 | User Management, Double Confirmation & Bulk Actions | Tombol reset & hapus tidak merespon (Alpine x-data scope), ketiadaan konfirmasi ganda & fitur pilih banyak dengan tripel konfirmasi | RESOLVED |
| **LRN-013** | 2026-09-14 | Storage Junction, Migrations & ID Collision | Lampiran 404, migrasi token sertifikat tertunda, tabrakan ID multi-tenant, dan surat kelulusan | RESOLVED |
| **LRN-014** | 2026-09-14 | Integrity Guard: Duplicate Application & Adaptive Certificate | Celah pengajuan ganda menimpa penempatan aktif, query final report rentan, dan sertifikat prematur | RESOLVED |
| **LRN-015** | 2026-09-15 | UI Hardening & Mobile-Friendly E2E Multi-Role | Tumpukan tombol ganda kartu kampus, tab bar melipat di layar HP, dan otomasi E2E lintas role mobile/desktop | RESOLVED |
| **LRN-016** | 2026-09-17 | UI Alignment & Card Layout Standardization | Posisi vertikal angka metrik kartu tidak rata akibat variasi panjang teks judul & alamat | RESOLVED |
| **LRN-017** | 2026-09-17 | Lifecycle Status Integration & Re-Application Loop | Status mahasiswa mengundurkan diri (resigned) keliru tampil 'DALAM PROSES' di dashboard | RESOLVED |
| **LRN-018** | 2026-09-23 | Graduation Verification & Evaluation Precondition | Label 'Aksi Kelulusan' muncul prematur saat nilai evaluasi magang belum diinput | RESOLVED |
| **LRN-019** | 2026-09-23 | Laporan Akhir & Lightbox Logbook | Pratinjau berkas live, lightbox modal, checklist 3-sisi & eliminasi orphan view | RESOLVED |
| **LRN-020** | 2026-09-23 | DPL Logbook & Serialization Memory Leak | Memory limit 512MB exhausted saat json_encode Eloquent model dengan recursive accessor | RESOLVED |
| **LRN-021** | 2026-09-24 | Status Pipeline & Architecture Standardization | Fragmentasi dualisme status virtual RAM, zombie placement status & filtering RAM collection | RESOLVED |
| **LRN-022** | 2026-09-24 | BackedEnum Type-Safety & View Hardening | TypeError strtolower()/strtoupper() saat menerima enum ApplicationStatus di Blade & Services | RESOLVED |
| **LRN-023** | 2026-09-24 | Admin Selection Reactivity & Offline TTE Letter | Konflik kelas Tailwind hidden dengan Alpine x-show, QR TTE rusak (eksternal API) & nama seeder | RESOLVED |
| **LRN-024** | 2026-09-25 | Mobile-First Responsive Tables & Dashboard Card Hardening | Tabel data dashboard terpotong di layar HP (< 640px) pada peran Mentor & Dosen, serta clipping teks kartu distribusi | RESOLVED |
| **LRN-025** | 2026-09-25 | Native MCP Sub-Agents & Stdio Bridge | Mock ANTHROPIC_API_KEY menimpa sesi Claude, timeout Ollama CPU inference, dan lifecycle handshake MCP | RESOLVED |
| **LRN-026** | 2026-09-28 | Lifecycle Pipeline, Quota Harmony & Logbook Destroy | Inkonsistensi status 'active' pada DPL/Admin/Mentor, disparitas kuota Unit vs Dinas, 403 sertifikat admin & missing method destroy logbook | RESOLVED |
| **LRN-027** | 2026-09-28 | Student Portal Layout Standardization | Disparitas lebar container halaman (max-w-4xl vs max-w-7xl) antara Profil, Laporan Akhir, dan Logbook Mahasiswa | RESOLVED |
| **LRN-028** | 2026-09-26 | Testing Architecture & Visual QA Guard | Evaluasi buta meloloskan unstyled HTML (Times New Roman & default grey button) akibat stale public/hot | RESOLVED |
| **LRN-029** | 2026-09-26 | Seleksi Pengajuan Admin & State Validation | Validasi state ketat alur seleksi (hanya pending yang dapat diubah) & kewajiban alasan penolakan | RESOLVED |
| **LRN-030** | 2026-09-28 | Card Action Button Harmony & Single-Line Layout | Mismatch styling tombol 'Buat Akun' (border hitam gelap) vs 'Login As', teks terlipat 2 baris (w-1/3) pada card Universitas & Dinas | RESOLVED |
| **LRN-031** | 2026-09-28 | Table Action Button Sizing & Visual Contrast | Tombol aksi tabel terlalu kecil (h-[30px]) dan kurang kontras; standardisasi ke h-[34px] px-3.5 dengan border crisp dan shadow | RESOLVED |
| **LRN-032** | 2026-09-28 | Agency Control Center Visual Parity & Clean Badging | Desain show dinas kurang selaras dengan univ; kontak terhimpit, dan badge/tab memuat angka redundan (Akun Terdaftar (3) & Personil 7) | RESOLVED |
| **LRN-033** | 2026-09-28 | Browser Engine, Driver 404 & Local Chrome Lockdown | Driver Playwright internal 1.57.0 404 CDN di IDE; penguncian ke channel 'chrome' lokal & protokol Mata Manusia | RESOLVED |
| **LRN-039** | 2026-09-30 | Fitur Chat & Error JSON Endpoint Web | Validasi endpoint fetch `/chat/api/*` membalas redirect 302 (bukan 422 JSON) karena `shouldRenderJsonWhen` hanya `api/*` | RESOLVED |
| **LRN-040** | 2026-09-30 | Chat Tahap Lengkap: Alpine `:style` vs `x-show` & Vite Dev Basi | Nama pengirim tampil di gelembung sendiri (`:style` string menimpa `display:none` dari `x-show`); Vite dev server menyajikan modul lama/terhapus | RESOLVED |
| **LRN-041** | 2026-09-30 | Info Kontak Chat, Privasi Data Pribadi & Notifikasi | Telepon dosen dari form admin terbuang (kolom `users.phone` tidak ada); dropdown notifikasi chat terpotong; penanda toast tercampur antar-akun | RESOLVED |

---

## 3. Catatan Masalah & Solusi (Knowledge Base)

### [LRN-001] Penanganan Tautan Simbolik Aset Logo Instansi (404 Not Found)
- **Tanggal**: 2026-09-03
- **Komponen**: Blade View (`resources/views/`) & Storage (`public/storage/`)
- **Problem / Symptom**: Logo instansi mitra magang tidak muncul (HTTP 404) di browser saat dipanggil menggunakan `asset('storage/logos/...')`.
- **Root Cause**: Symlink `public/storage` belum terhubung ke direktori fisik `storage/app/public` di sistem operasi Windows.
- **Fix Applied**: 
  - Mengeksekusi perintah Artisan: `php artisan storage:link`.
  - Menambahkan perlindungan pengecekan berkas dan fallback logo default di Blade jika berkas tidak ditemukan secara fisik.
- **Prevention Rule**: Pastikan symlink storage selalu diverifikasi aktif pada environment lokal/server dan sediakan selalu fallback image default.

---

### [LRN-002] Pencegahan SQL Injection & Type Safety pada PostgreSQL
- **Tanggal**: 2026-09-03
- **Komponen**: Eloquent ORM & Query Builder (`app/Services/`, `app/Models/`)
- **Problem / Symptom**: Kegagalan eksekusi query PostgreSQL saat melakukan filter dinamis status atau ID bertipe UUID/integer karena isu *casting* atau risiko injeksi.
- **Root Cause**: Penggunaan interpolasi string langsung di dalam `DB::raw()` tanpa parameter binding.
- **Fix Applied**: Mengubah seluruh pemanggilan query ke Eloquent model atau query builder dengan array parameter binding eksplisit (`['status' => $status]`).
- **Prevention Rule**: Larangan mutlak raw query tanpa *parameter binding*. Setiap kolom dinamis harus melalui validasi Form Request sebelum dieksekusi ke database.

---

### [LRN-003] Standardisasi Fallback Logo Default & Notifikasi Akun Admin Instansi Baru
- **Tanggal**: 2026-09-09
- **Komponen**: `AgencyController`, `DashboardController`, `NotificationService`, Blade Views (`admin/agencies/index.blade.php`, `admin/agencies/edit.blade.php`, `admin/dashboard.blade.php`)
- **Problem / Symptom**: 
  1. Instansi baru yang dibuat tanpa upload logo (seperti "Dinas Komunikasi Dan ikan") keliru menampilkan logo Diskominfo karena substring matching `str_contains($aName, 'komunikasi')`.
  2. Instansi baru tidak memiliki akun admin dinas dan sistem belum menyediakan notifikasi peringatan serta tombol pembuatan akun instan bagi Super Admin.
- **Root Cause**: Adanya logika heuristik pencocokan kata kunci nama pada view Blade jika logo bernilai null, serta belum adanya alur provisi akun `admin` terintegrasi untuk instansi sebagaimana alur pada universitas.
- **Fix Applied**: 
  - Menghapus pencocokan heuristik kata kunci nama instansi. Jika kolom `logo` kosong/null, langsung gunakan `asset('images/default-agency.svg')`.
  - Mengimplementasikan alur notifikasi terpadu (Banner Super Admin di Dashboard, Warning Banner di Master Instansi, dan notifikasi `/admin/notifications`).
  - Menambahkan method `createAccount` di `AgencyController`, badge `Belum Ada Akun` vs `Akun Aktif`, tombol `Buat Akun`, dan flash alert kredensial interaktif dengan tombol salin (copy to clipboard).
- **Prevention Rule**: Jangan pernah menggunakan heuristik string matching untuk menebak logo institusi dari input dinamis pengguna; selalu gunakan aset SVG default resmi (`default-agency.svg` / `default-university.svg`). Seluruh entitas kelembagaan baru wajib memiliki monitoring status akun admin terdaftar.

---

### [LRN-004] Penanganan Storage Serve Signature Conflict (403) & Standardisasi Nama Berkas Laporan Akhir
- **Tanggal**: 2026-09-09
- **Komponen**: `config/filesystems.php`, `FinalReportController`, `routes/web.php`, Blade Views (`student/final_report.blade.php`, `admin/applications/show.blade.php`, `lecturer/student-detail.blade.php`, `mentor/student-detail.blade.php`, `pembimbing/student-detail.blade.php`, `university/students/show.blade.php`)
- **Problem / Symptom**: 
  1. Berkas laporan akhir yang diunggah mahasiswa tersimpan dengan string hash acak (`YnfvRs5RU6vhbN9e7hP6syytMsK8FLotdLG4C5b9.docx`) dan saat diunduh oleh dosen/admin namanya tetap acak sehingga menyulitkan identifikasi.
  2. Saat Super Admin atau pemangku kepentingan menekan tombol "Buka Laporan Akhir" pada mahasiswa yang laporannya disetujui DPL namun belum memiliki naskah fisik (`default.pdf`), muncul tampilan error "Akses Dibatasi (403) Anda Tidak Memiliki Hak Akses".
- **Root Cause**:
  1. `FinalReportController::store()` menggunakan `$request->file('file_laporan')->store('final_reports', 'public')` yang secara default menghasilkan nama hash acak 40 karakter.
  2. Tautan di view mengarah langsung ke aset publik mentah `asset('storage/' . $filePath)`. Ketika file fisik tidak ada (`default.pdf`), permintaan dialihkan ke web server Laravel. Di Laravel 11, opsi `'serve' => true` pada disk `'local'` di `config/filesystems.php` mengintersepsi rute `/storage/...` untuk validasi signed URL, yang kemudian menggugurkan request dengan `abort(403)`.
- **Fix Applied**: 
  1. Mengubah `'serve' => false` pada disk `'local'` di `config/filesystems.php`.
  2. Mengenerate dokumen template PDF fisik resmi di `storage/app/public/final_reports/default.pdf` dan `public/storage/final_reports/default.pdf` (serta `sample_laporan_akhir.pdf` dan `test_report.pdf`).
  3. Memperbarui `FinalReportController::store()` agar menyimpan file dengan format baku: `Laporan_Akhir_[NIM]_[Nama]_[Timestamp].[ext]`.
  4. Menyediakan rute terpusat `Route::get('/final-reports/{id}/file', [FinalReportController::class, 'showFile'])->name('final_reports.show')` yang:
     - Melakukan validasi otorisasi multi-role (Super Admin, Admin Dinas, DPL, Mentor Dinas, Admin Univ, Mahasiswa pemilik).
     - Mengirimkan header `Content-Disposition: inline/attachment; filename="Laporan_Akhir_[NIM]_[Nama].[ext]"` sehingga unduhan selalu berformat rapi di browser.
     - Menyediakan fallback aman ke `default.pdf` jika file fisik naskah belum lengkap.
  5. Menambahkan kartu himbauan tata nama file baku di halaman unggah mahasiswa `student/final_report.blade.php`.
- **Prevention Rule**: Seluruh dokumen penting yang diunduh pengguna wajib dilayani melalui dedicated controller action dengan header `Content-Disposition` terformat jelas (bukan raw storage url acak). Jangan mengaktifkan `'serve' => true` pada disk `'local'` jika berpotensi konflik dengan URL storage publik.

---

### [LRN-005] Proteksi Kuota Unit Kerja Seleksi & Keamanan Token Verifikasi Dokumen Publik (Anti-IDOR)
- **Tanggal**: 2026-09-09
- **Komponen**: `Admin\ApplicationController`, `Student\ApplicationController`, `Student\CertificateController`, `database/migrations/`, `routes/web.php`, Blade Views (`letters/acceptance.blade.php`, `certificates/internship_certificate.blade.php`, `verify_letter.blade.php`, `verify_certificate.blade.php`)
- **Problem / Symptom**:
  1. Kuota unit kerja dihitung berkurang dua kali lipat saat seleksi diterima karena pemanggilan `$unit->decrement('quota')`, padahal accessor `remaining_quota` sudah menghitung selisih dinamis terhadap status `accepted`. Admin juga dapat menerima pelamar melampaui kuota jika kuota sudah 0.
  2. URL QR code pada surat penerimaan dan sertifikat magang sebelumnya menggunakan auto-increment ID (`/verify-letter/{id}` dan `/verify-certificate/{id}`) sehingga rentan serangan IDOR (*Insecure Direct Object References*) dan enumerasi dokumen oleh pihak luar.
- **Root Cause**:
  1. Adanya ketidaksinkronan antara kolom statis kapasitas unit (`quota`) dengan accessor dinamis `getRemainingQuotaAttribute()`.
  2. Ketiadaan kolom token keamanan acak (*cryptographically secure random token*) pada tabel `applications` dan `placements`.
- **Fix Applied**:
  1. Menghilangkan mutasi decrement/increment langsung pada kolom total kapasitas `$unit->quota` dan menambahkan pengecekan `if ($unit->remaining_quota <= 0)` dengan pesan penolakan yang tegas bagi Admin.
  2. Membuat migrasi database penambahan kolom `letter_token` (VARCHAR 64, unique, indexed) pada tabel `applications` dan `certificate_number` & `certificate_hash` (VARCHAR 64, unique, indexed) pada tabel `placements`.
  3. Memperbarui rute publik `verify.letter` dan `verify.certificate` agar mencari berdasarkan token/hash acak (dengan fallback backward-compatible ke ID).
  4. Mengintegrasikan QR Code resmi berstandar kedinasan pada surat penerimaan dan halaman 1 sertifikat magang.
- **Prevention Rule**:
  1. Jangan pernah memutasi kolom kapasitas dasar database jika sudah memiliki accessor dinamis yang menghitung kapasitas tersisa dari relasi anak.
  2. Seluruh dokumen publik atau QR code yang dapat diakses oleh publik tanpa login wajib menggunakan token unik tak tertebak (*unguessable random token* / hash), bukan sequential integer ID.

---

### [LRN-006] Sinkronisasi Alur Registrasi pada Feature Test & Pemulihan Ikon Navigasi Blade
- **Tanggal**: 2026-09-10
- **Komponen**: `tests/Feature/Auth/RegistrationTest.php`, Blade Views (`student/final_report.blade.php`, `admin/agencies/`, `admin/mentors/`, `admin/universities/`, `admin/users/`)
- **Problem / Symptom**: 
  1. Pengujian otomatis `php artisan test` gagal pada `RegistrationTest::test_new_users_can_register` dengan status error perbandingan redirect URL.
  2. Tombol kembali (*back button*) pada header formulir admin (Agencies, Mentors, Universities, Users) dan pengunggahan laporan akhir mahasiswa tampil berupa kotak kosong tanpa ikon navigasi.
- **Root Cause**: 
  1. `RegistrationTest` masih mengasumsikan alur default Laravel Breeze yang me-redirect ke `/dashboard`, sementara alur spesifik sistem telah diatur untuk langsung mengarahkan mahasiswa baru ke halaman pengisian profil `/student/profile`.
  2. Elemen `<svg>` penunjuk arah kembali (`d="M10 19l-7-7m0 0l7-7m-7 7h18"`) terhapus dari tag tautan `<a ...>`.
- **Fix Applied**: 
  1. Menyelaraskan assertion pada `RegistrationTest.php` menjadi `$response->assertRedirect(route('student.profile.edit', absolute: false))` sehingga 25/25 pengujian berstatus 100% PASS (Strict Exit Code 0).
  2. Mengembalikan ikon SVG navigasi kembali ke dalam seluruh tombol header yang terdampak serta mempercantik status badge & banner pada `final_report.blade.php`.
- **Prevention Rule**: Setiap perubahan pada alur redirect controller otentikasi wajib diiringi pembaruan pada unit/feature test terkait. Hindari tag tautan kosong pada template Blade dengan selalu memastikan elemen ikon/teks pembantu terpasang.

---

### [LRN-007] Verifikasi QR Code Surat Balasan Status Selesai (Completed) & Pengujian Hermes Multi-Role Swarm
- **Tanggal**: 2026-09-10
- **Komponen**: `routes/web.php` (`/verify-letter/{token}`), `scripts/hermes_all_roles_test.mjs`
- **Problem / Symptom**: 
  1. Halaman publik verifikasi QR code surat balasan/penerimaan magang (`/verify-letter/{token}`) mengembalikan error 404 (Not Found) jika mahasiswa terkait telah menyelesaikan magang (berstatus `completed`).
  2. Belum tersedianya runner pengujian sintetik otomatis yang mencakup 6 peran sekaligus (Super Admin, Admin Dinas, Mahasiswa, Mentor, DPL, Universitas) beserta keterhubungannya.
- **Root Cause**: Query builder pada rute `verify.letter` sebelumnya dibatasi secara kaku hanya dengan `->where('status', 'accepted')`, sehingga mahasiswa yang telah berstatus `completed` (lulus magang) surat penerimaannya dianggap tidak valid/tidak ditemukan.
- **Fix Applied**: 
  1. Mengubah query builder rute menjadi `->whereIn('status', ['accepted', 'completed'])` sehingga surat penerimaan tetap sah diverifikasi secara publik sepanjang masa.
  2. Mengembangkan test suite mandiri `scripts/hermes_all_roles_test.mjs` (`npm run test:hermes`) yang menguji 56 skenario multi-role, siklus delegasi logbook, penilaian, unduh berkas laporan, impersonasi, dan token publik dengan hasil **56/56 PASS (100% HIJAU)**.
- **Prevention Rule**: Seluruh dokumen hukum/arsip resmi (surat penerimaan, sertifikat, transkrip) tidak boleh dibatasi hanya pada status interim/aktif jika status akhir entitas (`completed`) tetap memerlukan validitas pembuktian dokumen.

### [LRN-008] Live Pratinjau Upload Logo Instansi & Penyisipan Direktif @stack('scripts') pada Layout Utama
- **Tanggal**: 2026-09-10
- **Komponen**: `resources/views/layouts/app.blade.php`, `resources/views/admin/universities/`, `resources/views/admin/agencies/`, `resources/views/admin/agency_profile/`, `resources/views/university/profile/`, `resources/views/student/logbook/`, `resources/views/student/final_report.blade.php`, `resources/views/feedbacks/create.blade.php`, `app/Http/Controllers/Admin/AgencyProfileController.php`
- **Problem / Symptom**: 
  1. Saat mengunggah logo instansi dinas atau universitas baru/edit, box gambar pratinjau di samping kiri tetap menampilkan logo default (topi toga / gedung) dan hanya teks native nama file browser yang berubah.
  2. Direktif `@push('scripts')` pada view anak tidak ter-render sama sekali pada HTML output.
  3. Validasi mimes logo pada `AgencyProfileController` tertinggal format ekstensi modern `.webp`.
- **Root Cause**: 
  1. Tag `<img>` pratinjau bersifat statis tanpa event listener JavaScript, sehingga browser tidak mengupdate atribut `src` saat berkas dipilih pengguna.
  2. Berkas layout utama `resources/views/layouts/app.blade.php` belum memuat direktif `@stack('scripts')` sebelum penutup `</body>`.
  3. Rule validasi logo di `AgencyProfileController` hanya mengizinkan `jpeg,png,jpg,svg` tanpa `webp`.
- **Fix Applied**: 
  1. Memasang `@stack('scripts')` tepat sebelum penutup tag `</body>` di `resources/views/layouts/app.blade.php`.
  2. Mengimplementasikan fungsi modular `handleLogoPreview(input, previewId, defaultSrc)` berbasis `FileReader.readAsDataURL` dengan validasi client-side tipe gambar dan batas ukuran 2MB pada seluruh formulir logo:
     - `admin/universities/create.blade.php` & `admin/universities/edit.blade.php`
     - `admin/agencies/create.blade.php` & `admin/agencies/edit.blade.php`
     - `admin/agency_profile/edit.blade.php`
     - `university/profile/index.blade.php`
  3. Memperkaya feedback live interaktif saat pengguna memilih berkas di peran lain:
     - `student/logbook/create.blade.php` & `student/logbook/edit.blade.php` (thumbnail preview untuk foto kegiatan, badge nama & ukuran berkas).
     - `student/final_report.blade.php` (kartu nama & ukuran berkas naskah laporan akhir).
     - `feedbacks/create.blade.php` (thumbnail pratinjau screenshot bukti bug & ukuran).
  4. Menyelaraskan rule validasi `webp` di `AgencyProfileController`.
- **Prevention Rule**: Seluruh layout induk Blade yang membungkus antarmuka aplikasi (`layouts/app.blade.php`) wajib menyertakan `@stack('scripts')` dan `@stack('styles')`. Setiap input file gambar dengan box pratinjau wajib memiliki event `onchange` yang memperbarui `src` secara live via JavaScript, dengan atribut CSS `object-contain` untuk menjaga proporsi logo.

---

### [LRN-009] Optimasi UI/UX Mobile-First Khusus Pengguna Smartphone Android
- **Tanggal**: 2026-09-10
- **Komponen**: `resources/views/layouts/navigation.blade.php`, `resources/views/admin/applications/index.blade.php`, `resources/views/student/logbook/`, `resources/views/student/application/`, `resources/views/student/final_report.blade.php`, `resources/views/admin/universities/`, `resources/views/admin/agencies/`
- **Problem / Symptom**: 
  1. Pada viewport smartphone (390x844), tata letak antarmuka hanya responsif pasif (CSS collapse biasa): tombol aksi sejajar horizontal di kanan bawah (`justify-end`) terpotong atau terlalu sempit untuk ditekan dengan jempol pengguna HP Android.
  2. Tabel data dengan banyak kolom (seperti pengajuan magang dan logbook) memicu scrolling horizontal yang canggung dan menyulitkan melihat aksi/status.
  3. Upload logo terhimpit secara vertikal dan tombol menu mobile drawer belum memiliki ikon pendukung sehingga terkesan hambar dan sulit dibaca secara cepat.
- **Root Cause**: Desain antarmuka awal berorientasi desktop-first tanpa varian kartu data khusus mobile (`stacked cards`) dan tanpa standarisasi touch target minimum (44-48px).
- **Fix Applied**: 
  1. **Dual-View Hybrid Pattern**: Menyembunyikan tabel lebar di layar kecil (`hidden md:block`) dan menyajikan kartu bertumpuk (`md:hidden space-y-3`) pada Pengajuan Magang Admin dan Logbook Mahasiswa lengkap dengan badge status dan tombol aksi lebar.
  2. **Ergonomic Action Buttons**: Mengubah grup tombol formulir menjadi `flex flex-col-reverse sm:flex-row items-stretch sm:items-center sm:justify-end gap-2.5 sm:gap-3` dengan tombol primary full-width di atas dan tombol Batal/Kembali di bawah pada mobile.
  3. **App-Like Mobile Navigation**: Memperbarui menu drawer navigasi mobile dengan kartu profil pengguna ber-watermark lambang Pemkot, indikator status online, dan tombol menu ber-ikon SVG dalam squircle container dengan touch target nyaman `min-h-[44px]`.
  4. **Responsive Upload Cards**: Mengubah pratinjau upload logo menjadi kartu vertikal/horizontal responsif (`flex-col sm:flex-row`) yang rapi pada smartphone.
- **Prevention Rule**: Setiap antarmuka formulir dan tabel data wajib mengadopsi standar mobile-first: tabel >4 kolom wajib memiliki varian mobile cards (`md:hidden`), seluruh tombol submit/batal formulir wajib full-width pada mobile (<640px) dengan tinggi minimal 44px, dan navigasi drawer mobile wajib dilengkapi ikon tematik yang jelas.

---

### [LRN-010] Double-Confirmation Modal Penghapusan & Pembersihan Akun Otomatis
- **Tanggal**: 2026-09-10
- **Komponen**: `resources/views/components/confirm-delete-modal.blade.php`, `resources/views/layouts/app.blade.php`, `app/Http/Controllers/Admin/UniversityController.php`, `app/Http/Controllers/Admin/AgencyController.php`, Views (`admin/universities/`, `admin/agencies/`, `admin/users/`, `admin/units/`, `admin/mentors/`, `university/lecturers/`)
- **Problem / Symptom**: 
  1. Super Admin tidak dapat menghapus universitas atau instansi dinas baru/uji coba setelah menekan "Buat Akun", karena controller memblokir penghapusan bila `users_count > 0`, padahal universitas tidak memiliki mahasiswa maupun dosen aktif (hanya akun admin tunggal bawaan sistem).
  2. Tombol hapus menggunakan `onsubmit="return confirm(...)"` native browser yang mudah salah klik tanpa sengaja, terhambat oleh pop-up blocker pada mobile Android, dan tidak memenuhi standar UI konfirmasi ganda (2x).
- **Root Cause**: 
  1. Validasi `UniversityController@destroy` dan `AgencyController@destroy` memeriksa total `users_count > 0` secara mentah tanpa membedakan akun mahasiswa/dosen aktif dengan akun admin mandiri instansi.
  2. Ketiadaan modal dialog konfirmasi ganda (double-confirmation modal) di level antarmuka.
- **Fix Applied**: 
  1. Membuat komponen Blade universal `components/confirm-delete-modal.blade.php` berbasis Alpine.js dengan lencana bahaya, detail nama data yang dihapus, checkbox persetujuan wajib ("Saya mengonfirmasi bahwa saya benar-benar ingin menghapus data ini..."), serta tombol "Ya, Hapus Permanen" yang dinonaktifkan sampai kotak dicentang.
  2. Mengintegrasikan modal ke dalam `layouts/app.blade.php` dengan event bus `@open-delete-modal.window`.
  3. Memperbarui seluruh tombol hapus di modul Universitas, Instansi, Pengguna, Divisi, Mentor, dan Dosen agar memicu event modal.
  4. Menyempurnakan logika `UniversityController@destroy` dan `AgencyController@destroy` agar memproteksi mahasiswa & penempatan aktif secara ketat, namun mengizinkan penghapusan instansi/kampus uji coba beserta pembersihan akun admin terkait di dalam transaksi database.
- **Prevention Rule**: Seluruh aksi destruktif (DELETE) wajib dilindungi oleh modal konfirmasi ganda (Double-Confirmation Modal) dengan checkbox persetujuan eksplisit, dilarang memakai `confirm()` native browser. Controller harus membedakan proteksi relasi bisnis bernilai tinggi (mahasiswa/transkrip aktif) dengan akun teknis internal yang dapat di-*clean-up* aman secara transaksional.
 
---

### [LRN-011] Pembenahan Alur Super Admin: Hub Kendali Terpadu Instansi Dinas, Tombol 'Lihat Akun', dan Eliminasi Panah Mentah
- **Tanggal**: 2026-09-13
- **Komponen**: `app/Http/Controllers/Admin/AgencyController.php`, `app/Http/Controllers/Admin/UnitController.php`, `app/Http/Controllers/Admin/UserController.php`, `app/Http/Controllers/Admin/LogbookController.php`, `resources/views/admin/agencies/index.blade.php`, `resources/views/admin/agencies/show.blade.php`, `resources/views/admin/units/index.blade.php`, `resources/views/admin/users/index.blade.php`
- **Problem / Symptom**: 
  1. Pada kartu master instansi dinas (`/admin/agencies`), Super Admin hanya memiliki satu tautan navigasi berupa link teks mentah `Lihat Unit ->` (dengan panah), tanpa opsi melihat akun-akun Personil (Admin & Mentor) yang terafiliasi dengan dinas tersebut.
  2. Alur Super Admin terlalu sempit (hanya fokus pembuatan dinas dan kuota lowongan), padahal diperlukan pengelolaan seluruh siklus hidup dan operasional dinas secara terpadu.
  3. Mengakses `/admin/units?agency_id=` menampilkan header statis tanpa konteks dinas yang dipilih dan menyisakan query string kosong.
- **Root Cause**: 
  1. Kartu pada `resources/views/admin/agencies/index.blade.php` tidak menyediakan tombol `Lihat Akun` dan `Kelola Dinas`.
  2. `Admin\AgencyController` tidak mengimplementasikan method `show($id)` untuk resource route `admin.agencies.show`.
  3. `UnitController@index` dan `UserController@index` tidak mengoper objek `$selectedAgency` ke view saat `agency_id` aktif.
- **Fix Applied**: 
  1. Menghilangkan panah `->` pada `Lihat Unit`, mendesain ulang tombol aksi sebagai tombol kapsul modern berikon gedung.
  2. Menambahkan tombol `Lihat Akun` pada kartu instansi lengkap dengan badge penghitung total Personil (Admin Dinas + Mentor) menuju `/admin/users?agency_id={id}`.
  3. Menambahkan tombol `Kelola Dinas` dan membuat halaman Pusat Manajemen Alur Dinas Terpadu (`resources/views/admin/agencies/show.blade.php`) yang merangkum:
     - Hero profile & data Pejabat Penandatangan Resmi (Kepala Dinas, NIP, Jabatan).
     - Tombol cepat: `Masuk Sebagai Admin Dinas (Login As)` dan `Edit Profil & TTD`.
     - 4 Tab Operasional: (1) Personil & Akun Kedinasan, (2) Divisi & Kuota Magang, (3) Pengajuan Magang Masuk, (4) Mahasiswa Aktif Magang & Mentor.
  4. Menambahkan 4 Executive Macro Stat Cards pada `/admin/agencies` (Total Instansi Dinas, Total Divisi, Total Kuota Magang & Terisi, Total Personil Kedinasan).
  5. Menambahkan banner konteks dinas aktif pada `/admin/units` dan `/admin/users` serta memperbaiki query submission dropdown.
- **Prevention Rule**: Master data instansi/entitas induk wajib menyediakan visibilitas dan kontrol 360 derajat (Unit, Akun Personil, Pelamar, dan Pengaturan). Tautan teks mentah bertanda panah dilarang digunakan di antarmuka manajemen utama; gunakan button/pill yang jelas dan terstandarisasi. Seluruh halaman yang difilter oleh parameter `agency_id` wajib menampilkan banner konteks lembaga yang sedang aktif.

---

### [LRN-012] Perbaikan Tombol Reset & Hapus Pengguna, Protokol Dobel Konfirmasi, dan Aksi Massal dengan Tripel Konfirmasi
- **Tanggal**: 2026-09-13
- **Komponen**: `app/Http/Controllers/Admin/UserController.php`, `routes/web.php`, `resources/views/admin/users/index.blade.php`, `resources/views/components/confirm-reset-modal.blade.php`, `resources/views/components/confirm-delete-modal.blade.php`, `resources/views/components/bulk-action-modal.blade.php`, `resources/views/layouts/app.blade.php`
- **Problem / Symptom**: 
  1. Tombol `Hapus` dan `Reset` pada Master Pengguna Sistem (`/admin/users`) tidak merespon saat diklik.
  2. Aksi krusial (reset & hapus) sebelumnya berpotensi salah pencet karena reset menggunakan browser `onsubmit="return confirm()"` mentah yang rentan ditekan tidak sengaja atau terblokir Chromium browser.
  3. Ketiadaan fitur pilih banyak (*multi-select* / *bulk actions*) untuk mengelola banyak akun secara serentak, serta ketiadaan mekanisme pengamanan ekstra tinggi (*triple-confirmation*) untuk operasi massal yang berisiko fatal.
- **Root Cause**: 
  1. Elemen tabel dan tombol aksi berada di luar cakupan (*scope*) `x-data` Alpine.js. Di Alpine v3, dispatch event kustom `@click="$dispatch(...)"` diabaikan sepenuhnya jika tidak memiliki elemen leluhur dengan direktif `x-data`.
  2. Dialog native `confirm()` browser rentan diabaikan/dibungkam oleh browser setelah pengalihan halaman berulang.
- **Fix Applied**: 
  1. Menyelimuti kontainer tabel dan floating bar dengan Alpine component `userBulkManagement()`.
  2. Mengganti semua dialog native browser dengan modal kustom elegan:
     - **Dobel Konfirmasi (Single Action)**: Menampilkan detail target akun + checkbox persetujuan sadar risiko sebelum tombol aktif (`confirm-reset-modal` dan `confirm-delete-modal`).
     - **Tripel Konfirmasi (Bulk Actions)**: Menampilkan audit preview daftar akun terdampak (Lapis 1), checkbox pernyataan tanggung jawab (Lapis 2), dan pengetikan kata kunci wajib persis `RESET SEMUA` / `HAPUS SEMUA` (Lapis 3) sebelum tombol eksekusi terbuka (`bulk-action-modal`).
  3. Menambahkan endpoint backend aman `admin.users.bulk_reset_password` dan `admin.users.bulk_delete` yang memproteksi akun Super Admin, memvalidasi relasi magang/penempatan aktif agar tidak merusak integritas basis data, dan mencatat transaksi ke `AuditLog`.
- **Prevention Rule**: Seluruh elemen interaktif yang memanfaatkan event Alpine (`$dispatch`, `@click`) WAJIB berada di dalam deklarasi `x-data`. Aksi krusial terhadap data sistem DILARANG menggunakan `window.confirm()` bawaan browser; wajib menggunakan modal konfirmasi ganda (*double confirmation*), dan aksi massal wajib menerapkan verifikasi 3 lapis (*triple confirmation*) dengan pengetikan kata kunci penegasan.

---

### [LRN-013] Penanganan Windows NTFS Junction Storage, Migrasi Token Verifikasi, & Resolusi Tabrakan ID Multi-Tenant
- **Tanggal**: 2026-09-14
- **Komponen**: `public/storage`, `database/migrations/`, `University\DashboardController`, `Student\ApplicationController`
- **Problem / Symptom**: 
  1. Lampiran logbook mahasiswa menghasilkan error 404 (Not Found) saat diklik di browser.
  2. Verifikasi sertifikat menghasilkan error `column "certificate_hash" does not exist`.
  3. Detail mahasiswa kampus (`/university/students/24`) memicu error 403 "Anda tidak memiliki hak akses untuk melihat data mahasiswa kampus lain".
  4. Mahasiswa berstatus lulus/selesai (`completed`) tidak dapat mengunduh kembali arsip surat penerimaan magang (`/student/application/{id}/letter`).
- **Root Cause**: 
  1. Direktori `public/storage` di Windows berupa folder fisik terpisah, bukan NTFS Junction ke `storage/app/public`.
  2. Migrasi penambahan token verifikasi belum dijalankan (`Pending`).
  3. Terjadi tabrakan ID (*ID collision*) antara `applications.id` dan `placements.id` di controller pemantauan universitas, di mana query mendahulukan `Application` kampus lain daripada `Placement` milik kampus sendiri yang memiliki nomor ID yang sama.
  4. Query download surat penerimaan dibatasi kaku hanya `where('status', 'accepted')`.
- **Fix Applied**: 
  1. Menghapus folder statis dan menghubungkan ulang `public/storage` sebagai NTFS Junction resmi via `php artisan storage:link`.
  2. Menjalankan `php artisan migrate` untuk mengaktifkan kolom `letter_token` dan `certificate_hash`.
  3. Memperbarui `University\DashboardController@showStudent` agar mengevaluasi relasi kampus terlebih dahulu dan memprioritaskan entitas yang cocok dengan institusi pengguna yang sedang login.
  4. Mengubah filter status surat menjadi `whereIn('status', ['accepted', 'completed'])`.
- **Prevention Rule**: Pastikan symlink Windows selalu bertipe `Junction`. Hindari asumsi nomor ID antar-tabel tidak beririsan; route parameter yang bersifat polimorfik/fallback wajib memvalidasi kepemilikan tenant (*tenant-aware resolution*) sebelum melakukan otorisasi penolakan.

---

### [LRN-014] Proteksi Integritas Pengajuan Ganda Mahasiswa, Resolusi Penempatan Laporan Akhir, & Penerbitan Sertifikat Adaptif
- **Tanggal**: 2026-09-14
- **Komponen**: `Student\ApplicationController`, `Student\FinalReportController`, `resources/views/student/application/create.blade.php`, `resources/views/student/final_report.blade.php`
- **Problem / Symptom**: 
  1. Mahasiswa yang sudah berstatus `accepted` (sedang aktif magang) atau `completed` masih dapat mengakses dan mengirim formulir pengajuan baru.
  2. Saat pengajuan baru terbuat (`status: pending`), query `Application::where('user_id', ...)->latest()->first()` di controller laporan akhir, logbook, dan dashboard mengambil baris pending baru tersebut, menyebabkan penempatan aktif, mentor, logbook, dan laporan akhir mendadak tersembunyi dengan error "Anda belum memiliki penempatan magang aktif".
  3. Pada universitas berskema penilaian ganda (*Dual Evaluation*), tombol unduh E-Sertifikat di `final_report.blade.php` langsung aktif saat pembimbing dinas selesai menilai (`nilai_pembimbing > 0`), padahal DPL kampus belum memasukkan nilai, berisiko menerbitkan sertifikat dengan nilai belum lengkap.
  4. Adanya *dead code* method admin `show` dan `updateStatus` di dalam `Student\ApplicationController`.
- **Root Cause**: 
  1. Pengecekan aktif di `create()` kaku hanya `status === 'pending'`, dan di `store()` sama sekali tidak ada validasi pengajuan aktif.
  2. Query `FinalReportController` tidak memprioritaskan relasi `whereHas('placement')`.
  3. Pengecekan view sertifikat hanya mengevaluasi `nilai_pembimbing > 0` tanpa memeriksa kelengkapan skema kampus (`evaluation_scheme`).
- **Fix Applied**: 
  1. Menambahkan guard di `create()` dan `store()` pada `Student\ApplicationController` yang memblokir pembuatan pengajuan baru jika mahasiswa memiliki pengajuan `pending`, `verified`, `accepted`, atau `completed`.
  2. Menjadikan banner status pengajuan di `create.blade.php` adaptif, dinamis, dan informatif.
  3. Memperkuat resolusi aplikasi di `FinalReportController@index` dan `@store` agar memprioritaskan `whereHas('placement')`.
  4. Memperketat pengecekan kelengkapan nilai pada `final_report.blade.php` agar mengevaluasi skema `dual_evaluation` (kedua pihak harus selesai menilai) atau `mentor_only` sebelum membuka tombol sertifikat, disertai pesan informatif jika baru salah satu pihak yang menilai.
  5. Menghapus method *dead code* di `Student\ApplicationController`.
- **Prevention Rule**: Seluruh alur formulir pengajuan wajib menerapkan *Guard Clause* terhadap status siklus hidup aktif pengguna. Query pemrosesan tahap lanjutan (Laporan Akhir, Evaluasi, Sertifikat) wajib mengaitkan entitas penempatan (`whereHas('placement')`) dan dilarang hanya bertumpu pada `latest()`. Validasi penerbitan dokumen resmi (sertifikat) wajib mematuhi skema penilaian institusi secara utuh.

---

### [LRN-015] Perapian UI Kartu Universitas (Primary + Dropdown ⋮), Tab Navigasi Horizontal Scrollable, & Pengujian E2E Multi-Role Layar Sentuh
- **Tanggal**: 2026-09-15
- **Komponen**: `resources/views/admin/universities/index.blade.php`, `resources/views/admin/universities/show.blade.php`, `resources/views/admin/agencies/show.blade.php`, Suite Pengujian E2E Desktop & Mobile
- **Problem / Symptom**: 
  1. Kartu universitas di halaman Super Admin memiliki tumpukan 5+ tombol aksi berdampingan (*Kelola Kampus, Akun, Login As, Edit, Hapus*), menyebabkan layout berantakan, teks bertumpuk, dan horizontal overflow pada perangkat mobile/tablet.
  2. Tab navigasi modul pada Pusat Kendali (Command Hub) melipat menjadi baris ganda (*multi-row wrap*) yang mengganggu estetika antarmuka saat dibuka pada layar ponsel 390px.
  3. Pengujian antarmuka sebelumnya hanya mengecek response status code tanpa validasi rendering aset CSS/JS dan kenyamanan sentuhan layar (*touch targets*).
- **Root Cause**: Desain tombol aksi menggunakan button biasa tanpa hierarki visual primary vs secondary, serta ketiadaan utilitas scrolling horizontal pada kontainer tab flex.
- **Fix Applied**: 
  1. Merefaktor kartu universitas menjadi pola standar industri: **1 Tombol Utama `Kelola Kampus` (Primary)** + **1 Tombol Dropdown Tiga Titik (`⋮`)** yang merangkum aksi sekunder (*Login As, Buatkan Akun, Edit, Daftar Dosen, Daftar Mahasiswa, Hapus*) menggunakan Alpine.js dengan click-outside protection.
  2. Menambahkan `flex-nowrap overflow-x-auto scrollbar-none` pada navigasi tab command hub universitas dan instansi.
  3. Melakukan kompilasi aset permanen via `npm run build` dan mengeksekusi uji coba langsung multi-role pada resolusi Desktop (1920x1080) dan Mobile (390x844) dengan capture visual terverifikasi.
- **Prevention Rule**: Seluruh halaman master data dengan lebih dari 2 aksi wajib menerapkan hierarki tombol (1 Primary + 1 Dropdown Secondary `⋮`). Kontainer navigasi tab horizontal wajib mendukung geser sentuh (*horizontal scrollable*) tanpa melipat (*no wrapping*).

---

### [LRN-016] Harmonisasi Slot UI Kartu Grid & Eliminasi Pergeseran Vertikal Angka Metrik
- **Tanggal**: 2026-09-17
- **Komponen**: `resources/views/admin/agencies/index.blade.php`, `resources/views/admin/universities/index.blade.php`, `resources/views/university/dashboard.blade.php`, `resources/views/dashboard.blade.php`
- **Problem / Symptom**: 
  1. Pada kartu instansi dinas (`/admin/agencies`) dan kartu universitas (`/admin/universities`), panjang judul/nama instansi (1 baris vs 2 baris), alamat (1 baris vs 2 baris), dan keberadaan PIC menyebabkan tinggi blok identitas bervariasi drastis antar kartu.
  2. Akibatnya, blok angka metrik ringkas (`Unit Kerja | Sisa Kuota | Personel` atau `Mahasiswa | Dosen | Akun`) serta tombol aksi di bagian bawah terdorong ke bawah atau tertarik ke atas, sehingga posisi angka metrik tidak sejajar secara horizontal antar-kartu dalam satu baris grid.
- **Root Cause**: 
  1. Elemen judul (`h3`) dan alamat (`p`) tidak memiliki slot tinggi tetap atau *fixed vertical footprint* (`line-clamp-2 h-[3rem]` dan `h-9`).
  2. Blok metrik ringkas dibungkus di dalam `div` pembungkus teks yang sama di atas, bukan sebagai elemen terpisah dengan `mt-auto` atau flex child berposisi presisi.
- **Fix Applied**: 
  1. **Slotting Standar**: Mengunci tinggi judul ke `line-clamp-2 h-[3rem] overflow-hidden` dan alamat ke `line-clamp-2 h-9 leading-relaxed overflow-hidden` pada kartu dinas dan kampus mitra.
  2. **Metadata Konsisten**: Memberikan slot 2-baris konstan (`h-10`) untuk informasi PIC dan Admin email.
  3. **Direct Child Alignment**: Menjadikan blok metrik ringkas dan footer aksi sebagai *direct flex children* dengan `shrink-0` dan `mt-4 pt-4 border-t`, dengan kontainer konten atas disetel ke `flex-1 flex flex-col`. Hasilnya, seluruh angka metrik dan tombol aksi sejajar 100% pada ketinggian pixel horizontal yang sama persis di setiap baris.
- **Prevention Rule**: Seluruh antarmuka berbasis *Card Grid* (`grid-cols-2`, `grid-cols-3`, `lg:grid-cols-4`) yang memiliki teks dinamis wajib menerapkan standarisasi tinggi slot (`line-clamp-X h-[Xrem]`) pada setiap lapisan teks dan memisahkan blok metrik/angka serta footer tombol dengan `shrink-0` / `mt-auto` agar tata letak kartu tidak bergelombang (*uneven/wavy layout*).

---

### [LRN-017] Integrasi Status Magang 'Mengundurkan Diri' (Resigned/Canceled) & Alur Pengajuan Ulang
- **Tanggal**: 2026-09-17
- **Komponen**: `resources/views/dashboard.blade.php`, `resources/views/student/logbook/index.blade.php`, `resources/views/mentor/dashboard.blade.php`, `resources/views/university/students/show.blade.php`, `resources/views/student/application/create.blade.php`
- **Problem / Symptom**: 
  1. Mahasiswa yang telah berstatus mengundurkan diri (`resigned` / lifecycle `RESIGNED`) pada database PostgreSQL tetap menampilkan status **"DALAM PROSES"** (badge kuning) pada kartu Status Magang dan Detail Penempatan Magang di dasbor mahasiswa (`/dashboard`).
  2. Banner eksekutif atas menampilkan teks mentah "Status: Resigned" bukannya bahasa Indonesia yang baku.
  3. Mahasiswa yang mengundurkan diri terjebak dalam kondisi *limbo* karena tidak memiliki tautan aksi untuk mendaftar ulang (*re-apply*), serta pada halaman logbook tidak ada penjelasan status pengunduran diri.
- **Root Cause**: 
  1. Pada `dashboard.blade.php`, percabangan `@if / @elseif` hanya mengecek `ACTIVE/accepted`, `COMPLETED/completed`, `ACCEPTED`, dan `REJECTED/rejected`. Status `RESIGNED` dan `CANCELED` tidak terdefinisi pada kondisi cabang manapun sehingga otomatis masuk ke blok fallback `@else`, yang mencetak badge teks kuning `<span ...>DALAM PROSES</span>`.
  2. Kartu border kiri (`border-l-4`) belum memiliki kelas slate untuk `RESIGNED`/`CANCELED`.
  3. Modal rincian kelengkapan berkas menganggap status `resigned` sebagai "Menunggu Verifikasi Dinas" karena hanya memeriksa keberadaan objek `$application`.
- **Fix Applied**: 
  1. Menambahkan pemetaan status terpadu di awal view `dashboard.blade.php` dengan penerjemahan resmi ke Bahasa Indonesia (`Mengundurkan Diri`, `Dibatalkan`, `Magang Aktif`, `Lulus Magang`, `Diterima (Calon Peserta)`, `Ditolak`).
  2. Menambahkan cabang eksplisit `@elseif($application->lifecycle_status === 'RESIGNED' || $application->status === 'resigned')` dengan badge slate resmi (`bg-slate-100 text-slate-700 border-slate-300`) baik pada Card 2 maupun pada Detail Penempatan Magang.
  3. Menyediakan Call-to-Action (CTA) interaktif `Buat Pengajuan Baru →` mengarah ke rute `student.application.create` pada kartu status, modal detail, dan alert box penempatan.
  4. Menambahkan alert banner terintegrasi pada `student/logbook/index.blade.php` yang menginformasikan penonaktifan logbook akibat pengunduran diri serta mengarahkan pendaftaran ulang.
  5. Memperbarui badge styling status pada `mentor/dashboard.blade.php` dan `university/students/show.blade.php` agar dinamis dan konsisten di seluruh role.
- **Prevention Rule**: Setiap kali memperkenalkan atau mengubah status siklus hidup (*lifecycle status*), pastikan seluruh view Blade yang memiliki logika kondisional status memperlakukan setiap status valid secara eksplisit (jangan membiarkan status terminal seperti `RESIGNED`, `CANCELED`, atau `TERMINATED` jatuh ke fallback `@else` yang ambigu). Sediakan selalu jalur pemulihan bisnis (*recovery path*) berupa tombol buat pengajuan baru bagi mahasiswa berstatus non-aktif.

---

### [LRN-018] Penyelarasan Syarat Kelulusan Magang (Laporan Akhir Disetujui & Penilaian Lengkap)
- **Tanggal**: 2026-09-23
- **Komponen**: `app/Models/Application.php`, `app/Http/Controllers/Admin/ApplicationController.php`, `resources/views/admin/applications/index.blade.php`, `resources/views/admin/applications/show.blade.php`
- **Problem / Symptom**: Mahasiswa magang berstatus `accepted` yang laporan akhirnya telah disetujui (misal pengajuan #25) muncul dengan label prioritas "Siap Kelulusan" dan tombol "Aksi Kelulusan" pada tabel Daftar Pengajuan Magang (`/admin/applications`). Namun, ketika admin membuka halaman detail, tombol keputusan status "COMPLETED" terkunci (disabled) dengan pesan syarat belum lengkap karena mahasiswa bersangkutan belum dinilai oleh Pembimbing Lapangan/DPL.
- **Root Cause**: Query penentuan ranking prioritas dan kondisi tampilan pada `index.blade.php` sebelumnya hanya memeriksa status laporan akhir (`final_reports.status IN ('approved', 'disetujui')`) tanpa memverifikasi kelengkapan nilai evaluasi pada tabel `evaluations`.
- **Fix Applied**: 
  1. Menambahkan atribut accessor terpadu pada `Application.php`: `has_approved_report`, `has_complete_evaluation` (memanfaatkan `Evaluation::is_complete` dan skema kampus `mentor_only` vs `dual_evaluation`), serta `can_complete`.
  2. Memperketat query pengurutan prioritas `ApplicationController::index()` sehingga Rank 2 ("Siap Diluluskan") hanya diberikan jika laporan berstatus approved DAN evaluasi telah dinilai (`final_score > 0` atau sub-skor dinas/dosen terisi). Mahasiswa yang laporannya disetujui namun nilainya belum lengkap diturunkan ke prioritas reguler (Rank 3).
  3. Memperbarui tabel & mobile card `index.blade.php`: menampilkan badge status yang tepat `● Menunggu Nilai` (badge biru) dan tombol standar `Detail Berkas` (bukan `Aksi Kelulusan`) jika mahasiswa belum selesai dinilai.
  4. Memperbarui `show.blade.php` dengan kartu info kontekstual "Menunggu Input Nilai Evaluasi Magang" dan subtitle dinamis pada tombol COMPLETED (misal: "Nilai Belum Diinput Lengkap" atau "Laporan Akhir Belum Disetujui").
  5. Menambahkan validasi ketat backend di `ApplicationController::updateStatus` yang memberikan notifikasi error spesifik jika admin mencoba mem-bypass status COMPLETED sebelum kedua syarat terpenuhi.
- **Prevention Rule**: Jangan pernah menggunakan asumsi satu syarat (misal hanya persetujuan laporan) untuk memicu tindakan kelulusan akhir jika bisnis proses mensyaratkan gabungan prasyarat (laporan + transkrip nilai). Selalu satukan logika prasyarat kelulusan ke level Model Accessor (`can_complete`) agar tabel daftar, halaman detail, dan validator controller selalu 100% konsisten.

---

### [LRN-019] Perapian Pratinjau Berkas Laporan Akhir, Lightbox Logbook, Eliminasi View Duplicate, & Checklist Evaluasi 3-Sisi
- **Tanggal**: 2026-09-23
- **Komponen**: `resources/views/student/final_report.blade.php`, `resources/views/student/logbook/index.blade.php`, `resources/views/student/dashboard.blade.php` (Deleted)
- **Problem / Symptom**: 
  1. Halaman unggah laporan akhir mahasiswa tidak memiliki tombol `Lihat Berkas` langsung pada box status file yang baru dipilih.
  2. Gambar lampiran logbook di smartphone tidak dapat diperbesar penuh.
  3. Berkas view `student/dashboard.blade.php` tidak terpakai (orphan) yang mengabaikan master layout app.
  4. Indikator kelayakan E-Sertifikat tidak menampilkan checklist 3-sisi (Pembimbing Dinas, DPL Kampus, ACC Naskah Laporan).
- **Root Cause**: Ketiadaan handler URL Object di tombol box status laporan akhir, ketiadaan modal Lightbox Alpine.js pada view logbook, serta layout duplicate.
- **Fix Applied**: 
  1. Menambahkan tombol `Lihat Berkas` interaktif berbasis `URL.createObjectURL` di `resources/views/student/final_report.blade.php`.
  2. Menambahkan modal Lightbox Image Viewer Alpine.js universal di `resources/views/student/logbook/index.blade.php`.
  3. Menghapus berkas redundan `resources/views/student/dashboard.blade.php`.
  4. Menyajikan *3-Item Progress Checklist* pada modul evaluasi laporan akhir.
- **Prevention Rule**: Setiap modul input berkas (file uploader) wajib dilengkapi tombol pratinjau `Lihat Berkas` secara live sebelum pengguna mengeklik tombol submit. Seluruh lampiran gambar pada antarmuka mobile wajib dilindungi modal Lightbox preview.

---

### [LRN-020] Memory Exhaustion saat Serialize Eloquent Model (N+1 Json Encode Blade)
- **Tanggal**: 2026-09-23
- **Komponen**: `LecturerLogbookController@index`, `resources/views/lecturer/logbooks/index.blade.php`
- **Problem / Symptom**: Mengakses halaman `/lecturer/logbooks` memicu error HTTP 500 dengan pesan `Allowed memory size of 536870912 bytes exhausted` (Memory Leak) dan mematikan server lokal/produksi.
- **Root Cause**: Pemanggilan `json_encode($bundle)` di Blade View (untuk data modal Alpine.js) di mana `$bundle` ternyata menampung utuh objek Eloquent Model (`Placement`, `Application`, `User`). Laravel secara otomatis akan men-serialize seluruh nested relations dan mengaktifkan accessor `$appends`. Accessor `getLifecycleStatusAttribute` pada model `Application` memanggil relasi `$this->placement` dari basis data, yang kemudian terhidrasi kembali dan diserialisasi secara tak terhingga (infinite recursive object graph).
- **Fix Applied**: 
  1. Memisahkan secara tegas payload untuk antarmuka/modal. 
  2. Menambahkan array `modal_data` di dalam `$bundle` pada `LecturerLogbookController`, yang *hanya* diekstrak menjadi primitive values (string, integer, array dasar).
  3. Mengubah trigger modal di `index.blade.php` menjadi `json_encode($bundle['modal_data'])`.
- **Prevention Rule**: DILARANG KERAS mengeksekusi `json_encode()` pada objek Eloquent Model secara utuh di dalam file Blade/Alpine JS. Terutama jika model tersebut memiliki `$appends` atau relasi bersarang. Selalu gunakan DTO (Data Transfer Object) atau mapping array primitif ringan (`map->toArray()`) khusus untuk konsumsi JSON front-end.

### [LRN-021] Standarisasi Pipeline Status Sistem Magang (Single Source of Truth) & Query-Level Filtering
- **Tanggal**: 2026-09-24
- **Komponen**: `app/Enums/ApplicationStatus.php`, `app/Enums/ReviewStatus.php`, `app/Models/Application.php`, `app/Models/Placement.php`, `app/Console/Commands/SyncInternshipStatus.php`, `routes/console.php`, Seluruh Controller Multi-Role (`Admin`, `Mentor`, `Lecturer`, `University`, `Student`)
- **Problem / Symptom**: 
  1. Terjadi fragmentasi dan dualisme status di mana status riil di database (`applications.status`) berbeda dengan status virtual di RAM (`lifecycle_status`).
  2. Akibatnya, Controller Mentor (`Mentor\DashboardController`) dan Dosen (`Lecturer\MonitoringController`) harus mengambil seluruh data dan mem-filternya di RAM (`$allPlacements->filter(...)`), tidak bisa di-paginate di level database SQL.
  3. Form Admin (`resources/views/admin/applications/show.blade.php`) dan index filter kekurangan opsi `verified` dan `active`.
  4. Adanya toleransi campur kode string bahasa Indonesia (`'disetujui'`, `'menunggu'`) di controller dan Blade views.
- **Root Cause**: Ketiadaan Backed Enum resmi PHP 8.1 dan perancangan awal yang menaruh komputasi status pada accessor model Eloquent (`$appends = ['lifecycle_status']`).
- **Fix Applied**: 
  1. Dibuat PHP 8.1 Backed Enums: `ApplicationStatus` (`pending`, `verified`, `accepted`, `active`, `completed`, `rejected`, `resigned`) dan `ReviewStatus` (`pending`, `approved`, `rejected`, `revision`) dengan helper `label()`, `badgeColor()`, `isOngoing()`, `canLogbook()`.
  2. Menghapus accessor `lifecycle_status` dari `$appends` pada `Application.php` dan mencasting `status` langsung ke `ApplicationStatus::class`.
  3. Menghapus zombie status pada `Placement` dan memperbarui `syncCompletionStatus()` untuk otomatis memperbarui status aplikasi ke `completed`.
  4. Membuat Artisan command `app:sync-internship-status` yang menjadwalkan transisi otomatis aplikasi `accepted` ke `active` saat `start_date <= today` dan mencatatnya ke `AuditLog`.
  5. Mengubah query controller Mentor, Dosen, dan Kampus menjadi database-level query dengan pagination (`paginate(10)->withQueryString()`) menggunakan relasi `whereRelation('application', 'status', ...)`.
  6. Memperbaiki Blade views Admin (semua 7 status berurutan dengan kartu Alpine), Mahasiswa (proteksi logbook aktif), Mentor, Dosen, dan Kampus dengan badge seragam Tailwind CSS.
  7. Menghapus seluruh string bahasa Indonesia `'disetujui'` dan `'menunggu'` dari logic controller dan query.
---

### [LRN-022] BackedEnum Type-Safety & Blade Views / Service Hardening
- **Tanggal**: 2026-09-24
- **Komponen**: `resources/views/dashboard.blade.php`, `resources/views/admin/applications/show.blade.php`, `resources/views/student/application/create.blade.php`, `resources/views/admin/universities/show.blade.php`, `resources/views/admin/agencies/show.blade.php`, `resources/views/student/logbook/index.blade.php`, `resources/views/mentor/student-detail.blade.php`, `app/Services/NotificationService.php`, `app/Http/Controllers/Student/FinalReportController.php`, `app/Http/Controllers/Student/ApplicationController.php`
- **Problem / Symptom**: 
  1. Internal Server Error (HTTP 500) `TypeError: strtolower(): Argument #1 ($string) must be of type string, App\Enums\ApplicationStatus given` di Dashboard Mahasiswa dan seluruh halaman ber-navbar.
  2. Dropdown dan form verifikasi Admin belum menyediakan 7 alur status baku secara berurutan dan kontainer *acceptance-box* tertutup untuk status `active`.
  3. Komparasi strict string PHP (`=== 'accepted'`, `in_array($app->status, ['accepted', 'completed'])`) mengevaluasi `false` saat model mengembalikan enum `ApplicationStatus::ACCEPTED`.
- **Root Cause**: 
  - Fungsi string native PHP (`strtolower()`, `strtoupper()`) dan `match()` operator menerima objek BackedEnum langsung dari atribut Eloquent (`$application->status`) tanpa ekstraksi properti `->value`.
  - Service `NotificationService::getNotificationsForUser` dipanggil oleh navbar di seluruh halaman dan memanggil `strtolower($latestApp->status)` secara mentah.
- **Fix Applied**: 
  1. Standarisasi ekstraksi status aman di Blade dan Service:
     `$appStatusVal = $application ? ($application->status instanceof \BackedEnum ? $application->status->value : (string)$application->status) : null;`
     `$rawSt = strtolower($appStatusVal ?? '');`
  2. Merombak form Admin di `resources/views/admin/applications/show.blade.php` dengan dropdown `<select id="status-select" name="status">` berisi 7 status lengkap (`pending`, `verified`, `accepted`, `active`, `completed`, `rejected`, `resigned`), proteksi syarat kelulusan `$canComplete`, sinkronisasi visual kartu Alpine.js, penamaan ID `#acceptance-box` & `#rejection-box`, serta fungsi `toggleFields()`.
  3. Mengamankan seluruh pemanggilan `strtolower()` dan `strtoupper()` di `NotificationService.php`, `FinalReportController.php`, `Student/ApplicationController.php`, dan semua Blade views.
  4. Menambahkan 3 skenario tes komprehensif pada `ApplicationStatusArchitectureTest.php` untuk merender seluruh halaman dengan 7 status enum (100% pass, 33/33 total tests pass).
- **Prevention Rule**: 
  - JANGAN PERNAH mengoper atribut status Eloquent langsung ke fungsi string native PHP seperti `strtolower()` atau `strtoupper()` tanpa mengecek `instanceof \BackedEnum ? ->value : (string)`.
  - Jangan gunakan komparasi identitas strict (`=== 'string'`) terhadap atribut status model; selalu bandingkan dengan enum instance (`=== ApplicationStatus::ACCEPTED`) atau ekstrak nilai string `->value` terlebih dahulu.

---

### [LRN-023] Admin Selection Reactivity & Offline Vector TTE Letter Generation
- **Tanggal**: 2026-09-24
- **Komponen**: `resources/views/admin/applications/show.blade.php`, `resources/views/letters/acceptance.blade.php`, `routes/web.php`, `app/Http/Controllers/Student/ApplicationController.php`, `composer.json`
- **Problem / Symptom**: 
  1. Pada halaman verifikasi admin (`admin/applications/{id}`), saat admin mengklik kartu status `ACCEPTED` dari posisi `pending` atau `verified`, kotak plotting pembimbing dan nomor surat tidak langsung muncul di layar secara interaktif.
  2. Gambar QR code pada kotak Tanda Tangan Elektronik (TTE) surat balasan (`/admin/applications/{id}/letter`) rusak/pecah (*broken image placeholder*).
  3. Nama mahasiswa di surat penerimaan mengandung teks pengujian dari database seeder (misal: `Nanda Kartika (Accepted Future Date)`).
  4. Admin kesulitan meninjau surat pengantar/proposal asli dari kampus mahasiswa saat akan memplot pembimbing dinas.
  5. Label tombol aksi di view admin masih bertuliskan `Pratinjau / Cetak Surat PDF` (diinginkan lebih ringkas).
  6. Rute verifikasi surat `/verify-letter/{token}` dan download surat mahasiswa mengembalikan 404 ketika status pengajuan telah bergeser ke `active`.
- **Root Cause**: 
  1. Kontainer `#acceptance-box` di-render dengan kelas Tailwind `hidden` secara hardcoded via PHP ternary. Di Tailwind CSS, class `.hidden` memiliki aturan `display: none !important;` yang menimpa manipulasi `style="display: block;"` bawaan directive `x-show` Alpine.js sebelum halaman di-reload.
  2. Template surat mengandalkan generator QR Code eksternal (`api.qrserver.com`) melalui tag `<img>`. Di lingkungan localhost/offline atau saat koneksi dibatasi firewall, request HTTP gagal dan menghasilkan icon gambar pecah.
  3. Atribut nama mahasiswa (`$application->user->name`) dicetak secara mentah tanpa sanitasi string kurung `(...)`.
  4. Rute verifikasi publik dan download mahasiswa membatasi filter status hanya pada `['accepted', 'completed']` tanpa menyertakan status `active`.
- **Fix Applied**: 
  1. Menghapus kelas `hidden` bawaan PHP pada `#acceptance-box` dan `#rejection-box`, menyerahkan kendali visibilitas sepenuhnya pada Alpine.js (`x-show="status === 'accepted' || status === 'active' || status === 'completed'"`) serta menyelaraskan fungsi vanilla JS `toggleFields()` dengan properti inline `style.display`.
  2. Menambahkan kartu pratinjau cepat *Surat Pengantar Kampus* di dalam `#acceptance-box` dengan tombol `Lihat Surat Pengantar` yang langsung membuka berkas asli mahasiswa di tab baru.
  3. Mengubah label tombol aksi menjadi `Cetak Surat Balasan`.
  4. Menginstal library `simplesoftwareio/simple-qrcode` (`^4.2`) dan merender QR Code TTE secara lokal sebagai inline vector SVG (`QrCode::size(68)->margin(0)->generate($verifyUrl)`), 100% bebas dari ketergantungan API internet.
  5. Melakukan sanitasi regex nama mahasiswa: `preg_replace('/\s*\([^)]*\)/', '', ...)`.
  6. Memperbarui klausa `whereIn('status', ['accepted', 'active', 'completed'])` pada rute verifikasi surat dan controller mahasiswa.
- **Prevention Rule**: 
  - JANGAN PERNAH mencampur class CSS Tailwind `.hidden` dengan direktif Alpine.js `x-show` pada elemen yang sama karena `.hidden` menerapkan `display: none !important`.
  - Dokumen resmi berharga hukum (seperti Surat Balasan Magang / TTE) tidak boleh bergantung pada API gambar pihak ketiga eksternal; selalu generate QR Code secara lokal dalam format SVG/Base64.
  - Setiap rute dokumen yang diterbitkan saat status `accepted` harus tetap dapat diakses dan diverifikasi secara konsisten saat status berjalan di tahap `active` hingga `completed`.

---

### [LRN-024] Mobile-First Responsive Tables & Dashboard Card Hardening
- **Tanggal**: 2026-09-25
- **Komponen**: `resources/views/mentor/dashboard.blade.php`, `resources/views/lecturer/dashboard.blade.php`, `resources/views/admin/dashboard.blade.php`
- **Problem / Symptom**: 
  1. Tampilan tabel bimbingan mahasiswa pada dashboard Mentor dan Dosen Pembimbing Lapangan terpotong (*clipped*) pada viewport seluler (375x812), di mana kolom krusial (Logbook, Laporan Akhir, Nilai, dan Tombol Aksi Detail) berada di luar batas layar tanpa card view khusus.
  2. Pada dashboard Super Admin, teks judul kartu distribusi penempatan dan kampus terpotong (*truncated*) menjadi `Distribusi Penempatan Ins...` dan `Distribusi Asal Kampus S...` saat diakses pada resolusi mobile.
- **Root Cause**: 
  1. Tabel HTML multi-kolom (> 6 kolom) dirender secara langsung tanpa mobile card pattern (`block sm:hidden divide-y divide-slate-100`), sehingga memaksa tabel melebar dan menyembunyikan kolom kanan dari pandangan visual.
  2. Pada admin dashboard, header kartu menggunakan `flex items-center justify-between` dengan kelas `truncate` statis pada judul, menyebabkan flex container memangkas teks judul pada layar sempit (< 640px).
- **Fix Applied**: 
  1. Menambahkan pola presentasi mobile card view (`block sm:hidden divide-y divide-slate-100`) pada `mentor/dashboard.blade.php` dan `lecturer/dashboard.blade.php` yang menyajikan ringkasan data mahasiswa, avatar inisial, badge status, progres logbook, nilai akhir/dinas, dan tombol aksi dengan touch target yang ramah jari seluler.
  2. Membungkus tabel data desktop dalam `hidden sm:block overflow-x-auto` agar tata letak tabel penuh tetap terjaga rapi pada layar tablet dan desktop.
  3. Mengubah header kartu distribusi di `admin/dashboard.blade.php` menggunakan `flex-col sm:flex-row sm:items-center` dan menghilangkan pemotongan `truncate` pada judul agar teks tampil utuh dan tombol kelola berposisi rapi.
- **Prevention Rule**: Seluruh tabel multi-kolom di view utama wajib menerapkan arsitektur adaptif: mobile card view (`block sm:hidden`) untuk layar seluler (< 640px/768px) dan tabel berbasis scroll (`hidden sm:block overflow-x-auto`) untuk desktop. Jangan gunakan class `truncate` pada judul komponen utama tanpa container pembungkus yang fleksibel.

---

### [LRN-025] Native Sub-Agents MCP Bridge (Claude Code & Ollama Qwen)
- **Tanggal**: 2026-09-25
- **Komponen**: `scripts/mcp-agents-server.mjs`, `~/.gemini/config/mcp_config.json`, `.antigravity/rules.md`
- **Problem / Symptom**: 
  1. Claude Code CLI mengembalikan pesan `Invalid API key · Fix external API key` saat dieksekusi melalui wrapper script.
  2. Eksekusi lokal Ollama Qwen 2.5 Coder 7B mengalami latensi tinggi (63 detik untuk 100 token) dan rawan timeout pada pengujian berbasis pipe PowerShell / CLI dengan spinner braille.
  3. Pemanggilan tool MCP via Stdio membutuhkan penanganan siklus hidup JSON-RPC 2.0 yang presisi (`initialize` $\rightarrow$ `notifications/initialized` $\rightarrow$ `tools/call`).
- **Root Cause**: 
  1. Berkas `.env.agents` memuat kunci tiruan/mock `ANTHROPIC_API_KEY=apikey_...` yang jika dimuat mentah ke `process.env` akan menimpa kredensial sesi OAuth resmi `claude login` yang aktif.
  2. Ollama CLI mencetak karakter braille spinner ke `stderr` dan berjalan pada CPU 100%, sehingga eksekusi multi-paragraf memerlukan waktu lebih lama daripada inferensi GPU.
  3. Server MCP berbasis SDK resmi `@modelcontextprotocol/sdk` mensyaratkan notifikasi `initialized` dari client sebelum merespons panggilan `tools/call`.
- **Fix Applied**: 
  1. Memodifikasi `scripts/mcp-agents-server.mjs` untuk memvalidasi `ANTHROPIC_API_KEY`. Jika formatnya bukan kunci resmi (`sk-ant-`), environment variable diabaikan agar Claude CLI secara otomatis memanfaatkan sesi login OAuth aktifnya.
  2. Mengimplementasikan koneksi langsung ke Ollama REST API (`http://127.0.0.1:11434/api/generate`) dengan fallback otomatis ke CLI spawn, pembersihan karakter braille/ANSI, serta peningkatan timeout menjadi 3 menit.
  3. Mendaftarkan server `subagents` ke `~/.gemini/config/mcp_config.json`, `.antigravity/mcp_config.json`, dan `.agents/mcp_config.json`, serta mempublikasikan skema tool ke `.gemini/antigravity-ide/mcp/subagents/`.
  4. Menambahkan arahan sistem refleks ke `.antigravity/rules.md` dan `AGENTS.md`.
- **Prevention Rule**: Selalu lindungi sesi OAuth bawaan tool CLI dari overwrite placeholder env. Gunakan direct HTTP API untuk inferensi lokal Ollama guna menghindari hambatan terminal spinner dan selalu terapkan timeout yang memadai untuk komputasi CPU.

---

### [LRN-026] Lifecycle Pipeline, Quota Harmony & Logbook Destroy Hardening
- **Tanggal**: 2026-09-28
- **Komponen**: `app/Http/Controllers/Student/LogbookController.php`, `app/Http/Controllers/Student/DashboardController.php`, `app/Http/Controllers/Admin/LogbookController.php`, `app/Http/Controllers/Lecturer/LogbookController.php`, `app/Http/Controllers/Admin/MentorController.php`, `app/Http/Controllers/Admin/UniversityController.php`, `app/Models/Unit.php`, `app/Models/AgencyProfile.php`, `app/Http/Controllers/Admin/CertificateController.php`, `resources/views/student/logbook/index.blade.php`, `tests/Feature/SystemAuditFixesTest.php`
- **Problem / Symptom**:
  1. Route `DELETE /student/logbook/{id}` melempar `BadMethodCallException` karena ketiadaan method `destroy()` di `StudentLogbookController`.
  2. Mahasiswa berstatus `active` yang memilih/mendaftarkan DPL di dashboard mengalami HTTP 404 (`whereIn('status', ['accepted', 'completed'])` tidak menyertakan `'active'`).
  3. Admin dan Dosen tidak dapat melihat logbook mahasiswa yang sedang aktif magang karena query logbook menyaring `accepted` tanpa `active`.
  4. Jumlah mahasiswa aktif pada profil mentor dinas keliru bernilai 0 karena query menghitung `['accepted', 'verified']` alih-alih `['accepted', 'active']`.
  5. Sisa kuota pada `Unit` dan `AgencyProfile` tidak sinkron dengan `AdminApplicationController`: `Unit` mengabaikan `active`, sedangkan `AgencyProfile` menghitung mahasiswa `completed` sebagai pengurang kuota permanen dan mengabaikan `active`.
  6. Pada `/admin/certificates`, mahasiswa berstatus `accepted` muncul di tabel namun saat admin mengklik preview sertifikat terjadi HTTP 403 karena hak cetak mensyaratkan status `completed`.
- **Root Cause**:
  1. Method `destroy()` belum diimplementasikan saat pendaftaran resource route logbook di `web.php`.
  2. Fragmentasi penulisan status lifecycle pasca-seleksi: status `active` terlewat di beberapa query Eloquent builder yang hanya memeriksa `accepted`.
  3. Formula kalkulasi sisa kuota tidak memperhitungkan irisan tanggal magang aktif dan salah memasukkan status `completed`.
  4. Query index sertifikat admin memuat status `accepted` tanpa sinkronisasi kelulusan dan tanpa filter status `completed`.
- **Fix Applied**:
  1. Mengimplementasikan `StudentLogbookController::destroy($id)` dengan validasi kepemilikan (`Auth::id()`), proteksi status (`status !== 'approved'`), penghapusan berkas fisik di `Storage::disk('public')`, serta menambahkan tombol Hapus interaktif pada tabel desktop dan kartu mobile logbook.
  2. Menambahkan status `'active'` pada query `DashboardController::selectAdvisor`, `DashboardController::storeNewAdvisor`, `Admin/LogbookController::index`, `Lecturer/LogbookController::index`, dan `Admin/MentorController::index`.
  3. Menyelaraskan penghitungan sisa kuota pada `Unit` dan `AgencyProfile` agar berbasis irisan tanggal magang aktif dengan status `['accepted', 'active']`.
  4. Menambahkan auto-sync kelulusan (`syncCompletionStatus`) dan membatasi index `/admin/certificates` hanya menampilkan pengajuan berstatus `completed`.
  5. Menambahkan rangkaian pengujian otomatis `tests/Feature/SystemAuditFixesTest.php` yang memvalidasi seluruh perbaikan secara menyeluruh (passed 100%).
- **Prevention Rule**:
  Setiap query yang berkaitan dengan peserta magang aktif wajib menyertakan status `['accepted', 'active']` (atau `['accepted', 'active', 'completed']` jika relevan dengan riwayat). Jangan pernah menghitung status `completed` sebagai beban kuota aktif. Setiap route yang didaftarkan di `routes/web.php` wajib diverifikasi keberadaan method-nya di Controller.

---

### [LRN-027] Penyelarasan Standar Lebar Layout Halaman Portal Mahasiswa (Max-W-7xl)
- **Tanggal**: 2026-09-28
- **Komponen**: `resources/views/student/profile/edit.blade.php`, `resources/views/student/final_report.blade.php`, `resources/views/student/logbook/index.blade.php`, `resources/views/layouts/app.blade.php`
- **Problem / Symptom**: Terjadi ketidaksinkronan lebar halaman (*layout width jumping*) saat mahasiswa berpindah antara Dashboard/Logbook (`max-w-7xl` / 1280px) dengan Profil Siswa dan Laporan Akhir (`max-w-4xl` / 896px). Konten Profil dan Laporan Akhir menyempit drastis ke tengah dan menyisakan ruang kosong besar di sisi kiri dan kanan, tidak selaras dengan header navigasi aplikasi.
- **Root Cause**: Desain awal halaman Profil dan Laporan Akhir memakai container sempit `max-w-4xl`, sedangkan `layouts/app.blade.php` mendefinisikan header di `max-w-7xl` dan halaman Logbook Mahasiswa serta Pengajuan Magang menggunakan `max-w-7xl`. Selain itu, pada `student/final_report.blade.php` terdapat referensi properti `$application->start_date` tanpa operator null-safe `?->` jika mahasiswa belum memiliki pengajuan.
- **Fix Applied**:
  1. Menyelaraskan container utama `student/final_report.blade.php` ke `max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6`, serta melengkapi seluruh pemanggilan relasi `$application` dengan operator null-safe `?->`.
  2. Merestrukturisasi `student/profile/edit.blade.php` ke standar `max-w-7xl` dengan layout 2-kolom responsif:
     - Kolom kiri (`lg:col-span-4`): Kartu Ringkasan Akun (Inisial, Nama, NIM, Universitas, Prodi, Semester) dan Kartu Ketentuan Data Profil.
     - Kolom kanan (`lg:col-span-8`): Kartu Formulir Pembaruan Data Profil (Identitas, PT, Kontak Pribadi, Kontak Darurat).
  3. Menyelaraskan teks judul header, subtitle, banner status verifikasi (nama instansi, badge status, stepper tracker 4-langkah, rincian data 4-kolom), dan kartu biru "Informasi Penempatan Magang" antara `student/logbook/index.blade.php` dan `student/final_report.blade.php` sehingga konten dan terminologi 100% konsisten.
  4. Memverifikasi seluruh halaman terkompilasi dan lulus 69 unit/feature tests (Exit Code 0).
- **Prevention Rule**: Seluruh halaman portal utama (Dashboard, Logbook, Profil, Laporan Akhir, Pendaftaran) wajib menggunakan container baku `max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6` dan struktur header/kartu data penempatan terstandar agar konsistensi visual serta keselarasan teks terjaga di seluruh navigasi aplikasi.


---

### [LRN-028] Hard Visual Guard & Mandat Multimodal Vision Browser Automation
- **Tanggal**: 2026-09-26
- **Komponen**: `scripts/visual-guard.mjs`, `scripts/tier1_puppeteer_audit.mjs`, `scripts/tier3_playwright_audit.mjs`, `.antigravity/rules.md`
- **Problem / Symptom**: 
  1. Runner pengujian visual sebelumnya melaporkan status **PASS** secara keliru (*false positive*) pada halaman login yang sama sekali belum ter-styling (HTML mentah polos dengan font Times New Roman dan tombol default abu-abu `rgb(240, 240, 240)`).
  2. Berkas `public/hot` tertinggal (*stale*) di repositori dari sesi Vite yang mati, menyebabkan tag `@vite(...)` di template Blade merujuk ke server lokal `5173` yang tidak aktif alih-alih merujuk aset kompilasi `public/build/manifest.json`.
- **Root Cause**:
  1. Ketiadaan asersi computed style pada runner pengujian; audit hanya menguji status HTTP 200 dan accessibility tree secara buta tanpa menganalisis font-family dan background color tombol.
  2. Model AI tidak membuka dan mengevaluasi berkas tangkapan layar (screenshot PNG) secara visual menggunakan kapabilitas multimodal vision sebelum menyimpulkan hasil.
- **Fix Applied**:
  1. Membuat modul Hard Visual Guard [`scripts/visual-guard.mjs`](file:///c:/Users/TK%20ABA%20SBY%2069%20(3)/Documents/@Yasin/Semester%205/Magang-main/Magang/scripts/visual-guard.mjs) yang wajib diimpor oleh seluruh runner:
     - `assertManifestBuilt()`: Membatalkan pengujian seketika dengan status **FAIL (Vite unbuilt)** jika `public/build/manifest.json` tidak ada, serta membersihkan file `public/hot` usang.
     - `assertVisualStyles()`: Melempar `UNSTYLED_HTML_DETECTED` jika font-family elemen `body` bernilai Times New Roman/raw serif, dan melempar `DEFAULT_BUTTON_DETECTED` jika tombol utama bernilai background default `rgb(240, 240, 240)`.
  2. Memperbarui `scripts/tier1_puppeteer_audit.mjs` dan membuat runner otonom `scripts/tier3_playwright_audit.mjs` terintegrasi guard.
  3. Menanamkan mandat mutlak di `.antigravity/rules.md`: dilarang keras meloloskan status visual berdasarkan HTTP 200/AXTree, dan AI WAJIB membuka berkas gambar via `view_file` untuk memvalidasi rendering Tailwind layaknya mata manusia.
  4. Menjalankan kompilasi produksi `npm run build`, audit Tier 1 & Tier 3, serta pengambilan screenshot Playwright MCP yang terverifikasi rapi dan presisi 100%.
- **Prevention Rule**: Seluruh runner pengujian visual wajib memiliki asersi computed style terhadap token desain utama (font sans Figtree/Inter dan background Tailwind) serta memvalidasi manifest Vite. Dilarang menyimpulkan tampilan UI aman tanpa membuka raster screenshot dan memverifikasi keselarasan visual secara multimodal.

---

### [LRN-029] Validasi State Ketat Alur Seleksi Pengajuan Admin & Form Input Alasan Penolakan
- **Tanggal**: 2026-09-26
- **Komponen**: `app/Http/Controllers/Admin/ApplicationController.php`, `resources/views/admin/applications/show.blade.php`, `resources/views/admin/applications/index.blade.php`, `database/seeders/AdminWorkflowSeeder.php`, `tests/Feature/AdminWorkflowSelectionTest.php`, `scripts/tier3_playwright_audit.mjs`
- **Problem / Symptom**: 
  1. Aksi persetujuan (*Approve*) dan penolakan (*Reject*) tidak memiliki batasan status asal (*state transition guard*), sehingga pengajuan yang sudah berstatus `accepted` dapat di-reject secara sewenang-wenang atau pengajuan yang sudah `rejected` dapat di-approve kembali tanpa validasi alur seleksi yang ketat.
  2. Input alasan penolakan (`rejection_note`) bersifat opsional (`nullable`), memungkinkan admin menolak permohonan magang mahasiswa tanpa memberikan keterangan atau feedback yang konstruktif.
  3. Runner pengujian visual otomatis `npm run test:visual` sebelumnya hanya menguji portal login dan belum memvalidasi rendering tabel pengajuan admin multi-role desktop dan mobile.
- **Root Cause**:
  1. Ketiadaan *guard clause* pemeriksaan `$oldStatus === 'pending'` sebelum mengubah status menjadi `accepted` atau `rejected` di controller admin.
  2. Rule validasi Form Request tidak menerapkan `required` dinamis saat status bernilai `rejected`.
  3. Ketiadaan kartu interaktif kondisional dan pelindung penonaktifan tombol (*disabled state*) pada view `show.blade.php`.
- **Fix Applied**:
  1. **Strict State Guard Backend**: Menambahkan validasi transisi status di `ApplicationController::updateStatus`:
     - Aksi `accepted` (Approve) dan `rejected` (Reject) DITOLAK jika status aplikasi saat ini bukan `pending`.
     - Pengajuan yang berstatus `rejected` dikunci secara permanen dan tidak dapat diubah kembali statusnya.
  2. **Mandatory Rejection Reason**: Menerapkan rule validasi backend dinamis: `'rejection_note' => 'required|string|min:5'` ketika status `rejected`, disertai pesan error kustom dalam Bahasa Indonesia yang ramah pengguna.
  3. **Visual & UX Hardening di Blade View**:
     - Menambahkan banner peringatan status final bila pengajuan telah ditolak, serta menonaktifkan tombol submit dan opsi status lainnya.
     - Mengunci tombol `ACCEPTED` dan `REJECTED` dengan tooltip informatif jika status saat ini bukan `pending`.
     - Mengintegrasikan banner kesalahan validasi `@if ($errors->any())` dan display inline `@error('rejection_note')`.
     - Menambahkan atribut `:required="status === 'rejected'"` pada textarea alasan penolakan di Alpine.js.
     - Menambahkan tooltip alasan penolakan pada tabel desktop dan kartu ringkasan alasan penolakan pada tampilan mobile `index.blade.php`.
  4. **Dedicated Feature Test & Visual Audit Runner**:
     - Membuat test suite lengkap [`tests/Feature/AdminWorkflowSelectionTest.php`](file:///c:/Users/TK%20ABA%20SBY%2069%20(3)/Documents/@Yasin/Semester%205/Magang-main/Magang/tests/Feature/AdminWorkflowSelectionTest.php) menguji seluruh siklus `AdminWorkflowSeeder` (7/7 PASS, Strict Exit Code 0).
     - Mengembangkan `scripts/tier3_playwright_audit.mjs` untuk mengotomatisasi login Admin QA, navigasi tabel desktop & mobile, inspeksi status badges, evaluasi rejection-box, dan capture screenshot beresolusi tinggi dengan Hard Visual Guard.
- **Prevention Rule**: Seluruh aksi perubahan siklus hidup entitas bisnis (*state machine transitions*) wajib memvalidasi status asal secara eksplisit di level controller sebelum eksekusi transaksi database. Setiap tindakan yang berdampak penolakan atau pembatalan hak pengguna wajib mewajibkan input alasan minimal yang jelas dan informatif.

---

### [LRN-030] Penyelarasan Desain & Layout Tombol Aksi Kartu (Buat Akun vs Login As)
- **Tanggal**: 2026-09-28
- **Komponen**: `resources/views/admin/universities/index.blade.php`, `resources/views/admin/agencies/index.blade.php`
- **Problem / Symptom**:
  1. Tombol "Buat Akun" pada kartu Universitas dan Instansi Dinas yang belum memiliki akun admin portal terlihat sangat kontras dan tidak selaras (*style mismatch*): tombol bergaris batas hitam tebal (`border-slate-900 text-slate-900`) dan berubah menjadi hitam pekat saat di-hover (`hover:bg-slate-900 hover:text-white`), sedangkan kartu yang sudah memiliki akun menampilkan tombol "Login As" dengan gaya outline abu-abu lembut (`border-slate-200 text-slate-700 bg-white hover:bg-slate-50`).
  2. Pembungkus form tombol sekunder menggunakan lebar statis `w-1/3`, sehingga pada grid 4-kolom kartu yang sempit teks "Buat Akun" (9 karakter) tidak muat dalam 1 baris dan terpotong/terlipat menjadi 2 baris ("Buat \n Akun"), menyebabkan tinggi tombol membesar dan tata letak footer kartu menjadi tidak rapi (*layout jumping/misalignment*).
- **Root Cause**:
  1. Penulisan styling tombol "Buat Akun" dilakukan secara terpisah dengan token warna gelap yang tidak mengikuti design system tombol sekunder sistem.
  2. Ketiadaan kelas `whitespace-nowrap` dan penggunaan lebar fraksional `w-1/3` yang kaku alih-alih `shrink-0` dengan auto-width berbasis konten.
- **Fix Applied**:
  1. Menyelaraskan seluruh styling tombol "Buat Akun" pada `admin/universities/index.blade.php` dan `admin/agencies/index.blade.php` ke format tombol sekunder baku: `border border-slate-200 text-slate-700 bg-white hover:bg-slate-50 hover:border-slate-300 font-bold text-xs shadow-2xs`.
  2. Mengganti `w-1/3` pada form pembungkus dengan `shrink-0 m-0` serta menambahkan `whitespace-nowrap` pada tombol, sehingga teks "Buat Akun" dan "Login As" dijamin selalu 1 baris.
  3. Menambahkan `min-w-0 truncate whitespace-nowrap` pada tombol primer "Kelola Kampus" dan "Kelola Dinas", menjamin ketinggian kedua tombol sejajar presisi (`py-2.5` = 38px).
  4. Menambahkan konfirmasi interaktif `onsubmit="return confirm(...)"` dan atribut `title` yang deskriptif pada form pembuatan akun dinas.
  5. Memvalidasi tampilan visual langsung via Playwright headless engine dan `view_file` (terverifikasi 100% presisi dan rapi).
- **Prevention Rule**: Seluruh tombol aksi sekunder pada kartu ringkasan (grid cards) wajib menggunakan token outline baku `border-slate-200 text-slate-700 bg-white hover:bg-slate-50`, selalu menyematkan `whitespace-nowrap` jika berada di dalam flex container berdampingan, serta menghindari lebar fraksional kaku `w-1/3` yang rentan menyebabkan wrapping teks pada viewport sempit.

### [LRN-031] Harmonisasi Gaya Tombol Aksi Tabel & Isolasi Tema Nomor Halaman (Pagination)
- **Tanggal**: 2026-09-28
- **Komponen**: `resources/views/vendor/pagination/tailwind.blade.php`, `tailwind.config.js`, `app/Providers/AppServiceProvider.php`, `resources/css/app.css`, `resources/views/admin/users/index.blade.php`, `resources/views/admin/mentors/index.blade.php`, `resources/views/admin/units/index.blade.php`, `resources/views/admin/agencies/show.blade.php`, `resources/views/admin/universities/show.blade.php`, `resources/views/student/logbook/index.blade.php`
- **Problem / Symptom**:
  1. Nomor halaman (pagination) tampil dalam kotak hitam/gelap pekat (*pitch black*) yang mengganggu estetika tema pemerintahan Surabaya (biru/slate), serta memakai teks bahasa Inggris ("Showing 1 to 10 of 28 results") yang tidak konsisten dengan Bahasa Indonesia sistem.
  2. Tombol-tombol aksi pada tabel admin (khususnya "Login As") tampil dengan gradien oranye/merah neon yang mencolok (`linear-gradient(to right, #f59e0b, #f43f5e)`), sementara tombol Edit, Reset, dan Hapus memiliki ukuran ketinggian, border-radius, dan padding yang berbeda-beda antar halaman (`admin/users`, `admin/mentors`, `admin/units`, `admin/agencies`, `admin/universities`).
- **Root Cause**:
  1. Tailwind CSS diatur pada mode dark default (`media`), sehingga ketika OS pengguna mengaktifkan mode gelap Windows, kelas `dark:bg-gray-800 dark:border-gray-600` pada template bawaan vendor Laravel langsung diaktifkan secara otomatis.
  2. Kelas `.btn-action-login` pada `resources/css/app.css` secara historis di-hardcode dengan warna gradien oranye menyala, dan beberapa tabel masih menggunakan tombol icon-only atau kelas CSS ad-hoc (`.mbtn-green`, dll.) alih-alih utility class Tailwind eksplisit.
- **Fix Applied**:
  1. Menambahkan `darkMode: 'class'` pada `tailwind.config.js` sehingga dark mode bawaan OS tidak lagi membajak styling komponen antarmuka.
  2. Membuat custom pagination template resmi [`resources/views/vendor/pagination/tailwind.blade.php`](file:///resources/views/vendor/pagination/tailwind.blade.php) ber-bahasa Indonesia ("Menampilkan X sampai Y dari Z hasil"), tombol nomor halaman `rounded-xl`, dan indikator aktif `bg-blue-600` dengan shadow lembut.
  3. Mendaftarkan `\Illuminate\Pagination\Paginator::useTailwind()` secara eksplisit pada `app/Providers/AppServiceProvider.php`.
  4. Menstandardisasi seluruh tombol aksi tabel (`Login As`, `Edit`, `Reset`, `Hapus`) ke tinggi baku `h-[30px]`, font `text-xs font-bold`, border-radius `rounded-xl`, dan palet selaras tema dinas:
     - `Login As`: `border border-slate-200 text-slate-700 bg-white hover:bg-slate-50 hover:border-slate-300 shadow-2xs`
     - `Edit`: `border border-blue-200 bg-blue-50 text-blue-700 hover:bg-blue-600 hover:text-white shadow-2xs`
     - `Reset`: `border border-amber-200 bg-amber-50 text-amber-700 hover:bg-amber-600 hover:text-white shadow-2xs`
     - `Hapus`: `border border-rose-200 bg-rose-50 text-rose-700 hover:bg-rose-600 hover:text-white shadow-2xs`
  5. Menjalankan `npm run build`, pembersihan cache (`optimize:clear`, `view:clear`), dan visual verification Playwright headless yang divalidasi langsung via multimodal screenshot inspection. Seluruh 78 unit/feature test lulus tanpa regresi (Strict Exit Code 0).
- **Prevention Rule**: Hindari penggunaan `prefers-color-scheme: dark` tanpa konfigurasi `darkMode: 'class'`, selalu gunakan template paginasi lokal berbahasa Indonesia, dan seragamkan seluruh tombol aksi baris tabel ke spesifikasi utility baku `h-[30px] px-3 text-xs font-bold rounded-xl` untuk menjaga konsistensi visual di seluruh modul.

---

### [LRN-031] Standardisasi Dimensi & Kontras Tombol Aksi Tabel (`h-[34px] px-3.5`)
- **Tanggal**: 2026-09-28
- **Komponen**: Blade Views (`admin/users/`, `admin/mentors/`, `admin/units/`, `admin/universities/`, `admin/agencies/`, `student/logbook/`)
- **Problem / Symptom**: Tombol aksi standar tabel (`h-[30px] px-3`) terasa terlalu kecil, sempit saat disentuh/diklik, dan batas bordernya kurang kontras pada tabel dengan densitas data tinggi.
- **Root Cause**: Ukuran tinggi 30px dan padding horizontal 12px terlalu minimalis untuk tombol teks deskriptif seperti `Login As`, `Reset`, `Edit`, dan `Hapus`, serta penggunaan kelas `border-slate-200` terlihat menyatu dengan background putih.
- **Fix Applied**: 
  - Meningkatkan tinggi tombol aksi menjadi `h-[34px]`, padding horizontal `px-3.5` (14px), font `text-xs font-bold`, border-radius `rounded-xl`, dan menambahkan tactile shadow `shadow-xs`.
  - Mempertegas border tiap varian warna:
    * `Login As`: `border-slate-300 text-slate-800 bg-white hover:bg-slate-100`
    * `Edit`: `border-blue-300 bg-blue-50 text-blue-700 hover:bg-blue-600 hover:text-white`
    * `Reset`: `border-amber-300 bg-amber-50 text-amber-800 hover:bg-amber-600 hover:text-white`
    * `Hapus`: `border-rose-300 bg-rose-50 text-rose-700 hover:bg-rose-600 hover:text-white`
  - Memperbarui seluruh tabel di modul Users, Mentors, Units, Universities Show, Agencies Show, dan Student Logbook.
- **Prevention Rule**: Selalu gunakan ukuran baku `h-[34px] px-3.5 text-xs font-bold rounded-xl shadow-xs` untuk tombol aksi baris tabel utama dan perjelas kontras border minimal skala 300 (`border-*-300`).

---

### [LRN-032] Penyelarasan Pusat Kendali Instansi Dinas dengan Gaya Simpel Universitas & Pembersihan Badging Redundan
- **Tanggal**: 2026-09-28
- **Komponen**: Blade View (`resources/views/admin/agencies/show.blade.php`), Hero & Tab Navigation
- **Problem / Symptom**: 
  1. Halaman detail instansi dinas terasa tidak selaras dengan halaman perguruan tinggi (universities show);
  2. Data kontak (email, telepon, website) bertumpuk/terhimpit tanpa pemisah yang jelas (`diskominfo@surabaya.go.id(031) 5312144diskominfo.surabaya.go.id`);
  3. Badge memuat angka yang membingungkan `Akun Terdaftar (3)` dan tab memuat angka statis `Personil & Akun Kedinasan 7`.
- **Root Cause**: Tampilan `admin/agencies/show.blade.php` sebelumnya menggunakan kartu putih biasa tanpa hirarki pejabat yang rapi, elemen kontak tidak dibungkus flex item terisolasi dengan ikon SVG, dan badge/tab masih menyertakan count statis/redundant di labelnya.
- **Fix Applied**: 
  1. Mengadopsi tata letak Pusat Kendali Universitas dengan Hero Card gradien navy resmi (`linear-gradient(135deg, #09172e 0%, #0d2857 50%, #07152c 100%)`) dilengkapi watermark perisai kedinasan Pemkot Surabaya.
  2. Menghapus angka `(3)` pada badge instansi menjadi `● Akun Terdaftar` yang bersih dengan dot pulse status.
  3. Menghapus angka `7` pada tab `Personil & Akun Kedinasan` dan menstandardisasi tab menjadi bare navigation bar di atas garis pembatas border yang rapi.
  4. Mengisolasi kontak (email, telepon, website) ke dalam wadah flex berjarak longgar (`gap-4`) dengan ikon SVG masing-masing dan teks host domain yang bersih.
  5. Mengintegrasikan kartu Pejabat Penandatangan Resmi di sisi kanan hero card lengkap dengan tombol primer `Masuk Sebagai Admin Dinas (Login As)`.
- **Prevention Rule**: Desain pusat kendali entitas mitra (Dinas maupun Universitas) wajib konsisten dalam hirarki visual hero card, tidak boleh menampilkan angka mentah berulang pada badge/tab jika sudah diwakili oleh kartu metrik ringkasan, dan kontak wajib memiliki layout modular ber-ikon.

---

### [LRN-033] Ekosistem Browser: Penguncian Google Chrome Lokal (channel: chrome) & Eliminasi Driver 404 IDE
- **Tanggal**: 2026-09-28
- **Komponen**: Testing Engine (`scripts/tier3_playwright_audit.mjs`, `scripts/browser-runner.mjs`, `scripts/verify_buttons.mjs`, `.antigravity/rules.md`, `AGENTS.md`)
- **Problem / Symptom**: 
  1. Tool browser bawaan IDE (`open_browser_url`) mengalami kegagalan fatal saat mengunduh driver Playwright 1.57.0 karena URL CDN Microsoft sudah berstatus HTTP 404.
  2. Agen keliru memanggil tool bawaan yang rusak tersebut alih-alih runner lokal, menyebabkan kegagalan observasi visual antarmuka nyata.
- **Root Cause**: Driver internal Playwright di IDE terikat pada versi statis 1.57.0 yang sudah kadaluwarsa di repositori CDN Microsoft, sedangkan runner lokal di terminal memiliki akses langsung ke executable Google Chrome Windows (`channel: 'chrome'`).
- **Fix Applied**: 
  1. Mengunci opsi peluncuran Chromium pada seluruh runner (`scripts/browser-runner.mjs`, `scripts/tier3_playwright_audit.mjs`, `scripts/verify_buttons.mjs`) menggunakan `{ headless: true, channel: 'chrome' }` tanpa mengunduh driver eksternal.
  2. Menambahkan `PROTOKOL MATA MANUSIA ASLI & LARANGAN TOOL 404` ke dalam `.antigravity/rules.md` dan `AGENTS.md`: melarang keras pemanggilan tool bawaan `open_browser_url` dan mewajibkan runner lokal `npm run test:visual` atau `node scripts/browser-runner.mjs`.
  3. Mengarahkan output tangkapan layar langsung ke `public/test-artifacts/` sekaligus disalin ke folder artefak aktif.
  4. Memvalidasi secara visual (Multimodal Vision `view_file`) untuk rute `/login`, `/admin/applications`, dan `/admin/users` (desktop 1920x1080 dan mobile 375x812) dengan Strict Exit Code 0.
- **Prevention Rule**: Jangan pernah memanggil tool bawaan `open_browser_url`. Setiap pengujian UI wajib menggunakan runner skrip lokal berbasis Node.js dengan opsi `channel: 'chrome'`, dan agen wajib menyajikan bukti tangkapan layar langsung (inline Markdown image) di chat.

---

### [LRN-034] Hero Pusat Kendali Univ & Dinas: Navy Gelap → Kartu Putih Bersih (Menggantikan LRN-032)
- **Tanggal**: 2026-09-29
- **Komponen**: Blade View (`resources/views/admin/universities/show.blade.php`, `resources/views/admin/agencies/show.blade.php`)
- **Problem / Symptom**: Hero card gradien navy (`#09172e → #0d2857`) terlalu mencolok dan tidak selaras dengan tema terang aplikasi kedinasan; tab universitas masih memuat badge angka (`cc-count`); tombol aksi tabel tidak seragam (hover border hilang, tabel Mahasiswa memakai ikon polos, DPL tanpa Edit, Personil Dinas tanpa Hapus).
- **Root Cause**: LRN-032 menetapkan hero navy sebagai standar; varian tombol ditulis manual per baris sehingga tiap tabel menyimpang.
- **Fix Applied**:
  1. Hero: `bg-white rounded-2xl border border-slate-200/80 p-6 md:p-8 shadow-xs`, soft badge (`bg-*-50 text-*-700 border-*-200/60`), ikon kontak `text-slate-400`, logo `border border-slate-200 rounded-xl p-2 bg-white shadow-2xs`, kartu PIC `bg-slate-50 border border-slate-200/80 rounded-xl p-5`, tombol Login As `bg-emerald-600 hover:bg-emerald-700 ... rounded-lg shadow-xs transition-colors`. Watermark perisai dihapus.
  2. Tab murni teks + ikon (span `cc-count` dihapus).
  3. Tombol aksi baris: base `inline-flex items-center justify-center px-3 py-1.5 text-xs font-medium rounded-lg border transition-all duration-150` + varian Login As (slate), Edit (blue), Reset (amber-50/700 → hover amber-500), Hapus (rose), semuanya dengan `hover:border-*`. DPL mendapat Edit (`admin.users.edit` + `return_to`), Personil Dinas mendapat Hapus (`admin.users.destroy` + `return_to`), tabel Mahasiswa memakai tombol teks `Detail` / `Logbook`.
- **Tambahan (Keterbacaan)**: Badge status 10px, header tabel 10–11px, dan label status berbahasa Inggris (`ucfirst($status)` → "Rejected") sulit dibaca. Skala baru: badan tabel `text-[13px]`, header/badge/teks sekunder minimal `text-xs` (12px) dengan `whitespace-nowrap` pada pill, angka kuota `text-base font-bold`, label status via `ApplicationStatus::tryFrom($s)?->label()`. Setelah menaikkan ukuran, ukur `table.scrollWidth` vs lebar wadah di 1280/1440/1920 agar kolom tidak terpotong.
- **Prevention Rule**: Hero pusat kendali entitas mitra wajib kartu putih netral (bukan gradien gelap). Jangan pakai `text-[10px]`/`text-[11px]` untuk teks informatif di desktop. Tombol aksi baris tabel di halaman show memakai base style tombol `Detail` di atas — ini menggantikan ukuran `h-[34px] rounded-xl font-bold` (LRN-031) khusus untuk halaman pusat kendali.

---

### [LRN-035] Standarisasi Konfirmasi Aksi: Hapus Semua `confirm()` / `alert()` Bawaan Browser
- **Tanggal**: 2026-09-29
- **Komponen**: `resources/views/components/confirm-action-modal.blade.php` (baru), `layouts/app.blade.php`, `resources/js/app.js`, serta view admin/agencies, admin/universities, lecturer/logbooks, student/logbook, university/lecturers
- **Problem / Symptom**: Tombol Reset/Hapus/Buat Akun/Minta Revisi di beberapa halaman masih memakai `onsubmit="return confirm(...)"` sehingga muncul popup bawaan browser ("127.0.0.1:8000 says"), tidak selaras dengan modal konfirmasi resmi di halaman Users. Validasi berkas juga memakai `alert()`.
- **Root Cause**: Modal global (`confirm-reset-modal`, `confirm-delete-modal`) hanya dipakai di sebagian halaman; belum ada modal untuk aksi non-destruktif, dan belum ada helper notifikasi.
- **Fix Applied**:
  1. Reset password → `$dispatch('open-reset-modal', {action, name, email, role})`; Hapus → `$dispatch('open-delete-modal', {action, title, name, desc})`.
  2. Komponen baru `x-confirm-action-modal` (event `open-confirm-modal`) untuk aksi non-destruktif. Bisa kirim ke URL (`action`) atau form yang sudah ada (`form: $el.form`, `submitter: $el` agar `name/value` tombol ikut terkirim). Memakai `form.submit()` (bukan `requestSubmit`) supaya modal tidak terbuka ulang.
  3. `window.notify(message, type)` di `app.js` → toast layout (`[data-toast-root]`); menggantikan 20 `alert()`.
- **Prevention Rule**: Dilarang memakai `confirm()`, `alert()`, `prompt()` bawaan browser. Gunakan 3 modal global di atas dan `notify()`. Untuk URL di payload `$dispatch`, tulis `'{{ route(...) }}'` (bukan `@js(route(...))`) karena `@js` meng-escape `/` menjadi `\/` sehingga test `assertSee/assertDontSee($url)` menjadi tidak valid; `@js()` tetap dipakai untuk teks bebas (nama, deskripsi).

---

### [LRN-036] Prioritas Tindakan, Nilai Resmi, & Bug Enum vs String di Pusat Kendali Dinas/Kampus
- **Tanggal**: 2026-09-29
- **Komponen**: `Application` (actionPriority/actionHint/statusValue/scopeOccupyingQuota), `Evaluation` (memo getUniversity), `Unit` (occupied_count), `Admin\{University,Agency,Unit}Controller`, view `admin/universities/show`, `admin/agencies/show`, `admin/units/*`, `admin/applications/show`
- **Problem / Symptom**:
  1. Daftar mahasiswa/pengajuan diurutkan `latest()` saja, sehingga yang butuh tindakan tercecer di tengah.
  2. Kolom Nilai di detail kampus tampil `0.0` untuk mahasiswa lulus (seharusnya 95.0 / 91.7).
  3. Metrik "Aktif Magang" = 0, "menunggu seleksi" = 0, tombol "Tugaskan DPL" tak pernah muncul, tab Mahasiswa Aktif dinas & "terisi" divisi tidak menghitung status `active`, halaman Divisi selalu "0 terisi".
  4. Divisi berisi mahasiswa `active` bisa dihapus → `applications.unit_id ON DELETE CASCADE` ikut menghapus pengajuannya.
- **Root Cause**:
  1. Kolom decimal `final_score` bernilai string `"0.00"` yang *truthy*, jadi `!$fScore` tidak pernah jatuh ke rumus bobot; view juga menghitung ulang nilai sendiri dari kolom lama `nilai_akademik`.
  2. `status` di-cast ke `ApplicationStatus`: `$app->status === 'accepted'` dan `Collection::where('status', 'accepted')` selalu false/0.
  3. Kode lama menganggap "aktif" = `accepted` (sebelum status `active` ada).
- **Fix Applied**:
  1. `Application::actionPriority($requireAdvisor)` + `actionHint()` (1 verifikasi → 2 siap diluluskan → 3 pembimbing belum ada → 4 aktif → 5 diterima → 6 selesai → 7 belum mengajukan → 8 ditolak/mundur); daftar di kedua pusat kendali diurutkan dengan ini + filter "Perlu Tindakan".
  2. Nilai tabel/rata-rata/CSV memakai accessor resmi `nilai_akhir`, `nilai_dosen_calculated`, `grade_calculated`, `is_complete`; nilai belum lengkap tampil "Menunggu nilai dosen/mentor".
  3. Semua perbandingan status memakai `$app->statusValue()`; kuota terisi = `Application::scopeOccupyingQuota()` / `Unit::occupied_count`; guard hapus divisi & validasi kuota server-side memakai `Application::QUOTA_STATUSES`.
  4. `Evaluation::getUniversity()` di-memo per instance + `useUniversity($univ)` untuk halaman kampus (menghilangkan query berantai per baris).
  5. Regresi: `tests/Feature/ActionPriorityDisplayTest.php`.
- **Prevention Rule**: Jangan hitung nilai akhir di view/controller — selalu pakai accessor `Evaluation`. Jangan bandingkan `status` dengan string secara langsung (termasuk `Collection::where`); pakai `statusValue()` atau enum case. Definisi "aktif" = status `active`; "menempati kuota" = `accepted` + `active` yang belum berakhir (`scopeOccupyingQuota`).

---

### [LRN-037] Perlindungan Arsip Alumni, Status Nonaktif, & Standar Urutan Prioritas Lintas Halaman
- **Tanggal**: 2026-09-29
- **Komponen**: `User` (hasInternshipHistory/isInactive/pembimbingPlacements), `EnsureAccountIsActive` (middleware web + alias `active` di API), `LoginRequest`, `Api\AuthController`, controller hapus (`Unit`, `Agency`, `Mentor`, `University@destroyDosen`, `University\LecturerController`, `UserController`, `ProfileController`), `Application` (actionPrioritySql/scopeOrderByActionPriority/scopeRequiringAction/actionSortKey), `Admin\ApplicationController@index`, view `admin/applications/index`, `student/application/create`
- **Problem / Symptom**:
  1. Menghapus divisi/instansi yang punya alumni ikut menghapus pengajuan, logbook, nilai, dan sertifikat (`applications.unit_id ON DELETE CASCADE`); guard hanya mengecek status accepted/verified/pending. Mahasiswa alumni bisa menghapus akunnya sendiri (cascade seluruh arsip). Menghapus mentor/dosen yang pernah membimbing mengosongkan namanya di arsip (`SET NULL`). `UserController` tidak memeriksa `pembimbing_id`.
  2. Status "Nonaktif" tidak mencegah login, jadi tidak ada cara mencabut akses akun yang wajib diarsipkan.
  3. Halaman Pengajuan utama memakai urutan SQL berbeda dari pusat kendali (tanpa tingkat "pembimbing belum ditetapkan", terbaru dulu untuk antrean) dan badge Inggris ("PENDING").
  4. Sisa bug enum vs string: banner ditolak/mengundurkan diri & warna status di halaman pengajuan mahasiswa tak pernah benar; notifikasi "belum ada DPL" mengabaikan status `active`.
- **Fix Applied**:
  1. Semua jalur hapus menolak bila ada riwayat magang apa pun (bukan hanya yang aktif): divisi/instansi (`applications` apa pun), mentor/dosen/user/profil (`User::hasInternshipHistory()` = pengajuan + mentor_id + pembimbing_id + academic_advisor_id). Pesan mengarahkan ke alternatif: kuota 0 (tutup pendaftaran) atau status Nonaktif.
  2. `User::isInactive()` (Super Admin/Admin Sistem dikecualikan agar tidak terkunci) diblokir di form login, login API, dan setiap request (middleware memutus sesi & "Ingat saya"; "Login As" tetap boleh).
  3. Satu standar prioritas: `Application::actionPriority()` (PHP, untuk keterangan) = `actionPrioritySql()` (SQL, untuk halaman berpaginasi), dikunci test kesetaraan per tingkat & urutan. Tingkat 1–3 (perlu tindakan) diurutkan antrean terlama dulu; lainnya terbaru dulu. DPL wajib mengikuti `universities.require_dpl` mahasiswa. Dipakai di halaman Pengajuan, pusat kendali dinas & kampus; filter "Perlu Tindakan" di halaman Pengajuan & kampus.
- **Prevention Rule**: Data magang yang sudah punya riwayat tidak boleh dihapus permanen — tambahkan guard `hasInternshipHistory()`/riwayat pengajuan pada setiap fitur hapus baru, dan gunakan status Nonaktif untuk mencabut akses. Urutan daftar pengajuan wajib memakai `orderByActionPriority()` (SQL) atau `Application::actionSortKey()` (koleksi); jangan menulis `orderByRaw` prioritas sendiri. Bila mengubah `actionPriority()`, ubah `actionPrioritySql()` bersamaan (test `ActionPriorityDisplayTest` akan gagal bila berbeda).

### [LRN-038] Nama status pengajuan berbeda di tiap role & nilai status hantu
- **Tanggal**: 2026-09-29
- **Komponen**: `App\Enums\ApplicationStatus`, `components/status-badge.blade.php`, 13 view lintas role, `NotificationService`, `tailwind.config.js`
- **Problem / Symptom**:
  1. Satu status tampil dengan 5–6 nama tergantung halaman (mis. `active` = "ACTIVE" di mentor/dosen, "Sedang Magang" di kampus, "AKTIF (Sedang Magang)" di mahasiswa, "Magang Aktif" di admin). Halaman Pengajuan lama bahkan menimpa status dengan "SIAP LULUS"/"MENUNGGU NILAI" yang tidak ada di sistem.
  2. Dashboard mahasiswa menganggap `verified` = diterima ("Pengajuan diterima! Silakan pilih DPL") padahal memilih DPL baru bisa setelah `accepted`.
  3. Notifikasi logbook (admin dinas, mentor) & laporan akhir (dosen) mencari status `submitted` yang tidak pernah disimpan (yang tersimpan `pending`) → notifikasi tidak pernah muncul. Nilai `canceled`/`submitted` pengajuan dicek di 10 file padahal bukan status resmi.
- **Root Cause**: Tiap view menulis daftar nama & warna status sendiri; tidak ada satu komponen render. Warna badge ada di enum PHP yang tidak dipindai Tailwind.
- **Fix Applied**: `label()` = kode sistem huruf kapital (`strtoupper(value)`), `description()` = keterangan Indonesia, `resolve()` untuk enum/string mentah. Semua badge lewat `<x-status-badge>` (ringkas: kode + tooltip keterangan, untuk tabel/area sempit; `stacked`: kode + keterangan di baris kedua, untuk kartu lapang — tanpa format "Nama (Kode)"). Kartu statistik yang menghitung satu status memakai kodenya; kartu gabungan memakai nama metrik dengan keterangan kode yang dihitung. Filter kampus kini persis per kode status terbaru. `app/Enums` ditambahkan ke `content` Tailwind. Nilai hantu dihapus; notifikasi memakai `pending`. `actionHint()` tidak lagi mengulang badge untuk PENDING/VERIFIED.
- **Lanjutan (2026-09-29)**: Status review logbook (`status`, `lecturer_status`) & laporan akhir memakai standar yang sama lewat `ReviewStatus` (`label()` = PENDING/APPROVED/REJECTED/REVISION, `description()`, `dotColor()`, `resolve()`) dan `<x-status-badge type="review">`. Filter laporan dosen kini per kode (dulu "pending" diam-diam memuat `revision`). Aturan transisi: ACCEPTED/REJECTED boleh dari PENDING **atau VERIFIED** (sebelumnya hanya PENDING, bertentangan dengan alur pending → verified → accepted).
- **Prevention Rule**: Jangan menulis nama/warna status pengajuan maupun status review di view — selalu `<x-status-badge>` (tambah `type="review"` untuk logbook/laporan) atau `Enum::X->label()/description()`. Status yang dibandingkan di kode hanya 7 nilai enum (`ApplicationStatus::values()`); status logbook/laporan hanya nilai `ReviewStatus`. Test `StatusLabelConsistencyTest` membuka satu pengajuan dari 10 halaman lintas role dan gagal bila nama berbeda.

---

### [LRN-039] Fitur Chat Antar Role & Error JSON pada Endpoint Fetch di Route Web
- **Tanggal**: 2026-09-30
- **Komponen**: `bootstrap/app.php`, `app/Services/Chat/*`, `app/Http/Controllers/Chat/*`, `resources/js/chat.js`, `resources/js/chat-notifier.js`, `resources/views/chat/index.blade.php`, `tests/Feature/Chat/*`
- **Problem / Symptom**: Saat membangun fitur chat, request `fetch` ke `/chat/api/...` dengan `Accept: application/json` yang gagal validasi dibalas **302 redirect** ke halaman sebelumnya, bukan 422 JSON. Test `assertUnprocessable()` gagal dengan `Call to a member function all() on array`, dan di browser pesan error tidak bisa ditampilkan.
- **Root Cause**: `withExceptions()->shouldRenderJsonWhen(fn ($r) => $r->is('api/*'))` **menggantikan** deteksi bawaan `expectsJson()`. Semua route di luar `api/*` selalu dirender sebagai HTML/redirect walaupun klien meminta JSON.
- **Fix Applied**: Kondisi diperluas menjadi `$r->is('api/*') || ($r->is('chat/*') && $r->expectsJson())`. Fitur chat tahap 1: percakapan 1-on-1 berdasarkan relasi magang (`ChatContactDirectory`, dua arah), satu lampiran per pesan di disk private, polling adaptif (tanpa WebSocket), badge navbar + toast + entri lonceng yang digabung per percakapan (`ChatNotifier`), dan mode Login As hanya-baca. Tercakup 23 test di `tests/Feature/Chat`.
- **Prevention Rule**: Endpoint JSON baru yang dipanggil via `fetch` dari halaman web wajib ditambahkan ke kondisi `shouldRenderJsonWhen` di `bootstrap/app.php` (atau diletakkan di `routes/api.php`). Aturan siapa-boleh-chat-siapa hanya boleh diubah di `ChatContactDirectory`, dan test simetri `ChatContactDirectoryTest` wajib tetap hijau. Batas upload chat mengikuti `upload_max_filesize` php.ini (default 2 MB). Jangan menaikkan `config/chat.php` tanpa menaikkan php.ini juga.

---

### [LRN-040] Chat Tahap Lengkap: `:style` String Menimpa `x-show`, Observer Penempatan, & Vite Dev Server Basi
- **Tanggal**: 2026-09-30
- **Komponen**: `resources/views/chat/partials/*.blade.php`, `resources/js/chat/*`, `app/Observers/PlacementChatObserver.php`, `app/Services/Chat/ChatGroupService.php`, `database/migrations/2026_09_30_010000_upgrade_chat_full_features.php`
- **Problem / Symptom**:
  1. Di grup, nama pengirim (teks berwarna) muncul di atas gelembung pesan **milik sendiri**, padahal `x-show="item.showName"` bernilai false.
  2. Setelah file JS diganti/dipindah (`resources/js/chat.js` → `resources/js/chat/app.js`), halaman memunculkan puluhan error Alpine `listFilter is not defined`, `$store.chatPrefs` undefined. Vite dev server yang sedang berjalan masih menyajikan `app.js` lama, bahkan file yang sudah dihapus.
- **Root Cause**:
  1. Binding Alpine `:style` berbentuk **string** (`` :style="`color: ${x}`" ``) memanggil `setAttribute('style', …)` dan menimpa `display: none` yang dipasang `x-show` pada elemen yang sama.
  2. Watcher Vite (chokidar) di path Windows ber-spasi/berkurung tidak mendeteksi perubahan, sehingga cache transform lama tetap disajikan selama `public/hot` ada.
- **Fix Applied**:
  1. Semua `:style` di tampilan chat & toast layout memakai **bentuk objek** (`:style="{ color: x }"`, `{ backgroundColor: x }`), yang menggabungkan style tanpa menghapus `display` dari `x-show`.
  2. Uji browser memakai hasil `npm run build` (intersep aset dev server di Playwright). Pengembang cukup me-restart `npm run dev` / `composer dev` setelah menarik perubahan struktur JS.
  3. Fitur tahap lengkap: grup buatan staf (admin grup, tambah/keluarkan anggota, keluar grup, promosi admin otomatis), **Grup Bimbingan otomatis** per penempatan via `Placement::observe(PlacementChatObserver)` (`ShouldHandleEventsAfterCommit` + try/catch, sehingga alur inti seleksi tidak ikut gagal), balas/kutip, hapus untuk semua (isi & file dibuang, tersisa penanda), laporkan pesan → tiket `laporan_chat` + panel moderasi Super Admin di `feedbacks/show`, maks. 5 lampiran/pesan (tabel `chat_attachments`), voice note (MediaRecorder), status dibaca per anggota, online/terakhir dilihat, sedang mengetik (cache), bisukan/sematkan, notifikasi desktop + suara, serta tombol "Chat" kontekstual (`<x-chat-button>`, `<x-chat-group-button>`).
- **Prevention Rule**: Jangan pernah memakai `:style` berbentuk string pada elemen yang juga memakai `x-show`; gunakan bentuk objek. Setelah mengganti/memindah file JS, restart Vite dev server atau hapus `public/hot` usang sebelum menguji. Setiap efek samping lintas modul yang dipicu event model (observer) wajib `ShouldHandleEventsAfterCommit` + try/catch + `report()` agar tidak membatalkan transaksi inti.

---

### [LRN-041] Info Kontak Chat Berbasis Hubungan Magang, Nomor Staf yang Terbuang, & Perbaikan Notifikasi Chat
- **Tanggal**: 2026-09-30
- **Komponen**: `ChatContactDirectory::personalDetailsVisibleTo()`, `ChatPresenter::contactDetails()`, `resources/views/chat/partials/contact-details.blade.php`, `ProfileController@updatePhone` (`profile.phone.update`), migrasi `2026_09_30_030000_add_phone_to_users_table`, `resources/js/chat/notifier.js`, `resources/views/chat/partials/sidebar.blade.php`, `layouts/navigation.blade.php`
- **Problem / Symptom**:
  1. Info Kontak chat belum menampilkan email & telepon, dan nomor dosen yang diisi Super Admin di form tambah dosen (`storeDosen`) **tidak pernah tersimpan**.
  2. Dropdown "Pengaturan notifikasi" di halaman chat terpotong di sisi kiri ("otifikasi desktop").
  3. Penanda pesan terakhir yang sudah di-toast disimpan di satu kunci localStorage untuk semua akun, jadi saat "Login As" atau ganti akun di browser yang sama, toast bisa hilang atau salah. Pesan chat di lonceng navbar juga tenggelam di bawah pemberitahuan otomatis (hanya 4 teratas yang tampil).
- **Root Cause**:
  1. Tabel `users` tidak punya kolom `phone`; `User::create([... 'phone' => ...])` diam-diam membuang atribut yang tidak ada di `$fillable`.
  2. Panel `absolute right-0 w-72` di tombol kecil melewati tepi kiri kartu yang `overflow-hidden`.
  3. Kunci `chat:last-seen-message-id` tidak memuat id pengguna; `array_slice(..., 0, 4)` pada urutan bawaan NotificationService (item otomatis dulu, notifikasi DB terakhir).
- **Fix Applied**:
  1. Kolom `users.phone` (nullable) + `$fillable`; staf mengisi nomor di kartu "Nomor Kontak untuk Chat" di Pengaturan Akun (mahasiswa tetap memakai `student_profiles.phone`). Data per role: mahasiswa = email, HP, NIM, prodi, fakultas, semester; staf = email & nomor akun; plus kontak resmi dinas/kampus (selalu tampil).
  2. **Privasi**: data pribadi hanya dikirim server bila `personalDetailsVisibleTo()` mengizinkan, yaitu mode ketat `contactsQuery(strict: true)`. Aturannya sama dengan aturan kontak chat, tetapi dosen ↔ mahasiswa sekampus hanya bila dosen tersebut DPL-nya. Super Admin melihat semua. Tanpa izin, API mengirim `visible=false` dan email/telepon/NIM `null` (bukan sekadar disembunyikan di tampilan).
  3. Panel notifikasi diposisikan selebar sidebar (`absolute left-3 right-3` terhadap header), berisi status izin browser & tombol tes suara. Kunci localStorage/sessionStorage memuat `data-chat-user`. Toast ringkasan "Anda memiliki N pesan belum dibaca" muncul sekali per sesi. Notifikasi desktop juga muncul saat jendela tidak fokus. Pesan chat belum dibaca didahulukan di lonceng navbar. Saat Login As, jumlah belum dibaca di daftar tidak di-nol-kan (konsisten dengan badge navbar).
- **Prevention Rule**: Data pribadi (email, telepon, NIM, alamat) di fitur apa pun wajib difilter di server lewat `personalDetailsVisibleTo()` (atau aturan setara), bukan dengan menyembunyikan elemen di Blade/Alpine. Setiap `Model::create()` dengan field baru wajib dicek kolom & `$fillable`-nya, karena atribut yang tidak dikenal dibuang tanpa error. Kunci localStorage/sessionStorage yang menyimpan status per pengguna wajib memuat id pengguna. Dropdown di dalam kontainer `overflow-hidden` wajib diposisikan relatif ke kontainer yang cukup lebar (atau `fixed`).

---

## 4. Format Template Entri Masalah Baru (Gunakan Format Ini)


```markdown
### [LRN-XXX] [Judul Singkat Masalah / Fitur]
- **Tanggal**: YYYY-MM-DD
- **Komponen**: [Nama Controller / Model / View / Migration / Script]
- **Problem / Symptom**: [Pesan error terminal, stack trace, kode status HTTP, atau kegagalan query PostgreSQL]
- **Root Cause**: [Penyebab teknis mendalam mengapa bug/error tersebut terjadi]
- **Fix Applied**: [Solusi, patch berkas, atau refactoring kode yang telah berhasil memecahkan masalah]
- **Prevention Rule**: [Aturan preventif baru yang wajib dipatuhi agen di masa mendatang]
```
