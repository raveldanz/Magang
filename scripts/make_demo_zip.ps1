<#
  Membuat zip demo SIP-MAGANG yang siap dijalankan di laptop Windows lain
  (cukup ekstrak lalu klik dua kali start_demo.bat; tidak perlu composer/npm/PostgreSQL).

  Pakai (dari folder proyek):
      powershell -ExecutionPolicy Bypass -File scripts\make_demo_zip.ps1
      powershell -ExecutionPolicy Bypass -File scripts\make_demo_zip.ps1 -NoPhp     # tanpa PHP portabel (zip lebih kecil)
      powershell -ExecutionPolicy Bypass -File scripts\make_demo_zip.ps1 -SkipBuild # jangan jalankan npm run build
      powershell -ExecutionPolicy Bypass -File scripts\make_demo_zip.ps1 -KeepDatabase # pakai database.sqlite folder kerja apa adanya

  Isi zip: kode, vendor/, public/build, .env (APP_DEBUG=false), database SQLite berisi data demo yang
  BARU dibuat (migrate:fresh --seed) + salinan awalnya (database.awal.sqlite untuk RESET_DATA_DEMO.bat),
  berkas contoh yang dirujuk data demo, folder php/ (PHP portabel + Visual C++ Runtime), dan
  PANDUAN_IT_SIP-MAGANG.pdf di folder teratas.
  TIDAK disertakan: .git, node_modules, log, sesi, cache, berkas token/agen, tangkapan layar uji,
  cadangan database, unggahan & lampiran chat hasil uji coba lokal.
#>
param(
    [switch]$NoPhp,
    [switch]$SkipBuild,
    [switch]$KeepDatabase,
    [switch]$SkipTests,
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

if ($SkipTests) {
    Write-Host '[PERHATIAN] -SkipTests: pengujian otomatis dilewati (hanya untuk menguji skrip ini, jangan untuk zip yang diserahkan).' -ForegroundColor Yellow
} else {
    Info 'Menjalankan pengujian otomatis (php artisan test)...'
    & $phpExe artisan test --compact 2>&1 | Select-Object -Last 3 | ForEach-Object { Write-Host "        $_" }
    if ($LASTEXITCODE -ne 0) { Fail 'Ada pengujian yang gagal. Perbaiki dulu sebelum membuat zip demo.' }
}

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
    (Join-Path $root 'storage\logs'), (Join-Path $root 'storage\app\backups'), (Join-Path $root 'storage\app\private\backups'),
    # Penanda sinkron sekolah milik database laptop ini; database zip baru harus sinkron sendiri (lihat routes/console.php)
    (Join-Path $root 'storage\app\private\sync'),
    (Join-Path $root 'database\cadangan'), (Join-Path $root 'database\pgsql'),
    (Join-Path $root 'storage\framework\sessions'), (Join-Path $root 'storage\framework\views'),
    (Join-Path $root 'storage\framework\cache'), (Join-Path $root 'storage\framework\testing'),
    (Join-Path $root 'bootstrap\cache'), (Join-Path $root 'scripts\archive')
)
$excludeFiles = @(
    '.env.agents', '.env.backup', '.env.production', '.aider.chat.history.md', '.aider.input.history',
    '.phpunit.result.cache', '*.pptx', '*.docx', '*.log', '*.tmp', 'hot', 'auth.json', 'demo_server.txt',
    'db_magang', '.gh-token.txt', '*.bundle', '*.tgz', '*.dump', 'ALAMAT_DEMO.html', '__*_tmp.js',
    'database.sqlite-wal', 'database.sqlite-shm', 'database.awal.sqlite'
)
& robocopy $root $dest /E /XJ /R:1 /W:1 /NFL /NDL /NJH /NJS /NP /XD @excludeDirs /XF @excludeFiles | Out-Null
if ($LASTEXITCODE -ge 8) { Fail "robocopy gagal (kode $LASTEXITCODE)." }

