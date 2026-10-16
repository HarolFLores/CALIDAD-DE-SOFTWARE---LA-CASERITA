<?php
require_once __DIR__ . '/../Core/dataBase.php';
$db = (new DataBase())->getConnection();
$cols = $db->query("DESCRIBE pedido")->fetchAll(PDO::FETCH_ASSOC);
echo "Columnas en pedido:\n";
foreach ($cols as $c) {
    echo "- " . $c['Field'] . " (" . $c['Type'] . ")\n";
}
$tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
echo "\nTablas existentes:\n";
print_r($tables);
