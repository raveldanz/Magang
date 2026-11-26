@echo off
setlocal EnableExtensions
title SIP-MAGANG - Pemasangan
cd /d "%~dp0"

echo ====================================================
echo    SIP-MAGANG - PEMASANGAN PERTAMA
echo    Butuh internet. Jangan tutup jendela ini.
echo ====================================================
echo.

where php >nul 2>&1 || (echo [GAGAL] PHP 8.3 atau lebih baru belum terpasang atau belum ada di PATH. & goto fail)
php -r "exit(version_compare(PHP_VERSION, '8.3.0', '>=') ? 0 : 1);" || (echo [GAGAL] Versi PHP terlalu lama. Butuh PHP 8.3 atau lebih baru. & goto fail)
where composer >nul 2>&1 || (echo [GAGAL] Composer belum terpasang: https://getcomposer.org/download/ & goto fail)
where node >nul 2>&1 || (echo [GAGAL] Node.js belum terpasang: https://nodejs.org/ & goto fail)
where npm >nul 2>&1 || (echo [GAGAL] npm belum tersedia. Pasang ulang Node.js. & goto fail)

echo [1/6] Memasang paket PHP...
call composer install --no-interaction || goto fail

echo [2/6] Memasang paket tampilan...
call npm ci --no-audit --no-fund || goto fail

echo [3/6] Membangun tampilan...
call npm run build || goto fail
if exist "public\hot" del /q "public\hot"

echo [4/6] Menyiapkan pengaturan...
if not exist ".env" copy /y ".env.example" ".env" >nul || goto fail
findstr /r /c:"^APP_KEY=base64:" ".env" >nul || (php artisan key:generate --force || goto fail)
rem Tautan penyimpanan dibuat sebelum data demo agar seeder tidak membuat folder biasa di public\storage
if not exist "public\storage" (php artisan storage:link || goto fail)

echo [5/6] Menyiapkan database...
php scripts\setup_database.php || goto fail

echo [6/6] Membersihkan cache...
php artisan optimize:clear || goto fail

echo.
echo  SELESAI. Jalankan start_demo.bat untuk membuka aplikasi.
echo.
pause
exit /b 0

:fail
echo.
echo  Pemasangan BERHENTI. Perbaiki pesan di atas lalu jalankan PASANG.bat lagi.
echo.
pause
exit /b 1
