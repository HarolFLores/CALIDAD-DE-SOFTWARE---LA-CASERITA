<?php
require_once 'Core/dataBase.php';

$db = (new DataBase())->getConnection();

// Add precio_anterior if not exists
try {
    $cols = $db->query("SHOW COLUMNS FROM producto LIKE 'precio_anterior'")->fetchAll();
    if (empty($cols)) {
        $db->exec("ALTER TABLE producto ADD COLUMN precio_anterior DECIMAL(10,2) NULL DEFAULT NULL AFTER precio");
        echo "Columna precio_anterior agregada.\n";
    } else {
        echo "Columna precio_anterior ya existe.\n";
    }
} catch (Exception $e) {
    echo "Error precio_anterior: " . $e->getMessage() . "\n";
}

// Add marca if not exists
try {
    $cols = $db->query("SHOW COLUMNS FROM producto LIKE 'marca'")->fetchAll();
    if (empty($cols)) {
        $db->exec("ALTER TABLE producto ADD COLUMN marca VARCHAR(100) NULL DEFAULT NULL AFTER titulo");
        echo "Columna marca agregada.\n";
    } else {
        echo "Columna marca ya existe.\n";
    }
} catch (Exception $e) {
    echo "Error marca: " . $e->getMessage() . "\n";
}

// Update brands and some discounts
try {
    $db->exec("UPDATE producto SET marca = 'Costeño' WHERE titulo LIKE '%Arroz%'");
    $db->exec("UPDATE producto SET marca = 'Gloria' WHERE titulo LIKE '%Gloria%' OR titulo LIKE '%Leche%'");
    $db->exec("UPDATE producto SET marca = 'Primor' WHERE titulo LIKE '%Primor%' OR titulo LIKE '%Aceite%'");
    $db->exec("UPDATE producto SET marca = 'Casino' WHERE titulo LIKE '%Casino%' OR titulo LIKE '%Galleta%'");
    $db->exec("UPDATE producto SET marca = 'Pampers' WHERE titulo LIKE '%Pampers%' OR titulo LIKE '%Pañal%'");
    $db->exec("UPDATE producto SET marca = 'Don Vittorio' WHERE titulo LIKE '%Fideo%' OR titulo LIKE '%Spaghetti%'");
    $db->exec("UPDATE producto SET marca = 'Caserita Selección' WHERE marca IS NULL");

    // Give discount to some products so they show percentage badge (-12%, -20%, etc.)
    $db->exec("UPDATE producto SET precio_anterior = ROUND(precio * 1.25, 2) WHERE id_producto IN (1, 3, 5, 8) OR es_sugerencia = 1");
    // Make sure some products have NO discount (precio_anterior = NULL or 0)
    $db->exec("UPDATE producto SET precio_anterior = NULL WHERE id_producto IN (2, 4, 6, 7, 9, 10)");
    echo "Descuentos y marcas actualizados correctamente.\n";
} catch (Exception $e) {
    echo "Error actualizando datos: " . $e->getMessage() . "\n";
}
