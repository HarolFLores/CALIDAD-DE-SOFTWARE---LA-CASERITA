<?php
require_once __DIR__ . '/../Core/dataBase.php';
require_once __DIR__ . '/kardexModel.php';

class VerificacionPagoModel {
    private $conn;
    private $kardexModel;

    public function __construct() {
        $db = new DataBase();
        $this->conn = $db->getConnection();
        $this->kardexModel = new KardexModel();
    }

    public function listarVerificaciones($filtro = null) {
        $sql = "SELECT 
                    COALESCE(pv.id_verificacion, p.id_pedido) as id_verificacion,
                    p.id_pedido,
                    p.codigo_pedido,
                    p.total as pedido_total,
                    p.estado as pedido_estado,
                    u.nombre as cliente_nombre,
                    u.apellido as cliente_apellido,
                    u.correo as cliente_correo,
                    COALESCE(u.telefono, pv.celular_remitente, '—') as cliente_telefono,
                    mp.nombre as metodo_nombre,
                    COALESCE(pv.codigo_operacion, CASE WHEN LOWER(mp.nombre) LIKE '%yape%' OR LOWER(mp.nombre) LIKE '%plin%' THEN 'OTP-DIGITAL' ELSE 'AUT-PASARELA' END) as codigo_operacion,
                    COALESCE(pv.celular_remitente, u.telefono, '—') as celular_remitente,
                    COALESCE(pv.titular_origen, CONCAT(u.nombre, ' ', u.apellido)) as titular_origen,
                    COALESCE(pv.monto_reportado, p.total) as monto_reportado,
                    COALESCE(pv.captura_voucher, 'VERIFICACION_SISTEMA') as captura_voucher,
                    COALESCE(pv.estado_verificacion, CASE WHEN p.estado = 'Cancelado' THEN 'Rechazado' WHEN p.estado IN ('En Preparacion', 'En Camino', 'Entregado') THEN 'Aprobado' ELSE 'Pendiente' END) as estado_verificacion,
                    COALESCE(pv.fecha_envio, p.fecha_pedido) as fecha_envio,
                    pv.fecha_verificacion,
                    COALESCE(CONCAT(v.nombre, ' ', v.apellido), 'Sistema Automatizado') as verificador_nombre,
                    pv.motivo_rechazo,
                    CASE 
                        WHEN LOWER(mp.nombre) LIKE '%tarjeta%' THEN 'Pasarela Tarjeta (Aprobación Instantánea)'
                        WHEN LOWER(mp.nombre) LIKE '%yape%' THEN 'Pasarela Yape Directo (Validación OTP)'
                        WHEN LOWER(mp.nombre) LIKE '%plin%' THEN 'Pasarela Plin Directo (Validación OTP)'
                        ELSE 'Procesamiento Digital'
                    END as canal_pasarela
                FROM pedido p
                JOIN metodo_pago mp ON p.id_metodo = mp.id_metodo
                JOIN usuario u ON p.id_usuario = u.id_usuario
                LEFT JOIN pago_verificacion pv ON pv.id_pedido = p.id_pedido
                LEFT JOIN usuario v ON pv.id_usuario_verificador = v.id_usuario
                WHERE LOWER(mp.nombre) NOT LIKE '%efectivo%'";

        if ($filtro && in_array($filtro, ['Pendiente', 'Aprobado', 'Rechazado'], true)) {
            $sql .= " HAVING estado_verificacion = :est";
            $sql .= " ORDER BY fecha_envio DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':est' => $filtro]);
        } else {
            $sql .= " ORDER BY fecha_envio DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function registrarVerificacion($idPedido, $idMetodo, $codigoOperacion, $celularRemitente, $titularOrigen, $capturaVoucher, $montoReportado) {
        $sql = "INSERT INTO pago_verificacion (id_pedido, id_metodo, codigo_operacion, celular_remitente, titular_origen, captura_voucher, monto_reportado, estado_verificacion, fecha_envio)
                VALUES (:ped, :met, :cod, :cel, :tit, :cap, :monto, 'Pendiente', NOW())";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':ped', $idPedido, PDO::PARAM_INT);
        $stmt->bindParam(':met', $idMetodo, PDO::PARAM_INT);
        $stmt->bindParam(':cod', $codigoOperacion);
        $stmt->bindParam(':cel', $celularRemitente);
        $stmt->bindParam(':tit', $titularOrigen);
        $stmt->bindParam(':cap', $capturaVoucher);
        $stmt->bindParam(':monto', $montoReportado);
        return $stmt->execute();
    }

    public function aprobarPago($idVerificacion, $idUsuarioVerificador) {
        try {
            $this->conn->beginTransaction();

            $sqlV = "SELECT * FROM pago_verificacion WHERE id_verificacion = :id FOR UPDATE";
            $stmtV = $this->conn->prepare($sqlV);
            $stmtV->bindParam(':id', $idVerificacion, PDO::PARAM_INT);
            $stmtV->execute();
            $verif = $stmtV->fetch(PDO::FETCH_ASSOC);

            if (!$verif || $verif['estado_verificacion'] !== 'Pendiente') {
                $this->conn->rollBack();
                return ['ok' => false, 'mensaje' => 'La verificación no está en estado Pendiente'];
            }

            // 1. Actualizar estado de verificación
            $sqlUpdV = "UPDATE pago_verificacion SET 
                            estado_verificacion = 'Aprobado',
                            id_usuario_verificador = :user,
                            fecha_verificacion = NOW()
                        WHERE id_verificacion = :id";
            $stmtUpdV = $this->conn->prepare($sqlUpdV);
            $stmtUpdV->bindParam(':user', $idUsuarioVerificador, PDO::PARAM_INT);
            $stmtUpdV->bindParam(':id', $idVerificacion, PDO::PARAM_INT);
            $stmtUpdV->execute();

            // 2. Actualizar estado del pedido a 'En Preparacion'
            $idPedido = $verif['id_pedido'];
            $sqlUpdP = "UPDATE pedido SET estado = 'En Preparacion', leido_cliente = 0 WHERE id_pedido = :ped";
            $stmtUpdP = $this->conn->prepare($sqlUpdP);
            $stmtUpdP->bindParam(':ped', $idPedido, PDO::PARAM_INT);
            $stmtUpdP->execute();

            // 3. Descontar stock y registrar salidas en Kardex
            $sqlDet = "SELECT dp.*, p.stock, p.precio_costo, p.nombre 
                       FROM detalle_pedido dp
                       JOIN producto p ON dp.id_producto = p.id_producto
                       WHERE dp.id_pedido = :ped";
            $stmtDet = $this->conn->prepare($sqlDet);
            $stmtDet->bindParam(':ped', $idPedido, PDO::PARAM_INT);
            $stmtDet->execute();
            $items = $stmtDet->fetchAll(PDO::FETCH_ASSOC);

            foreach ($items as $item) {
                $cant = (int)$item['cantidad'];
                $stockAnt = (int)$item['stock'];
                $stockNuevo = max(0, $stockAnt - $cant);

                // Actualizar producto
                $this->conn->query("UPDATE producto SET stock = {$stockNuevo} WHERE id_producto = {$item['id_producto']}");
                $this->conn->query("UPDATE inventario SET stock_actual = {$stockNuevo} WHERE id_producto = {$item['id_producto']}");

                // Registrar en Kardex
                $this->kardexModel->registrarMovimiento(
                    $item['id_producto'],
                    $idUsuarioVerificador,
                    'SALIDA',
                    'Venta Web Aprobada (Pago Digital) #' . $idPedido,
                    $cant,
                    $item['precio_costo'],
                    $item['precio_unitario'],
                    $stockAnt,
                    $stockNuevo,
                    $idPedido,
                    'pedido'
                );
            }

            $this->conn->commit();
            return ['ok' => true, 'mensaje' => 'Pago validado y aprobado con éxito. Pedido pasó a "En Preparación"', 'id_pedido' => (int)$idPedido];

        } catch (Exception $e) {
            $this->conn->rollBack();
            return ['ok' => false, 'mensaje' => 'Error: ' . $e->getMessage()];
        }
    }

    public function rechazarPago($idVerificacion, $idUsuarioVerificador, $motivo) {
        try {
            $this->conn->beginTransaction();

            $sqlV = "SELECT * FROM pago_verificacion WHERE id_verificacion = :id FOR UPDATE";
            $stmtV = $this->conn->prepare($sqlV);
            $stmtV->bindParam(':id', $idVerificacion, PDO::PARAM_INT);
            $stmtV->execute();
            $verif = $stmtV->fetch(PDO::FETCH_ASSOC);

            if (!$verif || $verif['estado_verificacion'] !== 'Pendiente') {
                $this->conn->rollBack();
                return ['ok' => false, 'mensaje' => 'La verificación no está en estado Pendiente'];
            }

            // 1. Actualizar estado de verificación
            $sqlUpdV = "UPDATE pago_verificacion SET 
                            estado_verificacion = 'Rechazado',
                            id_usuario_verificador = :user,
                            motivo_rechazo = :motivo,
                            fecha_verificacion = NOW()
                        WHERE id_verificacion = :id";
            $stmtUpdV = $this->conn->prepare($sqlUpdV);
            $stmtUpdV->bindParam(':user', $idUsuarioVerificador, PDO::PARAM_INT);
            $stmtUpdV->bindParam(':motivo', $motivo);
            $stmtUpdV->bindParam(':id', $idVerificacion, PDO::PARAM_INT);
            $stmtUpdV->execute();

            // 2. Marcar pedido como cancelado
            $sqlUpdP = "UPDATE pedido SET estado = 'Cancelado', leido_cliente = 0, observacion = CONCAT(COALESCE(observacion,''), ' [Rechazo comprobante: ', :motivo, ']') WHERE id_pedido = :ped";
            $stmtUpdP = $this->conn->prepare($sqlUpdP);
            $stmtUpdP->bindParam(':motivo', $motivo);
            $stmtUpdP->bindParam(':ped', $verif['id_pedido'], PDO::PARAM_INT);
            $stmtUpdP->execute();

            $this->conn->commit();
            return ['ok' => true, 'mensaje' => 'Pago rechazado correctamente. El pedido fue marcado como Cancelado.', 'id_pedido' => (int)$verif['id_pedido']];

        } catch (Exception $e) {
            $this->conn->rollBack();
            return ['ok' => false, 'mensaje' => 'Error: ' . $e->getMessage()];
        }
    }
}
