<?php
require_once 'Core/ajaxHelper.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$nombre = htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Administrador');
$flash_success = $_SESSION['flash_success'] ?? null;
$flash_error = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

$envios = $envios ?? [];
$repartidores = $repartidores ?? [];
$filtroActual = $_GET['estado'] ?? 'todos';

// Cálculo de métricas
$totalEnvios = count($envios);
$deliveryCount = 0;
$sinRepartidorCount = 0;
$recojoCount = 0;
$pendientesCount = 0;
$enPrepCount = 0;
$enRutaCount = 0;
$entregadosCount = 0;
$totalPorCobrar = 0.00;

foreach ($envios as $env) {
    $est = $env['estado_delivery'] ?? 'Pendiente';
    $isRec = ($env['tipo_entrega_norm'] ?? '') === 'recojo';
    
    if ($isRec) {
        $recojoCount++;
    } else {
        $deliveryCount++;
        $sinRep = (empty($env['id_repartidor']) || $env['id_repartidor'] == 0 || empty($env['repartidor_nombre']));
        if ($sinRep && $est !== 'Entregado' && $est !== 'Fallido' && $est !== 'Cancelado') {
            $sinRepartidorCount++;
        }
    }

    if ($est === 'Pendiente') {
        $pendientesCount++;
    } elseif ($est === 'En Preparacion' || $est === 'En Preparación') {
        $enPrepCount++;
    } elseif ($est === 'En Ruta' || $est === 'Asignado') {
        $enRutaCount++;
    } elseif ($est === 'Entregado') {
        $entregadosCount++;
    }

    if (empty($env['cobrado_por_repartidor']) && $est !== 'Fallido' && $est !== 'Cancelado') {
        $totalPorCobrar += (float)($env['pedido_total'] ?? 0);
    }
}
?>
<?php if (!AjaxHelper::isAjax()): ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logística & Despacho Delivery — ERP La Caserita</title>
    <link rel="icon" type="image/png" href="Images/logo_backup.png">
    <link rel="stylesheet" href="Styles/global.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="Styles/styles-home.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="Styles/styles-users-admin.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="Styles/admin-spa.css?v=<?= time(); ?>">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="Scripts/admin-spa.js?v=<?= time(); ?>" defer></script>
<link rel="stylesheet" href="Styles/Vistas/Admin/delivery_admin.css?v=<?= filemtime(__DIR__ . '/../../Styles/Vistas/Admin/delivery_admin.css'); ?>">
<link rel="stylesheet" href="Styles/Vistas/Admin/bandeja_pedidos.css?v=<?= filemtime(__DIR__ . '/../../Styles/Vistas/Admin/bandeja_pedidos.css'); ?>">
</head>
<body>
    <div class="admin-layout">
        <?php include __DIR__ . '/partials/admin_sidebar.php'; ?>

        <div class="admin-main">
            <header class="admin-topbar">
                <div class="admin-topbar-left">
                    <button type="button" class="admin-hamburger-btn hide-on-desktop" id="openAdminSidebar" aria-label="Abrir Menú">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
                    </button>
                    <div class="admin-topbar-brand">
                        <span class="brand-title">Logística y despacho</span>
                    </div>
                </div>
                <div class="admin-topbar-actions">
                    <?php include __DIR__ . '/partials/admin_topbar_actions.php'; ?>
                </div>
            </header>

            <div id="admin-container">
<?php endif; ?>

<!-- NOTIFICACIÓN TOAST SUPERIOR (IMAGEN 4) -->
<div id="deliveryTopToast" class="top-toast <?= ($flash_success || $flash_error) ? 'show' : ''; ?>">
    <div class="top-toast-icon" style="background: <?= $flash_error ? '#dc2626' : '#15803d'; ?>;">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
            <?= $flash_error ? '<line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line>' : '<polyline points="20 6 9 17 4 12"></polyline>'; ?>
        </svg>
    </div>
    <div class="top-toast-body">
        <strong><?= $flash_error ? 'Error' : 'Éxito' ?></strong>
        <span><?= htmlspecialchars($flash_error ?: ($flash_success ?: 'Operación completada con éxito.')); ?></span>
    </div>
    <button type="button" class="top-toast-close" id="deliveryToastClose" aria-label="Cerrar">&times;</button>
    <div class="top-toast-progress" style="background: <?= $flash_error ? '#dc2626' : '#15803d'; ?>;"></div>
</div>

<!-- Modal Confirmación Reutilizable (Imagen 3 / Ventas) -->
<div class="deliv-modal-overlay" id="delivConfirmModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.60); z-index: 999999; backdrop-filter: blur(3px); align-items: center; justify-content: center; padding: 16px;">
    <div style="background: #ffffff; border-radius: 18px; width: min(440px, 94vw); padding: 28px 24px; text-align: center; box-shadow: 0 24px 60px rgba(0,0,0,0.25); margin: auto; position: relative;">
        <button type="button" class="btn-close-universal" onclick="window.cerrarDelivConfirmacion(true)" aria-label="Cerrar modal" style="position: absolute; top: 14px; right: 14px;">&times;</button>
        <div id="delivConfirmIconBox" style="width: 56px; height: 56px; border-radius: 50%; background: #dcfce7; color: #15803d; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 16px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
        </div>
        <h3 id="delivConfirmModalTitulo" style="margin: 0 0 8px; font-size: 1.2rem; font-weight: 800; color: #0f172a;">¿Confirmar acción?</h3>
        <p id="delivConfirmModalMsg" style="margin: 0 0 22px; font-size: 0.90rem; color: #64748b; line-height: 1.5;">
            Mensaje de confirmación.
        </p>
        <div style="display: flex; gap: 12px; justify-content: center;">
            <button type="button" id="btnDelivConfirmarProceder" style="padding: 10px 22px; border-radius: 8px; border: none; background: #15803d; color: #ffffff; font-weight: 700; font-size: 0.88rem; cursor: pointer; box-shadow: 0 2px 8px rgba(21,128,61,0.25);">
                Sí, Confirmar
            </button>
        </div>
    </div>
</div>

