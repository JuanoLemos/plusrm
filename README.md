# +RM — Dashboard de Roadmaps

Dashboard web PHP que escanea directorios y muestra roadmaps de proyectos Diligencia. Parser multi-formato, lectura de 7 tipos de docs, badges de versión/stack/bugs, system tray Windows. Solo lectura, nunca modifica proyectos externos.

- **Repositorio:** [github.com/JuanoLemos/plusrm](https://github.com/JuanoLemos/plusrm)
- **Stack:** PHP 8.2 + HTML5 + JS vanilla sobre XAMPP
- **Licencia:** AGPL-3.0

## Arranque

```cmd
scripts\tray\+RMSrv.bat
```

Ícono en bandeja del sistema, sin consola. Abrir `http://localhost:8585/`

## Features

- Escaneo automático de directorios en busca de ROADMAP.md
- Parser multi-formato: estándar (Now/Next/Later) + extendido (#secciones)
- 7 tipos de documentos por proyecto (ROADMAP, CHECKLIST, CHANGELOG, DILIGENCIA, ADR, bugs, incidentes)
- System tray Windows (menú: Abrir / Reiniciar / Cerrar)
- Solo lectura — nunca modifica proyectos externos
