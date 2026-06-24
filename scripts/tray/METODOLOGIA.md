# METODOLOGÍA — +RMSrv

> System Tray Manager para +RM Dashboard.

---

## Propósito

+RMSrv es una aplicación de bandeja del sistema (system tray) que
administra `php -S` como proceso hijo. Evita tener ventanas de consola
visibles y proporciona acceso rápido al dashboard.

## Arquitectura

```
+RMSrv.ps1
  │
  ├── cmd /c php -S localhost:8585    ← proceso gestionado
  │
  ├── logs/+RM.log                   ← stdout/stderr + eventos
  │
  └── NotifyIcon (tray)              ← menú contextual
        ├── Abrir Dashboard  (abre http://localhost:8585)
        ├── Reiniciar         (taskkill /T + respawn)
        └── Cerrar            (kill + exit)
```

## Ciclo de vida

1. **Init**: se crea el icono de bandeja y el menú contextual.
2. **Start-up**: `php -S` se lanza automáticamente como proceso oculto.
3. **Runtime**:
   - stdout/stderr se redirigen asíncronamente a `logs/+RM.log`.
   - Un `Timer` de Windows Forms (1,5 s tick) actualiza el estado y muestra
     notificaciones en el hilo correcto.
   - Si el proceso hijo muere, el icono muestra "Stopped" y notifica al usuario.
4. **Restart**: mata el árbol de procesos completo (`taskkill /T`) y relanza.
5. **Shutdown**: mata proceso hijo, libera recursos, cierra el pump de mensajes.

## Formato de Log

```
[2026-06-10 21:30:00] [OUT] PHP 8.2.12 Development Server (http://localhost:8585) started
[2026-06-10 21:30:01] [ERR] ...
[2026-06-10 21:30:02] [EVENT] Server exited with code 1
[2026-06-10 21:30:03] PHP server started (PID 12345)
```

| Tag       | Origen                     |
|-----------|----------------------------|
| `[OUT]`   | stdout del proceso hijo    |
| `[ERR]`   | stderr del proceso hijo    |
| `[EVENT]` | Evento Exited del proceso  |
| sin tag   | Mensaje interno del tray   |

## Requisitos

- Windows 10/11
- PowerShell 5.1+
- PHP 8+ en `C:\xampp\php\php.exe`

## Instalación

No requiere instalación. Ejecutar:

```cmd
cd C:\xampp\htdocs\+RM
scripts\tray\+RMSrv.bat
```

El `.bat` lanza `+RMSrv.ps1` (en el mismo directorio) en una ventana
PowerShell oculta. Ambos archivos (`*.bat` y `*.ps1`) deben coexistir en
`scripts/tray/`.

## Solución de problemas

| Problema | Causa probable | Solución |
|---|---|---|
| El icono no aparece | Script bloqueado por ExecutionPolicy | Usar `-ExecutionPolicy Bypass` |
| Error puerto ocupado | Puerto 8585 en uso | `netstat -ano` y matar proceso |
| Balloon tip no se muestra | Focus Assist activo en Windows | Desactivar en Configuración > Sistema > Focus Assist |
| "Stopped" inmediato | Error al iniciar PHP | Revisar logs/+RM.log |
| No se abre el navegador | Navegador predeterminado no configurado | Abrir manualmente http://localhost:8585 |

## Gestión de procesos

- `taskkill /PID /T /F` asegura que **todo** el árbol de procesos (cmd → php) sea eliminado.
- Los event handlers `OutputDataReceived` y `ErrorDataReceived` son asíncronos;
  no bloquean el pump de mensajes del tray.

## Notas de seguridad

- El script usa `ExecutionPolicy Bypass` como parámetro de línea de comandos.
  No modifica la política global del sistema.
- No almacena credenciales ni tokens.
- Los logs pueden contener URLs, nombres de archivo y mensajes de error del
  servidor. No compartir logs públicamente.

---

Versión: 0.1.0 | Última actualización: 2026-06-10
