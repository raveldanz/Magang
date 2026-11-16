@echo off
setlocal EnableExtensions
title SIP-MAGANG - Dev Server
cd /d "%~dp0"

echo ====================================================
echo        SISTEM INFORMASI MANAJEMEN MAGANG (DEV)
echo ====================================================

rem --- Cari PHP: 1) folder php\  2) PATH  3) instalasi winget  4) XAMPP ----------------
set "PHP_EXE="
if exist "%~dp0php\php.exe" set "PHP_EXE=%~dp0php\php.exe"
if defined PHP_EXE goto php_ok
for /f "delims=" %%P in ('where php 2^>nul') do if not defined PHP_EXE set "PHP_EXE=%%P"
if defined PHP_EXE goto php_ok
for /d %%D in ("%LOCALAPPDATA%\Microsoft\WinGet\Packages\PHP.PHP.8*") do if exist "%%D\php.exe" set "PHP_EXE=%%D\php.exe"
if defined PHP_EXE goto php_ok
if exist "C:\xampp\php\php.exe" set "PHP_EXE=C:\xampp\php\php.exe"
if defined PHP_EXE goto php_ok

echo [GAGAL] PHP tidak ditemukan. Pasang PHP 8.3+ dan daftarkan di PATH.
goto fail

:php_ok
rem php dan node harus ada di PATH untuk jendela Vite
for %%I in ("%PHP_EXE%") do set "PATH=%%~dpI;%PATH%"
if exist "C:\Program Files\nodejs\npm.cmd" set "PATH=C:\Program Files\nodejs;%PATH%"
where npm >nul 2>&1
if errorlevel 1 (
    echo [GAGAL] npm tidak ditemukan. Pasang Node.js terlebih dahulu.
    goto fail
)

echo [1/3] Memastikan PostgreSQL aktif (dilewati bila C:\pgsql tidak ada)...
if exist "C:\pgsql\bin\pg_ctl.exe" "C:\pgsql\bin\pg_ctl.exe" -D "C:\pgsql\data" -l "C:\pgsql\logfile.log" -w -t 30 start >nul 2>&1

echo [2/3] Membuka Vite dev bundler di jendela baru...
start "Vite Dev" cmd /k "npm run dev"

echo [3/3] Menjalankan server Laravel di http://127.0.0.1:8000 ...
echo       Catatan: mode dev hanya untuk laptop ini. Untuk perangkat lain pakai start_demo.bat.
"%PHP_EXE%" artisan serve --port=8000
goto end

:fail
echo.
pause
exit /b 1

:end
endlocal
