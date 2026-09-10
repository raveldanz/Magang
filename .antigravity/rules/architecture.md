# Standar Arsitektur Sistem

Panduan ini mengatur standar arsitektur dan struktur kode untuk pengembangan aplikasi magang berbasis Laravel.

---

## 1. Teknologi Inti
- **Backend**: Laravel (versi terbaru, PHP 8.2+)
- **Frontend**: Blade Templating + Tailwind CSS + Alpine.js
- **Database**: PostgreSQL
- **Build Tool**: Vite

---

## 2. Prinsip Arsitektur (SOLID & Clean Architecture)

Setiap komponen harus mematuhi prinsip SOLID untuk menjaga kode tetap mudah dipelihara (*maintainable*), mudah diuji (*testable*), dan dapat diskalakan (*scalable*):

### A. Single Responsibility Principle (SRP)
- Satu kelas hanya boleh memiliki satu alasan untuk berubah.
- Hindari *Fat Controller* dan *Fat Model*. 

### B. Pemisahan Service Layer & Action Classes
- **Batas Ukuran Controller**: Jika sebuah Controller mulai melebihi **100 baris kode** atau sebuah metode memiliki logika bisnis yang kompleks (> 15 baris), logika tersebut **wajib dipisahkan** ke dalam `Service Layer` atau `Action Class`.
- **Struktur Direktori**:
  - `app/Services/` : Untuk layanan logika bisnis yang menangani alur proses terpadu (contoh: `FinalReportService`, `InstitutionService`).
  - `app/Actions/` : Untuk tindakan tunggal yang dapat digunakan kembali (*reusable single action*, contoh: `ApproveFinalReportAction`, `UploadInstitutionLogoAction`).
- **Form Request Validation**:
  - Validasi HTTP request tidak boleh ditulis langsung di dalam controller.
  - Wajib membuat kelas Form Request tersendiri (`app/Http/Requests/`) untuk menjaga controller tetap bersih.

---

## 3. Konvensi Rute & Controller

Pengorganisasian rute dan controller wajib mematuhi standar resmi Laravel:

### A. Resourceful Controller
Gunakan 7 metode standar RESTful jika memungkinkan:
- `index()` : Menampilkan daftar data
- `create()` : Menampilkan form tambah data
- `store()` : Memproses penyimpanan data baru
- `show()` : Menampilkan detail satu data
- `edit()` : Menampilkan form ubah data
- `update()` : Memproses pembaruan data
- `destroy()` : Menghapus data

### B. Penamaan Rute (Dot Notation)
Format penamaan rute: `[role].[fitur].[aksi]`
- Contoh:
  - `student.final-report.index`
  - `student.final-report.store`
  - `admin.institutions.show`

### C. Pengelompokan Rute (Route Grouping)
Selalu kelompokkan rute berdasarkan middleware autentikasi dan peran:
```php
Route::middleware(['auth', 'role:student'])->prefix('student')->name('student.')->group(function () {
    Route::resource('final-report', StudentFinalReportController::class);
});
```

### D. Dependency Injection
Prioritaskan *Constructor Injection* atau *Method Injection* daripada pemanggilan facade statis yang berlebihan pada layanan kustom:
```php
class FinalReportController extends Controller
{
    public function __construct(
        protected FinalReportService $reportService
    ) {}

    public function store(StoreFinalReportRequest $request)
    {
        $report = $this->reportService->submitReport($request->validated(), auth()->user());
        return redirect()->route('student.final-report.index')->with('success', 'Laporan berhasil diunggah.');
    }
}
```
