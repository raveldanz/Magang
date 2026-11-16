<#
  Menjaga komputer server tidak sleep dan layar tidak mati selama demo SIP-MAGANG berjalan.
  Dijalankan oleh 1_MULAI_DEMO.bat di jendela kecil; berhenti sendiri setelah server demo dimatikan.
  Tidak butuh hak Administrator dan tidak mengubah pengaturan daya Windows (hanya berlaku selama skrip hidup).
#>
param([string]$AppDir)

$signature = '[DllImport("kernel32.dll")] public static extern uint SetThreadExecutionState(uint esFlags);'
$api = Add-Type -MemberDefinition $signature -Name 'SipMagangPower' -Namespace 'SipMagang' -PassThru
# ES_CONTINUOUS | ES_SYSTEM_REQUIRED | ES_DISPLAY_REQUIRED
$flags = [uint32]'0x80000003'

function DemoRunning {
    @(Get-CimInstance Win32_Process -Filter "Name = 'php.exe'" -ErrorAction SilentlyContinue |
        Where-Object { $_.CommandLine -and $_.CommandLine.Contains('sip_magang_demo_server') -and (-not $AppDir -or $_.CommandLine.Contains($AppDir)) }).Count -gt 0
}

Write-Host 'SIP-MAGANG: komputer dijaga agar tidak sleep selama demo berjalan. Jendela ini menutup sendiri.'
$started = Get-Date
$missing = 0
while ($true) {
    [void]$api::SetThreadExecutionState($flags)
    Start-Sleep -Seconds 30
    if (DemoRunning) {
        $missing = 0
    } elseif (((Get-Date) - $started).TotalMinutes -gt 3) {
        # Beri waktu server menyala; setelah itu berhenti bila dua kali berturut-turut tidak ada server
        $missing++
        if ($missing -ge 2) { break }
    }
}
[void]$api::SetThreadExecutionState([uint32]'0x80000000')
