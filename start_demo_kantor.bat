@echo off
setlocal EnableExtensions
title SIP-MAGANG - Server Uji Coba Kantor (PostgreSQL + WiFi)
cd /d "%~dp0"

echo ===============================================================================
echo     SIP-MAGANG SURABAYA - SERVER PENGUJIAN KANTOR (POSTGRESQL + WIFI)
echo ===============================================================================
echo.

rem 1. Menyalakan PostgreSQL portabel (C:\pgsql) bila ada; -w menunggu sampai siap menerima koneksi
echo [1/6] Memeriksa ^& mengaktifkan layanan database PostgreSQL...
if not exist "C:\pgsql\bin\pg_ctl.exe" goto pg_done
if not exist "C:\pgsql\data\postmaster.pid" goto pg_start
tasklist /FI "IMAGENAME eq postgres.exe" 2>nul | find /I "postgres.exe" >nul
if errorlevel 1 del /f /q "C:\pgsql\data\postmaster.pid" >nul 2>&1
:pg_start
"C:\pgsql\bin\pg_ctl.exe" -D "C:\pgsql\data" -l "C:\pgsql\logfile.log" -w -t 30 start >nul 2>&1
:pg_done

rem 2. Mencari PHP: 1) folder php\ bawaan zip  2) PATH  3) instalasi winget  4) XAMPP
echo [2/6] Memeriksa lingkungan PHP 8.3...
set "PHP_EXE="
if exist "%~dp0php\php.exe" set "PHP_EXE=%~dp0php\php.exe"
if defined PHP_EXE goto php_ready
for /f "delims=" %%P in ('where php 2^>nul') do if not defined PHP_EXE set "PHP_EXE=%%P"
if defined PHP_EXE goto php_ready
for /d %%D in ("%LOCALAPPDATA%\Microsoft\WinGet\Packages\PHP.PHP.8*") do if exist "%%D\php.exe" set "PHP_EXE=%%D\php.exe"
if defined PHP_EXE goto php_ready
if exist "C:\xampp\php\php.exe" set "PHP_EXE=C:\xampp\php\php.exe"
if defined PHP_EXE goto php_ready

echo [GAGAL] PHP 8.3+ tidak ditemukan di sistem.
echo         Pastikan PHP terinstal dan terdaftar di PATH.
goto fail

:php_ready
rem Worker/penjadwal sisa sesi sebelumnya dihentikan dulu agar tidak berjalan ganda
call :stop_workers

rem 3. Preflight: .env PostgreSQL, migrasi (+ data demo bila database kosong), tautan storage, sinkron status
echo [3/6] Sinkronisasi database PostgreSQL ^& status magang otomatis...
"%PHP_EXE%" "%~dp0scripts\demo_preflight.php" --pgsql
if errorlevel 1 goto fail

rem 4. Membaca IP jaringan WiFi kantor ^& port yang tersedia
set "DEMO_IP=127.0.0.1"
set "DEMO_PORT=8000"
for /f "usebackq tokens=1,2" %%A in ("%~dp0storage\framework\demo_server.txt") do (
    set "DEMO_IP=%%A"
    set "DEMO_PORT=%%B"
)

rem QR verifikasi surat/sertifikat memakai alamat ini bila aplikasi dibuka lewat localhost
set "APP_PUBLIC_URL=http://%DEMO_IP%:%DEMO_PORT%"

echo [4/6] Menjalankan Queue Worker ^& Penjadwal di jendela terpisah (diperkecil)...
start "SIP-MAGANG Queue Worker" /min "%PHP_EXE%" "%~dp0artisan" queue:work --tries=3 --sleep=1
start "SIP-MAGANG Scheduler" /min "%PHP_EXE%" "%~dp0artisan" schedule:work

rem Browser dibuka beberapa detik kemudian, setelah server di bawah sempat hidup
echo [5/6] Membuka antarmuka aplikasi di peramban...
start "" /min cmd /c "timeout /t 3 /nobreak >nul & start http://localhost:%DEMO_PORT%"

echo.
echo ===============================================================================
echo   ALAMAT AKSES APLIKASI (PASTIKAN TERHUBUNG KE WIFI KANTOR YANG SAMA)
echo ===============================================================================
echo   * Di Laptop Anda Sendiri : http://localhost:%DEMO_PORT%
echo   * Dari HP / Laptop Siswa : http://%DEMO_IP%:%DEMO_PORT%
echo.
echo   AKUN DEMO PENGUJIAN (Kata sandi semua akun: password):
echo   -----------------------------------------------------------------------------
echo   [Admin Dinas]      admin.dispusip@surabaya.go.id  (Verifikasi ^& Pantau)
echo   [Mahasiswa Aktif]  mahasiswa.aktif@unesa.ac.id    (Isi Logbook ^& Proyek)
echo   [Mahasiswa Lulus]  mahasiswa.lulus@unesa.ac.id    (Unduh E-Sertifikat)
echo   [Mentor Lapangan]  mentor.dispusip@surabaya.go.id (Nilai Logbook ^& 40%% Nilai)
echo   [Dosen DPL]        dosen.unesa@unesa.ac.id        (Monitoring ^& 60%% Nilai)
echo   [Supervisor]       supervisor@dispusip.surabaya.go.id (Pengesahan Dokumen)
echo ===============================================================================
echo [6/6] Server HTTP aktif (batas unggah 64 MB).
echo       JANGAN tutup jendela ini selama pengujian berlangsung!
echo       Tekan Ctrl + C untuk mematikan server.
echo ===============================================================================
echo.

cd /d "%~dp0public"
"%PHP_EXE%" -d upload_max_filesize=64M -d post_max_size=80M -d memory_limit=512M -d max_execution_time=120 -d max_file_uploads=30 -S 0.0.0.0:%DEMO_PORT% "%~dp0vendor\laravel\framework\src\Illuminate\Foundation\resources\server.php"

echo.
echo  Server berhenti. Menghentikan Queue Worker ^& Penjadwal...
call :stop_workers
pause
goto end

:fail
echo.
echo  Server TIDAK dijalankan. Perbaiki pesan [GAGAL] di atas lalu coba lagi.
echo.
pause
exit /b 1

rem --- Menghentikan php.exe milik proyek ini yang menjalankan artisan queue:* / schedule:* ---
:stop_workers
set "SIPMAGANG_ARTISAN=%~dp0artisan"
powershell -NoProfile -ExecutionPolicy Bypass -Command "Get-CimInstance Win32_Process | Where-Object { $_.Name -eq 'php.exe' -and $_.CommandLine -and $_.CommandLine.Contains($env:SIPMAGANG_ARTISAN) -and $_.CommandLine -match 'queue:|schedule:' } | ForEach-Object { Stop-Process -Id $_.ProcessId -Force -ErrorAction SilentlyContinue }" >nul 2>&1
exit /b 0

:end
endlocal
