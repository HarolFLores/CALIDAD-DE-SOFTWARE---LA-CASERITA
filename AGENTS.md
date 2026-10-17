# AGENTS.md — Convenciones para agentes de IA

Proyecto: **La Caserita** (PHP sin framework, MVC ligero + Front Controller).
Ver `README.md` para arquitectura. Este archivo define reglas de trabajo que
**todos los agentes deben respetar** al modificar el panel de administración.

## Reglas obligatorias de CSS

1. **NUNCA usar `!important`.** Para ganar especificidad, prefija el selector con
   `html body ` (patrón ya usado en el proyecto). Si el selector ya empieza con
   `html`, déjalo igual; si empieza con `body`, reemplaza `body` → `html body`.
2. **No usar estilos inline nuevos.** Si necesitas vencer un `style=` inline del
   markup, edita el view (no agregues `!important`). Los `!important` fueron
   eliminados del proyecto (506 → 0); no los reintroduzcas.
3. Si un `@media`/`@supports`/`@keyframes` lleva un comentario `/* ... */` antes,
   ten cuidado al prefijar selectores: no insertes `html body` dentro del preludio.

## Regla de la "X" de cierre en modales (admin)

Todos los botones de cierre **X** del panel admin deben verse **idénticos al del
login** (misma forma, tamaño, escala y rotación), conservando el **color propio**
de cada modal (por ejemplo, rojo `#dc2626` en modales de eliminar).

Referencia de geometría (login): `Styles/styles-auth-modal.css` (~45-81) y
`Styles/styles-login.css` (~607-645):
`position:absolute; top/right:16px; width/height: clamp(36px, 8vw, 44px);
border:2px solid transparent; border-radius:50%; background:#f1f5f9;
color:#64748b; font-size: clamp(1.4rem, 4vw, 1.8rem); line-height:1;
box-shadow:none;` → hover: `background:#f0fdf4; color:#15803d;
border-color:#15803d; transform: rotate(90deg);`

La regla unificada vive en `Styles/styles-users-admin.css` (bloque ~3350), **sin
scope `.admin-main`** (para cubrir modales fuera de `.admin-main`). Cubre:
`.modal-close`, `.btn-close-universal`, `.comp-modal-close`,
`.va-modal-close-icon`, `.modal-close-caja`, `.cat-modal-close-btn`,
`.prov-modal-close`. Variantes de peligro (rojo): `.btn-close-universal.is-danger`,
`.btn-close-universal.danger`, `#modalConfirmarEliminarProd .btn-close-universal`.

`styles-users-admin.css` se carga en: `usuarios_admin`, `mi_perfil_admin`,
`inventario`, `caja_punto_venta` (Cajero), `caja_admin`, `verificacion_pagos`,
`ventas_admin`, `dashboard_ganancias`, `proveedores_admin`, `categorias_admin`,
`compras_admin`, `carrusel_admin`, `delivery_admin`, `kardex_admin`; y se inyecta
vía `Scripts/admin-spa.js` en la navegación POS. **NO** se carga en el storefront
(home/cuenta), por eso las reglas sin `.admin-main` no afectan la tienda.

### Al crear o editar un modal en admin

- El botón de cierre X debe usar la clase cubierta (p. ej. `btn-close-universal`)
  y solo `position:absolute; top:...; right:...` inline si hace falta ubicarlo.
- **Si el modal ya tiene una X, elimina cualquier botón de pie que solo cierre
  ("Cancelar" / "Cerrar").** La X (o el clic fuera del modal) es el cierre; el pie
  debe quedar solo con la acción principal.
- Si el modal **no** tiene X, conserva su botón de cancelar (o agrégale la X
  unificada). No dejes modales sin ninguna forma de cerrarse.

## Validación antes de terminar

- `C:\xampp\php\php.exe -l <view.php>` para cada view tocada.
- `node --check <archivo.js>` para cada JS tocado.
- Revisa que las llaves `{}` de los CSS editados estén balanceadas.