# Folder teratas zip hanya berisi yang dipakai di laptop tujuan, agar petugas IT tidak bingung.
# (Dihapus dari salinan, bukan lewat /XF robocopy, supaya berkas bernama sama di vendor/ tidak ikut hilang.)
$devOnly = @(
    '.antigravity', '.github', 'tests', '.agentignore', '.aider.conf.yml', '.env.agents.example', '.editorconfig', '.npmrc',
    'AGENTS.md', 'AGENTS_WORKFLOW.md', 'GEMINI.md', 'CLAUDE.md', 'agent-runner.ps1', 'pr_description.md', 'reset_verified.php',
    'start_dev.bat', 'start_demo_kantor.bat', 'pgsql_ekspor.bat', 'pgsql_impor.bat',
    'package.json', 'package-lock.json', 'vite.config.js', 'tailwind.config.js', 'postcss.config.js', 'phpunit.xml'
)
foreach ($item in $devOnly) {
    $p = Join-Path $dest $item
    if (Test-Path $p) { Remove-Item $p -Recurse -Force }
}

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
# Path sertifikat SSL milik laptop ini tidak ada di laptop tujuan; tanpa baris ini aplikasi memakai
# bundel bawaan proyek (resources/certs/cacert.pem) untuk API direktori sekolah.
$envText = [regex]::Replace($envText, '(?m)^SCHOOL_DIRECTORY_CA_BUNDLE=.*\r?\n?', '')
# Kunci notifikasi push (VAPID) tidak dipakai lewat alamat WiFi (bukan HTTPS) dan kunci privatnya tidak boleh
# ikut ke laptop lain; dengan kunci kosong, layout tidak mendaftarkan push sama sekali.
$envText = [regex]::Replace($envText, '(?m)^(VAPID_(?:PUBLIC|PRIVATE)_KEY)=.*$', '$1=')
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

