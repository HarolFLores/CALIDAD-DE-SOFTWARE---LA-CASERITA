<?php
$clPedidos = isset($bandejaPedidos) && is_array($bandejaPedidos) ? $bandejaPedidos : [];

if (!function_exists('clNormalizarEstado')) {
    function clNormalizarEstado($estado) {
        $valor = strtolower(trim(str_replace(['_', '-'], ' ', (string)$estado)));
        if (strpos($valor, 'cancel') !== false) return 'cancelado';
        if (strpos($valor, 'entreg') !== false) return 'entregado';
        if (strpos($valor, 'camin') !== false) return 'en_camino';
        if (strpos($valor, 'recoj') !== false || strpos($valor, 'listo') !== false) return 'listo_recojo';
        if (strpos($valor, 'prep') !== false) return 'en_preparacion';
        return 'pendiente';
    }
}

if (!function_exists('clEstadoDatos')) {
    function clEstadoDatos($estado) {
        $codigo = clNormalizarEstado($estado);
        $estados = [
            'pendiente' => 'Pendiente',
            'en_preparacion' => 'En Preparación',
            'en_camino' => 'En Ruta',
            'listo_recojo' => 'Listo para Recojo',
            'entregado' => 'Entregado',
            'cancelado' => 'Cancelado',
        ];
        return ['codigo' => $codigo, 'label' => $estados[$codigo] ?? 'Pendiente'];
    }
}

if (!function_exists('clTiempoPedido')) {
    function clTiempoPedido($minutos) {
        $minutos = max(0, (int)$minutos);
        if ($minutos < 1) return 'Ahora';
        if ($minutos < 60) return $minutos . ' min';
        $horas = intdiv($minutos, 60);
        return $horas . 'h ' . str_pad((string)($minutos % 60), 2, '0', STR_PAD_LEFT) . 'm';
    }
}

$clConteos = [
    'total' => count($clPedidos),
    'delivery' => 0,
    'sin_repartidor' => 0,
    'pendiente' => 0,
    'en_preparacion' => 0,
    'en_ruta' => 0,
    'recojo' => 0,
    'entregado' => 0,
    'por_cobrar' => 0.0,
];
foreach ($clPedidos as $clPedidoConteo) {
    $clCodigoConteo = clNormalizarEstado($clPedidoConteo['estado'] ?? '');
    $clEsDelivery = strtolower((string)($clPedidoConteo['tipo_entrega'] ?? '')) === 'delivery';
    $clTieneRepartidor = !empty($clPedidoConteo['id_repartidor']);
    $clFinalConteo = in_array($clCodigoConteo, ['entregado', 'cancelado'], true);
    $clMetodoConteo = mb_strtolower(trim((string)($clPedidoConteo['metodo_pago'] ?? '')));
    $clContraEntregaConteo = strpos($clMetodoConteo, 'efectivo') !== false
        || strpos($clMetodoConteo, 'contraentrega') !== false
        || strpos($clMetodoConteo, 'contra entrega') !== false;
    if ($clEsDelivery) $clConteos['delivery']++; else $clConteos['recojo']++;
    if ($clEsDelivery && !$clTieneRepartidor && !$clFinalConteo) $clConteos['sin_repartidor']++;
    if ($clCodigoConteo === 'pendiente') $clConteos['pendiente']++;
    if ($clCodigoConteo === 'en_preparacion') $clConteos['en_preparacion']++;
    if ($clCodigoConteo === 'en_camino' || ($clTieneRepartidor && !$clFinalConteo)) $clConteos['en_ruta']++;
    if ($clCodigoConteo === 'entregado') $clConteos['entregado']++;
    if ($clEsDelivery && $clContraEntregaConteo && empty($clPedidoConteo['cobrado_por_repartidor']) && $clCodigoConteo !== 'cancelado') {
        $clConteos['por_cobrar'] += (float)($clPedidoConteo['total'] ?? 0);
    }
}
?>

