<?php
$cssFile = __DIR__ . '/../Styles/styles-cuenta.css';
$css = file_get_contents($cssFile);
$css = str_replace(["\r\n", "\r"], "\n", $css);

// 1. Update lines 672-683: En camino color
$oldCaminoCSS = '.pedido-estado-camino,
.pedido-estado-en_camino,
.pedido-estado-en_ruta {
    color: #2563eb;
}';

$newCaminoCSS = '.pedido-estado-camino,
.pedido-estado-en_camino,
.pedido-estado-en_ruta,
.pedido-estado-despachado {
    color: #0284c7 !important; /* Celeste vivo / Cyan vibrante, claramente diferente de En Preparación */
    font-weight: 700;
}';

$oldCaminoCSS = str_replace(["\r\n", "\r"], "\n", $oldCaminoCSS);
$newCaminoCSS = str_replace(["\r\n", "\r"], "\n", $newCaminoCSS);

if (strpos($css, $oldCaminoCSS) !== false) {
    $css = str_replace($oldCaminoCSS, $newCaminoCSS, $css);
    echo "En camino CSS updated.\n";
} else {
    echo "Warning: oldCaminoCSS not matched.\n";
}

// 2. Remove mobile media query badge-tipo-entrega pill (lines 2109-2116)
$oldMobileBadge = '    .badge-tipo-entrega {
        font-size: 0.75rem;
        padding: 2px 8px;
        border-radius: 20px;
        background: #f1f5f9;
        color: #334155;
        font-weight: 600;
    }';

$newMobileBadge = '    .badge-tipo-entrega {
        font-size: 0.85rem;
        padding: 0;
        border-radius: 0;
        background: transparent !important;
        color: #1e293b;
        font-weight: 600;
    }';

$oldMobileBadge = str_replace(["\r\n", "\r"], "\n", $oldMobileBadge);
$newMobileBadge = str_replace(["\r\n", "\r"], "\n", $newMobileBadge);

if (strpos($css, $oldMobileBadge) !== false) {
    $css = str_replace($oldMobileBadge, $newMobileBadge, $css);
    echo "Mobile badge updated.\n";
} else {
    echo "Warning: oldMobileBadge not matched.\n";
}

// 3. Remove desktop badge-tipo-entrega and badge-metodo-pago pills (lines 2488-2498)
$oldPillBadge = '.badge-tipo-entrega,
.badge-metodo-pago {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 8px;
    font-size: 0.8rem;
    font-weight: 600;
    background: #f1f5f9;
    color: #334155;
    border: 1px solid #e2e8f0;
}';

$newPillBadge = '.badge-tipo-entrega,
.badge-metodo-pago {
    display: inline !important;
    padding: 0 !important;
    border-radius: 0 !important;
    background: transparent !important;
    border: none !important;
    box-shadow: none !important;
    font-size: 0.88rem !important;
    line-height: inherit !important;
}

.badge-tipo-entrega {
    color: #1e293b !important;
    font-weight: 600 !important;
}

.badge-metodo-pago {
    color: #475569 !important;
    font-weight: 400 !important;
}';

$oldPillBadge = str_replace(["\r\n", "\r"], "\n", $oldPillBadge);
$newPillBadge = str_replace(["\r\n", "\r"], "\n", $newPillBadge);

if (strpos($css, $oldPillBadge) !== false) {
    $css = str_replace($oldPillBadge, $newPillBadge, $css);
    echo "Pill badge updated to plain text.\n";
} else {
    echo "Warning: oldPillBadge not matched.\n";
}

// 4. Remove duplicate .badge-tipo-entrega rule (lines 2554-2561)
$oldDupBadge = '.badge-tipo-entrega {
    background-color: #f1f5f9;
    color: #334155;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}';

$oldDupBadge = str_replace(["\r\n", "\r"], "\n", $oldDupBadge);

if (strpos($css, $oldDupBadge) !== false) {
    $css = str_replace($oldDupBadge, '/* badge-tipo-entrega plain text handled above */', $css);
    echo "Duplicate badge rule removed.\n";
} else {
    echo "Warning: oldDupBadge not matched.\n";
}

file_put_contents($cssFile, $css);
echo "styles-cuenta.css updated successfully.\n";
