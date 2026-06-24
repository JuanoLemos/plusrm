# Diligencia v1.17.9 — Estructura estándar de documentación

Sello de metodología para proyectos OpenCode.

---

## Qué es

Diligencia es una convención de estructura de documentación para proyectos OpenCode.
Define dónde vive cada tipo de archivo, cómo se nombran las variables de ruta, y cómo se organizan los comandos.

## Convención

| Tipo | Ubicación |
|---|---|
| Roadmap | `ROADMAP.md` (raíz) |
| Checklist | `CHECKLIST.md` (raíz) |
| Changelog | `CHANGELOG.md` (raíz) |
| ADRs, sistema, bitácora | `doc/arch/` |
| Guías de usuario | `doc/guias/` (incluye `ESTANDAR-COMANDOS.md`) |
| Mecánicas del juego | `doc/mecanicas/` |
| Variables de ruta | `AGENTS.md` → `Mapeo de rutas` |
| Comandos locales | `.opencode/commands/` |
| Harness | `.opencode/HARNESS.md` (test, lint, skills, stack) |
| Comandos globales | `~/.config/opencode/commands/` |

## Proyectos adaptados

| Proyecto | Fecha | Estado |
|---|---|---|
| Diligencia (autor) | 2026-05-31 | ✅ |
| Némesis Detective | 2026-05-08 | ✅ |
| MarketAI | 2026-05-08 | ✅ |
| +RM | 2026-06-10 | ✅ |

## Historial

| Versión | Fecha | Cambios |
|---|---|---|
| v1.16.3 | 2026-06-06 | Provider-agnostic (razonamiento/ejecución). GUIA_ONBOARDING.md (api key genérica). /informe-salud inter-proyecto. SDD agents nota ADAPTAR. |
| v1.16.2 | 2026-06-05 | /doctor backup preventivo + $BACKUPS/$BACKUP_KEEP. Labels 15 guías bump. |
| v1.16.1 | 2026-06-05 | Higiene pública: sanitizar paths, redactar proyectos privados, audiencia (desarrollador→orquestador). |
| v1.16.0 | 2026-06-05 | GitHub readiness: README.md, LICENSE (GPL-3.0), .gitignore, CONTRIBUTING.md, CODE_OF_CONDUCT.md. GUIA_DE_CONTRIBUCION.md (guía), MECANICA-ENFORCEMENT.md (mecánica). CI workflow en .github/workflows/. |
| v1.15.3 | 2026-06-05 | Idioma español como Buena Práctica: templates AGENTS.md y HARNESS.md incluyen sección "Idioma". opencode.jsonc instructions: +"Siempre responde en español". GUIA_DE_BUENAS_PRACTICAS.md §10. |
| v1.15.2 | 2026-06-05 | /circuito → /CBP rename + 12 stale labels bump + gaps resueltos (CHANGELOG v1.14.0, GUIA_DE_ADAPTACION step 11.5). /CBP default → completo. |
| v1.15.1 | 2026-06-05 | Sync documental masivo: /updoc — 14 labels stale corregidos, INDEX sincronizado. /explica scope expandido. CBP corrections. |
| v1.14.0 | 2026-06-05 | /version con PRE-FLIGHT integral (6 checks: A-staleness, B-salud, C-/explica scope, D-template sync, E-cross-refs, F-variables). Micro-circuito pre-flight detecta alertas y pregunta forzar/abortar antes del bump. Circuito /updoc → /version cerrado. |
| v1.13.0 | 2026-06-03 | ADR_SUMMARY.md, identidad.md (guía), MANDATO.md (mandato Director). adr-template.md enriquecido (tabla decisión/impacto + bullets). CHECKLIST.md con dashboard de versiones. /adaptar Flujo A personaliza identidad.md y MANDATO.md por proyecto. |
| v1.12.0 | 2026-06-02 | Meta-PLAN (PRO) + BUILD (FLASH) en /CBP. /salud BUILD*. Meta-orquestador con agentes/skills. |
| v1.11.0 | 2026-06-02 | /CBP — orquestador de workflows vinculantes. "Próximo paso en el circuito" removido de comandos individuales. |
| v1.10.3 | 2026-06-02 | Circuito cíclico PLAN→BUILD vinculante entre comandos. /updoc, /version, /doctor: sección "Próximo paso en el circuito". MECANICA-CBP.md en doc-base. |
| v1.10.2 | 2026-06-01 | Template DILIGENCIA.md y /adaptar.md sincronizados a versión del proyecto. /updoc Fase D5 detecta staleness automáticamente. |
| v1.10.1 | 2026-06-01 | Bug fix: /adaptar y template stale. /version ahora sincroniza template+adaptar al versionar Diligencia. /updoc D5 detecta staleness. |
| v1.10.0 | 2026-06-01 | /version: autodetección post-/doctor sugiere patch. /doctor: circuito → /version patch tras correcciones. Nuevo /reanudar: recuperación de sesión tras interrupción brusca. |
| v1.9.1 | 2026-06-01 | /doctor sobre Diligencia: items stale en ROADMAP.md movidos de Siguiente a Completado. Gap D2 cerrado (diligencia-check.yml referenciado explícitamente en /adaptar). |
| v1.9.0 | 2026-06-01 | Integración con CI/CD: GitHub Actions workflow de validación de estructura Diligencia (Category A/ADR-003). Copiado automáticamente por /adaptar vía doc-base. |
| v1.8.0 | 2026-06-01 | CHANGELOG Keep a Changelog, ADR lifecycle, /commit Conventional Commits, /version [YANKED]+migración, plantillas stack (Node/Python/Go), GUIA_REFERENCIA_RAPIDA.md, /explica mejorado, comandos --auto delegate desde /version. |
| v1.7.2 | 2026-06-01 | GUIA_ECOSISTEMAS.md: mapa de 9 ecosistemas, cadenas de orquestación, reglas de frontera. |
| v1.7.1 | 2026-06-01 | Convención semver 3-partes (vX.Y.Z). /version, /diligencia-check y DILIGENCIA.md compatibles con vX.Y.Z. |
| v1.7.0 | 2026-06-01 | /doctor — comando unificado de cuidado integral (3 fases, orquesta 6 sub-comandos). |
| v1.6 | 2026-06-01 | MECANICA-DOCUMENTAL.md, templates bugs/incidentes/sesion, GUIA_DE_BUENAS_PRACTICAS.md, /updoc Fase D cross-ref, /health stack-aware. |
| v1.5 | 2026-05-31 | Comandos /bug, /incidente. $BUGS, $INCIDENTS. Template bugs.md en doc-base. |
| v1.4 | 2026-05-31 | Template HARNESS.md. ADR-003: harness como estándar. |
| v1.3 | 2026-05-31 | /updoc auditoría documental entre versiones. /adaptar con conciencia de versión. |
| v1.2 | 2026-05-31 | 12 comandos universales (+mec, upmec, +rm, backup, etc). /adaptar copia comandos al proyecto. |
| v1.1 | 2026-05-31 | Estándar de comandos: guarda + imperativo + Formato/Validación/Anti-patrones. |
| v1.0 | 2026-05-08 | Convención inicial: doc-base template, $variables en AGENTS.md, dos capas de comandos, /adaptar global. |
