<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=lacaserita_db;charset=utf8', 'root', '');
    $stmt = $pdo->query("DESCRIBE producto");
    $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach($cols as $col) {
        echo $col['Field'] . " | " . $col['Type'] . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
