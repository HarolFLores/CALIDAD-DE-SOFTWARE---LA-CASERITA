# La Caserita — Tienda Online, POS y ERP

Aplicación web en PHP (sin framework) que integra una **tienda online**, un
**punto de venta (POS)** para mostrador y un **panel de administración tipo ERP**
para la gestión de productos, inventario, ventas, pedidos, compras, logística y pagos.

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

## Requisitos

- Apache y PHP 8.1+ (desarrollado con PHP 8.3)
- MySQL o MariaDB (también soporta PostgreSQL vía `DB_DRIVER=pgsql`)
- Extensiones PHP: `pdo_mysql` (o `pdo_pgsql`), `mbstring`, `openssl`
- Apache debe permitir `AllowOverride` si usas reglas de reescritura

## Instalación (XAMPP local)

1. Copia el proyecto dentro de `htdocs` (por ejemplo
   `htdocs/clonacion harol/CALIDAD-DE-SOFTWARE---LA-CASERITA`).
2. Crea la base de datos e importa el esquema:
   - Importa `database/database.sql` en MySQL/MariaDB (phpMyAdmin o consola).
   - Nombre de base de datos por defecto: `lacaserita_db`.
3. Ajusta la conexión si es necesario (variables de entorno o valores por
   defecto en `Core/dataBase.php`):
   `DB_DRIVER`, `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`.
4. Abre en el navegador:
   `http://localhost/clonacion%20harol/CALIDAD-DE-SOFTWARE---LA-CASERITA/index.php`

## Variables de entorno

| Variable        | Descripción                          | Valor por defecto   |
|-----------------|--------------------------------------|---------------------|
| `DB_DRIVER`     | `mysql` o `pgsql`                    | `mysql`             |
| `DB_HOST`       | Host de la base de datos             | `localhost`         |
| `DB_PORT`       | Puerto                               | `3306`              |
| `DB_NAME`       | Nombre de la base de datos           | `lacaserita_db`     |
| `DB_USER`       | Usuario                              | `root`              |
| `DB_PASS`       | Contraseña                           | *(vacío)*           |
| `SMTP_HOST`     | Servidor SMTP                        | `smtp.gmail.com`    |
| `SMTP_PORT`     | Puerto SMTP                          | `587`               |
| `SMTP_USERNAME` | Usuario del correo                   | —                   |
| `SMTP_PASSWORD` | Contraseña de aplicación del correo  | —                   |
| `SMTP_FROM_EMAIL` | Correo remitente                   | —                   |

> **Seguridad:** no guardes credenciales reales (SMTP, Mercado Pago) en el
> repositorio; defínelas como variables de entorno. Revisa `Config/` antes de
> publicar el proyecto.

## Base de datos

El archivo `database/database.sql` contiene el esquema. Se excluyeron los
registros locales de usuarios, direcciones, pedidos y ventas. En
`database/` también hay scripts auxiliares de importación y parches.

## Integraciones

- **Correo (SMTP):** verificación de PIN, recuperación de contraseña y
  notificaciones (config en `Config/mail.php`).
- **Mercado Pago:** pago con tarjeta y retorno de transacción
  (config en `Config/mercadopago.php`, servicio en `Core/MercadoPagoService.php`).
- **Login con Google:** inicio de sesión alternativo.
- **POS móvil:** emparejamiento por QR para usar el celular como lector de códigos.

## Despliegue

El proyecto puede ejecutarse en Wasmer (`app.yaml` + `wasmer.toml`), que empaqueta
un runtime PHP 8.3 y monta el volumen `uploads-volume` en `/app/uploads`:

```
php -t /app -S localhost:8080
```
