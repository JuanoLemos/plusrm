<!-- ADAPTAR: Personalizar con las features reales del proyecto. El checklist es genérico; adaptar cada columna al contexto del proyecto (web, juego, API, etc.). -->

# UX-CHECKLIST — <NOMBRE_DEL_PROYECTO>

Checklist de control UX para funciones nuevas y existentes.
Verifica que cada feature tenga los elementos UX mínimos antes de darla por terminada.

---

## Verificaciones post-BUILD (rápida)

Cada vez que se implementa un feature, verificar:

### Visibilidad
- [ ] ¿El usuario puede ver el control/elemento relevante?
- [ ] ¿Tiene icono, etiqueta o tooltip que explique qué hace?
- [ ] ¿Funciona en los formatos que soporta el proyecto (desktop/mobile/web)?

### Configuración
- [ ] ¿Requiere toggle o ajuste en Settings?
- [ ] ¿Persiste entre sesiones (localStorage, DB, archivo)?
- [ ] ¿Está en la sección correcta de configuración?

### Feedback visual
- [ ] ¿El usuario recibe confirmación visual de que la acción ocurrió?
- [ ] ¿Hay cambio de color, número, animación o estado en pantalla?
- [ ] ¿En caso de error, se muestra advertencia clara?

### Consistencia
- [ ] ¿Sigue el patrón de features similares existentes?
- [ ] ¿El código está en el lugar correcto según la arquitectura del proyecto?
- [ ] ¿La configuración se guarda donde corresponde?

---

## Reglas de actualización

- **Durante /plan de un feature**: planear el comportamiento visual esperado en la sección "Ideas pendientes"
- **Después de cada BUILD**: revisar el estado UX del feature y actualizarlo
- **Al descartar una idea**: mover a "Ideas deprecadas" con fecha y motivo

---

## Features implementadas

| ID | Feature | Visibilidad | Configuración | Feedback | Estado UX |
|----|---------|-------------|---------------|----------|-----------|
| — | <!-- feature --> | — | — | — | ⏳ Pendiente |

## Ideas pendientes

| ID | Idea | Origen | Prioridad | Estado |
|----|------|--------|-----------|--------|
| — | <!-- idea discutida no build aún --> | /plan | — | ⏳ Propuesta |

## Ideas deprecadas

| ID | Idea | Fecha | Motivo |
|----|------|-------|--------|
| — | — | — | — |
