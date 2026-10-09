<?php
require_once 'Models/modelo_punto_venta.php';
$m = new ModeloPuntoVenta();
$res = $m->buscarProductosManual('a');
echo "Count: " . count($res) . "\n";
if (!empty($res)) {
    echo json_encode($res[0], JSON_PRETTY_PRINT);
}