<main class="main-content container-admin admin-fullwidth" style="padding-top: 25px; padding-bottom: 50px;">

    <!-- Selector de vista: Bandeja de Pedidos (HU33) / Despachos y Entregas -->
    <div class="dlv-view-switch" id="dlvViewSwitch">
        <button type="button" class="dlv-view-btn active" data-view="bandeja" onclick="window.dlvSetView('bandeja')">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16v12H4z"></path><path d="M4 12h4l2 3h4l2-3h4"></path></svg>
            Bandeja de Pedidos
        </button>
        <button type="button" class="dlv-view-btn" data-view="despachos" onclick="window.dlvSetView('despachos')">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="7" cy="17" r="2"></circle><circle cx="17" cy="17" r="2"></circle><path d="M5 17h-2v-4l2-5h9l4 5h1v4h-2"></path></svg>
            Despachos y Entregas
        </button>
    </div>

    <!-- VISTA 1: BANDEJA DE PEDIDOS PENDIENTES (HU33) -->
    <div id="dlvViewBandeja">
        <?php include __DIR__ . '/partials/bandeja_pedidos_section.php'; ?>
    </div>

    <!-- VISTA 2: DESPACHOS Y ENTREGAS -->
    <div id="dlvViewDespachos" class="is-hidden">

    <!-- TARJETAS CUADRADAS COMPACTAS (IMAGEN 2) -->
    <div class="deliv-stats-grid">
        <div class="deliv-stat-card <?= $filtroActual === 'todos' ? 'active-card' : ''; ?>" id="cardDelivTodos" onclick="window.filtrarEstadoDeliv('todos')">
            <span class="deliv-stat-title">TOTAL PEDIDOS</span>
            <span class="deliv-stat-number" id="lblTotalEnvios"><?= $totalEnvios; ?></span>
        </div>
        <div class="deliv-stat-card <?= $filtroActual === 'delivery' ? 'active-card' : ''; ?>" id="cardDelivTipoDelivery" onclick="window.filtrarEstadoDeliv('delivery')">
            <span class="deliv-stat-title">DELIVERY</span>
            <span class="deliv-stat-number" id="lblDeliveryCount"><?= $deliveryCount; ?></span>
        </div>
        <div class="deliv-stat-card <?= $filtroActual === 'sin_repartidor' ? 'active-card' : ''; ?>" id="cardDelivSinRepartidor" onclick="window.filtrarEstadoDeliv('sin_repartidor')">
            <span class="deliv-stat-title">SIN REPARTIDOR</span>
            <span class="deliv-stat-number" id="lblSinRepartidorCount"><?= $sinRepartidorCount; ?></span>
        </div>
        <div class="deliv-stat-card <?= $filtroActual === 'Pendiente' ? 'active-card' : ''; ?>" id="cardDelivPend" onclick="window.filtrarEstadoDeliv('Pendiente')">
            <span class="deliv-stat-title">PENDIENTES</span>
            <span class="deliv-stat-number" id="lblPendientesCount"><?= $pendientesCount; ?></span>
        </div>
        <div class="deliv-stat-card <?= ($filtroActual === 'En Preparacion' || $filtroActual === 'En Preparación') ? 'active-card' : ''; ?>" id="cardDelivPrep" onclick="window.filtrarEstadoDeliv('En Preparacion')">
            <span class="deliv-stat-title">EN PREPARACIÓN</span>
            <span class="deliv-stat-number" id="lblEnPrepCount"><?= $enPrepCount; ?></span>
        </div>
        <div class="deliv-stat-card <?= ($filtroActual === 'Asignado' || $filtroActual === 'En Ruta') ? 'active-card' : ''; ?>" id="cardDelivRuta" onclick="window.filtrarEstadoDeliv('En Ruta')">
            <span class="deliv-stat-title">EN RUTA / ASIGNADOS</span>
            <span class="deliv-stat-number" id="lblEnRutaCount"><?= $enRutaCount; ?></span>
        </div>
        <div class="deliv-stat-card <?= $filtroActual === 'recojo' ? 'active-card' : ''; ?>" id="cardDelivRecojo" onclick="window.filtrarEstadoDeliv('recojo')">
            <span class="deliv-stat-title">RECOJO EN TIENDA</span>
            <span class="deliv-stat-number" id="lblRecojoCount"><?= $recojoCount; ?></span>
        </div>
        <div class="deliv-stat-card <?= $filtroActual === 'Entregado' ? 'active-card' : ''; ?>" id="cardDelivEntr" onclick="window.filtrarEstadoDeliv('Entregado')">
            <span class="deliv-stat-title">ENTREGADOS</span>
            <span class="deliv-stat-number" id="lblEntregadosCount"><?= $entregadosCount; ?></span>
        </div>
        <div class="deliv-stat-card <?= $filtroActual === 'por_cobrar' ? 'active-card' : ''; ?>" id="cardDelivCobrar" onclick="window.filtrarEstadoDeliv('por_cobrar')">
            <span class="deliv-stat-title">TOTAL POR COBRAR</span>
            <span class="deliv-stat-number" id="lblTotalPorCobrar" style="font-size: 1.15rem;"><?= 'S/ ' . number_format($totalPorCobrar, 2); ?></span>
        </div>
    </div>

    <!-- PANEL PRINCIPAL CON TABLA -->
    <div class="deliv-card-panel">
        <div class="deliv-card-header">
            <div class="deliv-card-title">Despachos y Entregas (Delivery & Recojo en Tienda)</div>
            <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                <select id="filtroTipoSelect" class="deliv-search-input" style="min-width: 170px; padding: 9px 12px; cursor: pointer;" onchange="window.filtrarDeliveryEnVivo()">
                    <option value="todos">Todos los Tipos</option>
                    <option value="delivery">Solo Delivery</option>
                    <option value="recojo">Solo Recojo en Tienda</option>
                </select>
                <input type="text" id="busquedaDelivInput" class="deliv-search-input" placeholder="Buscar por pedido, cliente o dirección..." oninput="window.filtrarDeliveryEnVivo()">
                <button type="button" class="btn-limpiar-filtros" onclick="window.limpiarFiltrosDeliv()" title="Restablecer filtros">
                    Limpiar
                </button>
            </div>
        </div>

        <div style="overflow-x: auto;">
            <table class="deliv-table">
                <thead>
                    <tr>
                        <th>PEDIDO</th>
                        <th>DESTINATARIO / CELULAR</th>
                        <th>DIRECCIÓN DE ENTREGA</th>
                        <th style="text-align: right;">TOTAL A COBRAR</th>
                        <th>REPARTIDOR ASIGNADO</th>
                        <th style="text-align: center;">ESTADO DESPACHO</th>
                        <th style="width: 48px; text-align: center;"></th>
                    </tr>
                </thead>
                <tbody id="tbodyDeliv">
                    <!-- Filas renderizadas por JS con paginación de 5 en 5 -->
                </tbody>
            </table>
        </div>

        <!-- Paginación estilo Imagen 3 (< 1 2 3 ... >) -->
        <div class="deliv-pagination-bar" id="paginacionDelivBar"></div>
    </div>

    </div><!-- /#dlvViewDespachos -->
</main>

