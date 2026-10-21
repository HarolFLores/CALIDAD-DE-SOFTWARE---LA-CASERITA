# Documentación — La Caserita

- [Arquitectura](ARQUITECTURA.md)
- [Seguridad](SEGURIDAD.md)
- [Despliegue](DESPLIEGUE.md)
- [Base de datos](BASE_DATOS.md)

## Reglas rápidas

1. Leer la configuración desde `.env`; nunca escribir claves reales en PHP.
2. Toda vista debe cargar su CSS en `<head>` y su JS desde `Scripts/`.
3. No usar `!important` ni agregar `style="..."` nuevos.
4. No modificar el esquema sin respaldo y sin migración documentada.
5. No versionar volcados con datos reales; el esquema público es `database/schema.sql`.
6. Validar con `php -l`, `node --check` y una petición HTTP real.
