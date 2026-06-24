# Changelog

Todos los cambios notables en este proyecto se documentarán en este archivo.

El formato está basado en [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
y este proyecto adhiere a [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Parser multi-formato: detecta automáticamente ROADMAP estándar (Now/Next/Later) vs extendido (#tecnico/#ui/#ux)
- ProjectInfoReader: lee CHANGELOG, DILIGENCIA, ADR_SUMMARY, SISTEMA, bugs.md, incidentes.md
- Dashboard enriquecido: badges de versión, stack, ADRs, bugs, formato en sidebar + header
- Vista de secciones colapsables para ROADMAPs extendidos (Nemesis-style)
- Bloqueado como estado separado en stats

### Changed
- Upgrade Diligencia v1.16.5 → v1.17.9
- BUILD guarda agregado a AGENTS.md
- opencode.jsonc creado con instrucciones del proyecto
- LICENSE (AGPL-3.0), doc/mutaciones.md, doc/qa/UX-CHECKLIST.md agregados
- .opencode/themes/ sincronizado con 7 variantes nuevas (claro, oscuro, neon, verde, naranja, celeste, pastel)
- 4 comandos stale sincronizados (CBP, doctor, explica, PENDING)
- DILIGENCIA.md bump a v1.17.9
### Changed
### Deprecated
### Removed
### Fixed
### Security

## [0.1.0] - 2026-06-10

### Added
- Versión inicial del proyecto.
- Adaptación a metodología Diligencia v1.17.2.
- Estructura base del dashboard +RM.
