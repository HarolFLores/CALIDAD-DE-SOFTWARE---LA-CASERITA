<?php
require_once __DIR__ . '/../Core/dataBase.php';
$db = (new DataBase())->getConnection();
$stmt = $db->query("DESCRIBE producto");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['Field'] . " (" . $row['Type'] . ")\n";
}
