<?php
require_once __DIR__ . '/../Core/dataBase.php';

/**
 * Servicio único de "Entrada de mercancía".
 *
 * Reposición, Compra a proveedor y Stock inicial de un producto registran
 * SIEMPRE el mismo efecto contable en una sola transacción:
 *   1. reposicion  (documento de la entrada)
 *   2. producto.stock  += cantidad
 *   3. inventario.stock_actual += cantidad (crea la fila si no existe)
 *   4. kardex  (movimiento ENTRADA)
 *   5. opcional: egreso de caja chica (solo compras)
 */
class EntradaMercanciaModel {
    private $conn;

    public function __construct() {
        $db = new DataBase();
        $this->conn = $db->getConnection();
    }

    private function truncar($texto, $largo = 100) {
        $texto = (string)$texto;
        return function_exists('mb_substr') ? mb_substr($texto, 0, $largo) : substr($texto, 0, $largo);
    }

    private function insertarKardex($idProducto, $idUsuario, $tipo, $motivo, $cantidad, $costo, $precio, $stockAnterior, $stockActual, $refId = null, $refTipo = null) {
        $sql = "INSERT INTO kardex (id_producto, id_usuario, tipo_movimiento, motivo, cantidad, costo_unitario, precio_unitario, stock_anterior, stock_actual, referencia_id, referencia_tipo, fecha)
                VALUES (:id_producto, :id_usuario, :tipo, :motivo, :cantidad, :costo, :precio, :stock_ant, :stock_act, :ref_id, :ref_tipo, NOW())";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id_producto', $idProducto, PDO::PARAM_INT);
        $stmt->bindParam(':id_usuario', $idUsuario, PDO::PARAM_INT);
        $stmt->bindValue(':tipo', $tipo);
        $stmt->bindValue(':motivo', $this->truncar($motivo, 100));
        $stmt->bindParam(':cantidad', $cantidad, PDO::PARAM_INT);
        $stmt->bindValue(':costo', $costo);
        $stmt->bindValue(':precio', $precio);
        $stmt->bindParam(':stock_ant', $stockAnterior, PDO::PARAM_INT);
        $stmt->bindParam(':stock_act', $stockActual, PDO::PARAM_INT);
        $stmt->bindValue(':ref_id', $refId, $refId ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindValue(':ref_tipo', $refTipo, $refTipo ? PDO::PARAM_STR : PDO::PARAM_NULL);
        return $stmt->execute();
    }

    /**
     * @param array $d  [
     *   id_producto, id_proveedor, id_usuario, cantidad, costo_unitario,
     *   precio_venta_sugerido, nro_comprobante, lote, fecha_vencimiento,
     *   observacion, proveedor (texto libre), tipo ('reposicion'|'compra'|'stock_inicial'),
     *   descontar_caja (bool), id_sesion_caja, datos_nuevo_producto (array|null)
     * ]
     */
    public function registrarEntradaMercancia(array $d) {
        $idProducto       = (int)($d['id_producto'] ?? 0);
        $idProveedor      = !empty($d['id_proveedor']) ? (int)$d['id_proveedor'] : null;
        $idUsuario        = (int)($d['id_usuario'] ?? 0);
        $cantidad         = (int)($d['cantidad'] ?? 0);
        $costoUnit        = (float)($d['costo_unitario'] ?? 0);
        $precioSugerido   = (isset($d['precio_venta_sugerido']) && $d['precio_venta_sugerido'] !== null && $d['precio_venta_sugerido'] !== '') ? (float)$d['precio_venta_sugerido'] : null;
        $precioAnterior   = (isset($d['precio_anterior']) && $d['precio_anterior'] !== null && $d['precio_anterior'] !== '') ? (float)$d['precio_anterior'] : null;
        $actualizarOferta = !empty($d['actualizar_oferta']);
        $comprobante      = trim((string)($d['nro_comprobante'] ?? ''));
        $lote             = trim((string)($d['lote'] ?? ''));
        $vencimiento      = !empty($d['fecha_vencimiento']) ? trim((string)$d['fecha_vencimiento']) : null;
        $observacion      = trim((string)($d['observacion'] ?? ''));
        $proveedorTexto   = trim((string)($d['proveedor'] ?? ''));
        $tipo             = strtolower(trim((string)($d['tipo'] ?? 'reposicion')));
        $descontarCaja    = !empty($d['descontar_caja']);
        $idSesionCaja     = !empty($d['id_sesion_caja']) ? (int)$d['id_sesion_caja'] : null;
        $datosNuevoProd   = $d['datos_nuevo_producto'] ?? null;

        if ($idUsuario <= 0) {
            return ['ok' => false, 'mensaje' => 'Usuario no válido.'];
        }
        if ($cantidad <= 0) {
            return ['ok' => false, 'mensaje' => 'La cantidad debe ser mayor a cero.'];
        }
        if ($tipo === 'compra' && $costoUnit <= 0) {
            return ['ok' => false, 'mensaje' => 'Cantidad y costo unitario deben ser mayores a cero.'];
        }
        if ($costoUnit < 0) {
            $costoUnit = 0;
        }

        try {
            $this->conn->beginTransaction();

            // Producto nuevo al vuelo (solo flujo de compra)
            if ($idProducto <= 0 && !empty($datosNuevoProd['nombre'])) {
                $nomProd   = trim($datosNuevoProd['nombre']);
                $skuProd   = trim($datosNuevoProd['sku'] ?? '');
                if ($skuProd === '') {
                    $skuProd = 'PROD-' . rand(100000, 999999);
                }
                $codBarras = !empty($datosNuevoProd['codigo_barras']) ? trim($datosNuevoProd['codigo_barras']) : null;
                $precioVenta = $precioSugerido !== null ? $precioSugerido : round($costoUnit * 1.30, 2);
                $idCat = !empty($datosNuevoProd['id_categoria']) ? (int)$datosNuevoProd['id_categoria'] : 1;

                $sqlNewP = "INSERT INTO producto (nombre, titulo, codigo, codigo_barras, precio_costo, precio, precio_anterior, stock, id_categoria, activo, unidad_medida, imagen)
                            VALUES (:nom, :tit, :cod, :barras, :costo, :precio, :precio_anterior, 0, :id_cat, 1, 'UND', 'Images/productos/default.png')";
                $stmtNewP = $this->conn->prepare($sqlNewP);
                $stmtNewP->bindValue(':nom', $nomProd);
                $stmtNewP->bindValue(':tit', $nomProd);
                $stmtNewP->bindValue(':cod', $skuProd);
                $stmtNewP->bindValue(':barras', $codBarras);
                $stmtNewP->bindValue(':costo', $costoUnit);
                $stmtNewP->bindValue(':precio', $precioVenta);
                $stmtNewP->bindValue(':precio_anterior', $precioAnterior);
                $stmtNewP->bindParam(':id_cat', $idCat, PDO::PARAM_INT);
                $stmtNewP->execute();
                $idProducto = (int)$this->conn->lastInsertId();
            }

            if ($idProducto <= 0) {
                $this->conn->rollBack();
                return ['ok' => false, 'mensaje' => 'Producto no válido.'];
            }

            // Bloquea y lee el producto
            $stmtP = $this->conn->prepare("SELECT id_producto, nombre, titulo, stock, precio_costo, precio FROM producto WHERE id_producto = :id FOR UPDATE");
            $stmtP->bindParam(':id', $idProducto, PDO::PARAM_INT);
            $stmtP->execute();
            $prod = $stmtP->fetch(PDO::FETCH_ASSOC);

            if (!$prod) {
                $this->conn->rollBack();
                return ['ok' => false, 'mensaje' => 'Producto no encontrado.'];
            }

            $nombreProd = $prod['nombre'] !== '' ? $prod['nombre'] : $prod['titulo'];

            // Nombre del proveedor (tabla o texto libre)
            $nombreProveedor = $proveedorTexto !== '' ? $proveedorTexto : 'Proveedor Ocasional';
            if ($idProveedor) {
                try {
                    $stmtProv = $this->conn->prepare("SELECT razon_social FROM proveedor WHERE id_proveedor = :id");
                    $stmtProv->bindParam(':id', $idProveedor, PDO::PARAM_INT);
                    $stmtProv->execute();
                    $rz = $stmtProv->fetchColumn();
                    if ($rz) $nombreProveedor = $rz;
                } catch (Exception $eProv) {
                    // se conserva el texto libre
                }
            }

            $stockAnterior = (int)$prod['stock'];
            $nuevoStock = $stockAnterior + $cantidad;

            // 1. Documento de entrada
            $sqlRepo = "INSERT INTO reposicion (id_producto, id_proveedor, id_usuario, cantidad, costo_unitario, precio_venta_sugerido, proveedor, nro_comprobante, lote, fecha_vencimiento, observacion, fecha)
                        VALUES (:id_prod, :id_prov, :id_user, :cant, :costo, :sug, :prov_nom, :comp, :lote, :venc, :obs, NOW())";
            $stmtRepo = $this->conn->prepare($sqlRepo);
            $stmtRepo->bindParam(':id_prod', $idProducto, PDO::PARAM_INT);
            $stmtRepo->bindValue(':id_prov', $idProveedor, $idProveedor ? PDO::PARAM_INT : PDO::PARAM_NULL);
            $stmtRepo->bindParam(':id_user', $idUsuario, PDO::PARAM_INT);
            $stmtRepo->bindParam(':cant', $cantidad, PDO::PARAM_INT);
            $stmtRepo->bindValue(':costo', $costoUnit);
            $stmtRepo->bindValue(':sug', $precioSugerido, $precioSugerido !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmtRepo->bindValue(':prov_nom', $nombreProveedor);
            $stmtRepo->bindValue(':comp', $comprobante);
            $stmtRepo->bindValue(':lote', $lote);
            $stmtRepo->bindValue(':venc', $vencimiento, $vencimiento ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmtRepo->bindValue(':obs', $observacion);
            $stmtRepo->execute();
            $idReposicion = (int)$this->conn->lastInsertId();

            // 2. Producto: stock + costos
            $sqlUpdProd = "UPDATE producto SET stock = :stk";
            if ($costoUnit > 0) {
                $sqlUpdProd .= ", precio_costo = :costo";
            }
            if ($precioSugerido !== null) {
                $sqlUpdProd .= ", precio = :nuevo_precio";
            }
            if ($actualizarOferta) {
                $sqlUpdProd .= ", precio_anterior = :precio_anterior";
            }
            $sqlUpdProd .= " WHERE id_producto = :id";
            $stmtUpdProd = $this->conn->prepare($sqlUpdProd);
            $stmtUpdProd->bindParam(':stk', $nuevoStock, PDO::PARAM_INT);
            if ($costoUnit > 0) $stmtUpdProd->bindValue(':costo', $costoUnit);
            if ($precioSugerido !== null) $stmtUpdProd->bindValue(':nuevo_precio', $precioSugerido);
            if ($actualizarOferta) $stmtUpdProd->bindValue(':precio_anterior', $precioAnterior, $precioAnterior === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmtUpdProd->bindParam(':id', $idProducto, PDO::PARAM_INT);
            $stmtUpdProd->execute();

            // 3. Inventario: actualiza o crea la fila (evita divergencia producto/inventario)
            $stmtInv = $this->conn->prepare("SELECT id_inventario FROM inventario WHERE id_producto = :id FOR UPDATE");
            $stmtInv->bindParam(':id', $idProducto, PDO::PARAM_INT);
            $stmtInv->execute();
            $idInventario = $stmtInv->fetchColumn();

            if ($idInventario) {
                $stmtUpdInv = $this->conn->prepare("UPDATE inventario SET stock_actual = :stk, ultima_act = NOW() WHERE id_producto = :id");
                $stmtUpdInv->bindParam(':stk', $nuevoStock, PDO::PARAM_INT);
                $stmtUpdInv->bindParam(':id', $idProducto, PDO::PARAM_INT);
                $stmtUpdInv->execute();
            } else {
                $stmtInsInv = $this->conn->prepare("INSERT INTO inventario (id_producto, stock_actual, stock_minimo, ubicacion, ultima_act)
                                                    VALUES (:id, :stk, 5, 'Almacén principal', NOW())");
                $stmtInsInv->bindParam(':id', $idProducto, PDO::PARAM_INT);
                $stmtInsInv->bindParam(':stk', $nuevoStock, PDO::PARAM_INT);
                $stmtInsInv->execute();
            }

            // 4. Kardex (siempre escribe movimiento)
            if ($tipo === 'stock_inicial') {
                $motivoKardex = 'Stock inicial - Registro de producto';
            } elseif ($tipo === 'compra') {
                $motivoKardex = 'Compra Proveedor: ' . $nombreProveedor . ($comprobante ? ' (' . $comprobante . ')' : '');
            } else {
                $motivoKardex = 'Reposición: ' . $nombreProveedor;
            }
            $this->insertarKardex(
                $idProducto,
                $idUsuario,
                'ENTRADA',
                $motivoKardex,
                $cantidad,
                $costoUnit,
                $precioSugerido !== null ? $precioSugerido : ($prod['precio'] ?? 0),
                $stockAnterior,
                $nuevoStock,
                $idReposicion,
                'reposicion'
            );

            // 5. Egreso de caja chica (solo si se solicita)
            if ($descontarCaja && $idSesionCaja) {
                $totalCompra = round($cantidad * $costoUnit, 2);
                $stmtCaja = $this->conn->prepare("INSERT INTO movimiento_caja (id_sesion, id_usuario, tipo, categoria, monto, motivo, comprobante_ref, fecha)
                                                  VALUES (:id_sesion, :id_user, 'EGRESO', 'proveedor', :monto, :motivo, :comp, NOW())");
                $motivoEgreso = 'Pago Compra Proveedor: ' . $nombreProveedor . ' (' . $nombreProd . ')';
                $stmtCaja->bindParam(':id_sesion', $idSesionCaja, PDO::PARAM_INT);
                $stmtCaja->bindParam(':id_user', $idUsuario, PDO::PARAM_INT);
                $stmtCaja->bindValue(':monto', $totalCompra);
                $stmtCaja->bindValue(':motivo', $this->truncar($motivoEgreso, 255));
                $stmtCaja->bindValue(':comp', $comprobante);
                $stmtCaja->execute();

                $stmtUpdCaja = $this->conn->prepare("UPDATE sesion_caja SET total_egresos = total_egresos + :monto WHERE id_sesion = :id_sesion");
                $stmtUpdCaja->bindValue(':monto', $totalCompra);
                $stmtUpdCaja->bindParam(':id_sesion', $idSesionCaja, PDO::PARAM_INT);
                $stmtUpdCaja->execute();
            }

            $this->conn->commit();

            if ($tipo === 'compra') {
                $mensaje = 'Compra registrada con éxito. Se agregaron +' . $cantidad . ' unidades a "' . $nombreProd . '" (Stock actual: ' . $nuevoStock . ')';
            } elseif ($tipo === 'stock_inicial') {
                $mensaje = 'Stock inicial registrado: +' . $cantidad . ' unidades a "' . $nombreProd . '" (Stock actual: ' . $nuevoStock . ')';
            } else {
                $mensaje = 'Stock repuesto correctamente: +' . $cantidad . ' unidades a "' . $nombreProd . '" (Stock actual: ' . $nuevoStock . ')';
            }
            return [
                'ok' => true,
                'mensaje' => $mensaje,
                'nuevo_stock' => $nuevoStock,
                'id_reposicion' => $idReposicion
            ];

        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            return ['ok' => false, 'mensaje' => 'Error: ' . $e->getMessage()];
        }
    }
}
