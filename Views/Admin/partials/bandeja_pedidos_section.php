<?php
/**
 * Sección reutilizable: Bandeja de Pedidos Pendientes (HU33).
 * Se muestra dentro de Logística y Despacho (delivery_admin).
 * Requiere: $bandejaPedidos, $bandejaEmpaque, $bandejaRepartidores.
 */
$bpPedidos     = isset($bandejaPedidos) && is_array($bandejaPedidos) ? $bandejaPedidos : [];
$bpEmpaqueData = isset($bandejaEmpaque) && is_array($bandejaEmpaque) ? $bandejaEmpaque : [];
$bpRepartoData = isset($bandejaRepartidores) && is_array($bandejaRepartidores) ? $bandejaRepartidores : [];

if (!function_exists('bpNormalizarEstado')) {
    function bpNormalizarEstado($e) {
        $c = strtolower(trim(str_replace(['_', '-'], ' ', (string)$e)));
        if (strpos($c, 'pend') !== false) return 'pendiente';
        if (strpos($c, 'prep') !== false) return 'en_preparacion';
        if (strpos($c, 'camin') !== false) return 'en_camino';
        if (strpos($c, 'recoj') !== false || strpos($c, 'listo') !== false) return 'listo_recojo';
        if (strpos($c, 'entreg') !== false) return 'entregado';
        if (strpos($c, 'cancel') !== false) return 'cancelado';
        return 'pendiente';
    }
}

if (!function_exists('bpEstadoInfo')) {
    function bpEstadoInfo($e) {
        $cod = bpNormalizarEstado($e);
        $map = [
            'pendiente'      => ['label' => 'Pendiente',         'clase' => 'pendiente'],
            'en_preparacion' => ['label' => 'En Preparación',    'clase' => 'en_preparacion'],
            'en_camino'      => ['label' => 'En Camino',         'clase' => 'en_camino'],
            'listo_recojo'   => ['label' => 'Listo para Recojo', 'clase' => 'listo_recojo'],
            'entregado'      => ['label' => 'Entregado',         'clase' => 'entregado'],
            'cancelado'      => ['label' => 'Cancelado',         'clase' => 'cancelado'],
        ];
        return $map[$cod] ?? $map['pendiente'];
    }
}

if (!function_exists('bpClasificar')) {
    function bpClasificar($p) {
        $cod    = bpNormalizarEstado($p['estado'] ?? '');
        $metodo = strtolower((string)($p['metodo_pago'] ?? ''));
        $esEfectivo = strpos($metodo, 'efectivo') !== false;
        $pago   = $p['pago_estado'] ?? null;

        if (!$esEfectivo && $cod === 'pendiente' && ($pago === null || strcasecmp((string)$pago, 'Pendiente') === 0)) {
            return 'por_validar';
        }
        if ($cod === 'en_camino' || in_array(($p['estado_delivery'] ?? ''), ['Asignado', 'En Ruta'], true)) {
            return 'en_ruta';
        }
        return 'empaque';
    }
}

if (!function_exists('bpTiempoTexto')) {
    function bpTiempoTexto($min) {
        $min = (int)$min;
        if ($min < 1) return 'Ahora';
        if ($min < 60) return $min . ' min';
        $h = intdiv($min, 60);
        $m = $min % 60;
        return $h . 'h ' . str_pad((string)$m, 2, '0', STR_PAD_LEFT) . 'm';
    }
}

$bpConteoTabs = ['por_validar' => 0, 'empaque' => 0, 'en_ruta' => 0];
$bpTotalAlerta = 0;
$bpPedidosPrep = [];

foreach ($bpPedidos as $p) {
    $clasif = bpClasificar($p);
    $alerta = ((int)($p['minutos_espera'] ?? 0) > 20) && bpNormalizarEstado($p['estado'] ?? '') === 'pendiente';
    $p['_clasif'] = $clasif;
    $p['_alerta'] = $alerta;
    if ($alerta) $bpTotalAlerta++;
    $bpConteoTabs[$clasif]++;
    $bpPedidosPrep[] = $p;
}

