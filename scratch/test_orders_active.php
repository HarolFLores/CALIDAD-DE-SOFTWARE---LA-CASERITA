<?php
require_once __DIR__ . '/../Models/pedidoModel.php';
$m = new PedidoModel();
$pedidos = $m->obtenerPedidosActivosPorUsuario(2);
echo "Pedidos activos obtenidos: " . count($pedidos) . PHP_EOL;
foreach ($pedidos as $p) {
    echo "#" . $p['id_pedido'] . " - " . $p['estado'] . " - Total: S/ " . $p['total'] . PHP_EOL;
}
