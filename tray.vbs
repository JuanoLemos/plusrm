Set objShell = CreateObject("WScript.Shell")
objShell.CurrentDirectory = CreateObject("Scripting.FileSystemObject").GetParentFolderName(WScript.ScriptFullName)
objShell.Run "powershell -NoProfile -ExecutionPolicy Bypass -WindowStyle Hidden -File ""scripts\tray\+RMSrv.ps1""", 0, False
