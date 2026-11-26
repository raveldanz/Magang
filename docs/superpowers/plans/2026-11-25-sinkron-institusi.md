# Rencana Kerja: Data Sekolah dan Kampus Satu Sumber

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Pencarian sekolah/kampus hanya membaca database; API hanya dipakai sinkron (tombol Super Admin dan jadwal mingguan).

**Architecture:** Satu service sinkron yang berjalan per langkah (satu halaman API = satu langkah). Command terjadwal menjalankan langkahnya berurutan secara langsung; tombol admin menjalankannya sebagai rantai job antrean. Perubahan nama/alamat dan kampus baru disimpan sebagai usul yang diputuskan admin. Data awal sekolah dibawa sebagai file terkompresi.

**Tech Stack:** Laravel 13, PHPUnit 12, Blade + Alpine.js + Tailwind, antrean `database`, PostgreSQL (dev/demo), SQLite (tes).

**Spec:** `docs/superpowers/specs/2026-11-25-sinkron-institusi-design.md`

## Global Constraints

- Basis kerja `origin/main` (`01c4769` atau lebih baru), branch `sinkron-institusi`. Commit lokal saja; **push dan PR menunggu izin**.
- Pesan commit tanpa atribusi Claude; pesan multi-baris lewat `git commit -F`.
- Teks tampilan berbahasa Indonesia.
- Migrasi harus jalan di PostgreSQL dan SQLite.
- Semua aksi sinkron dan usul memakai `ensureSuperAdmin()`.
- Sinkron tidak pernah menghapus institusi dan tidak mengubah `is_verified`, `status`, `institution_type` pada baris yang sudah ada.
- Blade: petakan model menjadi array sebelum `@json`.
- Selesai = `php -l` dan `vendor/bin/pint --test` bersih serta `php artisan test` keluar 0.

## Review Focus

1. Tombol ditekan dua kali, atau jadwal jalan saat tombol sedang berjalan: hanya satu sinkron. (Task 3)
2. Antrean mati: halaman memberi tahu, proses macet bisa dimulai ulang, job lama tidak jalan lagi. (Task 5)
3. API membalas 200 tetapi semua jenjang kosong: dianggap gagal, waktu sinkron tidak dicatat. MA saja yang kosong tetap sukses. (Task 3)
4. Sinkron pertama setelah seeder membanjiri usul karena beda format nama: nama API yang sama dengan `acronym` (nama resmi) tidak diusulkan. (Task 4)
5. Nama yang diketik manual cocok dengan beberapa sekolah bernama sama: pengguna diminta memilih, bukan dipasangkan sembarang. (Task 2)

## Peta Berkas

| Berkas | Tanggung jawab |
|---|---|
| `app/Services/Institutions/InstitutionSyncService.php` (baru) | Mulai, jalankan langkah, selesai/gagal, penguncian |
| `app/Services/Institutions/ChangeProposalService.php` (baru) | Membuat dan memutuskan usul |
| `app/Services/Institutions/SchoolSnapshotService.php` (baru) | Ekspor/impor data awal |
| `app/Models/InstitutionSyncRun.php`, `InstitutionChangeProposal.php` (baru) | Status sinkron dan usul |
| `app/Jobs/RunInstitutionSyncStep.php` (baru) | Satu langkah di antrean |
| `app/Http/Controllers/Admin/InstitutionSyncController.php` (baru) | Tombol, status, usul |
| `resources/views/admin/universities/partials/sync-panel.blade.php`, `resources/views/admin/universities/proposals.blade.php` (baru) | Tampilan |

---

### Task 0: Siapkan ruang kerja

- [ ] Pastikan Mas Yasin sudah memberi tahu Evan dan Raveldo bahwa bagian API kampus akan diubah.
- [ ] Pakai superpowers:using-git-worktrees. Buat worktree dari `origin/main` dengan branch `sinkron-institusi`. Jangan pindah branch di folder `Magang` (ada berkas yang belum dilacak).
- [ ] Salin (bukan junction) `vendor/` dan salin `.env` ke worktree. Salin folder `docs/superpowers/` ke worktree lalu commit `docs: rancangan dan rencana sinkron institusi`.
- [ ] Jalankan `php artisan test`. Catat jumlah lulus/gagal sebagai patokan; bila ada yang gagal, laporkan sebelum lanjut.

