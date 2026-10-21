<?php
include_once 'Models/userAdminModel.php';
include_once 'Models/cajaModel.php';
include_once 'Models/deliveryModel.php';
require_once 'Core/ajaxHelper.php';

class ControllerPerfilStaff {
    private $model;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->model = new UserAdminModel();
    }

    private function validarStaff() {
        if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['usuario_rol'] ?? '', ['cajero', 'repartidor'], true)) {
            if (AjaxHelper::isAjax()) {
                AjaxHelper::jsonError("Sesión expirada o acceso denegado.", 401);
            }
            header("Location: index.php?action=home_usuario");
            exit();
        }
    }

    private function generarCsrfToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    private function validarCsrfToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    private function rutaStaff() {
        return 'index.php?action=mi_perfil_staff';
    }

    private function statsCajero($idUsuario) {
        $sesion = false;
        $resumen = null;
        $turnosCount = 0;
        try {
            $caja = new CajaModel();
            $sesion = $caja->obtenerSesionActiva($idUsuario);
            if ($sesion) {
                $resumen = $caja->calcularResumenCaja($sesion['id_sesion']);
            }
            $historial = $caja->listarHistorialSesionesUsuario($idUsuario, 500);
            $turnosCount = is_array($historial) ? count($historial) : 0;
        } catch (Throwable $e) {
        }

        $abierta = is_array($sesion);
        $totalVentas = $resumen !== null ? (float)($resumen['total_ventas'] ?? 0) : 0.0;
        $totalTickets = $resumen !== null ? (int)($resumen['total_tickets'] ?? 0) : 0;

        return [
            ['label' => 'Turno actual', 'value' => $abierta ? 'Abierta' : 'Cerrada', 'icon' => 'clock', 'tone' => $abierta ? 'green' : 'slate'],
            ['label' => 'Ventas del turno', 'value' => 'S/ ' . number_format($totalVentas, 2), 'icon' => 'cash', 'tone' => 'green'],
            ['label' => 'Tickets del turno', 'value' => (string)$totalTickets, 'icon' => 'receipt', 'tone' => 'blue'],
            ['label' => 'Turnos registrados', 'value' => (string)$turnosCount, 'icon' => 'store', 'tone' => 'amber'],
        ];
    }

    private function statsRepartidor($idUsuario) {
        $misEnvios = [];
        $disponibles = 0;
        try {
            $delivery = new DeliveryModel();
            $misEnvios = $delivery->listarEnvios(null, (int)$idUsuario);
            $dispEnvios = $delivery->listarEnvios(null, null, true);
            $disponibles = is_array($dispEnvios) ? count($dispEnvios) : 0;
        } catch (Throwable $e) {
        }

        $activas = 0;
        $completadas = 0;
        $efectivoPorRendir = 0.0;
        foreach ($misEnvios as $envio) {
            $estado = strtolower(trim($envio['estado_delivery'] ?? ''));
            if (in_array($estado, ['entregado', 'fallido', 'cancelado'], true)) {
                if ($estado === 'entregado') $completadas++;
                continue;
            }
            $activas++;
            $metodo = mb_strtolower(trim($envio['metodo_pago'] ?? ''));
            if (strpos($metodo, 'efectivo') !== false || strpos($metodo, 'contraentrega') !== false || strpos($metodo, 'contra entrega') !== false) {
                $efectivoPorRendir += (float)($envio['pedido_total'] ?? 0);
            }
        }

        return [
            ['label' => 'Entregas activas', 'value' => (string)$activas, 'icon' => 'truck', 'tone' => 'amber'],
            ['label' => 'Completadas', 'value' => (string)$completadas, 'icon' => 'check', 'tone' => 'green'],
            ['label' => 'Disponibles en tienda', 'value' => (string)$disponibles, 'icon' => 'store', 'tone' => 'blue'],
            ['label' => 'Efectivo por rendir', 'value' => 'S/ ' . number_format($efectivoPorRendir, 2), 'icon' => 'cash', 'tone' => 'teal'],
        ];
    }

    public function miPerfilStaff() {
        $this->validarStaff();
        $idUsuario = (int)$_SESSION['usuario_id'];
        $usuario = $this->model->getUsuarioById($idUsuario);
        $csrf_token = $this->generarCsrfToken();

        $flash_success = $_SESSION['flash_success'] ?? null;
        $flash_error = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        $lockTime = $_SESSION['password_lock_until_staff'] ?? 0;
        $remainingLock = max(0, $lockTime - time());

        $rolStaff = $_SESSION['usuario_rol'] ?? 'cajero';
        $mpRolLabel = $rolStaff === 'cajero' ? 'Cajero' : 'Repartidor';
        $mpPerfilAction = 'actualizar_perfil_staff';
        $mpPasswordAction = 'cambiar_password_staff';
        $mpVerifyAction = 'verificar_password_actual_staff';
        $mpStats = $rolStaff === 'cajero'
            ? $this->statsCajero($idUsuario)
            : $this->statsRepartidor($idUsuario);

        include 'Views/Staff/mi_perfil_staff.php';
    }

    public function actualizarPerfilStaff() {
        $this->validarStaff();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = filter_input(INPUT_POST, 'csrf_token', FILTER_SANITIZE_STRING);
            if (!$this->validarCsrfToken($token)) {
                if (AjaxHelper::isAjax()) AjaxHelper::jsonError("Solicitud expirada.", 403);
                $_SESSION['flash_error'] = "La solicitud ha expirado.";
                header("Location: " . $this->rutaStaff());
                exit();
            }

            $idUsuario = (int)$_SESSION['usuario_id'];
            $usuario = $this->model->getUsuarioById($idUsuario);
            if (!$usuario) {
                if (AjaxHelper::isAjax()) AjaxHelper::jsonError("El usuario no existe.", 404);
                $_SESSION['flash_error'] = "El usuario no existe.";
                header("Location: index.php?action=home_usuario");
                exit();
            }

            $nombre = trim($_POST['nombre'] ?? '');
            $apellido = trim($_POST['apellido'] ?? '');
            $correo = trim($_POST['correo'] ?? '');
            $telefono = trim($_POST['telefono'] ?? '');

            $errores = [];
            if ($nombre === '') $errores['nombre'] = 'El nombre es obligatorio.';
            if ($apellido === '') $errores['apellido'] = 'El apellido es obligatorio.';
            if ($correo === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                $errores['correo'] = 'Ingresa un correo válido.';
            } else if ($this->model->correoExisteExceptoUsuario($correo, $idUsuario)) {
                $errores['correo'] = 'Este correo ya está registrado.';
            }

            if (!empty($errores)) {
                if (AjaxHelper::isAjax()) {
                    http_response_code(422);
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode([
                        'success' => false,
                        'message' => 'Corrige los campos indicados.',
                        'errors' => $errores
                    ]);
                    exit();
                }
                $_SESSION['flash_error'] = 'Revisa los datos ingresados.';
                header("Location: " . $this->rutaStaff());
                exit();
            }

            $ok = $this->model->updateUsuario($idUsuario, $nombre, $apellido, $correo, $telefono, $usuario['rol']);
            if ($ok) {
                $_SESSION['usuario_nombre'] = trim($nombre . ' ' . $apellido);
                $_SESSION['usuario_correo'] = $correo;
                $msg = 'Tus datos fueron actualizados correctamente.';
                if (AjaxHelper::isAjax()) {
                    AjaxHelper::jsonResponse(true, $msg, [
                        'usuario' => $this->model->getUsuarioById($idUsuario)
                    ]);
                }
                $_SESSION['flash_success'] = $msg;
            } else {
                $msg = 'No se pudieron actualizar tus datos.';
                if (AjaxHelper::isAjax()) AjaxHelper::jsonError($msg, 500);
                $_SESSION['flash_error'] = $msg;
            }

            header("Location: " . $this->rutaStaff());
            exit();
        }
    }

    public function cambiarPasswordStaff() {
        $this->validarStaff();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = filter_input(INPUT_POST, 'csrf_token', FILTER_SANITIZE_STRING);
            if (!$this->validarCsrfToken($token)) {
                if (AjaxHelper::isAjax()) AjaxHelper::jsonError("Solicitud expirada.", 403);
                $_SESSION['flash_error'] = "La solicitud ha expirado.";
                header("Location: " . $this->rutaStaff());
                exit();
            }

            $idUsuario = (int)$_SESSION['usuario_id'];
            $passwordActual = $_POST['password_actual'] ?? '';
            $passwordNueva = $_POST['password_nueva'] ?? '';
            $passwordConfirmacion = $_POST['password_confirmacion'] ?? '';

            $usuario = $this->model->getUsuarioById($idUsuario);

            $errores = [];
            if (!$usuario || !password_verify($passwordActual, $usuario['password'])) {
                $errores['password_actual'] = 'La contraseña actual es incorrecta.';
            }
            if (strlen($passwordNueva) < 8) {
                $errores['password_nueva'] = 'La contraseña debe tener al menos 8 caracteres.';
            }
            if ($passwordNueva !== $passwordConfirmacion) {
                $errores['password_confirmacion'] = 'La confirmación de la contraseña no coincide.';
            }

            if (!empty($errores)) {
                if (AjaxHelper::isAjax()) {
                    http_response_code(422);
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode([
                        'success' => false,
                        'message' => 'Corrige los campos indicados.',
                        'errors' => $errores
                    ]);
                    exit();
                }
                $_SESSION['flash_error'] = 'Revisa los datos ingresados.';
                header("Location: " . $this->rutaStaff());
                exit();
            }

            $ok = $this->model->actualizarPassword($idUsuario, $passwordNueva);
            if ($ok) {
                $_SESSION['password_attempts_staff'] = 0;
                $_SESSION['password_lock_until_staff'] = 0;
                $msg = 'Tu contraseña fue actualizada correctamente.';
                if (AjaxHelper::isAjax()) {
                    AjaxHelper::jsonResponse(true, $msg);
                }
                $_SESSION['flash_success'] = $msg;
            } else {
                $msg = 'No se pudo actualizar tu contraseña.';
                if (AjaxHelper::isAjax()) AjaxHelper::jsonError($msg, 500);
                $_SESSION['flash_error'] = $msg;
            }

            header("Location: " . $this->rutaStaff());
            exit();
        }
    }

    public function verificarPasswordActualStaff() {
        $this->validarStaff();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = filter_input(INPUT_POST, 'csrf_token', FILTER_SANITIZE_STRING);
            if (!$this->validarCsrfToken($token)) {
                if (AjaxHelper::isAjax()) AjaxHelper::jsonError("Solicitud expirada.", 403);
                $_SESSION['flash_error'] = "La solicitud ha expirado.";
                header("Location: " . $this->rutaStaff());
                exit();
            }

            $idUsuario = (int)$_SESSION['usuario_id'];
            $lockTime = $_SESSION['password_lock_until_staff'] ?? 0;
            if (time() < $lockTime) {
                $remaining = $lockTime - time();
                if (AjaxHelper::isAjax()) {
                    AjaxHelper::jsonError("Por el momento no puede cambiar la contraseña, bloqueado por {$remaining} segundos.", 429);
                }
                exit();
            }

            $passwordActual = $_POST['password_actual'] ?? '';
            if ($passwordActual === '') {
                if (AjaxHelper::isAjax()) AjaxHelper::jsonError('Ingresa tu contraseña actual.', 400);
                exit();
            }

            $usuario = $this->model->getUsuarioById($idUsuario);
            if (!$usuario || !password_verify($passwordActual, $usuario['password'])) {
                $attempts = ($_SESSION['password_attempts_staff'] ?? 0) + 1;
                $_SESSION['password_attempts_staff'] = $attempts;

                if ($attempts >= 6) {
                    $_SESSION['password_lock_until_staff'] = time() + 30;
                    $_SESSION['password_attempts_staff'] = 0;
                    if (AjaxHelper::isAjax()) {
                        AjaxHelper::jsonError("Por el momento no puede cambiar la contraseña, bloqueado por 30 segundos.", 429);
                    }
                    exit();
                }

                $remainingAttempts = 6 - $attempts;
                if (AjaxHelper::isAjax()) {
                    AjaxHelper::jsonError("La contraseña actual es incorrecta. Te quedan {$remainingAttempts} intentos.", 401);
                }
                exit();
            }

            $_SESSION['password_attempts_staff'] = 0;
            if (AjaxHelper::isAjax()) {
                AjaxHelper::jsonResponse(true, 'Contraseña verificada correctamente.');
            }
            exit();
        }
    }
}
?>
