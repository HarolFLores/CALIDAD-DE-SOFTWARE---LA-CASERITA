<?php
require_once 'Core/dataBase.php';
$db = (new DataBase())->getConnection();
$st = $db->query('DESCRIBE producto');
echo json_encode(array_column($st->fetchAll(PDO::FETCH_ASSOC), 'Field'));
