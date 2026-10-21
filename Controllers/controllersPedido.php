<?php
require_once 'Models/pedidoModel.php';
require_once 'Core/ajaxHelper.php';

class ControllerPedido{
    private $pedidoModel;

    public function __construct(){
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->pedidoModel = new PedidoModel();
    }

    private function validarSesion(){
        if (!isset($_SESSION['usuario_id'])) {
            if (AjaxHelper::isAjax()) {
                AjaxHelper::jsonError(
                    'Tu sesión ha expirado.',
                    401
                );
            }

            header('Location: index.php?action=login');
            exit();
        }
    }

    public function listarPedidosUsuario(){
        $this->validarSesion();

        $idUsuario =
            (int)$_SESSION['usuario_id'];

        $pedidos = $this->pedidoModel
            ->getPedidosByUsuario($idUsuario);

        if (AjaxHelper::isAjax()) {
            AjaxHelper::jsonResponse(
                true,
                'Pedidos cargados correctamente.',
                [
                    'pedidos' => $pedidos
                ]
            );
        }

        include 'Views/Cuenta/partials/pedidos.php';
    }

    public function marcarPedidoLeido()
    {
        $this->validarSesion();
        $idUsuario = (int)$_SESSION['usuario_id'];
        $idPedido = (int)($_POST['id_pedido'] ?? ($_GET['id_pedido'] ?? 0));
        if ($idPedido > 0) {
            $this->pedidoModel->marcarPedidoLeido($idPedido, $idUsuario);
        }
        $restantes = $this->pedidoModel->obtenerCantidadPedidosNoLeidos($idUsuario);
        if (AjaxHelper::isAjax()) {
            AjaxHelper::jsonResponse(true, 'Pedido marcado como leído', ['restantes' => $restantes]);
        }
        header('Location: index.php?action=home_usuario');
        exit();
    }

    public function marcarTodosPedidosLeidos()
    {
        $this->validarSesion();
        $idUsuario = (int)$_SESSION['usuario_id'];
        $this->pedidoModel->marcarTodosPedidosLeidos($idUsuario);
        if (AjaxHelper::isAjax()) {
            AjaxHelper::jsonResponse(true, 'Todos los pedidos marcados como leídos', ['restantes' => 0]);
        }
        header('Location: index.php?action=home_usuario');
        exit();
    }

    public function detallePedidoUsuario(){
        $this->validarSesion();

        $idUsuario =
            (int)$_SESSION['usuario_id'];

        $idPedido = filter_input(
            INPUT_GET,
            'id',
            FILTER_VALIDATE_INT
        );

        if (!$idPedido) {
            if (AjaxHelper::isAjax()) {
                AjaxHelper::jsonError(
                    'Pedido inválido.',
                    422
                );
            }

            $_SESSION['flash_error'] =
                'Pedido inválido.';

            header('Location: index.php?action=mi_cuenta');
            exit();
        }

        $pedido = $this->pedidoModel
            ->getPedidoByIdAndUsuario(
                (int)$idPedido,
                $idUsuario
            );

        if (!$pedido) {
            if (AjaxHelper::isAjax()) {
                AjaxHelper::jsonError(
                    'El pedido no existe o no te pertenece.',
                    404
                );
            }

            $_SESSION['flash_error'] =
                'Pedido no encontrado.';

            header('Location: index.php?action=mi_cuenta');
            exit();
        }

        // Marcar automáticamente como leído al abrir el detalle
        $this->pedidoModel->marcarPedidoLeido((int)$idPedido, $idUsuario);

        $detalle = $this->pedidoModel
            ->getDetallePedido(
                (int)$idPedido,
                $idUsuario
            );

        if (AjaxHelper::isAjax()) {
            AjaxHelper::jsonResponse(
                true,
                'Detalle del pedido cargado.',
                [
                    'pedido' => $pedido,
                    'detalle' => $detalle
                ]
            );
        }

        include 'Views/Cuenta/detalle_pedido.php';
    }

