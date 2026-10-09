<?php
require_once __DIR__ . '/../Core/dataBase.php';

/**
 * Modelo para Simulación de Celular como Lector Portátil de Códigos de Barras
 * y Búsqueda Flexible de Productos (por Código de Barras, SKU o Nombre) en el POS.
 */
class PosScannerModel {
    private $conn;

    public function __construct() {
        $db = new DataBase();
        $this->conn = $db->getConnection();
    }

    private function scannerDirectory() {
        $directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'tecnoligas-pos-scanner';
        if (!is_dir($directory) && !mkdir($directory, 0700, true) && !is_dir($directory)) {
            throw new RuntimeException('No se pudo crear el directorio temporal del escáner.');
        }
        return $directory;
    }

    private function scannerPaths($token) {
        $name = hash('sha256', (string)$token);
        $directory = $this->scannerDirectory();
        return [
            'lock' => $directory . DIRECTORY_SEPARATOR . $name . '.lock',
            'queue' => $directory . DIRECTORY_SEPARATOR . $name . '.queue',
            'state' => $directory . DIRECTORY_SEPARATOR . $name . '.state',
            'destination' => $directory . DIRECTORY_SEPARATOR . $name . '.destination'
        ];
    }

    private function administrativeTokenPath($token) {
        return $this->scannerDirectory() . DIRECTORY_SEPARATOR
            . hash('sha256', (string)$token) . '.admin-session';
    }

    private function administrativeOwnerPath($userId) {
        return $this->scannerDirectory() . DIRECTORY_SEPARATOR
            . 'admin-user-' . hash('sha256', (string)(int)$userId) . '.session';
    }