---

### Task 1: Pencarian tanpa API

**Files:**
- Modify: `app/Http/Controllers/InstitutionSearchController.php` (hapus blok saran API dan method `resolve`)
- Modify: `app/Services/MasterUniversityService.php` (`search()`: hapus langkah 3 dan 4)
- Modify: `app/Services/Directory/SchoolDirectoryClient.php`, `CollegeDirectoryClient.php` (hapus `search`, `cached`, kunci cache per item)
- Modify: `routes/web.php` (hapus `institutions.resolve`)
- Modify: `resources/views/components/searchable-select.blade.php` (`select()` kembali sinkron, tanpa `resolve_url`)
- Modify: `resources/views/student/profile/edit.blade.php` (tambah `custom-label`)
- Modify: `resources/views/admin/universities/index.blade.php:251` (judul menjadi `Ditemukan di Data Bawaan`)
- Modify: `app/Models/University.php`
- Test: `tests/Feature/InstitutionSearchLocalOnlyTest.php` (baru), `ExternalInstitutionSyncTest.php`, `CollegeDirectoryApiTest.php`, `AdminUniversityQuickImportTest.php`

**Interfaces:**
- Produces: `University::cityLabel(): ?string` (segmen kedua dari belakang `address` bila ada minimal 3 segmen dipisah koma, selain itu `null`). `meta` hasil pencarian = `code` dan `cityLabel()` digabung ` · `.

- [ ] **Tulis tes yang gagal.** Setiap tes mengaktifkan kedua direktori dengan base URL `https://dir.test` lalu `Http::fake()`.

```php
public function test_pencarian_login_tidak_memanggil_api(): void
// GET institutions.search q='tidak ada sama sekali' → 200, data = [] ; Http::assertNothingSent();

public function test_master_search_publik_tidak_memanggil_api(): void
// GET universities.master-search q='tidak ada' → 200 ; Http::assertNothingSent();

public function test_hasil_sekolah_menampilkan_kota(): void
// sekolah code '90000001', address 'Jl. A, Surabaya, Jawa Timur' → data.0.meta === '90000001 · Surabaya'

public function test_route_resolve_sudah_tidak_ada(): void
// $this->assertFalse(Route::has('institutions.resolve'));
```

- [ ] Jalankan `php artisan test --filter=InstitutionSearchLocalOnlyTest` → FAIL.
- [ ] Implementasikan. Label manual: `[+] Sekolah/kampus tidak ada? Tambahkan manual`.
- [ ] Hapus tes yang menguji perilaku lama: di `ExternalInstitutionSyncTest` tes `pencarian_menambahkan_sekolah_dari_api...`, `get_pencarian_read_only_dan_resolve...`, `saran_direktori_hanya_jenjang_magang...`; di `CollegeDirectoryApiTest` tiga tes pertama. Di `AdminUniversityQuickImportTest` ganti `assertSee` judul menjadi `Ditemukan di Data Bawaan`.
- [ ] Jalankan `php artisan test` → semua lulus. Commit `perf: pencarian sekolah dan kampus hanya membaca database`.

---

### Task 2: Aturan simpan sekolah, input manual, dan "Tambahkan ke Mitra"

**Files:**
- Modify: `app/Services/MasterUniversityService.php` (`upsertDirectorySchool`)
- Modify: `app/Http/Controllers/Admin/UniversityController.php` (`quickImport`)
- Modify: `app/Http/Controllers/Student/ProfileController.php` (`resolveUniversity`)
- Test: `tests/Feature/DirectorySchoolRulesTest.php` (baru)

