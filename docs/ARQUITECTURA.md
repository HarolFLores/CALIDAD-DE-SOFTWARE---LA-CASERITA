# Arquitectura

La Caserita usa un MVC ligero con un único front controller.

```text
index.php
  ├─ Core/Security.php      cabeceras, cookies, origen y caché de assets
  ├─ Core/Env.php           carga .env
  ├─ Core/SessionManager.php sesiones en MySQL
  ├─ Core/AuthMiddleware.php control de roles
  ├─ Controllers/           lógica de cada módulo
  ├─ Models/                PDO + prepared statements
  └─ Views/                 HTML/PHP por área
```

## Flujo de una petición

1. `index.php` carga `.env` y aplica las políticas comunes de `Security`.
2. Se inicia la sesión en base de datos (`sys_sessions`).
3. `AuthMiddleware` valida el rol de la ruta solicitada.
4. El controlador ejecuta el modelo y renderiza la vista.
5. `Security::optimizeHtml()` reemplaza el `?v=` de CSS/JS locales por la fecha
   real del archivo, de modo que el navegador pueda cachear sin quedarse con
   versiones viejas.

## Carpetas

| Carpeta | Responsabilidad |
| --- | --- |
| `Config/` | Configuración que lee variables de entorno y `carrusel.json`. |
| `Controllers/` | Acciones de tienda, POS, repartidor y ERP. |
| `Core/` | Infraestructura transversal (BD, sesiones, seguridad, pagos). |
| `Models/` | Consultas SQL y reglas de persistencia. |
| `Views/` | Plantillas; no deben contener lógica de negocio. |
| `Styles/` | CSS global y por vista (`Styles/Vistas/...`). |
| `Scripts/` | JS global y por vista (`Scripts/Vistas/...`). |
| `database/` | Esquema, importador y migraciones. |
| `docs/` | Esta documentación. |

## Convención de vistas y assets

- El CSS de una vista se enlaza **en el `<head>`**, nunca al final del `<body>`.
- El JS propio de una vista vive en `Scripts/Vistas/<Área>/<vista>.js`.
- Las vistas no deben crear `<style>` ni `<script>` inline nuevos.
- El versionado de assets lo resuelve el servidor; no hace falta usar
  `time()` en cada `<link>`.
- Las vistas del ERP que se cargan por AJAX deben declarar sus estilos en
  `ensureModuleStyles()` de `Scripts/admin-spa.js`.

## Estados y capas

- La sesión guarda identidad y rol; no guarda secretos de proveedores.
- Los totales históricos de `pedido`, `detalle_pedido`, `envio_delivery` y
  `reposicion` son snapshots deliberados: no deben recalcularse desde el
  catálogo actual.
- `producto.stock` e `inventario.stock_actual` se mantienen sincronizados por
  la capa de inventario; no escribir uno sin el otro.