    public function crearPedido(){
        $this->validarSesion();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->responderErrorPedido(
                'Método de solicitud no permitido.',
                405
            );
        }

        $tokenSesion =
            $_SESSION['csrf_token'] ?? '';

        $tokenRecibido =
            $_POST['csrf_token'] ?? '';

        if (
            $tokenSesion === '' ||
            $tokenRecibido === '' ||
            !hash_equals(
                $tokenSesion,
                $tokenRecibido
            )
        ) {
            $this->responderErrorPedido(
                'El token de seguridad es inválido o ha expirado.',
                419
            );
        }

        $idUsuario =
            (int)$_SESSION['usuario_id'];

        $idMetodo = filter_input(
            INPUT_POST,
            'id_metodo',
            FILTER_VALIDATE_INT
        ) ?: (isset($_POST['id_metodo']) && is_numeric($_POST['id_metodo']) ? (int)$_POST['id_metodo'] : null);

        $idDireccion = filter_input(
            INPUT_POST,
            'id_direccion',
            FILTER_VALIDATE_INT
        ) ?: (isset($_POST['id_direccion']) && is_numeric($_POST['id_direccion']) ? (int)$_POST['id_direccion'] : null);

        $tipoEntrega = trim(
            $_POST['tipo_entrega'] ?? ''
        );

        $observacion = trim(
            $_POST['observacion'] ?? ''
        );

        if (!in_array(
            $tipoEntrega,
            ['delivery', 'recojo'],
            true
        )) {
            $this->responderErrorPedido(
                'Selecciona un tipo de entrega válido.',
                422
            );
        }

        if (!$idMetodo) {
            $this->responderErrorPedido(
                'Selecciona un método de pago válido.',
                422
            );
        }

        if (strlen($observacion) > 255) {
            $this->responderErrorPedido(
                'La observación no puede superar los 255 caracteres.',
                422
            );
        }

        if ($tipoEntrega === 'delivery') {
            require_once 'Models/direccionModel.php';
            $direccionModel = new DireccionModel();

            $calle = trim($_POST['calle'] ?? '');
            $distrito = trim($_POST['distrito'] ?? '');
            $numero = trim($_POST['numero'] ?? '');
            $loteManzana = trim($_POST['lote_manzana'] ?? '');
            $referencia = trim($_POST['referencia'] ?? '');
            $idDistrito = filter_input(INPUT_POST, 'id_distrito', FILTER_VALIDATE_INT) ?: null;

            // Si el usuario envió los campos de dirección directamente en el formulario
            if (!$idDireccion && !empty($calle) && !empty($distrito)) {
                $idNueva = $direccionModel->crearDireccionCompleta(
                    $idUsuario,
                    'Lambayeque',
                    'Chiclayo',
                    $distrito,
                    $calle,
                    $referencia,
                    1,
                    $numero,
                    $loteManzana,
                    $idDistrito
                );
                if ($idNueva) {
                    $idDireccion = $idNueva;
                }
            }

            if (!$idDireccion) {
                $this->responderErrorPedido(
                    'Ingresa o selecciona una dirección de entrega válida.',
                    422
                );
            }

            $direccion = $direccionModel->getDireccionById((int)$idDireccion, $idUsuario);

            if (!$direccion) {
                $this->responderErrorPedido(
                    'La dirección no existe o no pertenece a tu cuenta.',
                    403
                );
            }
        } else {
            $idDireccion = null;
        }

        $carrito =
            isset($_SESSION['carritos'][$idUsuario]) &&
            is_array($_SESSION['carritos'][$idUsuario])
                ? $_SESSION['carritos'][$idUsuario]
                : [];

        if (empty($carrito)) {
            $this->responderErrorPedido(
                'Tu carrito está vacío.',
                422
            );
        }

