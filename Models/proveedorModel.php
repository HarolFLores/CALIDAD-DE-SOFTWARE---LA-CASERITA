<?php
require_once __DIR__ . '/../Core/dataBase.php';
require_once __DIR__ . '/kardexModel.php';

class ProveedorModel {
    private $conn;
    private $kardexModel;

    public function __construct() {
        $db = new DataBase();
        $this->conn = $db->getConnection();
        $this->kardexModel = new KardexModel();
    }

    public function listarProveedores($soloActivos = false) {
        $sql = "SELECT p.*, 
                       (SELECT COUNT(*) FROM reposicion r WHERE r.id_proveedor = p.id_proveedor) as total_compras,
                       (SELECT COALESCE(SUM(r.cantidad * r.costo_unitario), 0) FROM reposicion r WHERE r.id_proveedor = p.id_proveedor) as monto_comprado
                FROM proveedor p";
        if ($soloActivos) {
            $sql .= " WHERE p.activo = 1";
        }
        $sql .= " ORDER BY p.activo DESC, p.razon_social ASC";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerProveedor($id) {
        $sql = "SELECT * FROM proveedor WHERE id_proveedor = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function obtenerEstadisticasProveedores() {
        $sql = "SELECT 
                    COUNT(*) as total_proveedores,
                    COALESCE(SUM(CASE WHEN activo = 1 THEN 1 ELSE 0 END), 0) as activos,
                    COALESCE(SUM(CASE WHEN activo = 0 THEN 1 ELSE 0 END), 0) as inactivos,
                    (SELECT COUNT(*) FROM reposicion) as total_compras,
                    (SELECT COALESCE(SUM(cantidad * costo_unitario), 0) FROM reposicion) as monto_total_compras
                FROM proveedor";
        $stmt = $this->conn->query($sql);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function crearProveedor($ruc, $razonSocial, $contacto = '', $telefono = '', $correo = '', $direccion = '') {
        $sql = "INSERT INTO proveedor (ruc, razon_social, contacto_nombre, telefono, correo, direccion, activo, fecha_registro)
                VALUES (:ruc, :razon, :contacto, :tel, :correo, :dir, 1, NOW())";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':ruc', $ruc);
        $stmt->bindParam(':razon', $razonSocial);
        $stmt->bindParam(':contacto', $contacto);
        $stmt->bindParam(':tel', $telefono);
        $stmt->bindParam(':correo', $correo);
        $stmt->bindParam(':dir', $direccion);
        return $stmt->execute();
    }

    public function actualizarProveedor($id, $ruc, $razonSocial, $contacto = '', $telefono = '', $correo = '', $direccion = '') {
        $sql = "UPDATE proveedor SET 
                    ruc = :ruc, 
                    razon_social = :razon, 
                    contacto_nombre = :contacto, 
                    telefono = :tel, 
                    correo = :correo, 
                    direccion = :dir 
                WHERE id_proveedor = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':ruc', $ruc);
        $stmt->bindParam(':razon', $razonSocial);
        $stmt->bindParam(':contacto', $contacto);
        $stmt->bindParam(':tel', $telefono);
        $stmt->bindParam(':correo', $correo);
        $stmt->bindParam(':dir', $direccion);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function cambiarEstado($id, $activo) {
        $sql = "UPDATE proveedor SET activo = :activo WHERE id_proveedor = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':activo', $activo, PDO::PARAM_INT);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Registra una compra / reposición de mercadería a un proveedor
     * Permite producto existente o creación al vuelo de nuevo producto
     */
    public function registrarCompraReposicion(
        $idProducto,
        $idProveedor,
        $idUsuario,
        $cantidad,
        $costoUnit,
        $precioSugerido = null,
        $comprobante = '',
        $lote = '',
        $vencimiento = null,
        $observacion = '',
        $descontarDeCaja = false,
        $idSesionCaja = null,
        $datosNuevoProducto = null,
        $precioAnterior = null,
        $actualizarOferta = false
    ) {
        require_once __DIR__ . '/entradaMercanciaModel.php';
        $servicio = new EntradaMercanciaModel();
        return $servicio->registrarEntradaMercancia([
            'id_producto' => $idProducto,
            'id_proveedor' => $idProveedor,
            'id_usuario' => $idUsuario,
            'cantidad' => $cantidad,
            'costo_unitario' => $costoUnit,
            'precio_venta_sugerido' => $precioSugerido,
            'nro_comprobante' => $comprobante,
            'lote' => $lote,
            'fecha_vencimiento' => $vencimiento,
            'observacion' => $observacion,
            'tipo' => 'compra',
            'descontar_caja' => $descontarDeCaja,
            'id_sesion_caja' => $idSesionCaja,
            'datos_nuevo_producto' => $datosNuevoProducto,
            'precio_anterior' => $precioAnterior,
            'actualizar_oferta' => $actualizarOferta
        ]);
    }

    public function listarReposiciones($limite = 500) {
        $sql = "SELECT r.*, COALESCE(NULLIF(p.nombre, ''), p.titulo) as producto_nombre, p.codigo, p.codigo_barras,
                       pv.razon_social as proveedor_razon_social,
                       u.nombre as usuario_nombre, u.apellido as usuario_apellido
                FROM reposicion r
                JOIN producto p ON r.id_producto = p.id_producto
                LEFT JOIN proveedor pv ON r.id_proveedor = pv.id_proveedor
                JOIN usuario u ON r.id_usuario = u.id_usuario
                ORDER BY r.fecha DESC LIMIT :limite";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':limite', (int)$limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
