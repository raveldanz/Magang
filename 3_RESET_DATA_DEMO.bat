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

rem Server harus mati dulu: database yang sedang dipakai tidak bisa ditimpa
powershell -NoProfile -ExecutionPolicy Bypass -Command "if (@(Get-CimInstance Win32_Process | Where-Object { $_.Name -eq 'php.exe' -and $_.CommandLine -and $_.CommandLine.Contains('sip_magang_demo_server') }).Count -gt 0) { exit 1 } else { exit 0 }"
if errorlevel 1 (
    echo [GAGAL] Server demo masih menyala. Tutup jendela demo ^(Ctrl+C lalu Y^) dulu,
    echo         lalu jalankan berkas ini lagi.
    goto end
)

findstr /b /i "DB_CONNECTION=pgsql" "%~dp0.env" >nul 2>&1
if not errorlevel 1 goto reset_pgsql

if not exist "%~dp0database\database.awal.sqlite" (
    echo [GAGAL] database\database.awal.sqlite tidak ada. Berkas ini hanya ada di zip demo
    echo         buatan scripts\make_demo_zip.ps1.
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
goto end

:reset_pgsql
if not exist "%~dp0database\pgsql\magang.dump" goto reset_pgsql_seed
choice /c YT /m "Kembalikan data PostgreSQL ke salinan awal (database\pgsql\magang.dump) sekarang"
if errorlevel 2 goto end
call "%~dp0scripts\pgsql_common.bat"
if errorlevel 1 goto end
for /f %%T in ('powershell -NoProfile -Command "Get-Date -Format yyyyMMdd-HHmmss"') do set "STAMP=%%T"
if not exist "%~dp0database\cadangan" mkdir "%~dp0database\cadangan"
"%PG_BIN%\pg_dump.exe" -Fc --no-owner --no-privileges -f "%~dp0database\cadangan\magang-%STAMP%.dump" "%PGDATABASE%" >nul 2>&1
call "%~dp0pgsql_impor.bat" "%PGDATABASE%" /y
if errorlevel 1 goto end
echo [OK] Data PostgreSQL sebelumnya dicadangkan di:
echo      database\cadangan\magang-%STAMP%.dump
goto end

rem --- PostgreSQL tanpa salinan dump (zip demo + SAMBUNGKAN_POSTGRESQL.bat): isi ulang data demo dari seeder ---
:reset_pgsql_seed
set "PHP_EXE="
if exist "%~dp0php\php.exe" set "PHP_EXE=%~dp0php\php.exe"
if not defined PHP_EXE for /f "delims=" %%P in ('where php 2^>nul') do if not defined PHP_EXE set "PHP_EXE=%%P"
if not defined PHP_EXE (
    echo [GAGAL] PHP tidak ditemukan.
    goto end
)
echo  Database PostgreSQL akan DIKOSONGKAN lalu diisi ulang dengan data demo awal.
if /i "%~1"=="/y" goto reset_pgsql_seed_go
choice /c YT /m "Lanjutkan"
if errorlevel 2 goto end
:reset_pgsql_seed_go
rem Cadangan dulu bila alat PostgreSQL (pg_dump) terpasang di komputer ini
call "%~dp0scripts\pgsql_common.bat" >nul 2>&1
if errorlevel 1 goto reset_pgsql_seed_nobackup
for /f %%T in ('powershell -NoProfile -Command "Get-Date -Format yyyyMMdd-HHmmss"') do set "STAMP=%%T"
if not exist "%~dp0database\cadangan" mkdir "%~dp0database\cadangan"
"%PG_BIN%\pg_dump.exe" -Fc --no-owner --no-privileges -f "%~dp0database\cadangan\pgsql-%STAMP%.dump" "%PGDATABASE%" >nul 2>&1
if not errorlevel 1 echo [OK] Data sebelumnya dicadangkan di database\cadangan\pgsql-%STAMP%.dump
:reset_pgsql_seed_nobackup
"%PHP_EXE%" "%~dp0artisan" migrate:fresh --seed --force
if errorlevel 1 (
    echo [GAGAL] Gagal mengisi ulang data demo. Baca pesan di atas.
    goto end
)
"%PHP_EXE%" "%~dp0artisan" universities:sync-master --only-schools >nul 2>&1
rem Daftar sekolah nasional ikut terhapus: hapus penanda agar penjadwal segera mengambilnya lagi
del /f /q "%~dp0storage\app\private\sync\institutions-external-last.txt" >nul 2>&1
echo.
echo [OK] Data demo PostgreSQL kembali ke awal. Jalankan 1_MULAI_DEMO.bat lagi.
echo      Daftar sekolah nasional diambil ulang otomatis oleh penjadwal saat demo berjalan.

:end
echo.
pause
endlocal