        // Verificar si el método de pago requiere comprobante o datos directos
        $metodoInfo = $this->pedidoModel->getMetodoPagoById((int)$idMetodo);
        $requiereVoucher = !empty($metodoInfo['requiere_comprobante']);

        $celularYP = trim($_POST['celular_yape_plin'] ?? $_POST['celular_remitente'] ?? '');
        $codigoYP = trim($_POST['codigo_yape_plin'] ?? $_POST['codigo_operacion'] ?? '');
        $billetera = trim($_POST['billetera_seleccionada'] ?? 'Yape');

        $ccNumero = trim($_POST['cc_numero'] ?? '');
        $ccNombre = trim($_POST['cc_nombre'] ?? '');

        $nombreMetodo = strtolower($metodoInfo['nombre'] ?? '');
        $esYapePlin = (strpos($nombreMetodo, 'yape') !== false || strpos($nombreMetodo, 'plin') !== false);
        $esTarjeta = (strpos($nombreMetodo, 'tarjeta') !== false || strpos($nombreMetodo, 'crédito') !== false || strpos($nombreMetodo, 'debito') !== false);

        $datosVoucher = [];

        if ($esYapePlin) {
            $celularLimpio = preg_replace('/\D/', '', $celularYP);
            $codigoLimpio = preg_replace('/\D/', '', $codigoYP);

            if (empty($celularLimpio) || strlen($celularLimpio) < 9 || !str_starts_with($celularLimpio, '9')) {
                $this->responderErrorPedido('Ingresa un número de celular válido para ' . $billetera . ' (9 dígitos que inicie con 9).', 422);
            }
            if (empty($codigoLimpio) || strlen($codigoLimpio) < 6) {
                $this->responderErrorPedido('Ingresa el código de aprobación de 6 dígitos de tu app ' . $billetera . '.', 422);
            }

            // Simulación Sandbox para Yape
            if ($codigoLimpio === '000000' || substr($celularLimpio, -3) === '000') {
                $this->responderErrorPedido('Pago rechazado por ' . $billetera . ': Saldo insuficiente en la cuenta. Por favor recarga tu saldo o intenta con otro medio de pago.', 422);
            }

            $datosVoucher = [
                'celular' => $celularLimpio,
                'codigo' => $codigoLimpio,
                'titular' => 'Cliente ' . $billetera,
                'voucher' => 'PAGO_DIRECTO_' . strtoupper($billetera),
                'billetera' => $billetera,
                'aprobacion_directa' => true,
                'pasarela_id' => 'mercadopago_' . strtolower($billetera)
            ];
        } elseif ($esTarjeta && !empty($ccNumero)) {
            $nombreTitularUpper = strtoupper(trim($ccNombre));

            // Simulación Sandbox oficial Mercado Pago para evaluación de rechazo
            if (strpos($nombreTitularUpper, 'FUND') !== false) {
                $this->responderErrorPedido('Pago rechazado: Tu tarjeta no cuenta con fondos suficientes. Por favor verifica tu saldo o intenta con otra tarjeta.', 422);
            }
            if (strpos($nombreTitularUpper, 'CONT') !== false || strpos($nombreTitularUpper, 'CALL') !== false) {
                $this->responderErrorPedido('Pago rechazado: Tu banco requiere autorización previa para esta compra. Comunícate con tu entidad bancaria o usa otra tarjeta.', 422);
            }
            if (strpos($nombreTitularUpper, 'REJ') !== false || strpos($nombreTitularUpper, 'DECLINED') !== false || strpos($nombreTitularUpper, 'FAIL') !== false) {
                $this->responderErrorPedido('Pago rechazado: La operación fue declinada por tu entidad financiera. Por favor intenta con otra tarjeta o medio de pago.', 422);
            }
            if (strpos($nombreTitularUpper, 'SECU') !== false) {
                $this->responderErrorPedido('Pago rechazado: El código de seguridad (CVV) ingresado es incorrecto. Por favor verifica los 3 o 4 dígitos al reverso de tu tarjeta.', 422);
            }
            if (strpos($nombreTitularUpper, 'DATE') !== false) {
                $this->responderErrorPedido('Pago rechazado: La fecha de vencimiento es inválida o ha expirado. Por favor revisa el mes y año de tu tarjeta.', 422);
            }

            $ultimos4 = substr(preg_replace('/\D/', '', $ccNumero), -4);
            $datosVoucher = [
                'celular' => '',
                'codigo' => 'TC-' . $ultimos4,
                'titular' => $ccNombre ?: 'Cliente Tarjeta',
                'voucher' => 'PAGO_DIRECTO_TARJETA',
                'billetera' => 'Tarjeta',
                'aprobacion_directa' => true,
                'pasarela_id' => 'mercadopago_tarjeta'
            ];
        } elseif ($requiereVoucher) {
            $celularRemitente = trim($_POST['celular_remitente'] ?? '');
            $codigoOperacion = trim($_POST['codigo_operacion'] ?? '');
            $titularOrigen = trim($_POST['titular_origen'] ?? '');

            if (empty($celularRemitente) || strlen($celularRemitente) < 8) {
                $this->responderErrorPedido('Ingresa el número celular emisor del pago digital.', 422);
            }
            if (empty($codigoOperacion) || strlen($codigoOperacion) < 3) {
                $this->responderErrorPedido('Ingresa el código de operación que figura en tu constancia.', 422);
            }

            if (!isset($_FILES['captura_voucher']) || $_FILES['captura_voucher']['error'] !== UPLOAD_ERR_OK) {
                $this->responderErrorPedido('Debes adjuntar la captura de pantalla o foto del comprobante de pago.', 422);
            }

            $file = $_FILES['captura_voucher'];
            $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            if (!in_array($mime, $allowedMimes, true)) {
                $this->responderErrorPedido('El comprobante debe ser una imagen válida (JPG, PNG o WEBP).', 422);
            }

            $extensiones = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
            $ext = $extensiones[$mime] ?? 'jpg';
            $nombreArchivo = 'voucher_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $dirDestino = __DIR__ . '/../uploads/vouchers/';
            if (!is_dir($dirDestino)) {
                mkdir($dirDestino, 0777, true);
            }

            $rutaCompleta = $dirDestino . $nombreArchivo;
            if (!move_uploaded_file($file['tmp_name'], $rutaCompleta)) {
                $this->responderErrorPedido('Error al guardar el archivo del voucher en el servidor.', 500);
            }

            $datosVoucher = [
                'celular' => $celularRemitente,
                'codigo' => $codigoOperacion,
                'titular' => $titularOrigen,
                'voucher' => 'uploads/vouchers/' . $nombreArchivo,
                'requiere' => true
            ];
        }

