<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=lacaserita_db;charset=utf8', 'root', '');
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "TABLES:\n" . implode("\n", $tables) . "\n\n";

    foreach ($tables as $table) {
        if (strpos($table, 'img') !== false || strpos($table, 'galer') !== false || strpos($table, 'foto') !== false || strpos($table, 'prod') !== false) {
            echo "--- Table: $table ---\n";
            $stmtCols = $pdo->query("DESCRIBE `$table`");
            print_r($stmtCols->fetchAll(PDO::FETCH_ASSOC));
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
