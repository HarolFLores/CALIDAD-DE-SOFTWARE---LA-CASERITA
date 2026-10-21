<?php
require_once __DIR__ . '/../Core/Env.php';
Env::load();

return [
    'reniec' => [
        'proveedor' => Env::get('RENIEC_PROVEEDOR', 'apiperu.dev'),
        'token' => Env::get('RENIEC_TOKEN', ''),
        'url' => Env::get('RENIEC_URL', 'https://apiperu.dev/api/dni'),
    ],
    'google' => [
        'client_id' => Env::get('GOOGLE_CLIENT_ID', ''),
    ],
    'mercadopago' => require __DIR__ . '/mercadopago.php',
];
