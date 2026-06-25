# ROADMAP — +RM

---

## Ahora (Now)

| ID | Item | Prioridad | Estado | Depende de |
|----|------|-----------|--------|------------|
| R03 | Logs de horas: sistema de registro y lectura JSON | P2 | 🔴 Pendiente | — |
| R04 | Estimador HS → días: cálculo según promedio diario | P2 | 🔴 Pendiente | R03, R05 |
| R05 | Dashboard UI: finalizar pulido visual y UX | P3 | 🔴 Pendiente | — |
| R16 | Botón Refresh + Auto-refresh cada 15 min | P1 | 🟡 En progreso | — |
| R17 | Ocultar proyectos (✕ en sidebar + restaurar) | P1 | 🟡 En progreso | — |
| R18 | Agregar proyecto manual (modal + ruta libre) | P1 | 🟡 En progreso | — |

## Siguiente (Next)

| ID | Item | Prioridad | Estado | Depende de |
|----|------|-----------|--------|------------|
| R08 | Registro de horas desde el dashboard (UI para log JSON) | P2 | 🔴 Pendiente | R03 |
| R09 | Exportación de reportes (PDF/CSV) | P3 | 🔴 Pendiente | R05 |
| R10 | Alertas de deadline / retraso | P3 | 🔴 Pendiente | R04 |

## Futuro (Later)

| ID | Item | Prioridad | Estado | Depende de |
|----|------|-----------|--------|------------|
| R11 | Multi-usuario / permisos | P3 | 🔴 Pendiente | — |
| R12 | Histórico de cambios por proyecto | P3 | 🔴 Pendiente | — |

## Completado

| Item | Instancia |
|------|-----------|
| R01 — Detección de proyectos (Scanner) | v0.1.0 |
| R02 — Parser de roadmap (RoadmapParser) | v0.1.0 |
| R06 — API REST endpoints PHP | v0.1.0 |
| R07 — Config (config.php + directorios) | v0.1.0 |
| R13 — Parser multi-formato (estándar + extendido) | v0.2.0 |
| R14 — ProjectInfoReader (7 docs: CHANGELOG, DILIGENCIA, ADR, SISTEMA, bugs, incidents, CHECKLIST) | v0.2.0 |
| R15 — Dashboard enriquecido (badges: version, ADRs, bugs, stack) | v0.2.0 |
| R16 — Botón Refresh + Auto-refresh 15 min | v0.3.0 |
| R17 — Ocultar proyectos (✕ + restaurar desde "Mostrar ocultos") | v0.3.0 |
| R18 — Agregar proyecto manual (modal + ruta libre) | v0.3.0 |
