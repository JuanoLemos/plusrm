#Requires -Version 5.1

<#
.SYNOPSIS
  +RMSrv - System Tray Manager for +RM Dashboard
.DESCRIPTION
  Runs as a Windows notification area icon. Spawns the PHP built-in
  server on launch and provides right-click menu:
  Abrir Dashboard, Reiniciar, Cerrar.
  All stdout/stderr is logged to logs/+RM.log.
.NOTES
  Version: 0.1.0
  Author:  Juan Manuel Lemos
#>

Add-Type -AssemblyName System.Windows.Forms
Add-Type -AssemblyName System.Drawing

#region Singleton (mutex)
$script:Mutex = New-Object System.Threading.Mutex($false, "+RM-Tray-Mutex")
if (-not $script:Mutex.WaitOne(0, $false)) {
    [System.Windows.Forms.MessageBox]::Show(
        "+RM Dashboard ya esta corriendo.",
        "+RM",
        [System.Windows.Forms.MessageBoxButtons]::OK,
        [System.Windows.Forms.MessageBoxIcon]::Information
    )
    exit
}
#endregion

#region Configuration
$ProjectRoot = Resolve-Path "$PSScriptRoot\..\.."
$LogDir     = Join-Path $ProjectRoot "logs"
$PhpExe     = "C:\xampp\php\php.exe"
$PhpRoot    = "C:\xampp\htdocs\+RM"
$DashUrl    = "http://localhost:8585"
#endregion

#region Globals
$script:DevProc     = $null
$script:TrayIcon    = $null
$script:IconBitmap  = $null
$script:StatusTimer = $null

$script:State = @{
    LogFile            = Join-Path $LogDir "+RM.log"
    Exiting            = $false
    PendingBalloon     = $null
}
#endregion

#region Logging
function Write-Log {
    param([string]$Message)
    $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    $line = "[$timestamp] $Message"
    Add-Content -Path $script:State.LogFile -Value $line -ErrorAction SilentlyContinue
}

function Ensure-LogDir {
    if (-not (Test-Path $LogDir)) {
        New-Item -ItemType Directory -Path $LogDir -Force | Out-Null
    }
}
#endregion

#region Icon helpers
function New-TrayIcon {
    $script:IconBitmap = New-Object System.Drawing.Bitmap(16, 16)
    $g = [System.Drawing.Graphics]::FromImage($script:IconBitmap)
    $g.SmoothingMode = [System.Drawing.Drawing2D.SmoothingMode]::HighQuality
    $g.Clear([System.Drawing.Color]::FromArgb(26, 26, 46))
    $brush = New-Object System.Drawing.SolidBrush([System.Drawing.Color]::FromArgb(233, 69, 96))
    $g.FillEllipse($brush, 0, 1, 14, 14)
    $g.DrawString("+RM", [System.Drawing.Font]::new("Segoe UI", 6, [System.Drawing.FontStyle]::Bold),
        [System.Drawing.Brushes]::White, 0, 3)
    $g.Dispose()
    $brush.Dispose()
    return [System.Drawing.Icon]::FromHandle($script:IconBitmap.GetHicon())
}
#endregion

#region Process management
function Start-DevServer {
    if ($script:DevProc -and -not $script:DevProc.HasExited) {
        Write-Log "Server already running (PID $($script:DevProc.Id))"
        return
    }

    Ensure-LogDir
    Write-Log "Starting PHP dev server..."

    $psi = New-Object System.Diagnostics.ProcessStartInfo
    $psi.FileName = $PhpExe
    $psi.Arguments = "-S localhost:8585 -t `"$PhpRoot`""
    $psi.WindowStyle = [System.Diagnostics.ProcessWindowStyle]::Hidden
    $psi.UseShellExecute = $true
    $psi.CreateNoWindow = $true
    $psi.WorkingDirectory = $PhpRoot

    $script:DevProc = New-Object System.Diagnostics.Process
    $script:DevProc.StartInfo = $psi
    $script:DevProc.EnableRaisingEvents = $true

    $stateRef = $script:State
    Register-ObjectEvent -InputObject $script:DevProc -EventName Exited -Action {
        $exitCode = $Event.SourceEventArgs.ExitCode
        $s = $using:stateRef
        $ts = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
        Add-Content -Path $s.LogFile -Value "[$ts] [EVENT] Server exited with code $exitCode" -ErrorAction SilentlyContinue
        if (-not $s.Exiting) {
            $s.PendingBalloon = @{
                Title = "+RM"
                Text  = "Server stopped (exit $exitCode)"
                Icon  = [System.Windows.Forms.ToolTipIcon]::Warning
            }
        }
    } | Out-Null

    $script:DevProc.Start() | Out-Null

    Write-Log "PHP server started (PID $($script:DevProc.Id))"
    Update-TrayStatus
    $script:TrayIcon.ShowBalloonTip(3000, "+RM", "Server running (PID $($script:DevProc.Id))", [System.Windows.Forms.ToolTipIcon]::Info)
}

function Stop-DevServer {
    if (-not $script:DevProc -or $script:DevProc.HasExited) { return }

    Write-Log "Stopping server..."
    try {
        $procId = $script:DevProc.Id
        & cmd.exe /c "taskkill /PID $procId /T /F 2>nul"
        $script:DevProc.WaitForExit(5000) | Out-Null
    } catch {
        Write-Log "Failed to kill PID ${procId}: $_"
    }
    $script:DevProc.Dispose()
    $script:DevProc = $null

    # Safety net: kill anything holding port 8585
    try {
        Write-Log "Cleaning up port 8585..."
        $pidLine = & cmd.exe /c "netstat -ano 2>nul | findstr `":8585.*LISTENING`"" 2>&1
        if ($pidLine -is [array]) { $pidLine = $pidLine[0] }
        if ($pidLine -match '\s+(\d+)$') {
            $orphanId = $matches[1]
            & cmd.exe /c "taskkill /PID $orphanId /F 2>nul"
            Write-Log "Killed orphan PID $orphanId on port 8585"
        }
    } catch {
        Write-Log "Port cleanup error: $_"
    }

    Write-Log "Server stopped"
    Update-TrayStatus
}

