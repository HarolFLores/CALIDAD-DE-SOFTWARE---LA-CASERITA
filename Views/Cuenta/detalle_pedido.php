<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['usuario_id'])) {
        header('Location: index.php?action=login');
        exit();
    }

    $pedido  = isset($pedido)  && is_array($pedido)  ? $pedido  : [];
    $detalle = isset($detalle) && is_array($detalle) ? $detalle : [];

    function escaparD($valor) {
        return htmlspecialchars((string)($valor ?? ''), ENT_QUOTES, 'UTF-8');
    }

    function textoEstadoD($estado) {
        $textos = [
            'pendiente'      => 'Pendiente de confirmación',
            'confirmado'     => 'Confirmado',
            'en preparacion' => 'En preparación',
            'en_preparacion' => 'En preparación',
            'en camino'      => 'En camino a tu dirección',
            'en_camino'      => 'En camino a tu dirección',
            'entregado'      => 'Pedido entregado con éxito',
            'cancelado'      => 'Pedido cancelado',
        ];
        $st = strtolower(trim((string)$estado));
        return $textos[$st] ?? ucfirst(str_replace('_', ' ', $st));
    }

    function pasoActualEstado($estado) {
        $st = strtolower(trim((string)$estado));
        if ($st === 'cancelado') return 0;
        if ($st === 'pendiente' || $st === 'por verificar') return 1;
        if ($st === 'confirmado' || $st === 'en preparacion' || $st === 'en_preparacion') return 2;
        if ($st === 'en camino' || $st === 'en_camino' || $st === 'despachado') return 3;
        if ($st === 'entregado') return 4;
        return 2;
    }

    function claseEstadoD($estado) {
        $st = strtolower(trim((string)$estado));
        if ($st === 'confirmado' || $st === 'en preparacion' || $st === 'en_preparacion') return 'pedido-estado-confirmado';
        if ($st === 'en camino' || $st === 'en_camino' || $st === 'despachado') return 'pedido-estado-camino';
        if ($st === 'entregado') return 'pedido-estado-entregado';
        if ($st === 'cancelado') return 'pedido-estado-cancelado';
        return 'pedido-estado-pendiente';
    }

    function fechaDetalleD($fecha) {
        if (empty($fecha)) return 'Sin fecha';
        $ts = strtotime($fecha);
        return $ts ? date('d/m/Y h:i A', $ts) : escaparD($fecha);
    }

    $idPedido     = (int)($pedido['id_pedido'] ?? 0);
    $estado       = $pedido['estado'] ?? 'en preparacion';
    $tipoEntrega  = $pedido['tipo_entrega'] ?? 'delivery';
    $metodoPago   = $pedido['metodo_pago'] ?? 'Pago Electrónico';
    $total        = (float)($pedido['total'] ?? 0);
    $costoEnvio   = (float)($pedido['costo_envio'] ?? 0);
    $subtotal     = (float)($pedido['subtotal'] ?? ($total - $costoEnvio));
    $codigoPedido = $pedido['codigo_pedido'] ?? ('PED-' . str_pad($idPedido, 5, '0', STR_PAD_LEFT));
    $horarioEntrega = $pedido['horario_entrega'] ?? '';
    $fechaProgramada = $pedido['fecha_programada'] ?? '';
    $observacion  = $pedido['observacion'] ?? '';
    $fecha        = $pedido['fecha'] ?? '';

    $depto    = $pedido['departamento'] ?? '';
    $prov     = $pedido['provincia'] ?? '';
    $dist     = $pedido['distrito'] ?? '';
    $calle    = $pedido['calle'] ?? '';
    $refencia = $pedido['referencia'] ?? '';
    $tieneDir = $depto || $prov || $dist || $calle;
    $pasoActivo = pasoActualEstado($estado);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de Pedido #<?= str_pad($idPedido, 5, '0', STR_PAD_LEFT); ?> — La Caserita</title>
    <link rel="icon" type="image/png" href="Images/logo_backup.png">
    <link rel="stylesheet" href="Styles/global.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="Styles/styles-cuenta.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="Styles/Vistas/Cuenta/detalle_pedido.css?v=<?= time(); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
