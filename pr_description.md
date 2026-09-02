## 📌 Ringkasan Perubahan

Pull Request ini mengimplementasikan serangkaian fitur baru, penyempurnaan alur bisnis kemitraan kampus, serta perbaikan UI/UX pada sistem SIP-MAGANG:

### 1. 🏛️ Skema Kebijakan & Evaluasi Kampus Adaptif (Adaptive University Policy)
* **Migrasi Basis Data:** Menambahkan kolom `evaluation_scheme`, `weight_mentor`, `weight_lecturer`, dan `require_dpl` pada tabel `universities`.
* **Panel Pengaturan Kampus:** Menyediakan UI switch skema di Profil Kampus (`/university/profile`) dan Master Universitas Admin (`/admin/universities/{id}/edit`):
  * **Kemitraan Terpadu (Dua Pihak):** Pembobotan dinamis (Mentor % + DPL % = 100%), wajib memilih DPL, verifikasi logbook 2 arah.
  * **Penilaian Penuh Instansi (100% Dinas):** Penilaian murni dari Mentor Lapangan Dinas, tidak wajib memilih DPL, verifikasi logbook cukup dari dinas.
* **Perhitungan Nilai Akhir & Transkrip:** Menghitung nilai akhir secara dinamis sesuai bobot masing-masing universitas di `Evaluation.php` dan menyesuaikan E-Sertifikat Transkrip Nilai (PDF/Cetak).

### 2. 📒 Logika Adaptif & Keamanan Logbook Mahasiswa
* Mengunci pengisian logbook bagi mahasiswa yang belum memilih DPL (jika kampusnya berskema wajib DPL).
* Membuka akses pengisian logbook langsung bagi mahasiswa dari kampus berskema 100% dinas (`mentor_only`) dengan badge status DPL `— Dilewati`.

### 3. 💬 Sistem Feedback & Pusat Notifikasi Real-time
* Penambahan sistem tiket masukan/kendala operasional (`SystemFeedback`) untuk pelaporan ke Super Admin.
* Pusat Notifikasi lengkap dengan tab filter: *Semua*, *⚡ Perlu Tindakan Segera*, *📋 Pendaftaran*, *🎓 Universitas*, *💬 Feedback*, *📖 Logbook*, dan *📜 Log Audit*.
* Indikator lonceng notifikasi (Red Dot) yang otomatis hilang setelah ditandai dibaca dan muncul kembali jika terdapat tugas urgent tertunda.

### 4. 🎨 Perbaikan UI/UX & Filter Master Pengguna
* Filter dinamis peran pengguna di `/admin/users` yang otomatis menyesuaikan dropdown (beralih antara daftar *Instansi Dinas* dan daftar *Universitas/Kampus*).
* Menghilangkan FOUC/modal popup flash saat transisi halaman portal universitas.
* Penyesuaian label navigasi atas dari `Instansi & Unit` menjadi `Instansi`.

---
### 🧪 Verifikasi & Pengujian
- Seluruh unit & integration test pada alur kebijakan kampus adaptif dan verifikasi logbook telah dijalankan dan berstatus **PASS** (100% hijau).
- Cache views, routes, dan konfigurasi telah dibersihkan.