# --- Database demo -----------------------------------------------------------------------------
# Bawaan: database demo dibuat BARU dari seeder di folder salinan, sehingga zip tidak membawa akun/data
# hasil uji coba lokal. -KeepDatabase memakai database.sqlite folder kerja (disalin konsisten lewat VACUUM INTO).
$destDb = Join-Path $dest 'database\database.sqlite'
$sqliteCopy = {
    param([string]$from, [string]$to)
    if (Test-Path $to) { Remove-Item $to -Force }
    $code = "`$pdo = new PDO('sqlite:' . `$argv[1]); `$pdo->exec('PRAGMA wal_checkpoint(TRUNCATE)'); `$pdo->exec('VACUUM INTO ' . `$pdo->quote(`$argv[2]));"
    & $phpExe -r $code $from $to
    if ($LASTEXITCODE -ne 0 -or -not (Test-Path $to)) { Fail "Gagal menyalin database SQLite ke $to." }
}
if ($envText -match '(?m)^DB_CONNECTION=sqlite') {
    if ($KeepDatabase) {
        $srcDb = Join-Path $root 'database\database.sqlite'
        if (-not (Test-Path $srcDb)) { Fail 'database\database.sqlite tidak ada di folder kerja.' }
        Info 'Menyalin database.sqlite folder kerja (-KeepDatabase)...'
        & $sqliteCopy $srcDb $destDb
    } else {
        Info 'Membuat database demo baru dari seeder (migrate:fresh --seed, bisa 1-3 menit)...'
        $savedEnv = @{}
        foreach ($v in 'DB_CONNECTION', 'DB_DATABASE', 'DB_HOST', 'DB_PORT', 'DB_USERNAME', 'DB_PASSWORD', 'APP_ENV', 'APP_DEBUG', 'VIEW_COMPILED_PATH') {
            $savedEnv[$v] = [Environment]::GetEnvironmentVariable($v)
            [Environment]::SetEnvironmentVariable($v, $null)
        }
        Get-ChildItem (Join-Path $dest 'database') -Filter 'database.sqlite*' | Remove-Item -Force
        New-Item -ItemType File -Path $destDb | Out-Null
        # Start-Process + berkas log: keluaran stderr seeder tidak dianggap galat PowerShell ($ErrorActionPreference = Stop)
        $seedLog = Join-Path $stage 'seed.log'
        $seedErr = Join-Path $stage 'seed.err'
        $proc = Start-Process -FilePath $phpExe -ArgumentList @("`"$(Join-Path $dest 'artisan')`"", 'migrate:fresh', '--seed', '--force', '--no-interaction') `
            -WorkingDirectory $dest -NoNewWindow -Wait -PassThru -RedirectStandardOutput $seedLog -RedirectStandardError $seedErr
        $seedCode = $proc.ExitCode
        Get-Content $seedLog, $seedErr -ErrorAction SilentlyContinue | Where-Object { $_.Trim() } | Select-Object -Last 3 | ForEach-Object { Write-Host "        $_" }
        Remove-Item $seedLog, $seedErr -ErrorAction SilentlyContinue
        foreach ($v in $savedEnv.Keys) { [Environment]::SetEnvironmentVariable($v, $savedEnv[$v]) }
        if ($seedCode -ne 0) { Fail 'migrate:fresh --seed gagal di folder salinan. Jalankan manual untuk melihat pesannya.' }

        # Unggahan & lampiran chat hasil uji coba lokal bisa berisi data pribadi dan tidak dirujuk data
        # demo baru. Berkas bernama acak (hashName Laravel) hanya dipertahankan bila disebut di seeder.
        $seederText = (Get-ChildItem (Join-Path $root 'database\seeders') -Filter *.php | Get-Content -Raw) -join "`n"
        $removed = 0
        foreach ($sub in 'private\chat', 'private\attachments', 'private\profile-photos', 'private\final_reports') {
            $dir = Join-Path $dest "storage\app\$sub"
            if (Test-Path $dir) { $removed += @(Get-ChildItem $dir -Recurse -File).Count; Remove-Item $dir -Recurse -Force; New-Item -ItemType Directory $dir | Out-Null }
        }
        Get-ChildItem (Join-Path $dest 'storage\app') -Recurse -File |
            Where-Object { $_.Name -match '^[A-Za-z0-9]{40}\.[A-Za-z0-9]+$' -and -not $seederText.Contains($_.Name) } |
            ForEach-Object { Remove-Item $_.FullName -Force; $removed++ }
        # YearlyProjectsFiveYearsSeeder juga menyalin ke public/storage; di salinan itu menjadi folder biasa
        # (bukan tautan) sehingga logo/gambar tidak tampil. start_demo.bat membuat tautannya ulang.
        $publicStorage = Join-Path $dest 'public\storage'
        if (Test-Path $publicStorage) { cmd /c rmdir /s /q "`"$publicStorage`"" | Out-Null }
        if (Test-Path $publicStorage) { Remove-Item $publicStorage -Recurse -Force }
        Info "Database demo baru siap; $removed berkas unggahan uji coba lokal tidak ikut ke zip."
    }
    Get-ChildItem (Join-Path $dest 'database') -Filter 'database.sqlite-*' | Remove-Item -Force
    # Salinan awal untuk RESET_DATA_DEMO.bat (mengembalikan data setelah gladi bersih)
    & $sqliteCopy $destDb (Join-Path $dest 'database\database.awal.sqlite')
}

$guidePdf = Join-Path $root 'docs\Panduan_IT_Demo_SIP-MAGANG.pdf'
if (Test-Path $guidePdf) {
    Copy-Item $guidePdf (Join-Path $dest 'PANDUAN_IT_SIP-MAGANG.pdf') -Force
} else {
    Write-Host '[PERHATIAN] docs\Panduan_IT_Demo_SIP-MAGANG.pdf belum ada (php scripts\build_it_guide.php).' -ForegroundColor Yellow
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
Write-Host 'Di laptop tujuan: ekstrak zip ke C:\, buka folder SIP-MAGANG, klik dua kali 1_MULAI_DEMO.bat.'
Write-Host 'Panduan untuk petugas IT: PANDUAN_IT_SIP-MAGANG.pdf dan BACA_DULU.txt di dalam zip.'
