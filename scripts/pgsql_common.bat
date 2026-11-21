@echo off
rem Dipanggil oleh pgsql_ekspor.bat / pgsql_impor.bat: mencari alat PostgreSQL dan membaca DB_* dari .env.
set "PG_BIN="
if exist "C:\pgsql\bin\pg_dump.exe" set "PG_BIN=C:\pgsql\bin"
if not defined PG_BIN for /d %%D in ("%ProgramFiles%\PostgreSQL\*") do if exist "%%D\bin\pg_dump.exe" set "PG_BIN=%%D\bin"
if not defined PG_BIN for /f "delims=" %%P in ('where pg_dump 2^>nul') do if not defined PG_BIN set "PG_BIN=%%~dpP"
if not defined PG_BIN (
    echo [GAGAL] PostgreSQL tidak ditemukan. Pasang PostgreSQL 16 lebih dulu ^(atau salin folder C:\pgsql^).
    exit /b 1
)
if not exist "%~dp0..\.env" (
    echo [GAGAL] Berkas .env tidak ada di folder proyek.
    exit /b 1
)
set "PGHOST=127.0.0.1"
set "PGPORT=5432"
set "PGUSER=postgres"
set "PGDATABASE=magang"
set "PGPASSWORD="
for /f "usebackq tokens=1,* delims==" %%A in ("%~dp0..\.env") do (
    if /i "%%A"=="DB_HOST" set "PGHOST=%%B"
    if /i "%%A"=="DB_PORT" set "PGPORT=%%B"
    if /i "%%A"=="DB_USERNAME" set "PGUSER=%%B"
    if /i "%%A"=="DB_DATABASE" set "PGDATABASE=%%B"
    if /i "%%A"=="DB_PASSWORD" set "PGPASSWORD=%%B"
)
rem Kata sandi di .env bisa diapit tanda petik tunggal (ditulis SAMBUNGKAN_POSTGRESQL.bat)
if defined PGPASSWORD set "PGPASSWORD=%PGPASSWORD:'=%"
rem Nyalakan PostgreSQL portabel bila ada dan belum hidup
if exist "C:\pgsql\bin\pg_ctl.exe" (
    "C:\pgsql\bin\pg_isready.exe" -h %PGHOST% -p %PGPORT% >nul 2>&1
    if errorlevel 1 "C:\pgsql\bin\pg_ctl.exe" -D "C:\pgsql\data" -l "C:\pgsql\logfile.log" -w -t 30 start >nul 2>&1
)
"%PG_BIN%\pg_isready.exe" -h %PGHOST% -p %PGPORT% >nul 2>&1
if errorlevel 1 (
    echo [GAGAL] PostgreSQL tidak menerima koneksi di %PGHOST%:%PGPORT%. Nyalakan layanannya lalu ulangi.
    exit /b 1
)
exit /b 0
