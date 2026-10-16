<?php
require_once __DIR__ . '/../Core/dataBase.php';
$db = (new DataBase())->getConnection();
$p = $db->query('SELECT id_producto, titulo, imagen, imagen_url FROM producto LIMIT 5')->fetchAll(PDO::FETCH_ASSOC);
print_r($p);
