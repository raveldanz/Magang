@echo off
setlocal EnableExtensions
title SIP-MAGANG - Kembalikan Data Demo ke Awal
cd /d "%~dp0"
echo ====================================================
echo    SIP-MAGANG - KEMBALIKAN DATA DEMO KE KEADAAN AWAL
echo ====================================================
echo  Semua perubahan selama uji coba (akun baru, pengajuan, logbook, chat)
echo  akan hilang dan data kembali seperti saat zip diekstrak.
echo  Data saat ini DISIMPAN dulu ke folder database\cadangan.
echo.

if not exist "%~dp0database\database.awal.sqlite" (
    echo [GAGAL] database\database.awal.sqlite tidak ada. Berkas ini hanya ada di zip demo
    echo         buatan scripts\make_demo_zip.ps1.
    goto end
)

rem Server harus mati dulu: database yang sedang dipakai tidak bisa ditimpa
powershell -NoProfile -ExecutionPolicy Bypass -Command "if (@(Get-CimInstance Win32_Process | Where-Object { $_.Name -eq 'php.exe' -and $_.CommandLine -and $_.CommandLine.Contains('sip_magang_demo_server') }).Count -gt 0) { exit 1 } else { exit 0 }"
if errorlevel 1 (
    echo [GAGAL] Server demo masih menyala. Tutup jendela demo ^(Ctrl+C lalu Y^) dulu,
    echo         lalu jalankan berkas ini lagi.
    goto end
)

choice /c YT /m "Kembalikan data demo ke awal sekarang"
if errorlevel 2 goto end

for /f %%T in ('powershell -NoProfile -Command "Get-Date -Format yyyyMMdd-HHmmss"') do set "STAMP=%%T"
if not exist "%~dp0database\cadangan" mkdir "%~dp0database\cadangan"
if exist "%~dp0database\database.sqlite" copy /y "%~dp0database\database.sqlite" "%~dp0database\cadangan\database-%STAMP%.sqlite" >nul
del /f /q "%~dp0database\database.sqlite-wal" "%~dp0database\database.sqlite-shm" >nul 2>&1
copy /y "%~dp0database\database.awal.sqlite" "%~dp0database\database.sqlite" >nul
if errorlevel 1 (
    echo [GAGAL] Gagal menyalin database awal.
    goto end
)
echo.
echo [OK] Data demo kembali ke awal. Data sebelumnya disimpan di:
echo      database\cadangan\database-%STAMP%.sqlite
echo      Jalankan 1_MULAI_DEMO.bat lagi.

:end
echo.
pause
endlocal
