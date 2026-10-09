<?php
require_once __DIR__ . '/../Core/dataBase.php';
$db = (new DataBase())->getConnection();
$stmt = $db->query("DESCRIBE pos_escaner_cola");
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $r['Field'] . " (" . $r['Type'] . ") Null:" . $r['Null'] . "\n";
}
