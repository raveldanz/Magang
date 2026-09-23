@echo off
title Magang Dev Server
echo ====================================================
echo        SISTEM INFORMASI MANAJEMEN MAGANG
echo ====================================================

set "PATH=C:\Program Files\nodejs;C:\Users\TK ABA SBY 69 (3)\AppData\Local\Microsoft\WinGet\Packages\PHP.PHP.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe;C:\pgsql\bin;%PATH%"

echo [1/3] Memastikan PostgreSQL aktif...
"C:\pgsql\bin\pg_ctl.exe" -D "C:\pgsql\data" -l "C:\pgsql\logfile.log" start >nul 2>&1

echo [2/3] Membuka Vite dev bundler di window baru...
start "Vite Dev" cmd /k "npm run dev"

echo [3/3] Menjalankan server Laravel di http://127.0.0.1:8000 ...
"C:\Users\TK ABA SBY 69 (3)\AppData\Local\Microsoft\WinGet\Packages\PHP.PHP.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe" artisan serve --port=8000
