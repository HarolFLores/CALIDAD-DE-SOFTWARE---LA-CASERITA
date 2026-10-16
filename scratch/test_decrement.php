<?php
require_once __DIR__ . '/../Models/pedidoModel.php';
$p = new PedidoModel();
$userId = 2; // Erik
echo "Pedidos no leídos actuales: " . $p->obtenerCantidadPedidosNoLeidos($userId) . "\n";
// Marcar pedido #30 como leido
$p->marcarPedidoLeido(30, $userId);
echo "Después de leer Pedido #30: " . $p->obtenerCantidadPedidosNoLeidos($userId) . "\n";
