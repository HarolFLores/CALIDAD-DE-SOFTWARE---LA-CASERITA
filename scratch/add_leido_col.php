<?php
require_once __DIR__ . '/../Core/dataBase.php';
$db = (new DataBase())->getConnection();

// Verificar si existe la columna leido_cliente en pedido
$res = $db->query("SHOW COLUMNS FROM pedido LIKE 'leido_cliente'")->fetch();
if (!$res) {
    echo "Agregando columna leido_cliente a tabla pedido...\n";
    $db->exec("ALTER TABLE pedido ADD COLUMN leido_cliente TINYINT(1) NOT NULL DEFAULT 0 AFTER estado");
    echo "Columna agregada exitosamente.\n";
} else {
    echo "La columna leido_cliente ya existe.\n";
}

$countTotal = $db->query("SELECT COUNT(*) FROM pedido WHERE id_usuario = 2")->fetchColumn();
$countNoLeidos = $db->query("SELECT COUNT(*) FROM pedido WHERE id_usuario = 2 AND LOWER(estado) NOT IN ('entregado', 'cancelado', 'rechazado') AND leido_cliente = 0")->fetchColumn();
echo "Total pedidos usuario 2: $countTotal | No leidos activos: $countNoLeidos\n";