<section class="cl-board" aria-label="Bandeja unificada de pedidos">
    <div class="cl-summary" id="clSummary">
        <button type="button" class="cl-stat is-active" data-quick-filter="total"><span>Total pedidos</span><strong data-cl-count="total"><?= $clConteos['total']; ?></strong></button>
        <button type="button" class="cl-stat" data-quick-filter="delivery"><span>Delivery</span><strong data-cl-count="delivery"><?= $clConteos['delivery']; ?></strong></button>
        <button type="button" class="cl-stat" data-quick-filter="sin_repartidor"><span>Sin repartidor</span><strong data-cl-count="sin_repartidor"><?= $clConteos['sin_repartidor']; ?></strong></button>
        <button type="button" class="cl-stat" data-quick-filter="pendiente"><span>Pendientes</span><strong data-cl-count="pendiente"><?= $clConteos['pendiente']; ?></strong></button>
        <button type="button" class="cl-stat" data-quick-filter="en_preparacion"><span>En preparación</span><strong data-cl-count="en_preparacion"><?= $clConteos['en_preparacion']; ?></strong></button>
        <button type="button" class="cl-stat" data-quick-filter="en_ruta"><span>En ruta / asignados</span><strong data-cl-count="en_ruta"><?= $clConteos['en_ruta']; ?></strong></button>
        <button type="button" class="cl-stat" data-quick-filter="recojo"><span>Recojo en tienda</span><strong data-cl-count="recojo"><?= $clConteos['recojo']; ?></strong></button>
        <button type="button" class="cl-stat" data-quick-filter="entregado"><span>Entregados</span><strong data-cl-count="entregado"><?= $clConteos['entregado']; ?></strong></button>
        <button type="button" class="cl-stat" data-quick-filter="por_cobrar"><span>Total por cobrar</span><strong data-cl-count="por_cobrar">S/ <?= number_format($clConteos['por_cobrar'], 2); ?></strong></button>
    </div>

    <div class="cl-toolbar">
        <h2>Despachos y Entregas</h2>
        <div class="cl-toolbar-filters">
        <select id="clTypeFilter" class="cl-filter" aria-label="Filtrar por tipo de entrega">
            <option value="">Todos los tipos</option>
            <option value="delivery">Solo Delivery</option>
            <option value="recojo">Solo Recojo en Tienda</option>
        </select>
        <label class="cl-search">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            <input type="search" id="clSearch" placeholder="Buscar por pedido, cliente o dirección..." autocomplete="off">
        </label>
        <button type="button" class="cl-clear-button" id="clClearFilters">Limpiar</button>
        </div>
    </div>

    <div class="cl-table-wrap">
        <table class="cl-table" id="clOrdersTable">
            <thead>
                <tr>
                    <th>N° pedido</th>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th>Entrega</th>
                    <th>Pago</th>
                    <th class="cl-align-right">Total</th>
                    <th class="cl-align-center">Tiempo</th>
                    <th class="cl-align-center">Estado</th>
                    <th class="cl-actions-head">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clPedidos as $clPedido): ?>
                    <?php
                    $clId = (int)$clPedido['id_pedido'];
                    $clEstado = clEstadoDatos($clPedido['estado'] ?? '');
                    $clFinalizado = in_array($clEstado['codigo'], ['entregado', 'cancelado'], true);
                    $clMinutos = (int)($clPedido['minutos_espera'] ?? 0);
                    $clAlerta = !$clFinalizado && $clMinutos > 20;
                    $clCliente = trim(($clPedido['cliente_nombre'] ?? '') . ' ' . ($clPedido['cliente_apellido'] ?? ''));
                    if ($clCliente === '') $clCliente = trim((string)($clPedido['pedido_cliente_nombre'] ?? '')) ?: 'Público General';
                    $clEntrega = strtolower((string)($clPedido['tipo_entrega'] ?? '')) === 'delivery' ? 'Delivery' : 'Recojo';
                    $clMetodo = trim((string)($clPedido['metodo_pago'] ?? '—')) ?: '—';
                    $clMetodoLower = mb_strtolower($clMetodo);
                    $clContraEntrega = strpos($clMetodoLower, 'efectivo') !== false
                        || strpos($clMetodoLower, 'contraentrega') !== false
                        || strpos($clMetodoLower, 'contra entrega') !== false;
                    $clDireccion = trim((string)($clPedido['direccion_envio'] ?? ''));
                    $clTieneRepartidor = !empty($clPedido['id_repartidor']);
                    $clCobrado = !empty($clPedido['cobrado_por_repartidor']);
                    $clFecha = !empty($clPedido['fecha']) ? date('d/m/Y H:i', strtotime($clPedido['fecha'])) : '—';
                    $clBuscar = strtolower('#' . $clId . ' ' . $clCliente . ' ' . $clDireccion . ' ' . $clMetodo . ' ' . $clEntrega);
                    ?>
                    <tr class="cl-order-row<?= $clAlerta ? ' is-alert' : ''; ?>"
                        data-id="<?= $clId; ?>"
                        data-estado="<?= $clEstado['codigo']; ?>"
                        data-entrega="<?= strtolower($clEntrega); ?>"
                        data-repartidor="<?= $clTieneRepartidor ? '1' : '0'; ?>"
                        data-cobrado="<?= $clCobrado ? '1' : '0'; ?>"
                        data-efectivo="<?= $clContraEntrega ? '1' : '0'; ?>"
                        data-total="<?= number_format((float)($clPedido['total'] ?? 0), 2, '.', ''); ?>"
                        data-search="<?= htmlspecialchars($clBuscar, ENT_QUOTES, 'UTF-8'); ?>">
                        <td class="cl-order-id">#<?= str_pad((string)$clId, 5, '0', STR_PAD_LEFT); ?></td>
                        <td class="cl-nowrap"><?= htmlspecialchars($clFecha, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td class="cl-client" title="<?= htmlspecialchars($clCliente, ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($clCliente, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?= $clEntrega; ?></td>
                        <td class="cl-payment"><?= htmlspecialchars($clMetodo, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td class="cl-align-right cl-total">S/ <?= number_format((float)($clPedido['total'] ?? 0), 2); ?></td>
                        <td class="cl-align-center"><span class="cl-time<?= $clAlerta ? ' is-alert' : ''; ?>"><?= $clFinalizado ? '—' : clTiempoPedido($clMinutos); ?></span></td>
                        <td class="cl-align-center"><span class="cl-status cl-status--<?= $clEstado['codigo']; ?>"><?= $clEstado['label']; ?></span></td>
                        <td class="cl-actions-cell">
                            <div class="cl-kebab">
                                <button type="button" class="cl-kebab-button" aria-label="Acciones del pedido" aria-expanded="false">
                                    <svg viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="5" r="1.8"></circle><circle cx="12" cy="12" r="1.8"></circle><circle cx="12" cy="19" r="1.8"></circle></svg>
                                </button>
                                <div class="cl-kebab-menu">
                                    <button type="button" class="cl-action cl-action-state" data-id="<?= $clId; ?>">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                                        <span>Cambiar estado</span>
                                    </button>
                                    <button type="button" class="cl-action cl-action-detail" data-id="<?= $clId; ?>">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                        <span>Ver detalle</span>
                                    </button>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="cl-empty" id="clEmpty"<?= empty($clPedidos) ? '' : ' hidden'; ?>>No hay pedidos que coincidan con la búsqueda.</div>
    </div>

    <div class="pg-footer" id="clPagination"></div>
</section>

<div class="cl-modal" id="clStateModal" aria-hidden="true">
    <section class="cl-modal-card cl-modal-small" role="dialog" aria-modal="true" aria-labelledby="clStateTitle">
        <header class="cl-modal-header">
            <div><span class="cl-modal-kicker">Pedido</span><h2 id="clStateTitle">Cambiar estado</h2></div>
            <button type="button" class="btn-close-universal cl-modal-close" data-close-modal aria-label="Cerrar modal">&times;</button>
        </header>
        <div class="cl-modal-body">
            <label for="clStateSelect">Nuevo estado</label>
            <select id="clStateSelect" class="cl-state-select"></select>
            <p>El cliente recibirá la actualización en sus notificaciones.</p>
        </div>
        <footer class="cl-modal-footer">
            <button type="button" class="cl-primary-button" id="clSaveState">Guardar cambio</button>
        </footer>
    </section>
</div>

<!-- Modal Boleta / Comprobante del Pedido (Logística y Despacho) -->
<div class="cl-modal" id="clDetailModal" aria-hidden="true">
    <section class="cl-modal-card cl-ticket-modal-card" role="dialog" aria-modal="true" aria-labelledby="clDetailTitle">
        <header class="cl-modal-header cl-ticket-no-print">
            <h3 id="clDetailTitle" style="margin:0; font-size:1.05rem; font-weight:800; color:#0f172a; display:flex; align-items:center; gap:8px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#15803d" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
                Comprobante de Venta
            </h3>
            <button type="button" class="btn-close-universal cl-modal-close" data-close-modal aria-label="Cerrar modal">&times;</button>
        </header>

        <div class="cl-modal-body cl-ticket-modal-body">
            <div id="clTicketImprimible" class="cl-ticket-box">
                <div class="cl-ticket-header">
                    <strong class="cl-ticket-brand">TIENDA "LA CASERITA"</strong>
                    <div class="cl-ticket-sub">R.U.C. 20512345678</div>
                    <div class="cl-ticket-sub">Av. Las Flores 123 - Chiclayo, Lambayeque</div>
                    <div class="cl-ticket-sub">Teléfono: (074) 456-7890</div>
                    <div class="cl-ticket-dash"></div>
                    <div class="cl-ticket-doctitle">BOLETA DE VENTA ELECTRÓNICA</div>
                    <div class="cl-ticket-doccode" id="lblClTicketCodigo">B001-000000</div>
                    <div class="cl-ticket-numint" id="lblClTicketNumInt">PED-00000</div>
                </div>

                <div class="cl-ticket-client-info">
                    <div><strong>Fecha:</strong> <span id="lblClTicketFecha">--</span></div>
                    <div><strong>Cliente:</strong> <span id="lblClTicketCliente">CLIENTE GENERAL</span></div>
                    <div id="rowClTicketCorreo" style="display:none; word-break:break-all;"><strong>Correo:</strong> <span id="lblClTicketCorreo">--</span></div>
                    <div><strong>Entrega:</strong> <span id="lblClTicketEntrega">Recojo en tienda</span></div>
                    <div id="rowClTicketDireccion" style="display:none; word-break:break-word;"><strong>Dirección:</strong> <span id="lblClTicketDireccion">--</span></div>
                    <div><strong>Forma Pago:</strong> <span id="lblClTicketMetodo">EFECTIVO</span></div>
                    <div><strong>Estado:</strong> <span id="lblClTicketEstado">PENDIENTE</span></div>
                </div>

                <div class="cl-ticket-dash"></div>

                <table class="cl-ticket-items">
                    <thead>
                        <tr>
                            <th style="width:34px;">CANT</th>
                            <th>DESCRIPCIÓN</th>
                            <th style="text-align:right; width:50px;">P.U</th>
                            <th style="text-align:right; width:55px;">TOTAL</th>
                        </tr>
                    </thead>
                    <tbody id="clTicketTbodyItems">
                        <tr><td colspan="4" style="text-align:center; padding:10px 0; color:#64748b;">Cargando productos...</td></tr>
                    </tbody>
                </table>

                <div class="cl-ticket-dash"></div>

                <div class="cl-ticket-totals">
                    <div>SUBTOTAL: <strong id="lblClTicketSubtotal">S/ 0.00</strong></div>
                    <div>I.G.V. (18%): <strong id="lblClTicketIgv">S/ 0.00</strong></div>
                    <div class="cl-ticket-grand-total">TOTAL A PAGAR: <span id="lblClTicketTotal">S/ 0.00</span></div>
                </div>

                <div id="rowClTicketEfectivo" class="cl-ticket-cash" style="display:none;">
                    <div>RECIBIDO: <span id="lblClTicketRecibido">S/ 0.00</span></div>
                    <div>VUELTO: <span id="lblClTicketVuelto">S/ 0.00</span></div>
                </div>

                <div id="rowClTicketObs" class="cl-ticket-obs" style="display:none;">
                    <strong>Observación:</strong> <span id="lblClTicketObs">--</span>
                </div>

                <div class="cl-ticket-dash" style="margin: 10px 0;"></div>

                <div class="cl-ticket-footer">
                    <p style="margin:2px 0; font-weight:700;">¡GRACIAS POR SU PREFERENCIA!</p>
                    <p style="margin:2px 0; font-size:9px; color:#444;">Representación impresa de la Boleta de Venta Electrónica.</p>
                    <p style="margin:2px 0; font-size:8.5px; color:#666;">Conserve este comprobante para cualquier reclamo.</p>
                    <div style="margin-top:7px; letter-spacing:2px; font-size:16px; font-family:monospace;">||| | ||||| || |||| |||||</div>
                    <div style="font-size:8.5px; color:#777; margin-top:2px;">Aut. SUNAT - La Caserita</div>
                </div>
            </div>
        </div>

        <footer class="cl-modal-footer cl-ticket-no-print">
            <div class="cl-ticket-btn-group">
                <button type="button" class="cl-btn-print" id="btnClTicketImprimir">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                    Imprimir Comprobante
                </button>
                <button type="button" class="cl-btn-pdf" id="btnClTicketDescargarPdf">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                    Descargar PDF
                </button>
            </div>
        </footer>
    </section>
</div>

<div class="cl-toast" id="clToast" role="status" aria-live="polite"></div>

<script>
window.CAJERO_LOGISTICA = {
    csrf: <?= json_encode($csrfToken ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>
};
</script>
<script src="Scripts/logistica-cajero.js?v=<?= filemtime(__DIR__ . '/../../../Scripts/logistica-cajero.js'); ?>"></script>
