<#
  Membuat zip demo SIP-MAGANG yang siap dijalankan di laptop Windows lain
  (cukup ekstrak lalu klik dua kali start_demo.bat; tidak perlu composer/npm/PostgreSQL).

  Pakai (dari folder proyek):
      powershell -ExecutionPolicy Bypass -File scripts\make_demo_zip.ps1
      powershell -ExecutionPolicy Bypass -File scripts\make_demo_zip.ps1 -NoPhp     # tanpa PHP portabel (zip lebih kecil)
      powershell -ExecutionPolicy Bypass -File scripts\make_demo_zip.ps1 -SkipBuild # jangan jalankan npm run build

  Isi zip: kode, vendor/, public/build, .env (APP_DEBUG=false), database SQLite beserta data demo,
  berkas unggahan (storage/app), dan folder php/ (PHP portabel dari komputer ini).
  TIDAK disertakan: .git, node_modules, log, sesi, cache, berkas token/agen, tangkapan layar uji.
#>
param(
    [switch]$NoPhp,
    [switch]$SkipBuild,
    [string]$OutDir
)

$ErrorActionPreference = 'Stop'
$root = Split-Path -Parent $PSScriptRoot
Set-Location $root
if (-not $OutDir) { $OutDir = Split-Path -Parent $root }

function Fail($msg) { Write-Host "[GAGAL] $msg" -ForegroundColor Red; exit 1 }
function Info($msg) { Write-Host "[INFO]  $msg" }

# --- PHP ------------------------------------------------------------------------------------
$phpCmd = Get-Command php -ErrorAction SilentlyContinue
$phpExe = $null
if ($phpCmd) { $phpExe = $phpCmd.Source }
if (-not $phpExe) {
    $found = Get-ChildItem "$env:LOCALAPPDATA\Microsoft\WinGet\Packages\PHP.PHP.8*\php.exe" -ErrorAction SilentlyContinue | Select-Object -First 1
    if ($found) { $phpExe = $found.FullName }
}
if (-not $phpExe) { Fail 'PHP tidak ditemukan di komputer ini.' }
Info "PHP: $phpExe"

# --- Prasyarat --------------------------------------------------------------------------------
if (-not (Test-Path 'vendor\autoload.php')) { Fail 'vendor/ tidak ada. Jalankan composer install dulu.' }
if (-not (Test-Path '.env')) { Fail '.env tidak ada.' }
if (Test-Path 'public\hot') {
    Fail 'public\hot ada: Vite dev server (npm run dev) masih berjalan. Tutup dulu jendela Vite, lalu ulangi.'
}

if (-not $SkipBuild) {
    $npm = Get-Command npm -ErrorAction SilentlyContinue
    if ($npm -and (Test-Path 'node_modules')) {
        Info 'Membangun aset (npm run build)...'
        & cmd /c "npm run build" | Out-Null
        if ($LASTEXITCODE -ne 0) { Fail 'npm run build gagal. Jalankan manual untuk melihat pesan errornya.' }
    } else {
        Info 'npm/node_modules tidak tersedia: memakai public/build yang sudah ada.'
    }
}
if (-not (Test-Path 'public\build\manifest.json')) { Fail 'public/build/manifest.json tidak ada. Jalankan npm run build.' }
$manifestTime = (Get-Item 'public\build\manifest.json').LastWriteTime
$newer = Get-ChildItem resources -Recurse -File | Where-Object { $_.LastWriteTime -gt $manifestTime } | Select-Object -First 1
if ($newer) { Write-Host "[PERHATIAN] Ada berkas di resources/ yang lebih baru dari hasil build ($($newer.Name)). Jalankan npm run build agar tampilan tidak basi." -ForegroundColor Yellow }

Info 'Menjalankan pengujian otomatis (php artisan test)...'
& $phpExe artisan test --compact 2>&1 | Select-Object -Last 3 | ForEach-Object { Write-Host "        $_" }
if ($LASTEXITCODE -ne 0) { Fail 'Ada pengujian yang gagal. Perbaiki dulu sebelum membuat zip demo.' }

