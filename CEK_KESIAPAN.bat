@echo off
setlocal EnableExtensions
title SIP-MAGANG - Cek Kesiapan Demo
cd /d "%~dp0"
echo ====================================================
echo    SIP-MAGANG - CEK KESIAPAN (tidak mengubah apa pun)
echo ====================================================
echo.

call "%~dp0scripts\find_php.bat"
if not defined PHP_EXE goto no_php
"%PHP_EXE%" -v >nul 2>&1
if errorlevel 1 goto php_broken

"%PHP_EXE%" "%~dp0scripts\demo_preflight.php" --check
set "RC=%ERRORLEVEL%"
echo.
if "%RC%"=="0" goto ready
echo HASIL: ada [GAGAL] di atas yang harus diperbaiki dulu.
goto end

:ready
echo HASIL: siap. Baca [PERHATIAN] bila ada, lalu jalankan 1_MULAI_DEMO.bat.
goto end

:no_php
echo [GAGAL] PHP tidak ditemukan. Pastikan folder php\ dari zip ikut terekstrak.
goto end

:php_broken
echo [GAGAL] PHP ditemukan tetapi tidak bisa dijalankan.
echo         Pasang Microsoft Visual C++ 2015-2022 x64 dari Microsoft:
echo         https://aka.ms/vs/17/release/vc_redist.x64.exe
goto end

:end
echo.
pause
endlocal
