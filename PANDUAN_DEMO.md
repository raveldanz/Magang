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
yang tanggal mulainya sudah tiba, menaikkan batas unggah ke 64 MB, dan menjalankan penjadwal di jendela kecil.

## 2. Akun demo (semua kata sandi `password`)

| Peran | Email |
|---|---|
| Super Admin | `admin@surabaya.go.id` |
| Super Admin (demo, tanpa banner password bawaan) | `admin@gmail.com` — kata sandi `admin123`, bukan `password` |
| Admin Dinas | `admin.dispusip@surabaya.go.id` |
| Supervisor (pengesahan) | `supervisor@dispusip.surabaya.go.id` |
| Mentor lapangan | `mentor.dispusip@surabaya.go.id` |
| Dosen (DPL) | `dosen.unesa@unesa.ac.id` |
| Admin Universitas | `admin@unesa.ac.id` |
| Mahasiswa aktif (logbook) | `mahasiswa.aktif@unesa.ac.id` |
| Mahasiswa lulus (sertifikat) | `mahasiswa.lulus@unesa.ac.id` |
| Mahasiswa diterima | `dani.accepted@unesa.ac.id` |
| Mahasiswa perlu revisi | `fajar.revisi@unesa.ac.id` |

## 3. Bila ada masalah

| Gejala | Penyebab & solusi |
|---|---|
| HP: "situs tidak dapat dijangkau" | Firewall (langkah 1.2), beda WiFi, atau WiFi tamu/kampus yang memakai *client isolation* — pakai hotspot HP/router sendiri. |
| Alamat IP berubah | Terjadi bila pindah WiFi. Tutup jendela server lalu jalankan ulang `start_demo.bat`. |
| "Port 8000 sedang dipakai" | Server lain masih terbuka; demo otomatis pindah ke 8080. Pakai alamat yang tertulis. |
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
menyetel `APP_DEBUG=false`, menyertakan PHP portabel, memeriksa hasil salinan, lalu membuat zip di folder induk.

Setelah demo, cabut izin firewall bila perlu:

```bat
netsh advfirewall firewall delete rule name="SIP-MAGANG Demo (PHP)"
```
