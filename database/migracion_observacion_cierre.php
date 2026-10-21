<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit('Not found');
}

require_once __DIR__ . '/../Core/dataBase.php';

$db = (new DataBase())->getConnection();
$consulta = $db->query("SHOW COLUMNS FROM sesion_caja LIKE 'observacion_cierre'");
if (!$consulta->fetch(PDO::FETCH_ASSOC)) {
    $db->exec("ALTER TABLE sesion_caja ADD COLUMN observacion_cierre VARCHAR(500) NULL AFTER diferencia");
    echo "Columna observacion_cierre agregada.\n";
} else {
    echo "La columna observacion_cierre ya existe.\n";
}
