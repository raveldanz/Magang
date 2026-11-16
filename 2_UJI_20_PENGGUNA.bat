@echo off
setlocal EnableExtensions
title SIP-MAGANG - Uji 20 Pengguna Serentak
cd /d "%~dp0"
echo ====================================================
echo    SIP-MAGANG - UJI 20 PENGGUNA SERENTAK
echo ====================================================
echo  Jalankan SETELAH 1_MULAI_DEMO.bat menyala (jendela server tetap terbuka).
echo  Meniru 20 peserta yang login lalu membuka dasbor, logbook, notifikasi,
echo  dan chat selama 1 menit. Hanya membaca, tidak mengubah data magang.
echo.

call "%~dp0scripts\find_php.bat"
if not defined PHP_EXE (
    echo [GAGAL] PHP tidak ditemukan.
    goto end
)

set "DEMO_PORT=8000"
if exist "%~dp0storage\framework\demo_server.txt" (
    for /f "usebackq tokens=2" %%A in ("%~dp0storage\framework\demo_server.txt") do set "DEMO_PORT=%%A"
)

rem Alamat lain (mis. dari laptop lain): 2_UJI_20_PENGGUNA.bat http://192.168.1.23:8000
set "TARGET=%~1"
if "%TARGET%"=="" set "TARGET=http://127.0.0.1:%DEMO_PORT%"

"%PHP_EXE%" "%~dp0scripts\demo_loadtest.php" --url=%TARGET% --users=20 --seconds=60 --think=5000

:end
echo.
pause
endlocal
