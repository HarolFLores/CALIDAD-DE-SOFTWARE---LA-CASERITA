<?php
$html = file_get_contents('http://localhost/clonacion%20harol/CALIDAD-DE-SOFTWARE---LA-CASERITA/index.php?action=ver_categoria&ofertas=1&nombre=Ofertas');
preg_match_all('/data-id="(\d+)"\s+data-titulo="([^"]+)"/', $html, $matches);
echo "Productos encontrados en Ofertas:\n";
foreach ($matches[1] as $idx => $id) {
    echo "ID: $id | " . $matches[2][$idx] . "\n";
}
echo "Total: " . count($matches[1]) . "\n";
