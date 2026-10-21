<?php
include_once 'Models/modelo_punto_venta.php';
require_once 'Models/cajaModel.php';

class ControladorPuntoVenta {
    private $model;
    private $cajaModel;

    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->model = new ModeloPuntoVenta();
        $this->cajaModel = new CajaModel();
    }

    private function verificarRolCajero() {
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: index.php?action=login');
            exit();
        }

        $rol = $_SESSION['usuario_rol'] ?? '';
        if ($rol !== 'cajero' && $rol !== 'admin') {
            $_SESSION['auth_error'] = 'Acceso no autorizado. Debe iniciar sesión como Cajero.';
            header('Location: index.php?action=login');
            exit();
        }
    }

    private function tokenPerteneceCajaActivaDelUsuario($token) {
        if (!isset($_SESSION['usuario_id']) || trim((string)$token) === '') return false;
        $sesion = $this->cajaModel->obtenerSesionActiva((int)$_SESSION['usuario_id']);
        return $sesion
            && ($sesion['estado'] ?? '') === 'abierta'
            && !empty($sesion['token_emparejamiento'])
            && hash_equals((string)$sesion['token_emparejamiento'], (string)$token);
    }

    private function directorioEstadoEscaner() {
        $dir = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR
            . 'caserita_scanner_' . hash('sha256', __DIR__);
        if (!is_dir($dir)) @mkdir($dir, 0700, true);
        return $dir;
    }

    private function rutaPingEscaner($token) {
        return $this->directorioEstadoEscaner() . DIRECTORY_SEPARATOR
            . 'celular_' . hash('sha256', (string)$token) . '.timestamp';
    }

    private function rutaPingEscanerCompartido() {
        return $this->directorioEstadoEscaner() . DIRECTORY_SEPARATOR . 'celular_activo.timestamp';
    }

    private function guardarEscanerActivo($token, $compartido = true) {
        if ($token === '' || $token === 'general_tienda_pos') return;
        @file_put_contents($this->rutaPingEscaner($token), time());
        if ($compartido) {
            @file_put_contents($this->rutaPingEscanerCompartido(), time());
        }
    }

    public function pos() {
        $this->verificarRolCajero();

        $idUsuario = (int)$_SESSION['usuario_id'];
        $sesionActiva = $this->cajaModel->obtenerSesionActiva($idUsuario);
        if (!$sesionActiva) {
            $_SESSION['flash_error'] = 'Para ingresar a Caja POS y realizar ventas, primero debes aperturar la caja indicando el fondo inicial.';
            $destino = ($_SESSION['usuario_rol'] ?? '') === 'admin' ? 'caja_admin' : 'panel_cajero';
            header('Location: index.php?action=' . $destino);
            exit();
        }

        $sesionCaja = $this->model->obtenerSesionPorToken($sesionActiva['token_emparejamiento']);
        $stampFile = $this->rutaPingEscaner($sesionCaja['token_emparejamiento']);
        $sharedStampFile = $this->rutaPingEscanerCompartido();
        $lastMobilePing = is_file($stampFile) ? (int)@file_get_contents($stampFile) : 0;
        $sharedMobilePing = is_file($sharedStampFile)
            ? (int)@file_get_contents($sharedStampFile)
            : 0;
        $lastMobilePing = max($lastMobilePing, $sharedMobilePing);
        $sesionCaja['estado_conexion'] = time() - $lastMobilePing <= 12 ? 'conectado' : 'esperando';

        include 'Views/Cajero/caja_punto_venta.php';
    }

    private function validarCsrfCaja() {
        $tokenSesion = $_SESSION['csrf_token'] ?? '';
        $tokenRecibido = $_POST['csrf_token'] ?? '';
        if ($tokenSesion === '' || $tokenRecibido === '' || !hash_equals($tokenSesion, (string)$tokenRecibido)) {
            $_SESSION['flash_error'] = 'La sesión de seguridad expiró. Recarga la página.';
            header('Location: index.php?action=arqueo_caja');
            exit();
        }
    }

    public function panelCaja() {
        $this->verificarRolCajero();
        $idUsuario = (int)$_SESSION['usuario_id'];
        $sesionActiva = $this->cajaModel->obtenerSesionActiva($idUsuario);
        $resumen = $sesionActiva ? $this->cajaModel->calcularResumenCaja((int)$sesionActiva['id_sesion']) : null;
        
        $historialVentas = $this->model->obtenerHistorialVentasCajero($idUsuario, 15);
        $totalVentas = $resumen ? (float)$resumen['total_ventas'] : 0.0;
        $totalTickets = $resumen ? (int)$resumen['total_tickets'] : 0;
        $ticketPromedio = $totalTickets > 0 ? ($totalVentas / $totalTickets) : 0.0;

        $nombre = htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Cajero');
        $csrfToken = $_SESSION['csrf_token'] ?? '';
        $flashSuccess = $_SESSION['flash_success'] ?? null;
        $flashError = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        include 'Views/Cajero/dashboard_caja.php';
    }

    public function productosCatalogo() {
        $this->verificarRolCajero();
        $productos = $this->model->listarProductosCatalogo();

        require_once 'Models/productModel.php';
        $productoModel = new ProductoModel();
        $productosStockBajo = $productoModel->getProductosStockBajo();

        $idsStockBajo = array_flip(array_map('intval', array_column($productosStockBajo, 'id_producto')));
        $productosPrioritarios = [];
        $productosRestantes = [];
        foreach ($productos as $producto) {
            if (isset($idsStockBajo[(int)($producto['id_producto'] ?? 0)])) {
                $productosPrioritarios[] = $producto;
            } else {
                $productosRestantes[] = $producto;
            }
        }
        $productos = array_merge($productosPrioritarios, $productosRestantes);

        include 'Views/Cajero/productos.php';
    }

    public function logisticaCajero() {
        $this->verificarRolCajero();
        require_once 'Models/pedidoModel.php';
        $pedidoModel = new PedidoModel();

        // Bandeja unificada de despacho y entregas, incluidos los finalizados.
        $bandejaPedidos = $pedidoModel->getPedidosBandeja(true);

        $nombre = htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Cajero');
        $csrfToken = $_SESSION['csrf_token'] ?? '';
        $flashSuccess = $_SESSION['flash_success'] ?? null;
        $flashError = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        include 'Views/Cajero/logistica.php';
    }

    public function detallePedidoLogisticaCajero() {
        $this->verificarRolCajero();
        header('Content-Type: application/json; charset=utf-8');

        $idPedido = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$idPedido) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'Pedido inválido.']);
            exit();
        }

        require_once 'Models/pedidoModel.php';
        $pedidoModel = new PedidoModel();
        $pedido = $pedidoModel->getPedidoByIdAdmin($idPedido);
        if (!$pedido) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Pedido no encontrado.']);
            exit();
        }

        echo json_encode([
            'success' => true,
            'data' => [
                'pedido' => $pedido,
                'detalle' => $pedidoModel->getDetallePedidoAdmin($idPedido)
            ]
        ]);
        exit();
    }

    public function actualizarEstadoLogisticaCajero() {
        $this->verificarRolCajero();
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
            exit();
        }

        $csrfSesion = $_SESSION['csrf_token'] ?? '';
        $csrfRecibido = (string)($_POST['csrf_token'] ?? '');
        if ($csrfSesion === '' || !hash_equals($csrfSesion, $csrfRecibido)) {
            http_response_code(419);
            echo json_encode(['success' => false, 'message' => 'La sesión expiró. Recarga la página.']);
            exit();
        }

        $idPedido = filter_input(INPUT_POST, 'id_pedido', FILTER_VALIDATE_INT);
        $estado = trim((string)($_POST['estado'] ?? ''));
        $permitidos = ['Pendiente', 'En Preparacion', 'En Camino', 'Listo para Recojo', 'Entregado', 'Cancelado'];
        if (!$idPedido || !in_array($estado, $permitidos, true)) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'Estado o pedido inválido.']);
            exit();
        }

        require_once 'Models/pedidoModel.php';
        require_once 'Models/deliveryModel.php';
        $pedidoModel = new PedidoModel();
        $deliveryModel = new DeliveryModel();
        $pedido = $pedidoModel->getPedidoByIdAdmin($idPedido);
        if (!$pedido) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Pedido no encontrado.']);
            exit();
        }

        $esDelivery = strtolower((string)($pedido['tipo_entrega'] ?? '')) === 'delivery';
        if ($esDelivery && $estado === 'Entregado') {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'Solo el repartidor puede marcar un delivery como entregado.']);
            exit();
        }
        if ($esDelivery && $estado === 'En Camino' && !$deliveryModel->pedidoTieneRepartidor($idPedido)) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'Asigna un repartidor antes de marcar el pedido En Ruta.']);
            exit();
        }

        $estadoAnterior = (string)($pedido['estado'] ?? '');

        if (!$pedidoModel->updateEstadoPedido($idPedido, $estado)) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'No se pudo actualizar el estado.']);
            exit();
        }

        if ($esDelivery) {
            $deliveryModel->sincronizarEstadoPorPedido($idPedido, $estado);
        }

        try {
            require_once __DIR__ . '/../Models/notificacionEmailModel.php';
            (new NotificacionEmailModel())->enviarActualizacionEstado($idPedido, $estado, '', $estadoAnterior);
        } catch (Throwable $e) {
            error_log('No se pudo enviar el aviso de estado del pedido ' . $idPedido . ': ' . $e->getMessage());
        }

        echo json_encode(['success' => true, 'message' => 'Estado actualizado correctamente.']);
        exit();
    }

    public function arqueoCaja() {
        $this->verificarRolCajero();
        $idUsuario = (int)$_SESSION['usuario_id'];
        $sesionActiva = $this->cajaModel->obtenerSesionActiva($idUsuario);
        $resumen = $sesionActiva ? $this->cajaModel->calcularResumenCaja((int)$sesionActiva['id_sesion']) : null;
        $movimientos = $sesionActiva ? $this->cajaModel->listarMovimientos((int)$sesionActiva['id_sesion']) : [];
        $historialSesiones = $this->cajaModel->listarHistorialSesionesUsuario($idUsuario, 20);
        $nombre = htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Cajero');
        $csrfToken = $_SESSION['csrf_token'] ?? '';
        $flashSuccess = $_SESSION['flash_success'] ?? null;
        $flashError = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        include 'Views/Cajero/panel_caja.php';
    }

    public function abrirCajaCajero() {
        $this->verificarRolCajero();
        $this->validarCsrfCaja();
        $monto = filter_input(INPUT_POST, 'monto_apertura', FILTER_VALIDATE_FLOAT);
        if ($monto === false || $monto === null || $monto < 0) {
            $_SESSION['flash_error'] = 'Ingresa un fondo inicial válido.';
        } else {
            $idUsuario = (int)$_SESSION['usuario_id'];
            $resultado = $this->cajaModel->abrirCaja($idUsuario, (float)$monto, 'CAJA-CJ-' . $idUsuario);
            $_SESSION[$resultado['ok'] ? 'flash_success' : 'flash_error'] = $resultado['mensaje'];
        }
        header('Location: index.php?action=arqueo_caja');
        exit();
    }

    public function registrarMovimientoCajaCajero() {
        $this->verificarRolCajero();
        $this->validarCsrfCaja();
        $sesion = $this->cajaModel->obtenerSesionActiva((int)$_SESSION['usuario_id']);
        $tipo = strtoupper(trim($_POST['tipo'] ?? ''));
        $monto = filter_input(INPUT_POST, 'monto', FILTER_VALIDATE_FLOAT);
        $motivo = trim($_POST['motivo'] ?? '');
        if (!$sesion || !in_array($tipo, ['INGRESO', 'EGRESO'], true) || $monto === false || $monto <= 0 || $motivo === '') {
            $_SESSION['flash_error'] = 'Completa el tipo, monto y motivo del movimiento.';
        } else {
            $categoria = trim($_POST['categoria'] ?? 'otro');
            $comprobante = trim($_POST['comprobante'] ?? '');
            $resultado = $this->cajaModel->registrarMovimiento((int)$sesion['id_sesion'], (int)$_SESSION['usuario_id'], $tipo, $categoria, (float)$monto, $motivo, $comprobante);
            $_SESSION[$resultado['ok'] ? 'flash_success' : 'flash_error'] = $resultado['mensaje'];
        }
        header('Location: index.php?action=arqueo_caja');
        exit();
    }

    public function cerrarCajaCajero() {
        $this->verificarRolCajero();
        $this->validarCsrfCaja();
        $sesion = $this->cajaModel->obtenerSesionActiva((int)$_SESSION['usuario_id']);
        $montoReal = filter_input(INPUT_POST, 'monto_cierre_real', FILTER_VALIDATE_FLOAT);
        $observacion = trim($_POST['observacion_cierre'] ?? '');
        if (!$sesion || $montoReal === false || $montoReal === null || $montoReal < 0 || $observacion === '') {
            $_SESSION['flash_error'] = 'Ingresa el efectivo contado y una observación para cerrar tu caja.';
        } else {
            $resultado = $this->cajaModel->cerrarCaja((int)$sesion['id_sesion'], (float)$montoReal, mb_substr($observacion, 0, 500));
            $_SESSION[$resultado['ok'] ? 'flash_success' : 'flash_error'] = $resultado['mensaje'];
        }
        header('Location: index.php?action=arqueo_caja');
        exit();
    }

    public function historial() {
        $this->verificarRolCajero();
        $ventas = $this->model->obtenerHistorialVentasCajero((int)$_SESSION['usuario_id']);
        $nombre = htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Cajero');
        include 'Views/Cajero/historial.php';
    }

    public function apiGenerarQR() {
        $this->verificarRolCajero();
        header('Content-Type: application/json');

        $sesion = $this->cajaModel->obtenerSesionActiva((int)$_SESSION['usuario_id']);
        if (!$sesion) {
            http_response_code(409);
            echo json_encode(['status' => 'error', 'message' => 'Debes abrir la caja desde Arqueo de caja antes de conectar el escáner.']);
            exit();
        }

        $payload = [
            'status' => 'success',
            'caja_id' => $sesion['caja_id'],
            'token' => $sesion['token_emparejamiento'],
            'estado_conexion' => $sesion['estado_conexion'],
            'qr_data' => json_encode([
                'caja_id' => $sesion['caja_id'],
                'token'   => $sesion['token_emparejamiento'],
                'app'     => 'LaCaseritaPOS'
            ])
        ];

        echo json_encode($payload);
        exit();
    }

    public function apiValidarEmparejamiento() {
        header('Content-Type: application/json');
        
        $token = trim($_POST['token'] ?? ($_GET['token'] ?? ''));
        $transporte = strtolower(trim($_POST['transporte'] ?? 'bluetooth'));
        $scannerProbado = filter_var($_POST['scanner_probado'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $camaraActiva = filter_var($_POST['camara_activa'] ?? true, FILTER_VALIDATE_BOOLEAN);
        if (!in_array($transporte, ['bluetooth', 'usb'], true)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Tipo de conexión del lector no válido.']);
            exit();
        }
        $dispositivo = 'Celular (' . ($transporte === 'usb' ? 'Cable USB' : 'Bluetooth');
        if ($scannerProbado) $dispositivo .= ' escaneo probado';
        $dispositivo .= ')';

        require_once 'Models/posScannerModel.php';
        $scannerModel = new PosScannerModel();
        try {
            $esSesionAdministrativa = $scannerModel->isAdministrativeScannerToken($token);
        } catch (RuntimeException $e) {
            error_log('Error leyendo la sesión temporal del escáner administrativo: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'No se pudo validar la sesión del escáner.']);
            exit();
        }
        if ($esSesionAdministrativa) {
            try {
                if (!$scannerModel->touchAdministrativeScannerToken($token)) {
                    http_response_code(409);
                    echo json_encode(['status' => 'error', 'message' => 'La sesión temporal del escáner administrativo expiró.']);
                    exit();
                }
                $this->guardarEscanerActivo($token, false);
                $scannerActivo = $scannerModel->isScannerEnabled($token);
            } catch (RuntimeException $e) {
                error_log('Error validando la sesión temporal del escáner administrativo: ' . $e->getMessage());
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'No se pudo validar la conexión del escáner administrativo.']);
                exit();
            }
            echo json_encode([
                'status' => 'success',
                'token' => $token,
                'dispositivo_info' => $dispositivo,
                'scanner_activo' => $scannerActivo,
                'camara_activa' => $camaraActiva,
                'message' => 'Escáner administrativo emparejado correctamente'
            ]);
            exit();
        }

        if (empty($token)) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Token de emparejamiento faltante']);
            exit();
        }

        $exito = $this->model->validarEmparejamiento($token, $dispositivo);
        if ($exito) {
            $this->guardarEscanerActivo($token);
            try {
                $scannerActivo = $scannerModel->isScannerEnabled($token);
            } catch (RuntimeException $e) {
                error_log('Error consultando el estado temporal del escáner: ' . $e->getMessage());
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'No se pudo consultar el estado del escáner.']);
                exit();
            }
            if (!$scannerActivo || !$camaraActiva) {
                $this->model->desconectarEmparejamiento($token);
            }
            echo json_encode([
                'status' => 'success',
                'token' => $token,
                'dispositivo_info' => $dispositivo,
                'scanner_activo' => $scannerActivo,
                'camara_activa' => $camaraActiva,
                'message' => 'Escáner emparejado correctamente con la caja'
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Token de caja inválido o expirado']);
        }
        exit();
    }

    public function apiEstadoSesion() {
        header('Content-Type: application/json');

        $token = trim($_GET['token'] ?? ($_POST['token'] ?? ''));
        if (isset($_POST['scanner_activo'])) {
            $activo = filter_var($_POST['scanner_activo'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($activo === null) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'No se pudo actualizar el estado del escáner.']);
                exit();
            }
            $destinoSolicitado = trim((string)($_POST['scanner_destino'] ?? 'pos'));
            if (!in_array($destinoSolicitado, ['pos', 'campo'], true)) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'El destino del escáner no es válido.']);
                exit();
            }
            require_once 'Models/posScannerModel.php';
            $scannerModel = new PosScannerModel();
            $esAdmin = ($_SESSION['usuario_rol'] ?? '') === 'admin';
            $userId = (int)($_SESSION['usuario_id'] ?? 0);

            if ($esAdmin && $destinoSolicitado === 'campo') {
                try {
                    $token = $scannerModel->getOrCreateAdministrativeScannerToken($userId);
                } catch (RuntimeException $e) {
                    error_log('Error creando la sesión temporal del escáner administrativo: ' . $e->getMessage());
                    http_response_code(500);
                    echo json_encode(['status' => 'error', 'message' => 'No se pudo preparar la sesión del escáner administrativo.']);
                    exit();
                }
            }

            try {
                $esSesionAdministrativa = $esAdmin && $scannerModel->isAdministrativeScannerToken($token, $userId);
            } catch (RuntimeException $e) {
                error_log('Error leyendo la sesión administrativa del escáner: ' . $e->getMessage());
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'No se pudo validar la sesión del escáner administrativo.']);
                exit();
            }
            if ($esSesionAdministrativa) {
                try {
                    if ($activo) {
                        $scannerModel->setScannerDestination($token, $destinoSolicitado);
                        $scannerModel->setScannerEnabled($token, true);
                        $scannerModel->touchAdministrativeScannerToken($token);
                    } else {
                        $scannerModel->setScannerEnabled($token, false);
                        $scannerModel->setScannerDestination($token, 'pos');
                    }
                } catch (RuntimeException $e) {
                    error_log('Error actualizando la sesión temporal del escáner administrativo: ' . $e->getMessage());
                    http_response_code(500);
                    echo json_encode(['status' => 'error', 'message' => 'No se pudo actualizar el escáner administrativo.']);
                    exit();
                }
                echo json_encode([
                    'status' => 'success',
                    'scanner_activo' => $activo,
                    'scanner_destino' => $activo ? $destinoSolicitado : 'pos',
                    'token' => $token
                ]);
                exit();
            }

            $sesionScanner = $token !== '' ? $this->model->obtenerSesionPorToken($token) : null;
            if (!$sesionScanner || ($sesionScanner['estado'] ?? '') !== 'abierta'
                || (!$esAdmin && !$this->tokenPerteneceCajaActivaDelUsuario($token))) {
                http_response_code(403);
                echo json_encode(['status' => 'error', 'message' => 'No tienes permiso para cambiar el estado de este escáner.']);
                exit();
            }
            try {
                if ($activo) {
                    $scannerModel->setScannerDestination($token, $destinoSolicitado);
                    $scannerModel->setScannerEnabled($token, true);
                } else {
                    $scannerModel->setScannerEnabled($token, false);
                    $scannerModel->setScannerDestination($token, 'pos');
                    $this->model->desconectarEmparejamiento($token);
                }
            } catch (RuntimeException $e) {
                error_log('Error actualizando el estado temporal del escáner: ' . $e->getMessage());
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'No se pudo actualizar el estado del escáner.']);
                exit();
            }
            echo json_encode([
                'status' => 'success',
                'scanner_activo' => $activo,
                'scanner_destino' => $activo ? $destinoSolicitado : 'pos',
                'token' => $token
            ]);
            exit();
        }

        require_once 'Models/posScannerModel.php';
        $scannerModel = new PosScannerModel();
        try {
            $esSesionAdministrativa = $token !== '' && $scannerModel->isAdministrativeScannerToken($token);
        } catch (RuntimeException $e) {
            error_log('Error leyendo la sesión temporal del escáner administrativo: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'No se pudo validar la sesión del escáner administrativo.']);
            exit();
        }
        if ($esSesionAdministrativa) {
            try {
                if (!$scannerModel->touchAdministrativeScannerToken($token)) {
                    http_response_code(409);
                    echo json_encode(['status' => 'error', 'message' => 'La sesión temporal del escáner administrativo expiró.']);
                    exit();
                }
                $scannerActivo = $scannerModel->isScannerEnabled($token);
                $scannerDestino = $scannerModel->getScannerDestination($token);
            } catch (RuntimeException $e) {
                error_log('Error consultando el estado temporal del escáner administrativo: ' . $e->getMessage());
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'No se pudo consultar el estado del escáner administrativo.']);
                exit();
            }
            $stampFile = $this->rutaPingEscaner($token);
            $lastMobilePing = is_file($stampFile) ? (int)@file_get_contents($stampFile) : 0;
            echo json_encode([
                'status' => 'success',
                'token' => $token,
                'estado_conexion' => time() - $lastMobilePing <= 12 ? 'conectado' : 'esperando',
                'dispositivo_info' => 'Escáner administrativo',
                'scanner_activo' => $scannerActivo,
                'scanner_destino' => $scannerDestino,
                'total_venta' => 0,
                'items' => []
            ]);
            exit();
        }

        if (empty($token) && isset($_SESSION['usuario_id'])) {
            $sesion = $this->cajaModel->obtenerSesionActiva((int)$_SESSION['usuario_id']);
            if (!$sesion && ($_SESSION['usuario_rol'] ?? '') !== 'admin') {
                http_response_code(409);
                echo json_encode(['status' => 'error', 'message' => 'Debes abrir la caja desde Arqueo de caja antes de vender.']);
                exit();
            }
            if ($sesion) {
                $token = $sesion['token_emparejamiento'] ?? '';
            }
        }

        if (empty($token)) {
            require_once 'Core/dataBase.php';
            $db = (new DataBase())->getConnection();
            $stmt = $db->query("SELECT token_emparejamiento FROM sesion_caja WHERE estado = 'abierta' ORDER BY id_sesion DESC LIMIT 1");
            $r = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($r && !empty($r['token_emparejamiento'])) {
                $token = $r['token_emparejamiento'];
            }
        }

        if (empty($token)) {
            echo json_encode(['status' => 'error', 'message' => 'Sin token de sesión']);
            exit();
        }

        $sesionData = $this->model->obtenerSesionPorToken($token);
        if (!$sesionData) {
            echo json_encode(['status' => 'error', 'message' => 'Sesión no encontrada']);
            exit();
        }

        try {
            $scannerActivo = $scannerModel->isScannerEnabled($token);
            $scannerDestino = $scannerModel->getScannerDestination($token);
        } catch (RuntimeException $e) {
            error_log('Error consultando el estado temporal del escáner: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'No se pudo consultar el estado del escáner.']);
            exit();
        }
        $items = json_decode($sesionData['items_json'] ?? '[]', true);
        if (is_array($items) && !empty($items)) {
            $idsToFix = [];
            foreach ($items as $it) {
                $idItem = (int)($it['id_producto'] ?? 0);
                if ($idItem <= 0) continue;
                $needsImg = empty($it['imagen']) && empty($it['imagen_url']);
                $needsName = trim((string)($it['nombre'] ?? '')) === '';
                $needsPrevPrice = !array_key_exists('precio_anterior', $it);
                if ($needsImg || $needsName || $needsPrevPrice) {
                    $idsToFix[] = $idItem;
                }
            }
            $idsToFix = array_values(array_unique($idsToFix));
            if (!empty($idsToFix)) {
                require_once 'Core/dataBase.php';
                $dbTmp = (new DataBase())->getConnection();
                $placeholders = implode(',', array_fill(0, count($idsToFix), '?'));
                $stFix = $dbTmp->prepare("SELECT id_producto,
                        COALESCE(NULLIF(imagen, ''), NULLIF(imagen_url, '')) AS img,
                        COALESCE(NULLIF(nombre, ''), titulo) AS nombre,
                        precio_anterior
                    FROM producto WHERE id_producto IN ($placeholders)");
                $stFix->execute($idsToFix);
                $mapFix = [];
                foreach ($stFix->fetchAll(PDO::FETCH_ASSOC) as $row) {
                    $mapFix[(int)$row['id_producto']] = $row;
                }
                $updatedAny = false;
                foreach ($items as &$it) {
                    $idItem = (int)($it['id_producto'] ?? 0);
                    if (!isset($mapFix[$idItem])) continue;
                    if ((empty($it['imagen']) && empty($it['imagen_url'])) && !empty($mapFix[$idItem]['img'])) {
                        $it['imagen'] = $mapFix[$idItem]['img'];
                        $it['imagen_url'] = $mapFix[$idItem]['img'];
                        $updatedAny = true;
                    }
                    if (trim((string)($it['nombre'] ?? '')) === '' && trim((string)($mapFix[$idItem]['nombre'] ?? '')) !== '') {
                        $it['nombre'] = $mapFix[$idItem]['nombre'];
                        $updatedAny = true;
                    }
                    if (!array_key_exists('precio_anterior', $it)) {
                        $it['precio_anterior'] = $mapFix[$idItem]['precio_anterior'] !== null
                            ? (float)$mapFix[$idItem]['precio_anterior']
                            : null;
                        $updatedAny = true;
                    }
                }
                unset($it);
                if ($updatedAny) {
                    $stmtSave = $dbTmp->prepare("UPDATE sesion_caja SET items_json = :items WHERE token_emparejamiento = :token");
                    $stmtSave->execute([':items' => json_encode($items), ':token' => $token]);
                }
            }
        }

        $stampFile = $this->rutaPingEscaner($token);
        $sharedStampFile = $this->rutaPingEscanerCompartido();
        $lastMobilePing = is_file($stampFile) ? (int)@file_get_contents($stampFile) : 0;
        $sharedMobilePing = is_file($sharedStampFile)
            ? (int)@file_get_contents($sharedStampFile)
            : 0;
        $lastMobilePing = max($lastMobilePing, $sharedMobilePing);
        $connectionState = time() - $lastMobilePing <= 12 ? $sesionData['estado_conexion'] : 'esperando';

        echo json_encode([
            'status' => 'success',
            'token' => $sesionData['token_emparejamiento'],
            'estado_conexion' => $connectionState,
            'dispositivo_info' => $sesionData['dispositivo_info'],
            'scanner_activo' => $scannerActivo,
            'scanner_destino' => $scannerDestino,
            'total_venta' => (float)$sesionData['total_venta'],
            'items' => $items
        ]);
        exit();
    }

    public function apiBuscarProductos() {
        header('Content-Type: application/json');

        $query = trim($_GET['q'] ?? ($_GET['term'] ?? ($_POST['q'] ?? '')));
        if ($query === '') {
            echo json_encode(['status' => 'success', 'data' => []]);
            exit();
        }

        try {
            $productos = $this->model->buscarProductosManual($query);
            echo json_encode([
                'status' => 'success',
                'data' => $productos ?: []
            ]);
        } catch (\Throwable $e) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Error al buscar productos: ' . $e->getMessage()
            ]);
        }
        exit();
    }

    public function apiEscanearProducto() {
        header('Content-Type: application/json');

        $token = filter_input(INPUT_POST, 'token', FILTER_SANITIZE_SPECIAL_CHARS);
        $idProducto = filter_input(INPUT_POST, 'id_producto', FILTER_VALIDATE_INT);
        $codigoBarras = filter_input(INPUT_POST, 'codigo_barras', FILTER_SANITIZE_SPECIAL_CHARS);

        if (!$token || (!$codigoBarras && !$idProducto)) {
            echo json_encode(['status' => 'error', 'message' => 'Parámetros incompletos (token y código son requeridos)']);
            exit();
        }

        if (!$this->tokenPerteneceCajaActivaDelUsuario($token)) {
            http_response_code(409);
            echo json_encode(['status' => 'error', 'message' => 'Abre tu caja desde Arqueo de caja antes de agregar productos.']);
            exit();
        }

        $resultado = $this->model->agregarProductoPorCodigo($token, $idProducto ?: $codigoBarras, (bool)$idProducto);
        echo json_encode($resultado);
        exit();
    }

    public function apiActualizarCantidad() {
        header('Content-Type: application/json');

        $token = filter_input(INPUT_POST, 'token', FILTER_SANITIZE_SPECIAL_CHARS);
        $idProducto = filter_input(INPUT_POST, 'id_producto', FILTER_VALIDATE_INT);
        $cantidad = filter_input(INPUT_POST, 'cantidad', FILTER_VALIDATE_INT);

        if (!$token || !$idProducto || $cantidad === null) {
            echo json_encode(['status' => 'error', 'message' => 'Datos de ítem inválidos']);
            exit();
        }

        if (!$this->tokenPerteneceCajaActivaDelUsuario($token)) {
            http_response_code(409);
            echo json_encode(['status' => 'error', 'message' => 'Abre tu caja desde Arqueo de caja antes de modificar productos.']);
            exit();
        }

        $res = $this->model->actualizarCantidadItem($token, $idProducto, $cantidad);
        if ($res === false) {
            echo json_encode(['status' => 'error', 'message' => 'La caja está cerrada o ya no existe.']);
            exit();
        }
        if (is_array($res) && !empty($res['error'])) {
            echo json_encode(['status' => 'error', 'message' => $res['message'] ?? 'Stock insuficiente']);
            exit();
        }
        echo json_encode(['status' => 'success', 'data' => $res]);
        exit();
    }

    public function apiProcesarVenta() {
        header('Content-Type: application/json');

        $token = filter_input(INPUT_POST, 'token', FILTER_SANITIZE_SPECIAL_CHARS);
        $metodoPago = filter_input(INPUT_POST, 'metodo_pago', FILTER_SANITIZE_SPECIAL_CHARS) ?? 'efectivo';
        $montoRecibido = filter_input(INPUT_POST, 'monto_recibido', FILTER_VALIDATE_FLOAT) ?? 0;
        $clienteDni = filter_input(INPUT_POST, 'cliente_dni', FILTER_SANITIZE_SPECIAL_CHARS) ?? '00000000';
        $clienteNombre = filter_input(INPUT_POST, 'cliente_nombre', FILTER_SANITIZE_SPECIAL_CHARS) ?? 'Público General';

        // Pagos divididos (uno o varios métodos por venta)
        $pagosRaw = $_POST['pagos'] ?? '';
        $pagos = [];
        if (is_string($pagosRaw) && $pagosRaw !== '') {
            $decoded = json_decode($pagosRaw, true);
            if (is_array($decoded)) {
                $pagos = $decoded;
            }
        } elseif (is_array($pagosRaw)) {
            $pagos = $pagosRaw;
        }

        if (!$token) {
            echo json_encode(['status' => 'error', 'message' => 'Token de sesión requerido']);
            exit();
        }

        if (!$this->tokenPerteneceCajaActivaDelUsuario($token)) {
            http_response_code(409);
            echo json_encode(['status' => 'error', 'message' => 'Debes abrir tu caja desde Arqueo de caja antes de procesar una venta.']);
            exit();
        }

        $resultado = $this->model->finalizarVenta($token, $metodoPago, $montoRecibido, $clienteDni, $clienteNombre, $pagos);
        echo json_encode($resultado);
        exit();
    }

    public function apiConsultarDni() {
        header('Content-Type: application/json');

         $documento = trim($_GET['documento'] ?? $_GET['dni'] ?? $_POST['documento'] ?? $_POST['dni'] ?? '');

        require_once 'Models/clienteDniModel.php';
        $dniModel = new ClienteDniModel();
         $resultado = $dniModel->consultarDocumento($documento);
        echo json_encode($resultado);
        exit();
    }

    public function vistaEscanerMovil() {
        $token = trim($_GET['token'] ?? ($_GET['amp;token'] ?? ''));
        if ($token === '') {
            http_response_code(403);
        }
        include 'Views/Cajero/escaner_movil.php';
        exit();
    }

    public function apiEscanearMovil() {
        header('Content-Type: application/json');

        $token = trim($_POST['token'] ?? ($_POST['amp;token'] ?? ($_GET['token'] ?? ($_GET['amp;token'] ?? ''))));
        $codigo = trim($_POST['codigo'] ?? ($_POST['codigo_barras'] ?? ($_GET['codigo'] ?? ($_GET['codigo_barras'] ?? ''))));
        $dispositivo = trim($_POST['dispositivo'] ?? 'Celular Lector (Cable USB)');

        require_once 'Models/posScannerModel.php';
        $scannerModel = new PosScannerModel();
        try {
            $esSesionAdministrativa = $scannerModel->isAdministrativeScannerToken($token);
        } catch (RuntimeException $e) {
            error_log('Error validando la sesión temporal del escáner: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['ok' => false, 'mensaje' => 'No se pudo validar la sesión del escáner.']);
            exit();
        }
        $sesionToken = $token !== '' ? $this->model->obtenerSesionPorToken($token) : null;
        if (!$esSesionAdministrativa && (!$sesionToken || ($sesionToken['estado'] ?? '') !== 'abierta')) {
            http_response_code(409);
            echo json_encode(['ok' => false, 'mensaje' => 'La sesión del escáner no es válida o expiró.']);
            exit();
        }

        if (empty($codigo)) {
            echo json_encode(['ok' => false, 'mensaje' => 'Parámetro código requerido']);
            exit();
        }

        try {
            $resultado = $scannerModel->registrarEscaneoRemoto($token, $codigo, $dispositivo);
        } catch (RuntimeException $e) {
            error_log('Error registrando un escaneo temporal: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['ok' => false, 'mensaje' => 'No se pudo guardar el código escaneado.']);
            exit();
        }
        if (!empty($resultado['ok'])) $this->guardarEscanerActivo($token, !$esSesionAdministrativa);
        echo json_encode($resultado);
        exit();
    }

    public function apiObtenerEscaneosPendientes() {
        header('Content-Type: application/json');

        $token = trim($_GET['token'] ?? ($_GET['amp;token'] ?? ''));
        if ($token === '') {
            echo json_encode(['status' => 'success', 'data' => []]);
            exit();
        }
        require_once 'Models/posScannerModel.php';
        $scannerModel = new PosScannerModel();
        try {
            $esSesionAdministrativa = $scannerModel->isAdministrativeScannerToken($token);
        } catch (RuntimeException $e) {
            error_log('Error validando la sesión temporal del escáner: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'No se pudo validar la sesión del escáner.']);
            exit();
        }
        $sesion = $esSesionAdministrativa ? null : ($token !== '' ? $this->model->obtenerSesionPorToken($token) : null);
        if (!$esSesionAdministrativa && (!$sesion || ($sesion['estado'] ?? '') !== 'abierta')) {
            echo json_encode(['status' => 'success', 'data' => []]);
            exit();
        }

        $destinoSolicitado = trim((string)($_GET['scanner_destino'] ?? 'pos'));
        if (!in_array($destinoSolicitado, ['pos', 'campo'], true)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'data' => [], 'message' => 'El destino del escáner no es válido.']);
            exit();
        }
        try {
            if ($scannerModel->getScannerDestination($token) !== $destinoSolicitado) {
                echo json_encode(['status' => 'success', 'data' => []]);
                exit();
            }
            $pendientes = $scannerModel->obtenerEscaneosPendientes($token);
        } catch (RuntimeException $e) {
            error_log('Error leyendo la cola temporal de escaneos: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'No se pudo leer la cola temporal de escaneos.']);
            exit();
        }
        echo json_encode(['status' => 'success', 'data' => $pendientes]);
        exit();
    }

    public function apiAbrirLectorEnCelular() {
        header('Content-Type: application/json');
        $rol = $_SESSION['usuario_rol'] ?? '';
        if (!in_array($rol, ['admin', 'cajero'], true)) {
            http_response_code(403);
            echo json_encode(['ok' => false, 'mensaje' => 'No tienes permiso para abrir el escáner en el celular.']);
            exit();
        }

        $transporte = strtolower(trim($_POST['transporte'] ?? ''));
        if ($transporte !== 'usb') {
            http_response_code(400);
            echo json_encode(['ok' => false, 'mensaje' => 'La apertura automática está disponible solo para conexión por cable USB.']);
            exit();
        }

        $tokenEnviado = trim($_POST['token'] ?? '');
        $sesionAdministrativa = false;
        if ($rol === 'admin') {
            require_once 'Models/posScannerModel.php';
            $scannerModel = new PosScannerModel();
            try {
                $userId = (int)($_SESSION['usuario_id'] ?? 0);
                if ($scannerModel->isAdministrativeScannerToken($tokenEnviado, $userId)) {
                    $token = $tokenEnviado;
                } else {
                    $token = $scannerModel->getOrCreateAdministrativeScannerToken($userId);
                }
                $sesionAdministrativa = true;
            } catch (RuntimeException $e) {
                error_log('Error preparando el escáner USB administrativo: ' . $e->getMessage());
                http_response_code(500);
                echo json_encode(['ok' => false, 'mensaje' => 'No se pudo preparar la sesión temporal del escáner administrativo.']);
                exit();
            }
            $sesion = null;
        } else {
            $sesion = $this->cajaModel->obtenerSesionActiva((int)$_SESSION['usuario_id']);
            if (!$sesion || ($tokenEnviado !== '' && !hash_equals((string)$sesion['token_emparejamiento'], $tokenEnviado))) {
                http_response_code(409);
                echo json_encode(['ok' => false, 'mensaje' => 'Abre tu caja desde Arqueo de caja antes de conectar el escáner.']);
                exit();
            }
            $token = (string)$sesion['token_emparejamiento'];
        }
        if (!$sesion && !$sesionAdministrativa) {
            http_response_code(409);
            echo json_encode(['ok' => false, 'mensaje' => 'Abre tu caja desde Arqueo de caja antes de conectar el escáner.']);
            exit();
        }

        $adbCandidates = [
            'C:\\Users\\smt2d\\AppData\\Local\\Android\\Sdk\\platform-tools\\adb.exe',
            (getenv('LOCALAPPDATA') ? getenv('LOCALAPPDATA') . '\\Android\\Sdk\\platform-tools\\adb.exe' : ''),
            'C:\\platform-tools\\adb.exe'
        ];
        $adb = '';
        foreach ($adbCandidates as $cand) {
            if (!empty($cand) && file_exists($cand)) {
                $adb = $cand;
                break;
            }
        }

        if (empty($adb)) {
            http_response_code(503);
            echo json_encode(['ok' => false, 'mensaje' => 'No se encontró ADB en la PC. Instala Android Platform Tools para abrir el escáner por cable.']);
            exit();
        }

        $adbProfile = dirname($adb, 6);
        $adbKey = $adbProfile . DIRECTORY_SEPARATOR . '.android' . DIRECTORY_SEPARATOR . 'adbkey';
        if (!is_file($adbKey)) {
            http_response_code(503);
            echo json_encode(['ok' => false, 'mensaje' => 'No se encontró la clave ADB del usuario de Windows. Ejecuta adb devices una vez desde la sesión de Windows que usa el teléfono y vuelve a intentar.']);
            exit();
        }

        // Apache puede ejecutarse como LocalSystem; entrega a ADB la clave del usuario que instaló Platform Tools.
        putenv('ADB_VENDOR_KEYS=' . $adbKey);
        putenv('ANDROID_USER_HOME=' . $adbProfile . DIRECTORY_SEPARATOR . '.android');
        $adbCommand = '"' . $adb . '"';
        exec($adbCommand . ' devices 2>&1', $devicesOutput, $devicesExitCode);
        $devicesResult = implode("\n", $devicesOutput);
        if ($devicesExitCode !== 0) {
            http_response_code(503);
            echo json_encode(['ok' => false, 'mensaje' => 'ADB no pudo iniciar. Verifica la instalación de Android Platform Tools.']);
            exit();
        }
        if (stripos($devicesResult, 'unauthorized') !== false) {
            http_response_code(409);
            echo json_encode(['ok' => false, 'mensaje' => 'La depuración USB está activa, pero Android todavía no autorizó esta PC. Desbloquea el celular y acepta “¿Permitir depuración USB?” (marca “Permitir siempre”). Si no aparece el aviso, Apache está ejecutándose como servicio LocalSystem: reinicia ADB desde tu sesión de Windows y vuelve a conectar el cable. También puedes revocar las autorizaciones USB en Opciones de desarrollador y reconectar.']);
            exit();
        }
        if (stripos($devicesResult, 'offline') !== false) {
            http_response_code(409);
            echo json_encode(['ok' => false, 'mensaje' => 'ADB detecta el teléfono sin conexión. Desconecta y vuelve a conectar el cable USB con el teléfono desbloqueado.']);
            exit();
        }
        if (!preg_match('/^\s*\S+\s+device(?:\s|$)/m', $devicesResult)) {
            http_response_code(409);
            echo json_encode(['ok' => false, 'mensaje' => 'No se detectó un teléfono autorizado por ADB. Conéctalo con un cable de datos, activa Depuración por USB en Opciones de desarrollador y acepta el aviso en el teléfono.']);
            exit();
        }

        exec($adbCommand . ' reverse tcp:8080 tcp:80 2>&1', $reverseOutput, $reverseExitCode);
        $reverseResult = implode("\n", $reverseOutput);
        if ($reverseExitCode !== 0 || stripos($reverseResult, 'error:') !== false) {
            error_log('ADB reverse falló: ' . $reverseResult);
            http_response_code(409);
            echo json_encode(['ok' => false, 'mensaje' => 'ADB detectó el teléfono, pero no pudo crear el túnel USB. Desbloquea el teléfono, confirma la autorización de depuración y vuelve a conectar el cable.']);
            exit();
        }

        $appPath = parse_url($_SERVER['REQUEST_URI'] ?? '/index.php', PHP_URL_PATH);
        if (!is_string($appPath) || $appPath === '') $appPath = '/index.php';
        $appPath = str_replace(' ', '%20', $appPath);
        $url = 'http://localhost:8080' . $appPath . '?action=pos_escaner_movil&token=' . rawurlencode($token) . '&transporte=usb&scanner_destino=campo';
        $cmd = $adbCommand . ' shell "am start -a android.intent.action.VIEW -d \'' . $url . '\' "';
        exec($cmd . ' 2>&1', $openOutput, $openExitCode);
        $openResult = implode("\n", $openOutput);
        if ($openExitCode !== 0 || stripos($openResult, 'Error:') !== false || stripos($openResult, 'Exception') !== false) {
            error_log('ADB no pudo abrir el lector móvil: ' . $openResult);
            http_response_code(409);
            echo json_encode(['ok' => false, 'mensaje' => 'El teléfono está autorizado, pero Android no pudo abrir el lector. Comprueba que tenga un navegador instalado y vuelve a intentarlo.']);
            exit();
        }

        echo json_encode([
            'ok' => true,
            'mensaje' => 'Se solicitó abrir el lector en el teléfono. Acepta el permiso de cámara del navegador si aparece.',
            'url' => $url,
            'token' => $token
        ]);
        exit();
    }
}
?>
