<?php
/**
 * Router para `php -S` (desarrollo y Wasmer). Apache usa .htaccess.
 */
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$path = rawurldecode($path);

$blocked = '#/(?:\.git|\.agents|Config|Core|Controllers|Models|Views|database|scratch|backups)(?:/|$)#i';
$blockedFiles = '#(?:^|/)\.|\.(?:env|sql|log|zip|token|timestamp|bak|ini|md|ya?ml|toml)$|(?:^|/)composer\.(?:json|lock)$#i';
if (preg_match($blocked, $path) || preg_match($blockedFiles, $path)) {
    http_response_code(404);
    exit('Not found');
}

$root = realpath(__DIR__);
$file = realpath($root . DIRECTORY_SEPARATOR . ltrim(str_replace('/', DIRECTORY_SEPARATOR, $path), DIRECTORY_SEPARATOR));

if ($file !== false && str_starts_with($file, $root . DIRECTORY_SEPARATOR) && is_file($file)) {
    $relative = str_replace('\\', '/', substr($file, strlen($root)));
    $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));

    if ($extension === 'php' && $relative !== '/index.php') {
        http_response_code(404);
        exit('Not found');
    }

    $mimeTypes = [
        'css' => 'text/css; charset=utf-8',
        'js' => 'application/javascript; charset=utf-8',
        'mjs' => 'application/javascript; charset=utf-8',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
        'svg' => 'image/svg+xml',
        'ico' => 'image/x-icon',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf' => 'font/ttf',
        'eot' => 'application/vnd.ms-fontobject',
    ];

    if (isset($mimeTypes[$extension])) {
        header('Content-Type: ' . $mimeTypes[$extension]);
        header('Content-Length: ' . (string)filesize($file));
        header('Cache-Control: public, max-age=604800, stale-while-revalidate=86400');
        header('X-Content-Type-Options: nosniff');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($file)) . ' GMT');
        readfile($file);
        exit;
    }

    return false;
}

require $root . DIRECTORY_SEPARATOR . 'index.php';
