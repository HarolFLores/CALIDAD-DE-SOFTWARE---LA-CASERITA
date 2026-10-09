<?php
require_once __DIR__ . '/../Core/dataBase.php';
$db = (new DataBase())->getConnection();
$stmt = $db->query("SELECT token_emparejamiento FROM sesion_caja WHERE estado = 'abierta' ORDER BY id_sesion DESC LIMIT 1");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$token = $row ? $row['token_emparejamiento'] : 'default';

$adb = getenv('LOCALAPPDATA') . '\\Android\\Sdk\\platform-tools\\adb.exe';
exec("\"$adb\" reverse tcp:8080 tcp:80");

$url = "http://localhost:8080/CALIDAD%20SOFTWARE/Web_mejorar/Proyecto_Teconoligas%20Web/index.php?action=pos_escaner_movil^&token=" . $token;

$cmd = "\"$adb\" shell am start -a android.intent.action.VIEW -d \"http://localhost:8080/CALIDAD%20SOFTWARE/Web_mejorar/Proyecto_Teconoligas%20Web/index.php?action=pos_escaner_movil\\&token=$token\"";
exec($cmd, $out, $ret);
echo implode("\n", $out) . "\nResult: $ret\n";
