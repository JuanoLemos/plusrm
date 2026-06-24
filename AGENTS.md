# +RM — AGENTS.md

## Idioma

Español — todas las respuestas del agente deben ser en español. Si el agente contesta en inglés, recordarle explícitamente que responda en español.

## Mapeo de rutas

| Variable | Ruta | Descripción |
|----------|------|-------------|
| $RM | `ROADMAP.md` | Roadmap del proyecto +RM |
| $ADRS | `doc/arch/` | Architecture Decision Records |
| $CHANGELOG | `CHANGELOG.md` | Historial de versiones |
| $CHECKLIST | `CHECKLIST.md` | Checklist de tareas operativas |
| $SISTEMA | `doc/arch/SISTEMA.md` | Documento de sistema (arquitectura, stack, dependencias) |
| $GUIAS | `doc/guias/` | Guías de uso, configuración, contribución |
| $MECANICAS | `doc/mecanicas/` | Reglas de negocio / mecánicas del proyecto |
| $TESTING | *(definido en HARNESS.md)* | Comando de test del proyecto |
| $HARNESS | `.opencode/HARNESS.md` | Configuración de harness (test, lint, skills, stack) |
| $PEND | `doc/pendientes/` | Pendientes de revisión |
| $QA | `doc/qa/` | Situaciones a revisar (QA) |
| $BUGS | `doc/arch/bugs.md` | Bug tracker (P1/P2/P3, severidad, estado) |
| $INCIDENTS | `doc/arch/incidentes.md` | Incidentes runtime y crashes |
| $MAIN_APP | `index.php` | Dashboard principal |
| $CRITICAL_FILES | `index.php`, `lib/`, `config.php` | Archivos para backup crítico |

## Comandos del proyecto

| Comando | Qué hace | Tipo |
|---------|----------|------|
| /commit | Commit rápido con formato | Global |
| /debug | Análisis profundo de sección | Global |
| /plan | Planificar tarea en modo lectura | Global |
| /health | Verificar integridad del código | Global |
| /limpiar | Eliminar archivos temporales | Global |
| /estado | Reporte rápido del proyecto | Local |
| /* | Próximos 5 pasos | Local |
| /checklist | Cruce RM + Checklist | Local |
| /rm | Revisar Roadmap | Local |
| /foco | Cargar contexto de área | Local |
| /updoc | Actualizar documentación | Local |

## Foco por área

- `scanner` → Detección: escaneo de directorios, detección de proyectos con RM
- `dashboard` → Frontend: UI del tablero, tablas, barras de avance, gráficos
- `api` → Backend: endpoints PHP, parsers de roadmap, estimador HS→días
- `hours` → Logs: registro y lectura de horas trabajadas (JSON)
- `config` → Configuración: directorios escaneados, settings del dashboard

## Comandos fundamentales heredados

Comandos globales de la metodología Diligencia copiados a `.opencode/commands/`.

| Comando | Propósito |
|---------|-----------|
| /CBP | Orquestador de workflows (PLAN→BUILD) |
| /version | Bump semver + CHANGELOG |
| /commit | Conventional Commit rápido |
| /doctor | Diagnóstico y reparación del proyecto |
| /updoc | Sincronización de documentación |
| /salud | Reporte de salud del proyecto |
| /estado | Reporte rápido del proyecto |
| /plan | Planificar tarea en modo lectura |
| /debug | Análisis profundo de sección |
| /health | Verificar integridad del código |
| /foco | Cargar contexto de área |
| /checklist | Cruce RM + Checklist |
| /rm | Revisar Roadmap |
| /bug | Registrar bug |
| /incidente | Registrar incidente |
| /backup | Backup de archivos críticos |
| /explica | Explicar sección de código |
| /limpiar | Eliminar archivos temporales |
| /legal | Verificar licencias y legal |
| /pushgh | Push a GitHub |
| /qa | Revisión de calidad |
| /next | Próximos pasos |
| /reanudar | Recuperar sesión interrumpida |
| /informe-salud | Reporte multi-proyecto |
| /notify | Notificaciones |
| /report | Generar reporte |
| /news | Cambios entrantes |
| /diligencia-check | Validar estructura Diligencia |
| /deprecar | Deprecar funcionalidad |
| /head | Leer inicio de archivos |
| /+rm | Agregar RM |
| /+rmi | Agregar RMI |
| /+guia | Agregar guía |
| /+mec | Agregar mecánica |
| /+pend | Agregar pendiente |
| /upguia | Actualizar guía |
| /upmec | Actualizar mecánica |
| /PENDING | Listar pendientes |
| /ADAPTAR-COMANDOS | Guía de adaptación de comandos |

## Disciplina BUILD

BUILD = aplicar cambios, NO commitear. Solo /commit, /CBP y /version ejecutan git commit.
Al terminar cualquier BUILD, reportar cambios aplicados y sugerir /CBP.

## Skills

<!-- Agregar skills específicos del proyecto si aplican -->
