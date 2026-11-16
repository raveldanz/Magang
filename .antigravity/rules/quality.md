# Standar Kualitas Antarmuka & Manajemen Cache

Panduan ini mengatur standar jaminan kualitas (*quality assurance*) untuk tampilan Blade, responsivitas antarmuka, konsistensi desain Tailwind CSS, serta prosedur pembersihan cache Laravel.

---

## 1. Standar Desain Blade & Mobile-Responsiveness

Setiap perubahan atau pembuatan berkas Blade (`resources/views/`) wajib mematuhi standar responsivitas dan konsistensi sistem:

### A. Pendekatan Mobile-First
- Rancang layout dari layar kecil ke layar besar.
- Gunakan breakpoint Tailwind secara terstruktur: `sm:` (640px), `md:` (768px), `lg:` (1024px), `xl:` (1280px).
- Contoh tata letak grid responsif:
  ```blade
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
      <!-- Card items -->
  </div>
  ```
- Tombol aksi dan form input harus memiliki ukuran sentuh (*touch target*) yang nyaman di perangkat seluler (minimal `py-2.5 px-4` atau `min-h-[42px]`).

### B. Konsistensi Skema Warna Sistem
Gunakan palette warna resmi aplikasi untuk menjaga harmoni visual:

| Peran UI | Kelas Tailwind | Penggunaan |
| :--- | :--- | :--- |
| **Primary Action** | `bg-blue-600 hover:bg-blue-700 text-white` | Tombol utama, link aktif, elemen fokus (`focus:ring-blue-500`). |
| **Success / Approved** | `bg-emerald-600`, background soft `bg-emerald-50 text-emerald-800 border-emerald-200` | Status laporan disetujui, badge verifikasi, alert sukses. |
| **Warning / Pending** | `bg-amber-500`, background soft `bg-amber-50 text-amber-800 border-amber-200` | Status menunggu review, peringatan revisi, alert tenggat waktu. |
| **Danger / Rejected** | `bg-rose-600`, background soft `bg-rose-50 text-rose-800 border-rose-200` | Status ditolak, pembatalan, tombol hapus, pesan error validasi. |
| **Neutral / Secondary**| `bg-gray-100 hover:bg-gray-200 text-gray-700` | Tombol batal, border pembatas (`border-gray-100`), teks sekunder (`text-gray-500`). |

### C. Konsistensi Bentuk & Interaktivitas
- **Radius Sudut**: Gunakan `rounded-xl` (12px) untuk tombol dan input; gunakan `rounded-2xl` atau `rounded-3xl` untuk container card / modal dialog.
- **Micro-Interactions**: Tambahkan animasi halus pada tombol interaktif:
  ```blade
  class="transition duration-150 ease-in-out active:scale-95 cursor-pointer shadow-xs hover:shadow-md"
  ```

---

## 2. Pembersihan Cache Laravel & Siklus Pembaruan

Untuk memastikan perubahan pada rute, konfigurasi, dan tampilan langsung teraplikasikan tanpa terhalang cache lama:

### A. Perintah Pembersihan Wajib:
| Jenis Perubahan | Perintah Artisan |
| :--- | :--- |
| **Modifikasi View / Blade** | `php artisan view:clear` |
| **Modifikasi Rute (`routes/`)** | `php artisan route:clear` |
| **Modifikasi Konfigurasi (`config/`, `.env`)** | `php artisan config:clear` |
| **Pembersihan Total (Rekomendasi)** | `php artisan optimize:clear` |

### B. Prosedur Wajib Pengembang / Agen:
1. Setelah menambahkan atau mengubah rute di `routes/web.php` atau `routes/api.php`, **wajib** jalankan `php artisan route:clear` atau `php artisan optimize:clear`.
2. Setelah mengubah template Blade utama atau komponen layout, jalankan `php artisan view:clear`.
3. Jika ada penambahan kelas Tailwind CSS baru yang belum terkompilasi di aset produksi, pastikan build berjalan:
   ```bash
   npm run build
   ```

---

## 3. Standar Minimalisme UI, Anti-Redundansi Teks & Quality Control (QC)

Seluruh halaman dan komponen antarmuka wajib mematuhi prinsip **Minimalis, To-The-Point, dan Bebas Redundansi**:

### A. Aturan Penulisan Teks Antarmuka (Micro-Copy)
1. **Dilarang Mengulang Informasi yang Sudah Ada di Judul/Badge**:
   - Jika judul kartu atau badge sudah menyebutkan nama kelompok, nama instansi, atau unit bidang, **dilarang** mengulang kembali nama kelompok/instansi/bidang tersebut di dalam kalimat deskripsi bawahnya maupun di dalam teks tombol.
   - Deskripsi/sub-judul kartu maksimal **1 kalimat singkat** (tanpa kalimat berlapis yang menjelaskan hal teknis yang sudah jelas).
2. **Dilarang Menulis Teks Tombol yang Terlalu Panjang**:
   - Label tombol aksi wajib ringkas (2–5 kata), misalnya `Kirim Perbaikan & Kembali ke Kelompok`, `Keluar dari Kelompok`, `Tolak Mahasiswa Ini Saja`, `Tolak Seluruh Kelompok`.
   - **Dilarang keras** menyisipkan nama entitas dinamis yang panjang seperti `({{ $group->name }})` di dalam label tombol submit.
3. **Hindari Penggunaan Huruf Tebal (`<strong>`) Berlebihan di Deskripsi (Anti-Bold-Spamming)**:
   - Gunakan warna teks sekunder (`text-slate-500`) yang bersih pada sub-judul tanpa menyisipkan banyak tag `<strong>` di tengah kalimat.
   - Pada panel petunjuk samping atau daftar panduan, batasi maksimal 3–4 butir singkat tanpa membungkus setiap kata dengan tag `<strong>`.
