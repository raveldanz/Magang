@echo off
setlocal EnableExtensions
title SIP-MAGANG - Ekspor data PostgreSQL
cd /d "%~dp0"
echo ===============================================================================
echo   EKSPOR DATA POSTGRESQL  (untuk dipindah ke laptop lain)
echo ===============================================================================
call "%~dp0scripts\pgsql_common.bat"
if errorlevel 1 goto fail
if not exist "%~dp0database\pgsql" mkdir "%~dp0database\pgsql"
set "OUT=%~dp0database\pgsql\magang.dump"
echo Mengekspor database "%PGDATABASE%" ...
"%PG_BIN%\pg_dump.exe" -Fc --no-owner --no-privileges -f "%OUT%" "%PGDATABASE%"
if errorlevel 1 goto fail
echo.
echo SELESAI: %OUT%
echo Salin berkas itu ke folder yang sama di laptop tujuan, lalu jalankan pgsql_impor.bat di sana.
echo Berkas ini memuat data pengguna: jangan dibagikan ke orang lain.
echo.
pause
exit /b 0
:fail
echo.
echo Ekspor TIDAK berhasil. Baca pesan [GAGAL] di atas.
pause
exit /b 1