# --- Susun folder sementara -------------------------------------------------------------------
$stamp = Get-Date -Format 'yyyyMMdd-HHmm'
$name = "SIP-MAGANG-demo-$stamp"
$stage = Join-Path $env:TEMP "$name"
$dest = Join-Path $stage 'SIP-MAGANG'
if (Test-Path $stage) { Remove-Item $stage -Recurse -Force }
New-Item -ItemType Directory -Path $dest -Force | Out-Null

Info 'Menyalin berkas proyek...'
$excludeDirs = @(
    '.git', 'node_modules', '.aider.tags.cache.v4', '.claude', '.agents', '.idea', '.vscode',
    (Join-Path $root 'php'),
    (Join-Path $root 'public\test-artifacts'), (Join-Path $root 'public\storage'), (Join-Path $root 'public\presentation'),
    (Join-Path $root 'storage\logs'), (Join-Path $root 'storage\app\backups'),
    (Join-Path $root 'storage\framework\sessions'), (Join-Path $root 'storage\framework\views'),
    (Join-Path $root 'storage\framework\cache'), (Join-Path $root 'storage\framework\testing'),
    (Join-Path $root 'bootstrap\cache'), (Join-Path $root 'scripts\archive')
)
$excludeFiles = @(
    '.env.agents', '.env.backup', '.env.production', '.aider.chat.history.md', '.aider.input.history',
    '.phpunit.result.cache', '*.pptx', '*.docx', '*.log', '*.tmp', 'hot', 'auth.json', 'demo_server.txt',
    'db_magang', '.gh-token.txt', '*.bundle', '*.tgz', '*.dump'
)
& robocopy $root $dest /E /XJ /R:1 /W:1 /NFL /NDL /NJH /NJS /NP /XD @excludeDirs /XF @excludeFiles | Out-Null
if ($LASTEXITCODE -ge 8) { Fail "robocopy gagal (kode $LASTEXITCODE)." }

foreach ($d in 'storage\logs', 'storage\framework\sessions', 'storage\framework\views', 'storage\framework\cache\data', 'bootstrap\cache', 'storage\app\public', 'storage\app\private') {
    New-Item -ItemType Directory -Path (Join-Path $dest $d) -Force | Out-Null
}
Set-Content -Path (Join-Path $dest 'bootstrap\cache\.gitignore') -Value "*`n!.gitignore" -Encoding ascii

# .env untuk demo: halaman error tidak menampilkan detail teknis
$envPath = Join-Path $dest '.env'
$envText = [System.IO.File]::ReadAllText($envPath)
$envText = [regex]::Replace($envText, '(?m)^APP_DEBUG=.*$', 'APP_DEBUG=false')
$envText = [regex]::Replace($envText, '(?m)^LOG_LEVEL=.*$', 'LOG_LEVEL=warning')
# Zip demo selalu SQLite: .env kerja bisa saja sedang menunjuk PostgreSQL (start_demo_kantor.bat --pgsql),
# padahal laptop tujuan tidak punya server database. .env di folder kerja tidak diubah.
if ($envText -notmatch '(?m)^DB_CONNECTION=sqlite') {
    Write-Host '[PERHATIAN] .env kerja tidak memakai SQLite. Zip disetel ke SQLite (database\database.sqlite); data yang hanya ada di database lain TIDAK ikut.' -ForegroundColor Yellow
    if ($envText -match '(?m)^DB_CONNECTION=') {
        $envText = [regex]::Replace($envText, '(?m)^DB_CONNECTION=.*$', 'DB_CONNECTION=sqlite')
    } else {
        $envText += "`nDB_CONNECTION=sqlite`n"
    }
    $envText = [regex]::Replace($envText, '(?m)^(DB_(?:DATABASE|HOST|PORT|USERNAME|PASSWORD)=.*)$', '# $1')
}
# Kata sandi server database laptop ini tidak boleh ikut ke zip, termasuk dalam baris yang dikomentari.
if ($envText -match '(?m)^DB_CONNECTION=sqlite') {
    $envText = [regex]::Replace($envText, '(?m)^#?\s*DB_PASSWORD=.*$', '# DB_PASSWORD=')
}
[System.IO.File]::WriteAllText($envPath, $envText, (New-Object System.Text.UTF8Encoding($false)))
if ($envText -notmatch '(?m)^DB_CONNECTION=sqlite') {
    Write-Host '[PERHATIAN] .env tidak memakai SQLite: laptop tujuan harus punya server database yang sama.' -ForegroundColor Yellow
} elseif (-not (Test-Path (Join-Path $dest 'database\database.sqlite'))) {
    Write-Host '[PERHATIAN] database\database.sqlite tidak ikut: start_demo.bat akan membuat database baru berisi data demo seeder.' -ForegroundColor Yellow
}

