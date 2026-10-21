# Base de datos

Motor local: MariaDB 10.4 (`lacaserita_db`). El acceso se hace con PDO y
prepared statements.

`database/schema.sql` es la referencia versionada y solo contiene estructura.
`database/database.sql` (volcado local con datos) está ignorado por Git porque
incluye PII y credenciales; nunca debe publicarse.

Para tu entorno local se conserva además `database/local_staff_users.sql`, con
los usuarios `admin`, `cajero` y `repartidor` y sus contraseñas ya hasheadas.
Ese archivo también está ignorado y sirve para reconstruir esos accesos sin
volver a escribir las contraseñas en texto plano. La base local actual ya los
tiene; no es necesario importarlo de nuevo.

## Cambios de integridad aplicados

Migración: `database/migracion_normalizacion_segura.sql`.

1. Se completó `pedido.id_metodo` para pedidos POS antiguos que solo tenían el
   texto `metodo_pago`.
2. Se agregó `fk_pago_pedido` (`pedido_pago.id_pedido -> pedido.id_pedido`).
3. Se agregó `fk_dir_distrito` (`direccion.id_distrito -> distrito_delivery`).
4. Se agregó `fk_venta_usuario` (`venta.id_usuario -> usuario`), con
   `ON DELETE RESTRICT`.
5. Se agregó el índice `venta (id_usuario, fecha)`.
6. Se agregó la columna generada `sesion_caja.usuario_sesion_abierta` y el
   índice único `uq_sesion_abierta_usuario`, que impide dos cajas abiertas para
   el mismo usuario.

La migración es reiterable: consulta `information_schema` antes de crear cada
objeto. No elimina columnas ni registros.

## Normalización: criterio aplicado

No se hicieron eliminaciones destructivas porque el código actual depende de
varias columnas históricas. Se priorizó integridad y compatibilidad:

| Caso | Decisión |
| --- | --- |
| `producto.stock` + `inventario.stock_actual` | Se mantienen sincronizados; unificarlos exige cambiar inventario, POS, kardex y auditoría en una misma fase. |
| `producto.imagen`, `imagen_2..4` | Se conservan; migrarlos a `producto_imagen` requiere adaptar altas, ediciones, catálogo y carrito. |
| `pedido.metodo_pago` + `id_metodo` | Se conserva el texto como snapshot histórico; se completó la FK cuando fue posible. |
| `pedido`, `detalle_pedido`, `envio_delivery` | Duplican datos del catálogo/usuario a propósito: son comprobantes históricos y no deben cambiar si el producto o la dirección cambian. |
| `venta` | Duplica totales del pedido; se conserva para compatibilidad. Queda una venta huérfana (`id_venta=15`, `id_pedido=25`) que impide una FK a `pedido` sin decidir antes si se repara o archiva. |
| `reposicion.proveedor` | Texto histórico de proveedor; se conserva junto a `id_proveedor`. |

## Fases futuras sugeridas

1. **Imágenes de producto**: crear `producto_imagen` con `id_producto`, `ruta`,
   `orden` y `es_principal`; migrar las 4 columnas y adaptar las lecturas.
2. **Stock único**: definir `inventario` como fuente de verdad y dejar
   `producto.stock` como columna calculada/compatibilidad.
3. **Venta/pedido**: decidir si `venta` sigue siendo una entidad separada o una
   vista de `pedido`; resolver el registro huérfano antes de agregar la FK.
4. **Método de pago**: dejar `pedido.id_metodo` como fuente y `metodo_pago` como
   snapshot de texto.
5. **Configuración SMTP**: no guardar contraseñas en `configuracion_correo`;
   usar `.env`.

Cada fase debe incluir respaldo, migración reversible y pruebas de POS,
checkout, reportes y logística.

## Consultas de diagnóstico

```sql
-- Pedidos sin método normalizado
SELECT COUNT(*) FROM pedido WHERE id_metodo IS NULL;

-- Ventas sin pedido
SELECT v.* FROM venta v LEFT JOIN pedido p ON p.id_pedido = v.id_pedido
WHERE p.id_pedido IS NULL;

-- Cajas abiertas duplicadas por usuario (debe devolver 0 filas)
SELECT id_usuario, COUNT(*) c FROM sesion_caja
WHERE estado = 'abierta' GROUP BY id_usuario HAVING c > 1;

-- Diferencias de stock entre producto e inventario
SELECT p.id_producto, p.stock, i.stock_actual
FROM producto p JOIN inventario i ON i.id_producto = p.id_producto
WHERE p.stock <> i.stock_actual;
```
