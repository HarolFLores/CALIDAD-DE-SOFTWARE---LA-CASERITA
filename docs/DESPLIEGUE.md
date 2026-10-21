# Despliegue

## Requisitos

- PHP 8.0+ con PDO MySQL, cURL, mbstring y OpenSSL.
- MySQL 8 o MariaDB 10.4+.
- Extensiones del navegador para la cámara solo en el escáner móvil (HTTPS en
  producción).

## Variables de entorno

1. Copiar `.env.example` a `.env`.
2. Completar los valores reales.
3. En producción:

```dotenv
APP_ENV=production
APP_DEBUG=0
SESSION_COOKIE_SECURE=1
MAIL_BACKUP_ENABLED=0
```

No subir `.env` a Git. Si el hosting no permite archivos, definir las variables
en su panel.

## Apache / XAMPP

El proyecto incluye `.htaccess` en la raíz. Requiere `mod_rewrite`,
`mod_headers`, `mod_expires` y `mod_deflate`; si `AllowOverride` está en
`None`, el bloqueo de carpetas internas no se aplica. En ese caso, configurar
el document root directamente en la carpeta pública o agregar las reglas en el
VirtualHost.

Comprobar que estos recursos respondan `404` (router) o `403` (Apache):

- `/Config/apis.php`
- `/Core/Security.php`
- `/database/schema.sql`
- `/scratch/emails_enviados`
- `/.git/config`

## Wasmer / PHP built-in

`wasmer.toml` arranca con `router.php`:

```toml
main-args = ["-S", "localhost:8080", "-t", "/app", "/app/router.php"]
```

El router bloquea las rutas internas, sirve assets estáticos y deriva el resto
a `index.php`. Para desarrollo local:

```powershell
php -S 127.0.0.1:8099 router.php
```

Las credenciales de Wasmer no se escriben en `app.yaml` ni en `Core/dataBase.php`.
Se guardan en el panel de la app: **Settings > Environment Vars**, y luego
**Save and Redeploy**. También se pueden cargar con:

```powershell
wasmer app secrets create --from-file=.env.wasmer
```

`.env.wasmer` es local y está ignorado por Git; contiene la conexión a la BD de
Wasmer y las claves de producción. No subirlo al repositorio.

## Base de datos

El repositorio incluye `database/schema.sql`, que contiene solo estructura y
ningún dato real. Importarlo en una base nueva y luego aplicar la migración:

```powershell
& "C:\xampp\mysql\bin\mysql.exe" -u root -e "CREATE DATABASE IF NOT EXISTS lacaserita_db CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci"
Get-Content -Raw database\schema.sql | & "C:\xampp\mysql\bin\mysql.exe" -u root lacaserita_db
Get-Content -Raw database\migracion_normalizacion_segura.sql | & "C:\xampp\mysql\bin\mysql.exe" -u root lacaserita_db
```

Los datos locales (`database/database.sql`, `database/local_staff_users.sql` y
respaldos `.sql`) están ignorados por Git porque contienen usuarios, direcciones,
teléfonos, hashes y credenciales SMTP. No subirlos al repositorio. La base local
actual conserva admin/cajero/repartidor para seguir probando en XAMPP. Para
publicar, usar solo `schema.sql` y un respaldo privado en el servidor. Antes de
cualquier migración hacer un respaldo de archivos y de la BD.

## Publicación en GitHub

1. Rotar todas las credenciales que estuvieron expuestas.
2. Verificar que `.env`, respaldos, `scratch` y `.agents` no estén rastreados:

```powershell
git status --short
git ls-files | Select-String -Pattern '\.env|scratch|backups|\.agents|database\.sql'
```

3. Si esas rutas ya están en el historial, purgarlas con `git filter-repo` o
   recrear el repositorio. Un `.gitignore` solo evita commits nuevos, no borra
   el pasado. **No publicar el historial actual**: contiene credenciales y el
   volcado antiguo con PII.
4. Configurar HTTPS y `SESSION_COOKIE_SECURE=1`.
5. Verificar que el servidor no permita listar directorios ni descargar `.sql`.

## Google Sign-In

El error `400: origin_mismatch` no se corrige en PHP: el dominio desplegado no
está autorizado en el cliente OAuth de Google Cloud.

1. En Google Cloud Console abrir **APIs y servicios > Credenciales**.
2. Editar el **OAuth 2.0 Client ID** de tipo *Web application* que corresponde
   al valor de `GOOGLE_CLIENT_ID`.
3. En **Authorized JavaScript origins** agregar los orígenes exactos, sin ruta
   ni barra final:
   - `http://localhost`
   - `http://127.0.0.1:8099` (solo si se usa ese puerto)
   - `https://TU-APP.wasmer.app`
   - el dominio propio, si se configura
4. Guardar, esperar unos minutos y probar en ventana incógnito.
5. En Wasmer definir `GOOGLE_CLIENT_ID` y las demás variables (`DB_*`, SMTP,
   Mercado Pago, RENIEC). El archivo `.env` no se sube al hosting.
6. Si la pantalla de consentimiento está en modo *Testing*, agregar el correo
   que va a iniciar sesión como usuario de prueba.

Esta implementación usa Google Identity Services con callback de credencial, por
lo que la validación clave es **Authorized JavaScript origins**. No se necesita
un redirect URI para este flujo.

## Caché

- HTML dinámico: `no-store` para no mostrar una sesión o vista anterior.
- CSS/JS/imágenes: caché de 7 días con invalidación por `?v=<filemtime>`.
- `Security::optimizeHtml()` hace el versionado automático de assets locales.
- No usar `time()` como versión: impide cachear y genera descargas repetidas.
