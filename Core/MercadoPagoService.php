<?php
/**
 * Servicio de Integración con Mercado Pago
 * La Caserita
 */
class MercadoPagoService
{
    private $publicKey;
    private $accessToken;
    private $sandbox;
    private $currencyId;

    public function __construct()
    {
        $configFile = __DIR__ . '/../Config/mercadopago.php';
        if (file_exists($configFile)) {
            $config = require $configFile;
            $this->publicKey   = $config['public_key'] ?? '';
            $this->accessToken = $config['access_token'] ?? '';
            $this->sandbox     = !empty($config['sandbox']);
            $this->currencyId  = $config['currency_id'] ?? 'PEN';
        } else {
            $this->publicKey   = 'APP_USR-3fd11a03-526f-4416-89c9-dc7f160ba809';
            $this->accessToken = 'APP_USR-5355592679688774-101520-2fcf0788bb3d34caa467574e3343688e-3766231487';
            $this->sandbox     = true;
            $this->currencyId  = 'PEN';
        }
    }

    public function getPublicKey(): string
    {
        return $this->publicKey;
    }

    public function isSandbox(): bool
    {
        return $this->sandbox;
    }

    /**
     * Construye la URL base actual del proyecto (ej: http://localhost/clonacion%20harol/CALIDAD-DE-SOFTWARE---LA-CASERITA/)
     */
    public function getBaseUrl(): string
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $script = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
        $dir = str_replace('\\', '/', dirname($script));
        if ($dir === '/' || $dir === '.') {
            $dir = '';
        }
        return $protocol . $host . $dir . '/';
    }

    /**
     * Crear Preferencia de Pago en Mercado Pago
     */
    public function crearPreferencia(
        string $codigoPedido,
        array $productos,
        float $costoEnvio,
        array $cliente,
        string $tipoEntrega = 'delivery'
    ): array {
        $items = [];

        foreach ($productos as $p) {
            $titulo = !empty($p['titulo']) ? $p['titulo'] : (!empty($p['titulo_producto']) ? $p['titulo_producto'] : 'Producto');
            $cantidad = isset($p['cantidad']) ? (int)$p['cantidad'] : 1;
            $precio = isset($p['precio']) ? (float)$p['precio'] : (!empty($p['precio_unitario']) ? (float)$p['precio_unitario'] : 0.00);

            if ($cantidad <= 0 || $precio <= 0) {
                continue;
            }

            $items[] = [
                'id'          => (string)($p['id_producto'] ?? '0'),
                'title'       => mb_substr($titulo, 0, 120),
                'description' => 'Compra en La Caserita',
                'quantity'    => $cantidad,
                'unit_price'  => round($precio, 2),
                'currency_id' => $this->currencyId
            ];
        }

        if ($tipoEnvioCosto = (float)$costoEnvio) {
            if ($tipoEnvioCosto > 0 && $tipoEntrega === 'delivery') {
                $items[] = [
                    'id'          => 'envio_delivery',
                    'title'       => 'Costo de Envío a Domicilio',
                    'description' => 'Servicio de entrega local',
                    'quantity'    => 1,
                    'unit_price'  => round($tipoEnvioCosto, 2),
                    'currency_id' => $this->currencyId
                ];
            }
        }

        $nombre = !empty($cliente['nombre']) ? trim($cliente['nombre']) : 'Cliente';
        $apellido = !empty($cliente['apellido']) ? trim($cliente['apellido']) : '';
        $email = !empty($cliente['correo']) ? trim($cliente['correo']) : 'cliente@lacaserita.com';

        $baseUrl = $this->getBaseUrl();

        $payload = [
            'items' => $items,
            'payer' => [
                'name'    => $nombre,
                'surname' => $apellido,
                'email'   => $email,
                'phone'   => [
                    'number' => !empty($cliente['telefono']) ? preg_replace('/\D/', '', $cliente['telefono']) : '999999999'
                ]
            ],
            'back_urls' => [
                'success' => $baseUrl . 'index.php?action=mercadopago_retorno',
                'failure' => $baseUrl . 'index.php?action=mercadopago_retorno',
                'pending' => $baseUrl . 'index.php?action=mercadopago_retorno'
            ],
            'external_reference'   => $codigoPedido,
            'statement_descriptor' => 'LA CASERITA'
        ];

        $ch = curl_init('https://api.mercadopago.com/checkout/preferences');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->accessToken,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            throw new Exception("Error de conexión con Mercado Pago: " . $curlError);
        }

        $data = json_decode($response, true);

        if ($httpCode !== 200 && $httpCode !== 201) {
            $msg = $data['message'] ?? 'No se pudo crear la preferencia de pago en Mercado Pago.';
            throw new Exception("Mercado Pago error ({$httpCode}): " . $msg);
        }

        return [
            'success'            => true,
            'preference_id'      => $data['id'] ?? '',
            'init_point'         => $data['init_point'] ?? '',
            'sandbox_init_point' => $data['sandbox_init_point'] ?? ($data['init_point'] ?? ''),
            'checkout_url'       => $this->sandbox ? ($data['sandbox_init_point'] ?? $data['init_point']) : $data['init_point']
        ];
    }

    /**
     * Consultar estado del pago por ID de Pago
     */
    public function consultarPago(string $paymentId): ?array
    {
        if (empty($paymentId)) {
            return null;
        }

        $ch = curl_init('https://api.mercadopago.com/v1/payments/' . urlencode($paymentId));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->accessToken,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            return json_decode($response, true);
        }

        return null;
    }
}
