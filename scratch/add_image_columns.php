<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=lacaserita_db;charset=utf8', 'root', '');
    
    // Check if columns exist
    $stmt = $pdo->query("SHOW COLUMNS FROM producto LIKE 'imagen_2'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE producto ADD COLUMN imagen_2 VARCHAR(255) NULL AFTER imagen_url, ADD COLUMN imagen_3 VARCHAR(255) NULL AFTER imagen_2, ADD COLUMN imagen_4 VARCHAR(255) NULL AFTER imagen_3");
        echo "Columns imagen_2, imagen_3, imagen_4 added successfully.\n";
    } else {
        echo "Columns imagen_2, imagen_3, imagen_4 already exist.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
