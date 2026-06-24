@echo off
setlocal

REM ============================================================
REM +RMSrv.bat - System Tray Manager for +RM Dashboard
REM ============================================================
REM Launches +RMSrv.ps1 (same directory) in a hidden PowerShell
REM window. The tray icon appears in the notification area.
REM Right-click for Abrir Dashboard / Reiniciar / Cerrar.
REM ============================================================

set "PS_SCRIPT=%~dp0+RMSrv.ps1"

if not exist "%PS_SCRIPT%" (
    echo [+RM] Error: +RMSrv.ps1 not found.
    echo Expected at: %PS_SCRIPT%
    pause
    exit /b 1
)

powershell -NoProfile -Command "Start-Process -WindowStyle Hidden -FilePath powershell -ArgumentList '-NoProfile','-ExecutionPolicy','Bypass','-WindowStyle','Hidden','-File','%PS_SCRIPT%'"
exit /b 0
