<?php
session_start();
$_SESSION['usuario_id'] = 2;
$_SESSION['usuario_nombre'] = 'Erik';
$_SESSION['usuario_rol'] = 'cliente';
$_SESSION['carritos'] = [
    2 => [
        ['id_producto' => 1, 'cantidad' => 1]
    ]
];

ob_start();
include __DIR__ . '/../Views/Home/partials/header_principal.php';
$html = ob_get_clean();

// Extract header-controls-group
if (preg_match('/<div class="header-controls-group header-actions">.*?<\/div>\s*<\/div>/s', $html, $matches)) {
    echo $matches[0];
} else {
    echo "Not matched\n";
    echo substr($html, 0, 1000);
}
