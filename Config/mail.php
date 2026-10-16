<?php
/**
 * Configuración de Correo Electrónico (SMTP)
 * Tienda La Caserita
 */
return [
    // Si tienes cuenta de Gmail, ingresa tu correo y tu contraseña de aplicación de 16 letras
    'smtp_host'       => getenv('SMTP_HOST') ?: 'smtp.gmail.com',
    'smtp_port'       => (int)(getenv('SMTP_PORT') ?: 587),
    'smtp_username'   => getenv('SMTP_USERNAME') ?: 'erikcarrasco455@gmail.com',
    'smtp_password'   => getenv('SMTP_PASSWORD') ?: 'dkgkbxgmenizalsi',
    'smtp_encryption' => getenv('SMTP_ENCRYPTION') ?: 'tls', // 'tls' o 'ssl'
    'from_email'      => getenv('SMTP_FROM_EMAIL') ?: 'erikcarrasco455@gmail.com',
    'from_name'       => 'Tienda La Caserita'
];
