# Panduan Deploy & Operasional SIP-MAGANG

Dokumen ini menjelaskan apa saja yang **wajib berjalan di server** agar seluruh fitur bekerja,
bukan hanya halaman web-nya.

## 1. Ringkasan proses yang harus hidup

| Proses | Perintah | Untuk apa | Gejala bila mati |
|---|---|---|---|
| Web server | Nginx/Apache + PHP-FPM 8.3 (`public/` sebagai document root) | Aplikasi | Situs tidak bisa dibuka |
| **Scheduler** | `php artisan schedule:run` **tiap menit** (cron) | Aktivasi magang saat tanggal mulai, penutupan & pengingat magang yang lewat tanggal selesai, heartbeat | Mahasiswa tetap "Diterima" walau sudah mulai magang; tidak ada pengingat |
| **Queue worker** | `php artisan queue:work --tries=3 --timeout=90` (proses permanen) | Notifikasi web push (antrean `database`) | Notifikasi push tidak pernah terkirim |

Cek kapan saja dengan:

```bash
php artisan app:health
```

Dasbor Super Admin juga menampilkan peringatan merah bila scheduler / queue worker tidak aktif
lebih dari 20 menit (dibaca dari heartbeat yang dikirim scheduler tiap 5 menit).

## 2. Langkah deploy pertama

```bash
composer install --no-dev --optimize-autoloader
cp .env.example .env          # lalu isi nilai produksi (lihat bagian 3)
php artisan key:generate
php artisan migrate --force
php artisan storage:link      # logo & aset publik
php artisan app:move-private-files   # hanya sekali, bila ada data lama di disk public
npm ci && npm run build
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

## 3. Variabel `.env` penting untuk produksi

```dotenv
APP_ENV=production
APP_DEBUG=false                 # WAJIB false di produksi
APP_URL=https://magang.surabaya.go.id
APP_TIMEZONE=Asia/Jakarta
APP_LOCALE=id

DB_CONNECTION=pgsql
QUEUE_CONNECTION=database
CACHE_STORE=database
SESSION_DRIVER=database

VERIFY_NUMERIC_FALLBACK=false
# VAPID_PUBLIC_KEY / VAPID_PRIVATE_KEY untuk web push (php artisan webpush:vapid)
```

## 4. Scheduler (cron)

### Linux

```cron
* * * * * cd /var/www/sip-magang && php artisan schedule:run >> /dev/null 2>&1
```

### Windows Server (Task Scheduler)

Buat task baru → Trigger: *Daily*, ulangi setiap **1 menit** selama 1 hari →
Action: `php.exe` dengan argumen `artisan schedule:run` dan *Start in* = folder proyek.

### Development

`composer dev` sudah menjalankan `php artisan schedule:work` dan `queue:listen` bersama server.

## 5. Queue worker

### Linux (Supervisor) — `/etc/supervisor/conf.d/sip-magang-worker.conf`

```ini
[program:sip-magang-worker]
command=php /var/www/sip-magang/artisan queue:work --tries=3 --timeout=90 --sleep=3
autostart=true
autorestart=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/sip-magang/storage/logs/worker.log
stopwaitsecs=120
```

```bash
sudo supervisorctl reread && sudo supervisorctl update && sudo supervisorctl start sip-magang-worker
```

### Windows Server

Jalankan `php artisan queue:work --tries=3` sebagai service (mis. dengan **NSSM**:
`nssm install SipMagangWorker "C:\php\php.exe" "artisan queue:work --tries=3"`, *Startup directory* = folder proyek).

> Setelah setiap deploy kode baru jalankan `php artisan queue:restart` agar worker memuat kode terbaru.

## 6. Penyimpanan berkas

- Berkas pribadi (dokumen pengajuan, lampiran logbook, foto profil, lampiran tiket, lampiran chat)
  ada di `storage/app/private` dan **hanya** dibuka lewat route berotorisasi. Jangan dibuat symlink publik.
- Logo instansi/kampus ada di `public/images/logos` dan `storage/app/public`.
- Backup rutin: database PostgreSQL **dan** folder `storage/app` (private + public).

## 7. Setelah deploy — checklist cepat

- [ ] `php artisan app:health` → semua normal (tunggu ±5 menit setelah cron & worker aktif)
- [ ] Login Super Admin, dasbor tidak menampilkan peringatan merah
- [ ] Unggah lampiran logbook uji lalu buka dari akun mentor → berhasil; buka URL-nya tanpa login → diarahkan ke login
- [ ] `APP_DEBUG=false` (halaman error tidak menampilkan stack trace)
