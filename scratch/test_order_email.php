<?php
require_once __DIR__ . '/../Models/notificacionEmailModel.php';
$notif = new NotificacionEmailModel();
echo "Enviando confirmación de pedido #28 con diseño mejorado a erikcarrasco455@gmail.com...\n";
$res = $notif->enviarConfirmacionCompra(28);
var_dump($res);
