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
rem PHP untuk Windows butuh Microsoft Visual C++ Runtime (vcruntime140.dll). Zip demo sudah
rem menyertakannya di folder php\; bila tetap gagal, pasang VC_redist.x64.exe dari Microsoft.
"%PHP_EXE%" -v >nul 2>&1
if errorlevel 1 goto php_broken

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

rem Saat demo, halaman galat tidak boleh menampilkan detail teknis ke perangkat lain di WiFi.
rem Variabel lingkungan ini mengalahkan APP_DEBUG di .env tanpa mengubah berkasnya.
set "APP_DEBUG=false"
rem Beberapa server aplikasi + gerbang pembagi beban (batas unggah 64 MB, OPcache bila tersedia)
call "%~dp0scripts\demo_serve.bat" "%PHP_EXE%"

echo.
echo  Server berhenti. Menghentikan Penjadwal ^& Queue Worker...
call :stop_workers
pause
goto end

:php_broken
echo [GAGAL] PHP ditemukan (%PHP_EXE%) tetapi tidak bisa dijalankan.
echo         Biasanya karena laptop ini belum punya Microsoft Visual C++ Runtime.
echo         Pasang "VC_redist.x64.exe" (Visual C++ 2015-2022, x64) dari situs resmi Microsoft:
echo         https://aka.ms/vs/17/release/vc_redist.x64.exe  lalu jalankan start_demo.bat lagi.
goto fail

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
