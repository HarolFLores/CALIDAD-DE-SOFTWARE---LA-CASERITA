<?php
session_start();
$_SESSION['usuario_id'] = 2;
$_POST['id_producto'] = 1;
$_POST['cantidad'] = 1;
$_SERVER['REQUEST_METHOD'] = 'POST';

require_once __DIR__ . '/../Controllers/controllersCarrito.php';
$c = new ControllerCarrito();

ob_start();
$c->agregar();
$resAdd = ob_get_clean();
echo "Agregar response: " . $resAdd . PHP_EOL;

// Now let's list
ob_start();
$c->listar();
$resList = ob_get_clean();
echo "Listar response: " . $resList . PHP_EOL;
