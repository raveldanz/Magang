<#
.SYNOPSIS
    Tri-Agent Orchestration Runner (Antigravity + Claude Code + Local Qwen)
.DESCRIPTION
    Menjalankan sub-agent (Qwen 2.5 Coder 7B atau Claude Code CLI) secara non-interaktif
    sebagai bagian dari pipeline tri-agen SIP-MAGANG.
.PARAMETER Agent
    Pilihan agen: 'qwen' atau 'claude'
.PARAMETER Prompt
    Instruksi atau prompt yang dikirim ke agen terpilih
.PARAMETER OutputFile
    (Opsional) Path berkas untuk menyimpan hasil keluaran agen
.EXAMPLE
    .\agent-runner.ps1 -Agent qwen -Prompt "Buat fungsi helper PHP validasi email"
.EXAMPLE
    .\agent-runner.ps1 -Agent claude -Prompt "Periksa status branch dan git diff"
.EXAMPLE
    .\agent-runner.ps1 -Agent qwen -Prompt "Buat fungsi helper" -OutputFile "output.txt"
#>

[CmdletBinding()]
param (
    [Parameter(Mandatory = $true, Position = 0)]
    [ValidateSet("qwen", "claude")]
    [string]$Agent,

    [Parameter(Mandatory = $true, Position = 1, ValueFromPipeline = $true)]
    [string]$Prompt,

    [Parameter(Mandatory = $false, Position = 2)]
    [string]$OutputFile
)

# Pastikan encoding console UTF-8
[Console]::OutputEncoding = [System.Text.Encoding]::UTF8
$OutputEncoding = [System.Text.Encoding]::UTF8

$targetAgent = $Agent.ToLower().Trim()
$response = ""

Write-Host "===================================================" -ForegroundColor Cyan
Write-Host " [TRI-AGENT RUNNER] Target Agent: $targetAgent" -ForegroundColor Cyan
Write-Host "===================================================" -ForegroundColor Cyan

switch ($targetAgent) {
    "qwen" {
        Write-Host "-> Menjalankan Qwen 2.5 Coder 7B via Ollama (Local/Offline)..." -ForegroundColor Yellow
        $response = $Prompt | ollama run qwen2.5-coder:7b
    }
    "claude" {
        Write-Host "-> Menjalankan Claude Code CLI (Deep Reasoning / Active Session)..." -ForegroundColor Yellow
        # Menggunakan null stream stdin ($null | ...) agar non-interaktif tanpa hanging di PowerShell
        $response = $null | claude -p "$Prompt"
    }
}

if ($OutputFile) {
    Set-Content -Path $OutputFile -Value $response -Encoding UTF8
    Write-Host "`n[Output berhasil disimpan ke: $OutputFile]" -ForegroundColor Green
}

# Tampilkan hasil ke stdout
$response