**Interfaces:**
- Produces: `upsertDirectorySchool(array $row): array{university: ?University, created: bool, filled: bool, matched_by: 'npsn'|'name'|null}`. `npsn` boleh `''`: cocokkan hanya lewat nama, `code` disimpan `null`. Pencocokan lewat nama hanya terhadap baris yang `code`-nya kosong (untuk `npsn` kosong: baris bernama sama mana pun).

- [ ] **Tulis tes yang gagal.**

```php
public function test_dua_sekolah_bernama_sama_beda_npsn_tersimpan_keduanya(): void
// upsert ('90000001','SMA PGRI 1') lalu ('90000002','SMA PGRI 1') → University::count() === 2

public function test_nama_sama_tanpa_kode_hanya_diisi_npsn(): void
// baris manual is_verified=false, code=null → upsert nama sama → count 1, code terisi, is_verified tetap false, matched_by 'name'

public function test_quick_import_tanpa_npsn_tidak_membuat_kode_acak_atau_data_bawaan(): void
// POST quick_import name='SMA CONTOH', institution_type=sekolah → code null, address null

public function test_input_manual_yang_cocok_beberapa_sekolah_meminta_memilih(): void
// dua 'SMA PGRI 1' → simpan profil university_id='other', custom_university_name='SMA PGRI 1'
// → assertSessionHasErrors('custom_university_name'); session 'university_suggestions' berisi 2; tidak ada baris baru
```

- [ ] Jalankan `--filter=DirectorySchoolRulesTest` → FAIL.
- [ ] Implementasikan. `quickImport`: buang `SCH-rand` dan nilai bawaan kota/provinsi/status. `resolveUniversity`: sebelum `findExact`, bila lebih dari satu institusi bernama sama persis (tanpa peka huruf), lempar `ValidationException` pesan `Ada beberapa sekolah/kampus bernama "<nama>". Pilih yang sesuai dari saran di bawah.` dengan `university_suggestions` (nama saran diberi ` · <cityLabel>`).
- [ ] `php artisan test` → lulus. Commit `fix: sekolah bernama sama tidak lagi terlewat dan data impor cepat tidak dikarang`.

---

### Task 3: Mesin sinkron

**Files:**
- Create: migrasi `*_create_institution_sync_tables.php`, `app/Models/InstitutionSyncRun.php`, `app/Models/InstitutionChangeProposal.php`, `app/Services/Institutions/InstitutionSyncService.php`, `app/Exceptions/SyncAlreadyRunning.php`
- Modify: `app/Console/Commands/SyncExternalInstitutions.php` (menjadi pemanggil service; opsi dan `lastFullSync`/`isDue` tetap)
- Test: `tests/Feature/InstitutionSyncEngineTest.php` (baru)

**Interfaces:**
- Produces:
  - Tabel `institution_sync_runs`: `trigger` (`manual`|`schedule`), `status` (`queued`|`running`|`success`|`failed`), `started_by` null FK users, `forms` json, `per_page`, `max_pages`, `current_step` null, `current_page`, `last_page` null, `created_count`, `updated_count`, `proposed_count`, `skipped_count`, `failed_steps` json null, `message` text null, `finished_at` null, timestamps.
  - Tabel `institution_change_proposals`: `university_id` null FK cascade, `kind` (`name`|`address`|`new_college`), `current_value` null, `proposed_value` string 500, `payload` json null, `fingerprint` char 40 **unique**, `status` (`pending`|`accepted`|`rejected`), `decided_by` null, `decided_at` null, timestamps.
  - Tabel `universities`: kolom baru `directory_synced_at` timestamp null.
  - `InstitutionSyncService::start(string $trigger, ?int $userId = null, array $forms = [], int $perPage = 1000, int $maxPages = 0): InstitutionSyncRun` (melempar `SyncAlreadyRunning`; dibungkus `Cache::lock('institution-sync:start', 10)`)
  - `firstStep(InstitutionSyncRun $run): array{step: string, page: int}`
  - `processStep(InstitutionSyncRun $run, string $step, int $page): ?array{step: string, page: int}` (`null` = sudah selesai dan status akhir ditulis)
  - `fail(InstitutionSyncRun $run, string $message): void`
  - `InstitutionSyncRun::isActive(): bool` (`queued`/`running`), `isStalled(): bool` (aktif dan `updated_at` lebih dari 15 menit), `InstitutionSyncRun::STALL_MINUTES = 15`
