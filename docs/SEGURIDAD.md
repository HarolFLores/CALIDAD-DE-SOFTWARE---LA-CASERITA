# Seguridad

## Secretos y variables de entorno

Las credenciales se leen desde `.env` mediante `Core/Env.php`.

Archivos que ya no contienen secretos:

- `Config/apis.php` (RENIEC y Google)
- `Config/mail.php` (SMTP)
- `Config/mercadopago.php` (Mercado Pago)
- `Core/MercadoPagoService.php`
- `Models/MailHelper.php`

`.env` y `.env.*` están excluidos por `.gitignore`; `.env.example` documenta las
claves necesarias. En producción configure las variables en el panel del
hosting si no es posible usar un archivo `.env`.

> Las claves anteriores estuvieron dentro del historial Git, igual que
> `database/database.sql` con usuarios, direcciones, teléfonos, códigos Yape y
> la contraseña SMTP. Cambiar las claves en el proveedor (Gmail, Mercado Pago,
> apiperu y Google) es obligatorio antes de publicar. Si el repositorio ya se
> compartió, purgar el historial con `git filter-repo` o volver a crear el
> repositorio sin esos commits. `database/schema.sql` sí es seguro de publicar
> porque no contiene datos.

## Archivos que no deben servirse

`.htaccess` (Apache) y `router.php` (PHP built-in/Wasmer) bloquean:

- `.git`, `.agents`, `Config`, `Core`, `Controllers`, `Models`, `Views`,
  `database`, `scratch`, `backups`
- `.env`, `*.sql`, `*.log`, `*.zip`, `*.token`, `*.timestamp`, `AGENTS.md`
- Cualquier PHP distinto de `index.php`

Los respaldos SQL y los correos de prueba ya no se escriben dentro del
document root. Las copias locales de correo, si se habilitan, van a
`sys_get_temp_dir()/caserita_mail_outbox` o a `MAIL_BACKUP_DIR`.

## Sesiones y cookies

`Core/Security.php` aplica:

- cookies `HttpOnly`, `SameSite=Lax` y `Secure` cuando hay HTTPS;
- `session.use_strict_mode=1` y `use_only_cookies=1`;
- `Cache-Control: no-store` en respuestas dinámicas para evitar que el navegador
  muestre una vista anterior después de cerrar sesión o navegar por el ERP;
- cabeceras `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`,
  `Permissions-Policy` y HSTS cuando corresponde;
- detección de HTTPS directo o detrás de proxy (`X-Forwarded-Proto`).

En producción configure `SESSION_COOKIE_SECURE=1`.

## Origen y CSRF

- Las peticiones `POST/PUT/PATCH/DELETE` con `Origin` o `Referer` de otro host
  se rechazan con `403` desde `Security::validateSameOrigin()`.
- El login ya usa `hash_equals()` para el token CSRF.
- Los formularios nuevos deben incluir `csrf_token` y los controladores deben
  validarlo. Si se activa una validación global, primero hay que revisar todos
  los formularios y llamadas AJAX para no romper el POS.
- `SameSite=Lax` es una segunda barrera, no sustituye al token CSRF.

## Escáner móvil

Se eliminó el fallback que tomaba automáticamente la última caja abierta.

- `pos_escaner_movil` exige un token en la URL; sin token responde `403`.
- `pos_validar_emparejamiento` y `pos_escanear_movil` rechazan token vacío.
- `pos_escaneos_pendientes` sin token devuelve una cola vacía, sin consultar
  cajas ajenas.
- El emparejamiento administrativo sigue usando su token temporal propio.

El token debe viajar únicamente en el enlace/QR generado desde el POS.

## Prácticas para cambios futuros

1. No interpolar datos de usuario en SQL: usar prepared statements.
2. Escapar toda salida HTML con `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`.
3. No aceptar `id_usuario`, roles o precios desde el cliente sin validarlos.
4. No subir `.env`, respaldos, correos de prueba ni capturas de vouchers.
5. Revisar logs en `C:\xampp\apache\logs` o en el panel del hosting, no en el
   document root.

## Verificación rápida

```powershell
php -l index.php
php -l Core\Security.php
node --check Scripts\admin-spa.js

# Con el servidor PHP levantado:
curl.exe -i http://127.0.0.1:8099/Config/apis.php     # 404
curl.exe -i http://127.0.0.1:8099/database/schema.sql  # 404
curl.exe -i -X POST -H "Origin: http://evil.example" http://127.0.0.1:8099/index.php?action=login # 403
```
