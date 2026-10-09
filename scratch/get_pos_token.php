<?php
require_once __DIR__ . '/../Core/dataBase.php';
$db = (new DataBase())->getConnection();
$stmt = $db->query("SELECT * FROM sesion_caja ORDER BY id_sesion DESC LIMIT 1");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
echo json_encode($row, JSON_PRETTY_PRINT);
