@echo off
setlocal EnableExtensions
title SIP-MAGANG - Izin Windows Firewall
cd /d "%~dp0"

rem Mengizinkan perangkat lain di WiFi membuka server demo (php.exe).
rem HARUS dijalankan sebagai Administrator: klik kanan berkas ini > Run as administrator.

net session >nul 2>&1
if errorlevel 1 (
    echo [GAGAL] Berkas ini harus dijalankan sebagai Administrator.
    echo         Klik kanan izinkan_firewall.bat ^> Run as administrator.
    echo.
    pause
    exit /b 1
)

set "PHP_EXE="
if exist "%~dp0php\php.exe" set "PHP_EXE=%~dp0php\php.exe"
if defined PHP_EXE goto php_ok
for /f "delims=" %%P in ('where php 2^>nul') do if not defined PHP_EXE set "PHP_EXE=%%P"
if defined PHP_EXE goto php_ok
for /d %%D in ("%LOCALAPPDATA%\Microsoft\WinGet\Packages\PHP.PHP.8*") do if exist "%%D\php.exe" set "PHP_EXE=%%D\php.exe"
if defined PHP_EXE goto php_ok
if exist "C:\xampp\php\php.exe" set "PHP_EXE=C:\xampp\php\php.exe"
if defined PHP_EXE goto php_ok
echo [GAGAL] PHP tidak ditemukan.
pause
exit /b 1

:php_ok
echo PHP: %PHP_EXE%
echo.
echo Yang akan dilakukan:
echo   1. Menghapus aturan firewall "Block" untuk php.exe di atas (bila ada)
echo   2. Menambah aturan "SIP-MAGANG Demo (PHP)" yang mengizinkan koneksi masuk TCP ke php.exe
echo.
choice /c YT /m "Lanjutkan"
if errorlevel 2 exit /b 0

set "SIPMAGANG_PHP=%PHP_EXE%"
powershell -NoProfile -ExecutionPolicy Bypass -Command "$p = $env:SIPMAGANG_PHP; Get-NetFirewallApplicationFilter -Program $p -ErrorAction SilentlyContinue | Get-NetFirewallRule | Where-Object { $_.Direction -eq 'Inbound' -and $_.Action -eq 'Block' } | Remove-NetFirewallRule; Get-NetFirewallRule -DisplayName 'SIP-MAGANG Demo (PHP)' -ErrorAction SilentlyContinue | Remove-NetFirewallRule; New-NetFirewallRule -DisplayName 'SIP-MAGANG Demo (PHP)' -Direction Inbound -Action Allow -Protocol TCP -Program $p -Profile Any | Out-Null; 'Selesai. Aturan firewall untuk php.exe sudah diizinkan.'"
echo.
echo Untuk mencabut izin setelah demo:
echo   netsh advfirewall firewall delete rule name="SIP-MAGANG Demo (PHP)"
echo.
pause
endlocal
