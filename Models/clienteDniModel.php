<?php
require_once __DIR__ . '/../Core/dataBase.php';

/**
 * Modelo para Consulta de DNI y Clientes en Caja POS y Ventas
 * Soporta API externa de RENIEC / DNI Perú con timeout seguro,
 * caché en base de datos local y clientes genéricos (00000000 / 99999999).
 */
class ClienteDniModel {
    private $conn;

    public function __construct() {
        $db = new DataBase();
        $this->conn = $db->getConnection();
    }

    /**
     * DNI Genéricos aceptados para ventas rápidas en mostrador
     */
    public function obtenerClienteGenerico($tipo = '00000000') {
        return [
            'ok'              => true,
            'dni'             => $tipo === '99999999' ? '99999999' : '00000000',
            'nombre_completo' => 'Público General',
            'nombres'         => 'Público',
            'apellido_paterno'=> 'General',
            'apellido_materno'=> '',
            'tipo_cliente'    => 'generico',
            'origen'          => 'sistema'
        ];
    }

    /**
     * Consulta un DNI:
     * 1. Si es genérico (00000000 / 99999999 / vacío), retorna Público General.
     * 2. Si ya existe en la base de datos local, lo devuelve de inmediato (súper rápido).
     * 3. Si no existe, consulta API REST externa de DNI con timeout de 3 segundos.
     */
    public function consultarDni($dni) {
        return $this->consultarDocumento($dni);
    }

    public function consultarDocumento($documento) {
        $documento = trim((string)$documento);
        if (in_array($documento, ['', '00000000', '99999999', '0'], true)) {
            return $this->obtenerClienteGenerico($documento);
        }

        if (strlen($documento) !== 8 || !ctype_digit($documento)) {
            return ['ok' => false, 'mensaje' => 'Ingresa un DNI de 8 dígitos.'];
        }

        $clienteLocal = $this->buscarEnBaseDatosLocal($documento);
        if ($clienteLocal) {
            return [
                'ok' => true,
                'dni' => $documento,
                'nombre_completo' => $clienteLocal['nombre_completo'],
                'nombres' => $clienteLocal['nombres'],
                'apellido_paterno' => $clienteLocal['apellido_paterno'] ?? '',
                'apellido_materno' => $clienteLocal['apellido_materno'] ?? '',
                'telefono' => $clienteLocal['telefono'] ?? '',
                'tipo_cliente' => 'registrado',
                'origen' => 'base_datos_local'
            ];
        }

        $resultadoApi = $this->consultarApiDocumento($documento);
        if ($resultadoApi['ok']) {
            return $resultadoApi;
        }

        return [
            'ok' => false,
            'dni' => $documento,
            'mensaje' => $resultadoApi['mensaje']
        ];
    }