        // Horario de entrega (inmediato o programado)
        $horarioTipo = trim($_POST['horario_tipo'] ?? 'inmediato');
        $fechaProgramada = null;
        $horarioTexto = 'Lo antes posible (30-45 min)';

        if ($horarioTipo === 'programado') {
            $fechaInput = trim($_POST['fecha_programada'] ?? '');
            if (!empty($fechaInput)) {
                $ts = strtotime($fechaInput);
                if ($ts && $ts > time()) {
                    $fechaProgramada = date('Y-m-d H:i:s', $ts);
                    $horarioTexto = 'Programado para ' . date('d/m/Y H:i', $ts);
                } else {
                    $fechaProgramada = date('Y-m-d H:i:s', strtotime('+1 hour'));
                    $horarioTexto = 'Programado para ' . date('d/m/Y H:i', strtotime($fechaProgramada));
                }
            } else {
                $horarioTexto = 'Programado para después';
            }
        }

        $resultado =
            $this->pedidoModel->crearPedido(
                $idUsuario,
                (int)$idMetodo,
                $idDireccion !== null
                    ? (int)$idDireccion
                    : null,
                $tipoEntrega,
                $observacion,
                $carrito,
                $datosVoucher,
                $horarioTexto,
                $fechaProgramada
            );

        if (
            !isset($resultado['success']) ||
            !$resultado['success']
        ) {
            $this->responderErrorPedido(
                $resultado['message']
                    ?? 'No se pudo realizar el pedido.',
                422
            );
        }