$bpEmpaqueJson  = json_encode(array_map(function ($u) {
    return [
        'id'     => (int)$u['id_usuario'],
        'nombre' => trim(($u['nombre'] ?? '') . ' ' . ($u['apellido'] ?? '')),
        'correo' => $u['correo'] ?? '',
    ];
}, $bpEmpaqueData), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

$bpRepartoJson = json_encode(array_map(function ($u) {
    return [
        'id'       => (int)$u['id_usuario'],
        'nombre'   => trim(($u['nombre'] ?? '') . ' ' . ($u['apellido'] ?? '')),
        'telefono' => $u['telefono'] ?? '',
    ];
}, $bpRepartoData), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
?>

<div class="bp-section">
    <?php if ($bpTotalAlerta > 0): ?>
    <div class="bp-alert-banner" id="bpAlertBanner">
        <span class="bp-alert-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
        </span>
        <span><strong><?= $bpTotalAlerta; ?> pedido(s)</strong> superan los <strong>20 minutos</strong> de espera sin ser atendidos. Prioriza su despacho.</span>
    </div>
    <?php endif; ?>

    <div class="bp-stats" id="bpStats">
        <button type="button" class="bp-stat active" data-tab="por_validar" onclick="window.bpSetTab('por_validar')">
            <span class="bp-stat-title">Por Validar Pago</span>
            <span class="bp-stat-val"><?= $bpConteoTabs['por_validar']; ?></span>
        </button>
        <button type="button" class="bp-stat" data-tab="empaque" onclick="window.bpSetTab('empaque')">
            <span class="bp-stat-title">Listos para Empaque</span>
            <span class="bp-stat-val"><?= $bpConteoTabs['empaque']; ?></span>
        </button>
        <button type="button" class="bp-stat" data-tab="en_ruta" onclick="window.bpSetTab('en_ruta')">
            <span class="bp-stat-title">En Ruta de Reparto</span>
            <span class="bp-stat-val"><?= $bpConteoTabs['en_ruta']; ?></span>
        </button>
        <button type="button" class="bp-stat bp-stat-alerta" data-tab="alerta" onclick="window.bpSetTab('alerta')">
            <span class="bp-stat-title">Con Alerta &gt; 20 min</span>
            <span class="bp-stat-val"><?= $bpTotalAlerta; ?></span>
        </button>
    </div>

    <section class="bp-panel">
        <div class="bp-panel-header">
            <div class="bp-tabs" id="bpTabs">
                <button type="button" class="bp-tab active" data-tab="por_validar" onclick="window.bpSetTab('por_validar')">Por Validar Pago</button>
                <button type="button" class="bp-tab" data-tab="empaque" onclick="window.bpSetTab('empaque')">Listos para Empaque</button>
                <button type="button" class="bp-tab" data-tab="en_ruta" onclick="window.bpSetTab('en_ruta')">En Ruta de Reparto</button>
            </div>
            <div class="bp-search-box">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                <input type="text" id="bpSearch" placeholder="Buscar pedido o cliente...">
            </div>
        </div>

        <div class="bp-bulk-bar" id="bpBulkBar">
            <span class="bp-bulk-count"><strong id="bpBulkCount">0</strong> seleccionado(s)</span>
            <div class="bp-bulk-actions">
                <button type="button" class="bp-btn bp-btn-empaque" id="bpBulkEmpaque">Asignar a Empaque</button>
                <button type="button" class="bp-btn bp-btn-reparto" id="bpBulkReparto">Asignar a Reparto</button>
                <button type="button" class="bp-btn bp-btn-ghost" id="bpBulkLimpiar">Limpiar</button>
            </div>
        </div>

        <div class="bp-table-scroll">
            <?php if (empty($bpPedidosPrep)): ?>
                <div class="bp-empty">
                    <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16v12H4z"></path><path d="M4 12h4l2 3h4l2-3h4"></path></svg>
                    <p>No hay pedidos pendientes por atender.</p>
                </div>
            <?php else: ?>
            <table class="bp-table" id="bpTabla">
                <thead>
                    <tr>
                        <th class="bp-col-check"><input type="checkbox" id="bpCheckAll" aria-label="Seleccionar todos"></th>
                        <th>N° PEDIDO</th>
                        <th>CLIENTE</th>
                        <th class="bp-th-center">ENTREGA</th>
                        <th class="bp-th-center">PAGO</th>
                        <th class="bp-th-right">TOTAL</th>
                        <th class="bp-th-center">ESPERA</th>
                        <th class="bp-th-center">ESTADO</th>
                        <th>EMPAQUE</th>
                        <th>REPARTO</th>
                        <th class="bp-col-acc"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bpPedidosPrep as $p): ?>
                        <?php
                            $idPed     = (int)$p['id_pedido'];
                            $padId     = str_pad($idPed, 5, '0', STR_PAD_LEFT);
                            $canalPed  = strtoupper(trim((string)($p['canal'] ?? 'WEB')));
                            if ($canalPed !== 'POS') $canalPed = 'WEB';
                            $cliente   = trim(($p['cliente_nombre'] ?? '') . ' ' . ($p['cliente_apellido'] ?? '')) ?: ($p['cliente_nombre'] ?: 'Sin nombre');
                            $entrega   = strtolower((string)($p['tipo_entrega'] ?? '')) === 'delivery' ? 'Delivery' : 'Recojo';
                            $estInfo   = bpEstadoInfo($p['estado'] ?? '');
                            $minutos   = (int)($p['minutos_espera'] ?? 0);
                            $tiempo    = bpTiempoTexto($minutos);
                            $empaque   = trim(($p['empaque_nombre'] ?? '') . ' ' . ($p['empaque_apellido'] ?? ''));
                            $reparto   = trim(($p['repartidor_nombre'] ?? '') . ' ' . ($p['repartidor_apellido'] ?? ''));
                            $metodo    = $p['metodo_pago'] ?? '—';
                            $metodoCorto = $metodo;
                            foreach (['efectivo' => 'Efectivo', 'yape' => 'Yape', 'plin' => 'Plin', 'tarjeta' => 'Tarjeta', 'transferencia' => 'Transferencia'] as $k => $v) {
                                if (stripos((string)$metodo, $k) !== false) { $metodoCorto = $v; break; }
                            }
                            $busq = strtolower("#{$padId} {$idPed} {$canalPed} " . ($cliente) . " " . ($p['cliente_correo'] ?? '') . " {$metodoCorto}");
                        ?>
                        <tr class="bp-row<?= !empty($p['_alerta']) ? ' bp-row-alerta' : ''; ?>"
                            data-id="<?= $idPed; ?>"
                            data-clasif="<?= $p['_clasif']; ?>"
                            data-alerta="<?= !empty($p['_alerta']) ? '1' : '0'; ?>"
                            data-search="<?= htmlspecialchars($busq, ENT_QUOTES, 'UTF-8'); ?>">
                            <td class="bp-col-check"><input type="checkbox" class="bp-check" value="<?= $idPed; ?>" aria-label="Seleccionar pedido <?= $padId; ?>"></td>
                            <td class="bp-cell-id">#<?= $padId; ?></td>
                            <td class="bp-cell-cliente" title="<?= htmlspecialchars($cliente, ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($cliente, ENT_QUOTES, 'UTF-8'); ?></td>
                            <td class="bp-tc"><?= $entrega; ?></td>
                            <td class="bp-tc"><?= htmlspecialchars($metodoCorto, ENT_QUOTES, 'UTF-8'); ?></td>
                            <td class="bp-tr bp-total">S/ <?= number_format((float)($p['total'] ?? 0), 2); ?></td>
                            <td class="bp-tc">
                                <span class="bp-tiempo<?= !empty($p['_alerta']) ? ' is-alerta' : ''; ?>"><?= $tiempo; ?></span>
                            </td>
                            <td class="bp-tc"><span class="bp-status <?= $estInfo['clase']; ?>"><?= $estInfo['label']; ?></span></td>
                            <td class="bp-cell-asig bp-cell-empaque"><?= $empaque !== '' ? htmlspecialchars($empaque, ENT_QUOTES, 'UTF-8') : '—'; ?></td>
                            <td class="bp-cell-asig bp-cell-reparto"><?= $reparto !== '' ? htmlspecialchars($reparto, ENT_QUOTES, 'UTF-8') : '—'; ?></td>
                            <td class="bp-col-acc">
                                <div class="table-kebab-container">
                                    <button type="button" class="table-kebab-btn" aria-label="Acciones" title="Opciones">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="5" r="1.8"/><circle cx="12" cy="12" r="1.8"/><circle cx="12" cy="19" r="1.8"/></svg>
                                    </button>
                                    <div class="table-kebab-menu">
                                        <button type="button" class="table-kebab-item bp-acc-empaque" data-id="<?= $idPed; ?>">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16v12H4z"></path><path d="M4 12h4l2 3h4l2-3h4"></path></svg>
                                            Asignar a empaque
                                        </button>
                                        <button type="button" class="table-kebab-item bp-acc-reparto" data-id="<?= $idPed; ?>">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="7" cy="17" r="2"></circle><circle cx="17" cy="17" r="2"></circle><path d="M5 17h-2v-4l2-5h9l4 5h1v4h-2"></path></svg>
                                            Asignar a reparto
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="bp-filter-empty" id="bpFilterEmpty">
                <p>No hay pedidos que coincidan con esta pestaña o búsqueda.</p>
            </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<!-- Modal de asignación (Bandeja) -->
<div class="bp-modal-overlay" id="bpModalOverlay">
    <div class="bp-modal-card">
        <div class="bp-modal-head">
            <h3 id="bpModalTitulo">Asignar pedidos</h3>
            <button type="button" class="bp-modal-close" id="bpModalCerrar" aria-label="Cerrar modal" title="Cerrar">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>
        <div class="bp-modal-body">
            <p class="bp-modal-resumen" id="bpModalResumen">1 pedido seleccionado.</p>
            <label class="bp-field-label" id="bpModalLabel" for="bpModalSelect">Personal de empaque</label>
            <select class="bp-select" id="bpModalSelect"></select>
            <p class="bp-modal-hint" id="bpModalHint"></p>
        </div>
        <div class="bp-modal-foot">
            <button type="button" class="bp-btn bp-btn-primary" id="bpModalConfirmar">Asignar</button>
        </div>
    </div>
</div>

<!-- Toast (Bandeja) -->
<div class="bp-toast" id="bpToast">
    <span class="bp-toast-icon" id="bpToastIcon"></span>
    <div class="bp-toast-body">
        <strong id="bpToastTitle">Éxito</strong>
        <span id="bpToastMsg"></span>
    </div>
</div>

<script>
    window.BP_PERSONAL_EMPAQUE = <?= $bpEmpaqueJson ?: '[]'; ?>;
    window.BP_REPARTIDORES = <?= $bpRepartoJson ?: '[]'; ?>;
</script>
<script src="Scripts/Admin/bandeja_pedidos.js?v=<?= filemtime(__DIR__ . '/../../../Scripts/Admin/bandeja_pedidos.js'); ?>"></script>
