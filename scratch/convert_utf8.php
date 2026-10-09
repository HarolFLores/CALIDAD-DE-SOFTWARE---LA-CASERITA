<?php
$files = [
    __DIR__ . '/../Views/Cajero/caja_punto_venta.php',
    __DIR__ . '/../Views/Inventario/inventario.php',
    __DIR__ . '/../Views/Admin/proveedores_admin.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        $data = file_get_contents($file);
        // Detect encoding
        $enc = mb_detect_encoding($data, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true);
        if ($enc && $enc !== 'UTF-8') {
            $data = mb_convert_encoding($data, 'UTF-8', $enc);
        }
        file_put_contents($file, $data);
        echo "OK: $file (original: $enc)\n";
    }
}