    /**
     * Busca el DNI en la base de datos de usuarios o pedidos previos
     */
    private function buscarEnBaseDatosLocal($dni) {
        // En tabla usuario
        $query = "SELECT id_usuario, nombre, apellido, telefono, dni 
                  FROM usuario 
                  WHERE dni = :dni LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':dni', $dni);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $nombreComp = trim($user['nombre'] . ' ' . ($user['apellido'] ?? ''));
            return [
                'nombre_completo' => $nombreComp,
                'nombres'         => $user['nombre'],
                'apellido_paterno'=> $user['apellido'] ?? '',
                'telefono'        => $user['telefono'] ?? '',
                'direccion'       => ''
            ];
        }

        // En histórico de pedidos
        $queryPed = "SELECT cliente_nombre 
                     FROM pedido 
                     WHERE cliente_dni = :dni 
                       AND cliente_nombre IS NOT NULL 
                       AND cliente_nombre != 'Público General' 
                     ORDER BY id_pedido DESC LIMIT 1";
        $stmtPed = $this->conn->prepare($queryPed);
        $stmtPed->bindParam(':dni', $dni);
        $stmtPed->execute();
        $ped = $stmtPed->fetch(PDO::FETCH_ASSOC);

        if ($ped && !empty($ped['cliente_nombre'])) {
            return [
                'nombre_completo' => $ped['cliente_nombre'],
                'nombres'         => $ped['cliente_nombre'],
                'apellido_paterno'=> '',
                'telefono'        => '',
                'direccion'       => ''
            ];
        }

        return null;
    }

    private function consultarApiDocumento($documento) {
        if (!function_exists('curl_init')) {
            return ['ok' => false, 'mensaje' => 'La consulta externa requiere habilitar cURL en PHP. Puedes ingresar el nombre manualmente.'];
        }

        $token = '';
        $configFile = __DIR__ . '/../Config/apis.php';
        if (is_file($configFile)) {
            $configApis = require $configFile;
            $token = trim((string)($configApis['reniec']['token'] ?? ''));
        }
        if ($token === '') {
            $token = trim((string)(getenv('APIS_NET_PE_TOKEN') ?: ''));
        }

        if ($token !== '') {
            $resultado = $this->solicitarApiPeru($documento, $token);
            if ($resultado !== null) {
                return $resultado;
            }
        }

        $resultado = $this->solicitarApiDeColector($documento);
        if ($resultado !== null) {
            return $resultado;
        }

        return [
            'ok' => false,
            'dni' => $documento,
            'mensaje' => 'No se encontró el DNI en RENIEC. Verifica el número o ingresa el nombre manualmente.'
        ];
    }

    private function solicitarApiPeru($documento, $token) {
        $curl = curl_init('https://apiperu.dev/api/dni');
        curl_setopt_array($curl, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode(['dni' => $documento]),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 6,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token,
                'User-Agent: LaCaseritaPOS/1.0'
            ]
        ]);
        $response = curl_exec($curl);
        $httpCode = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if (!$response || $httpCode < 200 || $httpCode >= 300) {
            return null;
        }

        $json = json_decode($response, true);
        if (!is_array($json) || empty($json['success']) || !isset($json['data']) || !is_array($json['data'])) {
            return null;
        }

        return $this->formatearResultadoDni($documento, $json['data'], 'apiperu');
    }

    private function solicitarApiDeColector($documento) {
        $url = 'https://api.decolector.com/api/v1/reniec/dni?numero=' . urlencode($documento);
        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_HTTPHEADER => ['Accept: application/json', 'User-Agent: LaCaseritaPOS/1.0']
        ]);
        $response = curl_exec($curl);
        $httpCode = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if (!$response || $httpCode < 200 || $httpCode >= 300) {
            return null;
        }

        $data = json_decode($response, true);
        if (!is_array($data)) {
            return null;
        }
        if (isset($data['data']) && is_array($data['data'])) {
            $data = $data['data'];
        } elseif (isset($data['resultado']) && is_array($data['resultado'])) {
            $data = $data['resultado'];
        }

        return $this->formatearResultadoDni($documento, $data, 'decolector');
    }

    private function formatearResultadoDni($documento, $data, $origen) {
        $nombres = trim((string)($data['nombres'] ?? ''));
        $paterno = trim((string)($data['apellidoPaterno'] ?? $data['apellido_paterno'] ?? ''));
        $materno = trim((string)($data['apellidoMaterno'] ?? $data['apellido_materno'] ?? ''));
        $nombre = trim("{$nombres} {$paterno} {$materno}");
        if ($nombre === '') {
            $nombre = trim((string)($data['nombre_completo'] ?? $data['nombreCompleto'] ?? $data['full_name'] ?? ''));
        }
        if ($nombre === '') {
            return null;
        }

        return [
            'ok' => true,
            'dni' => $documento,
            'nombre_completo' => $nombre,
            'nombres' => $nombres,
            'apellido_paterno' => $paterno,
            'apellido_materno' => $materno,
            'direccion' => trim((string)($data['direccion'] ?? '')),
            'tipo_cliente' => 'reniec',
            'origen' => $origen
        ];
    }
}
?>