<!-- Modal Asignar Repartidor -->
<div id="modalAsignar" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 99999; justify-content: center; align-items: center; padding: 20px;">
    <div style="background: #ffffff; border-radius: 16px; width: 100%; max-width: 440px; padding: 24px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.15);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
            <h3 style="margin: 0; font-size: 1.2rem; font-weight: 800; color: #0f172a;">Asignar Repartidor al Envío</h3>
            <button type="button" class="btn-close-universal" onclick="window.cerrarModalAsignar()" aria-label="Cerrar modal">&times;</button>
        </div>
        <p style="margin: 0 0 16px; font-size: 0.85rem; color: #64748b;">Seleccione el conductor o motorizado responsable del despacho.</p>
        <form id="formAsignarRepartidor" action="index.php?action=asignar_repartidor" method="POST" onsubmit="window.enviarAsignarAjax(event)">
            <input type="hidden" name="id_envio" id="idEnvioAsignar">
            <div style="margin-bottom: 18px;">
                <label style="display: block; font-size: 0.82rem; font-weight: 700; color: #334155; margin-bottom: 6px; text-transform: uppercase;">Repartidores Disponibles</label>
                <select name="id_repartidor" id="selectRepartidor" required style="width: 100%; padding: 10px 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 0.90rem; box-sizing: border-box; outline: none; background: #fff;">
                    <option value="">-- Seleccionar Repartidor --</option>
                    <?php foreach ($repartidores as $rep): ?>
                        <option value="<?= $rep['id_usuario']; ?>" data-nombre="<?= htmlspecialchars($rep['nombre']); ?>" data-apellido="<?= htmlspecialchars($rep['apellido'] ?? ''); ?>" data-telefono="<?= htmlspecialchars($rep['telefono'] ?? ''); ?>">
                            <?= htmlspecialchars($rep['nombre'] . ' ' . $rep['apellido']); ?> (<?= htmlspecialchars($rep['telefono'] ?? 'Sin tel.'); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="submit" id="btnSubmitAsignar" style="padding: 9px 20px; background: #15803d; color: #ffffff; border: none; border-radius: 8px; font-weight: 800; cursor: pointer;">Guardar Asignación</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Actualizar Estado Delivery & Recojo -->
<div id="modalEstado" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 99999; justify-content: center; align-items: center; padding: 20px;">
    <div style="background: #ffffff; border-radius: 16px; width: 100%; max-width: 460px; padding: 24px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.15);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
            <h3 id="delivModalEstadoTitulo" style="margin: 0; font-size: 1.2rem; font-weight: 800; color: #0f172a;">Actualizar Estado</h3>
            <button type="button" class="btn-close-universal" onclick="window.cerrarModalEstado()" aria-label="Cerrar modal">&times;</button>
        </div>
        <p id="delivModalEstadoSubtitulo" style="margin: 0 0 16px; font-size: 0.85rem; color: #64748b;">Cambie el estado del pedido y registre cobro en caso aplique.</p>
        <form id="formActualizarEstado" action="index.php?action=actualizar_estado_delivery" method="POST" onsubmit="window.enviarEstadoAjax(event)">
            <input type="hidden" name="id_envio" id="idEnvioEstado">
            <input type="hidden" name="id_pedido" id="idPedidoEstado">
            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 0.82rem; font-weight: 700; color: #334155; margin-bottom: 6px; text-transform: uppercase;">Nuevo Estado</label>
                <select name="nuevo_estado" id="selectNuevoEstado" required style="width: 100%; padding: 10px 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 0.90rem; box-sizing: border-box; outline: none; background: #fff;">
                    <option value="Pendiente">Pendiente</option>
                    <option value="En Preparacion">En Preparación</option>
                    <option value="Asignado">Asignado</option>
                    <option value="En Ruta">En Ruta</option>
                    <option value="Listo para Recojo">Listo para Recojo</option>
                    <option value="Entregado">Entregado</option>
                    <option value="Fallido">Fallido / No entregado</option>
                </select>
            </div>

            <div style="margin-bottom: 14px;" id="grpCobrado">
                <label style="display: inline-flex; align-items: center; gap: 8px; font-size: 0.88rem; font-weight: 700; color: #15803d; cursor: pointer;">
                    <input type="checkbox" name="cobrado" value="1" id="chkCobrado" style="accent-color: #15803d; width: 18px; height: 18px;">
                    <span id="lblCobradoTexto">¿Dinero cobrado / pagado?</span>
                </label>
            </div>

            <div style="margin-bottom: 18px;">
                <label style="display: block; font-size: 0.82rem; font-weight: 700; color: #334155; margin-bottom: 6px; text-transform: uppercase;">Notas o Incidencias</label>
                <textarea name="notas" id="txtNotasEstado" rows="2" placeholder="Ej: Entregado a cliente / Cobro presencial completado" style="width: 100%; padding: 10px 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 0.90rem; box-sizing: border-box; outline: none;"></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="submit" id="btnSubmitEstado" style="padding: 9px 20px; background: #15803d; color: #ffffff; border: none; border-radius: 8px; font-weight: 800; cursor: pointer;">Guardar Estado</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Boleta / Comprobante del Pedido (Logística y Despacho) -->
<div id="modalDetalleDeliv" class="deliv-modal-overlay" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.60); z-index: 99999; backdrop-filter: blur(3px); align-items: center; justify-content: center; padding: 16px;">
    <div style="background: #ffffff; border-radius: 18px; width: min(440px, 95vw); max-height: 94vh; display: flex; flex-direction: column; box-shadow: 0 28px 80px rgba(15, 23, 42, 0.28); overflow: hidden; margin: auto;">
        <div class="deliv-ticket-no-print" style="padding: 14px 18px; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between;">
            <h3 style="margin: 0; font-size: 1.05rem; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#15803d" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
                Comprobante de Venta
            </h3>
            <button type="button" class="btn-close-universal" onclick="window.cerrarModalDetalleDeliv()" aria-label="Cerrar modal">&times;</button>
        </div>

        <div style="padding: 12px; background: #f8fafc; overflow-y: auto; flex: 1;">
            <div id="delivTicketImprimible" style="background: #ffffff; padding: 18px 16px; font-family: 'Courier New', Courier, monospace; font-size: 11.5px; color: #000; line-height: 1.35; border: 1px solid #e2e8f0; border-radius: 8px; width: 100%; max-width: 330px; margin: 0 auto; box-shadow: 0 2px 10px rgba(0,0,0,0.06); box-sizing: border-box;">
                <div style="text-align: center; margin-bottom: 8px;">
                    <strong style="font-size: 15px; display: block; font-weight: 900; letter-spacing: 0.5px;">TIENDA "LA CASERITA"</strong>
                    <div style="font-size: 11px; font-weight: 700; margin-top: 2px;">R.U.C. 20512345678</div>
                    <div style="font-size: 10px; color: #333; margin-top: 1px;">Av. Las Flores 123 - San Juan, Lima</div>
                    <div style="font-size: 10px; color: #333;">Teléfono: (01) 456-7890</div>
                    <div style="margin: 6px 0; border-bottom: 1px dashed #000;"></div>
                    <div style="font-weight: 800; font-size: 12.5px;">BOLETA DE VENTA ELECTRÓNICA</div>
                    <div style="font-weight: 900; font-size: 13.5px; margin-top: 1px;" id="lblDelivTicketCodigo">B001-000000</div>
                    <div style="font-size: 10px; color: #555;" id="lblDelivTicketNumInt">PED-00000</div>
                </div>

                <div style="margin-bottom: 8px; font-size: 10.5px; line-height: 1.4;">
                    <div><strong>Fecha:</strong> <span id="lblDelivTicketFecha">--</span></div>
                    <div><strong>Cliente:</strong> <span id="lblDelivTicketCliente">CLIENTE GENERAL</span></div>
                    <div id="rowDelivTicketCorreo" style="display: none; word-break: break-all;"><strong>Correo:</strong> <span id="lblDelivTicketCorreo">--</span></div>
                    <div><strong>Entrega:</strong> <span id="lblDelivTicketEntrega">Recojo en tienda</span></div>
                    <div id="rowDelivTicketDireccion" style="display: none; word-break: break-word;"><strong>Dirección:</strong> <span id="lblDelivTicketDireccion">--</span></div>
                    <div><strong>Forma Pago:</strong> <span id="lblDelivTicketMetodo">EFECTIVO</span></div>
                    <div><strong>Estado:</strong> <span id="lblDelivTicketEstado">PENDIENTE</span></div>
                </div>

                <div style="border-bottom: 1px dashed #000; margin: 6px 0;"></div>

                <table style="width: 100%; border-collapse: collapse; font-size: 10.5px;">
                    <thead>
                        <tr style="border-bottom: 1px solid #000; text-align: left;">
                            <th style="padding-bottom: 3px; width: 34px;">CANT</th>
                            <th style="padding-bottom: 3px;">DESCRIPCIÓN</th>
                            <th style="padding-bottom: 3px; text-align: right; width: 50px;">P.U</th>
                            <th style="padding-bottom: 3px; text-align: right; width: 55px;">TOTAL</th>
                        </tr>
                    </thead>
                    <tbody id="delivTicketTbodyItems"></tbody>
                </table>

                <div style="border-bottom: 1px dashed #000; margin: 8px 0;"></div>

                <div style="font-size: 11px; text-align: right; line-height: 1.45;">
                    <div>SUBTOTAL: <strong id="lblDelivTicketSubtotal">S/ 0.00</strong></div>
                    <div>I.G.V. (18%): <strong id="lblDelivTicketIgv">S/ 0.00</strong></div>
                    <div style="font-size: 13.5px; margin-top: 4px; font-weight: 900; border-top: 1px dashed #000; padding-top: 3px;">TOTAL A PAGAR: <span id="lblDelivTicketTotal">S/ 0.00</span></div>
                </div>

                <div id="rowDelivTicketEfectivo" style="display: none; font-size: 10.5px; margin-top: 6px; border-top: 1px dashed #000; padding-top: 4px; text-align: right;">
                    <div>RECIBIDO: <span id="lblDelivTicketRecibido">S/ 0.00</span></div>
                    <div>VUELTO: <span id="lblDelivTicketVuelto">S/ 0.00</span></div>
                </div>

                <div id="rowDelivTicketObs" style="display: none; font-size: 10px; margin-top: 6px; border-top: 1px dashed #000; padding-top: 4px; color: #475569;">
                    <strong>Observación:</strong> <span id="lblDelivTicketObs">--</span>
                </div>

                <div style="border-bottom: 1px dashed #000; margin: 10px 0;"></div>

                <div style="text-align: center; font-size: 10px; line-height: 1.35;">
                    <p style="margin: 2px 0; font-weight: 700;">¡GRACIAS POR SU PREFERENCIA!</p>
                    <p style="margin: 2px 0; font-size: 9px; color: #444;">Representación impresa de la Boleta de Venta Electrónica.</p>
                    <p style="margin: 2px 0; font-size: 8.5px; color: #666;">Conserve este comprobante para cualquier reclamo.</p>
                    <div style="margin-top: 7px; letter-spacing: 2px; font-size: 16px; font-family: monospace;">||| | ||||| || |||| |||||</div>
                    <div style="font-size: 8.5px; color: #777; margin-top: 2px;">Aut. SUNAT - La Caserita</div>
                </div>
            </div>
        </div>

        <div class="deliv-ticket-no-print" style="padding: 12px 18px; border-top: 1px solid #f1f5f9; background: #fafbfc; display: flex; flex-direction: column; gap: 8px;">
            <div style="display: flex; gap: 10px;">
                <button type="button" id="btnDelivTicketImprimir" style="flex: 1; padding: 11px; background: #15803d; color: #fff; border: none; border-radius: 8px; font-weight: 800; cursor: pointer; font-size: 0.90rem; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 2px 6px rgba(21,128,61,0.25);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                    IMPRIMIR
                </button>
                <button type="button" id="btnDelivTicketDescargarPdf" style="flex: 1; padding: 11px; background: #15803d; color: #fff; border: none; border-radius: 8px; font-weight: 800; cursor: pointer; font-size: 0.90rem; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 2px 6px rgba(21,128,61,0.25);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
                    DESCARGAR PDF
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    const listaEnvios = <?= json_encode($envios); ?>;
    let filtroEstado = '<?= $filtroActual; ?>';
    let busquedaTexto = '';
    let paginaActual = 1;
    const ITEMS_PER_PAGE = 5;
    let accionDelivConfirmacion = null;
    let accionDelivCancelar = null;

    // Selector de vista: Bandeja de Pedidos (HU33) / Despachos y Entregas
    window.dlvSetView = function(vista) {
        const esBandeja = vista !== 'despachos';
        const bandeja = document.getElementById('dlvViewBandeja');
        const despachos = document.getElementById('dlvViewDespachos');
        if (!bandeja || !despachos) return;
        bandeja.classList.toggle('is-hidden', !esBandeja);
        despachos.classList.toggle('is-hidden', esBandeja);
        document.querySelectorAll('#dlvViewSwitch .dlv-view-btn').forEach(function(btn) {
            btn.classList.toggle('active', btn.getAttribute('data-view') === (esBandeja ? 'bandeja' : 'despachos'));
        });
    };

    // Modal Confirmación (Imagen 3 / Ventas)
    window.abrirDelivConfirmacion = function(titulo, mensaje, accion, esPeligro = false, onCancel = null) {
        const modal = document.getElementById('delivConfirmModal');
        const titEl = document.getElementById('delivConfirmModalTitulo');
        const msgEl = document.getElementById('delivConfirmModalMsg');
        const iconBox = document.getElementById('delivConfirmIconBox');
        const btnProc = document.getElementById('btnDelivConfirmarProceder');

        if (titEl) titEl.textContent = titulo;
        if (msgEl) msgEl.textContent = mensaje;

        if (esPeligro) {
            if (iconBox) {
                iconBox.style.background = '#fee2e2';
                iconBox.style.color = '#dc2626';
                iconBox.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>';
            }
            if (btnProc) {
                btnProc.style.background = '#dc2626';
                btnProc.textContent = 'Sí, Continuar';
            }
        } else {
            if (iconBox) {
                iconBox.style.background = '#dcfce7';
                iconBox.style.color = '#15803d';
                iconBox.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>';
            }
            if (btnProc) {
                btnProc.style.background = '#15803d';
                btnProc.textContent = 'Sí, Confirmar';
            }
        }

        accionDelivConfirmacion = accion;
        accionDelivCancelar = onCancel;
        if (btnProc) {
            btnProc.onclick = function() {
                const fn = accionDelivConfirmacion;
                window.cerrarDelivConfirmacion(false);
                if (typeof fn === 'function') fn();
            };
        }
        if (modal) modal.style.display = 'flex';
    };

    window.cerrarDelivConfirmacion = function(esCancel = false) {
        const modal = document.getElementById('delivConfirmModal');
        if (modal) modal.style.display = 'none';
        if (esCancel && typeof accionDelivCancelar === 'function') {
            accionDelivCancelar();
        }
        accionDelivConfirmacion = null;
        accionDelivCancelar = null;
    };

    // Toast interactivo (Imagen 4)
    window.mostrarDelivToast = function(titulo, mensaje, esError = false) {
        const toast = document.getElementById('deliveryTopToast');
        if (!toast) return;

        const iconEl = toast.querySelector('.top-toast-icon');
        const strongEl = toast.querySelector('.top-toast-body strong');
        const spanEl = toast.querySelector('.top-toast-body span');
        const progEl = toast.querySelector('.top-toast-progress');

        const color = esError ? '#dc2626' : '#15803d';
        if (iconEl) {
            iconEl.style.background = color;
            iconEl.innerHTML = esError ? 
                '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>' :
                '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>';
        }
        if (strongEl) strongEl.textContent = titulo;
        if (spanEl) spanEl.textContent = mensaje;
        if (progEl) progEl.style.background = color;

        toast.classList.add('show');
        if (window._delivToastTimer) clearTimeout(window._delivToastTimer);
        window._delivToastTimer = setTimeout(() => toast.classList.remove('show'), 3800);
    };

    (function initToast() {
        const toast = document.getElementById('deliveryTopToast');
        const closeBtn = document.getElementById('deliveryToastClose');
        if (closeBtn && toast) {
            closeBtn.addEventListener('click', () => toast.classList.remove('show'));
        }
        if (toast && toast.classList.contains('show')) {
            window._delivToastTimer = setTimeout(() => toast.classList.remove('show'), 3800);
        }
    })();

    // Recalcular métricas de las tarjetas en vivo
    window.actualizarContadoresDelivUI = function() {
        let total = listaEnvios.length;
        let deliveryCount = 0;
        let sinRepartidor = 0;
        let recojo = 0;
        let pend = 0;
        let enPrep = 0;
        let enRuta = 0;
        let entr = 0;
        let porCobrar = 0;

        listaEnvios.forEach(e => {
            const est = e.estado_delivery || 'Pendiente';
            const isRec = (e.tipo_entrega_norm === 'recojo');
            
            if (isRec) {
                recojo++;
            } else {
                deliveryCount++;
                const sinRep = !e.id_repartidor || parseInt(e.id_repartidor) === 0 || !e.repartidor_nombre;
                if (sinRep && est !== 'Entregado' && est !== 'Fallido' && est !== 'Cancelado') {
                    sinRepartidor++;
                }
            }

            if (est === 'Pendiente') pend++;
            else if (est === 'En Preparacion' || est === 'En Preparación') enPrep++;
            else if (est === 'En Ruta' || est === 'Asignado') enRuta++;
            else if (est === 'Entregado') entr++;

            if (parseInt(e.cobrado_por_repartidor || 0) === 0 && est !== 'Fallido' && est !== 'Cancelado') {
                porCobrar += parseFloat(e.pedido_total || 0);
            }
        });

        const elTot = document.getElementById('lblTotalEnvios');
        const elDeliv = document.getElementById('lblDeliveryCount');
        const elSinRep = document.getElementById('lblSinRepartidorCount');
        const elPend = document.getElementById('lblPendientesCount');
        const elPrep = document.getElementById('lblEnPrepCount');
        const elRuta = document.getElementById('lblEnRutaCount');
        const elRec = document.getElementById('lblRecojoCount');
        const elEntr = document.getElementById('lblEntregadosCount');
        const elCobrar = document.getElementById('lblTotalPorCobrar');

        if (elTot) elTot.textContent = total;
        if (elDeliv) elDeliv.textContent = deliveryCount;
        if (elSinRep) elSinRep.textContent = sinRepartidor;
        if (elPend) elPend.textContent = pend;
        if (elPrep) elPrep.textContent = enPrep;
        if (elRuta) elRuta.textContent = enRuta;
        if (elRec) elRec.textContent = recojo;
        if (elEntr) elEntr.textContent = entr;
        if (elCobrar) elCobrar.textContent = 'S/ ' + porCobrar.toFixed(2);
    };

    window.filtrarEstadoDeliv = function(est) {
        filtroEstado = est;
        paginaActual = 1;
        document.querySelectorAll('.deliv-stat-card').forEach(c => c.classList.remove('active-card'));
        if (est === 'todos') document.getElementById('cardDelivTodos')?.classList.add('active-card');
        else if (est === 'delivery') document.getElementById('cardDelivTipoDelivery')?.classList.add('active-card');
        else if (est === 'sin_repartidor') document.getElementById('cardDelivSinRepartidor')?.classList.add('active-card');
        else if (est === 'Pendiente') document.getElementById('cardDelivPend')?.classList.add('active-card');
        else if (est === 'En Preparacion' || est === 'En Preparación') document.getElementById('cardDelivPrep')?.classList.add('active-card');
        else if (est === 'En Ruta') document.getElementById('cardDelivRuta')?.classList.add('active-card');
        else if (est === 'recojo') document.getElementById('cardDelivRecojo')?.classList.add('active-card');
        else if (est === 'Entregado') document.getElementById('cardDelivEntr')?.classList.add('active-card');
        else if (est === 'por_cobrar') document.getElementById('cardDelivCobrar')?.classList.add('active-card');

        const selTipo = document.getElementById('filtroTipoSelect');
        if (selTipo) {
            if (est === 'recojo') selTipo.value = 'recojo';
            else if (est === 'delivery' || est === 'sin_repartidor') selTipo.value = 'delivery';
            else selTipo.value = 'todos';
        }

        renderizarTabla();
    };

    window.filtrarDeliveryEnVivo = function() {
        busquedaTexto = (document.getElementById('busquedaDelivInput')?.value || '').toLowerCase().trim();
        paginaActual = 1;
        renderizarTabla();
    };

    window.limpiarFiltrosDeliv = function() {
        const selTipo = document.getElementById('filtroTipoSelect');
        if (selTipo) selTipo.value = 'todos';
        const inpBusq = document.getElementById('busquedaDelivInput');
        if (inpBusq) inpBusq.value = '';
        busquedaTexto = '';
        window.filtrarEstadoDeliv('todos');
    };

    function renderizarTabla() {
        const tbody = document.getElementById('tbodyDeliv');
        const pagContainer = document.getElementById('paginacionDelivBar');
        if (!tbody) return;

        const filtroTipo = document.getElementById('filtroTipoSelect')?.value || 'todos';

        const filtrados = listaEnvios.filter(env => {
            const est = env.estado_delivery || 'Pendiente';
            const isRec = (env.tipo_entrega_norm === 'recojo');
            const sinRep = !isRec && (!env.id_repartidor || parseInt(env.id_repartidor) === 0 || !env.repartidor_nombre);

            if (filtroTipo === 'delivery' && isRec) return false;
            if (filtroTipo === 'recojo' && !isRec) return false;

            if (filtroEstado !== 'todos') {
                if (filtroEstado === 'recojo') {
                    if (!isRec) return false;
                } else if (filtroEstado === 'delivery') {
                    if (isRec) return false;
                } else if (filtroEstado === 'sin_repartidor') {
                    if (isRec || !sinRep || est === 'Entregado' || est === 'Cancelado' || est === 'Fallido') return false;
                } else if (filtroEstado === 'En Ruta') {
                    if (est !== 'En Ruta' && est !== 'Asignado') return false;
                } else if (filtroEstado === 'En Preparacion') {
                    if (est !== 'En Preparacion' && est !== 'En Preparación') return false;
                } else if (filtroEstado === 'por_cobrar') {
                    if (parseInt(env.cobrado_por_repartidor || 0) === 1 || est === 'Fallido' || est === 'Cancelado') return false;
                } else if (est !== filtroEstado) {
                    return false;
                }
            }

            if (busquedaTexto !== '') {
                const ped = (env.codigo_pedido || '#' + env.id_pedido).toLowerCase();
                const cli = (env.nombre_destinatario || '').toLowerCase();
                const dir = (env.direccion_destino || '').toLowerCase();
                const rep = ((env.repartidor_nombre || '') + ' ' + (env.repartidor_apellido || '')).toLowerCase();
                const met = (env.metodo_pago || '').toLowerCase();
                const tipo = isRec ? 'recojo tienda' : 'delivery';
                if (!ped.includes(busquedaTexto) && !cli.includes(busquedaTexto) && !dir.includes(busquedaTexto) && !rep.includes(busquedaTexto) && !met.includes(busquedaTexto) && !tipo.includes(busquedaTexto)) {
                    return false;
                }
            }
            return true;
        });

        const total = filtrados.length;
        const totalPaginas = Math.ceil(total / ITEMS_PER_PAGE) || 1;
        if (paginaActual > totalPaginas) paginaActual = totalPaginas;

        const inicio = (paginaActual - 1) * ITEMS_PER_PAGE;
        const fin = Math.min(inicio + ITEMS_PER_PAGE, total);
        const paginaItems = filtrados.slice(inicio, fin);

        if (paginaItems.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; padding:36px; color:#94a3b8;">No se encontraron pedidos con el filtro aplicado.</td></tr>';
        } else {
            tbody.innerHTML = paginaItems.map(env => {
                const est = env.estado_delivery || 'Pendiente';
                const estClass = est.toLowerCase().replace(/\s+/g, '_');
                const nroPed = env.codigo_pedido || ('#' + String(env.id_pedido).padStart(5, '0'));
                const repAsignado = env.repartidor_nombre ? (env.repartidor_nombre + ' ' + (env.repartidor_apellido || '')) : null;
                const isRec = (env.tipo_entrega_norm === 'recojo');

                return `
                    <tr>
                        <td>
                            <strong style="color:#0f172a; font-weight:800;">${nroPed}</strong>
                            <div style="font-size:0.75rem; color:#64748b;">${env.metodo_pago || 'Pago en entrega'}</div>
                            
                        </td>
                        <td>
                            <div style="font-weight:700; color:#0f172a;">${env.nombre_destinatario || 'Cliente'}</div>
                            <span style="color:#475569; font-weight:600; font-size:0.82rem;">${env.celular_contacto || 'Sin cel.'}</span>
                        </td>
                        <td style="max-width:260px;">
                            ${isRec 
                                ? `<div style="font-size:0.85rem; color:#334155; font-weight:700;">Recojo en Tienda</div><small style="color:#64748b; font-size:0.75rem;">Local Principal - Mostrador</small>`
                                : `<div style="font-size:0.85rem; color:#334155; line-height:1.3;">${env.direccion_destino || '—'}</div>${env.referencia ? `<small style="color:#64748b; font-size:0.75rem;">Ref: ${env.referencia}</small>` : ''}`
                            }
                        </td>
                        <td style="text-align:right;">
                            <span style="font-weight:800; font-size:1.02rem; color:#0f172a;">S/ ${parseFloat(env.pedido_total || 0).toFixed(2)}</span>
                            ${parseInt(env.cobrado_por_repartidor) === 1 ? '<div style="font-size:0.70rem; color:#15803d; font-weight:700;">✓ Cobrado</div>' : ''}
                        </td>
                        <td>
                            ${isRec ? `
                                <span style="color:#64748b; font-size:0.80rem; font-style:italic;">No aplica (Recojo en tienda)</span>
                            ` : (repAsignado ? `
                                <div style="font-weight:700; color:#0f172a; font-size:0.85rem;">${repAsignado}</div>
                                <small style="color:#64748b; font-size:0.75rem;">${env.repartidor_telefono || ''}</small>
                            ` : `
                                <span style="color:#94a3b8; font-style:italic; font-size:0.82rem;">Sin asignar</span>
                            `)}
                        </td>
                        <td style="text-align:center;">
                            <span class="status-pill ${estClass}">
                                ${est}
                            </span>
                        </td>
                        <td style="text-align:right; white-space:nowrap;">
                            <div class="table-kebab-container">
                                <button type="button" class="table-kebab-btn" aria-label="Acciones de delivery" title="Opciones">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="5" r="1.8"/><circle cx="12" cy="12" r="1.8"/><circle cx="12" cy="19" r="1.8"/></svg>
                                </button>
                                <div class="table-kebab-menu">
                                    <button type="button" class="table-kebab-item" onclick="window.abrirDetallePedidoDeliv(${env.id_pedido}, ${env.id_envio})">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                        Ver detalle
                                    </button>
                                    ${!isRec ? `
                                    <button type="button" class="table-kebab-item" onclick="window.abrirModalAsignar(${env.id_envio})">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6"/><path d="M22 11h-6"/></svg>
                                        Asignar repartidor
                                    </button>
                                    ` : ''}
                                    <button type="button" class="table-kebab-item" onclick='window.abrirModalEstado(${JSON.stringify(env)})'>
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                                        Cambiar estado
                                    </button>
                                </div>
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        renderPaginador(pagContainer, totalPaginas, paginaActual);
    }

    function renderPaginador(container, totalPaginas, actual) {
        if (!container) return;
        if (totalPaginas <= 1) {
            container.innerHTML = '';
            return;
        }

        let html = '';
        html += `<button type="button" class="pag-btn" ${actual <= 1 ? 'disabled' : ''} onclick="window.cambiarPagina(${actual - 1})" aria-label="Anterior">&lt;</button>`;

        let pages = [];
        if (totalPaginas <= 5) {
            for (let i = 1; i <= totalPaginas; i++) pages.push(i);
        } else {
            pages.push(1);
            if (actual > 3) pages.push('...');
            let start = Math.max(2, actual - 1);
            let end = Math.min(totalPaginas - 1, actual + 1);
            for (let i = start; i <= end; i++) {
                if (!pages.includes(i)) pages.push(i);
            }
            if (actual < totalPaginas - 2) pages.push('...');
            pages.push(totalPaginas);
        }

        pages.forEach(p => {
            if (p === '...') {
                html += `<span style="padding:0 4px; color:#94a3b8; font-weight:700;">...</span>`;
            } else {
                html += `<button type="button" class="pag-btn ${p === actual ? 'active' : ''}" onclick="window.cambiarPagina(${p})">${p}</button>`;
            }
        });

        html += `<button type="button" class="pag-btn" ${actual >= totalPaginas ? 'disabled' : ''} onclick="window.cambiarPagina(${actual + 1})" aria-label="Siguiente">&gt;</button>`;
        container.innerHTML = html;
    }

    window.cambiarPagina = function(p) {
        paginaActual = p;
        renderizarTabla();
    };

    window.cerrarModalDetalleDeliv = function() {
        const modal = document.getElementById('modalDetalleDeliv');
        if (modal) modal.style.display = 'none';
    };

    function escDelivTicket(s) {
        const el = document.createElement('div');
        el.textContent = String(s == null ? '' : s);
        return el.innerHTML;
    }

    function fechaDelivTicketFmt(f) {
        if (!f) return '--';
        const d = new Date(String(f).replace(' ', 'T'));
        if (isNaN(d.getTime())) return escDelivTicket(f);
        const dd = String(d.getDate()).padStart(2, '0');
        const mm = String(d.getMonth() + 1).padStart(2, '0');
        const yy = d.getFullYear();
        const hh = String(d.getHours()).padStart(2, '0');
        const mi = String(d.getMinutes()).padStart(2, '0');
        return dd + '/' + mm + '/' + yy + ' ' + hh + ':' + mi;
    }

    let ultimoTicketDelivData = null;

    function popularTicketDeliv(data, env) {
        env = env || {};
        ultimoTicketDelivData = data;
        const p = data.pedido || {};
        const prods = data.detalle || [];

        const setTxt = function(id, val) {
            const el = document.getElementById(id);
            if (el) el.textContent = val;
        };

        const numPadded = String(p.id_pedido || 0).padStart(5, '0');
        const codigoDoc = p.codigo_pedido ? escDelivTicket(p.codigo_pedido) : ('B001-' + String(p.id_pedido || 0).padStart(6, '0'));
        const clienteNombre = ((p.usuario_nombre || '') + ' ' + (p.usuario_apellido || '')).trim() || (p.cliente_nombre || env.nombre_destinatario || 'Cliente General');
        const isDelivery = (p.tipo_entrega === 'delivery' || env.tipo_entrega_norm === 'delivery');
        const total = parseFloat(p.total || env.pedido_total || 0);
        const subtotal = parseFloat((total / 1.18).toFixed(2));
        const igv = parseFloat((total - subtotal).toFixed(2));
        const montoRec = parseFloat(p.monto_recibido || 0);
        const vueltoPed = parseFloat(p.vuelto || 0);

        setTxt('lblDelivTicketCodigo', codigoDoc);
        setTxt('lblDelivTicketNumInt', 'N° Pedido: #' + numPadded);
        setTxt('lblDelivTicketFecha', fechaDelivTicketFmt(p.fecha));
        setTxt('lblDelivTicketCliente', clienteNombre.toUpperCase());

        const rowCorreo = document.getElementById('rowDelivTicketCorreo');
        if (rowCorreo) {
            if (p.usuario_correo) {
                rowCorreo.style.display = 'block';
                setTxt('lblDelivTicketCorreo', p.usuario_correo);
            } else {
                rowCorreo.style.display = 'none';
            }
        }

        setTxt('lblDelivTicketEntrega', isDelivery ? 'DELIVERY A DOMICILIO' : 'RECOJO EN TIENDA');

        const rowDirec = document.getElementById('rowDelivTicketDireccion');
        const dirDest = p.direccion_envio || env.direccion_destino || '';
        if (rowDirec) {
            if (isDelivery && dirDest) {
                rowDirec.style.display = 'block';
                setTxt('lblDelivTicketDireccion', dirDest + (env.referencia ? (' (Ref: ' + env.referencia + ')') : ''));
            } else {
                rowDirec.style.display = 'none';
            }
        }

        setTxt('lblDelivTicketMetodo', (p.metodo_pago || env.metodo_pago || 'EFECTIVO').toUpperCase());
        setTxt('lblDelivTicketEstado', (env.estado_delivery || p.estado || 'PENDIENTE').toUpperCase());

        const tbody = document.getElementById('delivTicketTbodyItems');
        if (tbody) {
            let rows = '';
            if (prods.length === 0) {
                rows = '<tr><td colspan="4" style="text-align:center; padding:8px 0; color:#64748b;">(Sin productos registrados)</td></tr>';
            } else {
                prods.forEach(function(item) {
                    rows += '<tr>'
                        + '<td style="padding:3px 0; vertical-align:top;">' + (item.cantidad || 1) + '</td>'
                        + '<td style="padding:3px 4px; vertical-align:top;">' + escDelivTicket(item.titulo || 'Producto') + '</td>'
                        + '<td style="padding:3px 0; vertical-align:top; text-align:right;">' + parseFloat(item.precio_unitario || 0).toFixed(2) + '</td>'
                        + '<td style="padding:3px 0; vertical-align:top; text-align:right; font-weight:bold;">' + parseFloat(item.subtotal || 0).toFixed(2) + '</td>'
                        + '</tr>';
                });
            }
            tbody.innerHTML = rows;
        }

        setTxt('lblDelivTicketSubtotal', 'S/ ' + subtotal.toFixed(2));
        setTxt('lblDelivTicketIgv', 'S/ ' + igv.toFixed(2));
        setTxt('lblDelivTicketTotal', 'S/ ' + total.toFixed(2));

        const rowEfectivo = document.getElementById('rowDelivTicketEfectivo');
        if (rowEfectivo) {
            if (montoRec > 0) {
                rowEfectivo.style.display = 'block';
                setTxt('lblDelivTicketRecibido', 'S/ ' + montoRec.toFixed(2));
                setTxt('lblDelivTicketVuelto', 'S/ ' + vueltoPed.toFixed(2));
            } else {
                rowEfectivo.style.display = 'none';
            }
        }

        const rowObs = document.getElementById('rowDelivTicketObs');
        if (rowObs) {
            const obs = p.observacion || env.notas_entrega || '';
            if (obs) {
                rowObs.style.display = 'block';
                setTxt('lblDelivTicketObs', obs);
            } else {
                rowObs.style.display = 'none';
            }
        }
    }

    window.abrirDetallePedidoDeliv = function(idPedido, idEnvio) {
        const modal = document.getElementById('modalDetalleDeliv');
        if (!modal) return;

        const env = listaEnvios.find(e => parseInt(e.id_envio) === parseInt(idEnvio)) || {};
        modal.style.display = 'flex';
        popularTicketDeliv({ pedido: { id_pedido: idPedido }, detalle: [] }, env);

        fetch('index.php?action=detalle_pedido_admin&id=' + idPedido, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(res => {
            if (!res || !res.success || !res.data) {
                if (window.mostrarDelivToast) window.mostrarDelivToast('Error', 'No se pudo cargar la boleta del pedido.', true);
                window.cerrarModalDetalleDeliv();
                return;
            }
            popularTicketDeliv(res.data, env);
        })
        .catch(() => {
            if (window.mostrarDelivToast) window.mostrarDelivToast('Error', 'Error de conexión al cargar la boleta.', true);
            window.cerrarModalDetalleDeliv();
        });
    };

    window.asegurarHtml2PdfDeliv = function(callback) {
        if (typeof window.html2pdf !== 'undefined') { callback(); return; }
        const exist = document.querySelector('script[src*="html2pdf"]');
        if (exist) { exist.addEventListener('load', function() { callback(); }); return; }
        const s = document.createElement('script');
        s.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js';
        s.onload = function() { callback(); };
        s.onerror = function() {
            if (window.mostrarDelivToast) window.mostrarDelivToast('Error', 'No se pudo cargar la librería para exportar PDF.', true);
        };
        document.head.appendChild(s);
    };

    function descargarTicketDelivPdf() {
        const ticketEl = document.getElementById('delivTicketImprimible');
        if (!ticketEl) return;
        const idPed = (ultimoTicketDelivData && ultimoTicketDelivData.pedido) ? ultimoTicketDelivData.pedido.id_pedido : 0;
        const filename = 'Comprobante-Pedido-' + String(idPed).padStart(5, '0') + '.pdf';
        if (window.mostrarDelivToast) window.mostrarDelivToast('Generando PDF', 'Descargando comprobante...', false);
        window.asegurarHtml2PdfDeliv(function() {
            const rect = ticketEl.getBoundingClientRect();
            let altoMm = Math.ceil((rect.height * 25.4) / 96) + 12;
            if (altoMm < 140) altoMm = 140;
            const opt = {
                margin: [4, 4, 4, 4],
                filename: filename,
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2, useCORS: true, logging: false, scrollX: 0, scrollY: 0 },
                jsPDF: { unit: 'mm', format: [80, altoMm], orientation: 'portrait' }
            };
            window.html2pdf().set(opt).from(ticketEl).save().then(function() {
                if (window.mostrarDelivToast) window.mostrarDelivToast('Descargado', 'Comprobante descargado con éxito.', false);
            }).catch(function() {
                if (window.mostrarDelivToast) window.mostrarDelivToast('Error', 'No se pudo generar el archivo PDF.', true);
            });
        });
    }

    const btnDelivTicketImprimir = document.getElementById('btnDelivTicketImprimir');
    if (btnDelivTicketImprimir) btnDelivTicketImprimir.addEventListener('click', function() { window.print(); });
    const btnDelivTicketDescargarPdf = document.getElementById('btnDelivTicketDescargarPdf');
    if (btnDelivTicketDescargarPdf) btnDelivTicketDescargarPdf.addEventListener('click', descargarTicketDelivPdf);

    const modalDetalleDelivEl = document.getElementById('modalDetalleDeliv');
    if (modalDetalleDelivEl) {
        modalDetalleDelivEl.addEventListener('click', function(e) {
            if (e.target === modalDetalleDelivEl) {
                window.cerrarModalDetalleDeliv();
            }
        });
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            window.cerrarModalDetalleDeliv();
        }
    });

    window.abrirModalAsignar = function(idEnvio) {
        document.getElementById('idEnvioAsignar').value = idEnvio;
        document.getElementById('modalAsignar').style.display = 'flex';
    };
    window.cerrarModalAsignar = function() {
        document.getElementById('modalAsignar').style.display = 'none';
    };

    window.abrirModalEstado = function(env) {
        const isRec = (env.tipo_entrega_norm === 'recojo');
        document.getElementById('idEnvioEstado').value = env.id_envio || 0;
        document.getElementById('idPedidoEstado').value = env.id_pedido || 0;

        const tit = document.getElementById('delivModalEstadoTitulo');
        const sub = document.getElementById('delivModalEstadoSubtitulo');
        const sel = document.getElementById('selectNuevoEstado');
        const lblCobrado = document.getElementById('lblCobradoTexto');

        if (tit) tit.textContent = isRec ? 'Actualizar Estado — Recojo en Tienda' : 'Actualizar Estado — Envío Delivery';
        if (sub) sub.textContent = isRec 
            ? 'Actualice la preparación del pedido para entrega al cliente en tienda.' 
            : 'Cambie el progreso logístico del pedido y registre cobro en caso aplique.';

        if (sel) {
            let options = '';
            if (isRec) {
                options = `
                    <option value="Pendiente">Pendiente</option>
                    <option value="En Preparacion">En Preparación</option>
                    <option value="Listo para Recojo">Listo para Recojo</option>
                    <option value="Entregado">Entregado</option>
                    <option value="Cancelado">Cancelado</option>
                `;
            } else {
                options = `
                    <option value="Pendiente">Pendiente</option>
                    <option value="En Preparacion">En Preparación</option>
                    <option value="Asignado">Asignado</option>
                    <option value="En Ruta">En Ruta</option>
                    <option value="Entregado">Entregado</option>
                    <option value="Fallido">Fallido / No entregado</option>
                `;
            }
            sel.innerHTML = options;
            sel.value = env.estado_delivery || 'Pendiente';
        }

        if (lblCobrado) {
            lblCobrado.textContent = isRec ? '¿Pago presencial completado en caja/mostrador?' : '¿Dinero cobrado por el repartidor?';
        }

        document.getElementById('chkCobrado').checked = parseInt(env.cobrado_por_repartidor) === 1;
        document.getElementById('txtNotasEstado').value = env.notas_entrega || env.observacion || '';
        document.getElementById('modalEstado').style.display = 'flex';
    };
    window.cerrarModalEstado = function() {
        document.getElementById('modalEstado').style.display = 'none';
    };

    // AJAX: ASIGNAR REPARTIDOR CON MODAL DE CONFIRMACIÓN Y NOTIFICACIÓN ARRIBA
    window.enviarAsignarAjax = function(e) {
        e.preventDefault();
        const idEnvio = document.getElementById('idEnvioAsignar')?.value;
        const sel = document.getElementById('selectRepartidor');
        const idRepartidor = sel ? sel.value : null;

        if (!idEnvio || !idRepartidor) {
            alert('Por favor selecciona un repartidor.');
            return;
        }

        const optSelected = sel.options[sel.selectedIndex];
        const repNombre = optSelected.getAttribute('data-nombre') || '';
        const repApellido = optSelected.getAttribute('data-apellido') || '';
        const repTelefono = optSelected.getAttribute('data-telefono') || '';

        // Cerrar modal de asignación para que no quede visible debajo
        window.cerrarModalAsignar();

        window.abrirDelivConfirmacion(
            '¿Asignar repartidor al envío?',
            'El repartidor ' + repNombre + ' ' + repApellido + ' será asignado al despacho de este pedido.',
            function() {
                const formData = new FormData();
                formData.append('id_envio', idEnvio);
                formData.append('id_repartidor', idRepartidor);
                formData.append('is_ajax', '1');

                fetch('index.php?action=asignar_repartidor', {
                    method: 'POST',
                    body: formData,
                    headers: { 
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(r => r.json())
                .then(res => {
                    if (res && res.success) {
                        window.mostrarDelivToast('Éxito', res.message || 'Repartidor asignado con éxito.', false);
                        const item = listaEnvios.find(x => String(x.id_envio) === String(idEnvio));
                        if (item) {
                            item.id_repartidor = idRepartidor;
                            item.repartidor_nombre = repNombre;
                            item.repartidor_apellido = repApellido;
                            item.repartidor_telefono = repTelefono;
                            if (item.estado_delivery === 'Pendiente') {
                                item.estado_delivery = 'Asignado';
                            }
                        }
                        window.actualizarContadoresDelivUI();
                        renderizarTabla();
                    } else {
                        window.mostrarDelivToast('Error', (res && res.message) || 'No se pudo asignar el repartidor.', true);
                    }
                })
                .catch(() => {
                    window.mostrarDelivToast('Error', 'Error de conexión al asignar repartidor.', true);
                });
            },
            false,
            function() {
                document.getElementById('modalAsignar').style.display = 'flex';
            }
        );
    };

    // AJAX: CAMBIAR ESTADO DELIVERY/RECOJO CON MODAL DE CONFIRMACIÓN Y NOTIFICACIÓN ARRIBA
    window.enviarEstadoAjax = function(e) {
        e.preventDefault();
        const idEnvio = document.getElementById('idEnvioEstado')?.value || 0;
        const idPedido = document.getElementById('idPedidoEstado')?.value || 0;
        const nuevoEstado = document.getElementById('selectNuevoEstado')?.value;
        const cobrado = document.getElementById('chkCobrado')?.checked ? 1 : 0;
        const notas = document.getElementById('txtNotasEstado')?.value || '';

        if ((!idEnvio && !idPedido) || !nuevoEstado) {
            return;
        }

        // Cerrar modal de estados inmediatamente
        window.cerrarModalEstado();

        window.abrirDelivConfirmacion(
            '¿Confirmar cambio de estado?',
            'El estado del pedido cambiará a: ' + nuevoEstado + (cobrado ? ' (Registrado como cobrado/pagado)' : '') + '.',
            function() {
                const formData = new FormData();
                formData.append('id_envio', idEnvio);
                formData.append('id_pedido', idPedido);
                formData.append('nuevo_estado', nuevoEstado);
                formData.append('cobrado', cobrado);
                formData.append('notas', notas);
                formData.append('is_ajax', '1');

                fetch('index.php?action=actualizar_estado_delivery', {
                    method: 'POST',
                    body: formData,
                    headers: { 
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(r => r.json())
                .then(res => {
                    if (res && res.success) {
                        window.mostrarDelivToast('Éxito', res.message || 'Estado actualizado.', false);
                        const item = listaEnvios.find(x => 
                            (idEnvio && parseInt(x.id_envio) === parseInt(idEnvio)) || 
                            (idPedido && parseInt(x.id_pedido) === parseInt(idPedido))
                        );
                        if (item) {
                            item.estado_delivery = nuevoEstado;
                            item.pedido_estado = nuevoEstado;
                            item.cobrado_por_repartidor = cobrado;
                            item.notas_entrega = notas;
                        }
                        window.actualizarContadoresDelivUI();
                        renderizarTabla();
                    } else {
                        window.mostrarDelivToast('Error', (res && res.message) || 'No se pudo actualizar el estado.', true);
                    }
                })
                .catch(() => {
                    window.mostrarDelivToast('Error', 'Error de conexión al actualizar estado.', true);
                });
            },
            false,
            function() {
                document.getElementById('modalEstado').style.display = 'flex';
            }
        );
    };

    // Render inicial
    renderizarTabla();
</script>

<?php if (!AjaxHelper::isAjax()): ?>
            </div>
        </div>
    </div>
</body>
</html>
<?php endif; ?>
