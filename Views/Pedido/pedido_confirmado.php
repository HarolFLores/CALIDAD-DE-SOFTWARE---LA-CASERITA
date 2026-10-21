<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($pedido) || !is_array($pedido)) {
    header('Location: index.php?action=home_usuario');
    exit();
}

$detalle = isset($detalle) && is_array($detalle) ? $detalle : [];

$escapar = static function ($valor): string {
    return htmlspecialchars((string)($valor ?? ''), ENT_QUOTES, 'UTF-8');
};

$formatearMoneda = static function ($valor): string {
    return 'S/ ' . number_format((float)($valor ?? 0), 2, '.', ',');
};

$idPedido = (int)($pedido['id_pedido'] ?? 0);
$idPedidoPad = str_pad($idPedido, 5, '0', STR_PAD_LEFT);
$fechaFmt = !empty($pedido['fecha']) ? date('d/m/Y h:i A', strtotime($pedido['fecha'])) : date('d/m/Y h:i A');
$tipoEntrega = !empty($pedido['tipo_entrega']) ? ucfirst((string)$pedido['tipo_entrega']) : 'Delivery';
$numeroComprobante = !empty($pedido['numero_comprobante']) ? $pedido['numero_comprobante'] : ('B001-' . str_pad($idPedido, 8, '0', STR_PAD_LEFT));
$subtotalMonto = (float)($pedido['subtotal'] ?? 0);
$costoEnvioMonto = (float)($pedido['costo_envio'] ?? 0);
$totalMonto = (float)($pedido['total'] ?? ($subtotalMonto + $costoEnvioMonto));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedido Confirmado #<?= $idPedidoPad; ?> — Tienda La Caserita</title>
    <link rel="icon" type="image/png" href="Images/logo_backup.png">
    <link rel="stylesheet" href="Styles/global.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="Styles/styles-pedido_confirmado.css?v=<?= time(); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <main class="container">
        <!-- Logo y Marca -->
        <div class="brand-header no-print">
            <a href="index.php?action=home_usuario" class="brand">
                <img src="Images/logo.png" alt="La Caserita" class="brand-logo">
                <span class="brand-title">La Caserita</span>
            </a>
        </div>

        <!-- Icono de Confirmación Animado -->
        <div class="success-icon-container">
            <svg xmlns="http://www.w3.org/2000/svg" width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
        </div>

        <h2 class="confirm-title">¡Pedido Confirmado con Éxito!</h2>
        <p class="subtitle">Muchas gracias por tu compra. Tu pedido ya fue recibido y nuestro equipo ha comenzado a prepararlo.</p>

        <!-- Información en Tarjetas Principales -->
        <section class="info-grid">
            <!-- Tarjeta 1: Información del Pedido -->
            <div class="info-section">
                <h3>Información de tu pedido</h3>
                <div class="info-card">
                    <div class="info-item">
                        <span class="info-label">Nº Pedido:</span>
                        <span class="info-value order-code">#<?= $idPedidoPad; ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Comprobante:</span>
                        <span class="info-value badge-comprobante"><?= $escapar($numeroComprobante); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Fecha y Hora:</span>
                        <span class="info-value"><?= $escapar($fechaFmt); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Método de pago:</span>
                        <span class="info-value"><?= $escapar($pedido['metodo_pago'] ?? 'Pago Electrónico'); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Estado de pago:</span>
                        <span class="info-value badge-pago-aprobado">✓ Aprobado</span>
                    </div>
                </div>
            </div>

            <!-- Tarjeta 2: Detalles de la Entrega -->
            <div class="info-section">
                <h3>Detalles de la entrega</h3>
                <div class="info-card">
                    <div class="info-item">
                        <span class="info-label">Modalidad:</span>
                        <span class="info-value"><?= $tipoEntrega === 'Delivery' ? '🛵 Delivery a Domicilio' : '🏪 Recojo en Tienda'; ?></span>
                    </div>
                    <?php if (strtolower($pedido['tipo_entrega'] ?? '') === 'delivery'): ?>
                        <div class="info-item">
                            <span class="info-label">Dirección:</span>
                            <span class="info-value address-highlight">
                                <?= $escapar(implode(', ', array_filter([
                                    $pedido['calle'] ?? '',
                                    $pedido['distrito'] ?? '',
                                    $pedido['provincia'] ?? '',
                                    $pedido['departamento'] ?? ''
                                ]))); ?>
                            </span>
                        </div>
                        <?php if (!empty($pedido['referencia'])): ?>
                            <div class="info-item">
                                <span class="info-label">Referencia:</span>
                                <span class="info-value"><?= $escapar($pedido['referencia']); ?></span>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="info-item">
                            <span class="info-label">Lugar de recojo:</span>
                            <span class="info-value">Av. Grau #156, Chiclayo - Lambayeque</span>
                        </div>
                    <?php endif; ?>
                    <?php 
                        $notasLimpia = preg_replace('/\[Pago[^\]]+\]/i', '', $pedido['observacion'] ?? '');
                        $notasLimpia = preg_replace('/\[Tarjeta[^\]]+\]/i', '', $notasLimpia);
                        $notasLimpia = preg_replace('/\[Cancelación[^\]]*\]/iu', '', $notasLimpia);
                        $notasLimpia = preg_replace('/\[Rechazo comprobante[^\]]*\]/iu', '', $notasLimpia);
                        $notasLimpia = trim(preg_replace('/^\s*\|\s*|\s*\|\s*$/', '', trim($notasLimpia)));
                        $notasLimpia = trim(preg_replace('/\s*\|\s*\|\s*/', ' | ', $notasLimpia));
                    ?>
                    <?php if (!empty($notasLimpia)): ?>
                        <div class="info-item">
                            <span class="info-label">Notas:</span>
                            <span class="info-value obs-text">"<?= $escapar($notasLimpia); ?>"</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- Productos Solicitados -->
        <section class="items-section">
            <h3>Productos solicitados (<?= count($detalle); ?> <?= count($detalle) === 1 ? 'ítem' : 'ítems'; ?>)</h3>
            <div class="items-list">
                <?php foreach ($detalle as $item): 
                    $cant = (float)($item['cantidad'] ?? 1);
                    $precioU = (float)($item['precio_unitario'] ?? 0);
                    $subt = (float)($item['subtotal'] ?? ($cant * $precioU));
                    $und = strtolower($item['unidad_medida'] ?? 'und.');
                    if ($und === 'kg') { $und = 'kg'; }
                    $img = !empty($item['imagen']) ? $item['imagen'] : 'Imagenes/producto-placeholder.png';
                ?>
                    <div class="item-row">
                        <img class="item-image" src="<?= $escapar($img); ?>" alt="<?= $escapar($item['titulo'] ?? $item['titulo_producto'] ?? 'Producto'); ?>" onerror="this.src='Imagenes/producto-placeholder.png'">
                        <div class="item-details">
                            <div class="item-title"><?= $escapar($item['titulo'] ?? $item['titulo_producto'] ?? 'Producto'); ?></div>
                            <div class="item-meta">
                                <?= $cant; ?> <?= $und; ?> &times; <?= $formatearMoneda($precioU); ?>
                            </div>
                        </div>
                        <div class="item-price-block">
                            <span class="item-subtotal"><?= $formatearMoneda($subt); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Resumen Financiero -->
            <div class="summary-block">
                <div class="summary-row">
                    <span class="info-label">Subtotal:</span>
                    <span class="info-value"><?= $formatearMoneda($subtotalMonto); ?></span>
                </div>
                <div class="summary-row">
                    <span class="info-label">Costo de envío:</span>
                    <span class="info-value"><?= $costoEnvioMonto > 0 ? $formatearMoneda($costoEnvioMonto) : 'S/ 0.00 (Gratis)'; ?></span>
                </div>
                <div class="summary-row total">
                    <span>Total Pagado:</span>
                    <span class="total-monto"><?= $formatearMoneda($totalMonto); ?></span>
                </div>
            </div>
        </section>

        <!-- Botones de Acción -->
        <div class="actions-group no-print">
            <a href="index.php?action=home_usuario" class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <path d="M16 10a4 4 0 0 1-8 0"></path>
                </svg>
                <span>Volver a comprar</span>
            </a>
            <a href="index.php?action=mi_cuenta" class="btn btn-secondary">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
                <span>Mis pedidos</span>
            </a>
        </div>
    </main>
</body>
</html>
