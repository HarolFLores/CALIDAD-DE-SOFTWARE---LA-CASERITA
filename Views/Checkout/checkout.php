<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php?action=login');
    exit();
}

$direcciones         = isset($direcciones) && is_array($direcciones) ? $direcciones : [];
$direccionSeleccionada = isset($direccionSeleccionada) && is_array($direccionSeleccionada) ? $direccionSeleccionada : null;
$metodosPago         = isset($metodosPago) && is_array($metodosPago) ? $metodosPago : [];
$distritos           = isset($distritos) && is_array($distritos) ? $distritos : [];
$productosCheckout   = isset($productosCheckout) && is_array($productosCheckout) ? $productosCheckout : [];
$productosNoDisponibles = isset($productosNoDisponibles) && is_array($productosNoDisponibles) ? $productosNoDisponibles : [];
$carritoValido       = isset($carritoValido) ? (bool)$carritoValido : false;
$cantidadTotal       = isset($cantidadTotal) ? (float)$cantidadTotal : 0;
$subtotal            = isset($subtotal) ? (float)$subtotal : 0;
$costoEnvio          = isset($costoEnvio) ? (float)$costoEnvio : 5;
$total               = isset($total) ? (float)$total : $subtotal + $costoEnvio;
$tipoEntrega         = $tipoEntrega ?? 'delivery';
$csrf_token          = $csrf_token ?? ($_SESSION['csrf_token'] ?? '');
$nombreUsuario       = trim($nombreUsuario ?? ($_SESSION['usuario_nombre'] ?? 'Usuario'));

$escapar = static function ($valor): string {
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
};
$formatearMoneda = static function ($valor): string {
    return 'S/ ' . number_format((float)$valor, 2, '.', '');
};
$obtenerUnidad = static function ($unidad): string {
    return strtoupper((string)$unidad) === 'KG' ? 'kg' : 'und.';
};
$direccionTexto = static function (?array $d) use ($escapar): string {
    if (!$d) return '';
    $partes = [];
    if (!empty($d['calle']))    $partes[] = $d['calle'];
    if (!empty($d['numero']))   $partes[] = '#' . $d['numero'];
    if (!empty($d['lote_manzana'])) $partes[] = 'Lote ' . $d['lote_manzana'];
    if (!empty($d['distrito'])) $partes[] = $d['distrito'];
    return implode(', ', $partes);
};

$idMetodoInicial = !empty($metodosPago) ? (int)$metodosPago[0]['id_metodo'] : 0;
$tieneDireccion  = !empty($direccionSeleccionada);
$puedeContinuar  = !empty($productosCheckout) && $carritoValido && !empty($metodosPago) && $tieneDireccion;

// Build distritos JS array for JS cost update
$distritosJson = json_encode(array_map(function($d) {
    return ['id' => (int)$d['id_distrito'], 'nombre' => $d['nombre'], 'costo' => (float)$d['costo_delivery']];
}, $distritos));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout &mdash; La Caserita</title>
    <meta name="description" content="Completa tu pedido. Selecciona modalidad de entrega, m&eacute;todo de pago y confirma tu orden.">
    <link rel="icon" type="image/png" href="Images/logo_backup.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="Styles/global.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="Styles/styles-checkout.css?v=<?= time(); ?>">
    <script src="Scripts/normalizador-modales.js?v=<?= filemtime(__DIR__ . '/../../Scripts/normalizador-modales.js'); ?>" defer></script>
</head>
<body>

<!-- ===== HEADER ===== -->
<header class="co-header">
    <div class="co-header-inner">
        <div class="co-header-left">
            <a href="index.php?action=home_usuario" class="co-brand" title="La Caserita">
                <div class="co-brand-logo">
                    <img src="Images/logo_backup.png" alt="La Caserita" onerror="this.onerror=null;this.src='Images/logo.png';">
                </div>
                <span class="co-brand-name">La Caserita</span>
            </a>
        </div>
        <div class="co-header-center">
            <span class="co-header-title">Checkout</span>
        </div>
        <div class="co-header-right"></div>
    </div>
</header>

<div style="width: min(1400px, calc(100% - 40px)); margin: 20px auto 0; padding: 0;">
    <a href="index.php?action=home_usuario" class="link-volver">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
        Volver a la tienda
    </a>
</div>

<!-- ===== BARRA DE PROGRESO ===== -->
<nav class="co-progress" aria-label="Pasos del checkout">
    <div class="co-progress-inner">
        <div class="co-step active" id="navStep1">
            <div class="co-step-bubble">1</div>
            <div class="co-step-label"><strong>Entrega</strong><small>Modalidad</small></div>
        </div>
        <div class="co-step-connector" id="connector1"></div>
        <div class="co-step" id="navStep2">
            <div class="co-step-bubble">2</div>
            <div class="co-step-label"><strong>Pago</strong><small>M&eacute;todo</small></div>
        </div>
        <div class="co-step-connector" id="connector2"></div>
        <div class="co-step" id="navStep3">
            <div class="co-step-bubble">3</div>
            <div class="co-step-label"><strong>Confirmar</strong><small>Resumen</small></div>
        </div>
    </div>
</nav>

