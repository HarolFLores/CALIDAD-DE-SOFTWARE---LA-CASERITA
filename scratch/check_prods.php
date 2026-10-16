<?php
require_once __DIR__ . '/../Models/productModel.php';
$pm = new ProductoModel();
$prods = $pm->getProductofull();
foreach ($prods as $p) {
    if ((int)($p['id_momento'] ?? 0) === 3) {
        echo "Momento 3 | ID: {$p['id_producto']} | {$p['titulo']} | Stock: {$p['stock']} | Min: {$p['stock_minimo']}\n";
    }
}
