"""
Generator Berkas DOCX Rekapan Logbook Magang
Format: Sesuai Struktur Google Spreadsheet (Tanggal | Report | Problem | Solution | To-do)
Lokasi: scripts/generate_logbook_docx.py
"""

import os
import docx
from docx.shared import Inches, Pt, RGBColor
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.enum.table import WD_TABLE_ALIGNMENT, WD_ALIGN_VERTICAL
from docx.oxml import OxmlElement, parse_xml
from docx.oxml.ns import nsdecls, qn

def set_cell_background(cell, hex_color):
    """Set background color of a cell (e.g. '1E293B')"""
    shading_elm = parse_xml(f'<w:shd {nsdecls("w")} w:fill="{hex_color}"/>')
    cell._tc.get_or_add_tcPr().append(shading_elm)

def set_cell_margins(cell, top=100, bottom=100, left=150, right=150):
    """Set inner padding of a cell in dxa (1 pt = 20 dxa)"""
    tcPr = cell._tc.get_or_add_tcPr()
    tcMar = OxmlElement('w:tcMar')
    for m, val in [('w:top', top), ('w:bottom', bottom), ('w:left', left), ('w:right', right)]:
        node = OxmlElement(m)
        node.set(qn('w:w'), str(val))
        node.set(qn('w:type'), 'dxa')
        tcMar.append(node)
    tcPr.append(tcMar)

