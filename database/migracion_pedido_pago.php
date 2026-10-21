<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit('Not found');
}

/**
 * Migración: pagos divididos en el POS.
 * Crea la tabla `pedido_pago` para registrar uno o varios métodos de pago
 * (efectivo, yape, tarjeta) sobre un mismo pedido.
 * Es idempotente: puede ejecutarse varias veces sin efectos secundarios.
 */
require_once __DIR__ . '/../Core/dataBase.php';

try {
    $db = (new DataBase())->getConnection();

    $db->exec("CREATE TABLE IF NOT EXISTS `pedido_pago` (
        `id_pago` INT(11) NOT NULL AUTO_INCREMENT,
        `id_pedido` INT(11) NOT NULL,
        `metodo` VARCHAR(20) NOT NULL,
        `monto` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        `fecha` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id_pago`),
        KEY `idx_pedido_pago_pedido` (`id_pedido`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    echo "Tabla pedido_pago lista.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
