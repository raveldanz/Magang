@echo off
setlocal EnableExtensions
title SIP-MAGANG - Server Demo (WiFi lokal)
cd /d "%~dp0"

echo ====================================================
echo    SIP-MAGANG - SERVER DEMO UNTUK WIFI LOKAL
echo ====================================================
echo.

rem --- Cari PHP: 1) folder php\ bawaan zip  2) PATH  3) instalasi winget -------------
set "PHP_EXE="
if exist "%~dp0php\php.exe" set "PHP_EXE=%~dp0php\php.exe"
if defined PHP_EXE goto php_ok
for /f "delims=" %%P in ('where php 2^>nul') do if not defined PHP_EXE set "PHP_EXE=%%P"
if defined PHP_EXE goto php_ok
for /d %%D in ("%LOCALAPPDATA%\Microsoft\WinGet\Packages\PHP.PHP.8*") do if exist "%%D\php.exe" set "PHP_EXE=%%D\php.exe"
if defined PHP_EXE goto php_ok
if exist "C:\xampp\php\php.exe" set "PHP_EXE=C:\xampp\php\php.exe"
if defined PHP_EXE goto php_ok

echo [GAGAL] PHP tidak ditemukan.
echo         Pasang PHP 8.3+ (https://windows.php.net/download) atau pakai zip
echo         buatan scripts\make_demo_zip.ps1 yang sudah menyertakan folder php\.
goto fail

:php_ok
rem Worker/penjadwal sisa sesi sebelumnya dihentikan dulu agar tidak berjalan ganda
call :stop_workers

"%PHP_EXE%" "%~dp0scripts\demo_preflight.php"
if errorlevel 1 goto fail

set "DEMO_IP=127.0.0.1"
set "DEMO_PORT=8000"
for /f "usebackq tokens=1,2" %%A in ("%~dp0storage\framework\demo_server.txt") do (
    set "DEMO_IP=%%A"
    set "DEMO_PORT=%%B"
)

rem QR verifikasi surat/sertifikat memakai alamat ini bila aplikasi dibuka lewat localhost
set "APP_PUBLIC_URL=http://%DEMO_IP%:%DEMO_PORT%"

rem Penjadwal (aktivasi magang otomatis, pengingat) dan pekerja antrean (notifikasi, heartbeat)
rem di jendela terpisah yang diperkecil
start "SIP-MAGANG Scheduler" /min "%PHP_EXE%" "%~dp0artisan" schedule:work
start "SIP-MAGANG Queue Worker" /min "%PHP_EXE%" "%~dp0artisan" queue:work --tries=3 --sleep=1

echo  Server berjalan. JANGAN tutup jendela ini selama demo.
echo  Tekan Ctrl+C untuk menghentikan.
echo ====================================================
echo.

rem Batas unggah bawaan PHP hanya 2 MB, sedangkan aplikasi menerima berkas sampai 50 MB
cd /d "%~dp0public"
"%PHP_EXE%" -d upload_max_filesize=64M -d post_max_size=80M -d memory_limit=512M -d max_execution_time=120 -d max_file_uploads=30 -S 0.0.0.0:%DEMO_PORT% "%~dp0vendor\laravel\framework\src\Illuminate\Foundation\resources\server.php"

echo.
echo  Server berhenti. Menghentikan Penjadwal ^& Queue Worker...
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
