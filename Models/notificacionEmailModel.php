<?php
require_once __DIR__ . '/../Core/dataBase.php';

/**
 * Modelo de Notificaciones por Correo Electrónico
 * Tienda La Caserita
 */
class NotificacionEmailModel {
    private $conn;

    public function __construct() {
        $db = new DataBase();
        $this->conn = $db->getConnection();
    }

    /**
     * Obtiene la configuración activa de correo de la tienda
     */
    public function obtenerConfiguracion() {
        $query = "SELECT * FROM configuracion_correo WHERE activo = 1 ORDER BY id_config DESC LIMIT 1";
        $stmt = $this->conn->query($query);
        $config = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;

        if (!$config) {
            $config = [
                'nombre_empresa'       => 'Tienda La Caserita',
                'logo_url'             => 'Images/logo.png',
                'cabecera_correo_url'  => '',
                'telefono_whatsapp'    => '+51 987 654 321',
                'correo_soporte'       => 'pedidos@lacaserita.com',
                'enlace_seguimiento'   => 'index.php?action=mi_cuenta',
                'direccion_fisica'     => 'Av. Grau #156, Chiclayo - Perú',
                'color_primario'       => '#15803D',
                'color_secundario'     => '#16a34a',
                'smtp_host'            => null,
                'smtp_port'            => 587,
                'smtp_user'            => null,
                'smtp_pass'            => null
            ];
        }

        return $config;
    }

