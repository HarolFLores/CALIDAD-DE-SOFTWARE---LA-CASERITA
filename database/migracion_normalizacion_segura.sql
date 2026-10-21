-- La Caserita - migración segura de integridad y normalización gradual
-- Ejecutar después del respaldo: backups/pre_auditoria_20261021_010958
-- No elimina columnas ni datos históricos.

-- 1. Completar la referencia de método de pago en pedidos POS antiguos.
UPDATE pedido
SET id_metodo = CASE
    WHEN LOWER(metodo_pago) LIKE '%yape%' THEN 2
    WHEN LOWER(metodo_pago) LIKE '%plin%' THEN 3
    WHEN LOWER(metodo_pago) LIKE '%tarjeta%' THEN 4
    WHEN LOWER(metodo_pago) LIKE '%transfer%' THEN 5
    WHEN LOWER(metodo_pago) LIKE '%efectivo%' THEN 1
    ELSE id_metodo
END
WHERE id_metodo IS NULL;

-- 2. FK pedido_pago -> pedido
SET @sql = IF(
    (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
     WHERE CONSTRAINT_SCHEMA = DATABASE()
       AND TABLE_NAME = 'pedido_pago'
       AND CONSTRAINT_NAME = 'fk_pago_pedido') = 0,
    'ALTER TABLE pedido_pago ADD CONSTRAINT fk_pago_pedido FOREIGN KEY (id_pedido) REFERENCES pedido (id_pedido) ON DELETE CASCADE',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 3. FK direccion -> distrito_delivery
SET @sql = IF(
    (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
     WHERE CONSTRAINT_SCHEMA = DATABASE()
       AND TABLE_NAME = 'direccion'
       AND CONSTRAINT_NAME = 'fk_dir_distrito') = 0,
    'ALTER TABLE direccion ADD CONSTRAINT fk_dir_distrito FOREIGN KEY (id_distrito) REFERENCES distrito_delivery (id_distrito) ON DELETE SET NULL',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 4. FK venta -> usuario e índice de consulta
SET @sql = IF(
    (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
     WHERE CONSTRAINT_SCHEMA = DATABASE()
       AND TABLE_NAME = 'venta'
       AND CONSTRAINT_NAME = 'fk_venta_usuario') = 0,
    'ALTER TABLE venta ADD CONSTRAINT fk_venta_usuario FOREIGN KEY (id_usuario) REFERENCES usuario (id_usuario) ON DELETE RESTRICT',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF(
    (SELECT COUNT(*) FROM information_schema.STATISTICS
     WHERE TABLE_SCHEMA = DATABASE()
       AND TABLE_NAME = 'venta'
       AND INDEX_NAME = 'idx_venta_usuario_fecha') = 0,
    'ALTER TABLE venta ADD INDEX idx_venta_usuario_fecha (id_usuario, fecha)',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 5. Una sola caja abierta por usuario (restricción parcial mediante columna generada).
SET @sql = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE()
       AND TABLE_NAME = 'sesion_caja'
       AND COLUMN_NAME = 'usuario_sesion_abierta') = 0,
    'ALTER TABLE sesion_caja ADD COLUMN usuario_sesion_abierta INT GENERATED ALWAYS AS (CASE WHEN estado = ''abierta'' THEN id_usuario ELSE NULL END) STORED',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF(
    (SELECT COUNT(*) FROM information_schema.STATISTICS
     WHERE TABLE_SCHEMA = DATABASE()
       AND TABLE_NAME = 'sesion_caja'
       AND INDEX_NAME = 'uq_sesion_abierta_usuario') = 0,
    'CREATE UNIQUE INDEX uq_sesion_abierta_usuario ON sesion_caja (usuario_sesion_abierta)',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
