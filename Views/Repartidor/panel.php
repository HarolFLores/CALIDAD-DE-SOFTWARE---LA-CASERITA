<?php
$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

if (!function_exists('repartidorTextoCliente')) {
    function repartidorTextoCliente($texto, $notasEntrega = '') {
        $texto = (string)$texto;
        if (trim((string)$notasEntrega) !== '') {
            $motivos = [];
            foreach (preg_split('/\r?\n/', (string)$notasEntrega) ?: [] as $linea) {
                $linea = trim($linea);
                if ($linea === '' || preg_match('/^(Horario|Programado)\s*:/iu', $linea)) {
                    continue;
                }
                if (preg_match('/^\[(?:Tarjeta|Pago|Cancelaci[oó]n|Rechazo comprobante|Incidencia)[^\]]*\]$/iu', $linea)) {
                    continue;
                }
                if (!in_array($linea, $motivos, true)) {
                    $motivos[] = $linea;
                }
            }
            foreach ($motivos as $motivo) {
                $texto = str_replace($motivo, ' ', $texto);
            }
        }
        $limpio = preg_replace('/\[(?:Tarjeta|Pago|Cancelaci[oó]n|Rechazo comprobante|Incidencia)[^\]]*\]/iu', ' ', $texto);
        $partes = preg_split('/\||\r?\n/', (string)$limpio);
        $vistos = [];
        $resultado = [];
        foreach ($partes as $parte) {
            $valor = trim($parte);
            if ($valor === '' || preg_match('/^(Horario|Programado)\s*:/iu', $valor)) {
                continue;
            }
            $clave = mb_strtolower($valor, 'UTF-8');
            if (isset($vistos[$clave])) {
                continue;
            }
            $vistos[$clave] = true;
            $resultado[] = $valor;
        }
        return trim(implode(' ', $resultado));
    }
}

if (!function_exists('repartidorIncidencia')) {
    function repartidorIncidencia($envio) {
        $notas = (string)($envio['notas_entrega'] ?? '');
        $observacion = (string)($envio['observacion'] ?? '');
        $texto = trim($notas . ' ' . $observacion);
        if (preg_match('/\[Cancelaci[oó]n:\s*([^\]]+)\]/iu', $texto, $coincidencia)) {
            return [
                'tipo' => 'Cancelado por caja/admin',
                'detalle' => trim($coincidencia[1]),
            ];
        }
        $estado = mb_strtolower(trim((string)($envio['estado_delivery'] ?? '')), 'UTF-8');
        if ($estado === 'fallido') {
            $detalle = repartidorTextoCliente($notas);
            return [
                'tipo' => 'Incidencia de entrega',
                'detalle' => $detalle !== '' ? $detalle : 'Sin motivo registrado.',
            ];
        }
        return null;
    }
}

$enviosAsignados = array_values(array_filter($envios, function ($envio) {
    $est = strtolower(trim($envio['estado_delivery'] ?? ''));
    return !in_array($est, ['entregado', 'fallido', 'cancelado'], true);
}));

$historialEnvios = array_values(array_filter($envios, function ($envio) {
    $est = strtolower(trim($envio['estado_delivery'] ?? ''));
    return in_array($est, ['entregado', 'fallido', 'cancelado'], true);
}));

$entregasCompletadas = count(array_filter($historialEnvios, function ($envio) {
    $est = strtolower(trim($envio['estado_delivery'] ?? ''));
    return $est === 'entregado';
}));

$efectivoPorCobrar = 0;
foreach ($enviosAsignados as $env) {
    $mp = mb_strtolower(trim($env['metodo_pago'] ?? ''));
    if (strpos($mp, 'efectivo') !== false || strpos($mp, 'contraentrega') !== false || strpos($mp, 'contra entrega') !== false) {
        $efectivoPorCobrar += (float)($env['pedido_total'] ?? 0);
    }
}

