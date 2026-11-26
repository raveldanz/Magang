# Rancangan: Data Sekolah dan Kampus Satu Sumber

Tanggal: 25/11/2026. Status: **menunggu persetujuan**. Acuan kode: `origin/main` `01c4769`.

## Tujuan

Pencarian sekolah/kampus saat ini lambatnya tidak menentu, karena API luar ikut dipanggil ketika hasil database sedikit. Sesudah perubahan: **saat mengetik, sumbernya hanya database.** API hanya bertugas mengisi database.

Pemeriksaan di laptop kantor: pencarian database (query disederhanakan, 25.848 sekolah dan 252 kampus) 11-95 ms. API sekolah 0,9-5,8 detik, API kampus 0,7-1,9 detik.

## Pendekatan

| Pendekatan | Untung | Rugi |
|---|---|---|
| A. Database dulu, pengguna punya tombol cari API | Sekolah baru bisa dicari sendiri | Pengguna masih bisa menunggu API |
| B. API otomatis, tampil menyusul | Tanpa tombol | Daftar berubah saat dibaca, API sering dipanggil |
| **C+. Database saja, API lewat tombol admin utama dan jadwal mingguan (dipilih)** | Cepat dan konsisten, pengguna tidak bergantung API | Sekolah yang belum ada harus diinput manual |

Dipilih C+ karena API sumber juga tidak memuat semua sekolah (misalnya yang terpencil), sedangkan input manual sudah ada.

## Yang dibangun

### 1. Pencarian tanpa API
- `InstitutionSearchController::search` hanya membaca database.
- `/universities/master-search` (route publik) tidak memanggil API. Database dan data bawaan saja.
- Endpoint `institutions.resolve` dan penanganan saran `npsn:` / `college:` di `searchable-select` dihapus karena tidak dipakai lagi.
- Saran dari API di menu admin (`externalSuggestions`) hanya untuk Super Admin, atau diambil dari database.
- Hasil sekolah menampilkan kota, agar sekolah bernama sama bisa dibedakan.

### 2. Input manual (dipertahankan)
- Form profil mahasiswa/murid: opsi "Tidak terdaftar? Masukkan manual" sudah ada, lengkap dengan cek kemiripan, status menunggu verifikasi, dan catatan audit.
- Daftar akun Pembimbing: nama bisa diketik bebas dan masuk sebagai belum terverifikasi.
- Admin memakai fitur Verifikasi dan Gabungkan yang sudah ada.
- Yang diubah hanya labelnya, supaya jelas untuk sekolah maupun kampus.

### 3. Tombol Perbarui Sekolah dan Perbarui Kampus
- Letak: menu Sekolah & Kampus. Hanya Super Admin (`ensureSuperAdmin`).
- Berjalan di antrean, dipecah **satu job per halaman API**, supaya pekerjaan antrean lain tidak tertahan lama dan tidak terkena batas waktu antrean.
- Status tampil di halaman: sedang memuat (progres), selesai (jumlah baru, usul perubahan, dilewati), gagal (pesan), serta waktu sinkron terakhir.
- Selama satu jenis berjalan, tombol jenis itu nonaktif. Jenis lainnya tetap bisa dijalankan.
- Jika antrean tidak menyala, halaman memberi tahu. Proses yang macet bisa dimulai ulang.
- Dicatat di AuditLog.

### 4. Jadwal otomatis
Jadwal yang sudah ada (diperiksa tiap jam, jalan jika sudah 7 hari) tetap menyala. Tombol dan jadwal memakai logika sinkron yang sama, dengan kunci dan waktu keberhasilan terakhir tersendiri per jenis.

### 5. Aturan pembaruan data

| Kasus | Tindakan |
|---|---|
| Sekolah dengan NPSN baru | Ditambahkan otomatis |
| Kolom lokal masih kosong | Dilengkapi otomatis |
| Nama/alamat di API berbeda | Menjadi **usul perubahan**, admin memilih Terima atau Tolak (bisa sekaligus banyak) |
| Usul sudah ditolak | Tidak diusulkan lagi untuk nilai yang sama |
| Data hilang dari API | Tidak dihapus |
| Entri manual belum terverifikasi bernama sama | Tidak diverifikasi otomatis; admin yang memutuskan |
| Kampus baru dari API kampus | Masuk daftar usul, tidak langsung ditambahkan (rawan dobel karena beda ejaan) |