        // Si es pago digital con Pasarela (Mercado Pago: Tarjetas, Yape, Plin)
        if (!empty($resultado['es_pasarela'])) {
            try {
                require_once __DIR__ . '/../Core/MercadoPagoService.php';
                require_once __DIR__ . '/../Models/checkoutModel.php';

                $mpService = new MercadoPagoService();
                $checkoutModel = new CheckoutModel();
                $resumenCarrito = $checkoutModel->construirResumenCarrito($carrito);
                $productosCheckout = $resumenCarrito['productos'];

                $clienteInfo = [
                    'nombre'   => $_SESSION['usuario_nombre'] ?? 'Cliente',
                    'apellido' => $_SESSION['usuario_apellido'] ?? '',
                    'correo'   => $_SESSION['usuario_correo'] ?? 'cliente@lacaserita.com',
                    'telefono' => $_SESSION['usuario_telefono'] ?? '999999999'
                ];

                $pref = $mpService->crearPreferencia(
                    $resultado['codigo_pedido'],
                    $productosCheckout,
                    (float)$resultado['costo_envio'],
                    $clienteInfo,
                    $tipoEntrega
                );

                $this->pedidoModel->actualizarPreferenciaPasarela((int)$resultado['id_pedido'], $pref['preference_id']);

                if (AjaxHelper::isAjax()) {
                    AjaxHelper::jsonResponse(true, 'Preferencia de pago generada.', [
                        'es_pasarela'    => true,
                        'preference_id'  => $pref['preference_id'],
                        'checkout_url'   => $pref['checkout_url'],
                        'public_key'     => $mpService->getPublicKey(),
                        'id_pedido'      => $resultado['id_pedido'],
                        'codigo_pedido'  => $resultado['codigo_pedido']
                    ]);
                }

                header('Location: ' . $pref['checkout_url']);
                exit();

            } catch (Throwable $e) {
                error_log("Error creando preferencia Mercado Pago: " . $e->getMessage());
                $this->responderErrorPedido('Error al conectar con la pasarela de pagos: ' . $e->getMessage(), 500);
            }
        }

        // Si es pago en efectivo: confirmar de inmediato
        $_SESSION['carritos'][$idUsuario] = [];

        // Notificación por correo
        try {
            require_once __DIR__ . '/../Models/notificacionEmailModel.php';
            $notifEmail = new NotificacionEmailModel();
            $notifEmail->enviarConfirmacionCompra((int)$resultado['id_pedido']);
        } catch (Throwable $e) {
            error_log("Error enviando correo de confirmación de pedido: " . $e->getMessage());
        }

        $redirect =
            'index.php?action=pedido_confirmado&id=' .
            (int)$resultado['id_pedido'];

        if (AjaxHelper::isAjax()) {
            AjaxHelper::jsonResponse(
                true,
                'Pedido realizado correctamente.',
                [
                    'es_pasarela' => false,
                    'redirect'    => $redirect,
                    'pedido'      => $resultado
                ]
            );
        }

        $_SESSION['flash_success'] =
            'Pedido realizado correctamente.';

