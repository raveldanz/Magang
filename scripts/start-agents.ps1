<#
.SYNOPSIS
    Launcher and orchestrator script for Multi-Agent AI Workflow (Antigravity, Claude Code, Aider).

.DESCRIPTION
    Loads API keys from .env.agents (if present), verifies runtimes and paths,
    and launches Claude Code or Aider sessions with optimal flags.

.PARAMETER Aider
    Launch an Aider pair programming session.

.PARAMETER Claude
    Launch a Claude Code autonomous session.

.PARAMETER Check
    Perform a complete readiness and environment check.

.PARAMETER Model
    Model selection for Aider (e.g. 'sonnet', 'deepseek', '4o', 'gemini'). Defaults to sonnet.

.EXAMPLE
    .\scripts\start-agents.ps1 -Check
    .\scripts\start-agents.ps1 -Claude
    .\scripts\start-agents.ps1 -Aider
    .\scripts\start-agents.ps1 -Aider -Model deepseek
#>

[CmdletBinding(DefaultParameterSetName = "Interactive")]
param (
    [Parameter(ParameterSetName = "Aider")]
    [switch]$Aider,

    [Parameter(ParameterSetName = "Claude")]
    [switch]$Claude,

    [Parameter(ParameterSetName = "Check")]
    [switch]$Check,

    [Parameter()]
    [string]$Model = "",

    [Parameter()]
    [string]$ExtraArgs = ""
)

$ErrorActionPreference = "Stop"

# 1. Ensure Python, Pip, Scripts, and npm paths are active in session
$scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$projectRoot = Split-Path -Parent $scriptDir
Set-Location $projectRoot

$pythonDir = "$env:LOCALAPPDATA\Programs\Python\Python312"
$scriptsDir = "$env:LOCALAPPDATA\Programs\Python\Python312\Scripts"
$launcherDir = "$env:LOCALAPPDATA\Programs\Python\Launcher"
$npmDir = "$env:APPDATA\npm"

$prependPaths = @($scriptsDir, $pythonDir, $launcherDir, $npmDir)
foreach ($p in $prependPaths) {
    if (Test-Path $p) {
        $env:Path = "$p;" + $env:Path
    }
}

# 2. Function to load .env.agents
function Load-AgentEnv {
    $envAgentsPath = Join-Path $projectRoot ".env.agents"
    if (Test-Path $envAgentsPath) {
        Write-Host "[*] Loading environment variables from .env.agents..." -ForegroundColor Cyan
        Get-Content $envAgentsPath | ForEach-Object {
            $line = $_.Trim()
            if ($line -and (-not $line.StartsWith("#")) -and ($line -match '^([^=]+)=(.*)$')) {
                $name = $matches[1].Trim()
                $val = $matches[2].Trim()
                if ($val) {
                    [System.Environment]::SetEnvironmentVariable($name, $val, [System.EnvironmentVariableTarget]::Process)
                }
            }
        }
    } else {
        Write-Host "[!] Note: .env.agents not found. Create one from .env.agents.example to store API keys." -ForegroundColor Yellow
    }
}

# 3. Health & Readiness Check
function Run-EnvironmentCheck {
    Write-Host "==========================================================" -ForegroundColor Green
    Write-Host "        MULTI-AGENT ENVIRONMENT & RUNTIME CHECK           " -ForegroundColor Green
    Write-Host "==========================================================" -ForegroundColor Green

    # Check Git
    try {
        $gitVer = git --version
        $isGit = git rev-parse --is-inside-work-tree 2>$null
        if ($isGit -eq "true") {
            Write-Host "[OK] Git: $gitVer (Inside Git worktree)" -ForegroundColor Green
        } else {
            Write-Host "[WARN] Git is installed ($gitVer), but NOT inside a git worktree!" -ForegroundColor Yellow
        }
    } catch {
        Write-Host "[ERR] Git is not found!" -ForegroundColor Red
    }

    # Check Node & Claude Code
    try {
        $nodeVer = node --version
        Write-Host "[OK] Node.js: $nodeVer" -ForegroundColor Green
    } catch {
        Write-Host "[ERR] Node.js not found!" -ForegroundColor Red
    }

    try {
        $claudeVer = claude --version 2>$null
        Write-Host "[OK] Claude Code: $claudeVer" -ForegroundColor Green
    } catch {
        Write-Host "[ERR] Claude Code is not found in PATH! (npm install -g @anthropic-ai/claude-code)" -ForegroundColor Red
    }

    # Check Python & Aider
    $pyVer = ""
    try {
        $pyVer = (& python --version 2>&1) | Out-String
        $pyVer = $pyVer.Trim()
    } catch {}

    if (-not $pyVer -or ($pyVer -notlike "Python*")) {
        if (Test-Path "$pythonDir\python.exe") {
            $pyVer = (& "$pythonDir\python.exe" --version 2>&1) | Out-String
            $pyVer = $pyVer.Trim()
        }
    }

    if ($pyVer -and ($pyVer -like "Python*")) {
        Write-Host "[OK] Python: $pyVer" -ForegroundColor Green
    } else {
        Write-Host "[ERR] Python 3 not found in PATH!" -ForegroundColor Red
    }

    $aiderVer = ""
    try {
        $aiderVer = (& aider --version 2>&1) | Out-String
        $aiderVer = $aiderVer.Trim()
    } catch {}

    if (-not $aiderVer -or ($aiderVer -notlike "*aider*")) {
        if (Test-Path "$scriptsDir\aider.exe") {
            $aiderVer = (& "$scriptsDir\aider.exe" --version 2>&1) | Out-String
            $aiderVer = $aiderVer.Trim()
        }
    }

    if ($aiderVer) {
        Write-Host "[OK] Aider: $aiderVer" -ForegroundColor Green
    } else {
        Write-Host "[ERR] Aider not found in PATH! (pip install aider-chat)" -ForegroundColor Red
    }

    # Check API keys
    Write-Host "`n--- API Credentials Status ---" -ForegroundColor Cyan
    $keys = @("ANTHROPIC_API_KEY", "DEEPSEEK_API_KEY", "OPENAI_API_KEY", "GEMINI_API_KEY")
    foreach ($k in $keys) {
        $v = [System.Environment]::GetEnvironmentVariable($k, "Process")
        if (-not $v) { $v = [System.Environment]::GetEnvironmentVariable($k, "User") }
        if (-not $v) { $v = [System.Environment]::GetEnvironmentVariable($k, "Machine") }
        
        if ($v) {
            $masked = $v.Substring(0, [Math]::Min(7, $v.Length)) + "..."
            Write-Host "  [OK] $k is SET ($masked)" -ForegroundColor Green
        } else {
            Write-Host "  [--] $k is NOT set" -ForegroundColor Yellow
        }
    }

    Write-Host "`nReady check completed.`n" -ForegroundColor Green
}

