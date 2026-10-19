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
