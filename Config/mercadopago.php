<?php
require_once __DIR__ . '/../Core/Env.php';
Env::load();

/**
 * Credenciales leídas exclusivamente de variables de entorno.
 * No versionar valores reales en este archivo.
 */
return [
    'public_key' => Env::get('MP_PUBLIC_KEY', ''),
    'access_token' => Env::get('MP_ACCESS_TOKEN', ''),
    'sandbox' => Env::bool('MP_SANDBOX', true),
    'currency_id' => Env::get('MP_CURRENCY_ID', 'PEN'),
    'country_code' => Env::get('MP_COUNTRY_CODE', 'PE'),
    'collector_id' => Env::get('MP_COLLECTOR_ID', ''),
];
