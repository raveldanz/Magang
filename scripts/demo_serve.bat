@echo off
rem ============================================================================================
rem  Menjalankan server demo SIP-MAGANG untuk banyak peserta sekaligus.
rem  Dipanggil oleh start_demo.bat dan start_demo_kantor.bat SETELAH scripts\demo_preflight.php:
rem      call "%~dp0scripts\demo_serve.bat" "C:\path\php.exe"
rem
rem  Server bawaan PHP hanya melayani satu permintaan pada satu waktu. Skrip ini menyalakan
rem  beberapa server aplikasi (jendela diperkecil "SIP-MAGANG Server n", port utama+1 ... +N)
rem  dan gerbang pembagi beban di port utama (scripts\demo_gateway.php) di jendela ini.
rem  Saat jendela ini dihentikan (Ctrl+C), semua server aplikasi ikut dimatikan.
rem ============================================================================================
setlocal EnableExtensions EnableDelayedExpansion
set "PHP_EXE=%~1"
set "APP_DIR=%~dp0.."
for %%I in ("%APP_DIR%") do set "APP_DIR=%%~fI"

set "DEMO_PORT=8000"
set "DEMO_WORKERS=4"
set "DEMO_OPCACHE=0"
for /f "usebackq tokens=2,3,4" %%A in ("%APP_DIR%\storage\framework\demo_server.txt") do (
    set "DEMO_PORT=%%A"
    if not "%%B"=="" set "DEMO_WORKERS=%%B"
    if not "%%C"=="" set "DEMO_OPCACHE=%%C"
)

rem Penanda sip_magang_demo_server dipakai untuk mematikan server ini saja, bukan server PHP lain.
set "PHP_FLAGS=-d sip_magang_demo_server=1 -d upload_max_filesize=64M -d post_max_size=80M -d memory_limit=512M -d max_execution_time=120 -d max_file_uploads=30"
if "%DEMO_OPCACHE%"=="1" set "PHP_FLAGS=%PHP_FLAGS% -d zend_extension=opcache"
if not "%DEMO_OPCACHE%"=="0" set "PHP_FLAGS=%PHP_FLAGS% -d opcache.enable_cli=1 -d opcache.memory_consumption=192 -d opcache.max_accelerated_files=20000 -d opcache.revalidate_freq=2"

call :stop_servers

rem Setiap server memakai folder hasil kompilasi Blade sendiri (disalin dari hasil view:cache di
rem preflight). Windows menolak menimpa berkas yang sedang dibuka proses lain, sehingga dua server
rem yang mengompilasi tampilan yang sama bersamaan akan memunculkan galat 500 bagi peserta.
set "VIEWS_DIR=%APP_DIR%\storage\framework\views"
set "DEMO_WORKER_PORTS="
for /L %%N in (1,1,%DEMO_WORKERS%) do (
    set /a "WORKER_PORT=DEMO_PORT+%%N"
    if defined DEMO_WORKER_PORTS (set "DEMO_WORKER_PORTS=!DEMO_WORKER_PORTS!,!WORKER_PORT!") else (set "DEMO_WORKER_PORTS=!WORKER_PORT!")
    robocopy "!VIEWS_DIR!" "!VIEWS_DIR!\demo-server-%%N" *.php /PURGE /R:1 /W:1 /NFL /NDL /NJH /NJS /NP >nul
    set "VIEW_COMPILED_PATH=!VIEWS_DIR!\demo-server-%%N"
    start "SIP-MAGANG Server %%N" /min /D "%APP_DIR%\public" "%PHP_EXE%" %PHP_FLAGS% -S 0.0.0.0:!WORKER_PORT! "%APP_DIR%\vendor\laravel\framework\src\Illuminate\Foundation\resources\server.php"
)
set "VIEW_COMPILED_PATH="

echo  %DEMO_WORKERS% server aplikasi berjalan di port %DEMO_WORKER_PORTS% (jendela diperkecil, JANGAN ditutup).
echo  Peserta cukup membuka alamat utama (port %DEMO_PORT%); gerbang membagi mereka bergiliran ke server yang hidup.
echo  Cek kesehatan server: http://localhost:%DEMO_PORT%/__demo-status
echo.

cd /d "%APP_DIR%\public"
"%PHP_EXE%" -d sip_magang_demo_server=1 -S 0.0.0.0:%DEMO_PORT% "%APP_DIR%\scripts\demo_gateway.php"

echo.
echo  Gerbang berhenti. Mematikan server aplikasi...
call :stop_servers
endlocal
exit /b 0

rem --- Mematikan php.exe server demo dari folder ini (ditandai sip_magang_demo_server) ----------
:stop_servers
set "SIPMAGANG_APP=%APP_DIR%"
powershell -NoProfile -ExecutionPolicy Bypass -Command "Get-CimInstance Win32_Process | Where-Object { $_.Name -eq 'php.exe' -and $_.CommandLine -and $_.CommandLine.Contains('sip_magang_demo_server') -and $_.CommandLine.Contains($env:SIPMAGANG_APP) } | ForEach-Object { Stop-Process -Id $_.ProcessId -Force -ErrorAction SilentlyContinue }" >nul 2>&1
exit /b 0
