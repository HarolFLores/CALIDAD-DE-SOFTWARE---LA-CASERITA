<?php
/**
 * Migración HU33 - Bandeja de Pedidos Pendientes.
 * Agrega la columna `id_empaque` a la tabla `pedido` para registrar
 * el personal de empaque asignado a cada pedido (asignación individual o masiva).
 * Es idempotente: puede ejecutarse varias veces sin efectos secundarios.
 */
require_once __DIR__ . '/../Core/dataBase.php';

try {
    $db = (new DataBase())->getConnection();

    $col = $db->query("SHOW COLUMNS FROM pedido LIKE 'id_empaque'")->fetch(PDO::FETCH_ASSOC);
    if (!$col) {
        $db->exec("ALTER TABLE pedido ADD COLUMN id_empaque INT(11) NULL DEFAULT NULL AFTER id_cajero");
        $db->exec("ALTER TABLE pedido ADD KEY idx_pedido_empaque (id_empaque)");
        echo "Columna id_empaque agregada a la tabla pedido.\n";
    } else {
        echo "La columna id_empaque ya existe. Sin cambios.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