- `step` = bentuk sekolah (`SMA`, `SMK`, `MA`, `SMLB`). Langkah kampus ditambahkan di Task 4.

**Aturan:** satu halaman diproses dalam satu `DB::transaction`, dan `directory_synced_at` diisi untuk setiap sekolah yang cocok lewat NPSN atau baru dibuat dari sinkron. Halaman gagal: catat di `failed_steps`, lanjut ke bentuk berikutnya, status akhir `failed`. Berkas penanda (`STAMP_FILE`) hanya ditulis bila `success`, `max_pages` 0, dan semua bentuk ikut. Bila semua bentuk melaporkan total 0: `failed`, pesan `Direktori sekolah tidak mengembalikan data.` `start()` menandai proses macet sebagai `failed` lalu memulai yang baru. Command yang mendapati sinkron lain aktif menampilkan peringatan dan keluar `SUCCESS`.

- [ ] **Tulis tes yang gagal.**

```php
public function test_sinkron_kedua_ditolak_selama_masih_aktif(): void
// start('manual') ; expectException(SyncAlreadyRunning::class) pada start('schedule') ; count run === 1

public function test_proses_macet_digantikan_proses_baru(): void
// start ; travel(16)->minutes() ; start → run lama 'failed', run baru 'queued'

public function test_command_dilewati_saat_ada_sinkron_aktif(): void
// start ; artisan('institutions:sync-external')->assertSuccessful() ; Http::assertNothingSent()

public function test_semua_bentuk_kosong_dianggap_gagal(): void
// semua total_data 0 → assertFailed ; lastFullSync() === null

public function test_ma_kosong_saja_tetap_sukses(): void
// SMK 1 baris, lainnya 0 → assertSuccessful ; lastFullSync() !== null ; directory_synced_at terisi
```

- [ ] Jalankan `--filter=InstitutionSyncEngineTest` → FAIL. Implementasikan.
- [ ] `php artisan test --filter="InstitutionSyncEngineTest|ExternalInstitutionSyncTest"` → lulus, lalu seluruh tes. Commit `feat: mesin sinkron institusi dengan status dan penguncian`.

---

### Task 4: Usul perubahan dan kampus baru

**Files:**
- Create: `app/Services/Institutions/ChangeProposalService.php`
- Modify: `InstitutionSyncService.php` (panggil usul; langkah `KAMPUS` setelah bentuk sekolah, hanya pada sinkron penuh dan bila `CollegeDirectoryClient::enabled()`)
- Modify: `phpunit.xml` (`COLLEGE_DIRECTORY_ENABLED=false` sebagai bawaan agar tes tidak menyentuh API asli; tes kampus mengaktifkannya sendiri dengan URL palsu)
- Modify: `CollegeDirectoryClient.php`
- Test: `tests/Feature/InstitutionChangeProposalTest.php` (baru)

**Interfaces:**
- Consumes: tabel dan service Task 3, `matched_by` Task 2.
- Produces:
  - `CollegeDirectoryClient::all(): ?array` (GET `/search?country=Indonesia`, dinormalisasi; `null` bila gagal)
  - `ChangeProposalService::forSchool(University $university, array $row): int` (jumlah usul baru)
  - `ChangeProposalService::forColleges(array $rows): int`
  - `ChangeProposalService::decide(array $ids, string $decision, int $userId): int` (`accept`|`reject`; hanya usul `pending`; dalam `DB::transaction`)
  - `ChangeProposalService::comparable(?string $value): string` = huruf kecil, buang semua selain `\p{L}\p{N}`

