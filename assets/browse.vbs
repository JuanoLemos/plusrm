Set shell = CreateObject("Shell.Application")
Set folder = shell.BrowseForFolder(0, "Seleccionar carpeta del proyecto", 0, "")
If Not folder Is Nothing Then
    WScript.Echo folder.Self.Path
End If