$activeTab = $_GET['tab'] ?? 'assigned';
if (!in_array($activeTab, ['assigned', 'available', 'history', 'detail'], true)) {
    $activeTab = 'assigned';
}
$topbarTitles = [
    'assigned' => 'Pedidos asignados',
    'available' => 'Tomar pedidos',
    'history' => 'Historial de entregas',
    'detail' => 'Detalle del pedido',
];
$detailId = filter_input(INPUT_GET, 'id_envio', FILTER_VALIDATE_INT) ?: 0;
$detailOriginParam = $_GET['origen'] ?? 'assigned';
$detailOrigin = in_array($detailOriginParam, ['assigned', 'available', 'history'], true) ? $detailOriginParam : 'assigned';
$detalleEnvio = null;
if ($activeTab === 'detail' && $detailId) {
    foreach (array_merge($enviosAsignados, $enviosDisponibles, $historialEnvios) as $envioDetalle) {
        if ((int)($envioDetalle['id_envio'] ?? 0) === $detailId) {
            $detalleEnvio = $envioDetalle;
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="Images/logo_backup.png">
    <title>Reparto - La Caserita</title>
    <link rel="stylesheet" href="Styles/global.css">
    <link rel="stylesheet" href="Styles/staff-panel.css?v=<?= time() ?>">
    <link rel="stylesheet" href="Styles/Vistas/Repartidor/panel.css?v=<?= time() ?>">
    <script src="Scripts/Vistas/Repartidor/filtros_historial.js?v=<?= time() ?>" defer></script>
    <script src="Scripts/Vistas/Repartidor/panel.js?v=<?= time() ?>" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="staff-app repartidor-panel">
    <?php 
    $paginaPanelStaff = 'entregas'; 
    $cantAsignados = count($enviosAsignados);
    $cantDisponibles = count($enviosDisponibles);
    $cantHistorial = count($historialEnvios);
    include __DIR__ . '/../Staff/partials/staff_sidebar.php'; 
    ?>
    <div class="staff-main">
        <header class="staff-topbar">
            <div class="staff-topbar-left">
                <button type="button" class="staff-menu-toggle hide-on-desktop" id="staffMenuToggle" aria-label="Abrir menú">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
                </button>
                <span class="staff-topbar-title"><?= htmlspecialchars($topbarTitles[$activeTab]) ?></span>
            </div>
            <div class="staff-topbar-actions">
                <a href="index.php?action=home_usuario" class="topbar-store-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                    Volver a Tienda
                </a>
            </div>
        </header>

        <main class="staff-content">
            <?php if ($flashSuccess): ?><div class="delivery-flash success"><?= htmlspecialchars($flashSuccess) ?></div><?php endif; ?>
            <?php if ($flashError): ?><div class="delivery-flash error"><?= htmlspecialchars($flashError) ?></div><?php endif; ?>

            <?php if ($activeTab === 'dashboard'): ?>
                <!-- ================= DASHBOARD DEL REPARTIDOR ================= -->
                <section class="repartidor-metrics-grid">
                    <article class="metric-card">
                        <span class="metric-title">ENTREGAS ACTIVAS</span>
                        <strong class="metric-value"><?= count($enviosAsignados) ?></strong>
                        <span class="metric-sub">En camino o pendientes</span>
                    </article>
                    <article class="metric-card">
                        <span class="metric-title">DISPONIBLES EN TIENDA</span>
                        <strong class="metric-value"><?= count($enviosDisponibles) ?></strong>
                        <span class="metric-sub">Listos para ser tomados</span>
                    </article>
                    <article class="metric-card">
                        <span class="metric-title">ENTREGAS COMPLETADAS</span>
                        <strong class="metric-value"><?= $entregasCompletadas ?></strong>
                        <span class="metric-sub">Historial de éxito</span>
                    </article>
                    <article class="metric-card">
                        <span class="metric-title">EFECTIVO POR RENDIR</span>
                        <strong class="metric-value">S/ <?= number_format($efectivoPorCobrar, 2) ?></strong>
                        <span class="metric-sub">Cobros en efectivo / contraentrega</span>
                    </article>
                </section>

                <section class="delivery-quick-actions">
                    <a href="index.php?action=panel_repartidor&tab=assigned" class="quick-action-btn">
                        <div class="quick-action-icon"><i class="fa-solid fa-truck"></i></div>
                        <div>
                            <strong>Ver Mis Pedidos Asignados</strong>
                            <p><?= count($enviosAsignados) ?> pedidos en tu lista de entrega</p>
                        </div>
                    </a>
                    <a href="index.php?action=panel_repartidor&tab=available" class="quick-action-btn">
                        <div class="quick-action-icon"><i class="fa-solid fa-hand-holding-hand"></i></div>
                        <div>
                            <strong>Tomar Pedidos Disponibles</strong>
                            <p><?= count($enviosDisponibles) ?> pedidos listos en almacén/tienda</p>
                        </div>
                    </a>
                    <a href="index.php?action=panel_repartidor&tab=history" class="quick-action-btn">
                        <div class="quick-action-icon"><i class="fa-solid fa-clock-rotate-left"></i></div>
                        <div>
                            <strong>Historial de Entregas</strong>
                            <p><?= count($historialEnvios) ?> entregas registradas en total</p>
                        </div>
                    </a>
                </section>

                <?php if ($enviosAsignados): ?>
                    <section class="delivery-section-block">
                        <div class="delivery-section-header">
                            <h2>Próximas entregas a realizar</h2>
                            <a href="index.php?action=panel_repartidor&tab=assigned">Ver todas (<?= count($enviosAsignados) ?>) &rarr;</a>
                        </div>
                        <div class="delivery-list">
                            <?php foreach (array_slice($enviosAsignados, 0, 2) as $envio): 
                                $estado = $envio['estado_delivery'] ?: 'Pendiente';
                                $metodoPago = mb_strtolower(trim($envio['metodo_pago'] ?? ''));
                                $cobroEfectivo = strpos($metodoPago, 'efectivo') !== false || strpos($metodoPago, 'contraentrega') !== false || strpos($metodoPago, 'contra entrega') !== false;
                            ?>
                                <article class="delivery-item">
                                    <header class="delivery-item-header">
                                        <div>
                                            <span class="delivery-order-code"><?= htmlspecialchars($envio['codigo_pedido'] ?: ('PED-' . $envio['id_pedido'])) ?></span>
                                            <span class="delivery-status route"><?= htmlspecialchars($estado) ?></span>
                                        </div>
                                        <strong>S/ <?= number_format((float)$envio['pedido_total'], 2) ?></strong>
                                    </header>
                                    <?php if ($cobroEfectivo): ?>
                                        <div class="delivery-cash-due">
                                            <i class="fa-solid fa-money-bill-wave"></i>
                                            <span>EFECTIVO POR COBRAR</span>
                                            <strong>S/ <?= number_format((float)$envio['pedido_total'], 2) ?></strong>
                                        </div>
                                    <?php endif; ?>
                                    <div class="delivery-details">
                                        <div><span>CLIENTE</span><strong><?= htmlspecialchars($envio['nombre_destinatario'] ?: 'Cliente') ?></strong></div>
                                        <div><span>TELÉFONO</span><a href="tel:<?= htmlspecialchars(preg_replace('/[^0-9+]/', '', $envio['celular_contacto'] ?? '')) ?>"><?= htmlspecialchars($envio['celular_contacto'] ?: 'Sin teléfono') ?></a></div>
                                        <div class="delivery-address">
                                            <span>DIRECCIÓN</span>
                                            <strong><?= htmlspecialchars($envio['direccion_destino'] ?: 'Sin dirección') ?></strong>
                                            <?php if (!empty($envio['referencia'])): ?><small><?= htmlspecialchars($envio['referencia']) ?></small><?php endif; ?>
                                        </div>
                                    </div>
                                    <form class="delivery-update-form" data-confirm-action data-confirm-title="<?= $estado === 'En Ruta' ? 'Confirmar entrega' : 'Iniciar ruta' ?>" data-confirm-message="<?= $estado === 'En Ruta' ? ($cobroEfectivo ? '¿Confirmas la entrega y el cobro de S/ ' . number_format((float)$envio['pedido_total'], 2) . ' en efectivo?' : '¿Confirmas que este pedido fue entregado correctamente?') : '¿Confirmas que deseas iniciar la ruta de este pedido?' ?>" method="post" action="index.php?action=actualizar_entrega_repartidor">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                        <input type="hidden" name="id_envio" value="<?= (int)$envio['id_envio'] ?>">
                                        <input id="delivery-notes-<?= (int)$envio['id_envio'] ?>" type="text" name="notas" maxlength="240" placeholder="Referencia o incidencia">
                                        <?php if ($cobroEfectivo && $estado === 'En Ruta'): ?>
                                            <label class="delivery-cash-check">
                                                <input type="checkbox" name="cobrado" value="1">
                                                <span>Confirmo que cobré S/ <?= number_format((float)$envio['pedido_total'], 2) ?> en efectivo</span>
                                            </label>
                                        <?php endif; ?>
                                        <div class="delivery-actions">
                                            <?php if ($estado !== 'En Ruta'): ?><button class="delivery-action secondary" name="nuevo_estado" value="En Ruta" type="submit">Iniciar ruta</button><?php endif; ?>
                                            <?php if ($estado === 'En Ruta'): ?><button class="delivery-action primary" name="nuevo_estado" value="Entregado" type="submit">Marcar entregado</button><?php endif; ?>
                                            <button class="delivery-action failed-action js-delivery-failed" type="button" data-id-envio="<?= (int)$envio['id_envio'] ?>" data-pedido="<?= htmlspecialchars($envio['codigo_pedido'] ?: ('PED-' . $envio['id_pedido']), ENT_QUOTES, 'UTF-8') ?>">No entregado</button>
                                        </div>
                                    </form>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>

            <?php elseif ($activeTab === 'assigned'): ?>
                <!-- ================= PEDIDOS ASIGNADOS ================= -->
                <?php if (!$enviosAsignados): ?>
                    <section class="delivery-empty">
                        <i class="fa-solid fa-route"></i>
                        <h2>No tienes entregas asignadas actualmente</h2>
                        <p>Revisa los pedidos disponibles para tomar nuevos despachos.</p>
                        <a href="index.php?action=panel_repartidor&tab=available" class="delivery-empty-link">Tomar pedidos disponibles</a>
                    </section>
                <?php else: ?>
                    <div class="delivery-card-filters" data-filter-scope="assigned">
                        <div class="delivery-filter-search-row"><label><span class="visually-hidden">Buscar pedidos asignados</span><i class="fa-solid fa-magnifying-glass"></i><input type="search" data-filter-search placeholder="Buscar pedido, cliente o dirección"></label><button type="button" class="delivery-filter-toggle" data-filter-toggle aria-expanded="false" aria-label="Mostrar filtros"><i class="fa-solid fa-sliders"></i></button></div>
                        <div class="delivery-filter-panel" data-filter-panel hidden><select data-filter-payment aria-label="Filtrar por método de pago"><option value="">Todos los pagos</option><option value="cash">Efectivo</option><option value="digital">Yape o tarjeta</option></select><select data-filter-status aria-label="Filtrar por estado"><option value="">Todos los estados</option><option value="pendiente">Pendiente</option><option value="en ruta">En ruta</option></select><button type="button" data-filter-clear>Limpiar filtros</button></div>
                    </div>
                    <section class="delivery-card-section" aria-label="Entregas asignadas">
                        <div class="delivery-table-summary delivery-results-count"><span><b data-filter-count><?= count($enviosAsignados) ?></b> de <?= count($enviosAsignados) ?> pedidos asignados</span></div>
                        <div class="delivery-card-grid">
                        <?php foreach ($enviosAsignados as $envio):
                            $estado = $envio['estado_delivery'] ?: 'Pendiente';
                            $estadoNormalizado = mb_strtolower($estado, 'UTF-8');
                            $estadoClase = $estadoNormalizado === 'en ruta' ? 'is-route' : (in_array($estadoNormalizado, ['fallido', 'cancelado'], true) ? 'is-failed' : ($estadoNormalizado === 'entregado' ? 'is-done' : ''));
                            $metodoPago = mb_strtolower(trim($envio['metodo_pago'] ?? ''));
                            $cobroEfectivo = strpos($metodoPago, 'efectivo') !== false || strpos($metodoPago, 'contraentrega') !== false || strpos($metodoPago, 'contra entrega') !== false;
                            $notaCliente = repartidorTextoCliente($envio['observacion'] ?? '', $envio['notas_entrega'] ?? '');
                            $incidencia = repartidorIncidencia($envio);
                        ?>
                            <article class="delivery-mobile-card delivery-card-filter-item" data-detail-url="index.php?action=panel_repartidor&amp;tab=detail&amp;origen=assigned&amp;id_envio=<?= (int)$envio['id_envio'] ?>" tabindex="0" role="link"
                                data-search="<?= htmlspecialchars(mb_strtolower(($envio['codigo_pedido'] ?? '') . ' ' . ($envio['nombre_destinatario'] ?? '') . ' ' . ($envio['celular_contacto'] ?? '') . ' ' . ($envio['direccion_destino'] ?? '') . ' ' . ($envio['referencia'] ?? '') . ' ' . ($envio['observacion'] ?? ''), 'UTF-8'), ENT_QUOTES, 'UTF-8') ?>"
                                data-payment="<?= $cobroEfectivo ? 'cash' : 'digital' ?>" data-status="<?= htmlspecialchars(strtolower($estado), ENT_QUOTES, 'UTF-8') ?>"
                                data-pedido="<?= htmlspecialchars($envio['codigo_pedido'] ?: ('PED-' . $envio['id_pedido']), ENT_QUOTES, 'UTF-8') ?>"
                                data-cliente="<?= htmlspecialchars($envio['nombre_destinatario'] ?: 'Cliente', ENT_QUOTES, 'UTF-8') ?>"
                                data-telefono="<?= htmlspecialchars($envio['celular_contacto'] ?: 'Sin teléfono', ENT_QUOTES, 'UTF-8') ?>"
                                data-destino="<?= htmlspecialchars($envio['direccion_destino'] ?: 'Sin dirección registrada', ENT_QUOTES, 'UTF-8') ?>"
                                data-referencia="<?= htmlspecialchars($envio['referencia'] ?: 'Sin referencia', ENT_QUOTES, 'UTF-8') ?>"
                                data-descripcion="<?= htmlspecialchars($notaCliente ?: 'Sin descripción del cliente', ENT_QUOTES, 'UTF-8') ?>"
                                data-total="S/ <?= number_format((float)$envio['pedido_total'], 2) ?>"
                                data-metodo="<?= htmlspecialchars($envio['metodo_pago'] ?: 'No especificado', ENT_QUOTES, 'UTF-8') ?>"
                                data-cobrar="<?= $cobroEfectivo ? 'S/ ' . number_format((float)$envio['pedido_total'], 2) : 'No cobra' ?>">
                                <header class="delivery-card-head">
                                    <div class="delivery-card-id"><span class="delivery-card-eyebrow">Pedido</span><strong><?= htmlspecialchars($envio['codigo_pedido'] ?: ('PED-' . $envio['id_pedido'])) ?></strong></div>
                                    <div class="delivery-card-head-meta"><span class="delivery-state-dot <?= $estadoClase ?>"><?= htmlspecialchars($estado) ?></span><span class="delivery-card-total">S/ <?= number_format((float)$envio['pedido_total'], 2) ?></span></div>
                                </header>
                                <div class="delivery-card-body">
                                    <div class="delivery-card-person">
                                        <div class="delivery-card-person-data"><strong><?= htmlspecialchars($envio['nombre_destinatario'] ?: 'Cliente') ?></strong><a href="tel:<?= htmlspecialchars(preg_replace('/[^0-9+]/', '', $envio['celular_contacto'] ?? '')) ?>"><?= htmlspecialchars($envio['celular_contacto'] ?: 'Sin teléfono') ?></a></div>
                                    </div>
                                    <div class="delivery-card-line">
                                        <div><span>Dirección de entrega</span><strong><?= htmlspecialchars($envio['direccion_destino'] ?: 'Sin dirección registrada') ?></strong><?php if (!empty($envio['referencia'])): ?><small>Ref. <?= htmlspecialchars($envio['referencia']) ?></small><?php endif; ?></div>
                                    </div>
                                    <div class="delivery-card-payment">
                                        <span><?= htmlspecialchars($envio['metodo_pago'] ?: 'No especificado') ?></span>
                                        <strong class="<?= $cobroEfectivo ? 'is-cash' : 'is-paid' ?>"><?= $cobroEfectivo ? 'Cobrar S/ ' . number_format((float)$envio['pedido_total'], 2) : 'No cobra' ?></strong>
                                    </div>
                                    <?php if ($notaCliente !== ''): ?>
                                        <div class="delivery-card-note"><span>Descripción del cliente</span><p><?= htmlspecialchars($notaCliente) ?></p></div>
                                    <?php endif; ?>
                                    <?php if ($incidencia): ?>
                                        <div class="delivery-card-incident"><span><?= htmlspecialchars($incidencia['tipo']) ?></span><p><?= htmlspecialchars($incidencia['detalle']) ?></p></div>
                                    <?php endif; ?>
                                </div>
                                <div class="delivery-card-footer">
                                    <span class="delivery-card-hint">Ver detalle</span>
                                    <div class="delivery-row-actions" data-card-action>
                                        <?php if ($estado !== 'En Ruta'): ?>
                                            <form data-confirm-action data-confirm-title="Iniciar ruta" data-confirm-message="¿Confirmas que deseas iniciar la ruta de este pedido?" method="post" action="index.php?action=actualizar_entrega_repartidor"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>"><input type="hidden" name="id_envio" value="<?= (int)$envio['id_envio'] ?>"><button class="delivery-action secondary" name="nuevo_estado" value="En Ruta" type="submit">Iniciar ruta</button></form>
                                        <?php else: ?>
                                            <form data-confirm-action data-confirm-title="Confirmar entrega" data-confirm-message="<?= $cobroEfectivo ? '¿Confirmas la entrega y el cobro de S/ ' . number_format((float)$envio['pedido_total'], 2) . ' en efectivo?' : '¿Confirmas que este pedido fue entregado correctamente?' ?>" method="post" action="index.php?action=actualizar_entrega_repartidor"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>"><input type="hidden" name="id_envio" value="<?= (int)$envio['id_envio'] ?>"><?php if ($cobroEfectivo): ?><input type="hidden" name="cobrado" value="1"><?php endif; ?><button class="delivery-action primary" name="nuevo_estado" value="Entregado" type="submit">Entregado</button></form>
                                        <?php endif; ?>
                                        <button class="delivery-action failed-action js-delivery-failed" type="button" data-id-envio="<?= (int)$envio['id_envio'] ?>" data-pedido="<?= htmlspecialchars($envio['codigo_pedido'] ?: ('PED-' . $envio['id_pedido']), ENT_QUOTES, 'UTF-8') ?>">No entregado</button>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>

            <?php elseif ($activeTab === 'available'): ?>
                <!-- ================= TOMAR PEDIDOS DISPONIBLES ================= -->
                <?php if (!$enviosDisponibles): ?>
                    <section class="delivery-empty"><i class="fa-solid fa-inbox"></i><h2>No hay pedidos pendientes en tienda para tomar en este momento.</h2></section>
                <?php else: ?>
                    <div class="delivery-card-filters" data-filter-scope="available">
                        <div class="delivery-filter-search-row"><label><span class="visually-hidden">Buscar pedidos disponibles</span><i class="fa-solid fa-magnifying-glass"></i><input type="search" data-filter-search placeholder="Buscar pedido, cliente o dirección"></label><button type="button" class="delivery-filter-toggle" data-filter-toggle aria-expanded="false" aria-label="Mostrar filtros"><i class="fa-solid fa-sliders"></i></button></div>
                        <div class="delivery-filter-panel" data-filter-panel hidden><select data-filter-payment aria-label="Filtrar por método de pago"><option value="">Todos los pagos</option><option value="cash">Efectivo</option><option value="digital">Yape o tarjeta</option></select><button type="button" data-filter-clear>Limpiar filtros</button></div>
                    </div>
                    <section class="delivery-card-section" aria-label="Pedidos disponibles">
                        <div class="delivery-table-summary delivery-results-count"><span><b data-filter-count><?= count($enviosDisponibles) ?></b> de <?= count($enviosDisponibles) ?> pedidos disponibles</span></div>
                        <div class="delivery-card-grid">
                        <?php foreach ($enviosDisponibles as $envio):
                            $metodoDisponible = mb_strtolower(trim($envio['metodo_pago'] ?? ''));
                            $esEfectivoDisponible = strpos($metodoDisponible, 'efectivo') !== false || strpos($metodoDisponible, 'contraentrega') !== false || strpos($metodoDisponible, 'contra entrega') !== false;
                            $notaClienteDisponible = repartidorTextoCliente($envio['observacion'] ?? '', $envio['notas_entrega'] ?? '');
                        ?>
                            <article class="delivery-mobile-card delivery-card-filter-item" data-detail-url="index.php?action=panel_repartidor&amp;tab=detail&amp;origen=available&amp;id_envio=<?= (int)$envio['id_envio'] ?>" tabindex="0" role="link"
                                data-search="<?= htmlspecialchars(mb_strtolower(($envio['codigo_pedido'] ?? '') . ' ' . ($envio['nombre_destinatario'] ?? '') . ' ' . ($envio['celular_contacto'] ?? '') . ' ' . ($envio['direccion_destino'] ?? '') . ' ' . ($envio['referencia'] ?? '') . ' ' . ($envio['observacion'] ?? ''), 'UTF-8'), ENT_QUOTES, 'UTF-8') ?>"
                                data-payment="<?= $esEfectivoDisponible ? 'cash' : 'digital' ?>"
                                data-pedido="<?= htmlspecialchars($envio['codigo_pedido'] ?: ('PED-' . $envio['id_pedido']), ENT_QUOTES, 'UTF-8') ?>"
                                data-cliente="<?= htmlspecialchars($envio['nombre_destinatario'] ?: 'Cliente', ENT_QUOTES, 'UTF-8') ?>"
                                data-telefono="<?= htmlspecialchars($envio['celular_contacto'] ?: 'Sin teléfono', ENT_QUOTES, 'UTF-8') ?>"
                                data-destino="<?= htmlspecialchars($envio['direccion_destino'] ?: 'Recojo en tienda', ENT_QUOTES, 'UTF-8') ?>"
                                data-referencia="<?= htmlspecialchars($envio['referencia'] ?: 'Sin referencia', ENT_QUOTES, 'UTF-8') ?>"
                                data-descripcion="<?= htmlspecialchars($notaClienteDisponible ?: 'Sin descripción del cliente', ENT_QUOTES, 'UTF-8') ?>"
                                data-total="S/ <?= number_format((float)$envio['pedido_total'], 2) ?>"
                                data-metodo="<?= htmlspecialchars($envio['metodo_pago'] ?: 'No especificado', ENT_QUOTES, 'UTF-8') ?>"
                                data-cobrar="<?= $esEfectivoDisponible ? 'S/ ' . number_format((float)$envio['pedido_total'], 2) : 'No cobra' ?>">
                                <header class="delivery-card-head">
                                    <div class="delivery-card-id"><span class="delivery-card-eyebrow">Pedido</span><strong><?= htmlspecialchars($envio['codigo_pedido'] ?: ('PED-' . $envio['id_pedido'])) ?></strong></div>
                                    <div class="delivery-card-head-meta"><span class="delivery-state-dot is-available">Disponible</span><span class="delivery-card-total">S/ <?= number_format((float)$envio['pedido_total'], 2) ?></span></div>
                                </header>
                                <div class="delivery-card-body">
                                    <div class="delivery-card-person">
                                        <div class="delivery-card-person-data"><strong><?= htmlspecialchars($envio['nombre_destinatario'] ?: 'Cliente') ?></strong><a href="tel:<?= htmlspecialchars(preg_replace('/[^0-9+]/', '', $envio['celular_contacto'] ?? '')) ?>"><?= htmlspecialchars($envio['celular_contacto'] ?: 'Sin teléfono') ?></a></div>
                                    </div>
                                    <div class="delivery-card-line">
                                        <div><span>Dirección de entrega</span><strong><?= htmlspecialchars($envio['direccion_destino'] ?: 'Recojo en tienda') ?></strong><?php if (!empty($envio['referencia'])): ?><small>Ref. <?= htmlspecialchars($envio['referencia']) ?></small><?php endif; ?></div>
                                    </div>
                                    <div class="delivery-card-payment">
                                        <span><?= htmlspecialchars($envio['metodo_pago'] ?: 'No especificado') ?></span>
                                        <strong class="<?= $esEfectivoDisponible ? 'is-cash' : 'is-paid' ?>"><?= $esEfectivoDisponible ? 'Cobrar S/ ' . number_format((float)$envio['pedido_total'], 2) : 'No cobra' ?></strong>
                                    </div>
                                    <?php if ($notaClienteDisponible !== ''): ?>
                                        <div class="delivery-card-note"><span>Descripción del cliente</span><p><?= htmlspecialchars($notaClienteDisponible) ?></p></div>
                                    <?php endif; ?>
                                </div>
                                <div class="delivery-card-footer">
                                    <span class="delivery-card-hint">Ver detalle</span>
                                    <form data-card-action data-confirm-action data-confirm-title="Tomar pedido" data-confirm-message="¿Confirmas que deseas asignarte este pedido?" method="post" action="index.php?action=tomar_entrega_repartidor"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>"><input type="hidden" name="id_envio" value="<?= (int)$envio['id_envio'] ?>"><button class="delivery-action primary" type="submit">Tomar pedido</button></form>
                                </div>
                            </article>
                        <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>

            <?php elseif ($activeTab === 'detail'): ?>
                <?php if (!$detalleEnvio): ?>
                    <section class="delivery-empty"><i class="fa-solid fa-circle-exclamation"></i><h2>Pedido no disponible</h2><p>Este pedido ya no está disponible o no pertenece a tu cuenta.</p><a class="delivery-empty-link" href="index.php?action=panel_repartidor&amp;tab=<?= htmlspecialchars($detailOrigin) ?>">Volver</a></section>
                <?php else:
                    $detalleEstado = $detalleEnvio['estado_delivery'] ?: 'Pendiente';
                    $detalleEstadoMostrar = $detailOrigin === 'available' ? 'Disponible' : $detalleEstado;
                    $detalleEstadoNormalizado = mb_strtolower($detalleEstado, 'UTF-8');
                    $detalleEstadoClase = $detalleEstadoNormalizado === 'en ruta' ? 'is-route' : ($detalleEstadoNormalizado === 'entregado' ? 'is-done' : (in_array($detalleEstadoNormalizado, ['fallido', 'cancelado'], true) ? 'is-failed' : ($detailOrigin === 'available' ? 'is-available' : '')));
                    $detalleMetodo = mb_strtolower(trim($detalleEnvio['metodo_pago'] ?? ''));
                    $detalleCobra = strpos($detalleMetodo, 'efectivo') !== false || strpos($detalleMetodo, 'contraentrega') !== false || strpos($detalleMetodo, 'contra entrega') !== false;
                    $detalleNotaCliente = repartidorTextoCliente($detalleEnvio['observacion'] ?? '', $detalleEnvio['notas_entrega'] ?? '');
                    $detalleIncidencia = repartidorIncidencia($detalleEnvio);
                ?>
                    <a class="delivery-detail-back" href="index.php?action=panel_repartidor&amp;tab=<?= htmlspecialchars($detailOrigin) ?>">&larr; Volver a <?= $detailOrigin === 'available' ? 'tomar pedidos' : ($detailOrigin === 'history' ? 'historial' : 'pedidos asignados') ?></a>
                    <section class="delivery-detail-page">
                        <header><div><span>Pedido</span><h1><?= htmlspecialchars($detalleEnvio['codigo_pedido'] ?: ('PED-' . $detalleEnvio['id_pedido'])) ?></h1></div><span class="delivery-state-dot <?= $detalleEstadoClase ?>"><?= htmlspecialchars($detalleEstadoMostrar) ?></span></header>
                        <div class="delivery-detail-page-grid">
                            <div><span>Cliente</span><strong><?= htmlspecialchars($detalleEnvio['nombre_destinatario'] ?: 'Cliente') ?></strong></div>
                            <div><span>Teléfono</span><a href="tel:<?= htmlspecialchars(preg_replace('/[^0-9+]/', '', $detalleEnvio['celular_contacto'] ?? '')) ?>"><?= htmlspecialchars($detalleEnvio['celular_contacto'] ?: 'Sin teléfono') ?></a></div>
                            <div class="wide"><span>Dirección</span><strong><?= htmlspecialchars($detalleEnvio['direccion_destino'] ?: 'Sin dirección registrada') ?></strong></div>
                            <div class="wide"><span>Referencia</span><p><?= htmlspecialchars($detalleEnvio['referencia'] ?: 'Sin referencia') ?></p></div>
                            <div class="wide delivery-detail-note"><span>Descripción del cliente</span><p><?= $detalleNotaCliente !== '' ? nl2br(htmlspecialchars($detalleNotaCliente)) : 'Sin descripción del cliente' ?></p></div>
                            <?php if ($detalleIncidencia): ?>
                                <div class="wide delivery-detail-incident"><span><?= htmlspecialchars($detalleIncidencia['tipo']) ?></span><p><?= nl2br(htmlspecialchars($detalleIncidencia['detalle'])) ?></p></div>
                            <?php endif; ?>
                            <div><span>Total</span><strong>S/ <?= number_format((float)$detalleEnvio['pedido_total'], 2) ?></strong></div>
                            <div><span>Método de pago</span><strong><?= htmlspecialchars($detalleEnvio['metodo_pago'] ?: 'No especificado') ?></strong></div>
                            <div><span>Cobrar</span><strong><?= $detalleCobra ? 'S/ ' . number_format((float)$detalleEnvio['pedido_total'], 2) : 'No cobra' ?></strong></div>
                            <div><span>Horario</span><strong><?= htmlspecialchars($detalleEnvio['horario_entrega'] ?: 'Lo antes posible') ?></strong></div>
                        </div>
                        <?php if ($detailOrigin !== 'history' || ($detalleIncidencia && $detalleIncidencia['tipo'] === 'Incidencia de entrega')): ?>
                        <div class="delivery-detail-page-actions">
                            <?php if ($detailOrigin === 'history'): ?>
                                <button class="delivery-action neutral js-edit-incident" type="button" data-id-envio="<?= (int)$detalleEnvio['id_envio'] ?>" data-pedido="<?= htmlspecialchars($detalleEnvio['codigo_pedido'] ?: ('PED-' . $detalleEnvio['id_pedido']), ENT_QUOTES, 'UTF-8') ?>" data-notas="<?= htmlspecialchars($detalleIncidencia['detalle'] === 'Sin motivo registrado.' ? '' : $detalleIncidencia['detalle'], ENT_QUOTES, 'UTF-8') ?>">Editar incidencia</button>
                            <?php elseif ($detailOrigin === 'available'): ?>
                                <form data-confirm-action data-confirm-title="Tomar pedido" data-confirm-message="¿Confirmas que deseas asignarte este pedido?" method="post" action="index.php?action=tomar_entrega_repartidor"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>"><input type="hidden" name="id_envio" value="<?= (int)$detalleEnvio['id_envio'] ?>"><button class="delivery-action primary" type="submit">Tomar pedido</button></form>
                            <?php else: ?>
                                <?php if ($detalleEstado !== 'En Ruta'): ?><form data-confirm-action data-confirm-title="Iniciar ruta" data-confirm-message="¿Confirmas que deseas iniciar la ruta de este pedido?" method="post" action="index.php?action=actualizar_entrega_repartidor"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>"><input type="hidden" name="id_envio" value="<?= (int)$detalleEnvio['id_envio'] ?>"><button class="delivery-action secondary" name="nuevo_estado" value="En Ruta" type="submit">Iniciar ruta</button></form><?php else: ?><form data-confirm-action data-confirm-title="Confirmar entrega" data-confirm-message="<?= $detalleCobra ? '¿Confirmas la entrega y el cobro de S/ ' . number_format((float)$detalleEnvio['pedido_total'], 2) . ' en efectivo?' : '¿Confirmas que este pedido fue entregado correctamente?' ?>" method="post" action="index.php?action=actualizar_entrega_repartidor"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>"><input type="hidden" name="id_envio" value="<?= (int)$detalleEnvio['id_envio'] ?>"><?php if ($detalleCobra): ?><input type="hidden" name="cobrado" value="1"><?php endif; ?><button class="delivery-action primary" name="nuevo_estado" value="Entregado" type="submit">Marcar entregado</button></form><?php endif; ?>
                                <button class="delivery-action failed-action js-delivery-failed" type="button" data-id-envio="<?= (int)$detalleEnvio['id_envio'] ?>" data-pedido="<?= htmlspecialchars($detalleEnvio['codigo_pedido'] ?: ('PED-' . $detalleEnvio['id_pedido']), ENT_QUOTES, 'UTF-8') ?>">No entregado</button>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </section>
                <?php endif; ?>

            <?php elseif ($activeTab === 'history'): ?>
                <!-- ================= HISTORIAL DE ENTREGAS ================= -->
                <?php if (!$historialEnvios): ?>
                    <section class="delivery-empty"><i class="fa-solid fa-clock-rotate-left"></i><h2>Sin entregas en el historial.</h2><p>Cuando finalices o concluyas un despacho aparecerá aquí registrado.</p></section>
                <?php else: ?>
                    <div class="delivery-history-filters" role="search" aria-label="Filtrar historial de entregas">
                        <div class="delivery-filter-search-row"><label class="delivery-history-search"><span class="visually-hidden">Buscar en el historial</span><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i><input type="search" id="deliveryHistorySearch" placeholder="Buscar pedido, cliente o dirección"></label><button type="button" class="delivery-filter-toggle" data-filter-toggle aria-expanded="false" aria-label="Mostrar filtros"><i class="fa-solid fa-sliders"></i></button></div>
                        <div class="delivery-filter-panel delivery-history-filter-panel" data-filter-panel hidden><label><span class="visually-hidden">Filtrar por resultado</span><select id="deliveryHistoryStatus"><option value="">Todos los resultados</option><option value="entregado">Entregados</option><option value="incidencia">Con incidencia</option></select></label><label><span class="visually-hidden">Desde fecha</span><input type="date" id="deliveryHistoryFrom" aria-label="Desde fecha"></label><label><span class="visually-hidden">Hasta fecha</span><input type="date" id="deliveryHistoryTo" aria-label="Hasta fecha"></label><button type="button" id="deliveryHistoryClear" class="delivery-history-clear">Limpiar filtros</button></div>
                        <span id="deliveryHistoryCount" class="delivery-history-filter-count delivery-history-count-row" aria-live="polite"></span>
                    </div>
                    <section class="delivery-card-section delivery-history-cards" id="deliveryHistoryList" aria-label="Historial de entregas">
                        <div class="delivery-card-grid">
                        <?php foreach ($historialEnvios as $envio): 
                            $estado = $envio['estado_delivery'] ?: ($envio['pedido_estado'] ?? 'Entregado');
                            $esExito = strtolower($estado) === 'entregado';
                            $notaHistorial = repartidorTextoCliente($envio['observacion'] ?? '', $envio['notas_entrega'] ?? '');
                            $incidenciaHistorial = repartidorIncidencia($envio);
                            $fechaTexto = !empty($envio['pedido_fecha']) ? date('d/m/Y H:i', strtotime($envio['pedido_fecha'])) : 'Sin fecha';
                            $textoBusqueda = strtolower(trim(($envio['codigo_pedido'] ?? '') . ' ' . ($envio['nombre_destinatario'] ?? '') . ' ' . ($envio['celular_contacto'] ?? '') . ' ' . ($envio['direccion_destino'] ?? '') . ' ' . ($envio['referencia'] ?? '') . ' ' . ($envio['notas_entrega'] ?? '') . ' ' . ($envio['observacion'] ?? '') . ' ' . $estado));
                            $fechaFiltro = !empty($envio['pedido_fecha']) ? date('Y-m-d', strtotime($envio['pedido_fecha'])) : '';
                        ?>
                            <article class="delivery-mobile-card history-item" data-detail-url="index.php?action=panel_repartidor&amp;tab=detail&amp;origen=history&amp;id_envio=<?= (int)$envio['id_envio'] ?>" tabindex="0" role="link" data-search="<?= htmlspecialchars($textoBusqueda, ENT_QUOTES, 'UTF-8') ?>" data-status="<?= $esExito ? 'entregado' : 'incidencia' ?>" data-date="<?= htmlspecialchars($fechaFiltro, ENT_QUOTES, 'UTF-8') ?>">
                                <header class="delivery-card-head">
                                    <div class="delivery-card-id"><span class="delivery-card-eyebrow">Pedido</span><strong><?= htmlspecialchars($envio['codigo_pedido'] ?: ('PED-' . $envio['id_pedido'])) ?></strong></div>
                                    <div class="delivery-card-head-meta"><span class="delivery-state-dot <?= $esExito ? 'is-done' : 'is-failed' ?>"><?= htmlspecialchars($estado) ?></span><span class="delivery-card-total">S/ <?= number_format((float)$envio['pedido_total'], 2) ?></span></div>
                                </header>
                                <div class="delivery-card-body">
                                    <div class="delivery-card-person">
                                        <div class="delivery-card-person-data"><strong><?= htmlspecialchars($envio['nombre_destinatario'] ?: 'Cliente') ?></strong><span><?= htmlspecialchars($envio['celular_contacto'] ?: 'Sin teléfono') ?></span></div>
                                    </div>
                                    <div class="delivery-card-line">
                                        <div><span>Dirección de entrega</span><strong><?= htmlspecialchars($envio['direccion_destino'] ?: 'Sin dirección registrada') ?></strong><?php if (!empty($envio['referencia'])): ?><small>Ref. <?= htmlspecialchars($envio['referencia']) ?></small><?php endif; ?></div>
                                    </div>
                                    <div class="delivery-card-payment">
                                        <span><?= htmlspecialchars($envio['metodo_pago'] ?: 'No especificado') ?></span>
                                        <strong class="is-date"><?= htmlspecialchars($fechaTexto) ?></strong>
                                    </div>
                                    <?php if ($notaHistorial !== ''): ?>
                                        <div class="delivery-card-note"><span>Descripción del cliente</span><p><?= htmlspecialchars($notaHistorial) ?></p></div>
                                    <?php endif; ?>
                                    <?php if ($incidenciaHistorial): ?>
                                        <div class="delivery-card-incident"><span><?= htmlspecialchars($incidenciaHistorial['tipo']) ?></span><p><?= htmlspecialchars($incidenciaHistorial['detalle']) ?></p></div>
                                    <?php endif; ?>
                                </div>
                                <div class="delivery-card-footer">
                                    <span class="delivery-card-hint">Ver detalle</span>
                                    <?php if (!$esExito && $incidenciaHistorial && $incidenciaHistorial['tipo'] === 'Incidencia de entrega'): ?>
                                        <button type="button" class="delivery-incident-edit js-edit-incident" data-card-action data-id-envio="<?= (int)$envio['id_envio'] ?>" data-pedido="<?= htmlspecialchars($envio['codigo_pedido'] ?: ('PED-' . $envio['id_pedido']), ENT_QUOTES, 'UTF-8') ?>" data-notas="<?= htmlspecialchars($incidenciaHistorial['detalle'] === 'Sin motivo registrado.' ? '' : $incidenciaHistorial['detalle'], ENT_QUOTES, 'UTF-8') ?>">Editar incidencia</button>
                                    <?php endif; ?>
                                </div>
                            </article>
                        <?php endforeach; ?>
                        </div>
                    </section>
                    <div class="pg-footer" id="deliveryHistoryPag"></div>
                    <p id="deliveryHistoryNoResults" class="delivery-history-no-results" hidden>No hay entregas que coincidan con estos filtros.</p>
                <?php endif; ?>
            <?php endif; ?>
        </main>
    </div>

    <div class="delivery-modal" id="deliveryConfirmModal" hidden role="dialog" aria-modal="true" aria-labelledby="deliveryConfirmTitle">
        <div class="delivery-modal-card delivery-confirm-card">
            <div class="delivery-confirm-icon"><i class="fa-solid fa-circle-question"></i></div>
            <h2 id="deliveryConfirmTitle">Confirmar acción</h2>
            <p id="deliveryConfirmMessage"></p>
            <div class="delivery-modal-actions"><button type="button" class="delivery-action neutral" data-close-confirm>Cancelar</button><button type="button" class="delivery-action primary" id="deliveryConfirmSubmit">Confirmar</button></div>
        </div>
    </div>

    <div class="delivery-modal" id="deliveryFailedModal" hidden role="dialog" aria-modal="true" aria-labelledby="deliveryFailedTitle">
        <div class="delivery-modal-card">
            <h2 id="deliveryFailedTitle">Registrar entrega no realizada</h2>
            <p id="deliveryFailedOrder"></p>
            <form method="post" action="index.php?action=actualizar_entrega_repartidor" id="deliveryFailedForm">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <input type="hidden" name="id_envio" id="deliveryFailedId">
                <input type="hidden" name="nuevo_estado" value="Fallido">
                <label for="deliveryFailedNotes">Motivo o detalle <span aria-hidden="true">*</span></label>
                <textarea name="notas" id="deliveryFailedNotes" maxlength="240" required placeholder="Indica por qué no se pudo entregar"></textarea>
                <div class="delivery-modal-actions"><button type="button" class="delivery-action neutral" data-close-failed>Cancelar</button><button type="submit" class="delivery-action primary">Confirmar</button></div>
            </form>
        </div>
    </div>

    <div class="delivery-modal" id="deliveryIncidentModal" hidden role="dialog" aria-modal="true" aria-labelledby="deliveryIncidentTitle">
        <div class="delivery-modal-card">
            <h2 id="deliveryIncidentTitle">Editar incidencia de entrega</h2>
            <p id="deliveryIncidentOrder"></p>
            <form method="post" action="index.php?action=editar_incidencia_repartidor" id="deliveryIncidentForm">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <input type="hidden" name="id_envio" id="deliveryIncidentId">
                <label for="deliveryIncidentNotes">Motivo o detalle <span aria-hidden="true">*</span></label>
                <textarea name="notas" id="deliveryIncidentNotes" maxlength="240" required placeholder="Describe la incidencia de la entrega"></textarea>
                <div class="delivery-modal-actions"><button type="button" class="delivery-action neutral" data-close-incident>Cancelar</button><button type="submit" class="delivery-action primary">Guardar cambios</button></div>
            </form>
        </div>
    </div>

    <script src="Scripts/app.js"></script>
</body>
</html>
