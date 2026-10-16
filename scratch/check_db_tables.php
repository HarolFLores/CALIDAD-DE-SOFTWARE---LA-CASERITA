<?php
require_once __DIR__ . '/../Core/dataBase.php';
$db = (new DataBase())->getConnection();
$st = $db->query("SHOW TABLES LIKE 'configuracion_correo'");
$row = $st->fetch();
var_dump($row);
if ($row) {
    $q = $db->query("SELECT * FROM configuracion_correo");
    print_r($q->fetchAll(PDO::FETCH_ASSOC));
}
