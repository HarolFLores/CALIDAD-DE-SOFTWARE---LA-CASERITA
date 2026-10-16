<?php
require_once __DIR__ . '/../Core/dataBase.php';
$db = new DataBase();
$conn = $db->getConnection();
$stmt = $conn->query("SELECT id_pedido, estado, tipo_entrega, metodo_pago, total, fecha, leido_cliente FROM pedido ORDER BY id_pedido DESC LIMIT 10");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($rows);
