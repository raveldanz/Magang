@echo off
setlocal EnableExtensions
title SIP-MAGANG - Impor data PostgreSQL
cd /d "%~dp0"
echo ===============================================================================
echo   IMPOR DATA POSTGRESQL  (mengganti isi database di laptop ini)
echo ===============================================================================
call "%~dp0scripts\pgsql_common.bat"
if errorlevel 1 goto fail
set "IN=%~dp0database\pgsql\magang.dump"
if not "%~1"=="" set "PGDATABASE=%~1"
rem Tanpa blok ( ): jalur folder bisa memuat tanda kurung, yang memutus blok if.
if exist "%IN%" goto found
echo [GAGAL] Berkas ekspor tidak ada: "%IN%"
echo         Jalankan pgsql_ekspor.bat di komputer asal lalu salin berkasnya ke sini.
goto fail
:found
echo Database tujuan : %PGDATABASE%  di %PGHOST%:%PGPORT%
echo Isi database itu akan DIGANTI dengan isi berkas ekspor.
if /i "%~2"=="/y" goto go
set /p "JAWAB=Lanjutkan? (Y/T): "
if /i not "%JAWAB%"=="Y" goto fail
:go
"%PG_BIN%\psql.exe" -d postgres -q -c "SELECT pg_terminate_backend(pid) FROM pg_stat_activity WHERE datname='%PGDATABASE%' AND pid <> pg_backend_pid()" >nul 2>&1
"%PG_BIN%\psql.exe" -d postgres -q -c "DROP DATABASE IF EXISTS \"%PGDATABASE%\"" -c "CREATE DATABASE \"%PGDATABASE%\""
if errorlevel 1 goto fail
"%PG_BIN%\pg_restore.exe" --no-owner --no-privileges -d "%PGDATABASE%" "%IN%"
if errorlevel 1 goto fail
echo.
echo SELESAI: data sudah masuk ke database "%PGDATABASE%".
echo Jalankan start_demo_kantor.bat untuk memulai server.
echo.
if /i not "%~2"=="/y" pause
exit /b 0
:fail
echo.
echo Impor TIDAK berhasil. Baca pesan di atas.
if /i not "%~2"=="/y" pause
exit /b 1
