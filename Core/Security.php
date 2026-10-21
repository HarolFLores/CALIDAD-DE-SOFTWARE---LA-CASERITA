<?php
require_once __DIR__ . '/Env.php';

/**
 * Endurecimiento transversal: cabeceras, cookies de sesión, validación de
 * origen y versionado estable de assets locales.
 */
final class Security
{
    public static function bootstrap(): void
    {
        Env::load();
        self::sendHeaders();
        self::configureSession();
        self::startOutputBuffer();
        self::validateSameOrigin();
    }

    private static function isHttps(): bool
    {
        if (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off') {
            return true;
        }
        if (strtolower((string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https') {
            return true;
        }
        if (strtolower((string)($_SERVER['HTTP_X_FORWARDED_SSL'] ?? '')) === 'on') {
            return true;
        }
        return (string)($_SERVER['SERVER_PORT'] ?? '') === '443';
    }

    private static function sendHeaders(): void
    {
        if (PHP_SAPI === 'cli' || headers_sent()) {
            return;
        }

        ini_set('expose_php', '0');
        header_remove('X-Powered-By');
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: camera=(self), microphone=(), geolocation=(self)');
        header('Cross-Origin-Opener-Policy: same-origin-allow-popups');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: 0');

        if (self::isHttps()) {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
    }

    private static function configureSession(): void
    {
        if (PHP_SAPI === 'cli' || session_status() !== PHP_SESSION_NONE) {
            return;
        }

        $secure = Env::bool('SESSION_COOKIE_SECURE', false) || self::isHttps();

        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_samesite', 'Lax');
        ini_set('session.cookie_secure', $secure ? '1' : '0');
        ini_set('session.gc_maxlifetime', (string)Env::int('SESSION_LIFETIME', 28800));
    }

    private static function startOutputBuffer(): void
    {
        if (PHP_SAPI === 'cli' || ob_get_level() > 0) {
            return;
        }
        ob_start([self::class, 'optimizeHtml']);
    }

    /**
     * Reemplaza cualquier ?v=... por la fecha real del archivo local. Así los
     * assets se cachean de forma agresiva pero se invalidan al modificarlos.
     */
    public static function optimizeHtml(string $html): string
    {
        if ($html === '') {
            return $html;
        }

        foreach (headers_list() as $header) {
            if (stripos($header, 'content-type:') === 0 && stripos($header, 'text/html') === false) {
                return $html;
            }
        }

        $root = dirname(__DIR__);

        return preg_replace_callback(
            '/((?:href|src)=["\'])([^"\'?#]+\.(?:css|js))(?:\?[^"\']*)?(["\'])/i',
            static function (array $matches) use ($root): string {
                $path = $matches[2];
                if (preg_match('#^(?:https?:)?//#i', $path) || str_contains($path, '..')) {
                    return $matches[0];
                }
                if ($path === '' || $path[0] === '/' || $path[0] === '\\') {
                    return $matches[0];
                }

                $file = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path);
                if (!is_file($file)) {
                    return $matches[0];
                }

                return $matches[1] . $path . '?v=' . filemtime($file) . $matches[3];
            },
            $html
        ) ?? $html;
    }

    private static function validateSameOrigin(): void
    {
        if (PHP_SAPI === 'cli') {
            return;
        }

        $method = strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET'));
        if (!in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return;
        }

        $source = (string)($_SERVER['HTTP_ORIGIN'] ?? '');
        if ($source === '') {
            $source = (string)($_SERVER['HTTP_REFERER'] ?? '');
        }
        if ($source === '') {
            return;
        }

        $sourceHost = parse_url($source, PHP_URL_HOST);
        $sourceScheme = parse_url($source, PHP_URL_SCHEME);
        $sourcePort = parse_url($source, PHP_URL_PORT);
        $requestHost = parse_url('http://' . (string)($_SERVER['HTTP_HOST'] ?? ''), PHP_URL_HOST);
        $requestPort = parse_url('http://' . (string)($_SERVER['HTTP_HOST'] ?? ''), PHP_URL_PORT);

        if (!is_string($sourceHost) || !is_string($requestHost) || $sourceHost === '' || $requestHost === '') {
            self::rejectOrigin();
            return;
        }

        $sourcePort = $sourcePort ?: ($sourceScheme === 'https' ? 443 : 80);
        $requestPort = $requestPort ?: (self::isHttps() ? 443 : 80);

        if (strcasecmp($sourceHost, $requestHost) !== 0 || (int)$sourcePort !== (int)$requestPort) {
            self::rejectOrigin();
        }
    }

    private static function rejectOrigin(): void
    {
        http_response_code(403);
        if (str_contains(strtolower((string)($_SERVER['HTTP_ACCEPT'] ?? '')), 'json')) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['status' => 'error', 'message' => 'Origen de la solicitud no permitido.'], JSON_UNESCAPED_UNICODE);
        } else {
            header('Content-Type: text/plain; charset=utf-8');
            echo 'Solicitud rechazada: origen no permitido.';
        }
        exit;
    }
}
