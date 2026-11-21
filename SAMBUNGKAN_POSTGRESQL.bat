@echo off
setlocal EnableExtensions
title SIP-MAGANG - Sambungkan ke PostgreSQL
cd /d "%~dp0"
echo ===============================================================================
echo    SIP-MAGANG - SAMBUNGKAN KE POSTGRESQL (dijalankan SEKALI sebelum demo)
echo ===============================================================================
echo  Pakai berkas ini bila komputer ini (atau server kantor) sudah terpasang
echo  PostgreSQL. Tanpa berkas ini, demo memakai database SQLite bawaan zip.
echo  Tekan Enter untuk memakai nilai bawaan di dalam kurung [ ].
echo.

rem Server demo harus mati: pengaturan database tidak boleh diganti saat dipakai
powershell -NoProfile -ExecutionPolicy Bypass -Command "if (@(Get-CimInstance Win32_Process | Where-Object { $_.Name -eq 'php.exe' -and $_.CommandLine -and $_.CommandLine.Contains('sip_magang_demo_server') }).Count -gt 0) { exit 1 } else { exit 0 }"
if errorlevel 1 (
    echo [GAGAL] Server demo masih menyala. Tutup jendela demo ^(Ctrl+C lalu Y^) dulu, lalu ulangi.
    goto end
)

rem PHP: 1) folder php\ bawaan zip  2) PATH  3) winget  4) XAMPP
set "PHP_EXE="
if exist "%~dp0php\php.exe" set "PHP_EXE=%~dp0php\php.exe"
if not defined PHP_EXE for /f "delims=" %%P in ('where php 2^>nul') do if not defined PHP_EXE set "PHP_EXE=%%P"
if not defined PHP_EXE for /d %%D in ("%LOCALAPPDATA%\Microsoft\WinGet\Packages\PHP.PHP.8*") do if exist "%%D\php.exe" set "PHP_EXE=%%D\php.exe"
if not defined PHP_EXE if exist "C:\xampp\php\php.exe" set "PHP_EXE=C:\xampp\php\php.exe"
if not defined PHP_EXE (
    echo [GAGAL] PHP tidak ditemukan. Pakai folder SIP-MAGANG hasil ekstrak zip ^(berisi folder php\^).
    goto end
)

rem Isian (bisa juga diisi lewat variabel lingkungan SIP_PG_* untuk pemasangan otomatis)
if defined SIP_PG_HOST goto ask_done
set "SIP_PG_HOST=127.0.0.1"
set /p "SIP_PG_HOST=Alamat server PostgreSQL [127.0.0.1 = komputer ini]: "
set "SIP_PG_PORT=5432"
set /p "SIP_PG_PORT=Port [5432]: "
set "SIP_PG_USER=postgres"
set /p "SIP_PG_USER=Nama pengguna PostgreSQL [postgres]: "
set "SIP_PG_PASSWORD="
for /f "usebackq delims=" %%P in (`powershell -NoProfile -Command "$p = Read-Host 'Kata sandi PostgreSQL (tidak tampil saat diketik)' -AsSecureString; [Runtime.InteropServices.Marshal]::PtrToStringAuto([Runtime.InteropServices.Marshal]::SecureStringToBSTR($p))"`) do set "SIP_PG_PASSWORD=%%P"
set "SIP_PG_DATABASE=sip_magang"
set /p "SIP_PG_DATABASE=Nama database untuk aplikasi [sip_magang]: "
set "SIP_PG_SYNC=Y"
set /p "SIP_PG_SYNC=Ambil daftar sekolah se-Indonesia sekarang? Butuh internet, 3-5 menit (Y/T) [Y]: "
:ask_done
echo.

"%PHP_EXE%" "%~dp0scripts\pgsql_connect.php"
set "RESULT=%ERRORLEVEL%"
set "SIP_PG_PASSWORD="
if not "%RESULT%"=="0" (
    echo.
    echo  Penyambungan TIDAK berhasil. Baca pesan [GAGAL] di atas, perbaiki, lalu jalankan berkas ini lagi.
    echo  Demo tetap bisa dijalankan dengan SQLite bawaan lewat 1_MULAI_DEMO.bat.
)

:end
echo.
if not defined SIP_PG_NOPAUSE pause
endlocal
