@echo off
setlocal EnableExtensions
title SIP-MAGANG - Mulai Demo
cd /d "%~dp0"
set "APP_DIR=%~dp0"
if "%APP_DIR:~-1%"=="\" set "APP_DIR=%APP_DIR:~0,-1%"

echo ===============================================================
echo    SIP-MAGANG - MULAI DEMO (cukup klik berkas ini)
echo ===============================================================
echo  Semua persiapan dikerjakan otomatis. Bila muncul jendela
echo  "Izinkan aplikasi ini membuat perubahan?", klik YES / YA.
echo.

rem ---------------------------------------------------------------
rem  Demo sudah berjalan? Jangan sampai klik kedua mematikan demo.
rem ---------------------------------------------------------------
powershell -NoProfile -ExecutionPolicy Bypass -Command "if (@(Get-CimInstance Win32_Process -Filter \"Name = 'php.exe'\" | Where-Object { $_.CommandLine -and $_.CommandLine.Contains('sip_magang_demo_server') -and $_.CommandLine.Contains($env:APP_DIR) }).Count -gt 0) { exit 1 } else { exit 0 }"
if errorlevel 1 goto already_running

rem ---------------------------------------------------------------
echo [1/5] Memeriksa lokasi folder...
rem ---------------------------------------------------------------
echo %APP_DIR%| findstr /i /c:"\Temp1_" /c:".zip\" >nul
if not errorlevel 1 goto inside_zip
echo %APP_DIR%| findstr /i /c:"\OneDrive" >nul
if not errorlevel 1 echo  [PERHATIAN] Folder berada di OneDrive. Sebaiknya pindahkan ke C:\SIP-MAGANG agar tidak tersinkron saat demo.
echo        OK: %APP_DIR%

rem ---------------------------------------------------------------
echo [2/5] Membuka blokir berkas hasil unduhan...
rem ---------------------------------------------------------------
rem Zip dari internet/flashdisk ditandai Windows ("Mark of the Web"); tanda itu dapat memicu peringatan
rem SmartScreen atau memblokir DLL PHP. Hanya berkas program yang dibuka blokirnya, tanpa hak Administrator.
powershell -NoProfile -ExecutionPolicy Bypass -Command "Get-ChildItem -LiteralPath $env:APP_DIR -File -Include *.bat,*.ps1,*.exe,*.dll -Recurse -ErrorAction SilentlyContinue | Where-Object { $_.FullName -notlike '*\vendor\*' -and $_.FullName -notlike '*\storage\*' } | Unblock-File -ErrorAction SilentlyContinue"
echo        OK

rem ---------------------------------------------------------------
echo [3/5] Memeriksa PHP...
rem ---------------------------------------------------------------
call "%~dp0scripts\find_php.bat"
if not defined PHP_EXE goto no_php
"%PHP_EXE%" -v >nul 2>&1
if errorlevel 1 goto php_broken
echo        OK

rem ---------------------------------------------------------------
echo [4/5] Memeriksa izin Windows Firewall (agar HP peserta bisa masuk)...
rem ---------------------------------------------------------------
set "SIPMAGANG_PHP=%PHP_EXE%"
call :firewall_ok
if not errorlevel 1 (
    echo        OK: sudah diizinkan.
    goto firewall_done
)
echo        Belum diizinkan. Jendela izin Windows akan muncul: klik YES / YA.
powershell -NoProfile -ExecutionPolicy Bypass -Command "try { Start-Process -FilePath ($env:APP_DIR + '\izinkan_firewall.bat') -ArgumentList '/auto' -Verb RunAs -Wait; exit 0 } catch { exit 1 }"
call :firewall_ok
if not errorlevel 1 (
    echo        OK: izin firewall berhasil dibuat.
    goto firewall_done
)
echo.
echo  [PERHATIAN] Izin firewall BELUM dibuat (jendela izin ditolak atau akun ini bukan Administrator).
echo              Demo tetap dijalankan, tetapi HP/laptop lain mungkin TIDAK bisa membuka alamatnya.
echo              Bila nanti muncul jendela "Windows Security Alert", centang Private DAN Public lalu
echo              klik Allow access; atau minta Administrator menjalankan izinkan_firewall.bat.
echo.
:firewall_done

