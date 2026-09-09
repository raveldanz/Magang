# Standar Database (PostgreSQL)

Panduan ini mengatur standar perancangan skema, integritas data, dan penulisan query untuk basis data PostgreSQL.

---

## 1. Database Engine
- **Engine**: PostgreSQL 14+
- **Integritas Transaksional**: ACID compliant.
- Gunakan `DB::transaction()` untuk seluruh operasi mutasi yang melibatkan lebih dari satu tabel guna mencegah ketidakkonsistenan data (*partial state*).

---

## 2. Pemilihan Tipe Data (Data Types)

Setiap migrasi Laravel (`database/migrations/`) wajib menggunakan tipe data PostgreSQL yang tepat:

| Keperluan | Tipe Kolom Laravel | Penjelasan & Aturan |
| :--- | :--- | :--- |
| **Primary Key** | `$table->id()` atau `$table->uuid('id')->primary()` | Default menggunakan `bigIncrements` (`bigint`). Gunakan `UUID` jika data memerlukan pengidentifikasi unik non-sekuensial publik. |
| **Relasi Foreign Key** | `$table->foreignId('user_id')` / `$table->foreignUuid('...')` | Wajib berpasangan dengan tipe primary key tabel target. |
| **Data Dinamis / Fleksibel** | `$table->jsonb('metadata')` | Gunakan `jsonb` (bukan `json`) di PostgreSQL untuk mendukung indexing GIN, query biner cepat, dan pencarian nested key. |
| **Waktu & Pencatatan** | `$table->timestamps()` | Selalu gunakan timestamp berpresisi timezone. Tambahkan `$table->softDeletes()` jika data membutuhkan audit trail / pemulihan. |
| **Nilai Moneter / Angka Teliti** | `$table->decimal('amount', 12, 2)` | Hindari float/double untuk nilai moneter atau skor akurat. |
| **Status / Kategori** | `$table->string('status', 30)` atau enum | Integrasikan dengan fitur PHP 8.1+ Enums di model Eloquent untuk *type safety*. |

---

## 3. Larangan Raw Query & Keamanan Query (SQL Injection Prevention)

### ⛔ Larangan Mutlak:
- **DILARANG KERAS** menggunakan interpolasi/konkatenasi string pada raw query:
  ```php
  // SALAH & TIDAK AMAN (VULNERABLE TO SQL INJECTION)
  DB::select("SELECT * FROM students WHERE nim = '" . $nim . "'");
  DB::statement("UPDATE reports SET status = '$status' WHERE id = $id");
  ```

### ✅ Kewajiban:
1. **Gunakan Eloquent ORM atau Query Builder resmi**:
   ```php
   // BENAR: Menggunakan Eloquent
   $student = Student::where('nim', $nim)->firstOrFail();

   // BENAR: Menggunakan Query Builder berparameter
   $reports = DB::table('final_reports')
       ->where('student_id', $studentId)
       ->where('status', 'approved')
       ->get();
   ```
2. **Jika Wajib Menggunakan Raw Query**, wajib gunakan **Parameter Binding**:
   ```php
   $results = DB::select(
       "SELECT * FROM final_reports WHERE status = :status AND created_at >= :date",
       ['status' => $status, 'date' => $startDate]
   );
   ```

---

## 4. Foreign Key Constraints & Indexing

### A. Foreign Key Constraints
Semua relasi antar tabel wajib didefinisikan secara eksplisit di migrasi:
```php
$table->foreignId('institution_id')
      ->constrained('institutions')
      ->cascadeOnUpdate()
      ->restrictOnDelete(); // atau ->cascadeOnDelete() jika data anak wajib ikut terhapus
```

### B. Indexing
Untuk menjaga performa query PostgreSQL saat data bertumbuh:
- **Foreign Key Indexing**: Kolom foreign key wajib diindeks (otomatis oleh `foreignId()->constrained()` atau eksplisit `$table->index('...')`).
- **Kolom Pencarian / Filter Sering**:
  ```php
  // Index tunggal
  $table->index('status');

  // Composite index untuk filter kombinasi
  $table->index(['student_id', 'status']);
  $table->index(['created_at', 'status']);
  ```
- **Unique Constraints**:
  ```php
  $table->unique('nim');
  $table->unique(['student_id', 'academic_year_id']);
  ```
- **JSONB Indexing**: Manfaatkan GIN index jika kolom JSONB sering dicari berdasarkan atribut di dalamnya:
  ```php
  $table->rawIndex("CREATE INDEX idx_reports_meta ON final_reports USING gin (metadata);");
  ```