    /**
     * Guarda o actualiza los ajustes de correo y cabecera
     */
    public function guardarConfiguracion($datos) {
        $query = "UPDATE configuracion_correo SET
                    nombre_empresa = :nombre,
                    logo_url = :logo,
                    cabecera_correo_url = :cabecera,
                    telefono_whatsapp = :whatsapp,
                    correo_soporte = :correo,
                    enlace_seguimiento = :seguimiento,
                    direccion_fisica = :direccion,
                    color_primario = :c_primario,
                    color_secundario = :c_secundario,
                    smtp_host = :host,
                    smtp_port = :port,
                    smtp_user = :user,
                    smtp_pass = :pass
                  WHERE id_config = :id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':nombre'       => $datos['nombre_empresa'] ?? 'Tienda La Caserita',
            ':logo'         => $datos['logo_url'] ?? 'Images/logo.png',
            ':cabecera'     => $datos['cabecera_correo_url'] ?? '',
            ':whatsapp'     => $datos['telefono_whatsapp'] ?? '+51 987 654 321',
            ':correo'       => $datos['correo_soporte'] ?? 'pedidos@lacaserita.com',
            ':seguimiento'  => $datos['enlace_seguimiento'] ?? 'index.php?action=mi_cuenta',
            ':direccion'    => $datos['direccion_fisica'] ?? 'Av. Grau #156, Chiclayo - Perú',
            ':c_primario'   => $datos['color_primario'] ?? '#15803D',
            ':c_secundario' => $datos['color_secundario'] ?? '#16a34a',
            ':host'         => $datos['smtp_host'] ?? null,
            ':port'         => (int)($datos['smtp_port'] ?? 587),
            ':user'         => $datos['smtp_user'] ?? null,
            ':pass'         => $datos['smtp_pass'] ?? null,
            ':id'           => (int)($datos['id_config'] ?? 1)
        ]);
    }

    /**
     * Construye la plantilla HTML del correo para la confirmación de la compra
     * Diseño moderno, elegante y compatible con todos los clientes de correo
     */
    public function generarHtmlConfirmacionPedido($pedido, $detalles, $cliente) {
        $cfg = $this->obtenerConfiguracion();

        $empresaNombre    = htmlspecialchars($cfg['nombre_empresa'] ?? 'Tienda La Caserita');
        $whatsapp         = htmlspecialchars($cfg['telefono_whatsapp'] ?? '+51 987 654 321');
        $correoSoporte    = htmlspecialchars($cfg['correo_soporte'] ?? 'pedidos@lacaserita.com');
        $enlaceRastreo    = htmlspecialchars($cfg['enlace_seguimiento'] ?? 'index.php?action=mi_cuenta');
        $direccionFisica  = htmlspecialchars($cfg['direccion_fisica'] ?? 'Av. Grau #156, Chiclayo - Perú');

        $idPad            = str_pad((int)($pedido['id_pedido'] ?? 0), 5, '0', STR_PAD_LEFT);
        $codigoPedido     = !empty($pedido['codigo_pedido']) ? htmlspecialchars($pedido['codigo_pedido']) : '#' . $idPad;
        $totalPedido      = number_format((float)($pedido['total'] ?? 0), 2);
        $costoEnvio       = number_format((float)($pedido['costo_envio'] ?? 0), 2);
        $subtotal         = number_format((float)($pedido['subtotal'] ?? ((float)($pedido['total'] ?? 0) - (float)($pedido['costo_envio'] ?? 0))), 2);
        $fechaPedido      = !empty($pedido['fecha']) ? date('d/m/Y h:i A', strtotime($pedido['fecha'])) : date('d/m/Y h:i A');
        $metodoPago       = htmlspecialchars($pedido['metodo_pago'] ?? 'Pago Electrónico');
        $tipoEntrega      = ucfirst(strtolower($pedido['tipo_entrega'] ?? 'delivery'));
        $estadoPedido     = htmlspecialchars($pedido['estado'] ?? 'En Preparacion');
        $nombreCliente    = htmlspecialchars($cliente['nombre'] ?? 'Estimado(a) Cliente');

        $direccionEntrega = '';
        if (strtolower($pedido['tipo_entrega'] ?? '') === 'delivery') {
            $partesDir = array_filter([
                $pedido['calle'] ?? '',
                $pedido['distrito'] ?? '',
                $pedido['provincia'] ?? ''
            ]);
            $direccionEntrega = htmlspecialchars(implode(', ', $partesDir));
            if (!empty($pedido['referencia'])) {
                $refLimpia = preg_replace('/\[Pago[^\]]+\]/i', '', $pedido['referencia']);
                $refLimpia = preg_replace('/\[Tarjeta[^\]]+\]/i', '', $refLimpia);
                $refLimpia = trim(preg_replace('/^\s*\|\s*|\s*\|\s*$/', '', trim($refLimpia)));
                if (!empty($refLimpia)) {
                    $direccionEntrega .= ' (Ref: ' . htmlspecialchars($refLimpia) . ')';
                }
            }
        } else {
            $direccionEntrega = 'Recojo en tienda: ' . $direccionFisica;
        }

        // Construir filas de productos
        $filasProductos = '';
        foreach ($detalles as $item) {
            $nombreProd = htmlspecialchars($item['nombre'] ?? $item['titulo'] ?? $item['titulo_producto'] ?? 'Producto');
            $cantProd   = (int)($item['cantidad'] ?? 1);
            $unidad     = htmlspecialchars(strtolower($item['unidad_medida'] ?? 'und.'));
            if ($unidad === 'kg') { $unidad = 'kg'; }
            $precioProd = number_format((float)($item['precio_unitario'] ?? $item['precio'] ?? 0), 2);
            $subtProd   = number_format((float)($item['subtotal'] ?? ($cantProd * (float)($item['precio_unitario'] ?? $item['precio'] ?? 0))), 2);
            $imgUrl     = !empty($item['imagen']) ? $item['imagen'] : (!empty($item['imagen_url']) ? $item['imagen_url'] : 'Images/logo.png');

            $filasProductos .= "
            <tr style='border-bottom: 1px solid #f1f5f9;'>
                <td style='padding: 14px 10px; width: 56px; vertical-align: middle;'>
                    <img src='{$imgUrl}' alt='{$nombreProd}' style='width: 50px; height: 50px; object-fit: cover; border-radius: 8px; border: 1px solid #e2e8f0; display: block;'>
                </td>
                <td style='padding: 14px 10px; font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif; font-size: 14px; color: #1e293b; vertical-align: middle;'>
                    <div style='font-weight: 700; color: #0f172a; margin-bottom: 4px;'>{$nombreProd}</div>
                    <div style='color: #64748b; font-size: 12px;'>{$cantProd} {$unidad} &times; S/ {$precioProd}</div>
                </td>
                <td style='padding: 14px 10px; font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif; font-size: 14px; font-weight: 800; color: #0f172a; text-align: right; vertical-align: middle;'>
                    S/ {$subtProd}
                </td>
            </tr>";
        }

        return "
<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Confirmación de Compra - {$empresaNombre}</title>
</head>
<body style='margin: 0; padding: 24px 12px; background-color: #f8fafc; font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased;'>
    <table role='presentation' cellspacing='0' cellpadding='0' border='0' align='center' width='100%' style='max-width: 620px; margin: 0 auto; background-color: #ffffff; border-radius: 20px; overflow: hidden; box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08); border: 1px solid #e2e8f0;'>
        <!-- Header con Identidad de Marca -->
        <tr>
            <td style='background: linear-gradient(135deg, #15803D 0%, #166534 100%); padding: 34px 28px; text-align: center; color: #ffffff;'>
                <div style='display: inline-block; width: 56px; height: 56px; line-height: 56px; background: rgba(255, 255, 255, 0.2); border-radius: 50%; margin-bottom: 12px; font-size: 28px;'>
                    ✓
                </div>
                <h1 style='margin: 0 0 6px 0; font-size: 24px; font-weight: 800; letter-spacing: -0.5px;'>¡Gracias por tu compra!</h1>
                <p style='margin: 0; font-size: 14px; opacity: 0.95; font-weight: 500;'>Tu pedido ha sido recibido y ya está en preparación.</p>
                <div style='margin-top: 14px;'>
                    <span style='display: inline-block; background: #ffffff; color: #15803D; font-size: 13px; font-weight: 800; padding: 6px 16px; border-radius: 9999px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);'>
                        Pedido #{$idPad}
                    </span>
                </div>
            </td>
        </tr>

        <!-- Saludo & Estado -->
        <tr>
            <td style='padding: 28px 28px 12px 28px;'>
                <p style='margin: 0 0 8px 0; font-size: 16px; color: #0f172a;'>
                    Hola <strong>{$nombreCliente}</strong>,
                </p>
                <p style='margin: 0; font-size: 14px; color: #475569; line-height: 1.6;'>
                    Hemos confirmado tu orden de manera exitosa. A continuación te detallamos el resumen completo de tu compra y los datos de entrega:
                </p>
            </td>
        </tr>

        <!-- Tarjetas de Información Rápida -->
        <tr>
            <td style='padding: 12px 28px 20px 28px;'>
                <table role='presentation' width='100%' cellspacing='0' cellpadding='0'>
                    <tr>
                        <td style='background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px; vertical-align: top;'>
                            <div style='font-size: 11px; text-transform: uppercase; font-weight: 800; color: #64748b; letter-spacing: 0.5px; margin-bottom: 4px;'>Tipo de Entrega</div>
                            <div style='font-size: 14px; font-weight: 700; color: #0f172a;'>{$tipoEntrega}</div>
                            <div style='font-size: 12px; color: #475569; margin-top: 4px;'>{$direccionEntrega}</div>
                        </td>
                        <td style='width: 12px;'></td>
                        <td style='background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px; vertical-align: top;'>
                            <div style='font-size: 11px; text-transform: uppercase; font-weight: 800; color: #64748b; letter-spacing: 0.5px; margin-bottom: 4px;'>Método de Pago</div>
                            <div style='font-size: 14px; font-weight: 700; color: #0f172a;'>{$metodoPago}</div>
                            <div style='font-size: 12px; color: #15803D; font-weight: 600; margin-top: 4px;'>Estado: Aprobado ✓</div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <!-- Detalle de Productos -->
        <tr>
            <td style='padding: 0 28px 20px 28px;'>
                <div style='border: 1px solid #e2e8f0; border-radius: 14px; overflow: hidden;'>
                    <div style='background-color: #f1f5f9; padding: 12px 16px; font-size: 12px; font-weight: 800; color: #334155; text-transform: uppercase; letter-spacing: 0.5px;'>
                        Artículos en tu pedido
                    </div>
                    <table role='presentation' width='100%' cellspacing='0' cellpadding='0' style='padding: 0 8px;'>
                        {$filasProductos}
                    </table>
                </div>
            </td>
        </tr>

        <!-- Resumen Financiero -->
        <tr>
            <td style='padding: 0 28px 24px 28px;'>
                <table role='presentation' align='right' width='100%' style='max-width: 280px;' cellspacing='0' cellpadding='0'>
                    <tr>
                        <td style='padding: 6px 0; font-size: 13px; color: #64748b;'>Subtotal:</td>
                        <td style='padding: 6px 0; font-size: 13px; font-weight: 600; color: #0f172a; text-align: right;'>S/ {$subtotal}</td>
                    </tr>
                    <tr>
                        <td style='padding: 6px 0; font-size: 13px; color: #64748b;'>Costo de Envío:</td>
                        <td style='padding: 6px 0; font-size: 13px; font-weight: 600; color: #0f172a; text-align: right;'>S/ {$costoEnvio}</td>
                    </tr>
                    <tr style='border-top: 2px solid #e2e8f0;'>
                        <td style='padding: 10px 0 0 0; font-size: 16px; font-weight: 800; color: #0f172a;'>Total Pagado:</td>
                        <td style='padding: 10px 0 0 0; font-size: 18px; font-weight: 800; color: #15803D; text-align: right;'>S/ {$totalPedido}</td>
                    </tr>
                </table>
            </td>
        </tr>

        <!-- Botón de Llamada a la Acción -->
        <tr>
            <td style='padding: 0 28px 28px 28px; text-align: center;'>
                <a href='{$enlaceRastreo}' target='_blank' style='display: inline-block; background-color: #15803D; color: #ffffff; text-decoration: none; font-weight: 800; font-size: 15px; padding: 14px 34px; border-radius: 12px; box-shadow: 0 4px 14px rgba(21, 128, 61, 0.35);'>
                    Ver estado de mi pedido →
                </a>
            </td>
        </tr>

        <!-- Soporte & Ayuda -->
        <tr>
            <td style='padding: 20px 28px; background-color: #f8fafc; border-top: 1px solid #e2e8f0; font-size: 13px; color: #64748b; line-height: 1.5;'>
                ¿Tienes alguna consulta sobre tu pedido? Escríbenos a nuestro WhatsApp oficial: <strong style='color: #0f172a;'>{$whatsapp}</strong> o responde directamente a este correo (<strong style='color: #0f172a;'>{$correoSoporte}</strong>).
            </td>
        </tr>

        <!-- Pie de Página Institucional -->
        <tr>
            <td style='padding: 22px 28px; background-color: #0f172a; text-align: center; color: #94a3b8; font-size: 12px;'>
                <p style='margin: 0 0 6px 0; font-weight: 700; color: #ffffff; font-size: 13px;'>{$empresaNombre}</p>
                <p style='margin: 0 0 8px 0; color: #cbd5e1;'>{$direccionFisica}</p>
                <p style='margin: 0; font-size: 11px; color: #64748b;'>Este correo fue generado automáticamente para notificar la compra de tu pedido.</p>
            </td>
        </tr>
    </table>
</body>
</html>";
    }

    /**
     * Envía la confirmación del pedido al correo del cliente
     */
    public function enviarConfirmacionCompra($idPedido) {
        $queryPed = "SELECT p.*, u.correo, u.nombre as user_nombre, u.apellido as user_apellido 
                     FROM pedido p 
                     JOIN usuario u ON p.id_usuario = u.id_usuario 
                     WHERE p.id_pedido = :id";
        $stmtPed = $this->conn->prepare($queryPed);
        $stmtPed->bindParam(':id', $idPedido, PDO::PARAM_INT);
        $stmtPed->execute();
        $pedido = $stmtPed->fetch(PDO::FETCH_ASSOC);

        if (!$pedido || empty($pedido['correo'])) {
            return ['ok' => false, 'mensaje' => 'Pedido o correo del destinatario no encontrado.'];
        }

        $queryDet = "SELECT d.*, pr.nombre, pr.titulo, pr.imagen, pr.imagen_url, pr.unidad_medida 
                     FROM detalle_pedido d 
                     JOIN producto pr ON d.id_producto = pr.id_producto 
                     WHERE d.id_pedido = :id";
        $stmtDet = $this->conn->prepare($queryDet);
        $stmtDet->bindParam(':id', $idPedido, PDO::PARAM_INT);
        $stmtDet->execute();
        $detalles = $stmtDet->fetchAll(PDO::FETCH_ASSOC);

        $cliente = [
            'nombre' => !empty($pedido['cliente_nombre']) ? $pedido['cliente_nombre'] : trim($pedido['user_nombre'] . ' ' . $pedido['user_apellido']),
            'correo' => $pedido['correo']
        ];

        $htmlContenido = $this->generarHtmlConfirmacionPedido($pedido, $detalles, $cliente);
        $idPad = str_pad((int)$idPedido, 5, '0', STR_PAD_LEFT);
        $asunto = "✓ Pedido Confirmado #" . $idPad . " - Tienda La Caserita";

        $destinatario = $pedido['correo'];

        require_once __DIR__ . '/MailHelper.php';
        $enviado = MailHelper::sendEmail($destinatario, $asunto, $htmlContenido, true);

        return [
            'ok'          => true,
            'mensaje'     => 'Correo de confirmación procesado exitosamente.',
            'enviado'     => $enviado,
            'destinatario'=> $destinatario
        ];
    }

    /**
     * Normaliza los distintos nombres de estado usados por cajero, admin y repartidor.
     */
    public static function normalizarEstado($estado) {
        $estado = str_replace(
            ['á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú'],
            ['a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U'],
            (string)$estado
        );
        $clave = strtolower(trim(str_replace(['_', '-'], ' ', $estado)));

        $map = [
            'pendiente'          => 'Pendiente',
            'en preparacion'     => 'En Preparacion',
            'preparacion'        => 'En Preparacion',
            'en camino'          => 'En Camino',
            'camino'             => 'En Camino',
            'en ruta'            => 'En Camino',
            'ruta'               => 'En Camino',
            'listo para recojo'  => 'Listo para Recojo',
            'listo recojo'       => 'Listo para Recojo',
            'entregado'          => 'Entregado',
            'cancelado'          => 'Cancelado',
            'fallido'            => 'Cancelado'
        ];

        return $map[$clave] ?? trim((string)$estado);
    }

    /**
     * Envía al cliente un aviso cuando cambia el estado de su pedido.
     */
    public function enviarActualizacionEstado($idPedido, $nuevoEstado, $notas = '', $estadoAnterior = null) {
        $idPedido = (int)$idPedido;
        $estadoNormalizado = self::normalizarEstado($nuevoEstado);

        if ($estadoAnterior !== null && self::normalizarEstado($estadoAnterior) === $estadoNormalizado) {
            return ['ok' => false, 'mensaje' => 'El pedido ya se encuentra en ese estado.'];
        }

        $query = "SELECT p.*, u.correo, u.nombre AS user_nombre, u.apellido AS user_apellido,
                         COALESCE(NULLIF(p.metodo_pago, ''), mp.nombre, 'No especificado') AS metodo_pago_mostrar,
                         d.departamento, d.provincia, d.distrito, d.calle, d.referencia AS direccion_referencia
                  FROM pedido p
                  LEFT JOIN usuario u ON u.id_usuario = p.id_usuario
                  LEFT JOIN metodo_pago mp ON mp.id_metodo = p.id_metodo
                  LEFT JOIN direccion d ON d.id_direccion = p.id_direccion
                  WHERE p.id_pedido = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $idPedido, PDO::PARAM_INT);
        $stmt->execute();
        $pedido = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$pedido) {
            return ['ok' => false, 'mensaje' => 'Pedido no encontrado.'];
        }

        if (strtoupper(trim((string)($pedido['canal'] ?? ''))) === 'POS') {
            return ['ok' => false, 'mensaje' => 'Los pedidos POS no generan avisos por correo.'];
        }

        $correo = trim((string)($pedido['correo'] ?? ''));
        if ($correo === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            return ['ok' => false, 'mensaje' => 'El cliente no tiene un correo válido registrado.'];
        }

        $html = $this->generarHtmlActualizacionEstado($pedido, $estadoNormalizado, $notas);
        $idPad = str_pad($idPedido, 5, '0', STR_PAD_LEFT);
        $asunto = 'Actualización de tu pedido #' . $idPad . ' - Tienda La Caserita';

        require_once __DIR__ . '/MailHelper.php';
        $enviado = MailHelper::sendEmail($correo, $asunto, $html, true);

        return [
            'ok'           => true,
            'mensaje'      => 'Aviso de estado procesado.',
            'enviado'      => $enviado,
            'destinatario' => $correo
        ];
    }

    /**
     * Construye la plantilla HTML del aviso de cambio de estado.
     */
    private function generarHtmlActualizacionEstado($pedido, $estado, $notas) {
        $cfg = $this->obtenerConfiguracion();
        $info = $this->obtenerTextoEstado($estado);

        $empresaNombre   = htmlspecialchars($cfg['nombre_empresa'] ?? 'Tienda La Caserita');
        $whatsapp        = htmlspecialchars($cfg['telefono_whatsapp'] ?? '+51 987 654 321');
        $correoSoporte   = htmlspecialchars($cfg['correo_soporte'] ?? 'pedidos@lacaserita.com');
        $enlaceRastreo   = htmlspecialchars($cfg['enlace_seguimiento'] ?? 'index.php?action=mi_cuenta');
        $direccionFisica = htmlspecialchars($cfg['direccion_fisica'] ?? 'Av. Grau #156, Chiclayo - Perú');

        $idPad        = str_pad((int)($pedido['id_pedido'] ?? 0), 5, '0', STR_PAD_LEFT);
        $codigoPedido = !empty($pedido['codigo_pedido']) ? htmlspecialchars($pedido['codigo_pedido']) : '#' . $idPad;
        $fechaPedido  = !empty($pedido['fecha']) ? date('d/m/Y h:i A', strtotime($pedido['fecha'])) : date('d/m/Y h:i A');
        $metodoPago   = htmlspecialchars((string)($pedido['metodo_pago_mostrar'] ?? 'No especificado'));
        $totalPedido  = number_format((float)($pedido['total'] ?? 0), 2);
        $esDelivery   = strtolower((string)($pedido['tipo_entrega'] ?? '')) === 'delivery';
        $tipoEntrega  = $esDelivery ? 'Delivery a domicilio' : 'Recojo en tienda';

        $nombreCliente = trim((string)($pedido['cliente_nombre'] ?? ''));
        if ($nombreCliente === '' || in_array(strtolower($nombreCliente), ['público general', 'publico general'], true)) {
            $nombreCliente = trim(($pedido['user_nombre'] ?? '') . ' ' . ($pedido['user_apellido'] ?? ''));
        }
        if ($nombreCliente === '') {
            $nombreCliente = 'Estimado(a) cliente';
        }
        $nombreCliente = htmlspecialchars($nombreCliente);

        if ($esDelivery) {
            $direccion = trim((string)($pedido['direccion_envio'] ?? ''));
            if ($direccion === '') {
                $partes = array_filter([
                    trim((string)($pedido['calle'] ?? '')),
                    trim((string)($pedido['distrito'] ?? '')),
                    trim((string)($pedido['provincia'] ?? '')),
                    trim((string)($pedido['departamento'] ?? ''))
                ]);
                $direccion = implode(', ', $partes);
            }
            if ($direccion === '') {
                $direccion = 'Dirección de entrega no especificada';
            }
            if (!empty($pedido['direccion_referencia'])) {
                $direccion .= ' (Ref: ' . $pedido['direccion_referencia'] . ')';
            }
        } else {
            $direccion = 'Recojo en tienda: ' . ($cfg['direccion_fisica'] ?? '');
        }
        $direccion = htmlspecialchars($direccion);

        $estadoVisible = $estado;
        if ($estado === 'En Preparacion') $estadoVisible = 'En preparación';
        if ($estado === 'En Camino') $estadoVisible = 'En camino';
        if ($estado === 'Listo para Recojo') $estadoVisible = 'Listo para recojo';
        $estadoVisible = htmlspecialchars($estadoVisible);

        $colorEstado  = htmlspecialchars($info['color']);
        $tituloEstado = htmlspecialchars($info['titulo']);
        $detalleEstado = htmlspecialchars($info['detalle']);

        $notaHtml = '';
        if (trim((string)$notas) !== '') {
            $notaHtml = "
            <div style='margin: 18px 0 0; padding: 14px 16px; background: #fff7ed; border: 1px solid #fed7aa; border-radius: 10px; font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif; font-size: 13px; color: #7c2d12; line-height: 1.5;'>
                <strong style='display: block; margin-bottom: 4px;'>Motivo / nota:</strong>
                " . nl2br(htmlspecialchars((string)$notas)) . "
            </div>";
        }

        return "
<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>{$tituloEstado} - {$empresaNombre}</title>
</head>
<body style='margin: 0; padding: 24px 12px; background-color: #f8fafc; font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif;'>
    <table role='presentation' cellspacing='0' cellpadding='0' border='0' align='center' width='100%' style='max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 18px; overflow: hidden; border: 1px solid #e2e8f0; box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);'>
        <tr>
            <td style='background: {$colorEstado}; padding: 30px 26px; text-align: center; color: #ffffff;'>
                <div style='display: inline-block; width: 54px; height: 54px; line-height: 54px; background: rgba(255, 255, 255, 0.18); border-radius: 50%; margin-bottom: 12px; font-size: 26px;'>
                    <i style='font-style: normal;'>&#10003;</i>
                </div>
                <h1 style='margin: 0 0 6px; font-size: 23px; font-weight: 800; letter-spacing: -0.4px;'>{$tituloEstado}</h1>
                <p style='margin: 0; font-size: 14px; opacity: 0.95;'>Pedido #{$idPad}</p>
            </td>
        </tr>
        <tr>
            <td style='padding: 26px 26px 10px;'>
                <p style='margin: 0 0 10px; font-size: 16px; color: #0f172a;'>Hola <strong>{$nombreCliente}</strong>,</p>
                <p style='margin: 0; font-size: 14px; color: #475569; line-height: 1.6;'>{$detalleEstado}</p>
                <div style='margin-top: 18px; padding: 14px 16px; background: #f8fafc; border: 1px solid #e2e8f0; border-left: 4px solid {$colorEstado}; border-radius: 10px; font-size: 14px; color: #0f172a;'>
                    <strong>Estado actual:</strong> <span style='color: {$colorEstado}; font-weight: 800;'>{$estadoVisible}</span>
                </div>
                {$notaHtml}
            </td>
        </tr>
        <tr>
            <td style='padding: 16px 26px 8px;'>
                <table role='presentation' width='100%' cellspacing='0' cellpadding='0' style='border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; font-size: 13px; color: #334155;'>
                    <tr>
                        <td style='padding: 10px 14px; background: #f8fafc; color: #64748b; width: 38%;'>Pedido</td>
                        <td style='padding: 10px 14px; font-weight: 700; color: #0f172a;'>{$codigoPedido}</td>
                    </tr>
                    <tr>
                        <td style='padding: 10px 14px; background: #f8fafc; color: #64748b;'>Fecha</td>
                        <td style='padding: 10px 14px;'>{$fechaPedido}</td>
                    </tr>
                    <tr>
                        <td style='padding: 10px 14px; background: #f8fafc; color: #64748b;'>Entrega</td>
                        <td style='padding: 10px 14px;'>{$tipoEntrega}</td>
                    </tr>
                    <tr>
                        <td style='padding: 10px 14px; background: #f8fafc; color: #64748b;'>Dirección</td>
                        <td style='padding: 10px 14px;'>{$direccion}</td>
                    </tr>
                    <tr>
                        <td style='padding: 10px 14px; background: #f8fafc; color: #64748b;'>Pago</td>
                        <td style='padding: 10px 14px;'>{$metodoPago}</td>
                    </tr>
                    <tr>
                        <td style='padding: 10px 14px; background: #f8fafc; color: #64748b;'>Total</td>
                        <td style='padding: 10px 14px; font-weight: 800; color: #15803D;'>S/ {$totalPedido}</td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td style='padding: 20px 26px 24px; text-align: center;'>
                <a href='{$enlaceRastreo}' target='_blank' style='display: inline-block; background-color: #15803D; color: #ffffff; text-decoration: none; font-weight: 800; font-size: 14px; padding: 13px 30px; border-radius: 10px;'>Ver estado de mi pedido</a>
            </td>
        </tr>
        <tr>
            <td style='padding: 18px 26px; background: #f8fafc; border-top: 1px solid #e2e8f0; font-size: 12.5px; color: #64748b; line-height: 1.5;'>
                ¿Tienes alguna consulta? Escríbenos al WhatsApp <strong style='color: #0f172a;'>{$whatsapp}</strong> o a <strong style='color: #0f172a;'>{$correoSoporte}</strong>.
            </td>
        </tr>
        <tr>
            <td style='padding: 18px 26px; background: #0f172a; text-align: center; color: #94a3b8; font-size: 12px;'>
                <p style='margin: 0 0 6px; font-weight: 700; color: #ffffff;'>{$empresaNombre}</p>
                <p style='margin: 0; color: #cbd5e1;'>{$direccionFisica}</p>
            </td>
        </tr>
    </table>
</body>
</html>";
    }

    /**
     * Devuelve el título, detalle y color del correo según el estado.
     */
    private function obtenerTextoEstado($estado) {
        switch ($estado) {
            case 'Pendiente':
                return ['titulo' => 'Pedido recibido', 'detalle' => 'Hemos registrado tu pedido. Te avisaremos cuando comencemos a prepararlo.', 'color' => '#15803D'];
            case 'En Preparacion':
                return ['titulo' => 'Estamos preparando tu pedido', 'detalle' => 'Nuestro equipo ya está alistando los productos de tu compra.', 'color' => '#15803D'];
            case 'En Camino':
                return ['titulo' => 'Tu pedido está en camino', 'detalle' => 'El repartidor ya salió con tu pedido. Mantente atento a tu teléfono.', 'color' => '#0284c7'];
            case 'Listo para Recojo':
                return ['titulo' => 'Tu pedido está listo para recojo', 'detalle' => 'Puedes acercarte a la tienda a recoger tu pedido.', 'color' => '#15803D'];
            case 'Entregado':
                return ['titulo' => 'Pedido entregado', 'detalle' => '¡Gracias por tu compra! Esperamos que disfrutes tus productos.', 'color' => '#15803D'];
            case 'Cancelado':
                return ['titulo' => 'Pedido cancelado', 'detalle' => 'Tu pedido fue cancelado. Si tienes alguna consulta, contáctanos.', 'color' => '#b91c1c'];
            default:
                return ['titulo' => 'Actualización de tu pedido', 'detalle' => 'El estado de tu pedido se ha actualizado.', 'color' => '#15803D'];
        }
    }
}
