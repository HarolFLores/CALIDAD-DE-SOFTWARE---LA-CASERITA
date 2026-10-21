<?php
require_once __DIR__ . '/../Models/deliveryModel.php';
require_once __DIR__ . '/../Core/ajaxHelper.php';

class ControllerRepartidor {
    private $deliveryModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->deliveryModel = new DeliveryModel();
    }

    private function validarRepartidor() {
        if (($_SESSION['usuario_rol'] ?? '') !== 'repartidor') {
            header('Location: index.php?action=home_usuario');
            exit();
        }
    }

    public function panel() {
        $this->validarRepartidor();
        $envios = $this->deliveryModel->listarEnvios(null, (int)$_SESSION['usuario_id']);
        $enviosDisponibles = $this->deliveryModel->listarEnvios(null, null, true);
        $nombre = htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Repartidor');
        $csrfToken = $_SESSION['csrf_token'] ?? '';
        include __DIR__ . '/../Views/Repartidor/panel.php';
    }

    public function tomarEntrega() {
        $this->validarRepartidor();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit();
        }

        $csrfToken = $_POST['csrf_token'] ?? '';
        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], (string)$csrfToken)) {
            $_SESSION['flash_error'] = 'La sesión de seguridad expiró. Recarga el panel.';
            header('Location: index.php?action=panel_repartidor');
            exit();
        }

        $idEnvio = filter_input(INPUT_POST, 'id_envio', FILTER_VALIDATE_INT);
        $ok = $idEnvio && $this->deliveryModel->tomarEnvioDisponible((int)$idEnvio, (int)$_SESSION['usuario_id']);
        $_SESSION[$ok ? 'flash_success' : 'flash_error'] = $ok
            ? 'Pedido agregado a tus entregas.'
            : 'Este pedido ya fue tomado o no está disponible.';
        header('Location: index.php?action=panel_repartidor');
        exit();
    }

    public function actualizarEntrega() {
        $this->validarRepartidor();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit();
        }

        $csrfToken = $_POST['csrf_token'] ?? '';
        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], (string)$csrfToken)) {
            if (AjaxHelper::isAjax()) AjaxHelper::jsonError('La sesión de seguridad expiró. Recarga el panel.', 419);
            $_SESSION['flash_error'] = 'La sesión de seguridad expiró. Recarga el panel.';
            header('Location: index.php?action=panel_repartidor');
            exit();
        }

        $idEnvio = filter_input(INPUT_POST, 'id_envio', FILTER_VALIDATE_INT);
        $nuevoEstado = trim($_POST['nuevo_estado'] ?? '');
        $estadosPermitidos = ['En Ruta', 'Entregado', 'Fallido'];
        if (!$idEnvio || !in_array($nuevoEstado, $estadosPermitidos, true)) {
            if (AjaxHelper::isAjax()) AjaxHelper::jsonError('Datos de entrega no válidos.', 422);
            $_SESSION['flash_error'] = 'No se pudo actualizar la entrega.';
            header('Location: index.php?action=panel_repartidor');
            exit();
        }

        $enviosPropios = $this->deliveryModel->listarEnvios(null, (int)$_SESSION['usuario_id']);
        $envioPropio = null;
        foreach ($enviosPropios as $envio) {
            if ((int)$envio['id_envio'] === (int)$idEnvio) {
                $envioPropio = $envio;
                break;
            }
        }
        if (!$envioPropio) {
            if (AjaxHelper::isAjax()) AjaxHelper::jsonError('Esta entrega no está asignada a tu cuenta.', 403);
            $_SESSION['flash_error'] = 'Esta entrega no está asignada a tu cuenta.';
            header('Location: index.php?action=panel_repartidor');
            exit();
        }

        if ($nuevoEstado === 'Entregado' && ($envioPropio['estado_delivery'] ?? '') !== 'En Ruta') {
            if (AjaxHelper::isAjax()) AjaxHelper::jsonError('Primero debes iniciar la ruta antes de marcar la entrega.', 422);
            $_SESSION['flash_error'] = 'Primero debes iniciar la ruta antes de marcar la entrega.';
            header('Location: index.php?action=panel_repartidor');
            exit();
        }

        $metodoPago = mb_strtolower((string)($envioPropio['metodo_pago'] ?? ''));
        $esContraEntrega = strpos($metodoPago, 'efectivo') !== false
            || strpos($metodoPago, 'contraentrega') !== false
            || strpos($metodoPago, 'contra entrega') !== false;
        $cobrado = ($nuevoEstado === 'Entregado' && $esContraEntrega)
            ? (isset($_POST['cobrado']) ? 1 : 0)
            : null;
        $notas = trim($_POST['notas'] ?? '');
        if ($nuevoEstado === 'Fallido' && mb_strlen($notas) < 3) {
            if (AjaxHelper::isAjax()) AjaxHelper::jsonError('Debes indicar el motivo de la no entrega.', 422);
            $_SESSION['flash_error'] = 'Debes indicar el motivo de la no entrega.';
            header('Location: index.php?action=panel_repartidor&tab=assigned');
            exit();
        }
        $estadoAnterior = (string)($envioPropio['pedido_estado'] ?? '');

        $ok = $this->deliveryModel->actualizarEstadoDelivery(
            (int)$idEnvio,
            $nuevoEstado,
            $cobrado,
            null,
            $notas,
            (int)$envioPropio['id_pedido']
        );

        if ($ok) {
            try {
                require_once __DIR__ . '/../Models/notificacionEmailModel.php';
                (new NotificacionEmailModel())->enviarActualizacionEstado((int)$envioPropio['id_pedido'], $nuevoEstado, $notas, $estadoAnterior);
            } catch (Throwable $e) {
                error_log('No se pudo enviar el aviso de estado del pedido ' . $envioPropio['id_pedido'] . ': ' . $e->getMessage());
            }
        }

        if (AjaxHelper::isAjax()) {
            if ($ok) AjaxHelper::jsonResponse(true, 'Entrega actualizada: ' . $nuevoEstado);
            AjaxHelper::jsonError('No se pudo actualizar la entrega.', 500);
        }

        $_SESSION[$ok ? 'flash_success' : 'flash_error'] = $ok
            ? 'Entrega actualizada: ' . $nuevoEstado
            : 'No se pudo actualizar la entrega.';
        header('Location: index.php?action=panel_repartidor');
        exit();
    }

    public function editarIncidencia() {
        $this->validarRepartidor();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit();
        }

        $csrfToken = $_POST['csrf_token'] ?? '';
        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], (string)$csrfToken)) {
            $_SESSION['flash_error'] = 'La sesión de seguridad expiró. Recarga el panel.';
            header('Location: index.php?action=panel_repartidor&tab=history');
            exit();
        }

        $idEnvio = filter_input(INPUT_POST, 'id_envio', FILTER_VALIDATE_INT);
        $notas = trim($_POST['notas'] ?? '');
        if (!$idEnvio || mb_strlen($notas) < 3) {
            $_SESSION['flash_error'] = 'Indica un motivo de incidencia válido.';
            header('Location: index.php?action=panel_repartidor&tab=history');
            exit();
        }

        $envioPropio = null;
        foreach ($this->deliveryModel->listarEnvios(null, (int)$_SESSION['usuario_id']) as $envio) {
            if ((int)$envio['id_envio'] === (int)$idEnvio) {
                $envioPropio = $envio;
                break;
            }
        }

        $esFallido = $envioPropio && strtolower(trim((string)($envioPropio['estado_delivery'] ?? ''))) === 'fallido';
        $esCancelacion = $envioPropio && preg_match('/\[Cancelaci[oó]n:/iu', (string)($envioPropio['notas_entrega'] ?? ''));
        if (!$esFallido || $esCancelacion) {
            $_SESSION['flash_error'] = 'Solo puedes editar incidencias de pedidos no entregados por ti.';
            header('Location: index.php?action=panel_repartidor&tab=history');
            exit();
        }

        $ok = $this->deliveryModel->actualizarIncidenciaEntrega((int)$idEnvio, $notas);
        $_SESSION[$ok ? 'flash_success' : 'flash_error'] = $ok
            ? 'Incidencia actualizada correctamente.'
            : 'No se pudo actualizar la incidencia.';
        header('Location: index.php?action=panel_repartidor&tab=history');
        exit();
    }
}