4. **Dilarang Menambahkan Banner Petunjuk Global yang Berulang**:
   - Jika komponen anak (*child cards*) sudah memiliki judul dan badge yang jelas (seperti *Khusus Ketua Tim* dan *Khusus Anggota Tim*), dilarang menambahkan banner amber/kuning besar di atasnya yang hanya mengulang fungsi kedua kartu tersebut.
   - Dilarang mengulang daftar opsi di dalam teks bantuan (*help text*) jika opsi tersebut sudah tercantum di dalam elemen `<select>` tepat di atasnya.
5. **Satu Fokus Konteks per Halaman (Mutually Exclusive UI States)**:
   - Jangan pernah menampilkan dua alur utama secara bersamaan di halaman yang sama. Contoh: saat mahasiswa masih terikat pada kelompok yang perlu perbaikan berkas (`$rejectedGroupApplication`), **sembunyikan** pilihan `Pilih Jalur Pendaftaran Magang (Individu / Kelompok)` dan cukup sediakan tombol eksplisit `Keluar dari Kelompok` di bagian bawah kartu jika mahasiswa ingin berpindah jalur.

### B. Daftar Periksa Quality Control (QC) Wajib Sebelum Menyelesaikan Tugas UI
1. **Pemeriksaan Keseimbangan Struktur HTML (`<div>` & `<form>`)**:
   - Pastikan jumlah pembuka `<div` dan penutup `</div>` seimbang serta tidak ada `<form>` bersarang (*nested form*) atau input `name="..."` yang terduplikasi dalam satu form.
2. **Pemeriksaan Anti-Redundansi Otomatis (`UiMinimalismAndAntiRedundancyTest`)**:
   - Jalankan `php artisan test --filter=UiMinimalismAndAntiRedundancyTest` untuk memvalidasi bahwa halaman tidak menampilkan state ganda, tidak memuat teks penjelasan berulang yang dilarang, dan memiliki struktur DOM yang bersih.
3. **Verifikasi Visual Nyata (Playwright Screenshot)**:
   - Setiap perubahan tampilan wajib diperiksa dengan tangkapan layar browser pada skenario data aktual (termasuk skenario anggota kelompok ditolak/diterima), bukan hanya skenario kosong.

---

## 4. Protokol Quality Control (QC) Holistik 9 Domain Sistem (Anti-Myopia)

Dilarang keras membatasi pengujian hanya pada satu modul (mis. pengajuan magang) ketika diminta melakukan QC menyeluruh sistem. Setiap audit wajib memverifikasi kesehatan seluruh 9 domain sistem:

| Domain | Lingkup Modul & Route Kunci | Validasi Wajib |
| :--- | :--- | :--- |
| **1. Notifikasi & Push** | `/notifications`, `/push-subscriptions`, Bell dropdown | Akses seluruh tab category (`all`, `urgent`, `logbook`, `application`, `evaluation`, `university`, `feedback`, `chat`), `markAsRead`, `markAllAsRead`, dan real-time red dot. |
| **2. Chat Antar Peran** | `/chat`, `/chat/api/*`, attachment | Rendering layout sidebar & shell, load percakapan direct/group/placement, kontak API, dan upload lampiran. |
| **3. Portal Mahasiswa** | `/dashboard`, `/student/application`, `/student/logbook`, `/student/final_report`, `/student/projects`, `/student/profile/edit` | Ketiadaan redundansi, form submit normal, upload file 2MB, download surat/sertifikat, dan responsif mobile. |
| **4. Portal Mentor** | `/mentor/dashboard`, `/mentor/logbooks`, `/mentor/evaluations` | Daftar bimbingan divisi, review logbook harian, penilaian lapangan (skala 40%), dan ketiadaan error query. |
| **5. Portal Dosen DPL** | `/lecturer/dashboard`, `/lecturer/monitoring`, `/lecturer/evaluations`, `/lecturer/logbooks` | Monitoring mahasiswa bimbingan, review logbook, penilaian akademik DPL (skala 60%), dan ekspor CSV. |
| **6. Portal Supervisor** | `/supervisor/dashboard`, `/supervisor/applications`, `/supervisor/reports`, `/supervisor/certificates` | Double ACC pengesahan pengajuan, pengesahan transkrip/sertifikat kelulusan, dan laporan monitoring. |
| **7. Admin Dinas & Super Admin** | `/admin/dashboard`, `/admin/applications`, `/admin/users`, `/admin/mentors`, `/admin/agencies`, `/admin/universities`, `/admin/audit-logs`, `/admin/feedbacks` | Manajemen instansi/kuota bidang, persetujuan/penolakan permohonan, penugasan mentor, dan impersonasi (*Login As*). |
| **8. Portal Universitas** | `/university/dashboard`, `/university/lecturers`, `/university/profile` | Penugasan dosen pembimbing ke mahasiswa satu kampus, pengaturan profil & kop surat rektorat. |
| **9. Halaman Publik & Verifikasi** | `/`, `/proyek-dinas`, `/verify-letter/{token}`, `/verify-certificate/{token}` | Landing page hero & kuota modal, katalog proyek publik & komentar, verifikasi QR code surat & sertifikat tanpa login. |

Semua domain di atas wajib divalidasi secara otomatis melalui `tests/Feature/SystemWideHolisticQcTest.php` sebelum menyatakan sistem siap untuk pengujian pengguna (*user testing*).
