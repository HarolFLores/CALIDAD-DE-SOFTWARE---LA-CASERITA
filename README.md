# La Caserita — Tienda Online, POS y ERP

Aplicación web en PHP (sin framework) que integra una **tienda online**, un
**punto de venta (POS)** para mostrador y un **panel de administración tipo ERP**
para la gestión de productos, inventario, ventas, pedidos, compras, logística y pagos.

> Documentación detallada: [`docs/README.md`](docs/README.md) — arquitectura,
> seguridad, despliegue y base de datos.

## Descripción del proyecto

La Caserita cubre todo el ciclo comercial de una tienda de abarrotes/frutas:

- **Tienda online (cliente):** catálogo por categorías, carrito, checkout con
  delivery o recojo en tienda, pago con Yape/Plin, tarjeta (Mercado Pago) o
  efectivo, historial de compras e inicio de sesión con Google.
- **POS (cajero):** venta rápida en mostrador, escáner de productos por código,
  lectura desde el celular (QR), arqueo y apertura/cierre de caja.
- **Administración (ERP):** dashboard financiero, ventas y pedidos, categorías,
  productos e inventario, kardex valorizado, compras y proveedores,
  logística y despacho (delivery/recojo), auditoría de pagos digitales,
  usuarios y carrusel del home.
- **Repartidor:** panel para ver y actualizar sus entregas asignadas.

### Roles del sistema

| Rol          | Acceso principal                                                        |
|--------------|-------------------------------------------------------------------------|
| `cliente`    | Tienda, carrito, checkout, mi cuenta                                    |
| `cajero`     | POS, caja, historial de ventas, escáner móvil                           |
| `admin`      | Todo el panel                                       |
| `repartidor` | Panel de entregas                                                       |

## Configuración

1. Copiar `.env.example` a `.env` y completar credenciales.
2. No versionar `.env` ni respaldos/volcados SQL con datos reales.
3. El esquema público es `database/schema.sql`; no contiene datos.
4. En producción usar HTTPS y `SESSION_COOKIE_SECURE=1`.
4. Ver [`docs/DESPLIEGUE.md`](docs/DESPLIEGUE.md) para Apache/Wasmer.

## Arquitectura

El proyecto sigue un enfoque **MVC ligero con un Front Controller**:

```
index.php  →  AuthMiddleware  →  Controller  →  Model (PDO)  →  View (HTML)
```

- **Front Controller (`index.php`):** punto único de entrada. Lee `?action=...`,
  valida el rol del usuario y despacha al método del controlador correspondiente
  mediante un `switch`. Las rutas no reconocidas redirigen al home público.
- **Controladores (`Controllers/`):** contienen la lógica de cada módulo
  (usuarios, productos, inventario, carrito, pedidos, checkout, POS, ERP, etc.).
  Instancian modelos y renderizan vistas.
- **Modelos (`Models/`):** encapsulan el acceso a datos (consultas SQL) usando
  **PDO** con *prepared statements*. La conexión es un **singleton** en
  `Core/dataBase.php`.
- **Vistas (`Views/`):** plantillas PHP organizadas por área
  (`Admin`, `Cajero`, `Home`, `Cuenta`, `Checkout`, `Repartidor`, `Login`, ...).
- **Núcleo (`Core/`):** infraestructura transversal:
  - `dataBase.php` — conexión PDO (MySQL/MariaDB, opcional PostgreSQL).
  - `AuthMiddleware.php` — control de acceso por rol.
  - `SessionManager.php` — sesiones almacenadas en base de datos.
  - `ajaxHelper.php` — respuestas JSON para peticiones AJAX.
  - `MercadoPagoService.php` — integración con la pasarela de pago.
- **Configuración (`Config/`):** SMTP (`mail.php`), Mercado Pago
  (`mercadopago.php`) y carrusel (`carrusel.json`).
- **Recursos estáticos:** `Styles/` (CSS), `Scripts/` (JS), `Images/` y `uploads/`.

### Flujo de una petición

1. El navegador llama `index.php?action=ventas_admin`.
2. `index.php` verifica la sesión y el rol con `AuthMiddleware`.
3. Se ejecuta `$pedidoController->listarVentasAdmin()`.
4. El controlador consulta los modelos y hace `include` de la vista.
5. La vista genera el HTML (algunas acciones responden JSON vía `AjaxHelper`).

## Estructura de carpetas

```
CALIDAD-DE-SOFTWARE---LA-CASERITA/
├── index.php            # Front controller y enrutador
├── Config/              # Configuración (SMTP, Mercado Pago, carrusel)
├── Controllers/         # Lógica de cada módulo
├── Core/                # BD, sesiones, auth, helpers, Mercado Pago
├── Models/              # Acceso a datos (PDO)
├── Views/               # Plantillas PHP
├── Styles/              # Hojas de estilo (global y por vista)
├── Scripts/             # JavaScript (por vista)
├── Images/              # Logos e imágenes del sitio
├── uploads/             # Archivos subidos (productos, vouchers)
├── database/            # Esquema SQL y scripts de importación
├── app.yaml             # Configuración de despliegue (Wasmer)
└── wasmer.toml          # Empaquetado del runtime PHP
```