def create_logbook_docx():
    doc = docx.Document()

    # Configure Landscape A4
    for section in doc.sections:
        section.orientation = docx.enum.section.WD_ORIENT.LANDSCAPE
        section.page_width = Inches(11.69)
        section.page_height = Inches(8.27)
        section.top_margin = Inches(0.5)
        section.bottom_margin = Inches(0.5)
        section.left_margin = Inches(0.55)
        section.right_margin = Inches(0.55)

    # Document Header / Title
    p_title = doc.add_paragraph()
    p_title.paragraph_format.space_before = Pt(0)
    p_title.paragraph_format.space_after = Pt(2)
    p_title.alignment = WD_ALIGN_PARAGRAPH.CENTER
    run_title = p_title.add_run("REKAPAN LOGBOOK AKTIVITAS MAGANG MAHASISWA")
    run_title.font.name = "Arial"
    run_title.font.size = Pt(14)
    run_title.font.bold = True
    run_title.font.color.rgb = RGBColor(15, 23, 42)

    p_sub = doc.add_paragraph()
    p_sub.paragraph_format.space_after = Pt(8)
    p_sub.alignment = WD_ALIGN_PARAGRAPH.CENTER
    run_sub = p_sub.add_run("Sistem Informasi Manajemen Magang Mahasiswa | Periode: 10 Agustus 2026 – 05 Oktober 2026\nKetentuan Kerja: WFO (Senin, Rabu, Kamis) | WFH (Selasa, Jumat) | OFF (Sabtu, Minggu)")
    run_sub.font.name = "Arial"
    run_sub.font.size = Pt(9)
    run_sub.font.color.rgb = RGBColor(100, 116, 139)

    # Dataset of Entries (57 calendar days)
    entries = [
        # MINGGU 1
        ("Senin, 10 Agt 2026", "WFO",
         "Onboarding magang di kantor dinas, koordinasi pembagian tim pengembang, instalasi environment lokal (PHP 8.3, Composer, Node.js, PostgreSQL), dan inisialisasi repositori Git proyek.",
         "Perbedaan versi ekstensi PHP lokal dengan kebutuhan framework Laravel 11/12.",
         "Konfigurasi php.ini lokal untuk mengaktifkan ekstensi PDO pgsql, mbstring, dan curl.",
         "Merancang arsitektur database awal untuk skema pendaftaran magang."),
        ("Selasa, 11 Agt 2026", "WFH",
         "Perancangan basis data relasional PostgreSQL. Membuat migrasi awal skema tabel users, agency_profiles, units, dan relasi profil mahasiswa.",
         "Hubungan relasi foreign key antara user dan agency profile perlu mendukung multi-tenant dinas.",
         "Menggunakan foreign key nullable agency_profile_id pada tabel users untuk isolasi multi-tenant.",
         "Mengembangkan form profil mahasiswa dan integrasi Laravel Breeze."),
        ("Rabu, 12 Agt 2026", "WFO",
         "Implementasi autentikasi akun mahasiswa dan form pelengkapan profil (NIM, universitas, jurusan, telepon).",
         "Validasi NIM unik perlu diselaraskan dengan universitas asal mahasiswa agar tidak bentrok.",
         "Menerapkan rule validasi unique gabungan antara nim dan university_id.",
         "Membangun modul pemilihan unit kerja dinas dan pengecekan kuota."),
        ("Kamis, 13 Agt 2026", "WFO",
         "Membangun modul pendaftaran magang sisi mahasiswa dengan pemilihan dinas dan unit kerja yang memiliki kuota aktif.",
         "Tampilan pemilihan unit kerja awal menggunakan radio button cards yang memakan terlalu banyak ruang vertikal.",
         "Merefaktor pemilihan unit menjadi dropdown dinamis yang menampilkan sisa kuota secara real-time.",
         "Membuat seeder instansi kedinasan Surabaya dan unit kerja pendukung."),
        ("Jumat, 14 Agt 2026", "WFH",
         "Menyusun seeder instansi dinas Pemkot Surabaya (DatabaseSeeder) dan merapikan layout formulir pendaftaran.",
         "Duplikasi entri seeder saat perintah db:seed dijalankan berulang.",
         "Menggunakan metode firstOrCreate() pada seluruh pembuatan master data seeder.",
         "Merancang alur verifikasi berkas pendaftaran di portal Admin Dinas."),
        ("Sabtu, 15 Agt 2026", "OFF", "Libur Akhir Pekan", "-", "-", "-"),
        ("Minggu, 16 Agt 2026", "OFF", "Libur Akhir Pekan", "-", "-", "-"),

        # MINGGU 2
        ("Senin, 17 Agt 2026", "WFO",
         "Mengikuti peringatan HUT RI di kantor dinas dilanjutkan evaluasi sprint 1 dan pemetaan alur seleksi berkas magang.",
         "Kebutuhan standardisasi tahapan seleksi berkas agar transparan bagi pendaftar.",
         "Merumuskan state lifecycle pendaftaran: pending, verified, accepted, rejected.",
         "Mengimplementasikan controller seleksi pendaftaran di dashboard Admin Dinas."),
        ("Selasa, 18 Agt 2026", "WFH",
         "Mengembangkan logika transisi status pada model Application dan controller seleksi admin dinas.",
         "Status yang tidak valid bisa lolos jika pengguna mengirim payload HTTP manual.",
         "Menambahkan form request validation ketat untuk parameter status dan alasan penolakan jika rejected.",
         "Membuat antarmuka tabel seleksi pelamar di dashboard Admin Dinas."),
        ("Rabu, 19 Agt 2026", "WFO",
         "Membangun tabel seleksi pendaftaran admin dinas dengan filter status, tombol verifikasi berkas, dan modal penolakan.",
         "Tabel data pelamar terpotong saat dibuka pada layar monitor beresolusi rendah.",
         "Membungkus tabel dengan container responsive overflow-x-auto dan penataan kolom ringkas.",
         "Memulai pengembangan modul Logbook harian aktivitas magang mahasiswa."),
        ("Kamis, 20 Agt 2026", "WFO",
         "Mengembangkan fitur CRUD Logbook mahasiswa (input tanggal, deskripsi pekerjaan, unggah foto bukti kegiatan).",
         "Format tanggal logbook rentan tidak seragam antara browser mobile dan desktop.",
         "Menggunakan input date standar ISO YYYY-MM-DD dengan casting Carbon di model Logbook.",
         "Menambahkan validasi tipe MIME dan batas ukuran file foto lampiran logbook."),
        ("Jumat, 21 Agt 2026", "WFH",
         "Menambahkan validasi keamanan unggahan file logbook dan konfigurasi disk private storage.",
         "Mahasiswa berpotensi mengunggah file non-gambar (seperti script PHP/HTML) pada lampiran logbook.",
         "Memperketat rule validasi mimes:jpg,jpeg,png dengan ukuran maksimal 2048 KB.",
         "Merancang modul Pembimbing Lapangan (Mentor Dinas) untuk verifikasi logbook."),
        ("Sabtu, 22 Agt 2026", "OFF", "Libur Akhir Pekan", "-", "-", "-"),
        ("Minggu, 23 Agt 2026", "OFF", "Libur Akhir Pekan", "-", "-", "-"),

        # MINGGU 3
        ("Senin, 24 Agt 2026", "WFO",
         "Mengembangkan role Mentor Dinas (role: mentor) dan tabel pivot Placement untuk penempatan mahasiswa.",
         "Kebutuhan mapping satu mentor membimbing beberapa mahasiswa dari universitas berbeda.",
         "Merancang relasi hasMany mahasiswa pada User mentor melalui model Placement.",
         "Mengimplementasikan dashboard bimbingan khusus mentor dinas."),
        ("Selasa, 25 Agt 2026", "WFH",
         "Membangun dashboard mentor dinas dengan statistik mahasiswa bimbingan aktif dan logbook yang menunggu ACC.",
         "Mentor berpotensi melihat data bimbingan dari dinas instansi lain jika query tidak di-scope.",
         "Menambahkan global scope / middleware tenant scoping berdasarkan agency_profile_id.",
         "Membangun fitur verifikasi logbook (Approval dan Catatan Feedback)."),
        ("Rabu, 26 Agt 2026", "WFO",
         "Mengembangkan halaman verifikasi logbook mahasiswa di sisi mentor: tombol ACC, tombol revisi, dan form catatan evaluasi.",
         "Mentor tidak bisa memberikan koreksi deskriptif jika kegiatan mahasiswa belum sesuai.",
         "Menambahkan kolom feedback dan status verifikasi di tabel logbooks.",
         "Membuat rekap kehadiran dan statistik logbook per mahasiswa."),
        ("Kamis, 27 Agt 2026", "WFO",
         "Membangun halaman detail profil bimbingan mahasiswa di portal mentor dengan timeline riwayat logbook lengkap.",
         "Jumlah entri logbook yang banyak menyebabkan halaman lambat dimuat.",
         "Menerapkan pagination 10 entri per halaman pada riwayat logbook mahasiswa.",
         "Melakukan review performa query pada daftar bimbingan mentor."),
        ("Jumat, 28 Agt 2026", "WFH",
         "Optimasi performa query database pada portal mentor dan perbaikan issue query N+1 pada data profil mahasiswa.",
         "Pemanggilan $placement->application->user->studentProfile memicu query berulang di perulangan @foreach.",
         "Menerapkan eager loading with(['application.user.studentProfile']) pada controller mentor.",
         "Menyiapkan integrasi modul Perguruan Tinggi / Universitas."),
        ("Sabtu, 29 Agt 2026", "OFF", "Libur Akhir Pekan", "-", "-", "-"),
        ("Minggu, 30 Agt 2026", "OFF", "Libur Akhir Pekan", "-", "-", "-"),

        # MINGGU 4
        ("Senin, 31 Agt 2026", "WFO",
         "Mengembangkan modul Perguruan Tinggi (role: universitas): manajemen profil kampus dan pemantauan mahasiswa terdaftar.",
         "Universitas memerlukan akses untuk memantau apakah mahasiswanya diterima atau ditolak di dinas.",
         "Membuat tabel monitoring pelamar kampus dengan filter status seleksi dinas.",
         "Menangani masalah asset storage symlink yang sering 404 pada local server."),
        ("Selasa, 01 Sep 2026", "WFH",
         "Investigasi dan perbaikan aset logo instansi mitra yang mengalami HTTP 404 (LRN-001).",
         "Symlink public/storage belum terhubung ke storage/app/public di sistem operasi Windows.",
         "Menjalankan php artisan storage:link dan menambahkan fallback logo SVG resmi jika file tidak ditemukan.",
         "Audit keamanan query mentah di seluruh service dan model."),
        ("Rabu, 02 Sep 2026", "WFO",
         "Audit keamanan database PostgreSQL: mengeliminasi celah SQL Injection pada filter dinamis (LRN-002).",
         "Penggunaan interpolasi string langsung di dalam DB::raw() berisiko injeksi dan error tipe data.",
         "Mengubah seluruh query ke Eloquent Query Builder dengan array parameter binding eksplisit.",
         "Standardisasi penanganan fallback logo instansi baru tanpa tebak nama."),
        ("Kamis, 03 Sep 2026", "WFO",
         "Menghapus heuristik tebak nama instansi dan menstandardisasi logo fallback default resmi (LRN-003).",
         "Instansi baru yang logonya kosong keliru menampilkan logo Diskominfo karena substring matching str_contains.",
         "Mengganti logika dengan fallback SVG resmi default-agency.svg dan menambahkan alur provisi akun dinas.",
         "Memperbaiki masalah otorisasi berkas laporan akhir mahasiswa."),
        ("Jumat, 04 Sep 2026", "WFH",
         "Menyelesaikan masalah otorisasi storage serving (403 forbidden) pada berkas laporan akhir (LRN-004).",
         "URL berkas laporan bertabrakan dengan signature serve storage privat.",
         "Membuat route dedicated /final-report/download/{id} dengan otorisasi berbasis role pengguna.",
         "Menyiapkan proteksi kuota unit agar tidak berkurang ganda."),
        ("Sabtu, 05 Sep 2026", "OFF", "Libur Akhir Pekan", "-", "-", "-"),
        ("Minggu, 06 Sep 2026", "OFF", "Libur Akhir Pekan", "-", "-", "-"),

        # MINGGU 5
        ("Senin, 07 Sep 2026", "WFO",
         "Membangun proteksi pengurangan kuota unit secara atomic di database transaction (LRN-005).",
         "Permintaan seleksi paralel berpotensi menyebabkan double-decrement kuota unit dinas.",
         "Menggunakan DB::transaction() dan locking lockForUpdate() saat mengubah kuota unit.",
         "Mengembangkan halaman publik verifikasi surat penerimaan magang."),
        ("Selasa, 08 Sep 2026", "WFH",
         "Membangun fitur verifikasi keaslian surat penerimaan magang dengan hash token dan QR Code (LRN-005 & LRN-007).",
         "Surat penerimaan magang format PDF rentan dipalsukan jika tidak memiliki verifikator digital.",
         "Menghasilkan token SHA-256 unik per surat yang mengarah ke endpoint /verify-letter/{token}.",
         "Melakukan pengujian antarmuka pada tampilan layar ponsel (mobile-first)."),
        ("Rabu, 09 Sep 2026", "WFO",
         "Audit antarmuka mobile-first: memperbaiki tabel yang melebar dan tata letak kartu aksi (LRN-009).",
         "Pengguna smartphone harus scroll horizontal sangat jauh untuk melihat tombol aksi tabel.",
         "Mendesain kartu responsif vertikal untuk breakpoint < 640px dengan tombol aksi ukuran sentuh ideal.",
         "Menambahkan proteksi konfirmasi ganda pada aksi penghapusan data master."),
        ("Kamis, 10 Sep 2026", "WFO",
         "Menambahkan modal konfirmasi ganda (double-confirmation modal) untuk aksi hapus instansi & kampus (LRN-010).",
         "Risiko terhapusnya data universitas/instansi yang sudah memiliki relasi data mahasiswa aktif.",
         "Mengharuskan pengguna mengetik nama entitas sebagai konfirmasi sebelum aksi hapus permanen dieksekusi.",
         "Membangun Agency Control Center bagi Super Admin."),
        ("Jumat, 11 Sep 2026", "WFH",
         "Membangun Pusat Kendali Alur Dinas (Agency Hub) bagi Super Admin (LRN-011).",
         "Super Admin kesulitan memantau dinas mana saja yang belum memiliki akun admin resmi.",
         "Menambahkan indikator status akun Belum Ada Akun vs Akun Aktif serta tombol buat akun instan.",
         "Mengembangkan modul sertifikat kelulusan digital."),
        ("Sabtu, 12 Sep 2026", "OFF", "Libur Akhir Pekan", "-", "-", "-"),
        ("Minggu, 13 Sep 2026", "OFF", "Libur Akhir Pekan", "-", "-", "-"),

        # MINGGU 6
        ("Senin, 14 Sep 2026", "WFO",
         "Mengembangkan modul sertifikat kelulusan digital dengan nomor seri otomatis dan QR hash acak (LRN-013).",
         "ID numerik sekuensial pada sertifikat mudah ditebak oleh pihak luar (risiko IDOR).",
         "Menerapkan kolom certificate_hash acak 32 karakter untuk verifikasi publik di /verify-certificate/{hash}.",
         "Mencegah mahasiswa mendaftar ganda saat sudah aktif magang."),
        ("Selasa, 15 Sep 2026", "WFH",
         "Membangun Integrity Guard pencegahan pengajuan ganda (duplicate application guard) (LRN-014).",
         "Mahasiswa yang sudah berstatus aktif dapat mengirim formulir pendaftaran baru yang merusak data penempatan.",
         "Menambahkan pengecekan di controller dan database constraint untuk mencegah pendaftaran duplikat.",
         "Menstandardisasi posisi vertikal kartu statistik dashboard."),
        ("Rabu, 16 Sep 2026", "WFO",
         "Merapikan alignment vertikal angka metrik kartu statistik di dashboard seluruh peran (LRN-016 & LRN-030).",
         "Panjang judul kartu yang bervariasi menyebabkan posisi angka metrik naik-turun tidak sejajar.",
         "Menata struktur flexbox dengan mt-auto sehingga baseline angka selalu sejajar sempurna.",
         "Menangani alur mahasiswa yang mengundurkan diri (resigned)."),
        ("Kamis, 17 Sep 2026", "WFO",
         "Mengintegrasikan status pengunduran diri (resigned) dan pemulihan kuota unit kerja otomatis (LRN-017).",
         "Mahasiswa yang mengundurkan diri tetap tampil berstatus aktif dan kuota unit dinas tertahan.",
         "Menambahkan transisi status resigned, memulihkan kuota unit (+1), dan mengarsipkan penempatan.",
         "Menambahkan validasi prasyarat kelulusan magang."),
        ("Jumat, 18 Sep 2026", "WFH",
         "Membangun validasi prasyarat kelulusan: tombol kelulusan terkunci sebelum nilai evaluasi masuk (LRN-018).",
         "Admin dinas dapat meluluskan mahasiswa secara prematur sebelum mentor mengisi lembar evaluasi.",
         "Menambahkan gate check has('evaluation') sebelum aksi kelulusan dapat diproses.",
         "Mengembangkan modal preview dokumen laporan akhir dan lightbox foto logbook."),
        ("Sabtu, 19 Sep 2026", "OFF", "Libur Akhir Pekan", "-", "-", "-"),
        ("Minggu, 20 Sep 2026", "OFF", "Libur Akhir Pekan", "-", "-", "-"),

        # MINGGU 7
        ("Senin, 21 Sep 2026", "WFO",
         "Mengembangkan modal interaktif live preview berkas PDF laporan akhir dan lightbox foto logbook (LRN-019).",
         "Pembimbing harus mengunduh file laporan berulang kali hanya untuk memeriksa kelengkapan draf.",
         "Memasang modal preview PDF tersemat (embedded iframe) dan modal foto popup interaktif.",
         "Mengatasi memory leak pada serialisasi model Eloquent."),
        ("Selasa, 22 Sep 2026", "WFH",
         "Investigasi dan perbaikan memory exhaustion 512MB pada serialisasi model Eloquent (LRN-020).",
         "Pemanggilan relasi rekursif di accessor model memicu circular dependency saat di-encode ke JSON.",
         "Menghapus recursive call pada model dan membatasi relasi yang di-append otomatis.",
         "Standardisasi arsitektur status magang dengan BackedEnum PHP 8.2."),
        ("Rabu, 23 Sep 2026", "WFO",
         "Migrasi seluruh representasi status magang ke PHP 8.2 BackedEnum ApplicationStatus (LRN-021 & LRN-022).",
         "TypeError saat fungsi string seperti strtolower() menerima objek Enum di template Blade.",
         "Memastikan seluruh pemanggilan Enum di Blade mengekstrak property ->value secara type-safe.",
         "Memperbaiki alur seleksi admin dan integrasi QR TTE surat balasan."),
        ("Kamis, 24 Sep 2026", "WFO",
         "Menata ulang halaman seleksi admin menjadi kartu pipeline interaktif dan integrasi QR TTE offline (LRN-023).",
         "API QR eksternal sering timeout saat server tidak memiliki akses internet langsung.",
         "Memigrasikan generator QR ke package lokal simplesoftwareio/simple-qrcode yang bekerja 100% offline.",
         "Menyiapkan framework otomasi pengujian browser headless lokal."),
        ("Jumat, 25 Sep 2026", "WFH",
         "Menyiapkan runner visual Playwright dan Puppeteer lokal mengunci ke Google Chrome sistem (LRN-025 & LRN-033).",
         "Driver internal Playwright mengalami kendala 404 CDN di environment IDE.",
         "Mengunci runner ke saluran Google Chrome sistem (channel: 'chrome') dengan mode eksekusi mandiri.",
         "Menyelesaikan harmonisasi kuota dinas dan standardisasi portal mahasiswa."),
        ("Sabtu, 26 Sep 2026", "OFF", "Libur Akhir Pekan", "-", "-", "-"),
        ("Minggu, 27 Sep 2026", "OFF", "Libur Akhir Pekan", "-", "-", "-"),

        # MINGGU 8
        ("Senin, 28 Sep 2026", "WFO",
         "Harmonisasi kuota dinas vs unit, perbaikan fungsi delete logbook, dan standardisasi lebar halaman (LRN-026 & LRN-027).",
         "Terjadi disparitas lebar container halaman antara profil (max-w-4xl) dan logbook (max-w-7xl).",
         "Menstandarkan seluruh halaman portal mahasiswa ke container max-w-7xl yang seragam.",
         "Mengembangkan fitur status akun pengguna dan indeks database."),
        ("Selasa, 29 Sep 2026", "WFH",
         "Mengembangkan fitur toggle status akun Aktif/Nonaktif dan menambahkan indeks foreign key PostgreSQL.",
         "Query pencarian pengguna melambat seiring bertambahnya data transaksi.",
         "Menambahkan index komposit pada kolom foreign key dan status pengguna.",
         "Membangun modul pesan instan (Chat) lintas-role."),
        ("Rabu, 30 Sep 2026", "WFO",
         "Mengembangkan modul Chat real-time lintas-role (Admin, DPL, Mentor, Mahasiswa) dengan percakapan personal dan grup.",
         "Respon endpoint fetch /chat/api/* mengembalikan redirect 302 jika validasi gagal.",
         "Mengonfigurasi shouldRenderJsonWhen agar endpoint chat konsisten membalas format JSON 422 (LRN-039).",
         "Menambahkan fitur lampiran media privat dan prioritas pesan kedinasan."),
        ("Kamis, 01 Okt 2026", "WFO",
         "Mengembangkan penyimpanan lampiran chat privat (foto, audio, PDF) dan Chat Priority Engine (LRN-041 & LRN-042).",
         "Pesan penting terkait tenggat magang atau seleksi mendesak tertimbun di daftar chat.",
         "Membangun Smart Priority Engine berbasis status lifecycle magang dengan badge urgensi otomatis.",
         "Mengembangkan modul Dosen Pembimbing Lapangan (DPL Kampus)."),
        ("Jumat, 02 Okt 2026", "WFH",
         "Mengembangkan portal Dosen Pembimbing Lapangan (DPL): monitoring bimbingan, verifikasi logbook, dan form evaluasi akademik.",
         "Perlunya pemisahan peran dan kewenangan antara DPL Kampus (akademik) dan Mentor Lapangan (teknis dinas).",
         "Mengimplementasikan scoping query berbasis academic_advisor_id terisolasi dari mentor dinas.",
         "Melaksanakan audit pengujian komprehensif pada Controller Dosen."),
        ("Sabtu, 03 Okt 2026", "OFF", "Libur Akhir Pekan", "-", "-", "-"),
        ("Minggu, 04 Okt 2026", "OFF", "Libur Akhir Pekan", "-", "-", "-"),

        # MINGGU 9
        ("Senin, 05 Okt 2026", "WFO",
         "1) Melaksanakan pengujian menyeluruh Controller Dosen (Dashboard, Monitoring, Logbook, Evaluasi, Anti-IDOR Role Guard). 2) Membuat slide presentasi interaktif PPTX V2 dan Web Presentation Deck dengan bukti fisik Before vs After. 3) Melakukan sanitasi codebase: eliminasi controller & view mati Pembimbing/, merapikan 33 skrip scratch ke scripts/archive/, dan penguatan test suite hingga 177 test lulus 100% (Strict Exit Code 0). 4) Melakukan commit dan push ke remote branch AkhirMagang-Evan.",
         "Terdapat dependensi PHP GD pada unit test chat serta file temporary PPT yang mengotori status git.",
         "Merefaktor test ke UploadedFile::fake()->create(), menambahkan ~$* ke .gitignore, dan merapikan folder skrip (LRN-043).",
         "Menyelesaikan laporan rekapitulasi logbook akhir magang dan persiapan presentasi evaluasi hasil.")
    ]

    # Create Table: 5 columns
    # Col 0: Tanggal (1.4 in)
    # Col 1: Report (3.2 in)
    # Col 2: Problem (2.0 in)
    # Col 3: Solution (2.0 in)
    # Col 4: To-do (1.9 in)
    col_widths = [Inches(1.4), Inches(3.2), Inches(2.0), Inches(2.0), Inches(1.9)]
    
    # We add:
    # Row 0: Super-Header (Tanggal | Evan - Mahasiswa Magang)
    # Row 1: Sub-Headers (Tanggal | Report | Problem | Solution | To-do)
    # Rows 2..N: Data rows
    total_rows = 2 + len(entries)
    table = doc.add_table(rows=total_rows, cols=5)
    table.alignment = WD_TABLE_ALIGNMENT.CENTER
    table.autofit = False

    # Format Super-Header (Row 0)
    # Cell 0: "Tanggal", Cells 1-4 merged: "Nama Mahasiswa: Evan (Web Developer / Fullstack Engineer)"
    r0 = table.rows[0]
    r0_c0 = r0.cells[0]
    r0_c1 = r0.cells[1]
    r0_c1.merge(r0.cells[4]) # Merge cols 1 to 4
    
    set_cell_background(r0_c0, "0F172A") # Deep Slate 900
    set_cell_margins(r0_c0, top=140, bottom=140, left=140, right=140)
    p = r0_c0.paragraphs[0]
    p.alignment = WD_ALIGN_PARAGRAPH.CENTER
    run = p.add_run("Tanggal")
    run.font.name = "Arial"
    run.font.size = Pt(9.5)
    run.font.bold = True
    run.font.color.rgb = RGBColor(255, 255, 255)

    set_cell_background(r0_c1, "1E293B") # Slate 800
    set_cell_margins(r0_c1, top=140, bottom=140, left=140, right=140)
    p = r0_c1.paragraphs[0]
    p.alignment = WD_ALIGN_PARAGRAPH.LEFT
    run = p.add_run("  Nama: Evan (Fullstack Software Engineer)  •  Project: SIM Magang Pemkot Surabaya")
    run.font.name = "Arial"
    run.font.size = Pt(10)
    run.font.bold = True
    run.font.color.rgb = RGBColor(56, 189, 248) # Cyan

    # Format Sub-Headers (Row 1)
    headers = ["Tanggal & Mode", "Report (Aktivitas)", "Problem (Kendala)", "Solution (Solusi)", "To-do (Rencana Lanjut)"]
    r1 = table.rows[1]
    for i, h in enumerate(headers):
        cell = r1.cells[i]
        set_cell_background(cell, "334155") # Slate 700
        set_cell_margins(cell, top=120, bottom=120, left=120, right=120)
        p = cell.paragraphs[0]
        p.alignment = WD_ALIGN_PARAGRAPH.CENTER if i == 0 else WD_ALIGN_PARAGRAPH.LEFT
        run = p.add_run(h)
        run.font.name = "Arial"
        run.font.size = Pt(9)
        run.font.bold = True
        run.font.color.rgb = RGBColor(255, 255, 255)

    # Populate Data Rows (Row 2 onwards)
    for row_idx, entry in enumerate(entries, start=2):
        row = table.rows[row_idx]
        tgl_str, mode, report, prob, sol, todo = entry
        is_off = (mode == "OFF")

        # Color palette
        if is_off:
            bg_color = "F1F5F9" # Light gray
            text_color = RGBColor(148, 163, 184) # Muted
        elif (row_idx % 2 == 0):
            bg_color = "FFFFFF"
            text_color = RGBColor(30, 41, 59)
        else:
            bg_color = "F8FAFC"
            text_color = RGBColor(30, 41, 59)

        # Col 0: Tanggal & Badge Mode
        c0 = row.cells[0]
        set_cell_background(c0, bg_color)
        set_cell_margins(c0, top=100, bottom=100, left=100, right=100)
        p0 = c0.paragraphs[0]
        p0.alignment = WD_ALIGN_PARAGRAPH.CENTER
        
        # Day & Date
        parts = tgl_str.split(", ")
        day_name = parts[0]
        date_part = parts[1] if len(parts) > 1 else ""
        
        r_day = p0.add_run(f"{day_name}\n{date_part}\n")
        r_day.font.name = "Arial"
        r_day.font.size = Pt(8.5)
        r_day.font.bold = True
        r_day.font.color.rgb = RGBColor(15, 23, 42) if not is_off else RGBColor(148, 163, 184)

        # Mode Badge
        r_mode = p0.add_run(f"[{mode}]")
        r_mode.font.name = "Arial"
        r_mode.font.size = Pt(8)
        r_mode.font.bold = True
        if mode == "WFO":
            r_mode.font.color.rgb = RGBColor(37, 99, 235) # Blue
        elif mode == "WFH":
            r_mode.font.color.rgb = RGBColor(13, 148, 136) # Teal
        else:
            r_mode.font.color.rgb = RGBColor(148, 163, 184) # Gray

        # Data Columns 1 to 4
        col_data = [report, prob, sol, todo]
        for c_idx, text_val in enumerate(col_data, start=1):
            cell = row.cells[c_idx]
            set_cell_background(cell, bg_color)
            set_cell_margins(cell, top=100, bottom=100, left=100, right=100)
            p = cell.paragraphs[0]
            p.alignment = WD_ALIGN_PARAGRAPH.LEFT
            
            run = p.add_run(text_val)
            run.font.name = "Arial"
            run.font.size = Pt(8.5)
            run.font.color.rgb = text_color
            if is_off:
                run.font.italic = True

    # Set column widths strictly on all cells
    for row in table.rows:
        for i, w in enumerate(col_widths):
            if len(row.cells) > i:
                row.cells[i].width = w

    # Set borders via XML
    tblPr = table._tbl.tblPr
    borders = parse_xml(
        f'<w:tblBorders {nsdecls("w")}>'
        '<w:top w:val="single" w:sz="4" w:space="0" w:color="CBD5E1"/>'
        '<w:bottom w:val="single" w:sz="6" w:space="0" w:color="94A3B8"/>'
        '<w:left w:val="none"/>'
        '<w:right w:val="none"/>'
        '<w:insideH w:val="single" w:sz="4" w:space="0" w:color="E2E8F0"/>'
        '<w:insideV w:val="single" w:sz="4" w:space="0" w:color="E2E8F0"/>'
        '</w:tblBorders>'
    )
    tblPr.append(borders)

    # Save to Root and Public
    project_root = r"C:\Magang-main"
    out_root = os.path.join(project_root, "Rekapan_Logbook_Magang_10Agt_05Okt.docx")
    out_public = os.path.join(project_root, "public", "Rekapan_Logbook_Magang_10Agt_05Okt.docx")
    
    doc.save(out_root)
    doc.save(out_public)
    print(f"[OK] Berkas DOCX berhasil dibuat di: {out_root}")
    print(f"[OK] Salinan publik dibuat di: {out_public}")

if __name__ == "__main__":
    create_logbook_docx()