rem ---------------------------------------------------------------
echo [5/5] Menjaga komputer tetap menyala selama demo...
rem ---------------------------------------------------------------
powershell -NoProfile -Command "$b = Get-CimInstance Win32_Battery -ErrorAction SilentlyContinue | Select-Object -First 1; if ($b -and $b.BatteryStatus -eq 1) { exit 1 } else { exit 0 }"
if errorlevel 1 echo  [PERHATIAN] Laptop sedang memakai BATERAI. Colokkan charger agar tidak mati/melambat saat demo.
start "SIP-MAGANG Jaga Agar Tidak Sleep" /min powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0scripts\keep_awake.ps1" -AppDir "%APP_DIR%"
echo        OK: sleep dan layar mati dicegah selama demo berjalan.
echo.
echo  Persiapan selesai. Menyalakan server demo (sekitar 1 menit)...
echo.

endlocal & call "%~dp0start_demo.bat"
exit /b

rem ===============================================================
:already_running
echo  Demo SUDAH berjalan di komputer ini.
echo    [B] Buka lagi halaman alamat ^& QR (demo tetap berjalan)
echo    [U] Ulang dari awal (semua peserta harus memuat ulang halaman)
choice /c BU /m " Pilih"
if errorlevel 2 goto restart
if exist "%~dp0ALAMAT_DEMO.html" start "" "%~dp0ALAMAT_DEMO.html"
exit /b 0

:restart
powershell -NoProfile -ExecutionPolicy Bypass -Command "Get-CimInstance Win32_Process -Filter \"Name = 'php.exe'\" | Where-Object { $_.CommandLine -and $_.CommandLine.Contains($env:APP_DIR) } | ForEach-Object { Stop-Process -Id $_.ProcessId -Force -ErrorAction SilentlyContinue }"
timeout /t 2 /nobreak >nul
endlocal & call "%~dp0start_demo.bat"
exit /b

:inside_zip
echo.
echo  [GAGAL] Berkas ini dibuka langsung dari dalam zip.
echo          Tutup jendela ini, klik kanan berkas zip, pilih "Extract All" ke C:\
echo          lalu buka folder C:\SIP-MAGANG dan klik 1_MULAI_DEMO.bat lagi.
goto stop

:no_php
echo  [GAGAL] PHP tidak ditemukan. Pastikan folder php\ dari zip ikut terekstrak utuh.
goto stop

:php_broken
echo  [GAGAL] PHP tidak bisa dijalankan di komputer ini.
echo          Pasang Microsoft Visual C++ 2015-2022 x64 dari Microsoft:
echo          https://aka.ms/vs/17/release/vc_redist.x64.exe  lalu klik berkas ini lagi.
goto stop

:stop
echo.
pause
exit /b 1

rem --- errorlevel 0 bila aturan "SIP-MAGANG Demo (PHP)" (semua jenis jaringan) ada dan tidak ada aturan Block.
rem     Izin otomatis dari jendela Windows Security Alert sering hanya untuk jaringan Public, jadi tidak dihitung.
:firewall_ok
powershell -NoProfile -ExecutionPolicy Bypass -Command "$r = @(Get-NetFirewallApplicationFilter -Program $env:SIPMAGANG_PHP -ErrorAction SilentlyContinue | Get-NetFirewallRule -ErrorAction SilentlyContinue | Where-Object { $_.Enabled -eq 'True' -and $_.Direction -eq 'Inbound' }); $ours = @($r | Where-Object { $_.DisplayName -eq 'SIP-MAGANG Demo (PHP)' -and $_.Action -eq 'Allow' -and [int]$_.Profile -in 0,7 }).Count; if (@($r | Where-Object { $_.Action -eq 'Block' }).Count -eq 0 -and $ours -gt 0) { exit 0 } else { exit 1 }"
exit /b %errorlevel%
