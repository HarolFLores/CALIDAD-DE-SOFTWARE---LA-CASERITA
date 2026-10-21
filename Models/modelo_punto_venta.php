<?php
include_once 'Core/dataBase.php';

class ModeloPuntoVenta {
    private $conn;

    public function __construct() {
        $dataBase = new DataBase();
        $this->conn = $dataBase->getConnection();
    }

    public function buscarProductosManual($term) {
        $termClean = trim($term);
        if ($termClean === '') {
            return [];
        }

        // Dividir en palabras para permitir búsquedas compuestas ("arroz costeño", "leche gloria", etc.)
        $words = preg_split('/\s+/', $termClean, -1, PREG_SPLIT_NO_EMPTY);
        $params = [];
        $whereParts = [];

        $pIndex = 0;
        foreach ($words as $word) {
            $p1 = ":p_{$pIndex}_1";
            $p2 = ":p_{$pIndex}_2";
            $p3 = ":p_{$pIndex}_3";
            $p4 = ":p_{$pIndex}_4";
            $p5 = ":p_{$pIndex}_5";
            $p6 = ":p_{$pIndex}_6";
            $pIndex++;

            $whereParts[] = "(p.nombre LIKE {$p1} OR p.titulo LIKE {$p6} OR p.codigo LIKE {$p2} OR p.codigo_barras LIKE {$p3} OR p.marca LIKE {$p4} OR p.descripcion LIKE {$p5})";
            $likeVal = '%' . $word . '%';
            $params[$p1] = $likeVal;
            $params[$p2] = $likeVal;
            $params[$p3] = $likeVal;
            $params[$p4] = $likeVal;
            $params[$p5] = $likeVal;
            $params[$p6] = $likeVal;
        }

        $whereSql = !empty($whereParts) ? implode(' AND ', $whereParts) : '1=1';

        $query = "SELECT p.*, COALESCE(i.stock_actual, p.stock, 0) as stock 
                  FROM producto p 
                  LEFT JOIN inventario i ON p.id_producto = i.id_producto 
                  WHERE ({$whereSql} 
                     OR p.codigo_barras = :exactBarcode 
                     OR p.codigo = :exactCode 
                     OR CAST(p.id_producto AS CHAR) = :exactId)
                    AND (p.activo = 1 OR p.activo IS NULL)
                                        ORDER BY CASE
                      WHEN p.codigo_barras = :exactBarcode2 THEN 1
                      WHEN p.codigo = :exactCode2 THEN 2
                                            WHEN p.id_producto = :exactProductId THEN 3
                                            WHEN p.nombre LIKE :startName OR p.titulo LIKE :startTitle THEN 4
                                            ELSE 5
                    END,
                    p.nombre ASC 
                  LIMIT 25";

        $params[':exactBarcode'] = $termClean;
        $params[':exactBarcode2'] = $termClean;
        $params[':exactCode'] = $termClean;
        $params[':exactCode2'] = $termClean;
        $params[':exactId'] = $termClean;
        $params[':exactProductId'] = ctype_digit($termClean) ? (int)$termClean : 0;
        $params[':startName'] = $termClean . '%';
        $params[':startTitle'] = $termClean . '%';

        $stmt = $this->conn->prepare($query);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarProductosCatalogo($limite = 200) {
        $stmt = $this->conn->prepare("SELECT p.*, COALESCE(i.stock_actual, p.stock, 0) AS stock, COALESCE(i.stock_minimo, 5) AS stock_minimo,
                c.nombre AS categoria_nombre, COALESCE(gm.nombre, 'Sin categoría') AS grupo_nombre
            FROM producto p
            LEFT JOIN inventario i ON p.id_producto = i.id_producto
            LEFT JOIN categoria c ON p.id_categoria = c.id_categoria
            LEFT JOIN grupo_momento gm ON p.id_grupo = gm.id_grupo
            WHERE p.activo = 1 OR p.activo IS NULL
            ORDER BY (COALESCE(i.stock_actual, p.stock, 0) <= COALESCE(i.stock_minimo, 5)) DESC,
                     COALESCE(NULLIF(p.nombre, ''), p.titulo) ASC
            LIMIT :limite");
        $stmt->bindValue(':limite', max(1, min(500, (int)$limite)), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerOCrearSesionCaja($idUsuario, $cajaId = 'CAJA-01') {
        $query = "SELECT * FROM sesion_caja 
                  WHERE id_usuario = :id_usuario 
                  AND estado_conexion != 'cerrado' 
                  ORDER BY id_sesion DESC LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_usuario', $idUsuario, PDO::PARAM_INT);
        $stmt->execute();
        $sesion = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($sesion) {
            return $sesion;
        }

        $token = bin2hex(random_bytes(16));
        $queryInsert = "INSERT INTO sesion_caja (caja_id, id_usuario, token_emparejamiento, estado_conexion, items_json) 
                        VALUES (:caja_id, :id_usuario, :token, 'esperando', '[]')";
        $stmtInsert = $this->conn->prepare($queryInsert);
        $stmtInsert->bindParam(':caja_id', $cajaId);
        $stmtInsert->bindParam(':id_usuario', $idUsuario, PDO::PARAM_INT);
        $stmtInsert->bindParam(':token', $token);
        $stmtInsert->execute();

        return $this->obtenerSesionPorToken($token);
    }

    public function obtenerSesionPorToken($token) {
        $query = "SELECT s.*, u.nombre, u.apellido, u.correo 
                  FROM sesion_caja s 
                  JOIN usuario u ON s.id_usuario = u.id_usuario 
                  WHERE s.token_emparejamiento = :token";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':token', $token);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function obtenerHistorialVentasCajero($idUsuario, $limite = 100) {
        $sql = "SELECT p.id_pedido, p.codigo_pedido, p.cliente_dni, p.cliente_nombre, p.subtotal, p.total, p.monto_recibido, p.vuelto, p.metodo_pago, p.fecha,
                       u.nombre AS cajero_nombre, u.apellido AS cajero_apellido,
                       TRIM(CONCAT(COALESCE(u.nombre, ''), ' ', COALESCE(u.apellido, ''))) AS cajero_nombre_completo
                FROM pedido p
                LEFT JOIN usuario u ON COALESCE(p.id_cajero, p.id_usuario) = u.id_usuario
                WHERE p.canal = 'POS'
                  AND (p.id_cajero = :id_cajero OR (p.id_cajero IS NULL AND p.id_usuario = :id_usuario))
                ORDER BY p.id_pedido DESC
                LIMIT :limite";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id_cajero', (int)$idUsuario, PDO::PARAM_INT);
        $stmt->bindValue(':id_usuario', (int)$idUsuario, PDO::PARAM_INT);
        $stmt->bindValue(':limite', (int)$limite, PDO::PARAM_INT);
        $stmt->execute();
        $ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($ventas)) {
            $stmtDet = $this->conn->prepare("SELECT dp.id_producto, dp.cantidad, dp.precio_unitario, dp.subtotal, COALESCE(NULLIF(pr.nombre, ''), pr.titulo) AS producto_nombre FROM detalle_pedido dp JOIN producto pr ON dp.id_producto = pr.id_producto WHERE dp.id_pedido = :id_ped");
            foreach ($ventas as &$v) {
                $stmtDet->bindValue(':id_ped', (int)$v['id_pedido'], PDO::PARAM_INT);
                $stmtDet->execute();
                $v['items'] = $stmtDet->fetchAll(PDO::FETCH_ASSOC);
            }
            unset($v);
        }
        return $ventas;
    }

    public function validarEmparejamiento($token, $dispositivoInfo = 'Escáner Móvil Android') {
        $sesion = $this->obtenerSesionPorToken($token);
        if (!$sesion || ($sesion['estado'] ?? '') !== 'abierta') {
            return false;
        }

        $query = "UPDATE sesion_caja 
                  SET estado_conexion = 'conectado', dispositivo_info = :info 
                  WHERE token_emparejamiento = :token";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':info', $dispositivoInfo);
        $stmt->bindParam(':token', $token);
        $stmt->execute();

        return true;
    }

    public function desconectarEmparejamiento($token) {
        $query = "UPDATE sesion_caja
                  SET estado_conexion = 'esperando'
                  WHERE token_emparejamiento = :token AND estado = 'abierta'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':token', $token);
        $stmt->execute();
    }

    public function agregarProductoPorCodigo($token, $codigoOId, $esIdProducto = false) {
        $sesion = $this->obtenerSesionPorToken($token);
        if (!$sesion) {
            return ['status' => 'error', 'message' => 'Sesión de caja no válida o no encontrada'];
        }
        if (($sesion['estado'] ?? '') !== 'abierta') {
            return ['status' => 'error', 'message' => 'La caja está cerrada. Abre un nuevo turno para continuar.'];
        }

        if ($esIdProducto) {
            $stmtProd = $this->conn->prepare("SELECT p.*, COALESCE(i.stock_actual, p.stock, 0) AS stock_disponible
                FROM producto p LEFT JOIN inventario i ON p.id_producto = i.id_producto
                WHERE p.id_producto = :id AND (p.activo = 1 OR p.activo IS NULL) LIMIT 1");
            $stmtProd->bindValue(':id', (int)$codigoOId, PDO::PARAM_INT);
            $stmtProd->execute();
            $producto = $stmtProd->fetch(PDO::FETCH_ASSOC);
        } else {
            $queryProd = "SELECT p.*, COALESCE(i.stock_actual, p.stock, 0) as stock_disponible 
                      FROM producto p 
                      LEFT JOIN inventario i ON p.id_producto = i.id_producto 
                      WHERE p.codigo_barras = :codigo1 
                                 OR p.codigo = :codigo2
                             ORDER BY CASE WHEN p.codigo_barras = :codigo_prioridad THEN 1 ELSE 2 END
                             LIMIT 1";
            $stmtProd = $this->conn->prepare($queryProd);
            $stmtProd->bindParam(':codigo1', $codigoOId);
            $stmtProd->bindParam(':codigo2', $codigoOId);
            $stmtProd->bindParam(':codigo_prioridad', $codigoOId);
            $stmtProd->execute();
            $producto = $stmtProd->fetch(PDO::FETCH_ASSOC);
            if (!$producto && ctype_digit((string)$codigoOId)) {
                $stmtId = $this->conn->prepare("SELECT p.*, COALESCE(i.stock_actual, p.stock, 0) AS stock_disponible
                    FROM producto p LEFT JOIN inventario i ON p.id_producto = i.id_producto
                    WHERE p.id_producto = :id AND (p.activo = 1 OR p.activo IS NULL) LIMIT 1");
                $stmtId->bindValue(':id', (int)$codigoOId, PDO::PARAM_INT);
                $stmtId->execute();
                $producto = $stmtId->fetch(PDO::FETCH_ASSOC);
            }
            if (!$producto) {
                $candidatos = $this->buscarProductosManual($codigoOId);
                if (!empty($candidatos)) {
                    $producto = $candidatos[0];
                    $producto['stock_disponible'] = $producto['stock'] ?? 0;
                }
            }
        }

        if (!$producto) {
            return ['status' => 'error', 'message' => "Producto con código o nombre '{$codigoOId}' no encontrado"];
        }

        $stockDisponible = (int)($producto['stock_disponible'] ?? $producto['stock'] ?? 0);
        $nombreProducto = trim((string)($producto['nombre'] ?? ''));
        if ($nombreProducto === '') {
            $nombreProducto = trim((string)($producto['titulo'] ?? ''));
        }
        if ($nombreProducto === '') {
            $nombreProducto = 'Producto #' . (int)$producto['id_producto'];
        }
        if ($stockDisponible <= 0) {
            return ['status' => 'error', 'message' => "El producto '{$nombreProducto}' no cuenta con stock disponible"];
        }

        $items = json_decode($sesion['items_json'] ?? '[]', true);
        if (!is_array($items)) {
            $items = [];
        }

        $encontrado = false;
        $imgProd = !empty($producto['imagen']) ? $producto['imagen'] : (!empty($producto['imagen_url']) ? $producto['imagen_url'] : '');
        foreach ($items as &$item) {
            if ($item['id_producto'] == $producto['id_producto']) {
                if ($item['cantidad'] + 1 > $stockDisponible) {
                    return ['status' => 'error', 'message' => "Stock insuficiente para agregar mǭs unidades de '{$nombreProducto}'"];
                }
                $item['cantidad']++;
                $item['subtotal'] = round($item['cantidad'] * $item['precio'], 2);
                if (trim((string)($item['nombre'] ?? '')) === '') {
                    $item['nombre'] = $nombreProducto;
                }
                if (empty($item['imagen']) && empty($item['imagen_url']) && !empty($imgProd)) {
                    $item['imagen'] = $imgProd;
                    $item['imagen_url'] = $imgProd;
                }
                if (!array_key_exists('precio_anterior', $item)) {
                    $item['precio_anterior'] = isset($producto['precio_anterior']) ? (float)$producto['precio_anterior'] : null;
                }
                $encontrado = true;
                break;
            }
        }
        unset($item);

        if (!$encontrado) {
            $precio = (float)$producto['precio'];
            $items[] = [
                'id_producto'   => (int)$producto['id_producto'],
                'codigo'        => $producto['codigo_barras'] ?: $producto['codigo'],
                'nombre'        => $nombreProducto,
                'precio'        => $precio,
                'precio_anterior' => isset($producto['precio_anterior']) ? (float)$producto['precio_anterior'] : null,
                'cantidad'      => 1,
                'subtotal'      => $precio,
                'imagen'        => $imgProd,
                'imagen_url'    => $imgProd
            ];
        }

        $totalGeneral = 0;
        foreach ($items as $it) {
            $totalGeneral += $it['subtotal'];
        }
        $totalGeneral = round($totalGeneral, 2);

        $itemsJson = json_encode($items);
        $queryUpdate = "UPDATE sesion_caja 
                        SET items_json = :items, total_venta = :total 
                        WHERE token_emparejamiento = :token";
        $stmtUpdate = $this->conn->prepare($queryUpdate);
        $stmtUpdate->bindParam(':items', $itemsJson);
        $stmtUpdate->bindParam(':total', $totalGeneral);
        $stmtUpdate->bindParam(':token', $token);
        $stmtUpdate->execute();

        return [
            'status' => 'success',
            'message' => "Producto '{$nombreProducto}' agregado a la venta",
            'items'   => $items,
            'total'   => $totalGeneral,
            'producto_reciente' => $nombreProducto
        ];
    }

    public function actualizarCantidadItem($token, $idProducto, $nuevaCantidad) {
        $sesion = $this->obtenerSesionPorToken($token);
        if (!$sesion) return false;
        if (($sesion['estado'] ?? '') !== 'abierta') return false;

        $nuevaCantidad = (int)$nuevaCantidad;
        $nombreProd = '';
        if ($nuevaCantidad > 0) {
            $stmtStk = $this->conn->prepare("SELECT p.nombre, p.titulo, COALESCE(i.stock_actual, p.stock, 0) AS stock_disponible
                FROM producto p LEFT JOIN inventario i ON p.id_producto = i.id_producto
                WHERE p.id_producto = :id LIMIT 1");
            $stmtStk->bindValue(':id', (int)$idProducto, PDO::PARAM_INT);
            $stmtStk->execute();
            $prodRow = $stmtStk->fetch(PDO::FETCH_ASSOC);
            $stk = (int)($prodRow['stock_disponible'] ?? 0);
            $nombreProd = trim((string)($prodRow['nombre'] ?? ''));
            if ($nombreProd === '') {
                $nombreProd = trim((string)($prodRow['titulo'] ?? ''));
            }
            if ($nombreProd === '') {
                $nombreProd = 'Producto';
            }

            if ($nuevaCantidad > $stk) {
                return [
                    'error' => true,
                    'message' => "Stock insuficiente para '{$nombreProd}'. Máximo disponible: {$stk} unidades."
                ];
            }
        }

        $items = json_decode($sesion['items_json'] ?? '[]', true);
        if (!is_array($items)) $items = [];

        $nuevosItems = [];
        $totalGeneral = 0;

        foreach ($items as $item) {
            if ($item['id_producto'] == $idProducto) {
                if ($nuevaCantidad > 0) {
                    $item['cantidad'] = $nuevaCantidad;
                    $item['subtotal'] = round($item['cantidad'] * $item['precio'], 2);
                    if (trim((string)($item['nombre'] ?? '')) === '' && $nombreProd !== '') {
                        $item['nombre'] = $nombreProd;
                    }
                    $nuevosItems[] = $item;
                    $totalGeneral += $item['subtotal'];
                }
            } else {
                $nuevosItems[] = $item;
                $totalGeneral += $item['subtotal'];
            }
        }

        $itemsJson = json_encode($nuevosItems);
        $queryUpdate = "UPDATE sesion_caja SET items_json = :items, total_venta = :total WHERE token_emparejamiento = :token";
        $stmtUpdate = $this->conn->prepare($queryUpdate);
        $stmtUpdate->bindParam(':items', $itemsJson);
        $stmtUpdate->bindParam(':total', $totalGeneral);
        $stmtUpdate->bindParam(':token', $token);
        $stmtUpdate->execute();

        return ['items' => $nuevosItems, 'total' => round($totalGeneral, 2)];
    }

    public function finalizarVenta($token, $metodoPago = 'efectivo', $montoRecibido = 0, $clienteDni = '00000000', $clienteNombre = 'Público General', $pagos = []) {
        $sesion = $this->obtenerSesionPorToken($token);
        if (!$sesion) {
            return ['status' => 'error', 'message' => 'Sesión de caja no encontrada'];
        }
        if (($sesion['estado'] ?? '') !== 'abierta') {
            return ['status' => 'error', 'message' => 'La caja está cerrada. No se pueden procesar más ventas en este turno.'];
        }

        $items = json_decode($sesion['items_json'] ?? '[]', true);
        if (empty($items)) {
            return ['status' => 'error', 'message' => 'No hay productos en la orden de cobro'];
        }

        $clienteDni = trim($clienteDni ?: '00000000');
        $clienteNombre = trim($clienteNombre ?: 'Público General');

        $totalVenta = (float)$sesion['total_venta'];
        $montoRecibido = (float)$montoRecibido;
        $metodosPermitidos = ['efectivo', 'tarjeta', 'yape'];

        // Pagos divididos: normalizar la lista de pagos recibida del POS
        $pagosLimpios = [];
        if (is_array($pagos)) {
            foreach ($pagos as $p) {
                $m = strtolower(trim((string)($p['metodo'] ?? '')));
                $monto = round((float)($p['monto'] ?? 0), 2);
                if ($m === 'yape_plin') {
                    $m = 'yape';
                }
                if ($m === '' || $monto <= 0) {
                    continue;
                }
                if (!in_array($m, $metodosPermitidos, true)) {
                    return ['status' => 'error', 'message' => 'Método de pago no permitido: ' . $m];
                }
                $pagosLimpios[] = ['metodo' => $m, 'monto' => $monto];
            }
        }

        // Compatibilidad: si no llega desglose, se arma un único pago con metodo_pago
        if (empty($pagosLimpios)) {
            $metodoPago = strtolower(trim((string)$metodoPago));
            if ($metodoPago === 'yape_plin') {
                $metodoPago = 'yape';
            }
            if (!in_array($metodoPago, $metodosPermitidos, true)) {
                return ['status' => 'error', 'message' => 'Selecciona un método de pago válido.'];
            }
            $pagosLimpios[] = ['metodo' => $metodoPago, 'monto' => round($totalVenta, 2)];
        }

        // Totales por método y validación de la suma
        $totalesPorMetodo = [];
        foreach ($pagosLimpios as $p) {
            $totalesPorMetodo[$p['metodo']] = round(($totalesPorMetodo[$p['metodo']] ?? 0) + $p['monto'], 2);
        }
        $sumaPagos = round(array_sum($totalesPorMetodo), 2);
        if (abs($sumaPagos - round($totalVenta, 2)) > 0.05) {
            return ['status' => 'error', 'message' => 'La suma de los pagos (S/ ' . number_format($sumaPagos, 2) . ') no coincide con el total de la venta (S/ ' . number_format($totalVenta, 2) . ').'];
        }

        // Vueltos: solo aplican a la porción pagada en efectivo
        $efectivoAplicado = round($totalesPorMetodo['efectivo'] ?? 0, 2);
        if ($efectivoAplicado > 0) {
            if ($montoRecibido < $efectivoAplicado) {
                return ['status' => 'error', 'message' => 'El monto recibido en efectivo no cubre la parte en efectivo de la venta.'];
            }
            $vueltas = round($montoRecibido - $efectivoAplicado, 2);
        } else {
            $montoRecibido = round($totalVenta, 2);
            $vueltas = 0;
        }

        $metodosUsados = array_keys($totalesPorMetodo);
        $metodoPago = (count($metodosUsados) === 1) ? $metodosUsados[0] : 'mixto';

        try {
            $this->conn->beginTransaction();

            // Bloquear filas de productos y verificar existencias disponibles antes de proceder
            foreach ($items as $item) {
                $stLock = $this->conn->prepare("SELECT p.nombre, p.titulo, p.stock, p.precio_costo, COALESCE(i.stock_actual, p.stock, 0) AS stock_inv
                    FROM producto p
                    LEFT JOIN inventario i ON p.id_producto = i.id_producto
                    WHERE p.id_producto = :id FOR UPDATE");
                $stLock->bindValue(':id', (int)$item['id_producto'], PDO::PARAM_INT);
                $stLock->execute();
                $prodData = $stLock->fetch(PDO::FETCH_ASSOC);

                if (!$prodData) {
                    $nombreItem = trim((string)($item['nombre'] ?? ''));
                    if ($nombreItem === '') {
                        $nombreItem = 'Producto #' . (int)$item['id_producto'];
                    }
                    $this->conn->rollBack();
                    return ['status' => 'error', 'message' => "El producto '{$nombreItem}' ya no existe en el catálogo."];
                }

                $nombreProdData = trim((string)($prodData['nombre'] ?? ''));
                if ($nombreProdData === '') {
                    $nombreProdData = trim((string)($prodData['titulo'] ?? ''));
                }
                if ($nombreProdData === '') {
                    $nombreProdData = 'Producto #' . (int)$item['id_producto'];
                }

                $stockActual = (int)min($prodData['stock'], $prodData['stock_inv']);
                if ($stockActual < (int)$item['cantidad']) {
                    $this->conn->rollBack();
                    return [
                        'status' => 'error',
                        'message' => "Stock insuficiente para '{$nombreProdData}'. Disponible: {$stockActual}, solicitado: {$item['cantidad']}."
                    ];
                }
            }

            $codigoPedido = 'POS-' . date('Ymd-His') . '-' . rand(10, 99);
            $idCajero = (int)$sesion['id_usuario'];
            $idSesion = (int)$sesion['id_sesion'];

            $queryPedido = "INSERT INTO pedido (codigo_pedido, id_usuario, cliente_dni, cliente_nombre, id_cajero, id_sesion_caja, canal, tipo_entrega, subtotal, total, monto_recibido, vuelto, estado, fecha, fecha_pedido, direccion_envio, metodo_pago) 
                            VALUES (:cod, :id_usuario, :cliente_dni, :cliente_nombre, :id_cajero, :id_sesion, 'POS', 'tienda', :subtotal, :total, :recibido, :vuelto, 'Entregado', NOW(), NOW(), 'Venta Presencial - Mostrador POS', :metodo_pago)";
            $stmtPedido = $this->conn->prepare($queryPedido);
            $stmtPedido->bindParam(':cod', $codigoPedido);
            $stmtPedido->bindParam(':id_usuario', $idCajero, PDO::PARAM_INT);
            $stmtPedido->bindParam(':cliente_dni', $clienteDni);
            $stmtPedido->bindParam(':cliente_nombre', $clienteNombre);
            $stmtPedido->bindParam(':id_cajero', $idCajero, PDO::PARAM_INT);
            $stmtPedido->bindParam(':id_sesion', $idSesion, PDO::PARAM_INT);
            $stmtPedido->bindParam(':subtotal', $totalVenta);
            $stmtPedido->bindParam(':total', $totalVenta);
            $stmtPedido->bindParam(':recibido', $montoRecibido);
            $stmtPedido->bindParam(':vuelto', $vueltas);
            $stmtPedido->bindParam(':metodo_pago', $metodoPago);
            $stmtPedido->execute();
            $idPedido = $this->conn->lastInsertId();

            $queryDetalle = "INSERT INTO detalle_pedido (id_pedido, id_producto, cantidad, precio_unitario, costo_unitario, subtotal) 
                             VALUES (:id_pedido, :id_producto, :cantidad, :precio, :costo, :subtotal)";
            $stmtDetalle = $this->conn->prepare($queryDetalle);

            $queryStockProd = "UPDATE producto SET stock = stock - :cant_restar WHERE id_producto = :id_prod AND stock >= :cant_validar";
            $stmtStockProd = $this->conn->prepare($queryStockProd);

            $queryStockInv = "UPDATE inventario SET stock_actual = stock_actual - :cant_restar WHERE id_producto = :id_prod AND stock_actual >= :cant_validar";
            $stmtStockInv = $this->conn->prepare($queryStockInv);

            $queryKardex = "INSERT INTO kardex (id_producto, id_usuario, tipo_movimiento, motivo, cantidad, costo_unitario, precio_unitario, stock_anterior, stock_actual, referencia_id, referencia_tipo, fecha)
                            VALUES (:id_prod, :id_user, 'SALIDA', :motivo, :cant, :costo, :precio, :stk_ant, :stk_act, :ref_id, 'pedido', NOW())";
            $stmtKardex = $this->conn->prepare($queryKardex);

            foreach ($items as $item) {
                // Obtener costo actual y stock anterior
                $stProd = $this->conn->query("SELECT stock, precio_costo FROM producto WHERE id_producto = " . (int)$item['id_producto'])->fetch(PDO::FETCH_ASSOC);
                $costoUnit = (float)($stProd['precio_costo'] ?? 0);
                $stkAnt = (int)($stProd['stock'] ?? 0);
                $stkAct = max(0, $stkAnt - (int)$item['cantidad']);

                $stmtDetalle->bindValue(':id_pedido', $idPedido, PDO::PARAM_INT);
                $stmtDetalle->bindValue(':id_producto', $item['id_producto'], PDO::PARAM_INT);
                $stmtDetalle->bindValue(':cantidad', $item['cantidad'], PDO::PARAM_INT);
                $stmtDetalle->bindValue(':precio', $item['precio']);
                $stmtDetalle->bindValue(':costo', $costoUnit);
                $stmtDetalle->bindValue(':subtotal', $item['subtotal']);
                $stmtDetalle->execute();

                $stmtStockProd->bindValue(':cant_restar', $item['cantidad'], PDO::PARAM_INT);
                $stmtStockProd->bindValue(':id_prod', $item['id_producto'], PDO::PARAM_INT);
                $stmtStockProd->bindValue(':cant_validar', $item['cantidad'], PDO::PARAM_INT);
                $stmtStockProd->execute();

                $stmtStockInv->bindValue(':cant_restar', $item['cantidad'], PDO::PARAM_INT);
                $stmtStockInv->bindValue(':id_prod', $item['id_producto'], PDO::PARAM_INT);
                $stmtStockInv->bindValue(':cant_validar', $item['cantidad'], PDO::PARAM_INT);
                $stmtStockInv->execute();

                $motivo = "Venta POS {$codigoPedido}";
                $stmtKardex->bindValue(':id_prod', $item['id_producto'], PDO::PARAM_INT);
                $stmtKardex->bindValue(':id_user', $idCajero, PDO::PARAM_INT);
                $stmtKardex->bindValue(':motivo', $motivo);
                $stmtKardex->bindValue(':cant', $item['cantidad'], PDO::PARAM_INT);
                $stmtKardex->bindValue(':costo', $costoUnit);
                $stmtKardex->bindValue(':precio', $item['precio']);
                $stmtKardex->bindValue(':stk_ant', $stkAnt, PDO::PARAM_INT);
                $stmtKardex->bindValue(':stk_act', $stkAct, PDO::PARAM_INT);
                $stmtKardex->bindValue(':ref_id', $idPedido, PDO::PARAM_INT);
                $stmtKardex->execute();
            }

            // Guardar el desglose de pagos (uno o varios métodos por pedido)
            $stmtPago = $this->conn->prepare("INSERT INTO pedido_pago (id_pedido, metodo, monto) VALUES (:id_pedido, :metodo, :monto)");
            foreach ($pagosLimpios as $p) {
                $stmtPago->bindValue(':id_pedido', $idPedido, PDO::PARAM_INT);
                $stmtPago->bindValue(':metodo', $p['metodo']);
                $stmtPago->bindValue(':monto', $p['monto']);
                $stmtPago->execute();
            }

            // Actualizar acumuladores en la sesión de caja (reparte el monto por método)
            $columnasMetodo = [
                'efectivo' => 'total_ventas_efectivo',
                'yape' => 'total_ventas_yape',
                'tarjeta' => 'total_ventas_tarjeta'
            ];
            $setParts = ["items_json = '[]'", "total_venta = 0.00"];
            $bindCaja = [':token' => $token];
            foreach ($totalesPorMetodo as $metodo => $montoMetodo) {
                if (!isset($columnasMetodo[$metodo])) {
                    continue;
                }
                $col = $columnasMetodo[$metodo];
                $ph = ':m_' . $metodo;
                $setParts[] = "{$col} = {$col} + {$ph}";
                $bindCaja[$ph] = $montoMetodo;
            }
            $queryCaja = "UPDATE sesion_caja SET " . implode(', ', $setParts) . " WHERE token_emparejamiento = :token";
            $stmtCaja = $this->conn->prepare($queryCaja);
            foreach ($bindCaja as $ph => $valor) {
                $stmtCaja->bindValue($ph, $valor);
            }
            $stmtCaja->execute();

            $this->conn->commit();

            return [
                'status' => 'success',
                'message' => '¡Venta procesada con éxito!',
                'id_pedido' => $idPedido,
                'codigo_pedido' => $codigoPedido,
                'cliente_dni' => $clienteDni,
                'cliente_nombre' => $clienteNombre,
                'total' => $totalVenta,
                'subtotal' => round($totalVenta / 1.18, 2),
                'igv' => round($totalVenta - ($totalVenta / 1.18), 2),
                'vueltas' => max(0, $vueltas),
                'monto_recibido' => $montoRecibido,
                'metodo_pago' => implode(' + ', array_map('strtoupper', $metodosUsados)),
                'pagos' => $pagosLimpios,
                'fecha' => date('d/m/Y H:i:s'),
                'cajero' => ($sesion['nombre'] ?? 'Cajero') . ' ' . ($sesion['apellido'] ?? ''),
                'items' => $items
            ];

        } catch (Exception $e) {
            $this->conn->rollBack();
            return ['status' => 'error', 'message' => 'Error al procesar la venta: ' . $e->getMessage()];
        }
    }
}
?>