Perbandingan nama/alamat mengabaikan huruf besar-kecil, spasi, dan tanda baca.

**Perbaikan wajib:** di `upsertDirectorySchool`, sekolah dengan NPSN baru tetapi namanya sama dengan baris lain dianggap sudah ada, walaupun baris itu punya NPSN berbeda. Akibatnya sekolah kedua tidak tersimpan. Aturan baru: pencocokan lewat nama hanya berlaku jika baris lama belum punya kode. (Temuan dari pembacaan kode; jumlah sekolah yang terlewat belum dihitung.)

### 6. Data awal
- File salinan data sekolah (terkompresi) disimpan di proyek, beserta tanggal pengambilannya.
- Isinya hanya sekolah dari direktori ber-NPSN. Entri manual, data peserta, dan akun tidak ikut.
- Diimpor sekali saat pemasangan. Aman diulang dan tidak menimpa data yang sudah ada.
- Ada perintah untuk membuat ulang file tersebut dari database. File dibuat sesudah sinkron ulang dengan aturan yang sudah diperbaiki.
- Data kampus tetap dari data bawaan yang sudah ada.

### 7. Rapikan "Tambahkan ke Mitra"
Sekolah tanpa NPSN tidak lagi diberi kode acak `SCH-xxxx` (kode dibiarkan kosong). Kota, provinsi, dan status tidak diisi bawaan "Surabaya / Jawa Timur / NEGERI".

## Bukan bagian pekerjaan ini
- Mencari API kampus baru yang lebih lengkap.
- Mengisi madrasah (MA), yang hasilnya 0 pada sinkron 21/11.
- Mengubah daftar institusi yang dimuat di halaman daftar akun Pembimbing. Ukurannya setelah sinkron penuh belum diukur.
- Kalimat batas 100 orang (ditangani Raveldo).

## Yang tidak boleh terjadi
- Pendaftaran buntu karena sekolah tidak ditemukan.
- Koreksi admin tertimpa sinkron.
- Data institusi yang sudah dipakai peserta terhapus atau berpindah.
- Aplikasi error ketika API mati.

## Cara memastikan berhasil
- Tes otomatis: pencarian tidak mengirim permintaan HTTP keluar; hanya Super Admin yang bisa memulai sinkron; sinkron ganda ditolak; semua baris tabel aturan pembaruan; dua sekolah bernama sama dengan NPSN berbeda sama-sama tersimpan; impor data awal aman diulang; API gagal tidak merusak data.
- Seluruh tes lulus di SQLite dan PostgreSQL.
- Uji di browser: ketik di form profil, input manual, tombol sinkron dari mulai sampai selesai.

## Kerja sama tim
- Kerja dimulai dari `origin/main` terbaru, di branch terpisah.
- Bagian API kampus berasal dari commit Evan, dan Raveldo yang menjelaskannya. Beri tahu keduanya sebelum mulai.

## Diserahkan ke pembuat
Nama tabel/kelas, struktur penyimpanan status dan usul perubahan, format file data awal, serta tata letak panel sinkron mengikuti pola halaman admin yang ada.

## Revisi 26 November 2026

Super Admin mendapat dua tombol, Perbarui Sekolah dan Perbarui Kampus. Tiap jenis memiliki kunci proses, status dan waktu sukses sendiri. Penjadwal memeriksa sekolah pada menit 30 dan kampus pada menit 45, masing-masing dengan interval tujuh hari sejak sukses penuh. Command tanpa opsi memilih sekolah. Gunakan --kind=college untuk kampus. Data kampus memakai tabel kelembagaan publik Kemdiktisaintek yang bersumber dari PDDikti. Kampus baru tetap memerlukan persetujuan admin.
