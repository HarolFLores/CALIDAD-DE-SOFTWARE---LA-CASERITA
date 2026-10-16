<?php
$file = __DIR__ . '/../Views/Checkout/checkout.php';
$content = file_get_contents($file);
$content = str_replace(["\r\n", "\r"], "\n", $content);

// 1. Remove SVGs in co-confirm-block-header
$oldBlockEntregaHeader = '<div class="co-confirm-block-header">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>
                            <h3>Modalidad de entrega</h3>';

$newBlockEntregaHeader = '<div class="co-confirm-block-header">
                            <h3>Modalidad de entrega</h3>';

$oldBlockEntregaHeader = str_replace(["\r\n", "\r"], "\n", $oldBlockEntregaHeader);
$newBlockEntregaHeader = str_replace(["\r\n", "\r"], "\n", $newBlockEntregaHeader);

if (strpos($content, $oldBlockEntregaHeader) !== false) {
    $content = str_replace($oldBlockEntregaHeader, $newBlockEntregaHeader, $content);
    echo "Block entrega header updated.\n";
} else {
    echo "Warning: oldBlockEntregaHeader not matched.\n";
}

$oldBlockPagoHeader = '<div class="co-confirm-block-header">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
                            <h3>M&eacute;todo de pago</h3>';

$newBlockPagoHeader = '<div class="co-confirm-block-header">
                            <h3>M&eacute;todo de pago</h3>';

$oldBlockPagoHeader = str_replace(["\r\n", "\r"], "\n", $oldBlockPagoHeader);
$newBlockPagoHeader = str_replace(["\r\n", "\r"], "\n", $newBlockPagoHeader);

if (strpos($content, $oldBlockPagoHeader) !== false) {
    $content = str_replace($oldBlockPagoHeader, $newBlockPagoHeader, $content);
    echo "Block pago header updated.\n";
} else {
    echo "Warning: oldBlockPagoHeader not matched.\n";
}

// 2. Remove emojis and improve typography in populateConfirmation()
$oldPopulateEntrega = '    if (mEl) mEl.innerHTML = tipo === \'delivery\'
        ? \'<strong>&#128666; Delivery a domicilio</strong> <span style="margin-left:8px;font-size:0.8rem;background:#f1f5f9;padding:3px 8px;border-radius:6px;color:#334155;">&#9200; \' + hTexto + \'</span>\'
        : \'<strong>&#127968; Recojo en tienda</strong> <span style="margin-left:8px;font-size:0.8rem;background:#f1f5f9;padding:3px 8px;border-radius:6px;color:#334155;">&#9200; \' + hTexto + \'</span>\';';

$newPopulateEntrega = '    if (mEl) mEl.innerHTML = tipo === \'delivery\'
        ? \'<div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;"><strong style="font-size:0.98rem;color:#0f172a;">Delivery a domicilio</strong> <span style="font-size:0.8rem;background:#f1f5f9;padding:3px 10px;border-radius:6px;color:#475569;font-weight:600;border:1px solid #e2e8f0;">\' + hTexto + \'</span></div>\'
        : \'<div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;"><strong style="font-size:0.98rem;color:#0f172a;">Recojo en tienda</strong> <span style="font-size:0.8rem;background:#f1f5f9;padding:3px 10px;border-radius:6px;color:#475569;font-weight:600;border:1px solid #e2e8f0;">\' + hTexto + \'</span></div>\';';

$oldPopulateEntrega = str_replace(["\r\n", "\r"], "\n", $oldPopulateEntrega);
$newPopulateEntrega = str_replace(["\r\n", "\r"], "\n", $newPopulateEntrega);

if (strpos($content, $oldPopulateEntrega) !== false) {
    $content = str_replace($oldPopulateEntrega, $newPopulateEntrega, $content);
    echo "populateConfirmation entrega updated.\n";
} else {
    echo "Warning: oldPopulateEntrega not matched.\n";
}

$oldObs = 'var obs = document.getElementById(\'checkoutObservacion\');
    if (iEl) iEl.textContent = obs && obs.value.trim() ? \'&#128172; \' + obs.value.trim() : \'\';';

$newObs = 'var obs = document.getElementById(\'checkoutObservacion\');
    if (iEl) iEl.textContent = obs && obs.value.trim() ? \'Indicaciones: \' + obs.value.trim() : \'\';';

$oldObs = str_replace(["\r\n", "\r"], "\n", $oldObs);
$newObs = str_replace(["\r\n", "\r"], "\n", $newObs);

if (strpos($content, $oldObs) !== false) {
    $content = str_replace($oldObs, $newObs, $content);
    echo "obs updated.\n";
} else {
    echo "Warning: oldObs not matched.\n";
}

file_put_contents($file, $content);
echo "checkout.php updated successfully.\n";