# 4. Launch Claude Code
function Start-ClaudeSession {
    Load-AgentEnv
    Write-Host "`n[>>>] Launching Claude Code Autonomous Session..." -ForegroundColor Cyan
    Write-Host "Tip: Type /init in Claude Code to initialize project guidelines or /exit to quit.`n" -ForegroundColor DarkGray
    
    if ($ExtraArgs) {
        & claude $ExtraArgs
    } else {
        & claude
    }
}

# 5. Launch Aider
function Start-AiderSession {
    Load-AgentEnv
    
    # Determine model
    $selectedModel = $Model
    if (-not $selectedModel) {
        $envModel = [System.Environment]::GetEnvironmentVariable("AIDER_DEFAULT_MODEL", "Process")
        if ($envModel) {
            $selectedModel = $envModel
        } else {
            $selectedModel = "sonnet"
        }
    }

    $modelFlag = ""
    switch ($selectedModel.ToLower()) {
        "sonnet"   { $modelFlag = "--sonnet" }
        "opus"     { $modelFlag = "--opus" }
        "deepseek" { $modelFlag = "--model deepseek/deepseek-coder" }
        "4o"       { $modelFlag = "--4o" }
        "gemini"   { $modelFlag = "--model gemini/gemini-2.5-pro" }
        default    { $modelFlag = "--model $selectedModel" }
    }

    Write-Host "`n[>>>] Launching Aider Pair Programming Session (Model: $selectedModel)..." -ForegroundColor Cyan
    Write-Host "Tip: Use /add <file> to focus files, /undo to revert changes, or /exit to quit.`n" -ForegroundColor DarkGray

    $cmd = "aider $modelFlag"
    if ($ExtraArgs) {
        $cmd += " $ExtraArgs"
    }
    
    Invoke-Expression $cmd
}

# -------------------------------------------------------------
# Main Execution Branch
# -------------------------------------------------------------
if ($Check) {
    Load-AgentEnv
    Run-EnvironmentCheck
    exit 0
}

if ($Claude) {
    Start-ClaudeSession
    exit 0
}

if ($Aider) {
    Start-AiderSession
    exit 0
}

# Interactive Menu if called without switch
Load-AgentEnv
Write-Host "==========================================================" -ForegroundColor Green
Write-Host "          MULTI-AGENT SESSION CONTROLLER                  " -ForegroundColor Green
Write-Host "==========================================================" -ForegroundColor Green
Write-Host "1. Launch Claude Code (Deep Autonomous Implementer)"
Write-Host "2. Launch Aider (Precision Pair Programmer - Sonnet)"
Write-Host "3. Launch Aider (DeepSeek Coder)"
Write-Host "4. Launch Aider (OpenAI GPT-4o)"
Write-Host "5. Run Environment & Credential Check"
Write-Host "Q. Quit"
Write-Host ""

$choice = Read-Host "Select option (1-5, Q)"
switch ($choice) {
    "1" { Start-ClaudeSession }
    "2" { Start-AiderSession -Model "sonnet" }
    "3" { Start-AiderSession -Model "deepseek" }
    "4" { Start-AiderSession -Model "4o" }
    "5" { Run-EnvironmentCheck }
    Default { Write-Host "Exited." }
}
