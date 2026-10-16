<?php
require_once __DIR__ . '/../Core/dataBase.php';
$db = (new DataBase())->getConnection();
$stmt = $db->prepare("UPDATE configuracion_correo SET smtp_host = 'smtp.gmail.com', smtp_port = 587, smtp_user = 'erikcarrasco455@gmail.com', smtp_pass = 'dkgkbxgmenizalsi', smtp_seguridad = 'tls', correo_soporte = 'erikcarrasco455@gmail.com' WHERE id_config = 1");
$stmt->execute();
echo "Filas actualizadas: " . $stmt->rowCount() . "\n";