<!-- ===== MAIN ===== -->
<main class="co-page">
<form id="formCheckout" action="index.php?action=crear_pedido" method="POST" autocomplete="off" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= $escapar($csrf_token); ?>">
    <input type="hidden" name="id_direccion" id="checkoutDireccionId" value="<?= $direccionSeleccionada ? (int)$direccionSeleccionada['id_direccion'] : ''; ?>">
    <input type="hidden" name="tipo_entrega" id="checkoutTipoEntrega" value="delivery">
    <input type="hidden" name="id_metodo" id="checkoutMetodoId" value="<?= $idMetodoInicial; ?>">

    <!-- ================================
         PANEL 1: MODALIDAD DE ENTREGA
         ================================ -->
    <div class="co-step-panel active" id="panel1">
        <div class="co-panel-layout">
            <div class="co-main-col">

                <?php if (!empty($productosNoDisponibles)): ?>
                <div class="co-alert-warn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                    <div>
                        <strong>Algunos productos necesitan revisi&oacute;n</strong>
                        <ul>
                            <?php foreach ($productosNoDisponibles as $prob): ?>
                            <li><?= $escapar($prob['titulo'] ?? 'Producto'); ?>: <?= $escapar($prob['motivo'] ?? 'No disponible.'); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Modalidad de entrega -->
                <section class="co-card">
                    <div class="co-card-header">
                        <div class="co-card-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>
                        </div>
                        <div>
                            <span class="co-eyebrow">Paso 1</span>
                            <h2>Modalidad de entrega</h2>
                            <p>Elige si deseas delivery a domicilio o recojo en tienda.</p>
                        </div>
                    </div>
                    <div class="co-options-grid">
                        <label class="co-option active" data-delivery-option="delivery">
                            <input type="radio" name="tipo_entrega_visual" value="delivery" checked>
                            <span class="co-radio-dot"></span>
                            <span class="co-option-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>
                            </span>
                            <div class="co-option-body">
                                <strong>Delivery a domicilio</strong>
                                <small>Recibe tu pedido en la direcci&oacute;n seleccionada</small>
                                <span class="co-option-meta" id="deliveryPriceLabel">&#9201; 30&ndash;45 min &nbsp;&middot;&nbsp; + <span id="costoDeliveryDisplay"><?= $formatearMoneda($costoEnvio); ?></span></span>
                            </div>
                        </label>
                        <label class="co-option" data-delivery-option="recojo">
                            <input type="radio" name="tipo_entrega_visual" value="recojo">
                            <span class="co-radio-dot"></span>
                            <span class="co-option-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                            </span>
                            <div class="co-option-body">
                                <strong>Recojo en tienda</strong>
                                <small>Recoge tu pedido en nuestro local</small>
                                <span class="co-option-meta">&#9201; 15&ndash;20 min &nbsp;&middot;&nbsp; Gratis</span>
                            </div>
                        </label>
                    </div>
                </section>

                <!-- Horario de entrega (Debajo de Modalidad de entrega - Imagen 4 y 5) -->
                <section class="co-card" id="seccionHorario">
                    <div class="co-card-header">
                        <div class="co-card-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        </div>
                        <div>
                            <span class="co-eyebrow">Horario</span>
                            <h2>Horario</h2>
                            <p>&iquest;Cu&aacute;ndo deseas recibir o recoger tu pedido?</p>
                        </div>
                    </div>

                    <div class="co-horario-row" id="horarioRow">
                        <div class="co-horario-col">
                            <label for="checkoutHorarioTipo" class="co-label">Horario</label>
                            <select name="horario_tipo" id="checkoutHorarioTipo" class="co-select">
                                <option value="inmediato">Lo antes posible (30-45 min)</option>
                                <option value="programado">Programar para despu&eacute;s</option>
                            </select>
                        </div>
                        <div class="co-horario-col" id="colFechaProgramada" style="display: none;">
                            <label for="checkoutFechaProgramada" class="co-label">Fecha programada</label>
                            <input type="datetime-local" name="fecha_programada" id="checkoutFechaProgramada" class="co-input">
                        </div>
                    </div>
                </section>

                <!-- Dirección de entrega (Nivel Chiclayo - Imagen 2 y 3) -->
                <section class="co-card" id="seccionDireccion">
                    <div class="co-card-header">
                        <div class="co-card-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 12-9 12S3 17 3 10a9 9 0 1 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                        </div>
                        <div style="flex:1">
                            <span class="co-eyebrow">Entrega</span>
                            <h2>Direcci&oacute;n de entrega</h2>
                            <p>Zona de reparto: distritos autorizados de Chiclayo.</p>
                        </div>
                        <?php if (!empty($direcciones)): ?>
                        <div class="co-saved-addresses-dropdown" style="display:flex; align-items:center; gap:8px;">
                            <label for="selectDireccionGuardada" class="co-small-label">Cargar guardada:</label>
                            <select id="selectDireccionGuardada" class="co-select-sm">
                                <option value="">-- Seleccionar --</option>
                                <?php foreach ($direcciones as $d): ?>
                                <option value="<?= (int)$d['id_direccion']; ?>"
                                    data-calle="<?= $escapar($d['calle'] ?? ''); ?>"
                                    data-numero="<?= $escapar($d['numero'] ?? ''); ?>"
                                    data-lote="<?= $escapar($d['lote_manzana'] ?? ''); ?>"
                                    data-distrito="<?= $escapar($d['distrito'] ?? ''); ?>"
                                    data-id-distrito="<?= (int)($d['id_distrito'] ?? 0); ?>"
                                    data-referencia="<?= $escapar($d['referencia'] ?? ''); ?>"
                                    data-costo="<?= (float)($d['costo_delivery'] ?? 5); ?>"
                                    <?= ($direccionSeleccionada && (int)$direccionSeleccionada['id_direccion'] === (int)$d['id_direccion']) ? 'selected' : ''; ?>>
                                    <?= $escapar($d['calle'] ?? ''); ?><?= !empty($d['distrito']) ? ' (' . $escapar($d['distrito']) . ')' : ''; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="co-address-form-grid">
                        <!-- Fila 1: Calle / Avenida + Número (Imagen 2) -->
                        <div class="co-field-group">
                            <label for="checkoutCalle" class="co-label">Calle / Avenida</label>
                            <input type="text" name="calle" id="checkoutCalle" class="co-input"
                                placeholder="Ej. Av. Jos&eacute; Balta"
                                value="<?= $escapar($direccionSeleccionada['calle'] ?? ''); ?>">
                        </div>
                        <div class="co-field-group">
                            <label for="checkoutNumero" class="co-label">N&uacute;mero</label>
                            <input type="text" name="numero" id="checkoutNumero" class="co-input"
                                placeholder="Ej. 456"
                                value="<?= $escapar($direccionSeleccionada['numero'] ?? ''); ?>">
                        </div>

                        <!-- Fila 2: Lote / Manzana + Distrito (Imagen 2 y 3) -->
                        <div class="co-field-group">
                            <label for="checkoutLote" class="co-label">Lote / Manzana</label>
                            <input type="text" name="lote_manzana" id="checkoutLote" class="co-input"
                                placeholder="Ej. Lote 12"
                                value="<?= $escapar($direccionSeleccionada['lote_manzana'] ?? ''); ?>">
                        </div>
                        <div class="co-field-group">
                            <label for="checkoutDistrito" class="co-label">Distrito</label>
                            <select name="distrito" id="checkoutDistrito" class="co-select">
                                <option value="">Selecciona...</option>
                                <?php 
                                $distritoActual = $direccionSeleccionada['distrito'] ?? '';
                                foreach ($distritos as $dist): 
                                    $isSelected = (strcasecmp(trim($distritoActual), trim($dist['nombre'])) === 0);
                                ?>
                                <option value="<?= $escapar($dist['nombre']); ?>"
                                    data-costo="<?= (float)$dist['costo_delivery']; ?>"
                                    data-id="<?= (int)$dist['id_distrito']; ?>"
                                    <?= $isSelected ? 'selected' : ''; ?>>
                                    <?= $escapar($dist['nombre']); ?> (S/ <?= number_format((float)$dist['costo_delivery'], 2); ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <input type="hidden" name="id_distrito" id="checkoutIdDistrito" value="<?= (int)($direccionSeleccionada['id_distrito'] ?? 1); ?>">
                        </div>

                        <!-- Fila 3: Referencia (Imagen 2) -->
                        <div class="co-field-group co-field-full">
                            <label for="checkoutReferencia" class="co-label">Referencia</label>
                            <textarea name="referencia" id="checkoutReferencia" class="co-textarea" rows="3"
                                placeholder="Casa color amarillo, frente al parque..."><?= $escapar($direccionSeleccionada['referencia'] ?? ''); ?></textarea>
                        </div>
                    </div>

                    <div class="co-field-error-box" id="checkoutAddressRequirement" hidden>
                        Por favor completa los campos de Calle / Avenida, N&uacute;mero y Distrito.
                    </div>
                </section>

                <!-- Indicaciones -->
                <section class="co-card">
                    <div class="co-card-header">
                        <div class="co-card-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"></path></svg>
                        </div>
                        <div>
                            <span class="co-eyebrow">Opcional</span>
                            <h2>Indicaciones del pedido</h2>
                            <p>Instrucciones especiales para la entrega.</p>
                        </div>
                    </div>
                    <div class="co-textarea-wrap">
                        <textarea name="observacion" id="checkoutObservacion" maxlength="255" rows="3" placeholder="Ej. Tocar el timbre, llamar antes de llegar..."></textarea>
                        <span class="co-char-count"><span id="observacionCount">0</span>/255</span>
                    </div>
                </section>

            </div><!-- /co-main-col -->

            <!-- Sidebar paso 1 -->
            <aside class="co-sidebar">
                <div class="co-sidebar-card co-card">
                    <div class="co-summary-header">
                        <h2>Tu Pedido</h2>
                        <span class="co-count-badge"><?= (float)$cantidadTotal; ?> prod.</span>
                    </div>
                    <div class="co-products-list">
                        <?php foreach ($productosCheckout as $prod): ?>
                        <div class="co-product-row <?= empty($prod['tiene_stock']) ? 'co-stock-err' : ''; ?>">
                            <div class="co-product-img"><img src="<?= $escapar($prod['imagen'] ?? ''); ?>" alt="<?= $escapar($prod['titulo'] ?? ''); ?>"></div>
                            <div class="co-product-data">
                                <span><?= $escapar($prod['titulo'] ?? ''); ?></span>
                                <small><?= $escapar($prod['cantidad']); ?> <?= $obtenerUnidad($prod['unidad_medida'] ?? 'UND'); ?> &times; <?= $formatearMoneda($prod['precio'] ?? 0); ?></small>
                                <?php if (empty($prod['tiene_stock'])): ?><em class="co-stock-label">Sin stock</em><?php endif; ?>
                            </div>
                            <strong><?= $formatearMoneda($prod['subtotal'] ?? 0); ?></strong>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="co-totals">
                        <div class="co-total-row"><span>Subtotal</span><strong id="checkoutSubtotal"><?= $formatearMoneda($subtotal); ?></strong></div>
                        <div class="co-total-row"><span>Env&iacute;o</span><strong id="checkoutDeliveryCost"><?= $formatearMoneda($costoEnvio); ?></strong></div>
                        <div class="co-total-divider"></div>
                        <div class="co-total-row co-total-final"><span>Total</span><strong id="checkoutTotal"><?= $formatearMoneda($total); ?></strong></div>
                    </div>
                    <!-- Seguir comprando en rojo vino y DEBAJO continuar al pago -->
                    <div style="margin-top:16px; display:flex; flex-direction:column; gap:10px;">
                        <a href="index.php?action=home_usuario" class="co-keep-shopping">&#8592; Seguir comprando</a>
                        <button type="button" class="co-btn-next co-btn-block" id="btnNextStep1Sidebar">
                            Continuar al pago
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                        </button>
                    </div>
                </div>
            </aside>
        </div><!-- /co-panel-layout -->
    </div><!-- /panel1 -->

    <!-- ================================
         PANEL 2: MÉTODO DE PAGO
         ================================ -->
    <div class="co-step-panel" id="panel2">
        <div class="co-panel-layout">
            <div class="co-main-col">

                <section class="co-card">
                    <div class="co-card-header">
                        <div class="co-card-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
                        </div>
                        <div>
                            <span class="co-eyebrow">Paso 2</span>
                            <h2>M&eacute;todo de pago</h2>
                            <p>Selecciona c&oacute;mo deseas pagar tu pedido.</p>
                        </div>
                    </div>

                    <?php if (!empty($metodosPago)): ?>
                    <div class="co-options-grid co-payment-options">
                        <?php foreach ($metodosPago as $idx => $metodo): ?>
                        <?php
                        $nombreM = strtolower(trim($metodo['nombre'] ?? ''));
                        $esYapePlin = strpos($nombreM, 'yape') !== false || strpos($nombreM, 'plin') !== false;
                        $esTarjeta = strpos($nombreM, 'tarjeta') !== false;
                        
                        if ($esYapePlin) {
                            $iconoM = '<img src="public/images/yape-plin-icon.png" alt="Yape Plin" style="height:24px;">'; // Fallback text icon if img not there
                            $iconoM = '<div style="display:flex; gap:4px;"><span style="color:#6A1B9A; font-weight:bold; background:#e1bee7; padding:2px 4px; border-radius:4px; font-size:10px;">Yape</span><span style="color:#00BFA5; font-weight:bold; background:#b2dfdb; padding:2px 4px; border-radius:4px; font-size:10px;">Plin</span></div>';
                        } elseif ($esTarjeta) {
                            $iconoM = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#d32f2f" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"></rect><line x1="2" y1="10" x2="22" y2="10"></line></svg>';
                        } else {
                            $iconoM = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#388e3c" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>';
                        }
                        ?>
                        <label class="co-option <?= $idx === 0 ? 'active' : ''; ?>"
                            data-payment-option="<?= (int)$metodo['id_metodo']; ?>"
                            data-nombre-metodo="<?= htmlspecialchars(strtolower($metodo['nombre'])); ?>"
                            data-qr="<?= htmlspecialchars($metodo['codigo_qr'] ?? ''); ?>"
                            data-cuenta="<?= htmlspecialchars($metodo['numero_cuenta'] ?? '999-111-222'); ?>"
                            data-titular="<?= htmlspecialchars($metodo['titular'] ?? 'La Caserita S.A.C.'); ?>"
                            data-requiere-comprobante="<?= !empty($metodo['requiere_comprobante']) ? '1' : '0'; ?>">
                            <input type="radio" name="metodo_pago_visual" value="<?= (int)$metodo['id_metodo']; ?>" <?= $idx === 0 ? 'checked' : ''; ?>>
                            <div class="co-radio-dot"></div>
                            <span class="co-option-icon"><?= $iconoM; ?></span>
                            <div class="co-option-body">
                                <strong><?= $escapar($metodo['nombre'] ?? 'M&eacute;todo de pago'); ?></strong>
                            </div>
                        </label>
                        
                        <?php if (strpos($nombreM, 'efectivo') !== false || strpos($nombreM, 'recibir') !== false): ?>
                            <!-- Panel Efectivo Moderno y Centrado (Imagen 5) -->
                            <div id="seccionEfectivo" class="co-dynamic-panel" style="display:<?= $idx === 0 ? 'block' : 'none'; ?>; margin-bottom:20px; padding:24px; background:#f8fafc; border-radius:12px; border:1px solid #e2e8f0; width:100%;">
                                <!-- Tarjeta Visual Efectivo (Color Sólido, Sin Degradado) -->
                                <div style="background:#15803d; border-radius:16px; padding:20px 24px; color:#fff; box-shadow:0 8px 24px rgba(21, 128, 61, 0.22); max-width:340px; margin:0 auto 20px; text-align:center;">
                                    <div style="display:inline-flex; align-items:center; justify-content:center; width:44px; height:44px; background:#4ade80; border-radius:50%; color:#052e16; font-weight:900; font-size:1.3rem; margin-bottom:6px; box-shadow:0 3px 10px rgba(74,222,128,0.4);">
                                        💵
                                    </div>
                                    <div style="font-family:'Inter', sans-serif; font-size:1.6rem; font-weight:800; letter-spacing:-0.5px; line-height:1.2; margin-bottom:4px;">
                                        Pago en Efectivo
                                    </div>
                                    <div style="font-size:0.85rem; opacity:0.95; font-weight:500;">
                                        Pagas contra entrega al recibir tu pedido
                                    </div>
                                </div>

                                <div style="max-width:360px; margin:0 auto;">
                                    <!-- Tarjeta con Total a pagar -->
                                    <div style="background:#ffffff; border:1px solid #e2e8f0; border-radius:10px; padding:12px 16px; margin-bottom:16px; display:flex; justify-content:space-between; align-items:center;">
                                        <span style="font-size:0.92rem; color:#64748b; font-weight:600;">Total a pagar:</span>
                                        <strong id="montoEfectivoTexto" style="color:#15803d; font-size:1.3rem; font-weight:800;"><?= $formatearMoneda($total); ?></strong>
                                    </div>

                                    <!-- Campo: ¿Con cuánto vas a pagar? -->
                                    <div class="co-form-group" style="margin-bottom:12px;">
                                        <label for="monto_efectivo" style="display:block; font-weight:600; font-size:0.9rem; color:#1e293b; margin-bottom:6px;">
                                            ¿Con cuánto vas a pagar? <span class="co-required" style="color:#d32f2f;">*</span>
                                        </label>
                                        <div style="display:flex; align-items:center; border:1.5px solid #cbd5e1; border-radius:8px; overflow:hidden; background:#fff; transition:border 0.2s;" id="boxMontoEfectivo">
                                            <span style="background:#f1f5f9; color:#475569; font-weight:700; font-size:1rem; padding:12px 14px; border-right:1px solid #cbd5e1; user-select:none;">
                                                S/
                                            </span>
                                            <input type="number" step="0.10" name="monto_efectivo" id="monto_efectivo" placeholder="Ej. <?= number_format($total, 2, '.', ''); ?>" style="flex:1; border:none; padding:12px 14px; font-size:1.05rem; font-weight:600; font-family:monospace; outline:none; color:#0f172a;">
                                        </div>
                                        <small id="efectivoError" style="color:#d32f2f; display:none; margin-top:6px; font-size:0.85rem; font-weight:500;">El monto ingresado debe ser mayor o igual al total a pagar.</small>
                                    </div>

                                    <!-- Botones rápidos de sugerencia de montos -->
                                    <div style="display:flex; gap:6px; margin-bottom:14px;">
                                        <button type="button" class="btn-sugerencia-efectivo" data-monto="exacto" style="flex:1; padding:7px 2px; font-size:0.78rem; font-weight:700; background:#fff; border:1px solid #cbd5e1; border-radius:6px; cursor:pointer; color:#334155; transition:all 0.2s;">Exacto</button>
                                        <button type="button" class="btn-sugerencia-efectivo" data-monto="20" style="flex:1; padding:7px 2px; font-size:0.78rem; font-weight:700; background:#fff; border:1px solid #cbd5e1; border-radius:6px; cursor:pointer; color:#334155; transition:all 0.2s;">S/ 20</button>
                                        <button type="button" class="btn-sugerencia-efectivo" data-monto="50" style="flex:1; padding:7px 2px; font-size:0.78rem; font-weight:700; background:#fff; border:1px solid #cbd5e1; border-radius:6px; cursor:pointer; color:#334155; transition:all 0.2s;">S/ 50</button>
                                        <button type="button" class="btn-sugerencia-efectivo" data-monto="100" style="flex:1; padding:7px 2px; font-size:0.78rem; font-weight:700; background:#fff; border:1px solid #cbd5e1; border-radius:6px; cursor:pointer; color:#334155; transition:all 0.2s;">S/ 100</button>
                                    </div>

                                    <!-- Indicador dinámico de vuelto -->
                                    <div id="boxVueltoEfectivo" style="display:none; background:#ecfdf5; border:1px solid #a7f3d0; border-radius:8px; padding:10px 14px; margin-bottom:14px; align-items:center; justify-content:space-between;">
                                        <span style="font-size:0.85rem; color:#065f46; font-weight:600;">Vuelto estimado:</span>
                                        <strong id="textoVueltoEfectivo" style="color:#047857; font-size:1.15rem; font-weight:800;">S/ 0.00</strong>
                                    </div>

                                    <!-- Nota informativa repartidor -->
                                    <div style="display:flex; align-items:center; gap:8px; font-size:0.82rem; color:#64748b; line-height:1.4;">
                                        <span style="font-size:1.1rem;">🛵</span>
                                        <span>El repartidor llevará el cambio indicado al entregar tu pedido en tu puerta.</span>
                                    </div>
                                </div>
                            </div>
                        <?php elseif ($esYapePlin): ?>
                            <!-- Panel Oficial Yape Directo (Imagen 4) -->
                            <div id="seccionYapePlin" class="co-dynamic-panel" style="display:<?= $idx === 0 ? 'block' : 'none'; ?>; margin-bottom:20px; padding:24px; background:#f8fafc; border-radius:12px; border:1px solid #e2e8f0; width:100%;">
                                <!-- Tarjeta Visual Yape (Color Sólido, Sin Degradado) -->
                                <div style="background:#73228B; border-radius:16px; padding:20px 24px; color:#fff; box-shadow:0 8px 24px rgba(115, 34, 139, 0.22); max-width:340px; margin:0 auto 20px; text-align:center;">
                                    <div style="display:inline-flex; align-items:center; justify-content:center; width:44px; height:44px; background:#00d3b8; border-radius:50%; color:#fff; font-weight:900; font-size:1.25rem; margin-bottom:4px; box-shadow:0 3px 10px rgba(0,211,184,0.4);">
                                        S/
                                    </div>
                                    <div style="font-family:'Inter', sans-serif; font-size:2.4rem; font-weight:900; letter-spacing:-1px; line-height:1; font-style:italic; margin-bottom:4px;">
                                        yape
                                    </div>
                                    <div style="font-size:0.85rem; opacity:0.95; font-weight:500;">
                                        Pago directo por aplicación
                                    </div>
                                </div>

                                <p style="margin:0 auto 24px; font-size:0.92rem; color:#475569; text-align:center; max-width:360px; line-height:1.45;">
                                    Ingresa a tu app <strong>Yape</strong>, abre el menú para ver tu <strong>código de aprobación</strong> y cópialo aquí para completar tu compra.
                                </p>

                                <!-- Contenedor de formulario centrado simétricamente -->
                                <div style="max-width:360px; margin:0 auto;">
                                    <!-- Campo: Celular Yape -->
                                    <div class="co-form-group" style="margin-bottom:20px;">
                                        <label for="celular_yape_plin" style="display:block; font-weight:600; font-size:0.9rem; color:#1e293b; margin-bottom:6px;">
                                            Ingresa tu celular Yape <span class="co-required" style="color:#d32f2f;">*</span>
                                        </label>
                                        <div style="display:flex; align-items:center; border:1.5px solid #cbd5e1; border-radius:8px; overflow:hidden; background:#fff; transition:border 0.2s;" id="boxCelularYape">
                                            <span style="background:#f1f5f9; color:#475569; font-weight:700; font-size:1rem; padding:12px 14px; border-right:1px solid #cbd5e1; user-select:none;">
                                                +51
                                            </span>
                                            <input type="tel" name="celular_yape_plin" id="celular_yape_plin" placeholder="987 654 321" maxlength="11" inputmode="numeric" style="flex:1; border:none; padding:12px 14px; font-size:1.05rem; font-weight:600; font-family:monospace; outline:none; color:#0f172a;">
                                        </div>
                                    </div>

                                    <!-- Campo: Código de aprobación (6 casillas OTP) -->
                                    <div class="co-form-group" style="margin-bottom:8px;">
                                        <label style="display:block; font-weight:600; font-size:0.9rem; color:#1e293b; margin-bottom:8px;">
                                            Código de aprobación <span class="co-required" style="color:#d32f2f;">*</span>
                                        </label>
                                        <div class="yape-otp-wrapper" style="display:flex; gap:8px; justify-content:space-between; width:100%;">
                                            <input type="text" class="yape-otp-box" maxlength="1" data-idx="0" inputmode="numeric" pattern="[0-9]*" placeholder="0" style="flex:1; min-width:0; height:52px; text-align:center; font-size:1.4rem; font-weight:800; border:1.5px solid #cbd5e1; border-radius:8px; outline:none; color:#73228B; transition:all 0.2s; background:#fff;">
                                            <input type="text" class="yape-otp-box" maxlength="1" data-idx="1" inputmode="numeric" pattern="[0-9]*" placeholder="0" style="flex:1; min-width:0; height:52px; text-align:center; font-size:1.4rem; font-weight:800; border:1.5px solid #cbd5e1; border-radius:8px; outline:none; color:#73228B; transition:all 0.2s; background:#fff;">
                                            <input type="text" class="yape-otp-box" maxlength="1" data-idx="2" inputmode="numeric" pattern="[0-9]*" placeholder="0" style="flex:1; min-width:0; height:52px; text-align:center; font-size:1.4rem; font-weight:800; border:1.5px solid #cbd5e1; border-radius:8px; outline:none; color:#73228B; transition:all 0.2s; background:#fff;">
                                            <input type="text" class="yape-otp-box" maxlength="1" data-idx="3" inputmode="numeric" pattern="[0-9]*" placeholder="0" style="flex:1; min-width:0; height:52px; text-align:center; font-size:1.4rem; font-weight:800; border:1.5px solid #cbd5e1; border-radius:8px; outline:none; color:#73228B; transition:all 0.2s; background:#fff;">
                                            <input type="text" class="yape-otp-box" maxlength="1" data-idx="4" inputmode="numeric" pattern="[0-9]*" placeholder="0" style="flex:1; min-width:0; height:52px; text-align:center; font-size:1.4rem; font-weight:800; border:1.5px solid #cbd5e1; border-radius:8px; outline:none; color:#73228B; transition:all 0.2s; background:#fff;">
                                            <input type="text" class="yape-otp-box" maxlength="1" data-idx="5" inputmode="numeric" pattern="[0-9]*" placeholder="0" style="flex:1; min-width:0; height:52px; text-align:center; font-size:1.4rem; font-weight:800; border:1.5px solid #cbd5e1; border-radius:8px; outline:none; color:#73228B; transition:all 0.2s; background:#fff;">
                                        </div>
                                        <input type="hidden" name="codigo_yape_plin" id="codigo_yape_plin" value="">
                                        <input type="hidden" name="billetera_seleccionada" id="billetera_seleccionada" value="Yape">
                                    </div>
                                </div>
                            </div>
                        <?php elseif ($esTarjeta): ?>
                            <!-- Panel Tarjeta con Detección Automática de Marcas (Imagen 2 y 3) -->
                            <div id="seccionTarjeta" class="co-dynamic-panel" style="display:<?= $idx === 0 ? 'block' : 'none'; ?>; margin-bottom:20px; padding:24px; background:#f8fafc; border-radius:12px; border:1px solid #e2e8f0; width:100%;">
                                <!-- Tarjeta Visual 3D Flip (Color Sólido, Sin Degradado) -->
                                <div class="cc-flip-container" style="perspective:1000px; max-width:340px; margin:0 auto 24px;">
                                    <div class="cc-flipper" id="ccFlipper" style="transition:0.6s; transform-style:preserve-3d; position:relative; height:200px; width:100%;">
                                        <!-- Front -->
                                        <div class="cc-front" id="ccFrontCard" style="backface-visibility:hidden; position:absolute; top:0; left:0; width:100%; height:100%; background:#1e293b; border-radius:16px; padding:24px; color:#fff; box-shadow:0 8px 24px rgba(0,0,0,0.22); box-sizing:border-box; transition:background 0.35s ease;">
                                            <div style="display:flex; justify-content:space-between; margin-bottom:28px; align-items:center;">
                                                <!-- Chip EMV con contactos realistas -->
                                                <div style="width:44px; height:32px; background:#d97706; border-radius:6px; border:1px solid #fbbf24; position:relative; overflow:hidden;">
                                                    <div style="width:100%; height:1px; background:#fde047; position:absolute; top:10px;"></div>
                                                    <div style="width:100%; height:1px; background:#fde047; position:absolute; bottom:10px;"></div>
                                                    <div style="height:100%; width:1px; background:#fde047; position:absolute; left:20px;"></div>
                                                </div>
                                                <div id="ccBrandVisual" style="min-height:28px; display:flex; align-items:center;">
                                                    <span style="font-weight:900; font-size:1.25rem; font-style:italic; letter-spacing:1px; color:#cbd5e1;">TARJETA</span>
                                                </div>
                                            </div>
                                            <div id="ccNumVisual" style="font-size:1.4rem; letter-spacing:2px; margin-bottom:24px; font-family:monospace; min-height:26px;">•••• •••• •••• ••••</div>
                                            <div style="display:flex; justify-content:space-between; font-size:0.75rem; text-transform:uppercase; letter-spacing:1px; opacity:0.8; margin-bottom:4px;">
                                                <span>Titular</span>
                                                <span>Vence</span>
                                            </div>
                                            <div style="display:flex; justify-content:space-between; font-size:1.1rem; font-family:monospace;">
                                                <span id="ccNameVisual" style="min-height:20px; text-transform:uppercase;">NOMBRE APELLIDO</span>
                                                <span id="ccExpVisual" style="min-height:20px;">MM/AA</span>
                                            </div>
                                        </div>
                                        <!-- Back -->
                                        <div class="cc-back" id="ccBackCard" style="backface-visibility:hidden; position:absolute; top:0; left:0; width:100%; height:100%; background:#1e293b; border-radius:16px; color:#fff; box-shadow:0 8px 24px rgba(0,0,0,0.22); transform:rotateY(180deg); box-sizing:border-box; padding-top:24px; transition:background 0.35s ease;">
                                            <div style="background:#09090b; height:40px; width:100%; margin-bottom:16px;"></div>
                                            <div style="padding:0 24px;">
                                                <div style="text-align:right; font-size:0.8rem; margin-bottom:4px; opacity:0.8;">CVV</div>
                                                <div style="background:#fff; color:#111; padding:8px 12px; border-radius:4px; text-align:right; font-family:monospace; font-size:1.1rem; min-height:18px;" id="ccCvvVisual">•••</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Fila de Logos de Marcas Aceptadas (Solo Visa, Amex y Mastercard) -->
                                <div style="display:flex; align-items:center; gap:10px; margin-bottom:18px; flex-wrap:wrap;">
                                    <span style="font-size:0.85rem; color:#475569; font-weight:600;">Tarjetas aceptadas:</span>
                                    <div style="display:flex; align-items:center; gap:8px;">
                                        <!-- Visa -->
                                        <div id="badgeCardVisa" class="cc-brand-badge" title="Visa" style="background:#fff; border:1.5px solid #cbd5e1; border-radius:6px; padding:4px 8px; display:inline-flex; align-items:center; justify-content:center; height:32px; min-width:52px; transition:all 0.25s ease;">
                                            <span style="font-family:'Arial Black', sans-serif; font-weight:900; font-size:14px; font-style:italic; letter-spacing:0.5px; color:#1434cb;">VISA</span>
                                        </div>
                                        <!-- American Express -->
                                        <div id="badgeCardAmex" class="cc-brand-badge" title="American Express" style="background:#fff; border:1.5px solid #cbd5e1; border-radius:6px; padding:3px 6px; display:inline-flex; align-items:center; justify-content:center; height:32px; min-width:52px; transition:all 0.25s ease;">
                                            <span style="font-family:'Arial Black', sans-serif; font-size:7.5px; font-weight:900; color:#016FD0; line-height:1.05; text-align:center; text-transform:uppercase;">AMERICAN<br>EXPRESS</span>
                                        </div>
                                        <!-- Mastercard -->
                                        <div id="badgeCardMastercard" class="cc-brand-badge" title="Mastercard" style="background:#fff; border:1.5px solid #cbd5e1; border-radius:6px; padding:4px 8px; display:inline-flex; align-items:center; justify-content:center; height:32px; min-width:52px; transition:all 0.25s ease;">
                                            <svg viewBox="0 0 36 22" width="30" height="18"><circle cx="12" cy="11" r="8" fill="#EB001B"/><circle cx="22" cy="11" r="8" fill="#F79E1B" fill-opacity="0.88"/></svg>
                                        </div>
                                    </div>
                                </div>

                                <!-- Formulario Tarjeta con Detección en Tiempo Real -->
                                <div class="co-form-group" style="margin-bottom:16px;">
                                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                                        <label style="font-weight:600; font-size:0.9rem; margin:0;">Número de tarjeta <span class="co-required">*</span></label>
                                        <span id="ccBadgeMarca" style="font-size:0.75rem; font-weight:700; padding:2px 8px; border-radius:6px; background:#e2e8f0; color:#475569; display:inline-flex; align-items:center; gap:4px;">
                                            💳 Tarjeta
                                        </span>
                                    </div>
                                    <input type="text" name="cc_numero" id="cc_numero" placeholder="1234 5678 9012 3456" maxlength="19" style="width:100%; padding:12px; border:1px solid #cbd5e1; border-radius:8px; font-size:1rem; box-sizing:border-box;">
                                </div>
                                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px;">
                                    <div class="co-form-group">
                                        <label style="font-weight:600; font-size:0.9rem;">Vencimiento <span class="co-required">*</span></label>
                                        <input type="text" name="cc_vencimiento" id="cc_vencimiento" placeholder="MM/AA" maxlength="5" style="width:100%; padding:12px; border:1px solid #cbd5e1; border-radius:8px; font-size:1rem; box-sizing:border-box;">
                                    </div>
                                    <div class="co-form-group">
                                        <label style="font-weight:600; font-size:0.9rem;">CVV <span class="co-required">*</span></label>
                                        <input type="password" name="cc_cvv" id="cc_cvv" placeholder="•••" maxlength="4" oninput="this.value = this.value.replace(/[^0-9]/g, '');" style="width:100%; padding:12px; border:1px solid #cbd5e1; border-radius:8px; font-size:1rem; box-sizing:border-box;">
                                    </div>
                                </div>
                                <div class="co-form-group">
                                    <label style="font-weight:600; font-size:0.9rem;">Nombre en la tarjeta <span class="co-required">*</span></label>
                                    <input type="text" name="cc_nombre" id="cc_nombre" placeholder="JUAN PÉREZ" oninput="this.value = this.value.replace(/[^a-zA-Z\s]/g, '');" style="width:100%; padding:12px; border:1px solid #cbd5e1; border-radius:8px; font-size:1rem; box-sizing:border-box; text-transform:uppercase;">
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="co-empty-msg">No hay m&eacute;todos de pago disponibles en este momento.</div>
                    <?php endif; ?>
                </section>

            </div><!-- /co-main-col -->

            <!-- Sidebar paso 2 -->
            <aside class="co-sidebar">
                <div class="co-sidebar-card co-card">
                    <div class="co-summary-header">
                        <h2>Tu Pedido</h2>
                        <span class="co-count-badge"><?= (float)$cantidadTotal; ?> prod.</span>
                    </div>
                    <div class="co-products-list">
                        <?php foreach ($productosCheckout as $prod): ?>
                        <div class="co-product-row <?= empty($prod['tiene_stock']) ? 'co-stock-err' : ''; ?>">
                            <div class="co-product-img"><img src="<?= $escapar($prod['imagen'] ?? ''); ?>" alt="<?= $escapar($prod['titulo'] ?? ''); ?>"></div>
                            <div class="co-product-data">
                                <span><?= $escapar($prod['titulo'] ?? ''); ?></span>
                                <small><?= $escapar($prod['cantidad']); ?> <?= $obtenerUnidad($prod['unidad_medida'] ?? 'UND'); ?> &times; <?= $formatearMoneda($prod['precio'] ?? 0); ?></small>
                                <?php if (empty($prod['tiene_stock'])): ?><em class="co-stock-label">Sin stock</em><?php endif; ?>
                            </div>
                            <strong><?= $formatearMoneda($prod['subtotal'] ?? 0); ?></strong>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="co-totals">
                        <div class="co-total-row"><span>Subtotal</span><strong><?= $formatearMoneda($subtotal); ?></strong></div>
                        <div class="co-total-row"><span>Env&iacute;o</span><strong id="checkoutDeliveryCost2"><?= $formatearMoneda($costoEnvio); ?></strong></div>
                        <div class="co-total-divider"></div>
                        <div class="co-total-row co-total-final"><span>Total</span><strong id="checkoutTotal2"><?= $formatearMoneda($total); ?></strong></div>
                    <div style="margin-top:16px; display:flex; flex-direction:column; gap:10px;">
                        <button type="button" class="co-btn-back-link" id="btnBackStep2Sidebar">&#8592; Volver</button>
                        <button type="button" class="co-btn-next co-btn-block" id="btnNextStep2Sidebar">
                            Ver confirmaci&oacute;n
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                        </button>
                    </div>
                </div>
            </aside>
        </div><!-- /co-panel-layout -->
    </div><!-- /panel2 -->

    <!-- ================================
         PANEL 3: CONFIRMACIÓN + RESUMEN
         ================================ -->
    <div class="co-step-panel" id="panel3">
        <div class="co-panel-layout">
            <div class="co-main-col">

                <section class="co-card">
                    <div class="co-card-header">
                        <div class="co-card-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        </div>
                        <div>
                            <span class="co-eyebrow">Paso 3</span>
                            <h2>Confirma tu pedido</h2>
                            <p>Revisa los detalles antes de enviar tu orden.</p>
                        </div>
                    </div>

                    <!-- Bloque Entrega -->
                    <div class="co-confirm-block">
                        <div class="co-confirm-block-header">
                            <h3>Modalidad de entrega</h3>
                            <button type="button" class="co-edit-link" onclick="goToStep(1)">Editar</button>
                        </div>
                        <div class="co-confirm-content">
                            <p id="confirmEntregaModalidad"><strong>Delivery a domicilio</strong></p>
                            <p id="confirmEntregaDireccion" class="co-muted"></p>
                            <p id="confirmEntregaIndicaciones" class="co-muted co-italic"></p>
                        </div>
                    </div>

                    <!-- Bloque Pago -->
                    <div class="co-confirm-block">
                        <div class="co-confirm-block-header">
                            <h3>M&eacute;todo de pago</h3>
                            <button type="button" class="co-edit-link" onclick="goToStep(2)">Editar</button>
                        </div>
                        <div class="co-confirm-content">
                            <p id="confirmMetodoPago"><strong>&mdash;</strong></p>
                        </div>
                    </div>



                    <!-- Totales -->
                    <div class="co-confirm-totals">
                        <div class="co-total-row"><span>Subtotal</span><strong><?= $formatearMoneda($subtotal); ?></strong></div>
                        <div class="co-total-row"><span>Env&iacute;o</span><strong id="checkoutDeliveryCost3"><?= $formatearMoneda($costoEnvio); ?></strong></div>
                        <div class="co-total-divider"></div>
                        <div class="co-total-row co-total-final"><span>Total a pagar</span><strong id="checkoutTotal3"><?= $formatearMoneda($total); ?></strong></div>
                    </div>
                </section>

                <div class="co-field-error-box" id="checkoutValidationMessage" <?= $puedeContinuar ? 'hidden' : ''; ?>>
                    <?php if (!$carritoValido): ?>
                        Revisa los productos que no tienen stock suficiente.
                    <?php elseif (empty($metodosPago)): ?>
                        No existen m&eacute;todos de pago disponibles.
                    <?php elseif (!$tieneDireccion): ?>
                        Selecciona o registra una direcci&oacute;n para delivery.
                    <?php endif; ?>
                </div>

            </div><!-- /co-main-col -->

            <!-- Sidebar paso 3 -->
            <aside class="co-sidebar">
                <div class="co-sidebar-card co-card">
                    <div class="co-summary-header">
                        <h2>Tu Pedido</h2>
                        <span class="co-count-badge"><?= (float)$cantidadTotal; ?> prod.</span>
                    </div>
                    <div class="co-products-list">
                        <?php foreach ($productosCheckout as $prod): ?>
                        <div class="co-product-row <?= empty($prod['tiene_stock']) ? 'co-stock-err' : ''; ?>">
                            <div class="co-product-img"><img src="<?= $escapar($prod['imagen'] ?? ''); ?>" alt="<?= $escapar($prod['titulo'] ?? ''); ?>"></div>
                            <div class="co-product-data">
                                <span><?= $escapar($prod['titulo'] ?? ''); ?></span>
                                <small><?= $escapar($prod['cantidad']); ?> <?= $obtenerUnidad($prod['unidad_medida'] ?? 'UND'); ?> &times; <?= $formatearMoneda($prod['precio'] ?? 0); ?></small>
                                <?php if (empty($prod['tiene_stock'])): ?><em class="co-stock-label">Sin stock</em><?php endif; ?>
                            </div>
                            <strong><?= $formatearMoneda($prod['subtotal'] ?? 0); ?></strong>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="co-totals">
                        <div class="co-total-row"><span>Subtotal</span><strong><?= $formatearMoneda($subtotal); ?></strong></div>
                        <div class="co-total-row"><span>Env&iacute;o</span><strong id="checkoutDeliveryCost4"><?= $formatearMoneda($costoEnvio); ?></strong></div>
                        <div class="co-total-divider"></div>
                        <div class="co-total-row co-total-final"><span>Total</span><strong id="checkoutTotal4"><?= $formatearMoneda($total); ?></strong></div>
                    </div>
                    <div style="margin-top:16px; display:flex; flex-direction:column; gap:10px;">
                        <button type="button" class="co-btn-back-link" id="btnBackStep3Sidebar">&#8592; Volver al pago</button>
                        <button type="submit" class="co-btn-submit co-btn-block" id="btnHacerPedidoSidebar" <?= $puedeContinuar ? '' : 'disabled'; ?>>
                            <span class="co-submit-default">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                Hacer pedido
                            </span>
                            <span class="co-submit-loading" hidden>Procesando...</span>
                        </button>
                    </div>
                    <p class="co-terms" style="margin-top:16px;">Al hacer el pedido confirmas que revisaste los productos, direcci&oacute;n y m&eacute;todo de pago.</p>
                </div>
            </aside>
        </div><!-- /co-panel-layout -->
    </div><!-- /panel3 -->

</form>

<div class="co-modal-overlay" id="modalConfirmarPedido" aria-hidden="true">
    <div class="co-modal" role="dialog" aria-modal="true" aria-labelledby="tituloConfirmarPedido">
        <div class="co-modal-head">
            <div>
                <span class="co-eyebrow">Revisión final</span>
                <h2 id="tituloConfirmarPedido">¿Confirmas tu pedido?</h2>
                <p>Se enviará la orden con los productos y datos que revisaste.</p>
            </div>
        </div>
        <div class="co-modal-body">
            <div class="co-confirm-totals" style="margin:0;">
                <div class="co-total-row co-total-final"><span>Total a pagar</span><strong id="checkoutModalConfirmTotal"><?= $formatearMoneda($total); ?></strong></div>
            </div>
        </div>
        <div class="co-modal-foot">
            <button type="button" class="co-btn-secondary" data-close-modal="modalConfirmarPedido">Seguir revisando</button>
            <button type="button" class="co-btn-submit" id="btnConfirmarPedidoFinal">Sí, hacer pedido</button>
        </div>
    </div>
</div>
</main>

<!-- FOOTER -->
<?php include 'Views/Home/partials/footer.php'; ?>

<!-- MODAL: LISTA DE DIRECCIONES -->
<div class="co-modal-overlay" id="modalDireccionesCheckout" aria-hidden="true">
    <div class="co-modal">
        <div class="co-modal-head">
            <div>
                <span class="co-eyebrow">Entrega</span>
                <h2>Mis direcciones</h2>
                <p>Selecciona d&oacute;nde deseas recibir el pedido.</p>
            </div>
            <button type="button" class="co-modal-close" data-close-modal="modalDireccionesCheckout">&#215;</button>
        </div>
        <div class="co-modal-body">
            <div id="checkoutAddressList" class="co-address-list">
                <?php if (!empty($direcciones)): ?>
                <?php foreach ($direcciones as $dir): ?>
                <label class="co-address-opt <?= $direccionSeleccionada && (int)$direccionSeleccionada['id_direccion'] === (int)$dir['id_direccion'] ? 'active' : ''; ?>"
                    data-id-direccion="<?= (int)$dir['id_direccion']; ?>"
                    data-calle="<?= $escapar($dir['calle'] ?? ''); ?>"
                    data-numero="<?= $escapar($dir['numero'] ?? ''); ?>"
                    data-lote="<?= $escapar($dir['lote_manzana'] ?? ''); ?>"
                    data-distrito="<?= $escapar($dir['distrito'] ?? ''); ?>"
                    data-referencia="<?= $escapar($dir['referencia'] ?? ''); ?>"
                    data-costo="<?= (float)($dir['costo_delivery'] ?? 5); ?>">
                    <input type="radio" name="direccion_checkout_visual" value="<?= (int)$dir['id_direccion']; ?>" <?= $direccionSeleccionada && (int)$direccionSeleccionada['id_direccion'] === (int)$dir['id_direccion'] ? 'checked' : ''; ?>>
                    <span class="co-radio-dot"></span>
                    <span class="co-address-opt-icon">&#127968;</span>
                    <div class="co-address-opt-info">
                        <strong><?= (int)$dir['es_principal'] === 1 ? 'Direcci&oacute;n principal' : 'Direcci&oacute;n guardada'; ?></strong>
                        <small><?= $escapar($direccionTexto($dir)); ?></small>
                        <?php if (!empty($dir['referencia'])): ?><span><?= $escapar($dir['referencia']); ?></span><?php endif; ?>
                        <?php if (!empty($dir['costo_delivery'])): ?>
                        <span class="co-badge-green" style="margin-top:4px;">Env&iacute;o: <?= $formatearMoneda($dir['costo_delivery']); ?></span>
                        <?php endif; ?>
                    </div>
                    <?php if ((int)$dir['es_principal'] === 1): ?><span class="co-badge-green">Principal</span><?php endif; ?>
                </label>
                <?php endforeach; ?>
                <?php else: ?>
                <div class="co-empty-msg">No tienes direcciones registradas.</div>
                <?php endif; ?>
            </div>
        </div>
        <div class="co-modal-foot">
            <button type="button" class="co-btn-secondary" data-close-modal="modalDireccionesCheckout">Cancelar</button>
            <button type="button" class="co-btn-primary" id="btnAbrirNuevaDireccion">+ Nueva direcci&oacute;n</button>
        </div>
    </div>
</div>

<!-- MODAL: NUEVA DIRECCIÓN (campos Chiclayo) -->
<div class="co-modal-overlay" id="modalNuevaDireccionCheckout" aria-hidden="true">
    <div class="co-modal">
        <div class="co-modal-head">
            <div>
                <span class="co-eyebrow">Direcci&oacute;n</span>
                <h2>Registrar nueva direcci&oacute;n</h2>
                <p>Esta direcci&oacute;n tambi&eacute;n se guardar&aacute; en tu cuenta.</p>
            </div>
            <button type="button" class="co-modal-close" data-close-modal="modalNuevaDireccionCheckout">&#215;</button>
        </div>
        <form id="formNuevaDireccionCheckout" action="index.php?action=crear_direccion" method="POST" autocomplete="off">
            <input type="hidden" name="csrf_token" value="<?= $escapar($csrf_token); ?>">
            <input type="hidden" name="departamento" value="Lambayeque">
            <input type="hidden" name="provincia" value="Chiclayo">
            <div class="co-modal-body">
                <div class="co-form-grid">
                    <!-- Calle / Avenida -->
                    <div class="co-form-group">
                        <label for="checkoutCalle">Calle / Avenida <span class="co-required">*</span></label>
                        <input type="text" name="calle" id="checkoutCalle" maxlength="150" placeholder="Ej. Av. Jos&eacute; Balta" required>
                        <small class="co-field-error" data-error-for="calle"></small>
                    </div>
                    <!-- Número -->
                    <div class="co-form-group">
                        <label for="checkoutNumero">N&uacute;mero <span class="co-required">*</span></label>
                        <input type="text" name="numero" id="checkoutNumero" maxlength="20" placeholder="Ej. 456" required>
                        <small class="co-field-error" data-error-for="numero"></small>
                    </div>
                    <!-- Lote / Manzana -->
                    <div class="co-form-group">
                        <label for="checkoutLote">Lote / Manzana <small style="color:#94a3b8;">(opcional)</small></label>
                        <input type="text" name="lote_manzana" id="checkoutLote" maxlength="30" placeholder="Ej. Lote 12">
                    </div>
                    <!-- Distrito dropdown -->
                    <div class="co-form-group">
                        <label for="checkoutDistrito">Distrito <span class="co-required">*</span></label>
                        <select name="distrito" id="checkoutDistrito" required class="co-select">
                            <option value="">Selecciona...</option>
                            <?php foreach ($distritos as $dist): ?>
                            <option value="<?= $escapar($dist['nombre']); ?>"
                                data-costo="<?= (float)$dist['costo_delivery']; ?>"
                                data-id="<?= (int)$dist['id_distrito']; ?>">
                                <?= $escapar($dist['nombre']); ?> (S/ <?= number_format((float)$dist['costo_delivery'], 2); ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <input type="hidden" name="id_distrito" id="checkoutIdDistrito" value="">
                        <small class="co-field-error" data-error-for="distrito"></small>
                    </div>
                    <!-- Referencia -->
                    <div class="co-form-group co-form-full">
                        <label for="checkoutReferencia">Referencia <small style="color:#94a3b8;">(opcional)</small></label>
                        <textarea name="referencia" id="checkoutReferencia" rows="3" maxlength="200" placeholder="Ej. Casa color amarilla, frente al parque..."></textarea>
                    </div>
                    <label class="co-checkbox co-form-full">
                        <input type="checkbox" name="es_principal" value="1">
                        <span class="co-checkbox-box"></span>
                        <span>
                            <strong>Guardar como direcci&oacute;n principal</strong>
                            <small>Se seleccionar&aacute; autom&aacute;ticamente en futuras compras.</small>
                        </span>
                    </label>
                </div>
            </div>
            <div class="co-modal-foot">
                <button type="button" class="co-btn-secondary" data-close-modal="modalNuevaDireccionCheckout">Cancelar</button>
                <button type="submit" class="co-btn-primary" id="btnGuardarDireccionCheckout">Guardar direcci&oacute;n</button>
            </div>
        </form>
    </div>
</div>

<!-- TOAST -->
<div class="co-toast-container" id="checkoutToastContainer"></div>

<script>
var DISTRITOS_DATA = <?= $distritosJson; ?>;

function previewVoucherImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('voucherPreviewImg').src = e.target.result;
            document.getElementById('voucherPreviewContainer').style.display = 'block';
            document.getElementById('voucherDropContent').style.display = 'none';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
function removeVoucherImage() {
    var input = document.getElementById('captura_voucher');
    if (input) input.value = '';
    document.getElementById('voucherPreviewContainer').style.display = 'none';
    document.getElementById('voucherDropContent').style.display = 'block';
}

var currentStep = 1;

function goToStep(n) {
    document.querySelectorAll('.co-step-panel').forEach(function(p) { p.classList.remove('active'); });
    document.querySelectorAll('.co-step').forEach(function(s) { s.classList.remove('active','completed'); });
    document.querySelectorAll('.co-step-connector').forEach(function(c) { c.classList.remove('active'); });

    for (var i = 1; i <= 3; i++) {
        var el = document.getElementById('navStep' + i);
        if (!el) continue;
        if (i < n) el.classList.add('completed');
        else if (i === n) el.classList.add('active');
    }
    if (n >= 2) { var c1 = document.getElementById('connector1'); if(c1) c1.classList.add('active'); }
    if (n >= 3) { var c2 = document.getElementById('connector2'); if(c2) c2.classList.add('active'); }

    var panel = document.getElementById('panel' + n);
    if (panel) panel.classList.add('active');
    currentStep = n;

    if (n === 3) populateConfirmation();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function populateConfirmation() {
    var tipoEl = document.getElementById('checkoutTipoEntrega');
    var tipo = tipoEl ? tipoEl.value : 'delivery';

    var mEl = document.getElementById('confirmEntregaModalidad');
    var dEl = document.getElementById('confirmEntregaDireccion');
    var iEl = document.getElementById('confirmEntregaIndicaciones');

    // Horario
    var hTipo = document.getElementById('checkoutHorarioTipo');
    var hFecha = document.getElementById('checkoutFechaProgramada');
    var hTexto = 'Lo antes posible (30-45 min)';
    if (hTipo && hTipo.value === 'programado') {
        if (hFecha && hFecha.value) {
            var f = new Date(hFecha.value);
            hTexto = 'Programado: ' + f.toLocaleDateString('es-PE') + ' ' + f.toLocaleTimeString('es-PE', { hour: '2-digit', minute: '2-digit' });
        } else {
            hTexto = 'Programado para despu\u00e9s';
        }
    }

    if (mEl) mEl.innerHTML = tipo === 'delivery'
        ? '<div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;"><strong style="font-size:0.98rem;color:#0f172a;">Delivery a domicilio</strong> <span style="font-size:0.8rem;background:#f1f5f9;padding:3px 10px;border-radius:6px;color:#475569;font-weight:600;border:1px solid #e2e8f0;">' + hTexto + '</span></div>'
        : '<div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;"><strong style="font-size:0.98rem;color:#0f172a;">Recojo en tienda</strong> <span style="font-size:0.8rem;background:#f1f5f9;padding:3px 10px;border-radius:6px;color:#475569;font-weight:600;border:1px solid #e2e8f0;">' + hTexto + '</span></div>';

    if (dEl) {
        if (tipo === 'delivery') {
            var calle = (document.getElementById('checkoutCalle') || {}).value || '';
            var num = (document.getElementById('checkoutNumero') || {}).value || '';
            var lote = (document.getElementById('checkoutLote') || {}).value || '';
            var dist = (document.getElementById('checkoutDistrito') || {}).value || '';
            var parts = [];
            if (calle) parts.push(calle);
            if (num) parts.push('#' + num);
            if (lote) parts.push('Lote ' + lote);
            if (dist) parts.push(dist + ', Chiclayo');
            dEl.textContent = parts.length ? parts.join(', ') : 'Direcci\u00f3n en Chiclayo';
        } else {
            dEl.textContent = 'Pasar\u00e1s a recoger tu pedido en nuestro local.';
        }
    }

    var obs = document.getElementById('checkoutObservacion');
    if (iEl) iEl.textContent = obs && obs.value.trim() ? 'Indicaciones: ' + obs.value.trim() : '';

    var pagoEl = document.getElementById('confirmMetodoPago');
    var activePayment = document.querySelector('[data-payment-option].active');
    if (pagoEl && activePayment) {
        var nm = activePayment.querySelector('strong');
        pagoEl.innerHTML = '<strong>' + (nm ? nm.textContent.trim() : 'M\u00e9todo seleccionado') + '</strong>';
    }

    ['3','4'].forEach(function(s) {
        var dc = document.getElementById('checkoutDeliveryCost');
        var dcN = document.getElementById('checkoutDeliveryCost' + s);
        if (dc && dcN) dcN.textContent = dc.textContent;
        var t = document.getElementById('checkoutTotal');
        var tN = document.getElementById('checkoutTotal' + s);
        if (t && tN) tN.textContent = t.textContent;
    });
}

document.addEventListener('DOMContentLoaded', function() {
    // Distrito select — actualizar costo y recalculate
    var selectDistrito = document.getElementById('checkoutDistrito');
    var idDistritoInput = document.getElementById('checkoutIdDistrito');
    if (selectDistrito) {
        selectDistrito.addEventListener('change', function() {
            var opt = this.options[this.selectedIndex];
            if (!opt) return;
            var costo = parseFloat(opt.dataset.costo || '5') || 5;
            var idDist = opt.dataset.id || '';
            if (idDistritoInput) idDistritoInput.value = idDist;
            
            // Actualizar preview de costo en entrega
            var displayEl = document.getElementById('costoDeliveryDisplay');
            if (displayEl) displayEl.textContent = 'S/ ' + costo.toFixed(2);

            // Actualizar costo de envío y totales en checkout
            var subtotalEl = document.getElementById('checkoutSubtotal');
            var subtotalVal = parseFloat((subtotalEl ? subtotalEl.textContent : '0').replace('S/','').replace(',','').trim()) || 0;
            var tipoEntregaEl = document.getElementById('checkoutTipoEntrega');
            var esDelivery = !tipoEntregaEl || tipoEntregaEl.value === 'delivery';
            var envioVal = esDelivery ? costo : 0;
            var totalVal = subtotalVal + envioVal;

            ['', '2', '3', '4'].forEach(function(suf) {
                var dCostEl = document.getElementById('checkoutDeliveryCost' + suf);
                var totEl = document.getElementById('checkoutTotal' + suf);
                if (dCostEl) dCostEl.textContent = 'S/ ' + envioVal.toFixed(2);
                if (totEl) totEl.textContent = 'S/ ' + totalVal.toFixed(2);
            });
            var voucherMonto = document.getElementById('voucherMontoTexto');
            if (voucherMonto) voucherMonto.textContent = 'S/ ' + totalVal.toFixed(2);
        });
    }

    // Horario de entrega toggle (Imagen 4 y 5)
    var selectHorario = document.getElementById('checkoutHorarioTipo');
    var colFecha = document.getElementById('colFechaProgramada');
    var horarioRow = document.getElementById('horarioRow');
    var fechaInput = document.getElementById('checkoutFechaProgramada');
    if (selectHorario) {
        if (fechaInput) {
            var now = new Date();
            now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
            fechaInput.min = now.toISOString().slice(0, 16);
        }
        selectHorario.addEventListener('change', function() {
            if (this.value === 'programado') {
                if (colFecha) colFecha.style.display = 'flex';
                if (horarioRow) horarioRow.classList.add('is-programado');
                if (fechaInput && !fechaInput.value) {
                    var defaultDate = new Date();
                    defaultDate.setHours(defaultDate.getHours() + 1);
                    defaultDate.setMinutes(defaultDate.getMinutes() - defaultDate.getTimezoneOffset());
                    fechaInput.value = defaultDate.toISOString().slice(0, 16);
                }
            } else {
                if (colFecha) colFecha.style.display = 'none';
                if (horarioRow) horarioRow.classList.remove('is-programado');
            }
        });
    }

    // Cargar dirección guardada si el usuario selecciona una
    var selectSaved = document.getElementById('selectDireccionGuardada');
    if (selectSaved) {
        selectSaved.addEventListener('change', function() {
            var opt = this.options[this.selectedIndex];
            if (!opt || !opt.value) return;
            var calle = document.getElementById('checkoutCalle');
            var num = document.getElementById('checkoutNumero');
            var lote = document.getElementById('checkoutLote');
            var dist = document.getElementById('checkoutDistrito');
            var ref = document.getElementById('checkoutReferencia');
            var idDir = document.getElementById('checkoutDireccionId');
            var idDist = document.getElementById('checkoutIdDistrito');

            if (calle && opt.dataset.calle) calle.value = opt.dataset.calle;
            if (num) num.value = opt.dataset.numero || '';
            if (lote) lote.value = opt.dataset.lote || '';
            if (ref) ref.value = opt.dataset.referencia || '';
            if (idDir) idDir.value = opt.value;
            if (idDist && opt.dataset.idDistrito) idDist.value = opt.dataset.idDistrito;

            if (dist && opt.dataset.distrito) {
                for (var i = 0; i < dist.options.length; i++) {
                    if (dist.options[i].value.toLowerCase() === opt.dataset.distrito.toLowerCase()) {
                        dist.selectedIndex = i;
                        dist.dispatchEvent(new Event('change'));
                        break;
                    }
                }
            }
        });
    }

    // Alternar paneles de pago en tiempo real (Efectivo, Yape, Tarjeta)
    function cambiarPanelPago(opt) {
        if (!opt) return;
        var secEf = document.getElementById('seccionEfectivo');
        var secYp = document.getElementById('seccionYapePlin');
        var secTj = document.getElementById('seccionTarjeta');

        if (secEf) secEf.style.display = 'none';
        if (secYp) secYp.style.display = 'none';
        if (secTj) secTj.style.display = 'none';

        document.querySelectorAll('.co-dynamic-panel').forEach(function(p) {
            p.style.display = 'none';
        });

        var nombre = (opt.dataset.nombreMetodo || '').toLowerCase();
        if (nombre.indexOf('efectivo') !== -1 || nombre.indexOf('recibir') !== -1) {
            if (secEf) secEf.style.display = 'block';
        } else if (nombre.indexOf('yape') !== -1 || nombre.indexOf('plin') !== -1 || nombre.indexOf('billetera') !== -1) {
            if (secYp) secYp.style.display = 'block';
        } else if (nombre.indexOf('tarjeta') !== -1 || nombre.indexOf('débito') !== -1 || nombre.indexOf('debito') !== -1 || nombre.indexOf('crédito') !== -1 || nombre.indexOf('credito') !== -1 || nombre.indexOf('card') !== -1) {
            if (secTj) secTj.style.display = 'block';
        }
    }

    document.querySelectorAll('[data-payment-option]').forEach(function(opt) {
        opt.addEventListener('click', function() {
            document.querySelectorAll('[data-payment-option]').forEach(function(item) {
                item.classList.remove('active');
                var r = item.querySelector('input[type="radio"]');
                if (r) r.checked = false;
            });
            this.classList.add('active');
            var r = this.querySelector('input[type="radio"]');
            if (r) r.checked = true;
            cambiarPanelPago(this);
        });
    });

    // Navegación entre pasos
    function goNext1() {
        var tipo = document.getElementById('checkoutTipoEntrega');
        var addrReq = document.getElementById('checkoutAddressRequirement');
        if (tipo && tipo.value === 'delivery') {
            var calle = document.getElementById('checkoutCalle');
            var num = document.getElementById('checkoutNumero');
            var dist = document.getElementById('checkoutDistrito');

            if (!calle || !calle.value.trim() || !num || !num.value.trim() || !dist || !dist.value.trim()) {
                if (addrReq) {
                    addrReq.textContent = 'Por favor completa la direcci\u00f3n de entrega: Calle / Avenida, N\u00famero y Distrito.';
                    addrReq.removeAttribute('hidden');
                }
                if (calle && !calle.value.trim()) calle.focus();
                else if (num && !num.value.trim()) num.focus();
                else if (dist && !dist.value.trim()) dist.focus();
                return;
            }
            if (addrReq) addrReq.setAttribute('hidden', '');
        }

        // Validar horario si es programado
        var hTipo = document.getElementById('checkoutHorarioTipo');
        var hFecha = document.getElementById('checkoutFechaProgramada');
        if (hTipo && hTipo.value === 'programado') {
            if (!hFecha || !hFecha.value) {
                alert('Por favor selecciona la fecha y hora programada.');
                if (hFecha) hFecha.focus();
                return;
            }
        }

        goToStep(2);
    }

    var b1s = document.getElementById('btnNextStep1Sidebar');
    var b2bs = document.getElementById('btnBackStep2Sidebar');
    var b2ns = document.getElementById('btnNextStep2Sidebar');
    var b3bs = document.getElementById('btnBackStep3Sidebar');

    function goNext2() {
        const metodoOption = document.querySelector('[data-payment-option].active');
        const nombreMetodo = (metodoOption && metodoOption.dataset.nombreMetodo) ? metodoOption.dataset.nombreMetodo.toLowerCase() : '';

        function avisar(msg) {
            if (typeof window.mostrarToast === 'function') {
                window.mostrarToast(msg, 'error');
            } else {
                alert(msg);
            }
        }

        // Efectivo
        if (nombreMetodo.includes('efectivo') || nombreMetodo.includes('recibir')) {
            const totalTexto = document.getElementById('checkoutTotal2').textContent.replace('S/', '').trim();
            const total = parseFloat(totalTexto) || 0;
            const montoEf = document.getElementById('monto_efectivo');
            const val = parseFloat(montoEf ? montoEf.value : 0);
            if (isNaN(val) || val < total) {
                avisar('El monto ingresado es menor al total a pagar.');
                if (montoEf) montoEf.focus();
                return;
            }
        }
        
        // Yape/Plin
        if (nombreMetodo.includes('yape') || nombreMetodo.includes('plin')) {
            const celYP = document.getElementById('celular_yape_plin');
            const codYP = document.getElementById('codigo_yape_plin');
            const celularLimpio = celYP ? celYP.value.replace(/\D/g, '') : '';
            if (!celYP || celularLimpio.length < 9 || !celularLimpio.startsWith('9')) {
                avisar('Ingresa un número de celular válido (9 dígitos). Debe empezar con 9.');
                if (celYP) celYP.focus();
                return;
            }
            if (!codYP || codYP.value.replace(/\D/g, '').length < 6) {
                avisar('Ingresa el código de aprobación de 6 dígitos.');
                if (codYP) codYP.focus();
                return;
            }
        }

        // Tarjeta
        if (nombreMetodo.includes('tarjeta') || nombreMetodo.includes('débito') || nombreMetodo.includes('crédito')) {
            const ccNum = document.getElementById('cc_numero');
            const ccExp = document.getElementById('cc_vencimiento');
            const ccCvv = document.getElementById('cc_cvv');
            const ccName = document.getElementById('cc_nombre');
            
            if (!ccNum || ccNum.value.replace(/\D/g, '').length < 14) {
                avisar('Ingresa un número de tarjeta válido.');
                if (ccNum) ccNum.focus();
                return;
            }
            if (!ccExp || ccExp.value.replace(/\D/g, '').length < 4) {
                avisar('Ingresa una fecha de vencimiento válida (MM/AA).');
                if (ccExp) ccExp.focus();
                return;
            }
            if (!ccCvv || ccCvv.value.replace(/\D/g, '').length < 3) {
                avisar('Ingresa un código de seguridad (CVV) válido.');
                if (ccCvv) ccCvv.focus();
                return;
            }
            if (!ccName || ccName.value.trim().length < 3) {
                avisar('Ingresa el nombre del titular de la tarjeta.');
                if (ccName) ccName.focus();
                return;
            }
        }

        goToStep(3);
    }

    if (b1s) b1s.addEventListener('click', goNext1);
    if (b2bs) b2bs.addEventListener('click', function() { goToStep(1); });
    if (b2ns) b2ns.addEventListener('click', goNext2);
    if (b3bs) b3bs.addEventListener('click', function() { goToStep(2); });
});
</script>

<div id="checkoutToastContainer" class="co-toast-container" aria-live="polite"></div>
<script src="Scripts/checkout.js?v=<?= time(); ?>"></script>

</body>
</html>
