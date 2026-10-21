<?php
require_once 'Core/dataBase.php';

class PedidoModel{
    private $conn;

    public function __construct(){
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getPedidosByUsuario($idUsuario){
        $sql = "SELECT p.id_pedido, p.total, p.estado, p.tipo_entrega, p.observacion, p.fecha, p.leido_cliente, mp.nombre AS metodo_pago,
                    d.departamento, d.provincia, d.distrito, d.calle, d.referencia
                FROM pedido p LEFT JOIN metodo_pago mp ON mp.id_metodo = p.id_metodo
                LEFT JOIN direccion d ON d.id_direccion = p.id_direccion
                WHERE p.id_usuario = :id_usuario ORDER BY p.fecha DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id_usuario', $idUsuario, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPedidosActivosPorUsuario($idUsuario){
        $sql = "SELECT id_pedido, total, estado, tipo_entrega, fecha, leido_cliente 
                FROM pedido 
                WHERE id_usuario = :id_usuario 
                  AND LOWER(estado) NOT IN ('entregado', 'cancelado', 'rechazado')
                ORDER BY fecha DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id_usuario', (int)$idUsuario, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerCantidadPedidosNoLeidos($idUsuario){
        $sql = "SELECT COUNT(*) FROM pedido 
                WHERE id_usuario = :id_usuario 
                  AND leido_cliente = 0";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id_usuario', (int)$idUsuario, PDO::PARAM_INT);
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    public function marcarPedidoLeido($idPedido, $idUsuario){
        $sql = "UPDATE pedido SET leido_cliente = 1 WHERE id_pedido = :id_pedido AND id_usuario = :id_usuario";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id_pedido', (int)$idPedido, PDO::PARAM_INT);
        $stmt->bindValue(':id_usuario', (int)$idUsuario, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function marcarTodosPedidosLeidos($idUsuario){
        $sql = "UPDATE pedido SET leido_cliente = 1 WHERE id_usuario = :id_usuario AND leido_cliente = 0";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id_usuario', (int)$idUsuario, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function getPedidoByIdAndUsuario($idPedido, $idUsuario){
        $sql = "SELECT p.*, mp.nombre AS metodo_pago, d.departamento, d.provincia, d.distrito, d.calle, d.referencia
                FROM pedido p LEFT JOIN metodo_pago mp ON mp.id_metodo = p.id_metodo
                LEFT JOIN direccion d ON d.id_direccion = p.id_direccion
                WHERE p.id_pedido = :id_pedido AND p.id_usuario = :id_usuario LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id_pedido', $idPedido, PDO::PARAM_INT);
        $stmt->bindValue(':id_usuario', $idUsuario, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getDetallePedido($idPedido, $idUsuario){
        $sql = "SELECT dp.id_detalle, dp.id_pedido, dp.id_producto, dp.cantidad, dp.precio_unitario, dp.subtotal, pr.titulo, pr.imagen, pr.unidad_medida
                FROM detalle_pedido dp
                INNER JOIN pedido p ON p.id_pedido = dp.id_pedido
                INNER JOIN producto pr ON pr.id_producto = dp.id_producto
                WHERE dp.id_pedido = :id_pedido AND p.id_usuario = :id_usuario ORDER BY dp.id_detalle ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id_pedido', $idPedido, PDO::PARAM_INT);
        $stmt->bindValue(':id_usuario', $idUsuario, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMetodoPagoById($idMetodo){
        $sql = "SELECT * FROM metodo_pago WHERE id_metodo = :id_metodo AND activo = 1 LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id_metodo', (int)$idMetodo, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function crearPedido($idUsuario, $idMetodo, $idDireccion, $tipoEntrega, $observacion, $carrito, $datosVoucher = [], $horarioEntrega = 'Lo antes posible (30-45 min)', $fechaProgramada = null){
        try {
            $this->conn->beginTransaction();
            $subtotal = 0.00;
            $costoEnvio = 0.00;
            $detalles = [];

            if ($tipoEntrega === 'delivery') {
                $costoEnvio = 5.00;
                if ($idDireccion) {
                    $stmtCost = $this->conn->prepare("SELECT COALESCE(dd.costo_delivery, 5.00) AS costo 
                        FROM direccion d 
                        LEFT JOIN distrito_delivery dd ON (dd.id_distrito = d.id_distrito OR dd.nombre COLLATE utf8mb4_unicode_ci = d.distrito COLLATE utf8mb4_unicode_ci) 
                        WHERE d.id_direccion = :id LIMIT 1");
                    $stmtCost->execute([':id' => $idDireccion]);
                    $rowCost = $stmtCost->fetch(PDO::FETCH_ASSOC);
                    if ($rowCost && !empty($rowCost['costo'])) {
                        $costoEnvio = (float)$rowCost['costo'];
                    }
                }
            }

            $sqlMetodo = "SELECT * FROM metodo_pago
                        WHERE id_metodo = :id_metodo AND activo = 1 LIMIT 1";

            $stmtMetodo = $this->conn->prepare($sqlMetodo);
            $stmtMetodo->bindValue(':id_metodo', $idMetodo, PDO::PARAM_INT);
            $stmtMetodo->execute();
            $metodoPago = $stmtMetodo->fetch(PDO::FETCH_ASSOC);

            if (!$metodoPago) {
                throw new Exception('El método de pago seleccionado no está disponible.');
            }

            foreach ($carrito as $item) {
                $idProducto = isset($item['id_producto']) ? (int) $item['id_producto'] : 0;
                $cantidad = isset($item['cantidad']) ? (float) $item['cantidad'] : 0;

                if ($idProducto <= 0 || $cantidad <= 0) {
                    throw new Exception('El carrito contiene información inválida.');
                }

                $sqlProducto = "SELECT p.id_producto, p.titulo, p.precio, COALESCE(p.precio_costo, 0.00) AS precio_costo, p.unidad_medida, p.activo,
                                    p.stock AS stock_producto, i.id_inventario, COALESCE(i.stock_actual, p.stock) AS stock_actual
                                FROM producto p LEFT JOIN inventario i ON i.id_producto = p.id_producto
                                WHERE p.id_producto = :id_producto AND p.activo = 1 LIMIT 1 FOR UPDATE";

                $stmtProducto = $this->conn->prepare($sqlProducto);
                $stmtProducto->bindValue(':id_producto', $idProducto, PDO::PARAM_INT);
                $stmtProducto->execute();
                $producto = $stmtProducto->fetch(PDO::FETCH_ASSOC);

                if (!$producto) {
                    throw new Exception('Uno de los productos ya no está disponible.');
                }

                $stockActual = (float) $producto['stock_actual'];

                if ($cantidad > $stockActual) {
                    throw new Exception(
                        'Stock insuficiente para "' . $producto['titulo'] . '". Disponible: ' . $stockActual . '.'
                    );
                }

                $precioUnitario = (float) $producto['precio'];
                $precioCosto = (float) $producto['precio_costo'];
                $subtotalDetalle = round($precioUnitario * $cantidad, 2);
                $subtotal += $subtotalDetalle;

                $detalles[] = [
                    'id_producto' => $idProducto,
                    'titulo_producto' => $producto['titulo'],
                    'unidad_medida' => strtoupper($producto['unidad_medida']),
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precioUnitario,
                    'precio_costo' => $precioCosto,
                    'subtotal' => $subtotalDetalle,
                    'stock_actual' => $stockActual,
                    'tiene_inventario' => !empty($producto['id_inventario'])
                ];
            }

            if (empty($detalles)) {
                throw new Exception('El carrito está vacío.');
            }

            $subtotal = round($subtotal, 2);
            $total = round($subtotal + $costoEnvio, 2);

            // Generar código de pedido único
            $codigoPedido = 'PED-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));

            $nombreMetodo = strtolower(trim($metodoPago['nombre']));
            $esEfectivo = (strpos($nombreMetodo, 'efectivo') !== false || strpos($nombreMetodo, 'recibir') !== false);
            $esAprobacionDirecta = !empty($datosVoucher['aprobacion_directa']);
            $esPasarela = !$esEfectivo && !$esAprobacionDirecta && !empty($metodoPago['activo']);
            $requiereVerificacion = !empty($metodoPago['requiere_comprobante']);

            if ($esAprobacionDirecta) {
                $estadoPedido = 'En Preparacion';
                $estadoPago = 'pagado';
                $pasarelaId = $datosVoucher['pasarela_id'] ?? 'mercadopago';
                $pasarelaStatus = 'approved';
                $pasarelaPaymentId = $datosVoucher['codigo'] ?? null;
                $pasarelaPaymentType = $datosVoucher['billetera'] ?? null;
            } elseif ($esPasarela) {
                $estadoPedido = 'Pendiente';
                $estadoPago = 'pendiente_pasarela';
                $pasarelaId = 'mercadopago';
                $pasarelaStatus = 'pending';
                $pasarelaPaymentId = null;
                $pasarelaPaymentType = null;
            } elseif ($requiereVerificacion) {
                $estadoPedido = 'Por Verificar';
                $estadoPago = 'pendiente_verificacion';
                $pasarelaId = null;
                $pasarelaStatus = null;
                $pasarelaPaymentId = null;
                $pasarelaPaymentType = null;
            } else {
                $estadoPedido = 'En Preparacion';
                $estadoPago = ($tipoEntrega === 'delivery') ? 'pendiente' : 'pagado';
                $pasarelaId = null;
                $pasarelaStatus = null;
                $pasarelaPaymentId = null;
                $pasarelaPaymentType = null;
            }

            $tipoEntregaBD = ($tipoEntrega === 'delivery') ? 'delivery' : 'tienda';

            // Datos de entrega
            $direccionTexto = 'Recojo en tienda';
            $nombreDestinatario = 'Cliente';
            $celularContacto = '999999999';
            $referenciaEnvio = $observacion;

            if ($tipoEntrega === 'delivery' && $idDireccion) {
                $stmtD = $this->conn->prepare("SELECT * FROM direccion WHERE id_direccion = :id LIMIT 1");
                $stmtD->execute([':id' => $idDireccion]);
                $dir = $stmtD->fetch(PDO::FETCH_ASSOC);
                if ($dir) {
                    $partes = array_filter([$dir['calle'], !empty($dir['numero']) ? '#' . $dir['numero'] : '', !empty($dir['lote_manzana']) ? 'Lote ' . $dir['lote_manzana'] : '', $dir['distrito'], 'Chiclayo']);
                    $direccionTexto = implode(', ', $partes);
                    $referenciaEnvio = !empty($dir['referencia']) ? $dir['referencia'] : $observacion;
                    if (!empty($dir['telefono_contacto'])) {
                        $celularContacto = $dir['telefono_contacto'];
                    }
                }

                $stmtU = $this->conn->prepare("SELECT nombre, apellido, telefono FROM usuario WHERE id_usuario = :id LIMIT 1");
                $stmtU->execute([':id' => $idUsuario]);
                $usr = $stmtU->fetch(PDO::FETCH_ASSOC);
                if ($usr) {
                    $nombreDestinatario = trim(($usr['nombre'] ?? '') . ' ' . ($usr['apellido'] ?? ''));
                    if (!empty($usr['telefono']) && $celularContacto === '999999999') {
                        $celularContacto = $usr['telefono'];
                    }
                }
            }

            $sqlPedido = "INSERT INTO pedido (
                            codigo_pedido,
                            id_usuario,
                            id_metodo,
                            id_direccion,
                            canal,
                            tipo_entrega,
                            horario_entrega,
                            fecha_programada,
                            subtotal,
                            costo_envio,
                            total,
                            estado,
                            metodo_pago,
                            pasarela_id,
                            pasarela_status,
                            pasarela_payment_id,
                            pasarela_payment_type,
                            direccion_envio,
                            observacion,
                            fecha,
                            fecha_pedido
                        ) VALUES (
                            :codigo_pedido,
                            :id_usuario,
                            :id_metodo,
                            :id_direccion,
                            'WEB',
                            :tipo_entrega,
                            :horario_entrega,
                            :fecha_programada,
                            :subtotal,
                            :costo_envio,
                            :total,
                            :estado,
                            :metodo_pago,
                            :pasarela_id,
                            :pasarela_status,
                            :pasarela_payment_id,
                            :pasarela_payment_type,
                            :direccion_envio,
                            :observacion,
                            NOW(),
                            NOW()
                        )";

            $stmtPedido = $this->conn->prepare($sqlPedido);
            $stmtPedido->bindValue(':codigo_pedido', $codigoPedido);
            $stmtPedido->bindValue(':id_usuario', $idUsuario, PDO::PARAM_INT);
            $stmtPedido->bindValue(':id_metodo', $idMetodo, PDO::PARAM_INT);

            if ($idDireccion === null) {
                $stmtPedido->bindValue(':id_direccion', null, PDO::PARAM_NULL);
            } else {
                $stmtPedido->bindValue(':id_direccion', $idDireccion, PDO::PARAM_INT);
            }

            $stmtPedido->bindValue(':tipo_entrega', $tipoEntregaBD);
            $stmtPedido->bindValue(':horario_entrega', $horarioEntrega);
            $stmtPedido->bindValue(':fecha_programada', $fechaProgramada);
            $stmtPedido->bindValue(':subtotal', $subtotal);
            $stmtPedido->bindValue(':costo_envio', $costoEnvio);
            $stmtPedido->bindValue(':total', $total);
            $stmtPedido->bindValue(':estado', $estadoPedido);
            $stmtPedido->bindValue(':metodo_pago', $metodoPago['nombre'] ?? 'Web');
            $stmtPedido->bindValue(':pasarela_id', $pasarelaId);
            $stmtPedido->bindValue(':pasarela_status', $pasarelaStatus);
            $stmtPedido->bindValue(':pasarela_payment_id', $pasarelaPaymentId);
            $stmtPedido->bindValue(':pasarela_payment_type', $pasarelaPaymentType);
            $stmtPedido->bindValue(':direccion_envio', $direccionTexto);
            $stmtPedido->bindValue(':observacion', $observacion);
            $stmtPedido->execute();

            $idPedido = (int) $this->conn->lastInsertId();

            $sqlDetalle = "INSERT INTO detalle_pedido (
                            id_pedido,
                            id_producto,
                            titulo_producto,
                            unidad_medida,
                            cantidad,
                            precio_unitario,
                            costo_unitario,
                            subtotal
                        ) VALUES (
                            :id_pedido,
                            :id_producto,
                            :titulo_producto,
                            :unidad_medida,
                            :cantidad,
                            :precio_unitario,
                            :costo_unitario,
                            :subtotal
                        )";

            $stmtDetalle = $this->conn->prepare($sqlDetalle);

            foreach ($detalles as $detalle) {
                $stmtDetalle->execute([
                    ':id_pedido' => $idPedido,
                    ':id_producto' => $detalle['id_producto'],
                    ':titulo_producto' => $detalle['titulo_producto'],
                    ':unidad_medida' => $detalle['unidad_medida'],
                    ':cantidad' => $detalle['cantidad'],
                    ':precio_unitario' => $detalle['precio_unitario'],
                    ':costo_unitario' => $detalle['precio_costo'],
                    ':subtotal' => $detalle['subtotal']
                ]);
            }

            // Si es Pago Digital (Voucher tradicional o Aprobación Directa con datos ingresados):
            if (!empty($datosVoucher['celular']) || !empty($datosVoucher['codigo'])) {
                $sqlPV = "INSERT INTO pago_verificacion (
                            id_pedido, id_metodo, codigo_operacion, celular_remitente, titular_origen, captura_voucher, monto_reportado, estado_verificacion, fecha_envio, fecha_verificacion
                          ) VALUES (
                            :id_pedido, :id_metodo, :cod, :cel, :tit, :cap, :monto, :estado_v, NOW(), " . ($esAprobacionDirecta ? "NOW()" : "NULL") . "
                          )";
                $stmtPV = $this->conn->prepare($sqlPV);
                $stmtPV->execute([
                    ':id_pedido' => $idPedido,
                    ':id_metodo' => $idMetodo,
                    ':cod' => $datosVoucher['codigo'] ?? 'S/C',
                    ':cel' => $datosVoucher['celular'] ?? '',
                    ':tit' => $datosVoucher['titular'] ?? 'Cliente Web',
                    ':cap' => $datosVoucher['voucher'] ?? 'VERIFICACION_AUTOMATICA',
                    ':monto' => $total,
                    ':estado_v' => $esAprobacionDirecta ? 'Aprobado' : 'Pendiente'
                ]);
            }

            if (!$esPasarela) {
                // Si es Efectivo o Aprobación Directa: Descontar stock y registrar Kardex de inmediato
                require_once __DIR__ . '/kardexModel.php';
                $kardex = new KardexModel();

                $sqlInventario = "UPDATE inventario SET stock_actual = stock_actual - :cantidad WHERE id_producto = :id_producto AND stock_actual >= :cantidad_min";
                $stmtInventario = $this->conn->prepare($sqlInventario);

                $sqlProductoStock = "UPDATE producto SET stock = stock - :cantidad WHERE id_producto = :id_producto AND stock >= :cantidad_min";
                $stmtProductoStock = $this->conn->prepare($sqlProductoStock);

                foreach ($detalles as $detalle) {
                    if ($detalle['tiene_inventario']) {
                        $stmtInventario->execute([
                            ':cantidad' => $detalle['cantidad'],
                            ':cantidad_min' => $detalle['cantidad'],
                            ':id_producto' => $detalle['id_producto']
                        ]);
                    }

                    $stmtProductoStock->execute([
                        ':cantidad' => $detalle['cantidad'],
                        ':cantidad_min' => $detalle['cantidad'],
                        ':id_producto' => $detalle['id_producto']
                    ]);

                    $nuevoStock = max(0, $detalle['stock_actual'] - $detalle['cantidad']);
                    $kardex->registrarMovimiento(
                        $detalle['id_producto'],
                        $idUsuario,
                        'SALIDA',
                        'Venta Web Directa #' . $codigoPedido,
                        $detalle['cantidad'],
                        $detalle['precio_costo'],
                        $detalle['precio_unitario'],
                        $detalle['stock_actual'],
                        $nuevoStock,
                        $idPedido,
                        'pedido'
                    );
                }
            }

            // Si es Delivery: Registrar en la cola de logística y envíos
            if ($tipoEntrega === 'delivery') {
                $sqlEnvio = "INSERT INTO envio_delivery (
                                id_pedido, nombre_destinatario, celular_contacto, direccion_destino, referencia, costo_delivery, estado_delivery, notas_entrega
                             ) VALUES (
                                :id_pedido, :nombre_destinatario, :celular_contacto, :direccion_destino, :referencia, :costo_delivery, 'Pendiente', :notas_entrega
                             )";
                $stmtEnvio = $this->conn->prepare($sqlEnvio);
                $stmtEnvio->execute([
                    ':id_pedido' => $idPedido,
                    ':nombre_destinatario' => $nombreDestinatario,
                    ':celular_contacto' => $celularContacto,
                    ':direccion_destino' => $direccionTexto,
                    ':referencia' => $referenciaEnvio,
                    ':costo_delivery' => $costoEnvio,
                    ':notas_entrega' => 'Horario: ' . $horarioEntrega . ($fechaProgramada ? ' | Programado: ' . $fechaProgramada : '')
                ]);
            }

            // Comprobante de venta
            $numeroComprobante = 'B001-' . str_pad((string) $idPedido, 8, '0', STR_PAD_LEFT);

            $sqlVenta = "INSERT INTO venta (
                            id_pedido,
                            id_usuario,
                            subtotal,
                            costo_envio,
                            igv,
                            descuento,
                            total,
                            tipo_comprobante,
                            numero_comprobante,
                            estado_pago,
                            fecha
                        ) VALUES (
                            :id_pedido,
                            :id_usuario,
                            :subtotal,
                            :costo_envio,
                            0.00,
                            0.00,
                            :total,
                            'boleta',
                            :numero_comprobante,
                            :estado_pago,
                            NOW()
                        )";

            $stmtVenta = $this->conn->prepare($sqlVenta);
            $stmtVenta->execute([
                ':id_pedido' => $idPedido,
                ':id_usuario' => $idUsuario,
                ':subtotal' => $subtotal,
                ':costo_envio' => $costoEnvio,
                ':total' => $total,
                ':numero_comprobante' => $numeroComprobante,
                ':estado_pago' => $estadoPago
            ]);

            $idVenta = (int) $this->conn->lastInsertId();

            $this->conn->commit();

            return [
                'success' => true,
                'id_pedido' => $idPedido,
                'codigo_pedido' => $codigoPedido,
                'id_venta' => $idVenta,
                'subtotal' => $subtotal,
                'costo_envio' => $costoEnvio,
                'total' => $total,
                'estado' => $estadoPedido,
                'estado_pago' => $estadoPago,
                'numero_comprobante' => $numeroComprobante,
                'es_pasarela' => $esPasarela
            ];
        } catch (Throwable $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function getPedidoConfirmado($idPedido, $idUsuario){
        $sql = "SELECT
                    p.*,
                    mp.nombre AS metodo_pago,
                    v.numero_comprobante,

                    d.departamento,
                    d.provincia,
                    d.distrito,
                    d.calle,
                    d.referencia,

                    u.nombre,
                    u.apellido,
                    u.correo,
                    u.telefono

                FROM pedido p

                INNER JOIN usuario u
                    ON u.id_usuario = p.id_usuario

                LEFT JOIN metodo_pago mp
                    ON mp.id_metodo = p.id_metodo

                LEFT JOIN direccion d
                    ON d.id_direccion = p.id_direccion

                LEFT JOIN venta v
                    ON v.id_pedido = p.id_pedido

                WHERE
                    p.id_pedido = :id_pedido
                AND
                    p.id_usuario = :id_usuario

                LIMIT 1";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(
            ':id_pedido',
            $idPedido,
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':id_usuario',
            $idUsuario,
            PDO::PARAM_INT
        );

        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllPedidosAdmin(): array
    {
        $sql = "SELECT
                    p.id_pedido,
                    p.canal,
                    p.total,
                    p.estado,
                    p.tipo_entrega,
                    p.fecha,
                    p.observacion,
                    mp.nombre AS metodo_pago,
                    u.nombre  AS usuario_nombre,
                    u.apellido AS usuario_apellido,
                    u.correo  AS usuario_correo
                FROM pedido p
                LEFT JOIN metodo_pago mp ON mp.id_metodo = p.id_metodo
                LEFT JOIN usuario u      ON u.id_usuario = p.id_usuario
                ORDER BY p.fecha DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDetallePedidoAdmin($idPedido): array
    {
        $sql = "SELECT dp.*, pr.titulo, pr.imagen, pr.unidad_medida
                FROM detalle_pedido dp
                INNER JOIN producto pr ON pr.id_producto = dp.id_producto
                WHERE dp.id_pedido = :id_pedido
                ORDER BY dp.id_detalle ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id_pedido', $idPedido, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPedidoByIdAdmin($idPedido)
    {
        $sql = "SELECT p.*, mp.nombre AS metodo_pago,
                    d.departamento, d.provincia, d.distrito, d.calle, d.referencia,
                    u.nombre AS usuario_nombre, u.apellido AS usuario_apellido, u.correo AS usuario_correo
                FROM pedido p
                LEFT JOIN metodo_pago mp ON mp.id_metodo = p.id_metodo
                LEFT JOIN direccion d    ON d.id_direccion = p.id_direccion
                LEFT JOIN usuario u      ON u.id_usuario = p.id_usuario
                WHERE p.id_pedido = :id_pedido LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id_pedido', $idPedido, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateEstadoPedido($idPedido, $nuevoEstado): bool
    {
        $map = [
            'pendiente'         => 'Pendiente',
            'en preparacion'    => 'En Preparacion',
            'preparacion'       => 'En Preparacion',
            'en camino'         => 'En Camino',
            'camino'            => 'En Camino',
            'listo para recojo' => 'Listo para Recojo',
            'listo recojo'      => 'Listo para Recojo',
            'entregado'         => 'Entregado',
            'cancelado'         => 'Cancelado'
        ];

        $key = strtolower(trim(str_replace(['_', '-'], ' ', $nuevoEstado)));
        $estadoFinal = $map[$key] ?? null;
        if (!$estadoFinal) {
            foreach ($map as $val) {
                if (strcasecmp($val, $nuevoEstado) === 0) {
                    $estadoFinal = $val;
                    break;
                }
            }
        }

        if (!$estadoFinal) {
            return false;
        }

        $sql = "UPDATE pedido
                SET leido_cliente = CASE
                        WHEN LOWER(estado) <> LOWER(:estado_comparar) THEN 0
                        ELSE leido_cliente
                    END,
                    estado = :estado
                WHERE id_pedido = :id_pedido";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':estado_comparar', $estadoFinal, PDO::PARAM_STR);
        $stmt->bindValue(':estado', $estadoFinal, PDO::PARAM_STR);
        $stmt->bindValue(':id_pedido', $idPedido, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function getPedidoByCodigo(string $codigoPedido)
    {
        $sql = "SELECT p.*, mp.nombre AS metodo_pago, u.nombre, u.apellido, u.correo, u.telefono
                FROM pedido p
                INNER JOIN usuario u ON u.id_usuario = p.id_usuario
                LEFT JOIN metodo_pago mp ON mp.id_metodo = p.id_metodo
                WHERE p.codigo_pedido = :codigo LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':codigo' => $codigoPedido]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getPedidoByPreferenceId(string $preferenceId)
    {
        $sql = "SELECT p.*, mp.nombre AS metodo_pago, u.nombre, u.apellido, u.correo, u.telefono
                FROM pedido p
                INNER JOIN usuario u ON u.id_usuario = p.id_usuario
                LEFT JOIN metodo_pago mp ON mp.id_metodo = p.id_metodo
                WHERE p.pasarela_preference_id = :pref LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':pref' => $preferenceId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function actualizarPreferenciaPasarela(int $idPedido, string $preferenceId): bool
    {
        $sql = "UPDATE pedido SET pasarela_preference_id = :pref WHERE id_pedido = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':pref' => $preferenceId, ':id' => $idPedido]);
    }

    public function confirmarPagoPasarela(int $idPedido, string $paymentId, string $status = 'approved', string $paymentType = 'credit_card'): bool
    {
        try {
            $this->conn->beginTransaction();

            $stmtP = $this->conn->prepare("SELECT * FROM pedido WHERE id_pedido = :id FOR UPDATE");
            $stmtP->execute([':id' => $idPedido]);
            $pedido = $stmtP->fetch(PDO::FETCH_ASSOC);

            if (!$pedido) {
                $this->conn->rollBack();
                return false;
            }

            // Si ya fue aprobado y procesado, retornar true sin duplicar kardex
            if ($pedido['pasarela_status'] === 'approved' && $pedido['estado'] === 'En Preparacion') {
                $this->conn->commit();
                return true;
            }

            // 1. Actualizar estado del pedido
            $sqlUpd = "UPDATE pedido SET 
                        estado = 'En Preparacion',
                        leido_cliente = 0,
                        pasarela_id = 'mercadopago',
                        pasarela_payment_id = :payment_id,
                        pasarela_status = :status,
                        pasarela_payment_type = :payment_type
                       WHERE id_pedido = :id";
            $stmtUpd = $this->conn->prepare($sqlUpd);
            $stmtUpd->execute([
                ':payment_id'   => $paymentId,
                ':status'       => $status,
                ':payment_type' => $paymentType,
                ':id'           => $idPedido
            ]);

            // 2. Actualizar estado de pago en venta
            $this->conn->prepare("UPDATE venta SET estado_pago = 'pagado' WHERE id_pedido = :id")
                       ->execute([':id' => $idPedido]);

            // 3. Descontar stock de inventario y registrar Kardex de salida
            require_once __DIR__ . '/kardexModel.php';
            $kardex = new KardexModel();

            $sqlDet = "SELECT dp.*, p.stock, COALESCE(p.precio_costo, 0.00) AS precio_costo, i.id_inventario, COALESCE(i.stock_actual, p.stock) AS stock_actual
                       FROM detalle_pedido dp
                       JOIN producto p ON dp.id_producto = p.id_producto
                       LEFT JOIN inventario i ON i.id_producto = p.id_producto
                       WHERE dp.id_pedido = :id";
            $stmtDet = $this->conn->prepare($sqlDet);
            $stmtDet->execute([':id' => $idPedido]);
            $detalles = $stmtDet->fetchAll(PDO::FETCH_ASSOC);

            $sqlInv = "UPDATE inventario SET stock_actual = stock_actual - :cantidad WHERE id_producto = :id_producto AND stock_actual >= :cantidad";
            $stmtInv = $this->conn->prepare($sqlInv);

            $sqlProd = "UPDATE producto SET stock = stock - :cantidad WHERE id_producto = :id_producto AND stock >= :cantidad";
            $stmtProd = $this->conn->prepare($sqlProd);

            foreach ($detalles as $det) {
                $cant = (float)$det['cantidad'];
                if (!empty($det['id_inventario'])) {
                    $stmtInv->execute([':cantidad' => $cant, ':id_producto' => $det['id_producto']]);
                }
                $stmtProd->execute([':cantidad' => $cant, ':id_producto' => $det['id_producto']]);

                $stockAnt = (float)$det['stock_actual'];
                $stockNuevo = max(0, $stockAnt - $cant);

                $kardex->registrarMovimiento(
                    $det['id_producto'],
                    $pedido['id_usuario'],
                    'SALIDA',
                    'Pago Aprobado Mercado Pago #' . $pedido['codigo_pedido'],
                    $cant,
                    (float)$det['precio_costo'],
                    (float)$det['precio_unitario'],
                    $stockAnt,
                    $stockNuevo,
                    $idPedido,
                    'pedido'
                );
            }

            $this->conn->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            error_log("Error confirmando pago pasarela: " . $e->getMessage());
            return false;
        }
    }

    public function cancelarPedidoPasarela(int $idPedido, string $status = 'rejected'): bool
    {
        $sql = "UPDATE pedido SET estado = 'Cancelado', leido_cliente = 0, pasarela_status = :status WHERE id_pedido = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':status' => $status, ':id' => $idPedido]);
    }

    /**
     * Bandeja de pedidos ordenada FIFO con datos de pago, despacho y entrega.
     * Puede incluir los pedidos finalizados para la vista unificada de Cajero.
     */
    public function getPedidosBandeja(bool $incluirFinalizados = false): array
    {
        $filtroEstado = $incluirFinalizados ? '' : "WHERE p.estado NOT IN ('Entregado', 'Cancelado')";
        $orden = $incluirFinalizados
            ? "ORDER BY CASE
                    WHEN LOWER(p.estado) LIKE '%pend%' THEN 1
                    WHEN LOWER(p.estado) LIKE '%prep%' THEN 2
                    WHEN LOWER(p.estado) LIKE '%camin%' OR LOWER(p.estado) LIKE '%recoj%' THEN 3
                    WHEN LOWER(p.estado) LIKE '%cancel%' THEN 4
                    WHEN LOWER(p.estado) LIKE '%entreg%' THEN 5
                    ELSE 6
                END,
                CASE WHEN p.estado IN ('Entregado', 'Cancelado') THEN p.fecha END DESC,
                p.fecha ASC"
            : 'ORDER BY p.fecha ASC, p.id_pedido ASC';

        $sql = "SELECT
                    p.id_pedido,
                    p.codigo_pedido,
                    p.canal,
                    p.estado,
                    p.tipo_entrega,
                    p.direccion_envio,
                    p.total,
                    p.fecha,
                    p.id_cajero,
                    COALESCE(mp.nombre, p.metodo_pago, '—') AS metodo_pago,
                    p.cliente_nombre AS pedido_cliente_nombre,
                    u.nombre    AS cliente_nombre,
                    u.apellido  AS cliente_apellido,
                    u.correo    AS cliente_correo,
                    u.telefono  AS cliente_telefono,
                    ed.id_envio,
                    ed.id_repartidor,
                    ed.estado_delivery,
                    COALESCE(ed.cobrado_por_repartidor, 0) AS cobrado_por_repartidor,
                    rep.nombre  AS repartidor_nombre,
                    rep.apellido AS repartidor_apellido,
                    TIMESTAMPDIFF(MINUTE, p.fecha, NOW()) AS minutos_espera,
                    (SELECT pv.estado_verificacion
                        FROM pago_verificacion pv
                        WHERE pv.id_pedido = p.id_pedido
                        ORDER BY pv.id_verificacion DESC
                        LIMIT 1) AS pago_estado
                FROM pedido p
                LEFT JOIN metodo_pago mp       ON mp.id_metodo = p.id_metodo
                LEFT JOIN usuario u            ON u.id_usuario = p.id_usuario
                LEFT JOIN envio_delivery ed    ON ed.id_pedido = p.id_pedido
                LEFT JOIN usuario rep          ON rep.id_usuario = ed.id_repartidor
                {$filtroEstado}
                {$orden}";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}
