<?php
require 'Core/dataBase.php';
$db = (new DataBase())->getConnection();

echo "--- COLUMNAS pos_escaner_cola ---\n";
foreach($db->query("DESCRIBE pos_escaner_cola") as $c) {
    echo $c['Field'] . " | " . $c['Type'] . PHP_EOL;
}

echo "\n--- FILAS pos_escaner_cola ---\n";
$stmt2 = $db->query("SELECT * FROM pos_escaner_cola LIMIT 10");
while ($r2 = $stmt2->fetch(PDO::FETCH_ASSOC)) {
    print_r($r2);
}