    private function readAdministrativeSession($path) {
        if (!is_file($path)) {
            return null;
        }
        $handle = fopen($path, 'r');
        if ($handle === false) {
            throw new RuntimeException('No se pudo leer la sesión temporal del escáner administrativo.');
        }
        try {
            if (!flock($handle, LOCK_SH)) {
                throw new RuntimeException('No se pudo bloquear la sesión temporal del escáner administrativo.');
            }
            $contents = stream_get_contents($handle);
            if ($contents === false) {
                throw new RuntimeException('No se pudo leer la sesión temporal del escáner administrativo.');
            }
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
        $session = json_decode($contents, true);
        if (!is_array($session) || empty($session['token']) || empty($session['user_id']) || empty($session['expires_at'])) {
            throw new RuntimeException('La sesión temporal del escáner administrativo no es válida.');
        }
        return $session;
    }

    private function writeAdministrativeSession($path, array $session) {
        $contents = json_encode($session);
        if ($contents === false) {
            throw new RuntimeException('No se pudo guardar la sesión temporal del escáner administrativo.');
        }
        $handle = fopen($path, 'c+');
        if ($handle === false) {
            throw new RuntimeException('No se pudo guardar la sesión temporal del escáner administrativo.');
        }
        try {
            if (!flock($handle, LOCK_EX) || !ftruncate($handle, 0) || rewind($handle) === false
                || fwrite($handle, $contents) !== strlen($contents) || !fflush($handle)) {
                throw new RuntimeException('No se pudo guardar la sesión temporal del escáner administrativo.');
            }
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }

    public function getOrCreateAdministrativeScannerToken($userId) {
        $userId = (int)$userId;
        if ($userId <= 0) {
            throw new InvalidArgumentException('El usuario administrativo no es válido.');
        }

        $ownerPath = $this->administrativeOwnerPath($userId);
        $lock = fopen($ownerPath . '.lock', 'c');
        if ($lock === false) {
            throw new RuntimeException('No se pudo preparar la sesión del escáner administrativo.');
        }

        try {
            if (!flock($lock, LOCK_EX)) {
                throw new RuntimeException('No se pudo bloquear la sesión del escáner administrativo.');
            }

            $ownerSession = $this->readAdministrativeSession($ownerPath);
            if ($ownerSession && (int)$ownerSession['user_id'] === $userId) {
                $tokenSessionPath = $this->administrativeTokenPath($ownerSession['token']);
                $tokenSession = $this->readAdministrativeSession($tokenSessionPath);
                if ($tokenSession
                    && (int)$tokenSession['user_id'] === $userId
                    && (int)$tokenSession['expires_at'] > time()) {
                    $tokenSession['expires_at'] = time() + 43200;
                    $this->writeAdministrativeSession($tokenSessionPath, $tokenSession);
                    $ownerSession['expires_at'] = $tokenSession['expires_at'];
                    $this->writeAdministrativeSession($ownerPath, $ownerSession);
                    return (string)$ownerSession['token'];
                }
            }

            $token = bin2hex(random_bytes(32));
            $session = [
                'token' => $token,
                'user_id' => $userId,
                'expires_at' => time() + 43200
            ];
            $this->writeAdministrativeSession($this->administrativeTokenPath($token), $session);
            $this->writeAdministrativeSession($ownerPath, $session);
            $this->setScannerEnabled($token, false);
            $this->setScannerDestination($token, 'pos');
            return $token;
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    public function isAdministrativeScannerToken($token, $userId = null) {
        $token = trim((string)$token);
        if (!preg_match('/\A[a-f0-9]{64}\z/', $token)) {
            return false;
        }
        $session = $this->readAdministrativeSession($this->administrativeTokenPath($token));
        if (!$session || (int)$session['expires_at'] <= time()) {
            return false;
        }
        return $userId === null || (int)$session['user_id'] === (int)$userId;
    }

    public function touchAdministrativeScannerToken($token) {
        $token = trim((string)$token);
        if (!$this->isAdministrativeScannerToken($token)) {
            return false;
        }
        $path = $this->administrativeTokenPath($token);
        $session = $this->readAdministrativeSession($path);
        if (!$session || (int)$session['expires_at'] <= time()) {
            return false;
        }
        $session['expires_at'] = time() + 43200;
        $this->writeAdministrativeSession($path, $session);
        return true;
    }

    private function readScannerState($statePath) {
        if (!is_file($statePath)) {
            return true;
        }
        $state = file_get_contents($statePath);
        if ($state === false) {
            throw new RuntimeException('No se pudo leer el estado temporal del escáner.');
        }
        $state = trim($state);
        if ($state !== '0' && $state !== '1') {
            throw new RuntimeException('El estado temporal del escáner no es válido.');
        }
        return $state === '1';
    }

    public function isScannerEnabled($token) {
        $paths = $this->scannerPaths($token);
        $lock = fopen($paths['lock'], 'c');
        if ($lock === false) {
            throw new RuntimeException('No se pudo consultar el estado temporal del escáner.');
        }

        try {
            if (!flock($lock, LOCK_EX)) {
                throw new RuntimeException('No se pudo bloquear el estado del escáner.');
            }
            return $this->readScannerState($paths['state']);
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    public function setScannerEnabled($token, $enabled) {
        $paths = $this->scannerPaths($token);
        $lock = fopen($paths['lock'], 'c');
        if ($lock === false) {
            throw new RuntimeException('No se pudo actualizar el estado temporal del escáner.');
        }

        try {
            if (!flock($lock, LOCK_EX)) {
                throw new RuntimeException('No se pudo bloquear el estado del escáner.');
            }
            if (file_put_contents($paths['state'], $enabled ? '1' : '0', LOCK_EX) === false) {
                throw new RuntimeException('No se pudo guardar el estado del escáner.');
            }
            if (!$enabled && is_file($paths['queue']) && !unlink($paths['queue'])) {
                throw new RuntimeException('No se pudieron descartar los escaneos pendientes.');
            }
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    public function setScannerDestination($token, $destination) {
        if (!in_array($destination, ['pos', 'campo'], true)) {
            throw new InvalidArgumentException('El destino del escáner no es válido.');
        }

        $paths = $this->scannerPaths($token);
        $lock = fopen($paths['lock'], 'c');
        if ($lock === false) {
            throw new RuntimeException('No se pudo actualizar el destino del escáner.');
        }

        try {
            if (!flock($lock, LOCK_EX)) {
                throw new RuntimeException('No se pudo bloquear el destino del escáner.');
            }
            if (file_put_contents($paths['destination'], $destination, LOCK_EX) === false) {
                throw new RuntimeException('No se pudo guardar el destino del escáner.');
            }
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    public function getScannerDestination($token) {
        $paths = $this->scannerPaths($token);
        $lock = fopen($paths['lock'], 'c');
        if ($lock === false) {
            throw new RuntimeException('No se pudo consultar el destino del escáner.');
        }

        try {
            if (!flock($lock, LOCK_EX)) {
                throw new RuntimeException('No se pudo bloquear el destino del escáner.');
            }
            if (!is_file($paths['destination'])) {
                return 'pos';
            }
            $destination = file_get_contents($paths['destination']);
            if ($destination === false || !in_array(trim($destination), ['pos', 'campo'], true)) {
                throw new RuntimeException('El destino temporal del escáner no es válido.');
            }
            return trim($destination);
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    private function enqueueScan($token, $codigo, $dispositivo) {
        $paths = $this->scannerPaths($token);
        $lock = fopen($paths['lock'], 'c');
        if ($lock === false) {
            throw new RuntimeException('No se pudo abrir la cola temporal del escáner.');
        }

        try {
            if (!flock($lock, LOCK_EX)) {
                throw new RuntimeException('No se pudo bloquear la cola temporal del escáner.');
            }
            if (!$this->readScannerState($paths['state'])) {
                return false;
            }

            $scan = [
                'id_escaneo' => uniqid('', true),
                'token_sesion' => $token,
                'codigo_escaneado' => $codigo,
                'dispositivo' => $dispositivo
            ];
            $encodedScan = json_encode($scan);
            if ($encodedScan === false || file_put_contents($paths['queue'], $encodedScan . PHP_EOL, FILE_APPEND | LOCK_EX) === false) {
                throw new RuntimeException('No se pudo escribir el escaneo en la cola temporal.');
            }
            return true;
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    /**
     * Busca y resuelve un producto de manera inteligente:
     * 1. Por código de barras exacto (escaneado por la cámara del celular)
     * 2. Por código SKU interno (ej: 'ARR-001')
     * 3. Por ID numérico directo
     * 4. Por coincidencia en el nombre / título (ej: 'arroz', 'leche', 'primor')
     */
    public function resolverProducto($criterio) {
        $criterio = trim($criterio ?? '');
        if ($criterio === '') {
            return null;
        }

        // 1. Intento por código de barras o SKU exacto
        $query = "SELECT p.*, COALESCE(i.stock_actual, p.stock, 0) as stock_disponible 
                  FROM producto p 
                  LEFT JOIN inventario i ON p.id_producto = i.id_producto 
                  WHERE p.activo = 1 
                    AND (p.codigo_barras = :crit1 OR p.codigo = :crit2)
                  LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':crit1', $criterio);
        $stmt->bindParam(':crit2', $criterio);
        $stmt->execute();
        $prod = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($prod) {
            $prod['metodo_coincidencia'] = 'codigo_barras_o_sku';
            return $prod;
        }

        // 2. Si es numérico, probar por ID de producto
        if (is_numeric($criterio)) {
            $queryId = "SELECT p.*, COALESCE(i.stock_actual, p.stock, 0) as stock_disponible 
                        FROM producto p 
                        LEFT JOIN inventario i ON p.id_producto = i.id_producto 
                        WHERE p.activo = 1 AND p.id_producto = :id LIMIT 1";
            $stmtId = $this->conn->prepare($queryId);
            $idNum = (int)$criterio;
            $stmtId->bindParam(':id', $idNum, PDO::PARAM_INT);
            $stmtId->execute();
            $prodId = $stmtId->fetch(PDO::FETCH_ASSOC);
            if ($prodId) {
                $prodId['metodo_coincidencia'] = 'id_producto';
                return $prodId;
            }
        }

        // 3. Intento flexible por Nombre / Título
        $like = '%' . $criterio . '%';
        $queryNombre = "SELECT p.*, COALESCE(i.stock_actual, p.stock, 0) as stock_disponible 
                        FROM producto p 
                        LEFT JOIN inventario i ON p.id_producto = i.id_producto 
                        WHERE p.activo = 1 
                          AND (p.nombre LIKE :like1 OR p.titulo LIKE :like2 OR p.descripcion LIKE :like3)
                        ORDER BY p.nombre ASC 
                        LIMIT 1";
        $stmtNombre = $this->conn->prepare($queryNombre);
        $stmtNombre->bindParam(':like1', $like);
        $stmtNombre->bindParam(':like2', $like);
        $stmtNombre->bindParam(':like3', $like);
        $stmtNombre->execute();
        $prodNombre = $stmtNombre->fetch(PDO::FETCH_ASSOC);

        if ($prodNombre) {
            $prodNombre['metodo_coincidencia'] = 'nombre';
            return $prodNombre;
        }

        return null;
    }

    public function registrarEscaneoRemoto($token, $codigo, $dispositivo = 'Celular Móvil') {
        if ($this->isAdministrativeScannerToken($token)) {
            if (!$this->enqueueScan($token, $codigo, $dispositivo)) {
                return ['ok' => false, 'mensaje' => 'El escáner está desactivado.'];
            }
            $this->touchAdministrativeScannerToken($token);
            return ['ok' => true, 'codigo' => $codigo, 'mensaje' => 'Escaneo enviado a la PC'];
        }

        $sesion = $this->conn->prepare("SELECT 1 FROM sesion_caja WHERE token_emparejamiento = :token AND estado = 'abierta' LIMIT 1");
        $sesion->bindParam(':token', $token);
        $sesion->execute();
        if (!$sesion->fetchColumn()) {
            return ['ok' => false, 'mensaje' => 'La caja está cerrada o la sesión ya no es válida.'];
        }

        if (!$this->enqueueScan($token, $codigo, $dispositivo)) {
            return ['ok' => false, 'mensaje' => 'El escáner está desactivado en la caja.'];
        }

        return ['ok' => true, 'codigo' => $codigo, 'mensaje' => 'Escaneo enviado a la PC'];
    }

    /**
     * Registra un escaneo desde el celular emparejado:
     * - Resuelve el producto (por código o nombre)
     * - Registra el evento en una cola temporal aislada por token
     * - Actualiza el carrito en la sesión de caja (sesion_caja)
     */
    public function procesarEscaneoMovil($token, $criterio, $cantidad = 1, $dispositivo = 'Celular Móvil') {
        // Verificar que la sesión de caja exista y esté abierta
        $querySes = "SELECT * FROM sesion_caja WHERE token_emparejamiento = :token LIMIT 1";
        $stmtSes = $this->conn->prepare($querySes);
        $stmtSes->bindParam(':token', $token);
        $stmtSes->execute();
        $sesion = $stmtSes->fetch(PDO::FETCH_ASSOC);

        if (!$sesion) {
            // Si no hay sesión de caja abierta, permitir el escaneo para Inventario y Compras
            if (!$this->enqueueScan($token, $criterio, $dispositivo)) {
                return ['ok' => false, 'mensaje' => 'El escáner está desactivado.'];
            }

            return [
                'ok'      => true,
                'codigo'  => $criterio,
                'mensaje' => "Código '{$criterio}' enviado al sistema."
            ];
        }

        // Resolver producto
        $producto = $this->resolverProducto($criterio);
        if (!$producto) {
            // Guardar en cola de escaneo para que Inventario o Compras puedan capturar el código leído
            if (!$this->enqueueScan($token, $criterio, $dispositivo)) {
                return ['ok' => false, 'mensaje' => 'El escáner está desactivado.'];
            }

            return [
                'ok'      => true,
                'no_registrado' => true,
                'codigo'  => $criterio,
                'mensaje' => "Código leído: '{$criterio}' (Listo para registrar en Inventario o Compra)."
            ];
        }

        $stockDisponible = (int)($producto['stock_disponible'] ?? 0);
        $idProd = (int)$producto['id_producto'];

        if ($stockDisponible <= 0) {
            // Guardar en cola para que Inventario o Compras puedan usarlo para reabastecer
            if (!$this->enqueueScan($token, $criterio, $dispositivo)) {
                return ['ok' => false, 'mensaje' => 'El escáner está desactivado.'];
            }

            return [
                'ok'      => true,
                'codigo'  => $criterio,
                'nombre'  => $producto['nombre'],
                'mensaje' => "Código transmitido a la PC: {$criterio}"
            ];
        }

        // Agregar o incrementar en el carrito de sesion_caja
        $items = json_decode($sesion['items_json'] ?? '[]', true);
        if (!is_array($items)) {
            $items = [];
        }

        $idProd = (int)$producto['id_producto'];
        $encontrado = false;
        foreach ($items as &$item) {
            if ($item['id_producto'] == $idProd) {
                if ($item['cantidad'] + $cantidad > $stockDisponible) {
                    return [
                        'ok'      => false,
                        'mensaje' => "Stock insuficiente en almacén (Disponible: {$stockDisponible})."
                    ];
                }
                $item['cantidad'] += $cantidad;
                $item['subtotal'] = round($item['cantidad'] * $item['precio'], 2);
                $encontrado = true;
                break;
            }
        }
        unset($item);

        if (!$encontrado) {
            $precio = (float)$producto['precio'];
            $items[] = [
                'id_producto'   => $idProd,
                'codigo'        => $producto['codigo_barras'] ?: $producto['codigo'],
                'nombre'        => $producto['nombre'],
                'precio'        => $precio,
                'cantidad'      => $cantidad,
                'subtotal'      => round($precio * $cantidad, 2),
                'imagen_url'    => $producto['imagen_url'] ?? $producto['imagen'] ?? ''
            ];
        }

        // Recalcular total general
        $totalGeneral = 0;
        foreach ($items as $it) {
            $totalGeneral += $it['subtotal'];
        }
        $totalGeneral = round($totalGeneral, 2);

        // Actualizar sesión de caja
        $itemsJson = json_encode($items);
        $updateSes = "UPDATE sesion_caja 
                      SET items_json = :items, total_venta = :total, estado_conexion = 'conectado' 
                      WHERE token_emparejamiento = :token";
        $stmtUp = $this->conn->prepare($updateSes);
        $stmtUp->bindParam(':items', $itemsJson);
        $stmtUp->bindParam(':total', $totalGeneral);
        $stmtUp->bindParam(':token', $token);
        $stmtUp->execute();

        $this->enqueueScan($token, $criterio, $dispositivo);

        return [
            'ok'               => true,
            'mensaje'          => "¡Agregado exitosamente!",
            'producto'         => [
                'id_producto'   => $idProd,
                'nombre'        => $producto['nombre'],
                'precio'        => (float)$producto['precio'],
                'codigo_barras' => $producto['codigo_barras'],
                'stock'         => $stockDisponible
            ],
            'items'            => $items,
            'total_venta'      => $totalGeneral,
            'dispositivo'      => $dispositivo
        ];
    }

    /**
     * Consulta escaneos nuevos que la pantalla de la caja en la computadora aún no ha visto
     */
    public function obtenerEscaneosPendientes($token) {
        $paths = $this->scannerPaths($token);
        $lock = fopen($paths['lock'], 'c');
        if ($lock === false) {
            throw new RuntimeException('No se pudo leer la cola temporal del escáner.');
        }

        try {
            if (!flock($lock, LOCK_EX)) {
                throw new RuntimeException('No se pudo bloquear la cola temporal del escáner.');
            }
            if (!$this->readScannerState($paths['state']) || !is_file($paths['queue'])) {
                return [];
            }

            $contents = file_get_contents($paths['queue']);
            if ($contents === false) {
                throw new RuntimeException('No se pudo leer la cola temporal del escáner.');
            }
            $escaneos = [];
            foreach (preg_split('/\r\n|\r|\n/', trim($contents)) as $line) {
                if ($line === '') {
                    continue;
                }
                $scan = json_decode($line, true);
                if (!is_array($scan) || ($scan['token_sesion'] ?? '') !== $token) {
                    throw new RuntimeException('La cola temporal contiene un escaneo inválido.');
                }
                $escaneos[] = $scan;
            }
            if (!unlink($paths['queue'])) {
                throw new RuntimeException('No se pudo limpiar la cola temporal del escáner.');
            }
            return $escaneos;
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }
}
?>