**Aturan:** `forSchool` hanya untuk `matched_by === 'npsn'`. Usul nama dibuat bila nama API berbeda (menurut `comparable`) dari `name` **dan** `acronym`. Usul alamat dibuat bila alamat lokal terisi dan berbeda. `fingerprint = sha1(kind|university_id atau 0|comparable(proposed_value))`; bila sudah ada dalam status apa pun, lewati. Usul `pending` lama untuk institusi dan `kind` yang sama dihapus saat nilai baru diusulkan. `forColleges` melewati kampus yang `comparable(name)`-nya sudah ada di `name` atau `acronym`. Terima `new_college` memakai `upsertDirectoryCollege(payload)`; terima `name`/`address` memakai `update()` model.

- [ ] **Tulis tes yang gagal.**

```php
public function test_perubahan_nama_jadi_usul_dan_data_lokal_tidak_tertimpa(): void
public function test_beda_huruf_spasi_tanda_baca_tidak_diusulkan(): void      // 'SMKN 1  Contoh.' vs 'smkn 1 contoh' → 0
public function test_nama_sama_dengan_nama_resmi_tidak_diusulkan(): void     // name 'SMA Negeri 5 Surabaya', acronym 'SMAN 5 SURABAYA', API 'SMAN 5 SURABAYA' → 0
public function test_usul_ditolak_tidak_muncul_lagi(): void                  // decide reject → forSchool lagi → 0
public function test_terima_usul_mengubah_hanya_kolom_itu(): void            // is_verified/status/institution_type tetap
public function test_kampus_baru_menunggu_persetujuan(): void                // forColleges → University tidak bertambah; setelah accept bertambah 1
public function test_kegagalan_api_kampus_tidak_menghapus_hasil_sekolah(): void // colleges.test 504 → sekolah tersimpan, run 'failed', failed_steps memuat 'KAMPUS'
```

Setiap tes memeriksa jumlah baris `institution_change_proposals` dan nilai kolom `universities` yang disebut pada komentarnya.

- [ ] `--filter=InstitutionChangeProposalTest` → FAIL. Implementasikan. → lulus, lalu seluruh tes. Commit `feat: perubahan dari API menjadi usul yang diputuskan admin`.

---

### Task 5: Tombol, antrean, dan halaman usul

**Files:**
- Create: `app/Jobs/RunInstitutionSyncStep.php`, `app/Http/Controllers/Admin/InstitutionSyncController.php`, `partials/sync-panel.blade.php`, `admin/universities/proposals.blade.php`
- Modify: `routes/admin.php` (letakkan **sebelum** route `/admin/universities/{id}`), `admin/universities/index.blade.php` (`@include` bila `$isSuperAdmin`)
- Test: `tests/Feature/InstitutionSyncAdminTest.php` (baru)

**Interfaces:**
- Consumes: `InstitutionSyncService`, `ChangeProposalService`.
- Produces:
  - `RunInstitutionSyncStep(int $runId, string $step, int $page)`; `public $tries = 1; public $timeout = 80;` (di bawah `retry_after` 90). `handle()` berhenti bila run tidak aktif; bila ada langkah berikutnya, dispatch job baru. `failed()` memanggil `fail()`.
  - Route: `admin.universities.sync.start` POST `/admin/universities/sync`; `admin.universities.sync.status` GET `.../sync/status`; `admin.universities.sync.proposals` GET `.../sync/proposals`; `admin.universities.sync.decide` POST `.../sync/proposals`
  - JSON status: `{run: {status, trigger, step, page, last_page, created, updated, proposed, skipped, message, finished_at}|null, active: bool, stalled: bool, queue_waiting: bool, last_full_sync: string|null, pending_proposals: int}`. `queue_waiting` = status `queued` lebih dari 60 detik.

**Tampilan:** panel berisi tombol `Muat/Perbarui dari API`, waktu sinkron terakhir, progres `Memuat SMK: halaman 3 dari 12`, hasil akhir, dan tautan `N usul menunggu keputusan`. Status ditanyakan tiap 3 detik hanya selama aktif. Tombol nonaktif bila aktif dan tidak macet; bila macet berlabel `Mulai ulang`. Pesan `queue_waiting`: `Antrean belum berjalan. Jalankan "php artisan queue:work".` Halaman usul: 50 per halaman, filter jenis, kolom `Sekarang` dan `Dari API`, centang semua di halaman, tombol `Terima` dan `Tolak`. Ikuti gaya kartu halaman `admin/universities/index`, dan nyaman di lebar 375 px.