if (-not $NoPhp) {
    Info 'Menyertakan PHP portabel...'
    & robocopy (Split-Path -Parent $phpExe) (Join-Path $dest 'php') /E /R:1 /W:1 /NFL /NDL /NJH /NJS /NP /XD dev extras /XF *.pdb php8phpdbg.dll phpdbg.exe php-cgi.exe php-win.exe | Out-Null
    if ($LASTEXITCODE -ge 8) { Fail 'Gagal menyalin PHP.' }
    if (-not (Test-Path (Join-Path $dest 'php\php.ini'))) { Fail 'php.ini tidak ditemukan di folder PHP: ekstensi tidak akan aktif di laptop tujuan.' }

    # PHP untuk Windows butuh Microsoft Visual C++ Runtime. Laptop baru/kantor sering belum punya,
    # sehingga php.exe gagal dibuka. Microsoft mengizinkan DLL runtime ini disertakan di samping aplikasi
    # (app-local), dan Windows mencarinya di folder php.exe lebih dulu.
    $vcDlls = 'vcruntime140.dll', 'vcruntime140_1.dll', 'msvcp140.dll'
    $vcCopied = 0
    foreach ($dll in $vcDlls) {
        $src = Join-Path $env:SystemRoot "System32\$dll"
        if (Test-Path $src) {
            Copy-Item $src (Join-Path $dest "php\$dll") -Force
            $vcCopied++
        }
    }
    if ($vcCopied -lt 2) {
        Write-Host '[PERHATIAN] Visual C++ Runtime tidak ditemukan di laptop ini, jadi tidak ikut ke zip. Bawa VC_redist.x64.exe untuk laptop tujuan.' -ForegroundColor Yellow
    } else {
        Info "Visual C++ Runtime disertakan ($vcCopied berkas) agar PHP jalan tanpa instalasi tambahan."
    }
}

# --- Uji folder hasil salinan (bukan folder kerja) ---------------------------------------------
Info 'Memeriksa hasil salinan...'
$testPhp = $phpExe
if (-not $NoPhp) { $testPhp = Join-Path $dest 'php\php.exe' }
& $testPhp (Join-Path $dest 'scripts\demo_preflight.php') --check
if ($LASTEXITCODE -ne 0) { Fail 'Hasil salinan tidak lolos pemeriksaan.' }

# --- Zip ---------------------------------------------------------------------------------------
$zip = Join-Path $OutDir "$name.zip"
if (Test-Path $zip) { Remove-Item $zip -Force }
Info 'Memampatkan (bisa 1-2 menit)...'
Push-Location $stage
# Jalur lengkap: bila skrip dipanggil dari Git Bash, "tar.exe" jatuh ke GNU tar yang tidak bisa membuat zip
# dan menganggap "C:\..." sebagai alamat host.
$tarExe = Join-Path $env:SystemRoot 'System32\tar.exe'
if (-not (Test-Path $tarExe)) { $tarExe = 'tar.exe' }
& $tarExe -a -c -f $zip 'SIP-MAGANG'
$tarCode = $LASTEXITCODE
Pop-Location
if ($tarCode -ne 0 -or -not (Test-Path $zip)) { Fail 'Gagal membuat zip.' }
Remove-Item $stage -Recurse -Force

$sizeMb = [math]::Round((Get-Item $zip).Length / 1MB, 1)
Write-Host ''
Write-Host "SELESAI: $zip ($sizeMb MB)" -ForegroundColor Green
Write-Host 'Di laptop tujuan: ekstrak zip, buka folder SIP-MAGANG, klik dua kali start_demo.bat.'
Write-Host 'Baca PANDUAN_DEMO.md untuk langkah firewall dan daftar akun.'