function Restart-DevServer {
    Write-Log "Restarting..."
    Stop-DevServer
    Start-Sleep 1
    Start-DevServer
}

function Open-Dashboard {
    try {
        Start-Process $DashUrl
        Write-Log "Opened dashboard to $DashUrl"
    } catch {
        Write-Log "Failed to open browser: $_"
    }
}
#endregion

#region Cleanup
function Cleanup-Exit {
    $script:State.Exiting = $true
    Write-Log "Shutting down +RM tray..."
    if ($script:StatusTimer) { $script:StatusTimer.Stop(); $script:StatusTimer.Dispose() }
    Stop-DevServer
    if ($script:TrayIcon) {
        $script:TrayIcon.Visible = $false
        $script:TrayIcon.Dispose()
    }
    if ($script:IconBitmap) { $script:IconBitmap.Dispose() }
    if ($script:Mutex) {
        $script:Mutex.ReleaseMutex()
        $script:Mutex.Dispose()
    }
    [System.Windows.Forms.Application]::Exit()
}

function Update-TrayStatus {
    if (-not $script:TrayIcon) { return }
    if ($script:DevProc -and -not $script:DevProc.HasExited) {
        $script:TrayIcon.Text = "+RM - Running (PID $($script:DevProc.Id))"
    } else {
        $script:TrayIcon.Text = "+RM - Stopped"
    }
}
#endregion

#region Tray icon setup
$script:TrayIcon = New-Object System.Windows.Forms.NotifyIcon
$script:TrayIcon.Icon = New-TrayIcon
$script:TrayIcon.Text = "+RM - Starting..."
$script:TrayIcon.Visible = $true

$menu = New-Object System.Windows.Forms.ContextMenuStrip

$openItem = New-Object System.Windows.Forms.ToolStripMenuItem
$openItem.Text = "Abrir Dashboard"
$openItem.Image = [System.Drawing.SystemIcons]::Information.ToBitmap()

$restartItem = New-Object System.Windows.Forms.ToolStripMenuItem
$restartItem.Text = "Reiniciar"

$sep = New-Object System.Windows.Forms.ToolStripSeparator

$closeItem = New-Object System.Windows.Forms.ToolStripMenuItem
$closeItem.Text = "Cerrar"

$menu.Items.AddRange(@($openItem, $restartItem, $sep, $closeItem))

$openItem.Add_Click({ Open-Dashboard })
$restartItem.Add_Click({ Restart-DevServer })

$closeItem.Add_Click({
    $result = [System.Windows.Forms.MessageBox]::Show(
        "Are you sure you want to stop the server and exit?",
        "+RM", [System.Windows.Forms.MessageBoxButtons]::YesNo,
        [System.Windows.Forms.MessageBoxIcon]::Question)
    if ($result -eq [System.Windows.Forms.DialogResult]::Yes) {
        Cleanup-Exit
    }
})

$script:TrayIcon.ContextMenuStrip = $menu

$script:TrayIcon.Add_MouseDoubleClick({
    if ($_.Button -eq [System.Windows.Forms.MouseButtons]::Left) {
        Open-Dashboard
    }
})
#endregion

#region Timer
$script:StatusTimer = New-Object System.Windows.Forms.Timer
$script:StatusTimer.Interval = 1500
$script:StatusTimer.Add_Tick({
    Update-TrayStatus
    $b = $script:State.PendingBalloon
    if ($b) {
        $script:State.PendingBalloon = $null
        $script:TrayIcon.ShowBalloonTip(5000, $b.Title, $b.Text, $b.Icon)
    }
})
$script:StatusTimer.Start()
#endregion

#region Entry point
Ensure-LogDir
Write-Log "========== +RM Dashboard started =========="
Write-Log "Project root: $ProjectRoot"
Write-Log "Log file: $($script:State.LogFile)"

Start-DevServer

Write-Log "Tray icon active. Right-click for menu."

try {
    [System.Windows.Forms.Application]::Run()
} finally {
    Cleanup-Exit
}
#endregion