- [ ] **Tulis tes yang gagal.**

```php
public function test_hanya_super_admin_yang_boleh(): void        // admin ber-agency_profile_id, supervisor, mahasiswa → 403 pada keempat route
public function test_tombol_membuat_run_dan_mengantrekan_satu_job(): void // Queue::fake ; Queue::assertPushed(RunInstitutionSyncStep::class, 1) ; AuditLog INSTITUTION_SYNC_START
public function test_klik_kedua_ditolak_dengan_pesan(): void     // flash error 'Sinkron masih berjalan.' ; job tetap 1
public function test_job_untuk_run_yang_sudah_gagal_tidak_berbuat_apa_pun(): void // Http::assertNothingSent
public function test_status_melaporkan_antrean_belum_berjalan(): void // travel(61)->seconds() → queue_waiting true
public function test_rantai_job_selesai_dengan_status_sukses(): void  // antrean sync + Http::fake → status 'success'
public function test_keputusan_usul_dicatat_audit(): void        // AuditLog INSTITUTION_PROPOSAL_ACCEPT / _REJECT
```

- [ ] `--filter=InstitutionSyncAdminTest` → FAIL. Implementasikan. → lulus, lalu seluruh tes.
- [ ] `npm run build && php artisan optimize:clear`, buka menu sebagai Super Admin dengan `queue:work` mati lalu hidup, di lebar desktop dan 375 px. Periksa pesan antrean, progres, hasil, dan halaman usul.
- [ ] Commit `feat: tombol Muat dari API dan halaman usul perubahan`.

---

### Task 6: Data awal sekolah

**Files:**
- Create: `app/Services/Institutions/SchoolSnapshotService.php`, `app/Console/Commands/ExportSchoolSnapshot.php`, `ImportSchoolSnapshot.php`, `database/seeders/SchoolSnapshotSeeder.php`
- Modify: `database/seeders/DatabaseSeeder.php` (setelah `MasterSchoolsSeeder`), `scripts/demo_preflight.php` (setelah langkah migrate), `scripts/pgsql_connect.php` (setelah migrate)
- Test: `tests/Feature/SchoolSnapshotTest.php` (baru)

**Interfaces:**
- Produces:
  - File `database/data/schools_snapshot.json.gz`: `{"generated_at": "<ISO8601>", "rows": [{"npsn","name","address"}]}`
  - `SchoolSnapshotService::export(string $path): int`; `import(string $path): array{created: int, filled: int, skipped: int}`
  - Perintah `institutions:export-snapshot` dan `institutions:import-snapshot`; opsi `--path`. File tidak ada: peringatan, keluar `SUCCESS`.

**Aturan:** ekspor hanya `institution_type` sekolah dengan `directory_synced_at` terisi dan `code` terisi. Impor memuat kode dan nama yang ada ke memori sekali, lalu `insert` per 1000 baris (isi `slug`, waktu, dan kolom bawaan sama seperti `upsertDirectorySchool`, karena `insert` melewati event model). NPSN sudah ada: lewati. Nama sama tanpa kode: isi `code`. `SchoolSnapshotSeeder` dilewati saat `app()->runningUnitTests()`.

- [ ] **Tulis tes yang gagal.**

```php
public function test_ekspor_hanya_sekolah_dari_direktori(): void  // manual, belum terverifikasi tanpa cap, dan kampus tidak ikut; kunci baris persis npsn,name,address
public function test_impor_aman_diulang_dan_tidak_menimpa(): void // dua kali impor → jumlah sama; nama hasil koreksi admin tetap
public function test_impor_menyimpan_nama_sama_beda_npsn_dan_mengisi_slug(): void
public function test_file_tidak_ada_tidak_menggagalkan_pemasangan(): void
```

