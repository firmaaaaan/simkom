# Daftarkan SimKomAgent sebagai scheduled task ONSTART (SYSTEM).
# Dipanggil dari install.bat — dipisah ke file .ps1 agar bebas masalah
# escaping/continuation caret pada file batch.
param(
    [Parameter(Mandatory = $true)][string]$TaskName,
    [Parameter(Mandatory = $true)][string]$ExePath,
    [string]$ScriptPath = ''
)

$action = if ($ScriptPath) {
    New-ScheduledTaskAction -Execute $ExePath -Argument ('"{0}"' -f $ScriptPath)
} else {
    New-ScheduledTaskAction -Execute $ExePath
}

$trigger = New-ScheduledTaskTrigger -AtStartup
$principal = New-ScheduledTaskPrincipal -UserId 'SYSTEM' -LogonType ServiceAccount -RunLevel Highest
$settings = New-ScheduledTaskSettingsSet -RestartCount 999 -RestartInterval (New-TimeSpan -Minutes 1) -ExecutionTimeLimit ([TimeSpan]::Zero)

Register-ScheduledTask -TaskName $TaskName -Action $action -Trigger $trigger -Principal $principal -Settings $settings -Force | Out-Null
