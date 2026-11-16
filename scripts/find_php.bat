@echo off
rem Mengisi variabel PHP_EXE milik pemanggil (tanpa setlocal). Urutan pencarian:
rem 1) folder php\ bawaan zip  2) PATH  3) instalasi winget  4) XAMPP
rem Pemakaian:  call "%~dp0scripts\find_php.bat"  lalu cek  if not defined PHP_EXE ...
set "PHP_EXE="
rem Path dinormalkan (tanpa "scripts\..") agar sama persis dengan aturan firewall buatan izinkan_firewall.bat
if exist "%~dp0..\php\php.exe" for %%I in ("%~dp0..\php\php.exe") do set "PHP_EXE=%%~fI"
if defined PHP_EXE goto :eof
for /f "delims=" %%P in ('where php 2^>nul') do if not defined PHP_EXE set "PHP_EXE=%%P"
if defined PHP_EXE goto :eof
for /d %%D in ("%LOCALAPPDATA%\Microsoft\WinGet\Packages\PHP.PHP.8*") do if exist "%%D\php.exe" set "PHP_EXE=%%D\php.exe"
if defined PHP_EXE goto :eof
if exist "C:\xampp\php\php.exe" set "PHP_EXE=C:\xampp\php\php.exe"
goto :eof