<div class="detalle-page">
    <header class="cuenta-header">
        <div class="cuenta-header-container">
            <div class="cuenta-header-left">
                <a href="index.php?action=home_usuario" class="cuenta-brand" title="Volver a la tienda">
                    <div class="cuenta-brand-icon">
                        <img src="Images/logo_backup.png" alt="La Caserita">
                    </div>
                    <span class="cuenta-brand-name">La Caserita</span>
                </a>
            </div>
            
            <div class="cuenta-header-center">
                <h1 class="cuenta-header-title">Detalle de Pedido</h1>
            </div>

            <div class="cuenta-header-right"></div>
        </div>
    </header>

    <div class="detalle-nav-sub">
        <a href="index.php?action=mi_cuenta" class="link-volver">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            Volver a Mis Pedidos
        </a>
    </div>

    <main class="detalle-main">
        <!-- Tarjeta de Estado y Línea de Tiempo -->
        <div class="detalle-card hero-status-card">
            <div class="detalle-status-banner">
                <div class="detalle-status-info">
                    <span class="order-number-tag">Pedido #<?= str_pad($idPedido, 5, '0', STR_PAD_LEFT); ?></span>
                    <h2><?= escaparD(textoEstadoD($estado)); ?></h2>
                    <p class="order-datetime">Emitido el <?= escaparD(fechaDetalleD($fecha)); ?></p>
                </div>
                <div class="status-badge-container">
                    <span class="badge-status-pill <?= claseEstadoD($estado); ?>">
                        <?= escaparD(ucfirst(str_replace('_', ' ', $estado))); ?>
                    </span>
                </div>
            </div>

            <!-- Tracker / Timeline de Estado -->
            <?php if ($pasoActivo > 0): ?>
            <div class="timeline-tracker-wrap">
                <div class="timeline-steps">
                    <div class="timeline-step <?= $pasoActivo >= 1 ? 'completed' : ''; ?> <?= $pasoActivo === 1 ? 'current' : ''; ?>">
                        <div class="step-circle"><?= $pasoActivo > 1 ? '✓' : '1'; ?></div>
                        <span class="step-label">Recibido</span>
                    </div>
                    <div class="timeline-connector <?= $pasoActivo >= 2 ? 'active' : ''; ?>"></div>
                    <div class="timeline-step <?= $pasoActivo >= 2 ? 'completed' : ''; ?> <?= $pasoActivo === 2 ? 'current' : ''; ?>">
                        <div class="step-circle"><?= $pasoActivo > 2 ? '✓' : '2'; ?></div>
                        <span class="step-label">En Preparación</span>
                    </div>
                    <div class="timeline-connector <?= $pasoActivo >= 3 ? 'active' : ''; ?>"></div>
                    <div class="timeline-step <?= $pasoActivo >= 3 ? 'completed' : ''; ?> <?= $pasoActivo === 3 ? 'current' : ''; ?>">
                        <div class="step-circle"><?= $pasoActivo > 3 ? '✓' : '3'; ?></div>
                        <span class="step-label"><?= $tipoEntrega === 'delivery' ? 'En Camino' : 'Listo p/ Recojo'; ?></span>
                    </div>
                    <div class="timeline-connector <?= $pasoActivo >= 4 ? 'active' : ''; ?>"></div>
                    <div class="timeline-step <?= $pasoActivo >= 4 ? 'completed' : ''; ?> <?= $pasoActivo === 4 ? 'current' : ''; ?>">
                        <div class="step-circle">4</div>
                        <span class="step-label">Entregado</span>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="detalle-card-body">
                <div class="detalle-summary-grid">
                    <div class="detalle-summary-item">
                        <small>Código de Seguimiento</small>
                        <strong><?= escaparD($codigoPedido); ?></strong>
                    </div>
                    <div class="detalle-summary-item">
                        <small>Método de Pago</small>
                        <strong><?= escaparD($metodoPago); ?></strong>
                    </div>
                    <div class="detalle-summary-item">
                        <small>Modalidad</small>
                        <strong><?= $tipoEntrega === 'delivery' ? '🛵 Delivery a Domicilio' : '🏪 Recojo en Tienda'; ?></strong>
                    </div>
                    <div class="detalle-summary-item">
                        <small>Horario Estimado</small>
                        <strong><?= !empty($horarioEntrega) ? escaparD($horarioEntrega) : 'Inmediato (30-45 min)'; ?></strong>
                    </div>
                </div>

                <?php 
                    $obsLimpia = preg_replace('/\[Pago[^\]]+\]/i', '', $observacion ?? '');
                    $obsLimpia = preg_replace('/\[Tarjeta[^\]]+\]/i', '', $obsLimpia);
                    $obsLimpia = preg_replace('/\[Cancelación[^\]]*\]/iu', '', $obsLimpia);
                    $obsLimpia = preg_replace('/\[Rechazo comprobante[^\]]*\]/iu', '', $obsLimpia);
                    $obsLimpia = trim(preg_replace('/^\s*\|\s*|\s*\|\s*$/', '', trim($obsLimpia)));
                    $obsLimpia = trim(preg_replace('/\s*\|\s*\|\s*/', ' | ', $obsLimpia));
                ?>
                <?php if (!empty($obsLimpia)): ?>
                <div class="detalle-observacion-box">
                    <span class="obs-title">Observaciones del cliente:</span>
                    <p>"<?= escaparD($obsLimpia); ?>"</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Dirección de Entrega -->
        <div class="detalle-card">
            <div class="detalle-card-header">
                <h3>
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#15803d" stroke-width="2.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                    <?= $tipoEntrega === 'delivery' ? 'Dirección de Entrega' : 'Lugar de Recojo en Tienda'; ?>
                </h3>
            </div>
            <div class="detalle-card-body">
                <?php if ($tipoEntrega === 'delivery'): ?>
                    <p class="dir-text-main">
                        <?= escaparD(implode(', ', array_filter([$calle, $dist, $prov, $depto]))); ?>
                    </p>
                    <?php if (!empty($refencia)): ?>
                        <p class="dir-ref-text">Referencia: <?= escaparD($refencia); ?></p>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="dir-text-main">Av. Grau #156, Chiclayo - Lambayeque, Perú</p>
                    <p class="dir-ref-text">Horario de atención para recojo: Lunes a Sábado de 8:00 AM a 8:00 PM</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Lista de Productos Comprados -->
        <div class="detalle-card">
            <div class="detalle-card-header">
                <h3>
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#15803d" stroke-width="2.5"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>
                    Productos Comprados (<?= count($detalle); ?> <?= count($detalle) === 1 ? 'artículo' : 'artículos'; ?>)
                </h3>
            </div>
            <div class="detalle-card-body">
                <?php if (empty($detalle)): ?>
                    <p style="text-align:center; color:#94a3b8; padding:24px 0;">No se encontraron artículos registrados en este pedido.</p>
                <?php else: ?>
                <div class="detalle-product-list">
                    <?php foreach ($detalle as $item): 
                        $img = !empty($item['imagen']) ? htmlspecialchars($item['imagen']) : 'Imagenes/sin-imagen.png';
                        $qty = (float)($item['cantidad'] ?? 1);
                        $pUnit = (float)($item['precio_unitario'] ?? 0);
                        $subItem = (float)($item['subtotal'] ?? ($qty * $pUnit));
                        $und = strtolower($item['unidad_medida'] ?? 'und.');
                    ?>
                    <div class="detalle-product-item">
                        <img src="<?= $img; ?>" alt="" class="detalle-product-img" onerror="this.src='Imagenes/sin-imagen.png'">
                        <div class="detalle-product-info">
                            <h4><?= escaparD($item['titulo'] ?? $item['titulo_producto'] ?? 'Producto'); ?></h4>
                            <p class="prod-meta"><?= $qty; ?> <?= $und; ?> &times; S/ <?= number_format($pUnit, 2); ?></p>
                        </div>
                        <div class="detalle-product-subtotal">
                            <strong>S/ <?= number_format($subItem, 2); ?></strong>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Totales Finales -->
                <div class="detalle-totals-box">
                    <div class="detalle-total-row">
                        <span>Subtotal de productos:</span>
                        <span>S/ <?= number_format($subtotal, 2); ?></span>
                    </div>
                    <div class="detalle-total-row">
                        <span>Costo de envío:</span>
                        <span><?= $costoEnvio > 0 ? 'S/ ' . number_format($costoEnvio, 2) : 'S/ 0.00 (Gratis)'; ?></span>
                    </div>
                    <div class="detalle-total-row total-final">
                        <span>TOTAL PAGADO:</span>
                        <span class="total-big">S/ <?= number_format($total, 2); ?></span>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Grupo de Acciones Finales -->
        <div class="detalle-actions-bar">
            <a href="index.php?action=pedido_confirmado&id=<?= $idPedido; ?>" class="btn-action-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                <span>Ver Boleta Electrónica</span>
            </a>
            <a href="index.php?action=home_usuario" class="btn-action-secondary">
                <span>Seguir Comprando</span>
            </a>
        </div>
    </main>
</div>
</body>
</html>
