# Panduan Demo SIP-MAGANG lewat WiFi Lokal

Laptop ini menjadi server; HP/laptop penguji membuka aplikasi lewat WiFi yang sama.
Tidak perlu internet, composer, npm, maupun PostgreSQL.

## 1. Menjalankan (laptop server)

1. Ekstrak zip, buka folder `SIP-MAGANG`.
2. **Sekali saja:** klik kanan `izinkan_firewall.bat` → **Run as administrator** → tekan `Y`.
   Tanpa langkah ini Windows Firewall memblokir php.exe dan perangkat lain hanya melihat "situs tidak dapat dijangkau".
3. Klik dua kali `start_demo.bat`. Jendela akan menampilkan alamat, misalnya:

   ```
   Di laptop ini      : http://localhost:8000
   Dari HP/laptop lain: http://192.168.1.23:8000   (harus di WiFi yang sama)
   ```

4. Bagikan alamat **"Dari HP/laptop lain"** ke penguji. Jangan tutup jendela hitam selama demo.

`start_demo.bat` otomatis: mencari PHP (folder `php\` di zip lebih dulu), membuat `.env`/database bila belum ada,
menjalankan migrasi, memperbaiki tautan `public/storage`, membuang cache path komputer lama, mengaktifkan magang
yang tanggal mulainya sudah tiba, mengompilasi semua tampilan, menaikkan batas unggah ke 64 MB, menjalankan
penjadwal di jendela kecil, dan menyalakan **beberapa server sekaligus** (lihat 1a).

Laptop server tidak perlu Node.js, Composer, PostgreSQL, maupun internet. Zip sudah membawa PHP portabel beserta
Microsoft Visual C++ Runtime yang dibutuhkannya. Bila tetap muncul `[GAGAL] PHP ditemukan ... tetapi tidak bisa
dijalankan`, pasang `VC_redist.x64.exe` (Visual C++ 2015-2022, x64) dari Microsoft, lalu jalankan ulang.

## 1a. Kapasitas: 15-20 peserta sekaligus

Server bawaan PHP hanya melayani **satu** permintaan pada satu waktu. Karena itu `start_demo.bat` (lewat
`scripts\demo_serve.bat`) menyalakan beberapa server aplikasi di jendela kecil bernama **"SIP-MAGANG Server 1..N"**
(port utama+1 ... +N) dan sebuah **gerbang** di port utama yang membagi peserta bergiliran ke server yang hidup.
Peserta cukup membuka alamat utama; alamat di browser akan berpindah ke port server yang melayaninya, dan login tetap
berlaku di semua port. OPcache PHP juga diaktifkan otomatis.

- Jumlah server mengikuti jumlah inti prosesor (4-8). Untuk mengubahnya, tambahkan `DEMO_WORKERS=6` di `.env`.
- **Jangan tutup** jendela "SIP-MAGANG Server n" yang diperkecil; menutup jendela utama mematikan semuanya.
- Kesehatan server: buka `http://localhost:<port utama>/__demo-status`.

Hasil uji beban di laptop pengembang (8 inti, peserta tiruan berjalan di laptop yang sama, klik tiap ±5 detik):

| Susunan | Median | 95% halaman terbuka dalam |
|---|---|---|
| 1 server tanpa OPcache (cara lama), 20 peserta | 11 detik | 26 detik |
| 1 server + OPcache, 20 peserta | 1,8 detik | 8 detik |
| 4-6 server + gerbang + OPcache, 20 peserta | 0,1-0,3 detik | 0,5-1,8 detik |
| 6 server + gerbang + OPcache, 30 peserta | 0,4 detik | 2,3 detik |

Login adalah halaman paling lambat (±1-3 detik saat 20 orang masuk bersamaan) karena pengecekan kata sandi memang
sengaja berat demi keamanan. Unduh PDF (surat/sertifikat) memakai satu server selama beberapa detik; server lain tetap
melayani peserta lain.

Uji sendiri sebelum hari-H (dari laptop lain di WiFi yang sama, setelah `start_demo.bat` berjalan):

```bat
php scripts\demo_loadtest.php --url=http://192.168.1.23:8000 --users=20 --seconds=60 --think=5000
```

Skrip meniru 20 peserta yang login lalu membuka dasbor, logbook, notifikasi, dan chat (hanya membaca, tidak mengubah
data magang) dan menyimpulkan apakah server lancar.

Tips agar tetap lancar: colokkan charger dan pilih mode daya "Best performance", matikan sleep, tutup aplikasi berat
lain di laptop server, dan mulai uji coba dengan meminta peserta login bergantian beberapa detik.

## 2. Akun demo (semua kata sandi `password`)

| Peran | Email |
|---|---|
| Super Admin | `admin@surabaya.go.id` |
| Super Admin (demo, tanpa banner password bawaan) | `admin@gmail.com` — kata sandi `admin123`, bukan `password` (bisa diganti lewat `DEMO_ADMIN_PASSWORD` di `.env` sebelum seeding) |
| Admin Dinas | `admin.dispusip@surabaya.go.id` |
| Supervisor (pengesahan) | `supervisor@dispusip.surabaya.go.id` |
| Mentor lapangan | `mentor.dispusip@surabaya.go.id` |
| Pembimbing (dosen kampus) | `dosen.unesa@unesa.ac.id` |
| Admin Kampus | `admin@unesa.ac.id` |
| Peserta magang aktif (logbook) | `mahasiswa.aktif@unesa.ac.id` |
| Peserta magang lulus (sertifikat) | `mahasiswa.lulus@unesa.ac.id` |
| Peserta magang diterima | `dani.accepted@unesa.ac.id` |
| Peserta magang perlu revisi | `fajar.revisi@unesa.ac.id` |

### Akun peserta dari sekolah (SMK Negeri 7 Surabaya)

| Peran / keadaan | Email | Yang bisa diperagakan |
|---|---|---|
| Admin Sekolah | `admin.smkn7@demo.sch.id` | Menyetujui guru yang mendaftar sendiri (menu Pembimbing) |
| Guru pembimbing (sudah disetujui) | `guru.smkn7@demo.sch.id` | Memeriksa logbook siswa, mengisi nilai pembimbing |
| Guru menunggu persetujuan | `guru.menunggu@demo.sch.id` | Login berhasil, belum bisa dipilih siswa |
| Siswa SMK aktif magang | `siswa.smk.aktif@demo.sch.id` | Profil sekolah (NIS, kelas), logbook |
| Siswa SMK lulus | `siswa.smk.lulus@demo.sch.id` | Sertifikat dan QR berlabel sekolah |

Daftar 283 SMA/SMK/MA Kota Surabaya otomatis terisi (`start_demo.bat` / `migrate:fresh --seed`),
jadi saat mendaftar baru, peserta atau guru cukup mengetik nama sekolahnya, misalnya `smkn 7`.

### Akun peserta magang per skenario

Urut mengikuti alur magang. Semua kata sandi `password`.

| Keadaan | Email | Yang bisa diperagakan |
|---|---|---|
| Baru mendaftar | `baru.daftar@unesa.ac.id` | Melengkapi profil dari nol |
| Profil lengkap, belum mengajukan | `siap.ajukan@unesa.ac.id` | Mengajukan magang |
| Pengajuan menunggu verifikasi | `fajar.revisi@unesa.ac.id` | Admin Dinas memeriksa berkas |
| Pengajuan ditolak | `gilang.ditolak@unesa.ac.id` | Melihat alasan, mengajukan ulang |
| Menunggu persetujuan Atasan | `mahasiswa.waiting@unesa.ac.id` | Supervisor menyetujui |
| Diterima, belum mulai | `dani.accepted@unesa.ac.id` | Surat balasan ber-QR |
| Aktif magang | `mahasiswa.aktif@unesa.ac.id` | Mengisi logbook |
| Aktif, logbook ada yang ditolak | `logbook.revisi@unesa.ac.id` | Catatan mentor, memperbaiki logbook |
| Laporan akhir menunggu tinjauan | `laporan.menunggu@unesa.ac.id` | Mentor menyetujui atau meminta revisi |
| Laporan akhir perlu revisi | `laporan.revisi@unesa.ac.id` | Mengunggah ulang laporan |
| Magang selesai, tinggal dinilai | `siap.dinilai@unesa.ac.id` | Mentor mengisi nilai, status langsung selesai |
| Penilaian ganda, menunggu nilai pembimbing | `ganda.aktif@pens.ac.id` | Pembimbing `dosen.pens@pens.ac.id` mengisi nilai 60% |
| Penilaian ganda, sudah lulus | `ganda.lulus@pens.ac.id` | Sertifikat dengan nilai mentor dan pembimbing |
| Lulus, sertifikat belum diterbitkan | `haikal.ketua.lulus@unitomo.ac.id` | Admin Dinas menerbitkan sertifikat |
| Lulus, sertifikat menunggu pengesahan | `hana.sertifikat@unesa.ac.id` | Supervisor mengesahkan |
| Lulus, sertifikat diminta revisi | `kartika.revisi@unesa.ac.id` | Admin Dinas memperbaiki sertifikat |
| Lulus, sertifikat sudah sah | `mahasiswa.lulus@unesa.ac.id` | Membuka sertifikat, memindai QR |
| Mengundurkan diri | `rina.mundur@unesa.ac.id` | Tampilan setelah mundur |
| Kelompok: ketua, pengajuan menunggu | `andi.ketua.pending@unesa.ac.id` | Pengajuan kelompok |
| Kelompok: ketua, aktif | `fauzan.ketua.aktif@unesa.ac.id` | Proyek kolaboratif |
| Akun dinonaktifkan | `akun.nonaktif@unesa.ac.id` | Login ditolak |

Mentor `mentor.dispusip@surabaya.go.id` membimbing akun `logbook.revisi`, `laporan.*`, `siap.dinilai`, dan `ganda.*`.
Mentor yang sama juga membimbing `siswa.smk.aktif` dan `siswa.smk.lulus`.
Sertifikat baru bisa dibuka peserta setelah diterbitkan Admin Dinas **dan** disahkan Supervisor.

Mengembalikan akun-akun skenario ke keadaan awal (misalnya setelah gladi bersih):

```bat
php artisan db:seed --class=DemoScenarioSeeder
```

## 3. Bila ada masalah

| Gejala | Penyebab & solusi |
|---|---|
| HP: "situs tidak dapat dijangkau" | Firewall (langkah 1.2), beda WiFi, atau WiFi tamu/kampus yang memakai *client isolation* — pakai hotspot HP/router sendiri. |
| Alamat IP berubah | Terjadi bila pindah WiFi. Tutup jendela server lalu jalankan ulang `start_demo.bat`. |
| "Port 8000-800N sebagian sedang dipakai" | Server lain masih terbuka; demo otomatis pindah ke 8080/8100/... Pakai alamat yang tertulis. |
| Halaman "Server demo belum siap" | Jendela "SIP-MAGANG Server n" tertutup atau baru dinyalakan. Tunggu sebentar; bila tetap, jalankan ulang `start_demo.bat`. |
| Terasa lambat saat ramai | Cek `/__demo-status`, tutup aplikasi lain, atau naikkan `DEMO_WORKERS` di `.env`. |
| Tampilan polos tanpa gaya | Ada berkas `public\hot` dari `npm run dev`: tutup Vite; `start_demo.bat` menghapusnya otomatis. |
| Logo/gambar tidak muncul | Jalankan ulang `start_demo.bat` (tautan `public\storage` dibuat ulang). |

Catatan yang memang tidak tersedia lewat alamat IP (keterbatasan browser untuk koneksi non-HTTPS):
notifikasi push dan rekam pesan suara di chat. Fitur lain, termasuk tombol "Salin" dan pemindaian QR
sertifikat/surat dari HP, sudah disesuaikan agar tetap berfungsi. Email "lupa kata sandi" tidak dikirim
(MAIL_MAILER=log); reset kata sandi lewat Super Admin.

## 4. Membuat ulang zip (di laptop pengembang)

```powershell
powershell -ExecutionPolicy Bypass -File scripts\make_demo_zip.ps1
```

Skrip membangun aset, menjalankan seluruh pengujian, menyalin proyek tanpa `.git`/`node_modules`/log/token,
menyetel `APP_DEBUG=false`, menyertakan PHP portabel beserta Visual C++ Runtime, memeriksa hasil salinan, lalu
membuat zip di folder induk. Buat zip hanya saat tidak ada perubahan yang belum di-commit (`git status` bersih),
karena skrip menyalin isi folder kerja apa adanya.

Setelah demo, cabut izin firewall bila perlu:

```bat
netsh advfirewall firewall delete rule name="SIP-MAGANG Demo (PHP)"
```

## 5. Demo di WiFi kantor dengan PostgreSQL

Pakai `start_demo_kantor.bat` (bukan `start_demo.bat`). Skrip ini menyalakan PostgreSQL portabel di `C:\pgsql`,
mengalihkan `.env` ke PostgreSQL, menjalankan migrasi, lalu menyalakan server di alamat WiFi laptop.

Yang perlu dipahami:

- **PostgreSQL tidak perlu diberi alamat IP.** HP penguji hanya berbicara dengan server web di laptop
  (`http://<IP-laptop>:8000`); server web itulah yang membaca PostgreSQL di laptop yang sama lewat `127.0.0.1`.
  Jadi `DB_HOST=127.0.0.1` tetap benar walaupun demo lewat WiFi, dan port 5432 tidak perlu dibuka di firewall.
- **Database kosong diisi otomatis.** Di laptop yang PostgreSQL-nya baru, `start_demo_kantor.bat` membuat database
  `magang` dan mengisinya dengan semua akun demo di atas. Tidak perlu memindahkan data apa pun.
- **Memindahkan data yang sudah ada** (misalnya hasil uji coba yang ingin dibawa):
  1. Di komputer asal klik dua kali `pgsql_ekspor.bat`. Hasilnya `database\pgsql\magang.dump`.
  2. Salin berkas itu ke folder yang sama di laptop tujuan.
  3. Di laptop tujuan klik dua kali `pgsql_impor.bat`, jawab `Y`. Isi database `magang` di laptop itu diganti.
  4. Jalankan `start_demo_kantor.bat`.
  Kata sandi PostgreSQL dibaca dari `DB_PASSWORD` di `.env` laptop masing-masing; samakan dengan kata sandi
  pengguna `postgres` di laptop itu.
- Berkas unggahan (CV, lampiran logbook, laporan) tidak ada di database. Bila perlu ikut pindah, salin juga
  folder `storagepp`.

Syarat laptop: PostgreSQL 16 (folder `C:\pgsql` atau pemasangan biasa) dan ekstensi PHP `pdo_pgsql` aktif.
Skrip akan memberi tahu bila salah satunya belum ada.

## 6. Penguji memakai HP

Bisa, dan sudah diuji lewat alamat WiFi: login, tampilan, unggah berkas, dan QR berjalan. Yang perlu diperhatikan:

- HP dan laptop harus di **WiFi yang sama**. WiFi kantor yang memisahkan perangkat (*client isolation*) akan
  memblokir; bila itu terjadi pakai hotspot dari satu HP dan sambungkan laptop serta penguji ke sana.
- Jalankan `izinkan_firewall.bat` sebagai administrator sekali di laptop server.
- Ketik alamat lengkap dengan `http://` di depan, misalnya `http://192.168.1.23:8000`. Tanpa itu sebagian
  browser HP mencoba `https` dan gagal.
- Foto dari iPhone berformat HEIC ditolak dengan pesan; pilih "Paling Kompatibel" di pengaturan kamera atau
  kirim sebagai JPG.
- Batas ukuran berkas diperiksa di HP sebelum dikirim, jadi berkas besar ditolak seketika tanpa menunggu unggah.
- Server demo melayani satu permintaan pada satu waktu. Untuk 10-15 penguji ini tidak terasa; unggahan besar
  yang bersamaan akan saling menunggu beberapa detik.
- Jangan biarkan laptop server tidur: matikan mode tidur dan sambungkan pengisi daya selama demo.
- Tidak tersedia lewat alamat IP: notifikasi push dan rekam suara di chat (butuh HTTPS).

## 7. Pemeriksaan cepat sebelum demo

```bat
php scripts\qc_smoke.php
```

Masuk sebagai tiap akun demo dan membuka semua halamannya (hanya membaca). Baris `[GAGAL]` berarti ada halaman
yang error untuk akun itu.

