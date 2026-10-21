<?php
require_once __DIR__ . '/../Core/dataBase.php';

class ReportesModel {
    private $conn;

    public function __construct() {
        $db = new DataBase();
        $this->conn = $db->getConnection();
    }

    public function obtenerMetricasFinancieras() {
        // 1. Total Ventas (excluyendo cancelados)
        $sqlVentas = "SELECT 
                        COALESCE(SUM(total), 0) as total_ventas,
                        COUNT(*) as total_pedidos,
                        COALESCE(SUM(CASE WHEN canal = 'POS' THEN total ELSE 0 END), 0) as ventas_pos,
                        COALESCE(SUM(CASE WHEN canal = 'WEB' THEN total ELSE 0 END), 0) as ventas_web,
                        COALESCE(SUM(CASE WHEN DATE(fecha) = CURDATE() THEN total ELSE 0 END), 0) as ventas_hoy
                      FROM pedido 
                      WHERE estado NOT IN ('Cancelado')";
        $ventas = $this->conn->query($sqlVentas)->fetch(PDO::FETCH_ASSOC);

        // 2. Costo de Mercadería Vendida (COGS)
        $sqlCogs = "SELECT 
                        COALESCE(SUM(dp.cantidad * COALESCE(NULLIF(dp.costo_unitario, 0), p.precio_costo, 0)), 0) as cogs_total
                    FROM detalle_pedido dp
                    JOIN pedido ped ON dp.id_pedido = ped.id_pedido
                    JOIN producto p ON dp.id_producto = p.id_producto
                    WHERE ped.estado NOT IN ('Cancelado')";
        $cogsData = $this->conn->query($sqlCogs)->fetch(PDO::FETCH_ASSOC);
        $cogs = (float)$cogsData['cogs_total'];

        // 3. Gastos Operativos y Egresos de Caja Chica
        $sqlEgresos = "SELECT 
                        COALESCE(SUM(monto), 0) as total_egresos,
                        COALESCE(SUM(CASE WHEN categoria = 'insumos' THEN monto ELSE 0 END), 0) as egresos_insumos,
                        COALESCE(SUM(CASE WHEN categoria = 'servicios' THEN monto ELSE 0 END), 0) as egresos_servicios,
                        COALESCE(SUM(CASE WHEN categoria = 'proveedor' THEN monto ELSE 0 END), 0) as egresos_proveedor,
                        COALESCE(SUM(CASE WHEN categoria = 'otro' THEN monto ELSE 0 END), 0) as egresos_otros
                       FROM movimiento_caja 
                       WHERE tipo = 'EGRESO'";
        $egresosData = $this->conn->query($sqlEgresos)->fetch(PDO::FETCH_ASSOC);
        $egresos = (float)$egresosData['total_egresos'];

        // 4. Ganancia Bruta y Ganancia Neta
        $totalVentas = (float)$ventas['total_ventas'];
        $gananciaBruta = $totalVentas - $cogs;
        $gananciaNeta = $gananciaBruta - $egresos;
        $margenNeto = $totalVentas > 0 ? round(($gananciaNeta / $totalVentas) * 100, 1) : 0;

        // 5. Ventas por Método de Pago
        $sqlMetodos = "SELECT 
                        COALESCE(NULLIF(metodo_pago, ''), 'Efectivo') as metodo,
                        COUNT(*) as cantidad_transacciones,
                        COALESCE(SUM(total), 0) as total_recaudado
                       FROM pedido
                       WHERE estado NOT IN ('Cancelado')
                       GROUP BY metodo
                       ORDER BY total_recaudado DESC";
        $metodos = $this->conn->query($sqlMetodos)->fetchAll(PDO::FETCH_ASSOC);

        // 6. Ventas por Empleado / Cajero
        $sqlCajeros = "SELECT 
                        u.id_usuario,
                        CONCAT(u.nombre, ' ', COALESCE(u.apellido, '')) as empleado_nombre,
                        u.rol,
                        COUNT(p.id_pedido) as tickets_atendidos,
                        COALESCE(SUM(p.total), 0) as total_vendido
                       FROM usuario u
                       LEFT JOIN pedido p ON (p.id_cajero = u.id_usuario OR (p.canal = 'POS' AND p.id_usuario = u.id_usuario)) AND p.estado NOT IN ('Cancelado')
                       WHERE u.rol IN ('cajero', 'admin')
                       GROUP BY u.id_usuario, u.nombre, u.apellido, u.rol
                       ORDER BY total_vendido DESC";
        $cajeros = $this->conn->query($sqlCajeros)->fetchAll(PDO::FETCH_ASSOC);

        // 7. Top 5 Productos más vendidos y rentables
        $sqlTop = "SELECT 
                    p.id_producto,
                    p.nombre,
                    p.codigo,
                    p.precio_costo,
                    p.precio,
                    SUM(dp.cantidad) as total_unidades_vendidas,
                    SUM(dp.subtotal) as total_ingresos,
                    SUM(dp.cantidad * (dp.precio_unitario - COALESCE(NULLIF(dp.costo_unitario, 0), p.precio_costo, 0))) as ganancia_aportada
                   FROM detalle_pedido dp
                   JOIN pedido ped ON dp.id_pedido = ped.id_pedido
                   JOIN producto p ON dp.id_producto = p.id_producto
                   WHERE ped.estado NOT IN ('Cancelado')
                   GROUP BY p.id_producto, p.nombre, p.codigo, p.precio_costo, p.precio
                   ORDER BY total_unidades_vendidas DESC LIMIT 5";
        $topProductos = $this->conn->query($sqlTop)->fetchAll(PDO::FETCH_ASSOC);

        return [
            'total_ventas' => $totalVentas,
            'total_pedidos' => (int)$ventas['total_pedidos'],
            'ventas_pos' => (float)$ventas['ventas_pos'],
            'ventas_web' => (float)$ventas['ventas_web'],
            'ventas_hoy' => (float)$ventas['ventas_hoy'],
            'cogs' => $cogs,
            'ganancia_bruta' => $gananciaBruta,
            'total_egresos' => $egresos,
            'egresos_detalle' => $egresosData,
            'ganancia_neta' => $gananciaNeta,
            'margen_neto' => $margenNeto,
            'metodos_pago' => $metodos,
            'ventas_cajeros' => $cajeros,
            'top_productos' => $topProductos
        ];
    }

