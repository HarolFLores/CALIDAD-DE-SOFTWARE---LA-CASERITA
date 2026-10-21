<?php
require_once __DIR__ . '/../Core/Env.php';
Env::load();

/**
 * Configuración SMTP. Las credenciales viven en .env (nunca en Git).
 */
return [
    'smtp_host' => Env::get('SMTP_HOST', 'smtp.gmail.com'),
    'smtp_port' => Env::int('SMTP_PORT', 587),
    'smtp_username' => Env::get('SMTP_USERNAME', ''),
    'smtp_password' => Env::get('SMTP_PASSWORD', ''),
    'smtp_encryption' => Env::get('SMTP_ENCRYPTION', 'tls'),
    'from_email' => Env::get('SMTP_FROM_EMAIL', ''),
    'from_name' => Env::get('SMTP_FROM_NAME', 'Tienda La Caserita'),
];
