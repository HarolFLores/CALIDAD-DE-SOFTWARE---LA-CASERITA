<?php
/**
 * Cargador mínimo de variables de entorno (.env) sin dependencias externas.
 * Las variables ya definidas en el proceso tienen prioridad sobre el archivo.
 */
final class Env
{
    private static bool $loaded = false;

    public static function load(?string $path = null): void
    {
        if (self::$loaded) {
            return;
        }
        self::$loaded = true;

        $path = $path ?: dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env';
        if (!is_file($path) || !is_readable($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            if (str_starts_with($line, 'export ')) {
                $line = trim(substr($line, 7));
            }

            $separator = strpos($line, '=');
            if ($separator === false) {
                continue;
            }

            $key = trim(substr($line, 0, $separator));
            $rawValue = trim(substr($line, $separator + 1));

            if ($key === '' || !preg_match('/^[A-Z_][A-Z0-9_]*$/i', $key)) {
                continue;
            }

            $quote = $rawValue[0] ?? '';
            $quoted = strlen($rawValue) >= 2
                && (($quote === '"' && substr($rawValue, -1) === '"')
                    || ($quote === "'" && substr($rawValue, -1) === "'"));

            if ($quoted) {
                $value = substr($rawValue, 1, -1);
                if ($quote === '"') {
                    $value = str_replace(
                        ['\\n', '\\r', '\\"', '\\\\'],
                        ["\n", "\r", '"', '\\'],
                        $value
                    );
                }
            } else {
                $value = preg_replace('/\s+#.*$/', '', $rawValue) ?? $rawValue;
            }

            if (getenv($key) === false) {
                putenv($key . '=' . $value);
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
            }
        }
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        self::load();
        $value = getenv($key);
        return $value === false ? $default : $value;
    }

    public static function bool(string $key, bool $default = false): bool
    {
        $value = self::get($key);
        if ($value === null || $value === '') {
            return $default;
        }
        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $default;
    }

    public static function int(string $key, int $default = 0): int
    {
        $value = self::get($key);
        return ($value === null || $value === '') ? $default : (int)$value;
    }
}
