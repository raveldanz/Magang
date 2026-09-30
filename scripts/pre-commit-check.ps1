# ==============================================================================
# LOCAL GIT PRE-COMMIT QUALITY GATE & SANITY CHECK
# Lokasi: scripts/pre-commit-check.ps1
# Deskripsi: Runner verifikasi lokal sebelum commit untuk memastikan kebersihan cache
#            dan integritas sintaks seluruh berkas PHP yang sedang di-stage.
# ==============================================================================

# Pastikan script berjalan dari root repositori Git
$repoRoot = git rev-parse --show-toplevel 2>$null
if ($LASTEXITCODE -eq 0 -and (Test-Path $repoRoot)) {
    Set-Location $repoRoot
}

Write-Host "============================================================" -ForegroundColor Cyan
Write-Host " [PRE-COMMIT GATE] Menjalankan Pemeriksaan Kualitas Lokal... " -ForegroundColor Cyan
Write-Host "============================================================" -ForegroundColor Cyan

# 1. Pembersihan Cache Framework (Views, Routes, Config)
Write-Host "`n[1/2] Membersihkan cache aplikasi (optimize:clear)..." -ForegroundColor Yellow

$clearOutput = php artisan optimize:clear
if ($LASTEXITCODE -ne 0) {
    Write-Host "[-] GAGAL: optimize:clear menghasilkan exit code non-zero." -ForegroundColor Red
    exit 1
}
Write-Host "[+] Cache views, routes, dan config berhasil dibersihkan." -ForegroundColor Green

# 2. Sanity Check Sintaks PHP (php -l) pada Berkas yang Sedang Di-Stage
Write-Host "`n[2/2] Memeriksa sintaks berkas PHP yang sedang di-stage..." -ForegroundColor Yellow

$stagedFiles = git -c core.quotePath=false diff --cached --name-only --diff-filter=ACM
if ($LASTEXITCODE -ne 0) {
    Write-Host "[-] GAGAL: Tidak dapat membaca daftar berkas staged dari git." -ForegroundColor Red
    exit 1
}

$stagedPhpFiles = $stagedFiles | Where-Object { $_ -match '\.php$' }

if (-not $stagedPhpFiles) {
    Write-Host "[i] Tidak ada berkas PHP yang sedang di-stage. Melewati linting sintaks." -ForegroundColor Cyan
} else {
    $hasSyntaxError = $false
    $checkedCount = 0

    foreach ($file in $stagedPhpFiles) {
        if (Test-Path $file) {
            $checkedCount++
            $lintResult = php -l "$file"
            if ($LASTEXITCODE -ne 0) {
                Write-Host "[-] SINTAKS ERROR pada $file :" -ForegroundColor Red
                Write-Host $lintResult -ForegroundColor DarkRed
                $hasSyntaxError = $true
            } else {
                Write-Host "  [OK] $file" -ForegroundColor DarkGray
            }
        }
    }

    if ($hasSyntaxError) {
        Write-Host "`n============================================================" -ForegroundColor Red
        Write-Host " [PRE-COMMIT BLOCKED] Ditemukan kesalahan sintaks pada berkas!" -ForegroundColor Red
        Write-Host " Harap perbaiki sintaks sebelum melanjutkan proses commit." -ForegroundColor Red
        Write-Host "============================================================" -ForegroundColor Red
        exit 1
    }

    Write-Host "[+] Seluruh $checkedCount berkas PHP lolos sanity check (Strict Exit Code 0)." -ForegroundColor Green
}

Write-Host "`n============================================================" -ForegroundColor Green
Write-Host " [PRE-COMMIT PASSED] Seluruh Quality Gate Lolos 100%!       " -ForegroundColor Green
Write-Host "============================================================`n" -ForegroundColor Green

exit 0
