@echo off
setlocal EnableExtensions
title SIP-MAGANG - Pembaruan
cd /d "%~dp0"

echo ====================================================
echo    SIP-MAGANG - SESUDAH "git pull"
echo    Data yang sudah ada tidak dihapus.
echo    Tutup dulu server yang sedang berjalan.
echo ====================================================
echo.

if not exist ".env" (echo [GAGAL] Belum pernah dipasang. Jalankan PASANG.bat dulu. & goto fail)

echo [1/5] Memasang paket PHP...
call composer install --no-interaction || goto fail

echo [2/5] Memasang paket tampilan...
call npm ci --no-audit --no-fund || goto fail

echo [3/5] Membangun tampilan...
call npm run build || goto fail
if exist "public\hot" del /q "public\hot"

echo [4/5] Memperbarui database...
php artisan optimize:clear || goto fail
php scripts\setup_database.php --update || goto fail

echo [5/5] Memeriksa tautan penyimpanan...
if not exist "public\storage" (php artisan storage:link || goto fail)

echo.
echo  SELESAI. Jalankan start_demo.bat untuk membuka aplikasi.
echo.
pause
exit /b 0

:fail
echo.
echo  Pembaruan BERHENTI. Perbaiki pesan di atas lalu jalankan PERBARUI.bat lagi.
echo.
pause
exit /b 1