        header('Location: ' . $redirect);
        exit();
    }

    public function mercadopagoRetorno()
    {
        $this->validarSesion();
        $idUsuario = (int)$_SESSION['usuario_id'];

        $paymentId   = trim($_GET['payment_id'] ?? ($_GET['collection_id'] ?? ''));
        $status      = trim($_GET['status'] ?? ($_GET['collection_status'] ?? ''));
        $prefId      = trim($_GET['preference_id'] ?? '');
        $externalRef = trim($_GET['external_reference'] ?? '');

        require_once __DIR__ . '/../Core/MercadoPagoService.php';
        $mpService = new MercadoPagoService();

        $paymentType = 'credit_card';
        if (!empty($paymentId)) {
            $pagoApi = $mpService->consultarPago($paymentId);
            if ($pagoApi) {
                $status = $pagoApi['status'] ?? $status;
                $paymentType = $pagoApi['payment_type_id'] ?? $paymentType;
            }
        }

        $pedido = null;
        if (!empty($externalRef)) {
            $pedido = $this->pedidoModel->getPedidoByCodigo($externalRef);
        } elseif (!empty($prefId)) {
            $pedido = $this->pedidoModel->getPedidoByPreferenceId($prefId);
        }

        if (!$pedido || (int)$pedido['id_usuario'] !== $idUsuario) {
            $_SESSION['flash_error'] = 'No se pudo identificar el pedido asociado a la pasarela.';
            header('Location: index.php?action=home_usuario');
            exit();
        }

        $idPedido = (int)$pedido['id_pedido'];

        if ($status === 'approved') {
            // Confirmar y aprobar pedido automáticamente
            $this->pedidoModel->confirmarPagoPasarela($idPedido, $paymentId, 'approved', $paymentType);

            // Vaciar carrito del usuario
            $_SESSION['carritos'][$idUsuario] = [];

            // Enviar notificación por correo
            try {
                require_once __DIR__ . '/../Models/notificacionEmailModel.php';
                $notifEmail = new NotificacionEmailModel();
                $notifEmail->enviarConfirmacionCompra($idPedido);
            } catch (Throwable $e) {
                error_log("Error enviando correo de confirmación de pedido: " . $e->getMessage());
            }

            $_SESSION['flash_success'] = '¡Tu pago ha sido aprobado exitosamente! Tu pedido ya está en preparación.';
            header('Location: index.php?action=pedido_confirmado&id=' . $idPedido);
            exit();

        } elseif ($status === 'in_process' || $status === 'pending') {
            $this->pedidoModel->confirmarPagoPasarela($idPedido, $paymentId, 'pending', $paymentType);
            $_SESSION['carritos'][$idUsuario] = [];
            $_SESSION['flash_info'] = 'Tu pago está en proceso de verificación por la entidad financiera.';
            header('Location: index.php?action=pedido_confirmado&id=' . $idPedido);
            exit();

        } else {
            // Rechazado o cancelado
            $this->pedidoModel->cancelarPedidoPasarela($idPedido, 'rejected');
            $_SESSION['flash_error'] = 'El pago fue cancelado o rechazado por la pasarela de pagos. Por favor intenta de nuevo.';
            header('Location: index.php?action=checkout');
            exit();
        }
    }

    private function responderErrorPedido(
        $mensaje,
        $status = 422
    ) {
        if (AjaxHelper::isAjax()) {
            AjaxHelper::jsonError(
                $mensaje,
                $status
            );
        }

        $_SESSION['flash_error'] = $mensaje;

        header('Location: index.php?action=checkout');
        exit();
    }

    public function pedidoConfirmado(){
        $this->validarSesion();

        $idPedido = filter_input(
            INPUT_GET,
            'id',
            FILTER_VALIDATE_INT
        );

        if (!$idPedido) {

            $_SESSION['flash_error'] =
                'Pedido inválido.';

            header(
                'Location: index.php?action=home_usuario'
            );

            exit();
        }

        $pedido =
            $this->pedidoModel
                ->getPedidoConfirmado(
                    $idPedido,
                    $_SESSION['usuario_id']
                );

        if (!$pedido) {

            $_SESSION['flash_error'] =
                'El pedido no existe.';

            header(
                'Location: index.php?action=home_usuario'
            );

            exit();
        }

        $detalle =
            $this->pedidoModel
                ->getDetallePedido(
                    $idPedido,
                    $_SESSION['usuario_id']
                );

        include
            'Views/Pedido/pedido_confirmado.php';
    }

    private function validarAdmin()
    {
        $this->validarSesion();
        if (($_SESSION['usuario_rol'] ?? '') !== 'admin') {
            if (AjaxHelper::isAjax()) {
                AjaxHelper::jsonError('Acceso no autorizado.', 403);
            }
            header('Location: index.php?action=home_usuario');
            exit();
        }
    }

    public function listarVentasAdmin()
    {
        $this->validarAdmin();
        $ventas = $this->pedidoModel->getAllPedidosAdmin();
        $nombre = htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Admin');
        include 'Views/Admin/ventas_admin.php';
    }

    public function detallePedidoAdmin()
    {
        $this->validarAdmin();

        $idPedido = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if (!$idPedido) {
            AjaxHelper::jsonError('ID de pedido inválido.', 422);
        }

        $pedido  = $this->pedidoModel->getPedidoByIdAdmin($idPedido);
        $detalle = $this->pedidoModel->getDetallePedidoAdmin($idPedido);

        if (!$pedido) {
            AjaxHelper::jsonError('Pedido no encontrado.', 404);
        }

        AjaxHelper::jsonResponse(true, 'Detalle cargado.', [
            'pedido'  => $pedido,
            'detalle' => $detalle
        ]);
    }

    public function actualizarEstadoPedido()
    {
        $this->validarAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            AjaxHelper::jsonError('Método no permitido.', 405);
        }

        $idPedido    = filter_input(INPUT_POST, 'id_pedido', FILTER_VALIDATE_INT);
        $nuevoEstado = trim($_POST['estado'] ?? '');

        if (!$idPedido || empty($nuevoEstado)) {
            AjaxHelper::jsonError('Datos incompletos.', 422);
        }

        $pedido = $this->pedidoModel->getPedidoByIdAdmin($idPedido);
        if (!$pedido) {
            AjaxHelper::jsonError('Pedido no encontrado.', 404);
        }

        $estadoAnterior = (string)($pedido['estado'] ?? '');
        $esDelivery = strtolower((string)($pedido['tipo_entrega'] ?? '')) === 'delivery';
        if ($esDelivery) {
            $estadoNormalizado = strtolower(trim(str_replace(['_', '-'], ' ', $nuevoEstado)));
            if (strpos($estadoNormalizado, 'entreg') !== false) {
                AjaxHelper::jsonError('Solo el repartidor puede marcar un delivery como entregado.', 422);
            }

            require_once 'Models/deliveryModel.php';
            $deliveryModel = new DeliveryModel();

            if ((strpos($estadoNormalizado, 'camino') !== false || strpos($estadoNormalizado, 'ruta') !== false)
                && !$deliveryModel->pedidoTieneRepartidor($idPedido)) {
                AjaxHelper::jsonError('Asigna un repartidor antes de marcar el pedido En Ruta.', 422);
            }
        }

        $ok = $this->pedidoModel->updateEstadoPedido($idPedido, $nuevoEstado);

        if ($ok) {
            if ($esDelivery) {
                $deliveryModel->sincronizarEstadoPorPedido($idPedido, $nuevoEstado);
            }

            try {
                require_once __DIR__ . '/../Models/notificacionEmailModel.php';
                (new NotificacionEmailModel())->enviarActualizacionEstado($idPedido, $nuevoEstado, '', $estadoAnterior);
            } catch (Throwable $e) {
                error_log('No se pudo enviar el aviso de estado del pedido ' . $idPedido . ': ' . $e->getMessage());
            }

            AjaxHelper::jsonResponse(true, 'Estado actualizado correctamente.', [
                'id_pedido' => $idPedido,
                'estado'    => $nuevoEstado
            ]);
        } else {
            AjaxHelper::jsonError('No se pudo actualizar el estado.', 500);
        }
    }
}