- [ ] `--filter=SchoolSnapshotTest` → FAIL. Implementasikan. → lulus, lalu seluruh tes. Commit `feat: data awal sekolah dapat diekspor dan diimpor`.

---

### Task 7: Buat data awal nyata, uji akhir, catatan

- [ ] Backup database `magang` dengan `pg_dump` sebelum menyentuhnya.
- [ ] Di database uji baru yang kosong (bukan `magang`): `migrate`, lalu `institutions:sync-external` sampai sukses. Catat jumlah sekolah per bentuk dan bandingkan dengan 25.848 di `magang`. Selisihnya menunjukkan dampak perbaikan Task 2; laporkan apa adanya.
- [ ] `php artisan institutions:export-snapshot`. Catat ukuran file; bila lebih dari 5 MB, tanyakan dulu sebelum commit.
- [ ] Di database kosong kedua: `migrate`, `institutions:import-snapshot` dua kali. Hitungan harus sama. Catat lama impor.
- [ ] Ukur ulang pencarian 5 kata kunci dan buka form profil di browser: cari, tidak ketemu, tambah manual, verifikasi oleh admin.
- [ ] `php artisan test` di SQLite lalu PostgreSQL (jangan bersamaan), `vendor/bin/pint --test`.
- [ ] Tambahkan entri LRN di `.antigravity/memory/learnings.md` (pencocokan nama menjatuhkan sekolah bernama sama; API langsung saat mengetik). Sebutkan `institutions:import-snapshot` di `scripts/build_it_guide.php` bagian API sekolah.
- [ ] Commit `feat: data awal sekolah dan catatan sinkron institusi`. Laporkan hasil, minta izin sebelum push/PR.

## Tambahan 25/11 (di luar rancangan sinkron, diminta Mas Yasin)

### Task 8: Angka batas anggota kelompok tidak ditampilkan

Kerjakan setelah Task 0 (kecil dan berdiri sendiri). Di `origin/main`, kalimat "batas maksimal sistem 100 orang" pada form pengajuan sudah diganti (commit `bbc5b27`). Yang masih menyebut angka hanya pesan saat bergabung ke kelompok penuh.

**Files:**
- Modify: `app/Http/Controllers/Student/ApplicationController.php:351-356`
- Test: `tests/Feature/InternshipGroupWorkflowTest.php`

Batas tetap 100 dan memakai `Unit::MAX_GROUP_MEMBERS` sebagai satu-satunya sumber angka (termasuk `ApplicationController.php:78`). Pesan: `Kelompok ini sudah penuh sehingga anggota baru tidak bisa ditambahkan.`

- [ ] Tulis tes `test_pesan_kelompok_penuh_tidak_menyebut_angka`: kelompok berisi 100 pengajuan → anggota ke-101 bergabung → flash `error` sama persis dengan pesan di atas dan jumlah anggota tetap 100. Jalankan → FAIL.
- [ ] Implementasikan, jalankan seluruh tes → lulus. Cari ulang di `resources/` dan `app/` agar tidak ada teks tampilan lain yang menyebut batas 100 anggota kelompok. Commit `fix: pesan kelompok penuh tanpa menyebut angka batas`.

### Task 9: Error zip pada fitur gabung PDF

**Penyebab (dari tangkapan layar teman, 25/11):** `Failed to resolve import "pdf-lib" from "resources/js/group-documents.js"` dari server Vite (`npm run dev`) di folder proyek teman. `node_modules` ada (Vite jalan) tetapi `pdf-lib` tidak terpasang di situ. `pdf-lib@1.17.1` tercatat di `package.json` dan `package-lock.json`, masuk 21/11 (`851e6ea`). Ini paket npm, bukan Composer. Dugaan yang belum dipastikan: `node_modules` dari salinan lama dan `npm install` tidak dijalankan ulang.

**Perbaikan di laptop teman (tanpa ubah kode):** hentikan Vite, di folder tersebut jalankan `npm install`, lalu `npm run dev` lagi. Bila tidak memakai mode pengembangan: `npm run build` dan pastikan `public/hot` tidak ada. Minta teman mengabari hasilnya.