    private function consultarReporte($sql, array $params) {
        $stmt = $this->conn->prepare($sql);
        foreach ($params as $clave => $valor) {
            $stmt->bindValue($clave, $valor);
        }
        $stmt->execute();
        return $stmt;
    }

    public function obtenerReporteVentas($desde, $hasta, $canal = 'all') {
        $desde = $desde ?: date('Y-m-01');
        $hasta = $hasta ?: date('Y-m-d');
        $canal = in_array($canal, ['POS', 'WEB'], true) ? $canal : 'all';

        $where = "ped.estado NOT IN ('Cancelado') AND DATE(ped.fecha) BETWEEN :desde AND :hasta";
        $params = [':desde' => $desde, ':hasta' => $hasta];
        if ($canal !== 'all') {
            $where .= " AND ped.canal = :canal";
            $params[':canal'] = $canal;
        }

        $resumen = $this->consultarReporte(
            "SELECT COALESCE(SUM(ped.total), 0) AS total_ventas,
                    COUNT(*) AS total_pedidos,
                    COALESCE(AVG(ped.total), 0) AS ticket_promedio
             FROM pedido ped
             WHERE {$where}",
            $params
        )->fetch(PDO::FETCH_ASSOC);

        $cogsData = $this->consultarReporte(
            "SELECT COALESCE(SUM(dp.cantidad * COALESCE(NULLIF(dp.costo_unitario, 0), p.precio_costo, 0)), 0) AS cogs
             FROM detalle_pedido dp
             JOIN pedido ped ON dp.id_pedido = ped.id_pedido
             JOIN producto p ON dp.id_producto = p.id_producto
             WHERE {$where}",
            $params
        )->fetch(PDO::FETCH_ASSOC);

        $porDia = $this->consultarReporte(
            "SELECT DATE(ped.fecha) AS fecha,
                    COUNT(*) AS pedidos,
                    COALESCE(SUM(ped.total), 0) AS total
             FROM pedido ped
             WHERE {$where}
             GROUP BY DATE(ped.fecha)
             ORDER BY fecha ASC",
            $params
        )->fetchAll(PDO::FETCH_ASSOC);

        $costosPorDia = $this->consultarReporte(
            "SELECT DATE(ped.fecha) AS fecha,
                    COALESCE(SUM(dp.cantidad * COALESCE(NULLIF(dp.costo_unitario, 0), p.precio_costo, 0)), 0) AS costo
             FROM detalle_pedido dp
             JOIN pedido ped ON dp.id_pedido = ped.id_pedido
             JOIN producto p ON dp.id_producto = p.id_producto
             WHERE {$where}
             GROUP BY DATE(ped.fecha)",
            $params
        )->fetchAll(PDO::FETCH_ASSOC);
        $mapaCostos = [];
        foreach ($costosPorDia as $filaCosto) {
            $mapaCostos[$filaCosto['fecha']] = (float)$filaCosto['costo'];
        }
        foreach ($porDia as &$filaDia) {
            $costoDia = (float)($mapaCostos[$filaDia['fecha']] ?? 0);
            $totalDia = (float)$filaDia['total'];
            $filaDia['total'] = $totalDia;
            $filaDia['pedidos'] = (int)$filaDia['pedidos'];
            $filaDia['costo'] = $costoDia;
            $filaDia['ganancia'] = $totalDia - $costoDia;
        }
        unset($filaDia);

        $porProducto = $this->consultarReporte(
            "SELECT p.id_producto,
                    COALESCE(NULLIF(p.nombre, ''), p.titulo) AS producto,
                    COALESCE(c.nombre, 'Sin categoría') AS categoria,
                    SUM(dp.cantidad) AS unidades,
                    SUM(dp.subtotal) AS ingresos,
                    SUM(dp.cantidad * COALESCE(NULLIF(dp.costo_unitario, 0), p.precio_costo, 0)) AS costo
             FROM detalle_pedido dp
             JOIN pedido ped ON dp.id_pedido = ped.id_pedido
             JOIN producto p ON dp.id_producto = p.id_producto
             LEFT JOIN categoria c ON p.id_categoria = c.id_categoria
             WHERE {$where}
             GROUP BY p.id_producto, producto, categoria
             ORDER BY ingresos DESC",
            $params
        )->fetchAll(PDO::FETCH_ASSOC);
        foreach ($porProducto as &$productoReporte) {
            $ingresosProducto = (float)$productoReporte['ingresos'];
            $costoProducto = (float)$productoReporte['costo'];
            $productoReporte['unidades'] = (float)$productoReporte['unidades'];
            $productoReporte['ingresos'] = $ingresosProducto;
            $productoReporte['costo'] = $costoProducto;
            $productoReporte['ganancia'] = $ingresosProducto - $costoProducto;
            $productoReporte['margen'] = $ingresosProducto > 0 ? round((($ingresosProducto - $costoProducto) / $ingresosProducto) * 100, 1) : 0;
        }
        unset($productoReporte);

        $porCategoria = $this->consultarReporte(
            "SELECT COALESCE(c.nombre, 'Sin categoría') AS categoria,
                    SUM(dp.cantidad) AS unidades,
                    SUM(dp.subtotal) AS ingresos,
                    SUM(dp.cantidad * COALESCE(NULLIF(dp.costo_unitario, 0), p.precio_costo, 0)) AS costo
             FROM detalle_pedido dp
             JOIN pedido ped ON dp.id_pedido = ped.id_pedido
             JOIN producto p ON dp.id_producto = p.id_producto
             LEFT JOIN categoria c ON p.id_categoria = c.id_categoria
             WHERE {$where}
             GROUP BY categoria
             ORDER BY ingresos DESC",
            $params
        )->fetchAll(PDO::FETCH_ASSOC);
        foreach ($porCategoria as &$categoriaReporte) {
            $ingresosCategoria = (float)$categoriaReporte['ingresos'];
            $costoCategoria = (float)$categoriaReporte['costo'];
            $categoriaReporte['unidades'] = (float)$categoriaReporte['unidades'];
            $categoriaReporte['ingresos'] = $ingresosCategoria;
            $categoriaReporte['costo'] = $costoCategoria;
            $categoriaReporte['ganancia'] = $ingresosCategoria - $costoCategoria;
            $categoriaReporte['margen'] = $ingresosCategoria > 0 ? round((($ingresosCategoria - $costoCategoria) / $ingresosCategoria) * 100, 1) : 0;
        }
        unset($categoriaReporte);

        $porMetodo = $this->consultarReporte(
            "SELECT COALESCE(NULLIF(ped.metodo_pago, ''), 'Sin método') AS metodo,
                    COUNT(*) AS pedidos,
                    COALESCE(SUM(ped.total), 0) AS total
             FROM pedido ped
             WHERE {$where}
             GROUP BY metodo
             ORDER BY total DESC",
            $params
        )->fetchAll(PDO::FETCH_ASSOC);
        foreach ($porMetodo as &$metodoReporte) {
            $metodoReporte['pedidos'] = (int)$metodoReporte['pedidos'];
            $metodoReporte['total'] = (float)$metodoReporte['total'];
        }
        unset($metodoReporte);

        $porCanal = $this->consultarReporte(
            "SELECT COALESCE(NULLIF(ped.canal, ''), 'Sin canal') AS canal,
                    COUNT(*) AS pedidos,
                    COALESCE(SUM(ped.total), 0) AS total
             FROM pedido ped
             WHERE {$where}
             GROUP BY canal
             ORDER BY total DESC",
            $params
        )->fetchAll(PDO::FETCH_ASSOC);
        foreach ($porCanal as &$canalReporte) {
            $canalReporte['pedidos'] = (int)$canalReporte['pedidos'];
            $canalReporte['total'] = (float)$canalReporte['total'];
        }
        unset($canalReporte);

        $totalVentas = (float)($resumen['total_ventas'] ?? 0);
        $totalPedidos = (int)($resumen['total_pedidos'] ?? 0);
        $cogs = (float)($cogsData['cogs'] ?? 0);
        $gananciaBruta = $totalVentas - $cogs;

        return [
            'desde' => $desde,
            'hasta' => $hasta,
            'canal' => $canal,
            'total_ventas' => $totalVentas,
            'total_pedidos' => $totalPedidos,
            'ticket_promedio' => $totalPedidos > 0 ? $totalVentas / $totalPedidos : 0,
            'cogs' => $cogs,
            'ganancia_bruta' => $gananciaBruta,
            'margen_bruto' => $totalVentas > 0 ? round(($gananciaBruta / $totalVentas) * 100, 1) : 0,
            'por_dia' => $porDia,
            'por_producto' => $porProducto,
            'por_categoria' => $porCategoria,
            'por_metodo' => $porMetodo,
            'por_canal' => $porCanal
        ];
    }
}
