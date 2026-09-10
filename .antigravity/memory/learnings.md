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