**Files:**
- Modify: `README.md:103-106`

- [ ] Hapus kalimat usang di README yang menyebut PHP memanggil `node` di server untuk menggabungkan dokumen (sejak 21/11 penggabungan di peramban). Ganti dengan: jalankan `npm install` setiap kali `package.json` berubah, termasuk sesudah mengambil kode terbaru. Commit `docs: langkah npm install untuk fitur gabung PDF`.
- [ ] Sesudah Task 7 dan 8, buat zip baru dengan `node_modules` asli (bukan tautan), ekstrak ke folder baru, jalankan, dan uji gabung PDF untuk satu pengajuan kelompok. Periksa `public/build/manifest.json`: di zip 22/11 kuncinya memuat jalur folder laptop.

### Task 10: Rilis ke GitHub agar teman bisa pasang tanpa zip

Dikerjakan terakhir, sesudah Task 1-9 lulus. **Repo `raveldanz/Magang` bersifat PUBLIK.**

**Tidak boleh masuk GitHub:** `.env`, token/kunci, database dan dump (`database/pgsql/`, `database/cadangan/`, sqlite), backup, `storage/app/private/`, unggahan pengguna di `storage/app/public/`, folder `_cadangan_data_pribadi_*`. Tidak ikut juga (dibuat ulang saat pasang): `vendor/`, `node_modules/`, `public/build/`.

**Yang membuat hasil sama:** kode, `composer.lock`, `package-lock.json`, migrasi, seeder, data awal sekolah (Task 6), logo dan aset. Data yang diketik manual di database laptop tidak ikut.

**Files:**
- Create: `PASANG.bat`, `PERBARUI.bat`
- Modify: `README.md`, `.env.example`
- Create bila perlu: berkas contoh **buatan** (tanpa data orang asli) beserta seeder yang menyalinnya

- [ ] `PASANG.bat`: periksa PHP 8.3, Composer, Node, npm (pesan jelas bila kurang); `composer install`; `npm ci`; `npm run build`; salin `.env.example` ke `.env` hanya bila belum ada; `key:generate` hanya bila `APP_KEY` kosong; siapkan database; `migrate --seed` hanya pada database kosong; `institutions:import-snapshot`; `storage:link`; hapus `public/hot`. Berhenti pada langkah pertama yang gagal.
- [ ] `PERBARUI.bat` (sesudah `git pull`): `composer install`, `npm ci`, `npm run build`, `migrate`, `institutions:import-snapshot`, `optimize:clear`. Tidak pernah menghapus data. Ini mencegah error `pdf-lib` terulang.
- [ ] Samakan `.env.example` dengan kunci yang dibutuhkan, tanpa nilai rahasia.
- [ ] Cari tampilan/seeder yang bergantung pada berkas lokal yang diabaikan git (antara lain gambar `storage/app/public/projects/` dan proposal contoh). Jangan salin berkas lokal apa adanya: daftar dulu ke Mas Yasin, ganti dengan berkas contoh buatan.
- [ ] Uji dari klon bersih di folder baru: `git clone` branch ini, `PASANG.bat`, `start_demo.bat`; masuk tiap peran, cek logo dan gambar, gabung PDF kelompok, pencarian sekolah. Bandingkan dengan laptop ini dan laporkan setiap beda.
- [ ] Sebelum push: periksa daftar berkas dan isi diff `origin/main..HEAD` terhadap daftar larangan di atas dan terhadap data pribadi. Tampilkan ringkasannya ke Mas Yasin dan **minta izin**. Sesudah diizinkan: push branch, buat PR. Merge ke `main` hanya dengan izin terpisah.

## Di luar rencana (temuan saat menyusun)

Halaman admin Sekolah & Kampus memuat **semua** institusi terverifikasi sebagai pilihan gabung bila di halaman itu ada entri menunggu verifikasi (`UniversityController::index`, `$verifiedOptions`). Dengan puluhan ribu sekolah, halaman bisa berat. Belum diukur; perlu diputuskan terpisah.
